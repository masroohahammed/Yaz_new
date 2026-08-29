<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-tools me-2 text-primary"></i>My Maintenance Tickets</h1></div>
  <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-fm-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>New Ticket
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (empty($tickets)): ?>
<div class="fm-card">
  <div class="fm-card-body text-center py-5">
    <i class="bi bi-tools fs-1 text-muted opacity-50 d-block mb-3"></i>
    <h5 class="text-muted">No maintenance tickets yet</h5>
    <p class="text-muted small">Have a maintenance issue? Let us know!</p>
    <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-fm-primary">
      <i class="bi bi-plus-lg me-1"></i>Submit a Ticket
    </a>
  </div>
</div>
<?php else: ?>
<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr>
            <th>Ticket #</th>
            <th>Title</th>
            <th>Category</th>
            <th>Property / Unit</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
          <td class="fw-semibold small text-primary"><?= esc($t['ticket_number']) ?></td>
          <td class="small"><?= esc($t['title'] ?? $t['category'] ?? '—') ?></td>
          <td class="small"><?= esc($t['category'] ?? '—') ?></td>
          <td class="small">
            <?= esc($t['facility_name'] ?? '—') ?>
            <?php if (! empty($t['unit_number'])): ?> · <?= esc($t['unit_number']) ?><?php endif; ?>
          </td>
          <td>
            <span class="fm-badge badge-priority-<?= esc($t['priority']) ?>">
              <?= ucfirst(esc($t['priority'])) ?>
            </span>
          </td>
          <td>
            <span class="fm-badge badge-status-<?= esc($t['status']) ?>">
              <?= ucfirst(str_replace('_', ' ', esc($t['status']))) ?>
            </span>
          </td>
          <td class="small text-muted">
            <?= $t['created_at'] ? date('d M Y', strtotime($t['created_at'])) : '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
