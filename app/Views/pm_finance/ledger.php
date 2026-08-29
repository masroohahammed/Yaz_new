<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Ledger</h1></div>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="date_from" value="<?= esc($from) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><input type="date" name="date_to" value="<?= esc($to) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><select name="property_id" class="form-select form-select-sm"><option value="">All properties</option>
    <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $facilityId==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-auto"><button class="btn btn-sm btn-secondary">Apply</button></div>
</form>
<table class="table table-sm table-hover">
  <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Property</th><th class="text-end">Amount</th></tr></thead>
  <tbody>
  <?php foreach ($entries as $e): ?>
  <tr>
    <td><?= esc($e['entry_date']) ?></td>
    <td><span class="badge bg-<?= $e['direction']==='income'?'success':'danger' ?>"><?= esc($e['direction']) ?></span></td>
    <td class="small"><?= esc($e['description'] ?? $e['entry_type']) ?></td>
    <td class="small"><?= esc($e['facility_name'] ?? '') ?></td>
    <td class="text-end"><?= number_format((float)$e['amount'], 2) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?= $this->endSection() ?>
