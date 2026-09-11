#!/usr/bin/env python3
"""Generate docs/API_REFERENCE.html from docs/build_api_reference_html.php endpoint definitions."""

from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PHP_BUILD = ROOT / "build_api_reference_html.php"
OUT = ROOT / "API_REFERENCE.html"

BASE_URL = "{{BASE_URL}}"
PUBLIC_BASE = "{{PUBLIC_BASE}}"
TOKEN = "{{TOKEN}}"
AUTH_HEADER = f'-H "Authorization: Bearer {TOKEN}"'
JSON_HEADER = '-H "Content-Type: application/json"'


def esc(s: str) -> str:
    return (
        s.replace("&", "&amp;")
        .replace("<", "&lt;")
        .replace(">", "&gt;")
        .replace('"', "&quot;")
    )


def json_pretty(data) -> str:
    if data is None:
        return '<em class="muted">None</em>'
    if isinstance(data, str):
        return esc(data)
    return esc(json.dumps(data, indent=2, ensure_ascii=False))


def expand_curl(raw: str) -> str:
    """Resolve PHP curl template variables to Postman placeholders."""
    s = raw
    s = s.replace("{$base}", BASE_URL)
    s = s.replace("{$authHeader}", AUTH_HEADER)
    s = s.replace("{$jsonHeader}", JSON_HEADER)
    # Decode escapes from PHP double-quoted curl strings
    s = s.replace("\\n", "\n").replace('\\"', '"').replace("\\\\", "\\")
    return s


def _find_matching_bracket(text: str, start: int) -> int:
    """Return index after closing bracket for `[` at `start`."""
    depth = 0
    in_single = in_double = False
    i = start
    while i < len(text):
        c = text[i]
        if in_double:
            if c == "\\" and i + 1 < len(text):
                i += 2
                continue
            if c == '"':
                in_double = False
        elif in_single:
            if c == "\\" and i + 1 < len(text):
                i += 2
                continue
            if c == "'":
                in_single = False
        elif c == "'":
            in_single = True
        elif c == '"':
            in_double = True
        elif c == "[":
            depth += 1
        elif c == "]":
            depth -= 1
            if depth == 0:
                return i + 1
        i += 1
    raise ValueError("Unbalanced brackets")


def _php_string(raw: str) -> str:
    raw = raw.strip()
    if raw.startswith("'") and raw.endswith("'"):
        return raw[1:-1].replace("\\'", "'").replace("\\\\", "\\")
    if raw.startswith('"') and raw.endswith('"'):
        return bytes(raw[1:-1], "utf-8").decode("unicode_escape")
    return raw


def _php_scalar(raw: str):
    raw = raw.strip().rstrip(",")
    if raw == "null":
        return None
    if raw == "true":
        return True
    if raw == "false":
        return False
    if re.fullmatch(r"-?\d+", raw):
        return int(raw)
    if re.fullmatch(r"-?\d+\.\d+", raw):
        return float(raw)
    if raw.startswith("'") or raw.startswith('"'):
        return _php_string(raw)
    if raw.startswith("(") and raw.endswith(")"):
        return _php_string(raw[1:-1])
    return raw


def _is_assoc_array(inner: str) -> bool:
    inner = inner.lstrip()
    return bool(re.match(r"'(?:[^'\\]|\\.)*'\s*=>", inner))


def _read_value(inner: str):
    inner = inner.lstrip()
    if inner.startswith("["):
        end = _find_matching_bracket(inner, 0)
        return _php_array_to_python(inner[:end]), inner[end:]
    vm = re.match(
        r"('(?:[^'\\]|\\.)*'|\"(?:[^\"\\]|\\.)*\"|-?\d+(?:\.\d+)?|true|false|null|\([^)]*\))",
        inner,
    )
    if not vm:
        raise ValueError(f"Cannot parse PHP value near: {inner[:40]!r}")
    return _php_scalar(vm.group(1)), inner[vm.end() :]


def _php_array_to_python(text: str):
    text = text.strip()
    if text == "null":
        return None
    if not text.startswith("["):
        return _php_scalar(text)

    inner = text[1:-1].strip()
    if not inner:
        return []

    if _is_assoc_array(inner):
        result = {}
        rest = inner
        while rest.strip():
            rest = rest.lstrip()
            km = re.match(r"'((?:[^'\\]|\\.)*)'\s*=>\s*", rest)
            if not km:
                break
            key = km.group(1).replace("\\'", "'")
            val, rest = _read_value(rest[km.end() :])
            result[key] = val
            rest = rest.lstrip()
            if rest.startswith(","):
                rest = rest[1:]
        return result

    items = []
    rest = inner
    while rest.strip():
        val, rest = _read_value(rest)
        items.append(val)
        rest = rest.lstrip()
        if rest.startswith(","):
            rest = rest[1:]
    return items


