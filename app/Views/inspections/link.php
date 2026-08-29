<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Link Inspection</h1></div>
<?= form_open(base_url('pm-inspections/link/'.$inspection['id'])) ?>
<?= csrf_field() ?>
<div class="mb-2"><select name="link_to" class="form-select form-select-sm"><option value="maintenance_request">Maintenance request</option><option value="contract">Contract</option></select></div>
<div class="mb-2"><select name="ref_id" class="form-select form-select-sm">
  <?php foreach ($workOrders as $w): ?><option value="<?= $w['id'] ?>">WO <?= esc($w['wo_number'] ?? $w['id']) ?> — <?= esc($w['title'] ?? '') ?></option><?php endforeach; ?>
</select></div>
<button type="submit" class="btn btn-sm btn-fm-primary">Link</button>
<?= form_close() ?>
<?= $this->endSection() ?>
