<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('fm'); $e = $employee; ?>

<?= $this->include('hr/_subnav', ['hrActive' => 'employees']) ?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <h1 class="h4 mb-1"><?= esc($e['user_name'] ?? $e['employee_code']) ?></h1>
    <span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst(str_replace('_', ' ', $e['status'])) ?></span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('hr/employees/' . (int) $e['id'] . '/edit') ?>" class="btn btn-fm-primary btn-sm">Edit</a>
    <?= form_open(base_url('hr/employees/' . (int) $e['id'] . '/status'), ['class' => 'd-inline']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="status" value="<?= ($e['status'] ?? '') === 'active' ? 'inactive' : 'active' ?>">
      <button type="submit" class="btn btn-fm-outline btn-sm"><?= ($e['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?></button>
    <?= form_close() ?>
    <a href="<?= base_url('hr/employees') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>

<ul class="nav fm-entity-tabs mb-3">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-profile">Profile</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-documents">Documents</a></li>
</ul>

<div class="tab-content fm-tab-pane">
  <div class="tab-pane fade show active" id="tab-profile">
    <div class="hr-page-card">
      <div class="row g-3 small">
        <div class="col-md-4"><span class="text-muted d-block">Code</span><div class="fw-semibold"><?= esc($e['employee_code']) ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Email</span><div><?= esc($e['user_email'] ?? '') ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Hire date</span><div><?= esc($e['hire_date'] ?? '—') ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Basic salary</span><div><?= number_format((float) ($e['basic_salary'] ?? 0), 2) ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Allowances</span><div><?= number_format((float) ($e['allowances'] ?? 0), 2) ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Passport</span><div><?= esc($e['passport_no'] ?? '—') ?> · <?= esc($e['passport_expiry'] ?? '') ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">Visa expiry</span><div><?= esc($e['visa_expiry'] ?? '—') ?></div></div>
        <div class="col-md-4"><span class="text-muted d-block">National ID</span><div><?= esc($e['national_id'] ?? '—') ?></div></div>
      </div>
    </div>
  </div>
  <div class="tab-pane fade" id="tab-documents">
    <?= $this->include('documents/_tab', [
      'module' => 'employee',
      'refId' => (int) $e['user_id'],
      'embed' => true,
      'documents' => $documents ?? [],
      'docTypes' => fm_document_types(),
    ]) ?>
  </div>
</div>

<?= view('documents/_embed_modals', [
  'module'   => 'employee',
  'refId'    => (int) $e['user_id'],
  'docTypes' => fm_document_types(),
]) ?>
<?= $this->endSection() ?>
