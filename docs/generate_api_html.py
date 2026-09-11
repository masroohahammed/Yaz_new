#!/usr/bin/env python3
"""Generate API HTML docs from docs/build_api_reference_html.php endpoint definitions."""

from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PHP_BUILD = ROOT / "build_api_reference_html.php"
PHP_MOBILE_BUILD = ROOT / "build_mobile_api_reference_html.php"
OUT_FULL = ROOT / "API_REFERENCE.html"
OUT_MOBILE = ROOT / "mobile-api-reference.html"

# Original Flutter mobile app flow (see docs/mobile-api-reference.html / uploaded reference)
MOBILE_GROUPS = [
    "System",
    "Authentication",
    "Tenant Portal",
    "Facility Management (FM)",
    "Employee Self-Service",
    "Property Management",
    "Finance",
    "App Telemetry",
]

MOBILE_EXCLUDED_PATHS = {
    "/api/v1/finance/invoices",  # GET and POST share path prefix — excluded via exact match below
    "/api/v1/work-orders",
    "/api/v1/work-orders/{id}",
    "/api/v1/work-orders/{id}/delete",
    "/api/v1/inspections/properties?facility_id=&status=&frequency=",
    "/api/v1/inspections/properties/{id}",
    "/api/v1/inspections/units?facility_id=&type=&frequency=",
    "/api/v1/inspections/units/{id}",
    "/api/public/maintenance",
    "/api/public/track/{ticket}",
    "/api/v1/{unknown-path}",
}

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


MOBILE_SECTIONS = [
    (
        "auth",
        "Authentication",
        ["System", "Authentication"],
        None,
    ),
    (
        "portal",
        "Tenant Portal",
        ["Tenant Portal"],
        None,
    ),
    (
        "fm",
        "Facility Management",
        ["Facility Management (FM)"],
        "Roles: facility_manager, supervisor, technician, super_admin, qa_inspector",
    ),
    (
        "employee",
        "Employee Self-Service",
        ["Employee Self-Service"],
        "Requires employees record linked to user · Also available to FM staff via Account menu",
    ),
    (
        "other",
        "Properties, Finance & Logging",
        ["Property Management", "Finance", "App Telemetry"],
        None,
    ),
]


def short_path(path: str) -> str:
    for prefix in ("/api/v1", "/api/public"):
        if path.startswith(prefix):
            return path[len(prefix) :] or "/"
    return path


def endpoint_title(ep: dict) -> str:
    overrides = {
        ("GET", "/api/v1/health"): "Health Check",
        ("POST", "/api/v1/auth/login"): "Login",
        ("GET", "/api/v1/auth/me"): "Current User",
        ("POST", "/api/v1/app-log"): "App Log",
    }
    key = (ep["method"], ep["path"].split("?")[0])
    if key in overrides:
        return overrides[key]
    p = short_path(ep["path"]).split("?")[0].strip("/")
    parts = [x for x in p.split("/") if x and not x.startswith("{")]
    if not parts:
        return ep["method"]
    return parts[-1].replace("-", " ").title()


def auth_badge(auth: str) -> tuple[str, str]:
    low = auth.lower()
    if auth == "None" or ("none" in low and "jwt" not in low):
        return "pub", "Public"
    if "optional" in low:
        return "pub", "Public (optional JWT)"
    return "jwt", "JWT required"


def json_block(data) -> str:
    if data is None:
        return "None"
    if isinstance(data, str):
        return data
    return json.dumps(data, indent=2, ensure_ascii=False)


ORIGINAL_THEME = ROOT / "mobile-api-reference-original.html"


def load_original_css() -> str:
    """CSS from mobile-api-reference-original.html (single source for theme)."""
    if not ORIGINAL_THEME.exists():
        raise FileNotFoundError(f"Missing theme reference: {ORIGINAL_THEME}")
    html = ORIGINAL_THEME.read_text(encoding="utf-8")
    m = re.search(r"<style>(.*?)</style>", html, re.S)
    if not m:
        raise ValueError("Could not extract CSS from mobile-api-reference-original.html")
    return m.group(1).strip()


def format_curl_original(curl: str) -> str:
    """Single-quoted cURL like the original reference."""
    return curl.replace('\\"', "'").replace('"', "'")


