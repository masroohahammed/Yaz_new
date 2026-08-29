<?php
/**
 * Bilingual parking rental agreement — English left, Arabic right.
 * @var array<string,mixed> $d
 */
$months     = (int) ($durationMonths ?? 0);
$rentFmt    = number_format((float) ($d['rent_amount'] ?? 0), 0);
$dateFmt    = function ($dt) {
    if (! $dt) {
        return '—';
    }
    $ts = strtotime($dt);

    return $ts ? date('Y/m/d', $ts) : $dt;
};
$contractDt = $dateFmt($d['contract_date'] ?? '');
$startDt    = $dateFmt($d['start_date'] ?? '');
$endDt      = $dateFmt($d['end_date'] ?? '');
$plate      = esc($d['plate_number'] ?? '');
$unitNo     = esc($d['parking_unit_no'] ?? '');
$vehicle    = esc($d['vehicle_type'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, "Segoe UI", sans-serif; font-size: 11px; color: #111; margin: 0; background: #fff; line-height: 1.55; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; }
  .no-print button, .no-print a {
    background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px;
    font-size: 12px; text-decoration: none; cursor: pointer;
  }
  .page { max-width: 210mm; margin: 0 auto; padding: 12mm 14mm; }
  .doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 14px; }
  .doc-header img { max-height: 52px; max-width: 160px; object-fit: contain; }
  .doc-header .meta { font-size: 10px; text-align: right; line-height: 1.5; }
  .title-block { text-align: center; margin: 12px 0 16px; }
  .title-block h1 { font-size: 15px; margin: 0; letter-spacing: 0.5px; }
  .title-block .sub { font-size: 11px; color: #444; margin-top: 4px; }
  .bilingual { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; border: 1px solid #ccc; margin-bottom: 10px; }
  .bilingual .head {
    grid-column: 1 / -1; background: #f4f4f4; font-weight: 700; font-size: 10px;
    padding: 5px 10px; border-bottom: 1px solid #ccc; text-transform: uppercase;
  }
  .col-en { padding: 8px 10px; border-right: 1px solid #e5e5e5; vertical-align: top; }
  .col-ar {
    padding: 8px 10px; direction: rtl; text-align: right; font-family: Arial, "Traditional Arabic", sans-serif;
    font-size: 12px; vertical-align: top;
  }
  .bilingual .row-pair { display: contents; }
  .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; margin: 12px 0; font-size: 10px; }
  .field-grid div { border-bottom: 1px dotted #ddd; padding: 3px 0; }
  .field-grid label { font-weight: 700; color: #555; }
  .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 36px; }
  .sig-line { border-top: 1px solid #333; margin-top: 48px; padding-top: 6px; font-size: 10px; }
  .highlight { font-weight: 700; }
  @media print {
    .no-print { display: none !important; }
    .page { padding: 10mm; }
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
  <div class="doc-header">
    <div>
      <?php if (!empty($companyLogoB64)): ?>
        <img src="<?= esc($companyLogoB64) ?>" alt="Logo">
      <?php elseif (!empty($companyLogoUrl)): ?>
        <img src="<?= esc($companyLogoUrl) ?>" alt="Logo">
      <?php endif; ?>
    </div>
    <div class="meta">
      <strong><?= esc($d['landlord_name'] ?? '') ?></strong><br>
      <?= esc($d['landlord_email'] ?? '') ?><br>
      CR: <?= esc($d['landlord_cr'] ?? '') ?>
    </div>
  </div>

  <div class="title-block">
    <h1>PARKING RENTAL AGREEMENT &nbsp;|&nbsp; عقد إيجار موقف سيارة</h1>
    <div class="sub">Contract No. <?= esc($d['contract_number'] ?? '—') ?> &nbsp;·&nbsp; Date <?= esc($contractDt) ?></div>
  </div>

  <div class="field-grid">
    <div><label>Parking Unit / موقف:</label> <span class="highlight"><?= $unitNo ?></span></div>
    <div><label>Plate / اللوحة:</label> <span class="highlight"><?= $plate ?></span></div>
    <div><label>Vehicle / المركبة:</label> <?= $vehicle ?></div>
    <div><label>Rent / الإيجار:</label> <?= $rentFmt ?> <?= esc($d['currency'] ?? 'QAR') ?></div>
    <div><label>Period / المدة:</label> <?= esc($startDt) ?> — <?= esc($endDt) ?></div>
    <div><label>Property / العقار:</label> <?= esc($d['property_name'] ?? '') ?></div>
  </div>

  <!-- Intro -->
  <div class="bilingual">
    <div class="head">Introduction / تمهيد</div>
    <div class="row-pair">
      <div class="col-en">
        On this day, <span class="highlight"><?= esc($englishDay ?? '') ?></span>, corresponding to
        <span class="highlight"><?= esc($contractDt) ?></span>, this Parking Rental Agreement is entered into between:
      </div>
      <div class="col-ar">
        أنه بتاريخ اليوم <span class="highlight"><?= esc($arabicDay ?? '') ?></span> الموافق
        <span class="highlight"><?= esc($contractDt) ?></span>، تم إبرام هذا العقد بين كل من:
      </div>
    </div>
  </div>

  <!-- Parties -->
  <div class="bilingual">
    <div class="head">Parties / الأطراف</div>
    <div class="row-pair">
      <div class="col-en">
        <strong>First Party (Owner / Lessor):</strong><br>
        <?= esc($d['landlord_name'] ?? '') ?>, Commercial Registration <?= esc($d['landlord_cr'] ?? '') ?>,
        email <?= esc($d['landlord_email'] ?? '') ?>, hereinafter referred to as the “First Party / Owner and Lessor”.
      </div>
      <div class="col-ar">
        <strong>الطرف الأول (المالك / المؤجر):</strong><br>
        <?= esc($d['landlord_name'] ?? '') ?>، سجل تجاري <?= esc($d['landlord_cr'] ?? '') ?>،
        البريد <?= esc($d['landlord_email'] ?? '') ?>، ويعرف فيما بعد بالطرف الأول / المالك والمؤجر.
      </div>
    </div>
    <div class="row-pair">
      <div class="col-en">
        <strong>Second Party (Tenant / Lessee):</strong><br>
        Mr./Ms. <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span>,
        ID No. <span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span>,
        Mobile <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span>,
        Nationality <?= esc($d['tenant_nationality'] ?? '') ?>,
        Address <?= esc($d['tenant_address'] ?? '') ?>,
        hereinafter referred to as the “Second Party / Tenant”.
      </div>
      <div class="col-ar">
        <strong>الطرف الثاني (المستأجر):</strong><br>
        السيد/ة <span class="highlight"><?= esc($d['tenant_name'] ?? '') ?></span>،
        رقم الهوية <span class="highlight"><?= esc($d['tenant_qid'] ?? '') ?></span>،
        هاتف <span class="highlight"><?= esc($d['tenant_phone'] ?? '') ?></span>،
        الجنسية <?= esc($d['tenant_nationality'] ?? '') ?>،
        العنوان <?= esc($d['tenant_address'] ?? '') ?>،
        ويعرف فيما بعد بالطرف الثاني / المستأجر.
      </div>
    </div>
  </div>

  <!-- Subject -->
  <div class="bilingual">
    <div class="head">Subject of the Agreement / موضوع العقد</div>
    <div class="row-pair">
      <div class="col-en">
        The property described under title deed No. <span class="highlight"><?= esc($d['title_deed_no'] ?? '—') ?></span>
        in <?= esc($d['property_city'] ?? '') ?> City, Building No. <?= esc($d['building_no'] ?? '—') ?>,
        Zone No. <?= esc($d['zone_no'] ?? '—') ?>, Street No. <?= esc($d['street_no'] ?? '—') ?>
        (<?= esc($d['property_name'] ?? '') ?>).
        The Second Party wishes to rent <strong>Parking Space No. <?= $unitNo ?></strong>
        for his/her <strong><?= $vehicle ?></strong> with plate number <strong><?= $plate ?></strong>.
      </div>
      <div class="col-ar">
        العقار الموصوف بسند الملكية رقم <span class="highlight"><?= esc($d['title_deed_no'] ?? '—') ?></span>
        في مدينة <?= esc($d['property_city'] ?? '') ?>، مبنى رقم <?= esc($d['building_no'] ?? '—') ?>،
        منطقة رقم <?= esc($d['zone_no'] ?? '—') ?>، شارع رقم <?= esc($d['street_no'] ?? '—') ?>
        (<?= esc($d['property_name'] ?? '') ?>).
        ويرغب الطرف الثاني في استئجار <strong>موقف رقم <?= $unitNo ?></strong>
        لاستخدام <strong><?= $vehicle ?></strong> رقم اللوحة <strong><?= $plate ?></strong>.
      </div>
    </div>
  </div>

  <!-- Clause 1 -->
  <div class="bilingual">
    <div class="head">Clause 1 — Duration &amp; Rent / البند الأول: مدة العقد والقيمة الإيجارية</div>
    <div class="row-pair">
      <div class="col-en">
        The contract duration is <span class="highlight"><?= (int) $months ?> month(s)</span>,
        from <span class="highlight"><?= esc($startDt) ?></span> to <span class="highlight"><?= esc($endDt) ?></span>.
        Monthly rent is <span class="highlight"><?= $rentFmt ?> <?= esc($d['currency'] ?? 'QAR') ?></span>,
        payable in advance via <?= esc($d['payment_terms'] ?? 'cash') ?> to
        <?= esc($d['collector_company'] ?? '') ?> (CR <?= esc($d['collector_cr'] ?? '') ?>).
      </div>
      <div class="col-ar">
        مدة العقد <span class="highlight"><?= (int) $months ?> شهر/أشهر</span>،
        من <span class="highlight"><?= esc($startDt) ?></span> إلى <span class="highlight"><?= esc($endDt) ?></span>.
        القيمة الإيجارية <span class="highlight"><?= $rentFmt ?> <?= esc($d['currency'] ?? 'QAR') ?></span> شهرياً،
        تُدفع مقدماً <?= esc($d['payment_terms'] ?? '') ?> لـ <?= esc($d['collector_company'] ?? '') ?>
        (سجل تجاري <?= esc($d['collector_cr'] ?? '') ?>).
      </div>
    </div>
  </div>

  <!-- Clause 2 -->
  <div class="bilingual">
    <div class="head">Clause 2 — Owner Obligations / البند الثاني: التزامات الطرف الأول</div>
    <div class="row-pair">
      <div class="col-en">
        The First Party shall hand over Parking Space No. <strong><?= $unitNo ?></strong> to the Second Party
        for the vehicle (plate <strong><?= $plate ?></strong>) for the agreed term, in usable condition.
      </div>
      <div class="col-ar">
        يلتزم الطرف الأول بتسليم موقف رقم <strong><?= $unitNo ?></strong> للطرف الثاني
        للمركبة (لوحة <strong><?= $plate ?></strong>) للمدة المتفق عليها بحالة صالحة للاستخدام.
      </div>
    </div>
  </div>

  <!-- Clause 3 -->
  <div class="bilingual">
    <div class="head">Clause 3 — Tenant Obligations / البند الثالث: التزامات الطرف الثاني</div>
    <div class="row-pair">
      <div class="col-en">
        The Second Party shall use the parking space only for the registered vehicle, keep the area clean,
        not sublease the space, and comply with building parking regulations. Late payment may incur penalties per company policy.
      </div>
      <div class="col-ar">
        يلتزم الطرف الثاني باستخدام الموقف للمركبة المسجلة فقط، المحافظة على نظافة الموقف،
        عدم التنازل أو الإيجار من الباطن، والالتزام بلوائح المبنى. التأخر في السداد قد يترتب عليه غرامات وفق سياسة الشركة.
      </div>
    </div>
  </div>

  <!-- Clause 4 -->
  <div class="bilingual">
    <div class="head">Clause 4 — Liability / البند الرابع: المسؤولية</div>
    <div class="row-pair">
      <div class="col-en">
        The First Party is not liable for theft, damage, or loss of the vehicle or its contents while parked,
        unless caused by gross negligence of the First Party.
      </div>
      <div class="col-ar">
        لا يتحمل الطرف الأول مسؤولية السرقة أو التلف أو فقدان المركبة أو محتوياتها أثناء الوقوف،
        إلا في حالة التعمد أو الإهمال الجسيم من الطرف الأول.
      </div>
    </div>
  </div>

  <!-- Clause 5 -->
  <div class="bilingual">
    <div class="head">Clause 5 — Termination / البند الخامس: إنهاء العقد</div>
    <div class="row-pair">
      <div class="col-en">
        Either party may terminate per Qatar law and agreed notice. Upon termination the Second Party shall vacate
        the parking space and remove the vehicle immediately.
      </div>
      <div class="col-ar">
        يجوز لأي من الطرفين إنهاء العقد وفق القانون القطري والإشعار المتفق عليه. عند الإنهاء يلتزم الطرف الثاني
        بإخلاء الموقف وإزالة المركبة فوراً.
      </div>
    </div>
  </div>

  <div class="signatures">
    <div>
      <div class="sig-line">
        <strong>First Party / الطرف الأول</strong><br>
        <?= esc($d['landlord_name'] ?? '') ?><br>
        Date: <?= esc($contractDt) ?>
      </div>
    </div>
    <div>
      <div class="sig-line">
        <strong>Second Party / الطرف الثاني</strong><br>
        <?= esc($d['tenant_name'] ?? '') ?><br>
        Date: <?= esc($contractDt) ?>
      </div>
    </div>
  </div>

  <div style="text-align:center;margin-top:24px;font-size:9px;color:#888;">
    Generated <?= date('Y-m-d H:i') ?> — <?= esc($d['landlord_name'] ?? '') ?>
  </div>
</div>
</body>
</html>