def _extract_field(block: str, name: str) -> str | None:
    if name == "curl":
        m = re.search(r"'curl'\s*=>\s*\"((?:[^\"\\]|\\.)*)\"", block, re.S)
        return m.group(1) if m else None
    if name in ("request", "response"):
        m = re.search(rf"'{name}'\s*=>\s*", block)
        if not m:
            return None
        rest = block[m.end() :].lstrip()
        if rest.startswith("null"):
            return "null"
        if rest.startswith("["):
            end = _find_matching_bracket(rest, 0)
            return rest[:end]
        if rest.startswith("("):
            end = rest.index(")") + 1
            return rest[:end]
        return None
    m = re.search(rf"'{name}'\s*=>\s*'((?:[^'\\]|\\.)*)'", block)
    return m.group(1).replace("\\'", "'") if m else None


def parse_php_endpoints() -> list[dict]:
    src = PHP_BUILD.read_text()
    start = src.index("return [")
    end = src.index("];", start)
    chunk = src[start + len("return [") : end]
    # Strip line comments so blocks separated by // ── section ── still split cleanly
    chunk = re.sub(r"^\s*//.*$", "", chunk, flags=re.M)

    parts = re.split(r"(?<=\]),\s*\n\s*\[", chunk)
    endpoints = []
    for part in parts:
        block = part if part.strip().startswith("'group'") else "[" + part
        if "'group'" not in block:
            continue

        group = _extract_field(block, "group")
        method = _extract_field(block, "method")
        path = _extract_field(block, "path")
        auth = _extract_field(block, "auth")
        desc = _extract_field(block, "desc")
        curl_raw = _extract_field(block, "curl")
        curl = expand_curl(curl_raw) if curl_raw else ""

        req_raw = _extract_field(block, "request")
        request = _php_array_to_python(req_raw) if req_raw else None

        res_raw = _extract_field(block, "response")
        response = _php_array_to_python(res_raw) if res_raw else None

        if not all([group, method, path]):
            continue

        endpoints.append(
            {
                "group": group,
                "method": method,
                "path": path,
                "auth": auth or "",
                "desc": desc or "",
                "curl": curl,
                "request": request,
                "response": response,
            }
        )
    return endpoints