def format_input(ep: dict) -> tuple[str, str]:
    if ep["request"] is not None:
        return "Request JSON", json_block(ep["request"])
    if ep["method"] == "GET" and "Bearer" in ep["auth"]:
        return "Input", "Bearer token"
    if "?" in ep["path"]:
        q = short_path(ep["path"]).split("?", 1)[1]
        return "Input", f"Query: {q.replace('&', ', ')}"
    return "Input", "None"


def http_codes(ep: dict) -> str:
    if ep["auth"] == "None":
        return "HTTP: 200 OK"
    if ep["method"] == "POST":
        return "HTTP: 200 · 400 validation · 401 unauthorized · 403 forbidden"
    return "HTTP: 200 · 401 unauthorized · 403 forbidden"


def _ordered_groups(endpoints: list[dict], group_order: list[str] | None) -> dict[str, list[dict]]:
    groups: dict[str, list[dict]] = {}
    for ep in endpoints:
        groups.setdefault(ep["group"], []).append(ep)
    if not group_order:
        return groups
    ordered: dict[str, list[dict]] = {}
    for g in group_order:
        if g in groups:
            ordered[g] = groups[g]
    return ordered


def render(
    endpoints: list[dict],
    *,
    title: str,
    subtitle: str,
    page_title: str,
    extra_nav: str = "",
    extra_main: str = "",
    group_order: list[str] | None = None,
    flow_styles: bool = False,
) -> str:
    groups = _ordered_groups(endpoints, group_order)

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

    flow_css = ""
    if flow_styles:
        flow_css = """
.flow { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem; }
.flow ol { margin:.5rem 0 0; padding-left:1.25rem; color:var(--muted); font-size:.9rem; }
.flow li { margin:.35rem 0; }
.flow code { background:#0d1117; padding:.1rem .35rem; border-radius:4px; font-size:.82rem; }"""

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{esc(page_title)}</title>
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
.vars code {{ background:#0d1117; padding:.15rem .4rem; border-radius:4px; font-size:.85rem; }}{flow_css}
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
  <h1>{esc(title)}</h1>
  <p>{esc(subtitle)}</p>
</header>
<div class="wrap">
<nav>
  <h2>Setup</h2>
  <a href="#variables">Variables</a>
  {extra_nav}
  <a href="#postman">Postman import</a>
  {''.join(nav)}
</nav>
<main>
<section id="variables" class="vars">
  <h2 style="margin-top:0;font-size:1.1rem;">Replace before calling</h2>
  <p><code>{{{{BASE_URL}}}}</code> — API base, e.g. <code>https://your-domain.com/public/api/v1</code></p>
  <p><code>{{{{PUBLIC_BASE}}}}</code> — Site root, e.g. <code>https://your-domain.com/public</code></p>
  <p><code>{{{{TOKEN}}}}</code> — Session token from <code>POST /auth/login</code> (<code>Authorization: Bearer …</code>, valid 24h)</p>
</section>
{extra_main}
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


def render_mobile_clean(endpoints: list[dict]) -> str:
    """Layout + CSS from docs/mobile-api-reference-original.html."""
    css = load_original_css()
    by_group: dict[str, list[dict]] = {}
    for ep in endpoints:
        by_group.setdefault(ep["group"], []).append(ep)

    toc_items = "".join(
        f'<li><a href="#{sid}">{esc(label)}</a></li>'
        for sid, label, _, _ in MOBILE_SECTIONS
    )

    sections_html = []
    for sid, label, groups, subtitle in MOBILE_SECTIONS:
        eps_in_section: list[dict] = []
        for g in groups:
            eps_in_section.extend(by_group.get(g, []))
        if not eps_in_section:
            continue

        sub = f'<p class="sub">{esc(subtitle)}</p>' if subtitle else ""
        articles = []
        for ep in eps_in_section:
            eid = slug(ep)
            mclass = ep["method"].lower()
            badge_cls, badge_text = auth_badge(ep["auth"])
            if badge_text == "Public (optional JWT)":
                badge_text = "Public"
            title = endpoint_title(ep)
            req_label, req_body = format_input(ep)
            resp_body = json_block(ep["response"])
            resp_pre = (
                f'<pre class="json">{esc(resp_body)}</pre>'
                if ep["response"] is not None and not isinstance(ep["response"], str)
                else f"<pre>{esc(resp_body)}</pre>"
            )
            articles.append(
                f"""
<article class="ep" id="{eid}">
  <div class="ep-head">
    <span class="method {mclass}">{esc(ep["method"])}</span>
    <code class="path">{esc(short_path(ep["path"]))}</code>
    <span class="badge {badge_cls}">{esc(badge_text)}</span>
  </div>
  <div class="ep-body">
    <p class="use"><strong>{esc(title)}</strong> — {esc(ep["desc"])}</p>
    <p class="codes">{esc(http_codes(ep))}</p>
    <div class="label">{req_label}</div><pre>{esc(req_body)}</pre>
    <div class="label">Response example</div>
    {resp_pre}
    <div class="label">cURL</div>
    <pre class="curl">{esc(format_curl_original(ep["curl"]))}</pre>
  </div>
</article>"""
            )

        sections_html.append(
            f'<section id="{sid}"><h2>{esc(label)}</h2>{sub}{"".join(articles)}</section>'
        )

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Al Yazwa FM — API Reference</title>
<style>
{css}
</style>
</head>
<body>
<header><div class="wrap" style="padding:0"><h1>Al Yazwa FM — API Reference</h1><p>REST API v1 · Postman import · cURL · JSON examples</p></div></header>
<div class="wrap">
<div class="hero">
  <table>
    <tr><th>Base URL</th><td><code>https://pfms.alyazwa.com/api/v1</code></td></tr>
    <tr><th>Auth</th><td><code>Authorization: Bearer &lt;token&gt;</code> (from POST /auth/login)</td></tr>
    <tr><th>Content-Type</th><td><code>application/json</code></td></tr>
    <tr><th>Token lifetime</th><td>24 hours</td></tr>
  </table>
  <div class="btns">
    <a href="alyazwa-fm-api.postman_collection.json" download>Download Postman Collection</a>
    <a href="index.html" class="green">App Downloads</a>
  </div>
</div>
<nav class="toc"><strong>Endpoints</strong><ul>{toc_items}</ul></nav>
{"".join(sections_html)}
</div>
<footer>Al Yazwa FM API v1 · <a href="index.html">Downloads</a></footer>
</body></html>"""


def try_php_build() -> bool:
    try:
        subprocess.run(["php", str(PHP_BUILD)], check=True, capture_output=True, text=True)
        subprocess.run(
            ["php", str(PHP_MOBILE_BUILD)], check=True, capture_output=True, text=True
        )
        return OUT_FULL.exists() and OUT_MOBILE.exists()
    except (FileNotFoundError, subprocess.CalledProcessError):
        return False


def mobile_endpoints(all_eps: list[dict]) -> list[dict]:
    allowed = set(MOBILE_GROUPS)
    return [
        ep
        for ep in all_eps
        if ep["group"] in allowed and ep["path"] not in MOBILE_EXCLUDED_PATHS
    ]


def write_python_docs(endpoints: list[dict]) -> None:
    OUT_FULL.write_text(
        render(
            endpoints,
            title="FM ERP — REST API Reference",
            subtitle="CodeIgniter 4 · API v1 · Postman-ready cURL for every endpoint with JSON examples",
            page_title="FM ERP — API Reference (Postman / cURL)",
        ),
        encoding="utf-8",
    )
    mobile_eps = mobile_endpoints(endpoints)
    OUT_MOBILE.write_text(render_mobile_clean(mobile_eps), encoding="utf-8")


def main() -> int:
    if try_php_build():
        print(f"Generated via PHP: {OUT_FULL}, {OUT_MOBILE}")
        return 0
    endpoints = parse_php_endpoints()
    if not endpoints:
        print("Failed to parse endpoints from PHP source", file=sys.stderr)
        return 1
    write_python_docs(endpoints)
    mobile_eps = mobile_endpoints(endpoints)
    print(
        f"Generated via Python: {OUT_FULL} ({len(endpoints)} endpoints, {OUT_FULL.stat().st_size} bytes)"
    )
    print(
        f"Generated via Python: {OUT_MOBILE} ({len(mobile_eps)} endpoints, {OUT_MOBILE.stat().st_size} bytes)"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
