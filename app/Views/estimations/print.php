<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= esc($est['est_number']) ?> — <?= esc($settings['company_name']??'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
<?php $p=$settings['primary_color']??'#76002b';$s=$settings['secondary_color']??'#c7ba9a';function _er($h){$h=ltrim($h,'#');if(strlen($h)==3)$h=$h[0].$h[0].$h[1].$h[1].$h[2].$h[2];return hexdec(substr($h,0,2)).','.hexdec(substr($h,2,2)).','.hexdec(substr($h,4,2));}?>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',Arial,sans-serif;font-size:13px;color:#1a2332}
.page{max-width:800px;margin:0 auto}
.header{display:flex;justify-content:space-between;padding:22px 30px 14px;border-bottom:3px solid <?= $p ?>}
.tagline{font-size:.62rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:3px}
.meta{display:grid;grid-template-columns:repeat(4,1fr);padding:14px 30px;background:#fafafa;border-bottom:1px solid #e8edf3}
.ml{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:2px}
.mv{font-size:.82rem;font-weight:600}
.section{padding:14px 30px}
.sec-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $p ?>;margin-bottom:8px;padding-bottom:4px;border-bottom:2px solid rgba(<?= _er($p) ?>,.12)}
.desc{background:#fafafa;border-radius:6px;padding:12px;font-size:.82rem;line-height:1.65;border-left:3px solid <?= $s ?>}
table{width:100%;border-collapse:collapse}
thead th{background:<?= $p ?>;color:#fff;font-size:.65rem;font-weight:700;text-transform:uppercase;padding:8px 14px;text-align:left}
td{padding:8px 14px;border-bottom:1px solid #f0f4f8;font-size:.8rem}
td:last-child{text-align:right;font-weight:600}
.totals-box{padding:14px 30px;display:flex;justify-content:flex-end}
.totals{width:260px}
.totals-row{display:flex;justify-content:space-between;font-size:.82rem;padding:4px 0;border-bottom:1px solid #f0f4f8}
.totals-total{display:flex;justify-content:space-between;font-size:1rem;font-weight:700;padding:8px 0;color:<?= $p ?>}
.footer{padding:10px 30px;border-top:2px solid <?= $p ?>;background:rgba(<?= _er($p) ?>,.05);display:flex;justify-content:space-between;margin-top:30px}
.footer-brand{font-weight:700;color:<?= $p ?>;font-size:.75rem}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div>
      <?php if(!empty($companyLogoB64)): ?><img src="<?= esc($companyLogoB64) ?>" style="max-height:52px;max-width:160px;object-fit:contain;display:block;margin-bottom:4px"><?php else: ?><div style="font-size:1.3rem;font-weight:700;color:<?= $p ?>"><?= esc($settings['company_name']??'FM ERP') ?></div><?php endif; ?>
      <div class="tagline"><?= esc($settings['company_tagline']??'Facility Management ERP') ?></div>
    </div>
    <div style="text-align:right">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $s ?>">QUOTATION / ESTIMATION</div>
      <div style="font-size:1.2rem;font-weight:700;color:<?= $p ?>"><?= esc($est['est_number']) ?></div>
      <div style="font-size:.72rem;color:#6b7a8d;margin-top:3px">Revision v<?= $est['revision'] ?></div>
    </div>
  </div>
  <div class="meta">
    <div style="padding:8px 10px"><div class="ml">Facility</div><div class="mv"><?= esc($est['facility_name']??'—') ?></div></div>
    <div style="padding:8px 10px"><div class="ml">Prepared By</div><div class="mv"><?= esc($est['created_by_name']??'—') ?></div></div>
    <div style="padding:8px 10px"><div class="ml">Date</div><div class="mv"><?= date('d M Y',strtotime($est['created_at'])) ?></div></div>
    <div style="padding:8px 10px"><div class="ml">Valid Until</div><div class="mv"><?= date('d M Y',strtotime($est['created_at'].' +30 days')) ?></div></div>
  </div>
  <?php if($est['description']): ?>
  <div class="section"><div class="sec-title">Scope of Work</div><div class="desc"><?= nl2br(esc($est['description'])) ?></div></div>
  <?php endif; ?>
  <?php if(!empty($items)): ?>
  <div class="section" style="padding-top:0">
    <div class="sec-title">Items &amp; Services</div>
    <table>
      <thead><tr><th>Item / Service</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th style="text-align:right">Amount</th></tr></thead>
      <tbody>
      <?php foreach($items as $item): ?>
      <tr>
        <td><?= esc($item['item_name'] ?? $item['description']) ?></td>
        <td><?= esc($item['description'] ?? '') ?></td>
        <td><?= $item['quantity'] ?></td>
        <td><?= esc($item['unit'] ?? 'unit') ?></td>
        <td><?= $currency ?> <?= number_format($item['unit_price'] ?? 0,2) ?></td>
        <td><?= $currency ?> <?= number_format($item['line_total'] ?? 0,2) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <div class="totals-box">
    <div class="totals">
      <div class="totals-row"><span>Subtotal</span><span><?= $currency ?> <?= number_format($est['selling_subtotal'] ?? $est['subtotal'],2) ?></span></div>
      <?php if($est['vat_amount']>0): ?><div class="totals-row"><span>VAT (<?= $est['vat_rate'] ?>%)</span><span><?= $currency ?> <?= number_format($est['vat_amount'],2) ?></span></div><?php endif; ?>
      <div class="totals-total"><span>TOTAL</span><span><?= $currency ?> <?= number_format($est['total'],2) ?></span></div>
    </div>
  </div>
  <?php if(!empty($clientView)): ?>
  <div class="section" style="padding-top:0"><p class="small text-muted mb-0"><em>Internal cost, profit, and margin data are not shown on client documents.</em></p></div>
  <?php endif; ?>
  <div class="footer">
    <div class="footer-brand"><?= esc($settings['company_name']??'FM ERP') ?></div>
    <div style="font-size:.68rem;color:#9ca3af">Printed: <?= date('d M Y H:i') ?> | <?= esc($est['est_number']) ?></div>
  </div>
  <?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'plain' => true]) ?>
</div>
<script>window.onload=()=>window.print()</script>
</body>
</html>
