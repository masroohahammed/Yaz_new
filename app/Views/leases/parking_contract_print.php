<?php
/**
 * Official Al Yazwa parking lease — Arabic page then English page.
 * @var array<string,mixed> $d
 */
$rentFmt   = number_format((float) ($d['rent_amount'] ?? 0), 0);
$unitNo    = esc($d['parking_unit_no'] ?? '');
$plate     = esc($d['plate_number'] ?? '');
$vehicleAr = esc($d['vehicle_type_ar'] ?? 'مركبته');
$tenantPo  = trim((string) ($d['tenant_po_box'] ?? '')) !== '' ? esc($d['tenant_po_box']) : '------';
$settings  = $settings ?? [];
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, "Traditional Arabic", sans-serif; font-size: 12px; color: #111; margin: 0; background: #fff; line-height: 1.65; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; }
  .no-print button, .no-print a { background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; text-decoration: none; cursor: pointer; }
  .page { max-width: 210mm; margin: 0 auto; padding: 12mm 14mm; page-break-after: always; }
  .page:last-child { page-break-after: auto; }
  .doc-title { text-align: center; font-weight: 700; margin: 10px 0 14px; }
  .doc-title.ar { direction: rtl; font-size: 14px; }
  .doc-title.en { font-size: 15px; text-transform: uppercase; letter-spacing: .4px; }
  .block { margin-bottom: 12px; }
  .block.ar { direction: rtl; text-align: right; font-size: 13px; }
  .block.en { font-size: 11.5px; text-align: justify; }
  .clause-title { font-weight: 700; margin: 14px 0 6px; }
  .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 28px; }
  .sig { font-size: 11px; }
  .sig-line { border-top: 1px solid #333; margin-top: 42px; padding-top: 6px; }
  .highlight { font-weight: 700; }
  ol.ar { margin: 0; padding-right: 18px; }
  ol.en { margin: 0; padding-left: 18px; }
  @media print { .no-print { display: none !important; } .page { padding: 10mm; } }
</style>
</head>
<body>
<div class="no-print">
  <button type="button" onclick="window.print()">Print</button>
  <?php if (!empty($pdfUrl)): ?>
  <a href="<?= esc($pdfUrl) ?>" target="_blank">Download PDF</a>
  <?php endif; ?>
</div>

<!-- Arabic document -->
<div class="page">
  <?= $this->include('layouts/_doc_letterhead', ['settings' => $settings, 'companyLogoUrl' => $companyLogoUrl ?? null, 'companyLogoB64' => $companyLogoB64 ?? null, 'usePdf' => true]) ?>

  <div class="doc-title ar">
    عقد ايجار موقف تحت مبنى العقار سند ملكية رقم (<span class="highlight"><?= esc($d['header_title_deed_no'] ?? '') ?></span>)
  </div>

  <div class="block ar">
    أنه بتاريخ اليوم <span class="highlight"><?= esc($arabicDay ?? '') ?></span> الموافق <span class="highlight"><?= esc($contractDateAr ?? '') ?></span><br>
    تم إبرام هذا العقد بين كل من:
  </div>

  <div class="block ar">
    <span class="highlight"><?= esc($d['owner_name_ar'] ?? '') ?></span> سجل تجاري رقم (<span class="highlight"><?= esc($d['owner_cr'] ?? '') ?></span>)
    ويمثلها في التوقيع على هذا العقد شركة /<span class="highlight"><?= esc($d['rep_company_ar'] ?? '') ?></span>
    سجل تجاري رقم (<span class="highlight"><?= esc($d['rep_cr'] ?? '') ?></span>)
    بموجب الوكالة رقم (<span class="highlight"><?= esc($d['poa_no'] ?? '') ?></span>)
    الصادرة في تاريخ (<span class="highlight"><?= esc($poaDateFmt ?? '') ?></span>)
    ويوقع عنها /<span class="highlight"><?= esc($d['rep_name_ar'] ?? '') ?></span>
    -<span class="highlight"><?= esc($d['rep_nationality_ar'] ?? '') ?></span> –
    رقم شخصي (<span class="highlight"><?= esc($d['rep_qid'] ?? '') ?></span>)
    وعنوانها: هاتف رقم <span class="highlight"><?= esc($d['landlord_phone'] ?? '') ?></span>
    ص ب رقم <span class="highlight"><?= esc($d['landlord_po_box'] ?? '') ?></span>، الدوحة، قطر،
    بريد الكتروني <span class="highlight"><?= esc($d['landlord_email'] ?? '') ?></span><br>
    ويعرف فيما بعد ولأغراض هذا العقد بالطرف الأول /المالك والمؤجر
  </div>

  <div class="block ar">
    السيد / <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span>
    بطاقة شخصية رقم <span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span>
    هاتف رقم <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span>
    ص ب <span class="highlight"><?= $tenantPo ?></span> الدوحة /قطر<br>
    ويعرف فيما بعد ولأغراض هذا العقد بالطرف الثاني /المستأجر
  </div>

  <div class="block ar">
    <span class="clause-title">موضوع العقد وتمهيده:</span>
    يمتلك الطرف الأول المؤجر مجموعة من المواقف المرقمة تحت مبنى عقاره سند التمليك
    (<span class="highlight"><?= esc($d['title_deed_no'] ?? '') ?></span>)
    الكائن بمدينة <span class="highlight"><?= esc($d['property_city'] ?? '') ?></span>
    مبنى رقم (<span class="highlight"><?= esc($d['building_no'] ?? '') ?></span>)
    المنطقة رقم (<span class="highlight"><?= esc($d['zone_no'] ?? '') ?></span>)
    الشارع رقم (<span class="highlight"><?= esc($d['street_no'] ?? '') ?></span>)
    ويرغب الطرف الثاني / المستأجر في استئجار الموقف رقم <span class="highlight">{<?= $unitNo ?>}</span>
    لاستخدامه موقفا لـ<span class="highlight"><?= $vehicleAr ?></span> رقم اللوحة <span class="highlight">{<?= $plate ?>}</span>
  </div>

  <div class="block ar">
    <span class="clause-title">البند الأول: مدة العقد والقيمة الايجارية:</span>
    مدة هذا العقد <span class="highlight"><?= esc($d['duration_ar'] ?? '') ?></span>
    تبدأ من <span class="highlight"><?= esc($startDateAr ?? '') ?></span>
    وتنتهي في <span class="highlight"><?= esc($endDateAr ?? '') ?></span>
    قابل للتجديد بالشروط التي يوافق عليها الطرفين في حينه،
    وقيمة الأجرة الشهرية للموقف المستأجر مبلغ <span class="highlight"><?= $rentFmt ?></span> ريال قطري
    يسدده الطرف الثاني للطرف الأول مقدما بموجب عدد <span class="highlight"><?= (int) ($d['cheque_count'] ?? 0) ?></span> اجلا
    يتم تسليمها عند التوقيع على هذا العقد الى حساب الشركة
    <span class="highlight"><?= esc($d['collector_company'] ?? '') ?></span>
    رقم فورا <span class="highlight"><?= esc($d['collector_account'] ?? '') ?></span>
  </div>

  <div class="block ar">
    <span class="clause-title">البند الثاني التزامات الطرف الأول/ المالك</span>
    تسليم المواقف رقم (<span class="highlight"><?= $unitNo ?></span>) للطرف الثاني
    وتمكينه من استخدامه موقف للمركبة المملوكة له رقم التسجيل <span class="highlight"><?= $plate ?></span>
  </div>

  <div class="block ar">
    <span class="clause-title">البند الثالث : التزامات الطرف الثاني المستأجر</span>
    <ol class="ar">
      <li>تسديد الأجرة المقررة في موعدها المحدد،</li>
      <li>عدم تأجير الموقف من الباطن لاي طرف اخر ،</li>
      <li>عدم اجراء أي اعمال ميكانيكية واعمال صيانة في المركبة في الموقف المستأجر والمحافظة على نظافته،</li>
      <li>تخصيص الموقف المستأجر فقط للمركبة المذكورة أعلاه وعدم استبدالها بأي مركية أخرى حتى ولو كانت مملوكة له الا بموجب موافقة مكتوبة ومسبقة من المالك ،</li>
      <li>وإعادة الموقف المستأجر الى المالك بالحالة التي كان عليها عند بداية سريان هذا العقد</li>
    </ol>
  </div>

  <div class="block ar">
    <span class="clause-title">البند الرابع : الشروط العامة</span>
    إذا تأخر الطرف الثاني عند تسديد الأجرة المقررة في موعدها المحدد او خالف أي شرط من شروط هذا العقد،
    يعتبر العقد لا غيا من تلقاء نفسه ويحق للطرف الأول إخلاء المركبة من الموقف المستأجر دون اتخاذ أي اجراء قانوني<br>
    يقرأ هذا العقد ويفسر بمواجب قوانين دولة قطر وتكون المحاكم القطرية هي صاحبة الاختصاص بالفصل في أي نزاع ينشأ بين الطرفين<br>
    تم ابرام هذه العقد بتاريخه في نسختين أصليتين بيد كل طرف نسخة منه للعمل بموجبها
  </div>

  <div class="signatures ar" style="direction:rtl">
    <div class="sig">
      <strong>الطرف الأول / المالك</strong><br>
      <?= esc($d['rep_company_ar'] ?? '') ?><br>
      <?= esc($d['rep_name_ar'] ?? '') ?><br>
      <div class="sig-line">التوقيع:</div>
    </div>
    <div class="sig">
      <strong>الطرف الثاني/ المستأجر</strong><br>
      <?= esc($d['tenant_name'] ?? '') ?><br>
      رقم شخصي: <?= esc($d['tenant_qid'] ?? '') ?><br>
      <div class="sig-line">التوقيع:</div>
    </div>
  </div>

  <?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'plain' => true]) ?>
