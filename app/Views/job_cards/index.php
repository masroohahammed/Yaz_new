<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-card-checklist me-2 text-primary"></i>Job Cards</h1>
    <p class="text-muted small mb-0">Total: <?= number_format($total) ?></p>
  </div>
</div>

<!-- Filters -->
<div class="fm-card mb-4 p-3">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search JC#, WO#…" value="<?= esc($filters['search'] ?? '') ?>">
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['draft'=>'Draft','in_progress'=>'In Progress','completed'=>'Completed','approved'=>'Approved'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <a href="/job-cards" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="fm-card">
    <div class="table-responsive">
        <table class="table table-fm mb-0">
            <thead>
                <tr>
                    <th>JC #</th>
                    <th>Work Order</th>
                    <th>Facility</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Supervisor</th>
                    <th>Technician</th>
                    <th>Scheduled</th>
                    <th>Hours</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobCards)): ?>
                    <tr><td colspan="10" class="text-center py-5 text-muted">No job cards found.</td></tr>
                <?php else: ?>
                    <?php foreach ($jobCards as $jc): ?>
                        <tr>
                            <td><a href="/job-cards/<?= $jc['id'] ?>" class="fw-medium text-decoration-none"><?= esc($jc['jc_number']) ?></a></td>
                            <td>
                                <a href="/work-orders/<?= $jc['wo_id'] ?? '' ?>" class="text-decoration-none small">
                                    <?= esc($jc['wo_number']) ?>
                                </a>
                                <div class="text-muted" style="font-size:.75rem;max-width:180px" class="text-truncate"><?= esc($jc['wo_title']) ?></div>
                            </td>
                            <td class="small text-muted"><?= esc($jc['facility_name']) ?></td>
                            <td><span class="badge-status badge-<?= $jc['priority'] ?>"><?= ucfirst($jc['priority']) ?></span></td>
                            <td><span class="badge-status badge-<?= $jc['status'] ?>"><?= ucwords(str_replace('_',' ',$jc['status'])) ?></span></td>
                            <td class="small"><?= esc($jc['supervisor_name'] ?? '—') ?></td>
                            <td class="small"><?= esc($jc['technician_name'] ?? '—') ?></td>
                            <td class="small"><?= $jc['scheduled_date'] ? date('d M Y', strtotime($jc['scheduled_date'])) : '—' ?></td>
                            <td class="small"><?= $jc['labor_hours'] ?? '—' ?></td>
                            <td><a href="/job-cards/<?= $jc['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <?php if ($total > $perPage): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <small class="text-muted">Showing <?= ($currentPage-1)*$perPage+1 ?>–<?= min($currentPage*$perPage,$total) ?> of <?= $total ?></small>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
