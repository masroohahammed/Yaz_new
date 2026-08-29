<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-clipboard2-check me-2 text-primary"></i>Work Orders</h1>
    <p class="text-muted small mb-0">Total: <?= number_format($total) ?></p>
  </div>
  <?php if (in_array(session('user_role'), ['super_admin','facility_manager'])): ?>
  <a href="<?= base_url('work-orders/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Work Order</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="fm-card mb-4 p-3">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-3">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search WO#, title…" value="<?= esc($filters['search'] ?? '') ?>">
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['new','assigned','in_progress','on_hold','completed','closed','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="priority" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <?php foreach (['critical','high','medium','low'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="workflow_stage" class="form-select form-select-sm">
                <option value="">All Stages</option>
                <?php foreach ([
                    'complaint_received'=>'1. Complaint Received',
                    'complaint_verification'=>'2. Verification',
                    'approval_process'=>'3. Approval',
                    'converted_to_wo'=>'4. Converted',
                    'assigned_to_supervisor'=>'5. Supervisor Assigned',
                    'job_card_created'=>'6. Job Card Created',
                    'technician_assigned'=>'7. Technician Assigned',
                    'planning_scheduling'=>'8. Scheduled',
                    'work_execution'=>'9. In Execution',
                    'inspection_qc'=>'10. QC Check',
                    'job_completed'=>'11. Completed',
                    'wo_closed'=>'12. Closed',
                ] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['workflow_stage'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="facility_id" class="form-select form-select-sm">
                <option value="">All Facilities</option>
                <?php foreach ($facilities as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= ($filters['facility_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <a href="/work-orders" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="fm-card">
    <div class="table-responsive">
        <table class="table table-fm mb-0">
            <thead>
                <tr>
                    <th>WO #</th>
                    <th>Title</th>
                    <th>Facility</th>
                    <th>Priority</th>
                    <th>Stage</th>
                    <th>Status</th>
                    <th>Supervisor</th>
                    <th>Technician</th>
                    <th>SLA Due</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workOrders)): ?>
                    <tr><td colspan="11" class="text-center py-5 text-muted">No work orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($workOrders as $wo): ?>
                        <?php
                        $slaBreach = $wo['sla_breached'] || (! empty($wo['sla_due']) && strtotime($wo['sla_due']) < time() && ! in_array($wo['status'], ['completed','closed','cancelled']));
                        ?>
                        <tr class="<?= $slaBreach ? 'table-danger' : '' ?>">
                            <td><a href="/work-orders/<?= $wo['id'] ?>" class="fw-medium text-decoration-none"><?= esc($wo['wo_number']) ?></a></td>
                            <td class="text-truncate" style="max-width:220px"><?= esc($wo['title']) ?></td>
                            <td class="text-muted small"><?= esc($wo['facility_name']) ?></td>
                            <td><span class="badge-status badge-<?= $wo['priority'] ?>"><?= ucfirst($wo['priority']) ?></span></td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border small">
                                    <?= ucwords(str_replace('_',' ', $wo['workflow_stage'])) ?>
                                </span>
                            </td>
                            <td><span class="badge-status badge-<?= $wo['status'] ?>"><?= ucwords(str_replace('_',' ',$wo['status'])) ?></span></td>
                            <td class="small"><?= esc($wo['supervisor_name'] ?? '—') ?></td>
                            <td class="small"><?= esc($wo['assigned_name'] ?? '—') ?></td>
                            <td class="small <?= $slaBreach ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                <?= $wo['sla_due'] ? date('d M Y H:i', strtotime($wo['sla_due'])) : '—' ?>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($wo['created_at'])) ?></td>
                            <td>
                                <a href="/work-orders/<?= $wo['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a>
                            </td>
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
            <nav><?= paginate($total, $perPage, $currentPage) ?></nav>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
