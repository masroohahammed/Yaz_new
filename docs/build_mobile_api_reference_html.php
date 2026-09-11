<?php
/**
 * Generates docs/mobile-api-reference.html — mobile app flow (see docs/MOBILE_API.md).
 * Run: php docs/build_mobile_api_reference_html.php
 */

require_once __DIR__ . '/build_api_reference_html.php';

$mobileGroups = [
    'System',
    'Authentication',
    'Tenant Portal',
    'Facility Management (FM)',
    'Employee Self-Service',
    'Property Management',
    'Finance',
    'App Telemetry',
];

$mobileExcludedPaths = [
    '/api/v1/finance/invoices',
    '/api/v1/work-orders',
    '/api/v1/work-orders/{id}',
    '/api/v1/work-orders/{id}/delete',
    '/api/v1/inspections/properties?facility_id=&status=&frequency=',
    '/api/v1/inspections/properties/{id}',
    '/api/v1/inspections/units?facility_id=&type=&frequency=',
    '/api/v1/inspections/units/{id}',
    '/api/public/maintenance',
    '/api/public/track/{ticket}',
    '/api/v1/{unknown-path}',
];

$eps = array_values(array_filter(
    endpoints(),
    static fn(array $ep): bool => in_array($ep['group'], $mobileGroups, true)
        && ! in_array($ep['path'], $mobileExcludedPaths, true)
));

