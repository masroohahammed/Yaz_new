<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Vendor Quotations</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Quotations</li></ol></nav>
  </div>
  <a href="<?= base_url('quotations/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Quotation</a>
</div>

<?php if(!empty($migrationRequired)): ?>
<div class="alert alert-warning">Vendor quotations tables are not available. Run database migration first.</div>
<?php else: ?>

<div class="fm-card mb-3 p-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-sm-4">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search vendor / description…" value="<?= esc($filters['search'] ?? '') ?>">
    </div>
    <div class="col-sm-3">
      <select name="facility_id" class="form-select form-select-sm">
        <option value="">All Facilities</option>
        <?php foreach($facilities ?? [] as $f): ?>
        <option value="<?= $f['id'] ?>" <?= ($filters['facility_id'] ?? 0) == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <?php foreach(['draft'=>'Draft','submitted'=>'Submitted','approved'=>'Approved','rejected'=>'Rejected','expired'=>'Expired'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary-brand btn-sm" type="submit">Filter</button></div>
    <div class="col-auto"><a href="<?= base_url('quotations') ?>" class="btn btn-outline-secondary btn-sm">Reset</a></div>
  </form>
</div>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <?php if(empty($rows)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-file-earmark-text d-block mb-2" style="font-size:2.5rem"></i>No quotations found.</div>
    <?php else: ?>
    <table class="fm-table">
      <thead><tr><th>#</th><th>Vendor</th><th>Facility</th><th>Description</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td class="small text-muted"><?= $r['id'] ?></td>
        <td class="fw-semibold small"><?= esc($r['vendor_name']) ?></td>
        <td class="small"><?= esc($r['facility_name'] ?? '—') ?></td>
        <td class="small"><?= esc(substr($r['description'] ?? '', 0, 60)) ?><?= strlen($r['description'] ?? '') > 60 ? '…' : '' ?></td>
        <td class="small"><?= $r['valid_until'] ? date('d M Y', strtotime($r['valid_until'])) : '—' ?></td>
        <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= base_url('quotations/'.$r['id']) ?>" class="btn-action bg-primary text-white" title="View"><i class="bi bi-eye"></i></a>
            <a href="<?= base_url('quotations/'.$r['id'].'/edit') ?>" class="btn-action bg-warning text-white" title="Edit"><i class="bi bi-pencil"></i></a>
            <form action="<?= base_url('quotations/'.$r['id'].'/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this quotation?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn-action bg-danger text-white" title="Delete"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
