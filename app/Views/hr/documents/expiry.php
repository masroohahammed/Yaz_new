<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-excel me-2 text-primary"></i>HR Document Expiry</h1>
    <p class="text-muted small mb-0">Employee documents from the central DMS (module=employee).</p>
  </div>
  <a href="<?= base_url('employees') ?>" class="btn btn-fm-outline btn-sm">Workforce</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>patch_hr_m2_dms.sql</code> to enable HR document categories and expiry tracking.</div>
<?php else: ?>

<div class="row g-3 mb-3">
  <?php foreach ($counts ?? [] as $key => $val): ?>
  <div class="col-md-3">
    <div class="fm-form-section text-center py-3">
      <div class="text-muted small"><?= esc(ucwords(str_replace('_', ' ', $key))) ?></div>
      <div class="fs-4 fw-bold"><?= (int)$val ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body">
    <?= form_open(base_url('hr/documents/expiry'), ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
    <div class="col-md-3">
      <label class="form-label small">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" value="<?= esc($filters['search'] ?? '') ?>">
    </div>
    <?php if (!empty($masters['facilities'])): ?>
    <div class="col-md-2">
      <label class="form-label small">Facility</label>
      <select name="facility_id" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($masters['facilities'] as $f): ?>
        <option value="<?= (int)$f['id'] ?>" <?= ($filters['facility_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <?php if (!empty($masters['departments'])): ?>
    <div class="col-md-2">
      <label class="form-label small">Department</label>
      <select name="department_id" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($masters['departments'] as $d): ?>
        <option value="<?= (int)$d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
    <?= form_close() ?>
  </div>
</div>

<ul class="nav nav-pills mb-3 flex-wrap gap-1">
  <?php foreach ($buckets as $key => $meta): ?>
  <li class="nav-item">
    <a class="nav-link <?= ($activeBucket ?? '') === $key ? 'active' : '' ?>" href="<?= base_url('hr/documents/expiry?bucket='.$key) ?>">
      <?= esc($meta['label']) ?>
      <span class="badge bg-secondary ms-1"><?= count($bucketData[$key] ?? []) ?></span>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<div class="fm-card">
  <div class="card-header-fm"><h5><?= esc($buckets[$activeBucket]['label'] ?? 'Documents') ?></h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Employee</th><th>Code</th><th>Document</th><th>Category</th><th>Expiry</th><th>Facility</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($activeRows as $row): ?>
      <tr>
        <td><?= esc($row['employee_name'] ?? '—') ?></td>
        <td class="small"><?= esc($row['emp_code'] ?? '') ?></td>
        <td><?= esc($row['title']) ?></td>
        <td class="small"><?= esc($row['category_name'] ?? $row['doc_type'] ?? '') ?></td>
        <td><span class="badge <?= ($hrDocService ?? new \App\Services\Hr\HrDocumentService())->expiryBadgeClass($row['expiry_date'] ?? null) ?>"><?= !empty($row['expiry_date']) ? date('d M Y', strtotime($row['expiry_date'])) : '—' ?></span></td>
        <td class="small text-muted"><?= esc($row['facility_name'] ?? '—') ?></td>
        <td><a href="<?= base_url('employees/view/'.$row['employee_id'].'?tab=documents') ?>" class="btn btn-sm btn-fm-outline">Profile</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($activeRows)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No documents in this bucket.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
