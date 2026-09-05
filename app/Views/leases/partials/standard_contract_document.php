<?php
/** @var array<string,mixed> $contract */
$displayQid = trim((string) ($tenantQid ?? $contract['tenant_qid'] ?? $contract['qid_no'] ?? $contract['passport_no'] ?? ''));
?>
<?= $this->include('layouts/_doc_letterhead', [
    'settings' => $settings,
    'companyBranding' => $companyBranding ?? null,
    'companyLogoUrl' => $companyLogoUrl ?? null,
    'companyLogoB64' => $companyLogoB64 ?? null,
    'usePdf' => ! empty($usePdf),
]) ?>

<h1 class="contract-title">Lease Contract / عقد إيجار</h1>
<div class="contract-sub"><?= esc($contract['contract_number']) ?></div>

<div class="bilingual">
  <div class="bilingual-row">
    <div class="col-en">
      <div class="section-title" style="margin-top:0">Contract Details (English)</div>
      <div class="info-row"><label>Tenant:</label> <span><?= esc($contract['tenant_name'] ?? '—') ?></span></div>
      <?php if ($displayQid !== ''): ?>
      <div class="info-row"><label>QID / Passport:</label> <span><?= esc($displayQid) ?></span></div>
      <?php endif; ?>
      <div class="info-row"><label>Property:</label> <span><?= esc($contract['facility_name'] ?? '—') ?></span></div>
      <div class="info-row"><label>Unit:</label> <span><?= esc($contract['unit_number'] ?? '—') ?></span></div>
      <div class="info-row"><label>Start:</label> <span><?= esc($contract['start_date']) ?></span></div>
      <div class="info-row"><label>End:</label> <span><?= esc($contract['end_date']) ?></span></div>
      <div class="info-row"><label>Rent:</label> <span><?= number_format((float) $contract['rent_amount'], 2) ?> <?= esc($currency ?? 'QAR') ?></span></div>
      <div class="info-row"><label>Frequency:</label> <span><?= esc($contract['payment_frequency'] ?? '—') ?></span></div>
    </div>
    <div class="col-ar">
      <div class="section-title" style="margin-top:0;text-align:right;border-left:none;border-right:3px solid #76002b">بيانات العقد (عربي)</div>
      <div class="info-row"><label>المستأجر:</label> <span><?= esc($contract['tenant_name'] ?? '—') ?></span></div>
      <?php if ($displayQid !== ''): ?>
      <div class="info-row"><label>الهوية:</label> <span><?= esc($displayQid) ?></span></div>
      <?php endif; ?>
      <div class="info-row"><label>العقار:</label> <span><?= esc($contract['facility_name'] ?? '—') ?></span></div>
      <div class="info-row"><label>الوحدة:</label> <span><?= esc($contract['unit_number'] ?? '—') ?></span></div>
      <div class="info-row"><label>البداية:</label> <span><?= esc($contract['start_date']) ?></span></div>
      <div class="info-row"><label>النهاية:</label> <span><?= esc($contract['end_date']) ?></span></div>
      <div class="info-row"><label>الإيجار:</label> <span><?= number_format((float) $contract['rent_amount'], 2) ?> <?= esc($currency ?? 'QAR') ?></span></div>
      <div class="info-row"><label>الدورية:</label> <span><?= esc($contract['payment_frequency'] ?? '—') ?></span></div>
    </div>
  </div>

  <?php if (! empty($templateEn) || ! empty($templateAr)): ?>
  <div class="bilingual-row">
    <div class="col-en">
      <?php if (! empty($templateEn)): ?>
      <div class="section-title">Contract Terms (English)</div>
      <div class="content-en"><?= $templateEn ?></div>
      <?php endif; ?>
      <?php if (! empty($contract['contract_terms'])): ?>
      <div class="section-title">Additional Terms</div>
      <div class="content-en"><?= nl2br(esc($contract['contract_terms'])) ?></div>
      <?php endif; ?>
    </div>
    <div class="col-ar">
      <?php if (! empty($templateAr)): ?>
      <div class="section-title" style="text-align:right;border-left:none;border-right:3px solid #76002b">بنود العقد (عربي)</div>
      <div class="content-ar"><?= $templateAr ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="signatures">
  <div class="sig-row">
    <div class="col-en">
      <div class="signature-line"></div>
      <strong>Landlord / Owner</strong>
      <div class="signature-label">Name / Date</div>
    </div>
    <div class="col-ar">
      <div class="signature-line"></div>
      <strong>المالك / المؤجر</strong>
      <div class="signature-label">الاسم / التاريخ</div>
    </div>
  </div>
  <div class="sig-row">
    <div class="col-en">
      <?= $this->include('leases/partials/_tenant_signature_slot', [
          'signMode' => $signMode ?? false,
          'alreadySigned' => $alreadySigned ?? false,
          'tenantSignatureB64' => $tenantSignatureB64 ?? '',
      ]) ?>
      <strong>Tenant / Lessee</strong>
      <div class="signature-label"><?= esc($contract['tenant_name'] ?? '') ?><?php if ($displayQid !== ''): ?> · QID <?= esc($displayQid) ?><?php endif; ?></div>
      <?php if (! empty($signMode) && empty($alreadySigned)): ?>
        <div class="signature-hint">Draw your signature on the line</div>
      <?php endif; ?>
    </div>
    <div class="col-ar">
      <?= $this->include('leases/partials/_tenant_signature_slot', [
          'signMode' => false,
          'alreadySigned' => $alreadySigned ?? false,
          'tenantSignatureB64' => $tenantSignatureB64 ?? '',
      ]) ?>
      <strong>المستأجر</strong>
      <div class="signature-label"><?= esc($contract['tenant_name'] ?? '') ?><?php if ($displayQid !== ''): ?> · <?= esc($displayQid) ?><?php endif; ?></div>
      <?php if (! empty($signMode) && empty($alreadySigned)): ?>
        <div class="signature-hint">وقع على الخط</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'companyBranding' => $companyBranding ?? null, 'plain' => true]) ?>
