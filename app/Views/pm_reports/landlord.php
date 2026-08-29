<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$currency = $currency ?? 'QAR';
$ov = $overview ?? [];
$landlordId = (int) ($landlordId ?? 0);
$qs = http_build_query(array_filter([
  'landlord' => $landlordId ?: null,
  'facility' => ! empty($facilityId) ? $facilityId : null,
  'from' => $from ?? null,
  'to' => $to ?? null,
  'expense_category' => ! empty($expCat) ? $expCat : null,
]));
$export = static fn (string $sec, string $fmt = 'csv') => base_url('reports/pm/landlord/export/' . $sec) . '?' . $qs . '&format=' . $fmt;
$exportBtns = static function (string $sec) use ($export): void { ?>
    <a class="btn btn-fm-outline btn-sm" href="<?= $export($sec, 'csv') ?>">CSV</a>
    <a class="btn btn-fm-outline btn-sm" href="<?= $export($sec, 'excel') ?>">Excel</a>
    <a class="btn btn-fm-outline btn-sm" href="<?= $export($sec, 'pdf') ?>">PDF</a>
<?php };
$jump = [
  ['overview', 'Overview', 'bi-speedometer2'],
  ['units', 'Units', 'bi-door-closed'],
  ['tenants', 'Tenants', 'bi-people'],
  ['collections', 'Collections', 'bi-cash-stack'],
  ['pending', 'Pending', 'bi-hourglass-split'],
  ['cheques', 'Cheques', 'bi-bank'],
  ['maintenance', 'Maintenance', 'bi-tools'],
  ['revenue', 'Revenue', 'bi-graph-up'],
  ['expenses', 'Expenses', 'bi-wallet2'],
  ['pnl', 'P&L', 'bi-pie-chart'],
  ['contracts', 'Contracts', 'bi-file-earmark-text'],
  ['occupancy', 'Occupancy', 'bi-grid'],
  ['statement', 'Statement', 'bi-journal-text'],
];
$activeTab = 'overview';
foreach ($jump as [$tid]) {
  if (($tabHash ?? '') === $tid) {
    $activeTab = $tid;
    break;
  }
}
?>
<style>
  .lr-tab-pane .fm-card { margin-bottom: 0; }
  .lr-tab-pane .fm-table th, .lr-tab-pane .fm-table td { padding: .45rem .65rem; font-size: .78rem; }
  .lr-section-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .65rem; }
  .lr-section-head h5 { margin: 0; font-size: 1rem; font-weight: 600; }
  @media print {
    .fm-entity-tabs, .no-print { display: none !important; }
    .tab-pane { display: block !important; opacity: 1 !important; }
  }
</style>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-person-badge me-2"></i>Landlord Reports</h1>
    <div class="small text-muted">
      <?= ! empty($landlord['full_name']) ? esc($landlord['full_name']) : 'Select a landlord' ?>
      <?php if (! empty($landlord['short_code'])): ?>
      · <span class="badge bg-light text-dark border"><?= esc($landlord['short_code']) ?></span>
      <?php endif; ?>
      · <?= esc($from) ?> → <?= esc($to) ?>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap no-print">
    <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">PM Reports Hub</a>
    <?php if ($landlordId): ?>
    <a href="<?= base_url('landlords/' . $landlordId) ?>" class="btn btn-fm-outline btn-sm">Landlord profile</a>
    <a href="<?= base_url('finance/pm/owner-statement/' . $landlordId . '?from=' . urlencode((string) $from) . '&to=' . urlencode((string) $to)) ?>" class="btn btn-fm-outline btn-sm">Ledger statement</a>
    <?php endif; ?>
    <button type="button" class="btn btn-fm-outline btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
  </div>
</div>

