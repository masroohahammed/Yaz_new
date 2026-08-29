<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi <?= esc($icon) ?> me-2 text-primary"></i><?= esc($title) ?></h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url($slug) ?>"><?= esc($title) ?></a></li>
        <li class="breadcrumb-item active">#<?= (int) $row['id'] ?></li>
      </ol>
    </nav>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= base_url($slug . '/edit/' . $row['id']) ?>" class="btn btn-sm btn-fm-primary">Edit</a>
    <?php if ($slug === 'rent-payments' && ($row['status'] ?? '') !== 'paid'): ?>
      <form method="post" action="<?= base_url($slug . '/action/' . $row['id'] . '/mark-paid') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
      </form>
    <?php endif; ?>
    <?php if ($slug === 'cheques' && in_array($row['status'] ?? '', ['pending', 'deposited'], true)): ?>
      <form method="post" action="<?= base_url($slug . '/action/' . $row['id'] . '/clear') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-success">Mark Cleared</button>
      </form>
      <form method="post" action="<?= base_url($slug . '/action/' . $row['id'] . '/bounce') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-warning">Mark Bounced</button>
      </form>
    <?php endif; ?>
    <?php if ($slug === 'landlord-payouts' && ($row['status'] ?? '') !== 'paid'): ?>
      <form method="post" action="<?= base_url($slug . '/action/' . $row['id'] . '/mark-paid') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
      </form>
    <?php endif; ?>
    <a href="<?= base_url($slug) ?>" class="btn btn-sm btn-outline-secondary">Back to List</a>
  </div>
</div>

<div class="row g-3">
  <?php foreach ($sections as $section): ?>
  <div class="col-lg-6">
    <div class="fm-card p-4 h-100">
      <h6 class="text-uppercase small text-muted mb-3"><?= esc($section['label']) ?></h6>
      <dl class="row small mb-0">
        <?php foreach ($section['fields'] as $field): ?>
        <dt class="col-sm-4 text-muted"><?= esc($field['label']) ?></dt>
        <dd class="col-sm-8"><?= esc($field['value']) ?: '—' ?></dd>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($slug === 'crm'): ?>
<div class="fm-card p-4 mt-3">
  <h6 class="mb-3">Log Activity</h6>
  <?= form_open(base_url('crm/activity/' . $row['id'])) ?>
  <div class="row g-2">
    <div class="col-md-3">
      <select name="activity_type" class="form-select form-select-sm">
        <option value="call">Call</option>
        <option value="email">Email</option>
        <option value="visit">Visit</option>
        <option value="note">Note</option>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject">
    </div>
    <div class="col-md-5">
      <input type="text" name="description" class="form-control form-control-sm" placeholder="Notes">
    </div>
  </div>
  <button type="submit" class="btn btn-sm btn-fm-primary mt-2">Add Activity</button>
  <?= form_close() ?>
</div>
<?php endif; ?>

<?php if ($slug === 'crm' && ! empty($crmActivities)): ?>
<div class="fm-card p-4 mt-3">
  <h6 class="mb-3">Recent Activities</h6>
  <ul class="list-unstyled small mb-0">
    <?php foreach ($crmActivities as $a): ?>
      <li class="mb-2 border-bottom pb-2">
        <strong><?= esc($a['activity_type'] ?? 'Activity') ?></strong>
        — <?= esc($a['subject'] ?? $a['description'] ?? '') ?>
        <span class="text-muted"><?= esc($a['created_at'] ?? '') ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
