<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lease Contract — <?= esc($contract['contract_number']) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
  .page { max-width: 210mm; margin: 0 auto; padding: 15mm; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 12px; }
  .header .logo img { max-height: 60px; }
  .header .co-info { text-align: right; font-size: 11px; line-height: 1.6; }
  h1.contract-title { font-size: 18px; text-align: center; margin: 16px 0 4px; text-transform: uppercase; letter-spacing: 1px; }
  .contract-sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 20px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 20px; }
  .info-row { display: flex; gap: 6px; font-size: 11px; padding: 3px 0; border-bottom: 1px dotted #ddd; }
  .info-row label { font-weight: bold; min-width: 120px; color: #555; }
  .section-title { font-size: 13px; font-weight: bold; background: #f4f4f4; padding: 5px 8px; margin: 18px 0 8px; border-left: 3px solid #333; }
  .content-en { margin-bottom: 24px; line-height: 1.8; font-size: 12px; }
  .content-ar { direction: rtl; text-align: right; line-height: 1.8; font-size: 13px; font-family: 'Arial', sans-serif; margin-bottom: 24px; }
  .divider { border: none; border-top: 1px dashed #bbb; margin: 20px 0; }
  .signature-block { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; }
  .signature-party { text-align: center; }
  .signature-line { border-top: 1px solid #333; margin: 40px 0 6px; }
  .signature-label { font-size: 10px; color: #666; }
  .print-btn { position: fixed; top: 15px; right: 15px; background: #333; color: #fff; border: none; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-size: 12px; }
  @media print {
    .print-btn { display: none; }
    body { font-size: 11px; }
    .page { padding: 10mm; }
  }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">&#128438; Print</button>
<div class="page">
  <div class="header">
    <div class="logo">
      <?php if (!empty($companyLogoUrl)): ?>
        <img src="<?= esc($companyLogoUrl) ?>" alt="Logo">
      <?php endif; ?>
    </div>
    <div class="co-info">
      <?= esc($settings['company_name'] ?? '') ?><br>
      <?= esc($settings['company_address'] ?? '') ?><br>
      <?= esc($settings['company_phone'] ?? '') ?>
    </div>
  </div>

  <h1 class="contract-title">Lease Contract / عقد إيجار</h1>
  <div class="contract-sub"><?= esc($contract['contract_number']) ?></div>

  <div class="info-grid">
    <div>
      <div class="info-row"><label>Tenant / المستأجر:</label> <span><?= esc($contract['tenant_name'] ?? '—') ?></span></div>
      <div class="info-row"><label>Property / العقار:</label> <span><?= esc($contract['facility_name'] ?? '—') ?></span></div>
      <div class="info-row"><label>Unit / الوحدة:</label> <span><?= esc($contract['unit_number'] ?? '—') ?></span></div>
    </div>
    <div>
      <div class="info-row"><label>Start / البداية:</label> <span><?= esc($contract['start_date']) ?></span></div>
      <div class="info-row"><label>End / النهاية:</label> <span><?= esc($contract['end_date']) ?></span></div>
      <div class="info-row"><label>Rent / الإيجار:</label> <span><?= number_format((float)$contract['rent_amount'],2) ?> <?= esc($currency) ?></span></div>
      <div class="info-row"><label>Frequency / الدورية:</label> <span><?= esc($contract['payment_frequency'] ?? '—') ?></span></div>
    </div>
  </div>

  <?php if (!empty($templateEn)): ?>
  <div class="section-title">Contract Terms (English)</div>
  <div class="content-en"><?= $templateEn ?></div>
  <?php endif; ?>

  <?php if (!empty($templateAr)): ?>
  <hr class="divider">
  <div class="section-title">بنود العقد (عربي)</div>
  <div class="content-ar"><?= $templateAr ?></div>
  <?php endif; ?>

  <?php if (!empty($contract['contract_terms'])): ?>
  <div class="section-title">Additional Terms / شروط إضافية</div>
  <div class="content-en"><?= nl2br(esc($contract['contract_terms'])) ?></div>
  <?php endif; ?>

  <div class="signature-block">
    <div class="signature-party">
      <div class="signature-line"></div>
      <strong>Landlord / المالك</strong>
      <div class="signature-label">Name / Date</div>
    </div>
    <div class="signature-party">
      <div class="signature-line"></div>
      <strong>Tenant / المستأجر</strong>
      <div class="signature-label"><?= esc($contract['tenant_name'] ?? '') ?></div>
    </div>
  </div>

  <div style="text-align:center;margin-top:30px;font-size:10px;color:#999;">
    Generated: <?= date('Y-m-d H:i') ?> — <?= esc($settings['company_name'] ?? '') ?>
  </div>
</div>
</body>
</html>
