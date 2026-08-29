<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between">
  <h1>Inspections</h1>
  <a href="<?= base_url('pm-inspections/create') ?>" class="btn btn-sm btn-fm-primary">New Inspection</a>
</div>
<form method="get" class="row g-2 mb-3">
  <div class="col-md-3"><select name="property_id" class="form-select form-select-sm"><option value="">All properties</option>
    <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= ($filters['property_id']??0)==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-md-2"><select name="inspection_type" class="form-select form-select-sm">
    <option value="">All types</option>
    <?php foreach (['move_in','move_out','routine'] as $t): ?><option value="<?= $t ?>" <?= ($filters['type']??'')===$t?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-auto"><button class="btn btn-sm btn-secondary">Filter</button></div>
</form>
<table class="table table-sm table-hover">
  <thead><tr><th>Date</th><th>Type</th><th>Unit</th><th>Property</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($inspections as $i): ?>
  <tr>
    <td><?= esc($i['inspection_date'] ?? $i['created_at']) ?></td>
    <td><?= esc(str_replace('_',' ',$i['type'])) ?></td>
    <td><?= esc($i['unit_number']) ?></td>
    <td><?= esc($i['property_name']) ?></td>
    <td><?= esc($i['status']) ?></td>
    <td>
      <a href="<?= base_url('pm-inspections/view/'.$i['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
      <a href="<?= base_url('pm-inspections/print/'.$i['id']) ?>" class="btn btn-sm btn-outline-primary">Print</a>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?= $this->endSection() ?>
