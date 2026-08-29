<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= esc($inv['invoice_number']) ?> — <?= esc($settings['company_name']??'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
<?php
$p=$settings['primary_color']??'#76002b';
$s=$settings['secondary_color']??'#c7ba9a';
function _ir($h){$h=ltrim($h,'#');if(strlen($h)==3)$h=$h[0].$h[0].$h[1].$h[1].$h[2].$h[2];return hexdec(substr($h,0,2)).','.hexdec(substr($h,2,2)).','.hexdec(substr($h,4,2));}
?>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',Arial,sans-serif;font-size:13px;color:#1a2332;background:#fff}
.page{max-width:800px;margin:0 auto;min-height:100vh;display:flex;flex-direction:column}
.header{display:flex;justify-content:space-between;align-items:flex-start;padding:24px 30px 16px;border-bottom:3px solid <?= $p ?>}
.brand-text{font-size:1.3rem;font-weight:700;color:<?= $p ?>}
.tagline{font-size:.62rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:3px}
.contact{font-size:.7rem;color:#6b7a8d;line-height:1.45;margin-top:6px}
.contact div{margin-top:2px}
.doc-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $s ?>;margin-bottom:4px;text-align:right}
.doc-num{font-size:1.2rem;font-weight:700;color:<?= $p ?>;text-align:right}
.bill-row{display:grid;grid-template-columns:1fr 1fr;padding:16px 30px;border-bottom:1px solid #e8edf3;gap:20px}
.bl{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:4px}
.bv{font-size:.83rem;font-weight:600}
.bv-sub{font-size:.75rem;color:#6b7a8d;margin-top:2px}
table{width:100%;border-collapse:collapse}
thead th{background:<?= $p ?>;color:#fff;font-size:.65rem;font-weight:700;text-transform:uppercase;padding:9px 16px;text-align:left}
thead th:last-child{text-align:right}
td{padding:10px 16px;border-bottom:1px solid #f0f4f8;font-size:.82rem}
td:last-child{text-align:right;font-weight:600}
tfoot td{font-weight:700;background:rgba(<?= _ir($p) ?>,.06)}
.tfoot-total td{font-size:1.05rem;color:<?= $p ?>;border-top:2px solid <?= $p ?>}
.notes-sec{padding:14px 30px;background:#fafafa;border-bottom:1px solid #e8edf3}
.nl{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:4px}
.nv{font-size:.82rem;color:#374151}
.footer{margin-top:auto;padding:10px 30px;border-top:2px solid <?= $p ?>;background:rgba(<?= _ir($p) ?>,.05);display:flex;justify-content:space-between}
.fbrand{font-weight:700;color:<?= $p ?>;font-size:.75rem}
.finfo{font-size:.68rem;color:#9ca3af}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div>
      <?php
      $logoSrc = $companyLogoB64 ?? $companyLogoUrl ?? '';
      if ($logoSrc !== ''): ?><img src="<?= esc($logoSrc) ?>" style="max-height:56px;max-width:170px;object-fit:contain;display:block;margin-bottom:4px"><?php else: ?><div class="brand-text"><?= esc($settings['company_name']??'FM ERP') ?></div><?php endif; ?>
      <div class="tagline"><?= esc($settings['company_tagline']??'Facility Management ERP') ?></div>
      <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'class' => 'contact', 'plain' => true]) ?>
    </div>
    <div>
      <div class="doc-label">INVOICE</div>
      <div class="doc-num"><?= esc($inv['invoice_number']) ?></div>
      <div style="font-size:.7rem;color:#6b7a8d;text-align:right;margin-top:4px"><?= ucfirst($inv['status']) ?></div>
    </div>
  </div>

  <div class="bill-row">
    <div>
      <div class="bl">Bill To</div>
      <div class="bv"><?= esc($inv['facility_name']??'—') ?></div>
      <?php if(!empty($inv['facility_address'])): ?><div class="bv-sub"><?= esc($inv['facility_address']) ?></div><?php endif; ?>
      <?php if(!empty($inv['contract_number'])): ?><div class="bv-sub">Contract: <?= esc($inv['contract_number']) ?></div><?php endif; ?>
    </div>
    <div style="text-align:right">
      <div class="bl" style="text-align:right">Invoice Details</div>
      <div class="bv-sub">Issue: <?= date('d M Y',strtotime($inv['issue_date'])) ?></div>
      <div class="bv-sub <?= $inv['status']==='overdue'?'':''; ?>">Due: <span <?= $inv['status']==='overdue'?'style="color:red;font-weight:700"':'' ?>><?= date('d M Y',strtotime($inv['due_date'])) ?></span></div>
      <?php if($inv['paid_at']): ?><div class="bv-sub" style="color:green">Paid: <?= date('d M Y',strtotime($inv['paid_at'])) ?></div><?php endif; ?>
    </div>
  </div>

  <table>
    <thead><tr><th>Description</th><th>Qty</th><th style="text-align:right">Amount</th></tr></thead>
    <tbody>
      <tr>
        <td>Facility Management Services — <?= esc($inv['facility_name']??'') ?><?php if(!empty($inv['invoice_type'])): ?><br><span style="font-size:.72rem;color:#6b7a8d"><?= ucfirst(str_replace('_',' ',$inv['invoice_type'])) ?></span><?php endif; ?></td>
        <td>1</td>
        <td><?= $inv['currency']??$currency ?> <?= number_format($inv['subtotal'],2) ?></td>
      </tr>
    </tbody>
    <tfoot>
      <tr><td colspan="2" style="text-align:right;padding:8px 16px;font-size:.8rem">Subtotal</td><td><?= $inv['currency']??$currency ?> <?= number_format($inv['subtotal'],2) ?></td></tr>
      <?php if(($inv['vat_amount']??0)>0): ?>
      <tr><td colspan="2" style="text-align:right;padding:8px 16px;font-size:.8rem">VAT (<?= $inv['vat_rate']??0 ?>%)</td><td><?= $inv['currency']??$currency ?> <?= number_format($inv['vat_amount'],2) ?></td></tr>
      <?php endif; ?>
      <tr class="tfoot-total"><td colspan="2" style="text-align:right;padding:12px 16px">TOTAL</td><td style="font-size:1.1rem"><?= $inv['currency']??$currency ?> <?= number_format($inv['total'],2) ?></td></tr>
    </tfoot>
  </table>

  <?php if(!empty($inv['notes'])): ?>
  <div class="notes-sec"><div class="nl">Notes</div><div class="nv"><?= esc($inv['notes']) ?></div></div>
  <?php endif; ?>

  <div class="footer">
    <div>
      <div class="fbrand"><?= esc($settings['company_name']??'FM ERP') ?></div>
      <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'style' => 'font-size:.65rem;color:#9ca3af;margin-top:3px;line-height:1.35', 'plain' => true]) ?>
    </div>
    <div class="finfo">Generated: <?= date('d M Y H:i') ?> | <?= esc($inv['invoice_number']) ?></div>
  </div>
</div>
<script>window.onload=()=>window.print()</script>
</body>
</html>
