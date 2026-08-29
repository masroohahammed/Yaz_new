<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="fm-card mx-auto" style="max-width:720px">
  <div class="text-center p-4 border-bottom">
    <?php if (! empty($companyLogoUrl)): ?><img src="<?= esc($companyLogoUrl) ?>" alt="" style="max-height:60px" class="mb-2"><?php endif; ?>
    <h4 class="mb-0"><?= esc(fm_setting('company_name','FM ERP')) ?></h4>
    <div class="text-muted small"><?= esc(ucfirst($type)) ?> Voucher</div>
  </div>
  <div class="p-4">
    <?php if (! $record): ?><p class="text-muted">Record not found.</p><?php else: ?>
    <?php
    $numKey = match($type) {
      'deposit' => 'deposit_number',
      'withdrawal' => 'withdrawal_number',
      'transfer' => 'transfer_number',
      default => 'id',
    };
    $dateKey = match($type) {
      'deposit' => 'deposit_date',
      'withdrawal' => 'withdrawal_date',
      'transfer' => 'transfer_date',
      default => 'created_at',
    };
    ?>
    <div class="row mb-3"><div class="col-6"><strong>Number</strong><br><?= esc($record[$numKey] ?? '') ?></div><div class="col-6 text-end"><strong>Date</strong><br><?= esc($record[$dateKey] ?? '') ?></div></div>
    <div class="row mb-3"><div class="col-6"><strong>Amount</strong><br><?= $currency ?> <?= number_format((float)($record['amount'] ?? 0), 2) ?></div><div class="col-6 text-end"><strong>Status</strong><br><?= ucwords(str_replace('_',' ', $record['status'] ?? '')) ?></div></div>
    <?php if (! empty($record['description']) || ! empty($record['purpose'])): ?><p><strong>Description</strong><br><?= esc($record['description'] ?? $record['purpose'] ?? '') ?></p><?php endif; ?>
    <div class="row mt-5 pt-4"><div class="col-4 text-center"><hr><small>Prepared By</small></div><div class="col-4 text-center"><hr><small>Approved By</small></div><div class="col-4 text-center"><hr><small>Received By</small></div></div>
    <?php endif; ?>
  </div>
</div>
<div class="text-center mt-3 no-print"><button onclick="window.print()" class="btn btn-fm-primary">Print</button></div>
<?= $this->endSection() ?>
