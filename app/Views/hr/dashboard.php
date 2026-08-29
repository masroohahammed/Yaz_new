<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'dashboard']) ?>

<div class="page-header mb-3">
  <h1 class="h4 mb-0"><i class="bi bi-person-vcard me-2"></i>HR Dashboard</h1>
  <p class="text-muted small">Workforce overview, approvals, and GL-linked payroll & expenses</p>
</div>

<div class="hr-stat-grid mb-4">
  <?php foreach ([
    ['Total Employees', $stats['total_employees'], 'bi-people'],
    ['Active', $stats['active'], 'bi-person-check'],
    ['On Leave', $stats['on_leave'], 'bi-calendar-x'],
    ['New Joiners', $stats['new_joiners'], 'bi-person-plus'],
    ['Pending Approvals', $stats['pending_approvals'], 'bi-hourglass-split'],
    ['Expiring Documents', $stats['expiring_docs'], 'bi-file-earmark-excel'],
  ] as [$label, $val, $icon]): ?>
  <div class="hr-stat">
    <div class="d-flex align-items-center gap-2">
      <i class="bi <?= $icon ?> text-muted"></i>
      <div>
        <div class="label"><?= esc($label) ?></div>
        <div class="value"><?= (int) $val ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="hr-page-card">
  <h6 class="text-muted text-uppercase small mb-3">HRMS modules</h6>
  <div class="row g-2">
    <?php foreach ([
      ['employees', 'Employees', 'bi-person-badge', 'hr/employees'],
      ['leave', 'Leave', 'bi-calendar-check', 'hr/leave'],
      ['payroll', 'Payroll', 'bi-cash-stack', 'hr/payroll'],
      ['performance', 'Performance', 'bi-graph-up-arrow', 'hr/performance'],
      ['expenses', 'Expenses', 'bi-receipt', 'hr/expenses'],
      ['assets', 'Assets', 'bi-laptop', 'hr/assets'],
      ['documents', 'Documents', 'bi-folder2-open', 'documents'],
    ] as [$key, $label, $icon, $url]): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a href="<?= base_url($url) ?>" class="d-block text-decoration-none p-3 border rounded-3 hr-module-tile">
        <i class="bi <?= $icon ?> me-2"></i><strong><?= esc($label) ?></strong>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-3 pt-3 border-top">
    <a href="<?= base_url('finance/payroll-finance') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-journal-text me-1"></i>Finance payroll GL view</a>
    <a href="<?= base_url('employees') ?>" class="btn btn-sm btn-fm-outline ms-1"><i class="bi bi-people me-1"></i>FM attendance workforce</a>
  </div>
</div>

<?= $this->endSection() ?>
