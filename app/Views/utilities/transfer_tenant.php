<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Transfer to Tenant — <?= esc($account['utility_name']) ?></h1>
<?= form_open(base_url('utilities/'.$account['id'].'/transfer-to-tenant')) ?>
<?= csrf_field() ?>
<div class="mb-2"><label class="form-label small">Tenant *</label>
  <select name="tenant_id" class="form-select form-select-sm" required>
    <?php foreach ($tenants as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['full_name']) ?></option><?php endforeach; ?>
  </select></div>
<div class="mb-2"><label class="form-label small">Transfer date *</label><input type="date" name="transfer_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
<button type="submit" class="btn btn-sm btn-fm-primary">Transfer to Tenant</button>
<?= form_close() ?>
<?= $this->endSection() ?>