<div class="fm-card mb-3 no-print"><div class="fm-card-body py-2">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label small">Landlord</label>
      <select name="landlord" class="form-select form-select-sm" <?= ! empty($forcedLandlord) ? 'disabled' : '' ?>>
        <?php foreach ($landlords as $l): ?>
        <option value="<?= (int) $l['id'] ?>" <?= $landlordId === (int) $l['id'] ? 'selected' : '' ?>><?= esc($l['full_name']) ?><?php if (! empty($l['short_code'])): ?> (<?= esc($l['short_code']) ?>)<?php endif; ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (! empty($forcedLandlord)): ?><input type="hidden" name="landlord" value="<?= $landlordId ?>"><?php endif; ?>
    </div>
    <div class="col-md-3">
      <label class="form-label small">Property</label>
      <select name="facility" class="form-select form-select-sm">
        <option value="">All properties</option>
        <?php foreach ($properties as $p): ?>
        <option value="<?= (int) $p['id'] ?>" <?= (int) $facilityId === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
    <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
    <div class="col-md-3">
      <label class="form-label small">Expense category</label>
      <select name="expense_category" class="form-select form-select-sm">
        <option value="">All categories</option>
        <?php foreach (($expenseCategories ?? []) as $val => $lab): ?>
        <option value="<?= esc($val) ?>" <?= ($expCat ?? '') === $val ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Apply</button></div>
  </form>
</div></div>

<ul class="nav fm-entity-tabs mb-0 no-print" role="tablist" id="lr-tabs">
  <?php foreach ($jump as [$id, $label, $icon]): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $activeTab === $id ? 'active' : '' ?>" id="tab-<?= $id ?>-tab"
       data-bs-toggle="tab" href="#tab-<?= $id ?>" role="tab" data-tab-id="<?= $id ?>">
      <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<div class="tab-content fm-tab-pane lr-tab-pane">
<section id="tab-overview" class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" role="tabpanel">
  <h5 class="mb-2">Overview</h5>
  <div class="row g-2 mb-2">
    <?php
    $kpis = [
      ['Properties', $ov['properties'] ?? 0, 'kpi-blue'],
      ['Units', $ov['units'] ?? 0, 'kpi-teal'],
      ['Occupied', $ov['occupied'] ?? 0, 'kpi-green'],
      ['Vacant', $ov['vacant'] ?? 0, 'kpi-orange'],
      ['Maint. units', $ov['maintenance_units'] ?? 0, 'kpi-red'],
      ['Collected', number_format((float) ($ov['rent_collected'] ?? 0), 0) . ' ' . $currency, 'kpi-green'],
      ['Pending', number_format((float) ($ov['rent_pending'] ?? 0), 0) . ' ' . $currency, 'kpi-orange'],
      ['Overdue', number_format((float) ($ov['rent_overdue'] ?? 0), 0) . ' ' . $currency, 'kpi-red'],
      ['Collection %', ($ov['collection_pct'] ?? 0) . '%', 'kpi-primary'],
      ['Expenses', number_format((float) ($ov['expenses'] ?? 0), 0) . ' ' . $currency, 'kpi-red'],
      ['Net income', number_format((float) ($ov['net_income'] ?? 0), 0) . ' ' . $currency, 'kpi-primary'],
      ['Open maint.', $ov['maint_open'] ?? 0, 'kpi-orange'],
      ['Cheques pending', $ov['cheques_pending'] ?? 0, 'kpi-gold'],
      ['Cheques cleared', $ov['cheques_cleared'] ?? 0, 'kpi-green'],
      ['Bounced', $ov['cheques_bounced'] ?? 0, 'kpi-red'],
      ['Upcoming cheques', $ov['cheques_upcoming'] ?? 0, 'kpi-teal'],
    ];
    foreach ($kpis as [$label, $val, $cls]): ?>
    <div class="col-6 col-md-3 col-xl-2">
      <div class="kpi-card <?= $cls ?>"><div class="kpi-label"><?= $label ?></div><div class="kpi-value" style="font-size:1.05rem"><?= $val ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="d-flex gap-2 no-print mb-2"><?php $exportBtns('overview'); ?></div>
  <p class="small text-muted mb-0">Properties and buildings are the same <code>facilities</code> records. Rent uses <code>lease_payments</code>; expenses use approved <code>expenses</code>.</p>