</div>

<!-- English document -->
<div class="page">
  <?= $this->include('layouts/_doc_letterhead', ['settings' => $settings, 'companyLogoUrl' => $companyLogoUrl ?? null, 'companyLogoB64' => $companyLogoB64 ?? null, 'usePdf' => true]) ?>

  <div class="doc-title en">PARKING SPACE LEASE AGREEMENT</div>
  <div class="doc-title en" style="font-size:12px;font-weight:600;margin-top:-8px">
    Parking Space Under the Property Building – Title Deed No. (<span class="highlight"><?= esc($d['header_title_deed_no'] ?? '') ?></span>)
  </div>
  <div class="block en" style="text-align:center;margin-bottom:14px">
    Date: <span class="highlight"><?= esc($contractDateEn ?? '') ?></span>
  </div>

  <div class="block en">
    This Agreement has been entered into by and between:<br><br>
    <strong>First Party / Owner and Lessor:</strong><br>
    <span class="highlight"><?= esc($d['owner_name_en'] ?? '') ?></span>, Commercial Registration No. <span class="highlight"><?= esc($d['owner_cr'] ?? '') ?></span>,
    represented for the purpose of signing this Agreement by
    <span class="highlight"><?= esc($d['rep_company_en'] ?? '') ?></span>, Commercial Registration No. <span class="highlight"><?= esc($d['rep_cr'] ?? '') ?></span>,
    pursuant to Power of Attorney No. <span class="highlight"><?= esc($d['poa_no'] ?? '') ?></span>, issued on <span class="highlight"><?= esc($poaDateFmt ?? '') ?></span>, represented by:<br>
    <span class="highlight"><?= esc($d['rep_name_en'] ?? '') ?></span>, <span class="highlight"><?= esc($d['rep_nationality_en'] ?? '') ?></span>, Personal ID No. <span class="highlight"><?= esc($d['rep_qid'] ?? '') ?></span><br>
    Address: <?= esc($d['landlord_address'] ?? 'Doha, Qatar') ?><br>
    Tel.: <span class="highlight"><?= esc($d['landlord_phone'] ?? '') ?></span><br>
    P.O. Box: <span class="highlight"><?= esc($d['landlord_po_box'] ?? '') ?></span><br>
    Email: <span class="highlight"><?= esc($d['landlord_email'] ?? '') ?></span><br>
    Hereinafter referred to as the “First Party / Owner and Lessor.”
  </div>

  <div class="block en">
    <strong>Second Party / Lessee:</strong><br>
    <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span><br>
    Personal ID No. <span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span><br>
    Tel.: <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span><br>
    P.O. Box: <span class="highlight"><?= $tenantPo ?></span><br>
    Doha, Qatar<br>
    Hereinafter referred to as the “Second Party / Lessee.”
  </div>

  <div class="block en">
    <span class="clause-title">Subject Matter and Preamble</span>
    The First Party, the Lessor, owns a number of parking spaces located beneath its property, Title Deed No. (<span class="highlight"><?= esc($d['title_deed_no'] ?? '') ?></span>),
    situated in <span class="highlight"><?= esc($d['property_city'] ?? '') ?></span>, Building No. (<span class="highlight"><?= esc($d['building_no'] ?? '') ?></span>),
    Zone No. (<span class="highlight"><?= esc($d['zone_no'] ?? '') ?></span>), Street No. (<span class="highlight"><?= esc($d['street_no'] ?? '') ?></span>).<br>
    The Second Party, the Lessee, wishes to lease Parking Space No. (<span class="highlight"><?= $unitNo ?></span>)
    for the purpose of parking his <span class="highlight"><?= esc($vehicleEn ?? '') ?></span>, Registration Plate No. (<span class="highlight"><?= $plate ?></span>).
  </div>

  <div class="block en">
    <span class="clause-title">Article One: Term of the Agreement and Rent</span>
    The term of this Agreement shall be <span class="highlight"><?= esc($d['duration_en'] ?? '') ?></span>,
    commencing on <span class="highlight"><?= esc($startDateEn ?? '') ?></span> and ending on <span class="highlight"><?= esc($endDateEn ?? '') ?></span>.<br>
    The Agreement may be renewed subject to the terms and conditions agreed upon by both parties at the time of renewal.<br>
    The monthly rent for the leased parking space shall be QAR <span class="highlight"><?= $rentFmt ?></span> (<span class="highlight"><?= esc($d['rent_words_en'] ?? '') ?></span>),
    payable by the Second Party to the First Party in advance by means of
    <span class="highlight"><?= (int) ($d['cheque_count'] ?? 0) ?> (<?= esc($d['cheque_count_words_en'] ?? '') ?>)</span> post-dated cheques,
    to be delivered upon signing this Agreement to the account of
    <span class="highlight"><?= esc($d['collector_company'] ?? '') ?></span>, <?= esc($d['collector_account'] ?? '') ?>.
  </div>

  <div class="block en">
    <span class="clause-title">Article Two: Obligations of the First Party / Owner</span>
    The First Party shall hand over Parking Space No. (<span class="highlight"><?= $unitNo ?></span>) to the Second Party
    and enable him to use it as a parking space for his vehicle bearing Registration No. <span class="highlight"><?= $plate ?></span>.
  </div>

  <div class="block en">
    <span class="clause-title">Article Three: Obligations of the Second Party / Lessee</span>
    The Second Party shall:
    <ol class="en">
      <li>Pay the agreed rent on the specified due dates.</li>
      <li>Not sublease the parking space or allow any other party to use it for consideration or otherwise.</li>
      <li>Not carry out any mechanical work or vehicle maintenance in the leased parking space and shall maintain the parking space in a clean condition.</li>
      <li>Use the leased parking space exclusively for the vehicle specified above and shall not replace it with any other vehicle, even if such vehicle is owned by him, except with the prior written approval of the Owner.</li>
      <li>Return the leased parking space to the Owner in the same condition in which it was at the commencement of this Agreement.</li>
    </ol>
  </div>

  <div class="block en">
    <span class="clause-title">Article Four: General Terms and Conditions</span>
    If the Second Party delays payment of the rent on the specified due date or violates any of the terms and conditions of this Agreement,
    the Agreement shall be deemed automatically terminated, and the First Party shall have the right to remove the vehicle from the leased parking space without taking any legal proceedings.<br><br>
    This Agreement shall be governed by and construed in accordance with the laws of the State of Qatar, and the Qatari courts shall have jurisdiction to settle any dispute arising between the parties.<br><br>
    This Agreement has been executed on its date in two (2) original copies, with each party receiving one original copy for implementation.
  </div>

  <div class="signatures en">
    <div class="sig">
      <strong>First Party / Owner and Lessor</strong><br>
      <?= esc($d['rep_company_en'] ?? '') ?><br>
      Represented by:<br>
      <?= esc($d['rep_name_en'] ?? '') ?><br>
      Personal ID No.: <?= esc($d['rep_qid'] ?? '') ?><br>
      <div class="sig-line">Signature: ______________________</div>
    </div>
    <div class="sig">
      <strong>Second Party / Lessee</strong><br>
      <?= esc(strtoupper((string) ($d['tenant_name'] ?? ''))) ?><br>
      Personal ID No.: <?= esc($d['tenant_qid'] ?? '') ?><br>
      <div class="sig-line">Signature: ______________________</div>
    </div>
  </div>

  <?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'plain' => true]) ?>
</div>
</body>
</html>