def slug(ep: dict) -> str:
    s = f"{ep['method']}-{ep['path']}"
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def render(endpoints: list[dict]) -> str:
    groups: dict[str, list[dict]] = {}
    for ep in endpoints:
        groups.setdefault(ep["group"], []).append(ep)

    nav = []
    body = []
    for gname, items in groups.items():
        nav.append(f"<h2>{esc(gname)}</h2>")
        body.append(f'<h2 class="group-title">{esc(gname)}</h2>')
        for ep in items:
            sid = slug(ep)
            nav.append(f'<a href="#{sid}">{esc(ep["method"])} {esc(ep["path"])}</a>')
            mclass = ep["method"].lower()
            body.append(
                f"""
<article class="endpoint" id="{sid}">
  <h3><span class="method {mclass}">{esc(ep["method"])}</span> <span class="path">{esc(ep["path"])}</span></h3>
  <p class="desc">{esc(ep["desc"])}</p>
  <p class="meta"><strong>Auth:</strong> {esc(ep["auth"])}</p>
  <h4>cURL — Postman import</h4>
  <pre class="curl" data-copy>{esc(ep["curl"])}<button type="button" class="copy-btn" onclick="copyPre(this)">Copy</button></pre>
  <h4>Request body (JSON)</h4>
  <pre class="json">{json_pretty(ep["request"])}</pre>
  <h4>Response example</h4>
  <pre class="json">{json_pretty(ep["response"])}</pre>
</article>"""
            )

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FM ERP — API Reference (Postman / cURL)</title>
<style>
:root {{ --bg:#0f1419; --card:#1a2332; --border:#2d3a4f; --text:#e7ecf3; --muted:#8b9cb3; --get:#61affe; --post:#49cc90; --accent:#7c6cff; }}
* {{ box-sizing:border-box; }}
body {{ font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--bg); color:var(--text); margin:0; line-height:1.5; }}
header {{ background:linear-gradient(135deg,#1a2332,#252d3d); border-bottom:1px solid var(--border); padding:2rem; }}
header h1 {{ margin:0 0 .5rem; font-size:1.75rem; }}
header p {{ margin:.25rem 0; color:var(--muted); max-width:720px; }}
.wrap {{ display:flex; gap:0; max-width:1400px; margin:0 auto; }}
nav {{ width:260px; flex-shrink:0; position:sticky; top:0; height:100vh; overflow-y:auto; padding:1rem; border-right:1px solid var(--border); background:#121820; }}
nav h2 {{ font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin:1.25rem 0 .5rem; }}
nav a {{ display:block; color:#b8c5d6; text-decoration:none; font-size:.85rem; padding:.25rem 0; }}
nav a:hover {{ color:#fff; }}
main {{ flex:1; padding:1.5rem 2rem 3rem; min-width:0; }}
.vars {{ background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; margin-bottom:2rem; }}
.vars code {{ background:#0d1117; padding:.15rem .4rem; border-radius:4px; font-size:.85rem; }}
.endpoint {{ background:var(--card); border:1px solid var(--border); border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; scroll-margin-top:1rem; }}
.endpoint h3 {{ margin:0 0 .75rem; font-size:1.05rem; display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }}
.method {{ font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:4px; text-transform:uppercase; color:#000; }}
.method.get {{ background:var(--get); }}
.method.post {{ background:var(--post); }}
.path {{ font-family: ui-monospace, monospace; font-size:.9rem; color:#c9d6e3; }}
.desc {{ color:var(--muted); margin:0 0 .75rem; }}
.meta {{ font-size:.85rem; margin-bottom:1rem; }}
.meta strong {{ color:var(--text); }}
h4 {{ font-size:.8rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin:1rem 0 .4rem; }}
pre {{ background:#0d1117; border:1px solid var(--border); border-radius:6px; padding:.85rem 1rem; overflow-x:auto; font-size:.78rem; line-height:1.45; margin:0; position:relative; }}
pre.curl {{ border-left:3px solid var(--accent); white-space:pre-wrap; word-break:break-all; }}
.copy-btn {{ position:absolute; top:.5rem; right:.5rem; background:#2d3a4f; border:none; color:#fff; font-size:.7rem; padding:.25rem .5rem; border-radius:4px; cursor:pointer; }}
.copy-btn:hover {{ background:var(--accent); }}
.muted {{ color:var(--muted); font-style:italic; }}
.group-title {{ font-size:1.35rem; margin:2rem 0 1rem; padding-bottom:.5rem; border-bottom:1px solid var(--border); }}
.note {{ background:#1e2a3a; border-left:3px solid var(--get); padding:.75rem 1rem; border-radius:0 6px 6px 0; margin-bottom:1.5rem; font-size:.9rem; }}
@media (max-width:900px) {{ .wrap {{ flex-direction:column; }} nav {{ width:100%; height:auto; position:relative; }} }}
</style>
</head>
<body>
<header>
  <h1>FM ERP — REST API Reference</h1>
  <p>CodeIgniter 4 · API v1 · Postman-ready cURL for every endpoint with JSON examples</p>
</header>
<div class="wrap">
<nav>
  <h2>Setup</h2>
  <a href="#variables">Variables</a>
  <a href="#postman">Postman import</a>
  {''.join(nav)}
</nav>
<main>
<section id="variables" class="vars">
  <h2 style="margin-top:0;font-size:1.1rem;">Replace before calling</h2>
  <p><code>{{{{BASE_URL}}}}</code> — API base, e.g. <code>https://your-domain.com/public/api/v1</code></p>
  <p><code>{{{{PUBLIC_BASE}}}}</code> — Site root, e.g. <code>https://your-domain.com/public</code></p>
  <p><code>{{{{TOKEN}}}}</code> — JWT from <code>POST /auth/login</code> (<code>Authorization: Bearer …</code>)</p>
</section>
<section id="postman" class="note">
  <strong>Import into Postman:</strong> Click <em>Import → Raw text</em> and paste any cURL block below. Postman converts it to a ready request. Set collection variables <code>BASE_URL</code> and <code>TOKEN</code> to avoid editing each request.
</section>
{''.join(body)}
</main>
</div>
<script>
function copyPre(btn) {{
  const text = btn.parentElement.textContent.replace('Copy','').trim();
  navigator.clipboard.writeText(text).then(() => {{ btn.textContent='Copied!'; setTimeout(()=>btn.textContent='Copy',1500); }});
}}
</script>
</body>
</html>"""


def try_php_build() -> bool:
    try:
        subprocess.run(["php", str(PHP_BUILD)], check=True, capture_output=True, text=True)
        return OUT.exists()
    except (FileNotFoundError, subprocess.CalledProcessError):
        return False


def main() -> int:
    if try_php_build():
        print(f"Generated via PHP: {OUT}")
        return 0
    endpoints = parse_php_endpoints()
    if not endpoints:
        print("Failed to parse endpoints from PHP source", file=sys.stderr)
        return 1
    OUT.write_text(render(endpoints), encoding="utf-8")
    print(
        f"Generated via Python: {OUT} ({len(endpoints)} endpoints, {OUT.stat().st_size} bytes)"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