</section>

<section id="tab-units" class="tab-pane fade <?= $activeTab === 'units' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Unit reports</h5>
    <div class="d-flex gap-2 no-print">
      <?php $exportBtns('units'); ?>
      <a class="btn btn-fm-outline btn-sm" href="<?= base_url('reports/pm/occupancy?' . $qs) ?>">Portfolio occupancy</a>
    </div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Landlord</th><th>Property</th><th>Unit</th><th>Type</th><th>Floor</th><th>Area</th><th>Tenant</th><th>Rent</th><th>Contract</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($units as $u): ?>
      <tr>
        <td class="small fw-semibold"><?= esc($landlord['short_code'] ?? '—') ?></td>
        <td class="small"><?= esc($u['facility_name'] ?? '') ?></td>
        <td class="small fw-semibold"><?= esc($u['unit_number'] ?? '') ?></td>
        <td class="small"><?= esc($u['unit_type'] ?? '—') ?></td>
        <td class="small"><?= esc($u['floor'] ?? '—') ?></td>
        <td class="small"><?= esc($u['area_sqft'] ?? '—') ?></td>
        <td class="small"><?= esc($u['lease_tenant'] ?? $u['tenant_name'] ?? '—') ?></td>
        <td class="small"><?= number_format((float) ($u['lease_rent'] ?? $u['rent_amount'] ?? 0), 0) ?></td>
        <td class="small"><?= esc($u['lease_start'] ?? $u['contract_start'] ?? '—') ?> → <?= esc($u['lease_end'] ?? $u['contract_end'] ?? '—') ?></td>
        <td><span class="fm-badge badge-status-<?= esc($u['status'] ?? '') ?>"><?= esc($u['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($units)): ?><tr><td colspan="10" class="text-muted text-center py-3">No units for this landlord/property filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-tenants" class="tab-pane fade <?= $activeTab === 'tenants' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Tenant reports</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('tenants'); ?></div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Tenant</th><th>Phone</th><th>Property</th><th>Unit</th><th>Contract</th><th>Rent</th><th>Deposit</th><th>Lease</th></tr></thead>
      <tbody>
      <?php foreach ($tenants as $t): ?>
      <tr>
        <td class="small fw-semibold"><?= esc($t['full_name'] ?? '') ?></td>
        <td class="small"><?= esc($t['phone'] ?? '—') ?></td>
        <td class="small"><?= esc($t['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($t['unit_number'] ?? '—') ?></td>
        <td class="small"><a href="<?= base_url('contracts/' . (int) ($t['contract_id'] ?? 0)) ?>"><?= esc($t['contract_number'] ?? '') ?></a></td>
        <td class="small"><?= number_format((float) ($t['rent_amount'] ?? 0), 0) ?></td>
        <td class="small"><?= number_format((float) ($t['security_deposit'] ?? 0), 0) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($t['lease_status'] ?? '') ?>"><?= esc($t['lease_status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tenants)): ?><tr><td colspan="8" class="text-muted text-center py-3">No tenants on this landlord’s leases.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-collections" class="tab-pane fade <?= $activeTab === 'collections' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Collection / rent</h5>
    <div class="d-flex gap-2 no-print">
      <?php $exportBtns('collections'); ?>
      <a class="btn btn-fm-outline btn-sm" href="<?= base_url('reports/pm/payments?' . $qs) ?>">Payments report</a>
    </div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Payment #</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Period</th><th>Due</th><th>Paid</th><th>Method</th><th>Ref</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
      <tr>
        <td class="small fw-semibold"><?= esc($p['payment_number'] ?? $p['id']) ?></td>
        <td class="small"><?= esc($p['tenant_name'] ?? '—') ?></td>
        <td class="small"><?= esc($p['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($p['unit_number'] ?? '—') ?></td>
        <td class="small"><?= esc($p['period_from'] ?? '') ?> → <?= esc($p['period_to'] ?? '') ?></td>
        <td class="small"><?= esc($p['due_date'] ?? '') ?></td>
        <td class="small"><?= esc($p['payment_date'] ?? '—') ?></td>
        <td class="small"><?= esc($p['payment_method'] ?? '') ?></td>
        <td class="small"><?= esc($p['reference_no'] ?? $p['transfer_reference'] ?? '—') ?></td>
        <td class="small"><?= number_format((float) ($p['amount'] ?? 0), 2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($p['status'] ?? '') ?>"><?= esc($p['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($payments)): ?><tr><td colspan="11" class="text-muted text-center py-3">No lease payments in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-pending" class="tab-pane fade <?= $activeTab === 'pending' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Pending collections</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('pending'); ?></div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Tenant</th><th>Property</th><th>Unit</th><th>Period</th><th>Due</th><th>Amount</th><th>Days overdue</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($pending as $p): $over = (int) ($p['days_overdue'] ?? 0); ?>
      <tr class="<?= $over > 0 ? 'table-danger' : '' ?>">
        <td class="small"><?= esc($p['tenant_name'] ?? '—') ?></td>
        <td class="small"><?= esc($p['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($p['unit_number'] ?? '—') ?></td>
        <td class="small"><?= esc($p['period_from'] ?? '') ?> → <?= esc($p['period_to'] ?? '') ?></td>
        <td class="small"><?= esc($p['due_date'] ?? '') ?></td>
        <td class="small"><?= number_format((float) ($p['amount'] ?? 0), 2) ?></td>
        <td class="small <?= $over > 0 ? 'text-danger fw-bold' : '' ?>"><?= $over ?></td>
        <td><span class="fm-badge badge-status-<?= esc($p['status'] ?? '') ?>"><?= esc($p['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pending)): ?><tr><td colspan="8" class="text-muted text-center py-3">No pending or overdue collections.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-cheques" class="tab-pane fade <?= $activeTab === 'cheques' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Cheque reports</h5>
    <div class="d-flex gap-2 no-print">
      <?php $exportBtns('cheques'); ?>
      <a class="btn btn-fm-outline btn-sm" href="<?= base_url('reports/pm/cheques?' . $qs) ?>">PDC report</a>
    </div>
  </div>
  <div class="small text-muted mb-2">Upcoming <?= (int) ($ov['cheques_upcoming'] ?? 0) ?> · Overdue <?= (int) ($ov['cheques_overdue'] ?? 0) ?> · Cleared <?= (int) ($ov['cheques_cleared'] ?? 0) ?> · Bounced <?= (int) ($ov['cheques_bounced'] ?? 0) ?></div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Tenant</th><th>Property</th><th>Cheque #</th><th>Bank</th><th>Cheque date</th><th>Deposited</th><th>Cleared</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($cheques as $c):
        $upcoming = ($c['status'] ?? '') === 'pending' && ($c['cheque_date'] ?? '') >= date('Y-m-d');
        $overdueChq = ($c['status'] ?? '') === 'pending' && ($c['cheque_date'] ?? '') < date('Y-m-d');
      ?>
      <tr class="<?= $overdueChq ? 'table-danger' : ($upcoming ? 'table-warning' : '') ?>">
        <td class="small"><?= esc($c['tenant_name'] ?? '—') ?></td>
        <td class="small"><?= esc($c['facility_name'] ?? '') ?></td>
        <td class="small fw-semibold"><?= esc($c['cheque_no'] ?? '') ?></td>
        <td class="small"><?= esc($c['bank_name'] ?? '—') ?></td>
        <td class="small"><?= esc($c['cheque_date'] ?? '') ?></td>
        <td class="small"><?= esc($c['deposit_date'] ?? '—') ?></td>
        <td class="small"><?= esc($c['clearance_date'] ?? '—') ?></td>
        <td class="small"><?= number_format((float) ($c['amount'] ?? 0), 2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($c['status'] ?? '') ?>"><?= esc($c['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($cheques)): ?><tr><td colspan="9" class="text-muted text-center py-3">No cheques in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-maintenance" class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Maintenance</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('maintenance'); ?></div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Ticket</th><th>Property</th><th>Unit</th><th>Issue</th><th>Priority</th><th>Requested</th><th>Technician</th><th>Completed</th><th>Cost</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($maintenance as $m): ?>
      <tr>
        <td class="small fw-semibold"><a href="<?= base_url('maintenance/' . (int) $m['id']) ?>"><?= esc($m['ticket_number'] ?? '') ?></a></td>
        <td class="small"><?= esc($m['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($m['unit_number'] ?? '—') ?></td>
        <td class="small"><?= esc($m['category'] ?? '') ?> — <?= esc(mb_strimwidth((string) ($m['description'] ?? ''), 0, 60, '…')) ?></td>
        <td class="small"><?= esc($m['priority'] ?? '') ?></td>
        <td class="small"><?= esc($m['created_at'] ?? '') ?></td>
        <td class="small"><?= esc($m['technician_name'] ?? '—') ?></td>
        <td class="small"><?= esc($m['completed_at'] ?? '—') ?></td>
        <td class="small"><?= number_format((float) ($m['actual_cost'] ?? 0), 2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($m['status'] ?? '') ?>"><?= esc($m['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($maintenance)): ?><tr><td colspan="10" class="text-muted text-center py-3">No maintenance requests in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-revenue" class="tab-pane fade <?= $activeTab === 'revenue' ? 'show active' : '' ?>" role="tabpanel">
  <h5 class="mb-2">Revenue</h5>
  <?php $p = $pnl; ?>
  <div class="row g-2">
    <?php foreach ([['Rental', $p['rental']], ['Parking', $p['parking']], ['Service', $p['service']], ['Utility', $p['utility']], ['Late fees', $p['late']], ['Other', $p['other']], ['Collected', $p['collected']], ['Pending', $p['pending']]] as [$lab, $amt]): ?>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-label"><?= $lab ?></div><div class="kpi-value" style="font-size:1rem"><?= number_format((float) $amt, 2) ?></div></div></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="tab-expenses" class="tab-pane fade <?= $activeTab === 'expenses' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Expenses</h5>
    <div class="d-flex gap-2 no-print">
      <?php $exportBtns('expenses'); ?>
      <a class="btn btn-fm-outline btn-sm" href="<?= base_url('reports/pm/expenses?' . $qs) ?>">Expense report</a>
    </div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Date</th><th>Property</th><th>Category</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($expenses as $e): ?>
      <tr>
        <td class="small"><?= esc($e['expense_date'] ?? '') ?></td>
        <td class="small"><?= esc($e['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($e['category'] ?? '') ?></td>
        <td class="small"><?= esc($e['description'] ?? '') ?></td>
        <td class="small"><?= number_format((float) ($e['amount'] ?? 0), 2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($e['status'] ?? '') ?>"><?= esc($e['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($expenses)): ?><tr><td colspan="6" class="text-muted text-center py-3">No expenses in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-pnl" class="tab-pane fade <?= $activeTab === 'pnl' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Profit &amp; loss</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('pnl'); ?></div>
  </div>
  <div class="fm-card"><div class="fm-card-body">
    <div class="row small">
      <div class="col-md-6">
        <div class="d-flex justify-content-between"><span>Rental income</span><strong><?= number_format((float) $p['rental'], 2) ?></strong></div>
        <div class="d-flex justify-content-between"><span>Other income</span><strong><?= number_format((float) ($p['other'] + $p['parking'] + $p['service'] + $p['utility'] + $p['late']), 2) ?></strong></div>
        <div class="d-flex justify-content-between border-top pt-1 mt-1"><span>Gross collected</span><strong><?= number_format((float) $p['collected'], 2) ?></strong></div>
      </div>
      <div class="col-md-6">
        <div class="d-flex justify-content-between"><span>Operating expenses</span><strong><?= number_format((float) $p['expenses'], 2) ?></strong></div>
        <div class="d-flex justify-content-between"><span>Of which maintenance</span><strong><?= number_format((float) $p['maintenance'], 2) ?></strong></div>
        <div class="d-flex justify-content-between border-top pt-1 mt-1"><span>Net income</span><strong><?= number_format((float) $p['net'], 2) ?></strong></div>
        <div class="d-flex justify-content-between"><span>Margin</span><strong><?= number_format((float) $p['margin'], 1) ?>%</strong></div>
      </div>
    </div>
    <?php if (! empty($p['by_group'])): ?>
    <div class="row small mt-3">
      <?php foreach ($p['by_group'] as $g => $amt): ?>
      <div class="col-6 col-md-3"><div class="d-flex justify-content-between"><span><?= esc(ucfirst(str_replace('_', ' ', (string) $g))) ?></span><strong><?= number_format((float) $amt, 2) ?></strong></div></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <p class="small text-muted mb-0 mt-2">Uses paid <code>lease_payments</code> and approved <code>expenses</code>. Ledger P&amp;L per property remains at <?= esc('finance/pm/property') ?>.</p>
  </div></div>
</section>

<section id="tab-contracts" class="tab-pane fade <?= $activeTab === 'contracts' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Contracts / leases</h5>
    <div class="d-flex gap-2 no-print">
      <?php $exportBtns('contracts'); ?>
      <a class="btn btn-fm-outline btn-sm" href="<?= current_url() . '?' . $qs . '&expiry_days=30#contracts' ?>">30d</a>
      <a class="btn btn-fm-outline btn-sm" href="<?= current_url() . '?' . $qs . '&expiry_days=60#contracts' ?>">60d</a>
      <a class="btn btn-fm-outline btn-sm" href="<?= current_url() . '?' . $qs . '&expiry_days=90#contracts' ?>">90d</a>
      <a class="btn btn-fm-outline btn-sm" href="<?= base_url('reports/pm/leases?' . $qs) ?>">Lease expiry</a>
    </div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Contract</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Start</th><th>End</th><th>Monthly</th><th>Annual</th><th>Deposit</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($contracts as $c):
        $rent = (float) ($c['rent_amount'] ?? 0);
        $days = ! empty($c['end_date']) ? (int) ceil((strtotime((string) $c['end_date']) - time()) / 86400) : null;
        $soon = $days !== null && $days <= 90 && ($c['status'] ?? '') === 'active';
      ?>
      <tr class="<?= $soon && $days <= 30 ? 'table-danger' : ($soon ? 'table-warning' : '') ?>">
        <td class="small fw-semibold"><a href="<?= base_url('contracts/' . (int) $c['id']) ?>"><?= esc($c['contract_number'] ?? '') ?></a></td>
        <td class="small"><?= esc($c['tenant_name'] ?? '') ?></td>
        <td class="small"><?= esc($c['facility_name'] ?? '') ?></td>
        <td class="small"><?= esc($c['unit_number'] ?? '—') ?></td>
        <td class="small"><?= esc($c['start_date'] ?? '') ?></td>
        <td class="small"><?= esc($c['end_date'] ?? '') ?><?php if ($soon): ?> <span class="text-danger"><?= $days ?>d</span><?php endif; ?></td>
        <td class="small"><?= number_format($rent, 0) ?></td>
        <td class="small"><?= number_format($rent * 12, 0) ?></td>
        <td class="small"><?= number_format((float) ($c['security_deposit'] ?? 0), 0) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($c['status'] ?? '') ?>"><?= esc($c['status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($contracts)): ?><tr><td colspan="10" class="text-muted text-center py-3">No leases for this filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>

<section id="tab-occupancy" class="tab-pane fade <?= $activeTab === 'occupancy' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Occupancy</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('occupancy'); ?></div>
  </div>
  <div class="small mb-2">Occupancy <?= esc($occupancy['occupancy_pct'] ?? 0) ?>% · Vacancy <?= esc($occupancy['vacancy_pct'] ?? 0) ?>%</div>
  <div class="fm-card mb-2"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Property</th><th>Units</th><th>Occupied</th><th>Vacant</th><th>Maintenance</th><th>%</th></tr></thead>
      <tbody>
      <?php foreach (($occupancy['rows'] ?? []) as $r): ?>
      <tr>
        <td class="small"><?= esc($r['name'] ?? '') ?></td>
        <td><?= (int) ($r['total_units'] ?? 0) ?></td>
        <td><?= (int) ($r['occupied'] ?? 0) ?></td>
        <td><?= (int) ($r['vacant'] ?? 0) ?></td>
        <td><?= (int) ($r['maintenance'] ?? 0) ?></td>
        <td><?= esc($r['occupancy_pct'] ?? 0) ?>%</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <?php if (! empty($occupancy['trend'])): ?>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Month</th><th>Occupied units</th><th>Active leases</th><th>Total units</th><th>Occupancy %</th></tr></thead>
      <tbody>
      <?php foreach ($occupancy['trend'] as $tr): ?>
      <tr>
        <td class="small"><?= esc($tr['month'] ?? '') ?></td>
        <td><?= (int) ($tr['occupied_units'] ?? 0) ?></td>
        <td><?= (int) ($tr['active_leases'] ?? 0) ?></td>
        <td><?= (int) ($tr['total_units'] ?? 0) ?></td>
        <td><?= esc($tr['occupancy_pct'] ?? 0) ?>%</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <p class="small text-muted mt-1 mb-0">Monthly trend is derived from <code>lease_contracts</code> date overlap against current unit count. There is no occupancy snapshot table.</p>
  <?php endif; ?>
</section>

<section id="tab-statement" class="tab-pane fade <?= $activeTab === 'statement' ? 'show active' : '' ?>" role="tabpanel">
  <div class="lr-section-head">
    <h5 class="mb-0">Landlord statement</h5>
    <div class="d-flex gap-2 no-print"><?php $exportBtns('statement'); ?></div>
  </div>
  <div class="fm-card"><div class="fm-card-body p-0 table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Date</th><th>Property</th><th>Unit</th><th>Description</th><th>Income</th><th>Expense</th><th>Payment</th><th>Balance</th></tr></thead>
      <tbody>
      <?php foreach ($statement as $s): ?>
      <tr>
        <td class="small"><?= esc($s['entry_date'] ?? '') ?></td>
        <td class="small"><?= esc($s['facility'] ?? '') ?></td>
        <td class="small"><?= esc($s['unit'] ?? '') ?></td>
        <td class="small"><?= esc($s['description'] ?? '') ?></td>
        <td class="small"><?= number_format((float) ($s['income'] ?? 0), 2) ?></td>
        <td class="small"><?= number_format((float) ($s['expense'] ?? 0), 2) ?></td>
        <td class="small"><?= number_format((float) ($s['payment'] ?? 0), 2) ?></td>
        <td class="small fw-semibold"><?= number_format((float) ($s['balance'] ?? 0), 2) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($statement)): ?><tr><td colspan="8" class="text-muted text-center py-3">No statement lines in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</section>
</div>

<script>
(function () {
  var tabIds = <?= json_encode(array_column($jump, 0)) ?>;
  function showTab(id) {
    if (tabIds.indexOf(id) === -1) return;
    var link = document.querySelector('#lr-tabs a[data-tab-id="' + id + '"]');
    if (link && window.bootstrap && bootstrap.Tab) {
      bootstrap.Tab.getOrCreateInstance(link).show();
    }
  }
  function syncHash() {
    var id = (location.hash || '').replace(/^#/, '');
    if (id) showTab(id);
  }
  document.querySelectorAll('#lr-tabs a[data-tab-id]').forEach(function (a) {
    a.addEventListener('shown.bs.tab', function () {
      var id = a.getAttribute('data-tab-id');
      if (id && location.hash !== '#' + id) {
        history.replaceState(null, '', '#' + id);
      }
    });
  });
  syncHash();
  window.addEventListener('hashchange', syncHash);
})();
</script>
<?= $this->endSection() ?>
