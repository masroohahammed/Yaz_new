<!DOCTYPE html><html><head><title>Inspection Report</title><style>body{font-family:sans-serif;padding:24px} table{width:100%;border-collapse:collapse} td,th{border:1px solid #ccc;padding:6px;font-size:12px}</style></head>
<body>
<h2>Inspection Report</h2>
<p>Unit <?= esc($inspection['unit_number']) ?> · <?= esc($inspection['property_name']) ?> · <?= esc(str_replace('_',' ',$inspection['type'])) ?></p>
<p>Date: <?= esc($inspection['inspection_date'] ?? $inspection['created_at']) ?> · Inspector: <?= esc($inspection['inspector_name'] ?? '') ?></p>
<p>Overall: <?= esc($inspection['overall_condition'] ?? '') ?></p>
<?php if (! empty($inspection['notes'])): ?><p><?= esc($inspection['notes']) ?></p><?php endif; ?>
<button onclick="window.print()">Print</button>
</body></html>
