<!DOCTYPE html>
<html><head><meta charset="utf-8"><title><?= esc($title) ?></title>
<style>body{font-family:Arial,sans-serif;padding:40px;line-height:1.5} h1{font-size:18px}</style>
</head><body>
<h1>Lease Agreement — <?= esc($contract['contract_number'] ?? '') ?></h1>
<p><strong>Tenant:</strong> <?= esc($contract['tenant_name'] ?? '') ?> |
<strong>Property:</strong> <?= esc($contract['facility_name'] ?? '') ?> Unit <?= esc($contract['unit_number'] ?? '') ?></p>
<p><strong>Term:</strong> <?= esc($contract['start_date'] ?? '') ?> to <?= esc($contract['end_date'] ?? '') ?> |
<strong>Rent:</strong> <?= esc($contract['rent_amount'] ?? '') ?></p>
<hr>
<div><?= $body ?></div>
<?php if (!empty($bodyAr)): ?><hr><div dir="rtl"><?= $bodyAr ?></div><?php endif; ?>
</body></html>
