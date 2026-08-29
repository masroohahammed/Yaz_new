<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= esc($po['po_number']) ?> — <?= esc($settings['company_name']??'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
<?php $p=$settings['primary_color']??'#76002b';$s=$settings['secondary_color']??'#c7ba9a';function _pr($h){$h=ltrim($h,'#');if(strlen($h)==3)$h=$h[0].$h[0].$h[1].$h[1].$h[2].$h[2];return hexdec(substr($h,0,2)).','.hexdec(substr($h,2,2)).','.hexdec(substr($h,4,2));}?>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',Arial,sans-serif;font-size:13px;color:#1a2332}
.page{max-width:800px;margin:0 auto}
.header{display:flex;justify-content:space-between;padding:22px 28px 14px;border-bottom:3px solid <?= $p ?>}
.brand{font-size:1.25rem;font-weight:700;color:<?= $p ?>}
.tagline{font-size:.6rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:2px}
.meta{display:grid;grid-template-columns:1fr 1fr;padding:16px 28px;background:#fafafa;border-bottom:1px solid #e8edf3;gap:20px}
.ml{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:3px}
.mv{font-size:.82rem;font-weight:600}
.mv-sub{font-size:.72rem;color:#6b7a8d;margin-top:1px}
.section{padding:14px 28px}
.sec-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $p ?>;margin-bottom:8px;padding-bottom:4px;border-bottom:2px solid rgba(<?= _pr($p) ?>,.12)}
table{width:100%;border-collapse:collapse}
thead th{background:<?= $p ?>;color:#fff;font-size:.65rem;font-weight:700;text-transform:uppercase;padding:8px 14px;text-align:left}
td{padding:8px 14px;border-bottom:1px solid #f0f4f8;font-size:.8rem}
.sig-row{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:20px 28px}
.sig-block{padding-top:50px;border-top:1.5px solid #bbb;text-align:center;font-size:.72rem;color:#6b7a8d}
.footer{padding:10px 28px;border-top:2px solid <?= $p ?>;background:rgba(<?= _pr($p) ?>,.05);display:flex;justify-content:space-between}
.fbrand{font-weight:700;color:<?= $p ?>;font-size:.75rem}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div>
      <?php if($companyLogoB64): ?><img src="<?= esc($companyLogoB64) ?>" style="max-height:50px;max-width:160px;object-fit:contain;display:block;margin-bottom:3px"><?php else: ?><div class="brand"><?= esc($settings['company_name']??'FM ERP') ?></div><?php endif; ?>
      <div class="tagline"><?= esc($settings['company_tagline']??'Facility Management ERP') ?></div>
    </div>
    <div style="text-align:right">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $s ?>;margin-bottom:3px">PURCHASE ORDER</div>
      <div style="font-size:1.2rem;font-weight:700;color:<?= $p ?>"><?= esc($po['po_number']) ?></div>
      <span style="font-size:.65rem;font-weight:700;padding:2px 10px;border-radius:12px;background:rgba(<?= _pr($p) ?>,.1);color:<?= $p ?>"><?= ucfirst($po['status']) ?></span>
    </div>
  </div>
  <div class="meta">
    <div>
      <div class="ml">Vendor</div>
      <div class="mv"><?= esc($po['vendor_name']??'—') ?></div>
      <?php if(!empty($po['vendor_address'])): ?><div class="mv-sub"><?= esc($po['vendor_address']) ?></div><?php endif; ?>
      <?php if(!empty($po['vendor_email'])): ?><div class="mv-sub"><?= esc($po['vendor_email']) ?></div><?php endif; ?>
    </div>
    <div>
      <div class="ml">Order Details</div>
      <div class="mv-sub">PO Date: <?= date('d M Y',strtotime($po['created_at'])) ?></div>
      <div class="mv-sub">Delivery: <?= date('d M Y',strtotime($po['delivery_date'])) ?></div>
      <div class="mv-sub">Created by: <?= esc($po['created_by_name']??'—') ?></div>
    </div>
  </div>
  <?php if(!empty($lineItems)): ?>
  <div class="section">
    <div class="sec-title">Order Items</div>
    <table>
      <thead><tr><th>Item</th><th>Unit</th><th>Qty</th></tr></thead>
      <tbody><?php foreach($lineItems as $li): ?><tr><td><?= esc($li['item_name']??'—') ?></td><td><?= esc($li['unit']??'') ?></td><td><?= $li['quantity'] ?></td></tr><?php endforeach; ?></tbody>
    </table>
  </div>
  <?php endif; ?>
  <div class="section" style="padding-top:0">
    <table style="max-width:300px;margin-left:auto">
      <tr><td style="text-align:right;padding:6px 14px;font-size:.8rem;border:none">Total Amount</td><td style="font-size:1rem;font-weight:700;color:<?= $p ?>;padding:6px 14px;border:none"><?= $currency ?> <?= number_format($po['total_amount'],2) ?></td></tr>
    </table>
  </div>
  <?php if($po['notes']): ?><div class="section" style="padding-top:0"><div class="sec-title">Notes</div><div style="font-size:.82rem;line-height:1.5"><?= esc($po['notes']) ?></div></div><?php endif; ?>
  <div class="sig-row">
    <div class="sig-block"><div>Prepared By</div><div>Signature &amp; Date</div></div>
    <div class="sig-block"><div>Approved By</div><div>Signature &amp; Date</div></div>
    <div class="sig-block"><div>Vendor Acknowledgement</div><div>Signature &amp; Date</div></div>
  </div>
  <div class="footer">
    <div class="fbrand"><?= esc($settings['company_name']??'FM ERP') ?></div>
    <div style="font-size:.68rem;color:#9ca3af">Printed: <?= date('d M Y H:i') ?> | <?= esc($po['po_number']) ?></div>
  </div>
</div>
<script>window.onload=()=>window.print()</script>
</body>
</html>