$groups = [];
foreach ($eps as $ep) {
    $groups[$ep['group']][] = $ep;
}
// Preserve MOBILE_API.md section order
$ordered = [];
foreach ($mobileGroups as $g) {
    if (isset($groups[$g])) {
        $ordered[$g] = $groups[$g];
    }
}

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FM ERP — Mobile API Reference (Postman / cURL)</title>
<style>
:root { --bg:#0f1419; --card:#1a2332; --border:#2d3a4f; --text:#e7ecf3; --muted:#8b9cb3; --get:#61affe; --post:#49cc90; --accent:#7c6cff; }
* { box-sizing:border-box; }
body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--bg); color:var(--text); margin:0; line-height:1.5; }
header { background:linear-gradient(135deg,#1a2332,#252d3d); border-bottom:1px solid var(--border); padding:2rem; }
header h1 { margin:0 0 .5rem; font-size:1.75rem; }
header p { margin:.25rem 0; color:var(--muted); max-width:720px; }
.wrap { display:flex; gap:0; max-width:1400px; margin:0 auto; }
nav { width:260px; flex-shrink:0; position:sticky; top:0; height:100vh; overflow-y:auto; padding:1rem; border-right:1px solid var(--border); background:#121820; }
nav h2 { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin:1.25rem 0 .5rem; }
nav a { display:block; color:#b8c5d6; text-decoration:none; font-size:.85rem; padding:.25rem 0; }
nav a:hover { color:#fff; }
main { flex:1; padding:1.5rem 2rem 3rem; min-width:0; }
.vars { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; margin-bottom:2rem; }
.vars code { background:#0d1117; padding:.15rem .4rem; border-radius:4px; font-size:.85rem; }
.flow { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem; }
.flow ol { margin:.5rem 0 0; padding-left:1.25rem; color:var(--muted); font-size:.9rem; }
.flow li { margin:.35rem 0; }
.flow code { background:#0d1117; padding:.1rem .35rem; border-radius:4px; font-size:.82rem; }
.endpoint { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; scroll-margin-top:1rem; }
.endpoint h3 { margin:0 0 .75rem; font-size:1.05rem; display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
.method { font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:4px; text-transform:uppercase; color:#000; }
.method.get { background:var(--get); }
.method.post { background:var(--post); }
.path { font-family: ui-monospace, monospace; font-size:.9rem; color:#c9d6e3; }
.desc { color:var(--muted); margin:0 0 .75rem; }
.meta { font-size:.85rem; margin-bottom:1rem; }
.meta strong { color:var(--text); }
h4 { font-size:.8rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin:1rem 0 .4rem; }
pre { background:#0d1117; border:1px solid var(--border); border-radius:6px; padding:.85rem 1rem; overflow-x:auto; font-size:.78rem; line-height:1.45; margin:0; position:relative; }
pre.curl { border-left:3px solid var(--accent); white-space:pre-wrap; word-break:break-all; }
.copy-btn { position:absolute; top:.5rem; right:.5rem; background:#2d3a4f; border:none; color:#fff; font-size:.7rem; padding:.25rem .5rem; border-radius:4px; cursor:pointer; }
.copy-btn:hover { background:var(--accent); }
.muted { color:var(--muted); font-style:italic; }
.group-title { font-size:1.35rem; margin:2rem 0 1rem; padding-bottom:.5rem; border-bottom:1px solid var(--border); }
.note { background:#1e2a3a; border-left:3px solid var(--get); padding:.75rem 1rem; border-radius:0 6px 6px 0; margin-bottom:1.5rem; font-size:.9rem; }
@media (max-width:900px) { .wrap { flex-direction:column; } nav { width:100%; height:auto; position:relative; } }
</style>
</head>
<body>
<header>
  <h1>FM ERP — Mobile API Reference</h1>
  <p>Original Flutter mobile app flow · API v1 · Postman-ready cURL with JSON examples</p>
</header>
<div class="wrap">
<nav>
  <h2>Setup</h2>
  <a href="#variables">Variables</a>
  <a href="#app-flow">App flow</a>
  <a href="#postman">Postman import</a>
  <?php foreach (array_keys($ordered) as $gName): ?>
  <h2><?= escHtml($gName) ?></h2>
  <?php foreach ($ordered[$gName] as $ep):
      $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($ep['method'] . '-' . $ep['path']));
  ?>
  <a href="#<?= escHtml($slug) ?>"><?= escHtml($ep['method']) ?> <?= escHtml($ep['path']) ?></a>
  <?php endforeach; endforeach; ?>
</nav>
<main>
<section id="variables" class="vars">
  <h2 style="margin-top:0;font-size:1.1rem;">Replace before calling</h2>
  <p><code>{{BASE_URL}}</code> — API base, e.g. <code>https://your-domain.com/public/api/v1</code></p>
  <p><code>{{PUBLIC_BASE}}</code> — Site root, e.g. <code>https://your-domain.com/public</code></p>
  <p><code>{{TOKEN}}</code> — Session token from <code>POST /auth/login</code> (<code>Authorization: Bearer …</code>, valid 24h)</p>
</section>
<section id="app-flow" class="flow">
  <h2 style="margin-top:0;font-size:1.1rem;">Original mobile app flow</h2>
  <ol>
    <li><strong>Startup</strong> — <code>GET /api/v1/health</code> to verify API reachability</li>
    <li><strong>Login</strong> — <code>POST /api/v1/auth/login</code> → store <code>token</code> (24h)</li>
    <li><strong>Session restore</strong> — <code>GET /api/v1/auth/me</code> → read <code>role</code> / <code>app_area</code> and route UI</li>
    <li><strong>Tenant app</strong> — contracts, payments, maintenance requests, document download</li>
    <li><strong>FM app</strong> — dashboard, work orders, complaints, job cards, technicians</li>
    <li><strong>Employee app</strong> — profile, attendance check-in/out, breaks, leave requests, team attendance (managers)</li>
    <li><strong>Shared</strong> — properties/KPIs, finance reports, optional <code>POST /api/v1/app-log</code> telemetry</li>
  </ol>
</section>
<section id="postman" class="note">
  <strong>Import into Postman:</strong> Click <em>Import → Raw text</em> and paste any cURL block below. Set collection variables <code>BASE_URL</code> and <code>TOKEN</code>.
</section>
<?php foreach ($ordered as $gName => $items): ?>
<h2 class="group-title"><?= escHtml($gName) ?></h2>
<?php foreach ($items as $ep):
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($ep['method'] . '-' . $ep['path']));
    $mClass = strtolower($ep['method']);
?>
<article class="endpoint" id="<?= escHtml($slug) ?>">
  <h3>
    <span class="method <?= escHtml($mClass) ?>"><?= escHtml($ep['method']) ?></span>
    <span class="path"><?= escHtml($ep['path']) ?></span>
  </h3>
  <p class="desc"><?= escHtml($ep['desc']) ?></p>
  <p class="meta"><strong>Auth:</strong> <?= escHtml($ep['auth']) ?></p>
  <h4>cURL — Postman import</h4>
  <pre class="curl" data-copy><?= escHtml($ep['curl']) ?><button type="button" class="copy-btn" onclick="copyPre(this)">Copy</button></pre>
  <h4>Request body (JSON)</h4>
  <pre class="json"><?= jsonPretty($ep['request']) ?></pre>
  <h4>Response example</h4>
  <pre class="json"><?= jsonPretty($ep['response']) ?></pre>
</article>
<?php endforeach; endforeach; ?>
</main>
</div>
<script>
function copyPre(btn) {
  const pre = btn.parentElement;
  const text = pre.textContent.replace('Copy', '').trim();
  navigator.clipboard.writeText(text).then(() => {
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 1500);
  });
}
</script>
</body>
</html>
<?php
$html = ob_get_clean();
$out = __DIR__ . '/mobile-api-reference.html';
file_put_contents($out, $html);
echo "Written {$out} (" . strlen($html) . " bytes, " . count($eps) . " endpoints)\n";
