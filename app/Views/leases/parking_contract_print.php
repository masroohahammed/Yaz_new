<?php
/**
 * Bilingual parking lease — English left, Arabic right (single page).
 * @var array<string,mixed> $d
 */
$rentFmt   = number_format((float) ($d['rent_amount'] ?? 0), 0);
$unitNo    = esc($d['parking_unit_no'] ?? '');
$plate     = esc($d['plate_number'] ?? '');
$vehicleAr = esc($d['vehicle_type_ar'] ?? 'مركبته');
$tenantPo  = trim((string) ($d['tenant_po_box'] ?? '')) !== '' ? esc($d['tenant_po_box']) : '------';
$settings  = $settings ?? [];
$landlordName = esc($d['owner_name_en'] ?? $settings['company_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #fff; color: #111; line-height: 1.55; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; }
  .no-print button, .no-print a { background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; text-decoration: none; cursor: pointer; font-family: 'DM Sans', sans-serif; }
  .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm 14mm; }
  .bilingual { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; align-items: start; }
  .col-en { font-family: 'DM Sans', Arial, sans-serif; font-size: 10.5px; direction: ltr; text-align: left; }
  .col-ar { font-family: 'Cairo', 'Traditional Arabic', sans-serif; font-size: 10.5px; direction: rtl; text-align: right; }
  .doc-title-en { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; margin: 8px 0 4px; text-align: center; }
  .doc-title-ar { font-weight: 700; font-size: 12px; margin: 8px 0 4px; text-align: center; }
  .row-pair { display: contents; }
  .block { margin-bottom: 8px; }
  .clause-title { font-weight: 700; margin: 10px 0 4px; display: block; }
  .highlight { font-weight: 700; }
  ol.en { margin: 4px 0 0; padding-left: 14px; }
  ol.ar { margin: 4px 0 0; padding-right: 14px; }
  ol.en li, ol.ar li { margin-bottom: 3px; }
  .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
  .sig-line { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; font-size: 9.5px; }
  .landlord-line { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; margin: 6px 0 10px; color: #333; }
  @media print {
    .no-print { display: none !important; }
    .page { padding: 8mm 10mm; }
    @page { size: A4 portrait; margin: 8mm; }
  }
</style>
</head>
<body>
<div class="no-print">
  <button type="button" onclick="window.print()">Print</button>
  <?php if (!empty($pdfUrl)): ?>
  <a href="<?= esc($pdfUrl) ?>" target="_blank">Download PDF</a>
  <?php endif; ?>
</div>

<div class="page">
  <?= $this->include('layouts/_doc_letterhead', [
    'settings' => $settings,
    'companyBranding' => $companyBranding ?? null,
    'companyLogoUrl' => $companyLogoUrl ?? null,
    'companyLogoB64' => $companyLogoB64 ?? null,
    'usePdf' => true,
  ]) ?>

  <?php if ($landlordName !== ''): ?>
  <div class="landlord-line">Landlord / Company: <?= $landlordName ?></div>
  <?php endif; ?>

  <div class="bilingual">
    <div class="col-en">
      <div class="doc-title-en">Parking Space Lease Agreement</div>
      <div class="doc-title-en" style="font-size:10px;font-weight:600;margin-bottom:8px">
        Parking Space Under the Property Building – Title Deed No. (<span class="highlight"><?= esc($d['header_title_deed_no'] ?? '') ?></span>)
      </div>
      <div class="block" style="text-align:center;margin-bottom:8px">Date: <span class="highlight"><?= esc($contractDateEn ?? '') ?></span></div>
    </div>
    <div class="col-ar">
      <div class="doc-title-ar">
        عقد ايجار موقف تحت مبنى العقار سند ملكية رقم (<span class="highlight"><?= esc($d['header_title_deed_no'] ?? '') ?></span>)
      </div>
      <div class="block" style="margin-bottom:8px">
        أنه بتاريخ اليوم <span class="highlight"><?= esc($arabicDay ?? '') ?></span> الموافق <span class="highlight"><?= esc($contractDateAr ?? '') ?></span><br>
        تم إبرام هذا العقد بين كل من:
      </div>
    </div>

    <div class="col-en block">
      This Agreement has been entered into by and between:<br><br>
      <strong>First Party / Owner and Lessor:</strong><br>
      <span class="highlight"><?= esc($d['owner_name_en'] ?? '') ?></span>, Commercial Registration No. <span class="highlight"><?= esc($d['owner_cr'] ?? '') ?></span>,
      represented by <span class="highlight"><?= esc($d['rep_company_en'] ?? '') ?></span>, CR <span class="highlight"><?= esc($d['rep_cr'] ?? '') ?></span>,
      POA No. <span class="highlight"><?= esc($d['poa_no'] ?? '') ?></span> dated <span class="highlight"><?= esc($poaDateFmt ?? '') ?></span>.<br>
      Rep: <span class="highlight"><?= esc($d['rep_name_en'] ?? '') ?></span>, <span class="highlight"><?= esc($d['rep_nationality_en'] ?? '') ?></span>, ID <span class="highlight"><?= esc($d['rep_qid'] ?? '') ?></span><br>
      Tel: <span class="highlight"><?= esc($d['landlord_phone'] ?? '') ?></span> · P.O. Box: <span class="highlight"><?= esc($d['landlord_po_box'] ?? '') ?></span> · Email: <span class="highlight"><?= esc($d['landlord_email'] ?? '') ?></span>
    </div>
    <div class="col-ar block">
      <span class="highlight"><?= esc($d['owner_name_ar'] ?? '') ?></span> سجل تجاري (<span class="highlight"><?= esc($d['owner_cr'] ?? '') ?></span>)
      ويمثلها شركة /<span class="highlight"><?= esc($d['rep_company_ar'] ?? '') ?></span> سجل (<span class="highlight"><?= esc($d['rep_cr'] ?? '') ?></span>)
      بموجب الوكالة (<span class="highlight"><?= esc($d['poa_no'] ?? '') ?></span>) بتاريخ (<span class="highlight"><?= esc($poaDateFmt ?? '') ?></span>)<br>
      /<span class="highlight"><?= esc($d['rep_name_ar'] ?? '') ?></span> -<span class="highlight"><?= esc($d['rep_nationality_ar'] ?? '') ?></span>- رقم (<span class="highlight"><?= esc($d['rep_qid'] ?? '') ?></span>)<br>
      هاتف <span class="highlight"><?= esc($d['landlord_phone'] ?? '') ?></span> · ص ب <span class="highlight"><?= esc($d['landlord_po_box'] ?? '') ?></span> · <?= esc($d['landlord_email'] ?? '') ?><br>
      <em>الطرف الأول / المالك والمؤجر</em>
    </div>

    <div class="col-en block">
      <strong>Second Party / Lessee:</strong><br>
      <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span>, ID <span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span><br>
      Tel: <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span> · P.O. Box: <span class="highlight"><?= $tenantPo ?></span>, Doha, Qatar
    </div>
    <div class="col-ar block">
      السيد / <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span> بطاقة (<span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span>)
      هاتف <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span> ص ب <span class="highlight"><?= $tenantPo ?></span><br>
      <em>الطرف الثاني / المستأجر</em>
    </div>

    <div class="col-en block">
      <span class="clause-title">Subject Matter</span>
      Lessor owns parking spaces under Title Deed (<span class="highlight"><?= esc($d['title_deed_no'] ?? '') ?></span>),
      <?= esc($d['property_city'] ?? '') ?>, Bldg (<span class="highlight"><?= esc($d['building_no'] ?? '') ?></span>),
      Zone (<span class="highlight"><?= esc($d['zone_no'] ?? '') ?></span>), Street (<span class="highlight"><?= esc($d['street_no'] ?? '') ?></span>).
      Lessee wishes to lease Parking No. (<span class="highlight"><?= $unitNo ?></span>) for
      <span class="highlight"><?= esc($vehicleEn ?? '') ?></span>, Plate (<span class="highlight"><?= $plate ?></span>).
    </div>
    <div class="col-ar block">
      <span class="clause-title">موضوع العقد</span>
      يمتلك الطرف الأول مواقف تحت سند (<span class="highlight"><?= esc($d['title_deed_no'] ?? '') ?></span>)
      <?= esc($d['property_city'] ?? '') ?> مبنى (<span class="highlight"><?= esc($d['building_no'] ?? '') ?></span>)
      منطقة (<span class="highlight"><?= esc($d['zone_no'] ?? '') ?></span>) شارع (<span class="highlight"><?= esc($d['street_no'] ?? '') ?></span>).
      يرغب المستأجر باستئجار الموقف (<span class="highlight"><?= $unitNo ?></span>) لـ<span class="highlight"><?= $vehicleAr ?></span> لوحة (<span class="highlight"><?= $plate ?></span>).
    </div>

    <div class="col-en block">
      <span class="clause-title">Article One: Term and Rent</span>
      Term: <span class="highlight"><?= esc($d['duration_en'] ?? '') ?></span>,
      from <span class="highlight"><?= esc($startDateEn ?? '') ?></span> to <span class="highlight"><?= esc($endDateEn ?? '') ?></span>.<br>
      Monthly rent QAR <span class="highlight"><?= $rentFmt ?></span> (<span class="highlight"><?= esc($d['rent_words_en'] ?? '') ?></span>),
      payable in advance via <span class="highlight"><?= (int) ($d['cheque_count'] ?? 0) ?></span> cheques to
      <span class="highlight"><?= esc($d['collector_company'] ?? '') ?></span> <?= esc($d['collector_account'] ?? '') ?>.
    </div>
    <div class="col-ar block">
      <span class="clause-title">البند الأول: المدة والأجرة</span>
      مدة <span class="highlight"><?= esc($d['duration_ar'] ?? '') ?></span>
      من <span class="highlight"><?= esc($startDateAr ?? '') ?></span> إلى <span class="highlight"><?= esc($endDateAr ?? '') ?></span>.<br>
      الأجرة الشهرية <span class="highlight"><?= $rentFmt ?></span> ريال قطري،
      <span class="highlight"><?= (int) ($d['cheque_count'] ?? 0) ?></span> شيكاً لحساب
      <span class="highlight"><?= esc($d['collector_company'] ?? '') ?></span> <?= esc($d['collector_account'] ?? '') ?>.
    </div>

    <div class="col-en block">
      <span class="clause-title">Article Two: Lessor Obligations</span>
      Hand over Parking No. (<span class="highlight"><?= $unitNo ?></span>) for vehicle Reg. <span class="highlight"><?= $plate ?></span>.
    </div>
    <div class="col-ar block">
      <span class="clause-title">البند الثاني: التزامات المالك</span>
      تسليم الموقف (<span class="highlight"><?= $unitNo ?></span>) للمركبة (<span class="highlight"><?= $plate ?></span>).
    </div>

    <div class="col-en block">
      <span class="clause-title">Article Three: Lessee Obligations</span>
      <ol class="en">
        <li>Pay rent on due dates.</li>
        <li>Not sublease the parking space.</li>
        <li>No mechanical work; keep the space clean.</li>
        <li>Use only for the specified vehicle unless approved in writing.</li>
        <li>Return the space in the same condition.</li>
      </ol>
    </div>
    <div class="col-ar block">
      <span class="clause-title">البند الثالث: التزامات المستأجر</span>
      <ol class="ar">
        <li>تسديد الأجرة في موعدها.</li>
        <li>عدم تأجير الموقف من الباطن.</li>
        <li>عدم أعمال ميكانيكية والمحافظة على النظافة.</li>
        <li>تخصيص الموقف للمركبة المذكورة فقط.</li>
        <li>إعادة الموقف بالحالة الأصلية.</li>
      </ol>
    </div>

    <div class="col-en block">
      <span class="clause-title">Article Four: General Terms</span>
      Late payment or breach terminates the Agreement; Lessor may remove the vehicle without legal proceedings.
      Governed by Qatari law; Qatari courts have jurisdiction. Executed in two original copies.
    </div>
    <div class="col-ar block">
      <span class="clause-title">البند الرابع: الشروط العامة</span>
      التأخر أو المخالفة يلغي العقد تلقائياً ويحق للمالك إخلاء المركبة.
      يخضع لقوانين قطر وتختص المحاكم القطرية. أُبرم من نسختين أصليتين.
    </div>
  </div>

  <div class="signatures">
    <div class="col-en" style="font-size:10px">
      <strong>First Party / Owner</strong><br><?= esc($d['rep_company_en'] ?? '') ?><br><?= esc($d['rep_name_en'] ?? '') ?>
      <div class="sig-line">Signature: ______________________</div>
    </div>
    <div class="col-ar" style="font-size:10px;direction:rtl;text-align:right;font-family:'Cairo',sans-serif">
      <strong>الطرف الأول / المالك</strong><br><?= esc($d['rep_company_ar'] ?? '') ?><br><?= esc($d['rep_name_ar'] ?? '') ?>
      <div class="sig-line">التوقيع:</div>
    </div>
    <div class="col-en" style="font-size:10px">
      <strong>Second Party / Lessee</strong><br><?= esc(strtoupper((string) ($d['tenant_name'] ?? ''))) ?><br>ID: <?= esc($d['tenant_qid'] ?? '') ?>
      <div class="sig-line">Signature: ______________________</div>
    </div>
    <div class="col-ar" style="font-size:10px;direction:rtl;text-align:right;font-family:'Cairo',sans-serif">
      <strong>الطرف الثاني / المستأجر</strong><br><?= esc($d['tenant_name'] ?? '') ?><br>رقم: <?= esc($d['tenant_qid'] ?? '') ?>
      <div class="sig-line">التوقيع:</div>
    </div>
  </div>

  <?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'companyBranding' => $companyBranding ?? null, 'plain' => true]) ?>
</div>
</body>
</html>
