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

<div class="info-grid">
  <div>
    <div class="info-row"><label>Tenant / المستأجر:</label> <span><?= esc($contract['tenant_name'] ?? '—') ?></span></div>
    <?php if ($displayQid !== ''): ?>
    <div class="info-row"><label>QID / Passport / الهوية:</label> <span><?= esc($displayQid) ?></span></div>
    <?php endif; ?>
    <div class="info-row"><label>Property / العقار:</label> <span><?= esc($contract['facility_name'] ?? '—') ?></span></div>
    <div class="info-row"><label>Unit / الوحدة:</label> <span><?= esc($contract['unit_number'] ?? '—') ?></span></div>
  </div>
  <div>
    <div class="info-row"><label>Start / البداية:</label> <span><?= esc($contract['start_date']) ?></span></div>
    <div class="info-row"><label>End / النهاية:</label> <span><?= esc($contract['end_date']) ?></span></div>
    <div class="info-row"><label>Rent / الإيجار:</label> <span><?= number_format((float) $contract['rent_amount'], 2) ?> <?= esc($currency ?? 'QAR') ?></span></div>
    <div class="info-row"><label>Frequency / الدورية:</label> <span><?= esc($contract['payment_frequency'] ?? '—') ?></span></div>
  </div>
</div>

<?php if (! empty($templateEn)): ?>
<div class="section-title">Contract Terms (English)</div>
<div class="content-en"><?= $templateEn ?></div>
<?php endif; ?>

<?php if (! empty($templateAr)): ?>
<hr class="divider">
<div class="section-title">بنود العقد (عربي)</div>
<div class="content-ar"><?= $templateAr ?></div>
<?php endif; ?>

<?php if (! empty($contract['contract_terms'])): ?>
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
    <?= $this->include('leases/partials/_tenant_signature_slot', [
        'signMode' => $signMode ?? false,
        'alreadySigned' => $alreadySigned ?? false,
        'tenantSignatureB64' => $tenantSignatureB64 ?? '',
        'lineClass' => 'signature-line',
        'imgClass' => 'signature-img',
    ]) ?>
    <strong>Tenant / المستأجر</strong>
    <div class="signature-label"><?= esc($contract['tenant_name'] ?? '') ?><?php if ($displayQid !== ''): ?> · QID <?= esc($displayQid) ?><?php endif; ?></div>
    <?php if (! empty($signMode) && empty($alreadySigned)): ?>
      <div class="signature-hint">Draw your signature above / وقع في المربع أعلاه</div>
    <?php endif; ?>
  </div>
</div>

<?= $this->include('layouts/_doc_footer', ['settings' => $settings, 'companyBranding' => $companyBranding ?? null, 'plain' => true]) ?>
