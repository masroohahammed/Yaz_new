<?php
/**
 * Parking contract body — shared by print and public sign views.
 *
 * @var array<string,mixed> $d
 */
$rentFmt      = number_format((float) ($d['rent_amount'] ?? 0), 0);
$unitNo       = esc($d['parking_unit_no'] ?? '');
$plate        = esc($d['plate_number'] ?? '');
$vehicleAr    = esc($d['vehicle_type_ar'] ?? 'مركبته');
$tenantPo     = trim((string) ($d['tenant_po_box'] ?? '')) !== '' ? esc($d['tenant_po_box']) : '------';
$settings     = $settings ?? [];
$landlordName = esc($d['owner_name_en'] ?? $settings['company_name'] ?? '');
$tenantSig    = trim((string) ($tenantSignatureB64 ?? ''));
$signMode     = ! empty($signMode);
$alreadySigned = ! empty($alreadySigned);
$contractPhotos = array_values(array_filter($d['contract_photos'] ?? [], static fn ($p) => is_string($p) && trim($p) !== ''));
$usePdfEmbed  = ! empty($usePdf);
?>
<?= $this->include('layouts/_doc_letterhead', [
    'settings' => $settings,
    'companyBranding' => $companyBranding ?? null,
    'companyLogoUrl' => $companyLogoUrl ?? null,
    'companyLogoB64' => $companyLogoB64 ?? null,
    'usePdf' => ! empty($usePdf),
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

  <?php if ($contractPhotos !== []): ?>
  <div class="col-en block">
    <span class="clause-title">Photos</span>
    Vehicle / parking photos attached to this agreement.
  </div>
  <div class="col-ar block">
    <span class="clause-title">الصور</span>
    صور المركبة / الموقف المرفقة بهذا العقد.
  </div>
  <div class="contract-photos-grid" style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:8px 0 12px">
    <?php foreach ($contractPhotos as $photoPath): ?>
    <?php
      $photoSrc = \App\Services\ParkingContractPhotoService::photoSrc((string) $photoPath, $usePdfEmbed);
      if ($photoSrc === '') {
          continue;
      }
    ?>
    <img src="<?= esc($photoSrc) ?>" alt="Parking contract photo" style="max-height:140px;max-width:31%;object-fit:contain;border:1px solid #ccc;border-radius:4px;padding:4px;background:#fff">
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
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
    <?= $this->include('leases/partials/_tenant_signature_slot', [
        'signMode' => $signMode,
        'alreadySigned' => $alreadySigned,
        'tenantSignatureB64' => $tenantSignatureB64 ?? '',
    ]) ?>
    <?php if ($signMode && ! $alreadySigned): ?>
      <div class="signature-hint">Draw your signature on the line / وقع على الخط</div>
    <?php endif; ?>
  </div>
  <div class="col-ar" style="font-size:10px;direction:rtl;text-align:right;font-family:'Cairo',sans-serif">
    <strong>الطرف الثاني / المستأجر</strong><br><?= esc($d['tenant_name'] ?? '') ?><br>رقم: <?= esc($d['tenant_qid'] ?? '') ?>
    <?php if ($tenantSig !== ''): ?>
      <img src="<?= esc($tenantSig) ?>" alt="Tenant signature" class="tenant-signature-image" style="position:static;transform:none;margin:8px auto 0;display:block;max-height:50px">
    <?php elseif ($signMode && ! $alreadySigned): ?>
      <div class="sig-line" style="margin-top:12px;font-size:9px;border:none;padding-top:8px">← التوقيع</div>
    <?php else: ?>
      <div class="sig-line">التوقيع:</div>
    <?php endif; ?>
  </div>
</div>

<?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'companyBranding' => $companyBranding ?? null, 'plain' => true]) ?>
