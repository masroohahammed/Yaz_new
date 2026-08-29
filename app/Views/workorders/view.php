<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="wo-workflow-page">
<div class="page-header">
  <div>
    <h1><i class="bi bi-tools me-2 text-primary"></i><?= esc($wo['wo_number']) ?></h1>
    <div class="d-flex gap-2 align-items-center mt-1 flex-wrap">
      <span class="fm-badge badge-priority-<?= esc($wo['priority']) ?>"><?= ucfirst($wo['priority']) ?></span>
      <span class="fm-badge badge-status-<?= esc($wo['status']) ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span>
      <?php if ($wo['category']): ?><span class="fm-badge" style="background:#e0f2fe;color:#0277bd"><?= ucfirst(str_replace('_', ' ', $wo['category'])) ?></span><?php endif; ?>
      <?php if ($wo['sla_breached']): ?><span class="fm-badge" style="background:#fee2e2;color:#991b1b"><i class="bi bi-exclamation-triangle me-1"></i>SLA Breached</span><?php endif; ?>
      <?php if (($wo['approval_status'] ?? 'approved') === 'pending'): ?><span class="fm-badge" style="background:#fff3cd;color:#856404"><i class="bi bi-hourglass-split me-1"></i>Awaiting Approval</span><?php endif; ?>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
    <a href="<?= base_url('workorders/edit/' . $wo['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php endif; ?>
    <a href="<?= base_url('workorders') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
</div>

<?= view('workorders/_lifecycle_row', ['stageFlow' => $stageFlow ?? [], 'wo' => $wo]) ?>

<?= view('workorders/_wo_actions_panel', ['wo' => $wo, 'supervisors' => $supervisors ?? [], 'technicians' => $technicians ?? [], 'jobCards' => $jobCards ?? [], 'inventoryItems' => $inventoryItems ?? []]) ?>

<!-- Tabbed Content -->
<ul class="nav nav-tabs mb-3" id="woTabs">
  <li class="nav-item"><a class="nav-link active" href="#tab-overview"   data-bs-toggle="tab">Overview</a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-jobcards"   data-bs-toggle="tab">Job Cards <span class="badge bg-secondary ms-1"><?= count($jobCards ?? []) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-sitevisit"  data-bs-toggle="tab"><i class="bi bi-geo-alt me-1"></i>Site Visits <span class="badge bg-secondary ms-1"><?= count($siteVisits ?? []) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-labor"      data-bs-toggle="tab">Labor &amp; Time <span class="badge bg-secondary ms-1"><?= count($labor) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-materials"  data-bs-toggle="tab">Materials <span class="badge bg-secondary ms-1"><?= count($materials) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-costing"    data-bs-toggle="tab">Costing</a></li>
  <?php if (!empty($wo['asset_id'])): ?>
  <li class="nav-item"><a class="nav-link"        href="#tab-asset"      data-bs-toggle="tab">Asset Info</a></li>
  <?php endif; ?>
  <li class="nav-item"><a class="nav-link"        href="#tab-docs"       data-bs-toggle="tab">Documents <span class="badge bg-secondary ms-1"><?= count($attachments) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-chat"       data-bs-toggle="tab"><i class="bi bi-chat-dots me-1"></i>Team Chat <span class="badge bg-secondary ms-1" id="chatCountBadge"><?= (int)($chatCount ?? 0) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-activity"   data-bs-toggle="tab">Activity <span class="badge bg-secondary ms-1"><?= count($comments) ?></span></a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-closure"    data-bs-toggle="tab">Closure &amp; Invoice</a></li>
  <li class="nav-item"><a class="nav-link"        href="#tab-approval"   data-bs-toggle="tab">Approval</a></li>
</ul>

<div class="tab-content" id="woTabContent">

  <!-- ============================================================ TAB: OVERVIEW -->
  <div class="tab-pane fade show active" id="tab-overview">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h5 class="fw-bold mb-2" style="color:#0a3d6b"><?= esc($wo['title']) ?></h5>
            <p class="text-muted small mb-4"><?= nl2br(esc($wo['description'] ?? 'No description provided.')) ?></p>
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-buildings"></i> Facility / Location</h6>
                  <p class="mb-0 text-muted small"><?= esc($wo['facility_name']) ?></p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-wrench"></i> Type</h6>
                  <p class="mb-0 text-muted small"><?= ucfirst($wo['type']) ?></p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-person"></i> Assigned Technician</h6>
                  <p class="mb-0 text-muted small"><?= esc($wo['assigned_name'] ?? 'Unassigned') ?></p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-truck"></i> Vendor</h6>
                  <p class="mb-0 text-muted small"><?= esc($wo['vendor_name'] ?? '—') ?></p>
                </div>
              </div>
              <?php if ($wo['planned_start']): ?>
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-calendar-event"></i> Planned Start</h6>
                  <p class="mb-0 text-muted small"><?= date('d M Y H:i', strtotime($wo['planned_start'])) ?></p>
                </div>
              </div>
              <?php endif; ?>
              <?php if ($wo['planned_end']): ?>
              <div class="col-sm-6">
                <div class="fm-form-section">
                  <h6><i class="bi bi-calendar-check"></i> Planned End</h6>
                  <p class="mb-0 text-muted small"><?= date('d M Y H:i', strtotime($wo['planned_end'])) ?></p>
                </div>
              </div>
              <?php endif; ?>
              <?php if (!empty($jobCards)): ?>
              <div class="col-12">
                <div class="fm-form-section">
                  <h6><i class="bi bi-card-checklist"></i> Job Cards (<?= count($jobCards) ?>)</h6>
                  <ul class="list-unstyled small text-muted mb-0">
                    <?php foreach ($jobCards as $jc): ?>
                    <li class="mb-1">
                      <strong><?= esc($jc['jc_number']) ?></strong> — <?= esc($jc['technician_name'] ?? 'Unassigned') ?>
                      (<?= ucfirst(str_replace('_', ' ', $jc['status'])) ?>)
                      <?php if (!empty($wo['started_at'])): ?> · started <?= date('d M H:i', strtotime($wo['started_at'])) ?><?php endif; ?>
                      <?php if (!empty($jc['completed_at'])): ?> · ended <?= date('d M H:i', strtotime($jc['completed_at'])) ?><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                  </ul>
                  <a href="#tab-jobcards" class="small" data-bs-toggle="tab">View all job cards</a>
                </div>
              </div>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <?php if ($wo['completion_notes']): ?>
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-2"><i class="bi bi-clipboard2-check me-2 text-success"></i>Completion Notes</h6>
            <p class="text-muted small mb-0"><?= nl2br(esc($wo['completion_notes'])) ?></p>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($wo['requester_name'] || $wo['requester_phone'] || $wo['requester_email']): ?>
        <div class="fm-card">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Requester Details</h6>
            <div class="row g-2 small text-muted">
              <?php if ($wo['requester_name']): ?><div class="col-sm-4"><i class="bi bi-person me-1"></i><?= esc($wo['requester_name']) ?></div><?php endif; ?>
              <?php if ($wo['requester_phone']): ?><div class="col-sm-4"><i class="bi bi-telephone me-1"></i><?= esc($wo['requester_phone']) ?></div><?php endif; ?>
              <?php if ($wo['requester_email']): ?><div class="col-sm-4"><i class="bi bi-envelope me-1"></i><?= esc($wo['requester_email']) ?></div><?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Side panel -->
      <div class="col-lg-4">
        <!-- SLA & Timing -->
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock me-2"></i>SLA & Timing</h6>
            <div class="small">
              <?php
              $slaPct = 0;
              if ($wo['sla_due'] && $wo['created_at']) {
                  $total   = strtotime($wo['sla_due']) - strtotime($wo['created_at']);
                  $elapsed = time() - strtotime($wo['created_at']);
                  $slaPct  = min(100, ($total > 0 ? round($elapsed / $total * 100) : 100));
              }
              $slaColor = $slaPct >= 100 ? 'danger' : ($slaPct >= 75 ? 'warning' : 'success');
              ?>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">SLA Due</span>
                <strong class="text-<?= $slaColor ?>"><?= $wo['sla_due'] ? date('d M Y H:i', strtotime($wo['sla_due'])) : '—' ?></strong>
              </div>
              <div class="progress mb-3" style="height:6px">
                <div class="progress-bar bg-<?= $slaColor ?>" style="width:<?= $slaPct ?>%"></div>
              </div>
              <div class="d-flex justify-content-between mb-1"><span class="text-muted">Created</span><span><?= date('d M Y H:i', strtotime($wo['created_at'])) ?></span></div>
              <?php if ($wo['started_at']): ?><div class="d-flex justify-content-between mb-1"><span class="text-muted">Started</span><span><?= date('d M Y H:i', strtotime($wo['started_at'])) ?></span></div><?php endif; ?>
              <?php if ($wo['completed_at']): ?><div class="d-flex justify-content-between mb-1"><span class="text-muted">Completed</span><span><?= date('d M Y H:i', strtotime($wo['completed_at'])) ?></span></div><?php endif; ?>
              <?php if ($wo['started_at'] && $wo['completed_at']): ?>
              <?php $mttr = round((strtotime($wo['completed_at']) - strtotime($wo['started_at'])) / 3600, 1); ?>
              <div class="d-flex justify-content-between mt-2 pt-2 border-top"><span class="text-muted fw-semibold">MTTR</span><strong><?= $mttr ?>h</strong></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Cost Summary -->
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i>Cost Summary</h6>
            <div class="small">
              <div class="d-flex justify-content-between mb-1"><span class="text-muted">Labor</span><span><?= $currency ?> <?= number_format($laborTotal, 2) ?></span></div>
              <div class="d-flex justify-content-between mb-1"><span class="text-muted">Materials</span><span><?= $currency ?> <?= number_format($materialTotal, 2) ?></span></div>
              <div class="d-flex justify-content-between mb-1"><span class="text-muted">Vendor</span><span><?= $currency ?> <?= number_format($vendorCost, 2) ?></span></div>
              <div class="d-flex justify-content-between fw-bold pt-2 border-top"><span>Total</span><span><?= $currency ?> <?= number_format($totalCost, 2) ?></span></div>
              <?php if ($wo['estimated_cost']): ?>
              <?php $variance = $totalCost - $wo['estimated_cost']; ?>
              <div class="d-flex justify-content-between mt-1 text-<?= $variance > 0 ? 'danger' : 'success' ?> small">
                <span>vs Estimate (<?= $currency ?> <?= number_format($wo['estimated_cost'], 2) ?>)</span>
                <span><?= $variance > 0 ? '+' : '' ?><?= number_format($variance, 2) ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Created by -->
        <div class="fm-card">
          <div class="fm-card-body small text-muted">
            <div class="mb-1"><strong>Created by:</strong> <?= esc($wo['created_by_name']) ?></div>
            <div class="mb-1"><strong>Approved by:</strong> <?= esc($wo['approved_by_name'] ?? '—') ?></div>
            <div><strong>Approval:</strong>
              <?php $apSt = $wo['approval_status'] ?? 'approved'; ?>
              <span class="fm-badge badge-status-<?= $apSt === 'approved' ? 'completed' : ($apSt === 'rejected' ? 'cancelled' : 'pending') ?>"><?= ucfirst($apSt) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ============================================================ TAB: JOB CARDS -->
  <div class="tab-pane fade" id="tab-jobcards">
    <div class="fm-card">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-card-checklist"></i> Job Cards on this Work Order</h5>
        <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager', 'supervisor'])): ?>
        <button type="button" class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createJobCardModal"><i class="bi bi-plus-lg me-1"></i>New Job Card</button>
        <?php endif; ?>
      </div>
      <div class="fm-card-body">
        <?php if (empty($jobCards)): ?>
        <p class="text-muted text-center py-4 mb-0">No job cards yet. Create one from the workflow panel above.</p>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr>
              <th>JC #</th><th>Technician</th><th>Status</th><th>Scheduled</th><th>Work Started</th><th>Completed</th><th>Hours</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($jobCards as $jc): ?>
            <tr>
              <td class="fw-semibold"><?= esc($jc['jc_number']) ?></td>
              <td><?= esc($jc['technician_name'] ?? '—') ?></td>
              <td><span class="fm-badge fm-badge-<?= $jc['status'] ?>"><?= ucfirst(str_replace('_', ' ', $jc['status'])) ?></span></td>
              <td class="small"><?= $jc['scheduled_date'] ? date('d M Y', strtotime($jc['scheduled_date'])) : '—' ?></td>
              <td class="small"><?= !empty($jc['started_at']) ? date('d M Y H:i', strtotime($jc['started_at'])) : ($jc['status'] === 'in_progress' ? '<span class="text-success fw-semibold">In progress</span>' : '—') ?></td>
              <td class="small"><?= !empty($jc['completed_at']) ? date('d M Y H:i', strtotime($jc['completed_at'])) : '—' ?></td>
              <td><?= number_format((float)($jc['labor_hours'] ?? 0), 1) ?></td>
              <td class="text-end" style="white-space:nowrap">
                <?php $canAct = in_array(session()->get('user_role'), ['super_admin','facility_manager','supervisor'], true); ?>
                <?php if ($canAct && $jc['status'] === 'draft'): ?>
                <button type="button" class="btn btn-sm btn-success btn-ajax-start-work me-1"
                        data-url="<?= base_url('job-cards/' . $jc['id'] . '/start') ?>">
                  <i class="bi bi-play-fill"></i> Start
                </button>
                <?php endif; ?>
                <?php if ($canAct && $jc['status'] === 'in_progress'): ?>
                <button type="button" class="btn btn-sm btn-warning btn-open-complete-jc me-1"
                        data-url="<?= base_url('job-cards/' . $jc['id'] . '/complete') ?>"
                        data-jc="<?= esc($jc['jc_number']) ?>"
                        data-bs-toggle="modal" data-bs-target="#completeJcModal">
                  <i class="bi bi-check-lg"></i> Complete
                </button>
                <?php endif; ?>
                <a href="<?= base_url('job-cards/' . $jc['id']) ?>" class="btn btn-sm btn-fm-outline">Open</a>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: SITE VISITS -->
  <div class="tab-pane fade" id="tab-sitevisit">
    <div class="fm-card mb-3">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Site Visits</h5>
        <?php if (in_array(session()->get('user_role'), ['super_admin','facility_manager','supervisor'])): ?>
        <button type="button" class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleSiteVisitModal">
          <i class="bi bi-plus-lg me-1"></i>Schedule Visit
        </button>
        <?php endif; ?>
      </div>
      <div class="fm-card-body">
        <?php if (empty($siteVisits)): ?>
        <p class="text-muted text-center py-4 mb-0">No site visits scheduled for this work order yet.</p>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Scheduled</th><th>Technician</th><th>Status</th><th>Purpose</th><th>Notes</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($siteVisits as $sv): ?>
            <tr>
              <td class="small fw-semibold"><?= $sv['scheduled_at'] ? date('d M Y H:i', strtotime($sv['scheduled_at'])) : '—' ?></td>
              <td><?= esc($sv['technician_name'] ?? '—') ?></td>
              <td><span class="fm-badge fm-badge-<?= $sv['status'] ?>"><?= ucfirst($sv['status']) ?></span></td>
              <td class="small"><?= esc($sv['purpose'] ?? '—') ?></td>
              <td class="small text-muted"><?= esc(mb_strimwidth($sv['notes'] ?? '', 0, 60, '…')) ?></td>
              <td><a href="<?= base_url('site-visits/view/' . $sv['id']) ?>" class="btn btn-sm btn-fm-outline">Open</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: LABOR -->
  <div class="tab-pane fade" id="tab-labor">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-stopwatch"></i> Labor & Time Entries</h5>
          </div>
          <div class="fm-card-body p-0">
            <div class="table-responsive">
              <table class="fm-table">
                <thead><tr><th>Technician</th><th>Date</th><th>Start</th><th>End</th><th>Hours</th><th>OT Hrs</th><th>Rate</th><th>Cost</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($labor as $l): ?>
                  <tr>
                    <td class="small"><?= esc($l['tech_name']) ?></td>
                    <td class="small"><?= date('d M Y', strtotime($l['work_date'])) ?></td>
                    <td class="small"><?= $l['start_time'] ? date('H:i', strtotime($l['start_time'])) : '—' ?></td>
                    <td class="small"><?= $l['end_time']   ? date('H:i', strtotime($l['end_time']))   : '—' ?></td>
                    <td class="small"><?= $l['hours_worked'] ?>h</td>
                    <td class="small"><?= $l['overtime_hours'] ?>h</td>
                    <td class="small"><?= $currency ?> <?= number_format($l['hourly_rate'], 2) ?></td>
                    <td class="small fw-semibold"><?= $currency ?> <?= number_format($l['labor_cost'], 2) ?></td>
                    <td class="small text-muted"><?= esc($l['notes'] ?? '—') ?></td>
                    <td>
                      <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
                      <a href="<?= base_url('workorders/labor/delete/' . $l['id']) ?>" class="btn-action bg-danger bg-opacity-10 text-danger" onclick="return confirm('Remove this entry?')"><i class="bi bi-trash"></i></a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($labor)): ?><tr><td colspan="10" class="text-center py-4 text-muted">No labor entries yet</td></tr><?php endif; ?>
                  <?php if (!empty($labor)): ?>
                  <tr class="fw-bold" style="background:#f8fafc">
                    <td colspan="7" class="text-end small">Total Labor Cost</td>
                    <td class="small"><?= $currency ?> <?= number_format($laborTotal, 2) ?></td>
                    <td colspan="2"></td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Add Labor Form -->
      <div class="col-lg-4">
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-plus-circle"></i> Add Labor Entry</h5></div>
          <div class="fm-card-body">
            <?= form_open('workorders/labor/add/' . $wo['id']) ?>
            <div class="mb-2">
              <label class="form-label">Technician</label>
              <select name="user_id" class="form-select form-select-sm">
                <?php foreach ($technicians as $t): ?>
                <option value="<?= $t['id'] ?>" <?= session()->get('user_id') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Work Date</label>
              <input type="date" name="work_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="row g-2 mb-2">
              <div class="col-6"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control form-control-sm"></div>
              <div class="col-6"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control form-control-sm"></div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-6"><label class="form-label">Hours Worked</label><input type="number" name="hours_worked" class="form-control form-control-sm" step="0.5" min="0" value="0"></div>
              <div class="col-6"><label class="form-label">Overtime Hrs</label><input type="number" name="overtime_hours" class="form-control form-control-sm" step="0.5" min="0" value="0"></div>
            </div>
            <div class="mb-2">
              <label class="form-label">Hourly Rate (<?= $currency ?>) — leave 0 to auto</label>
              <input type="number" name="hourly_rate" class="form-control form-control-sm" step="0.01" value="0">
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea name="labor_notes" class="form-control form-control-sm" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-fm-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>Add Entry</button>
            <?= form_close() ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: MATERIALS -->
  <div class="tab-pane fade" id="tab-materials">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-boxes"></i> Materials & Parts Used</h5></div>
          <div class="fm-card-body p-0">
            <div class="table-responsive">
              <table class="fm-table">
                <thead><tr><th>Item</th><th>Code</th><th>Qty</th><th>Unit Cost</th><th>Total</th><th>Stock</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($materials as $m): ?>
                  <tr>
                    <td class="small fw-semibold"><?= esc($m['item_name']) ?></td>
                    <td class="small text-muted"><?= esc($m['item_code'] ?? '—') ?></td>
                    <td class="small"><?= $m['quantity'] ?></td>
                    <td class="small"><?= $currency ?> <?= number_format($m['unit_cost'], 2) ?></td>
                    <td class="small fw-semibold"><?= $currency ?> <?= number_format($m['total_cost'], 2) ?></td>
                    <td class="small"><?= $m['deducted_from_stock'] ? '<span class="text-success"><i class="bi bi-check-circle"></i> Deducted</span>' : '<span class="text-muted">Manual</span>' ?></td>
                    <td class="small text-muted"><?= esc($m['notes'] ?? '—') ?></td>
                    <td>
                      <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
                      <a href="<?= base_url('workorders/material/delete/' . $m['id']) ?>" class="btn-action bg-danger bg-opacity-10 text-danger" onclick="return confirm('Remove this material? Stock will be restored if applicable.')"><i class="bi bi-trash"></i></a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($materials)): ?><tr><td colspan="8" class="text-center py-4 text-muted">No materials logged yet</td></tr><?php endif; ?>
                  <?php if (!empty($materials)): ?>
                  <tr class="fw-bold" style="background:#f8fafc">
                    <td colspan="4" class="text-end small">Total Materials Cost</td>
                    <td class="small"><?= $currency ?> <?= number_format($materialTotal, 2) ?></td>
                    <td colspan="3"></td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Add Material Form -->
      <div class="col-lg-4">
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-plus-circle"></i> Add Material / Part</h5></div>
          <div class="fm-card-body">
            <?= form_open('workorders/material/add/' . $wo['id']) ?>
            <div class="mb-2">
              <label class="form-label">From Inventory (auto-deducts stock)</label>
              <select name="item_id" class="form-select form-select-sm" onchange="fillItemDetails(this)">
                <option value="">— Manual Entry —</option>
                <?php foreach ($inventoryItems as $inv): ?>
                <option value="<?= $inv['id'] ?>" data-name="<?= esc($inv['name']) ?>" data-cost="<?= $inv['unit_cost'] ?>"><?= esc($inv['name'] . ' [' . $inv['quantity'] . ' ' . $inv['unit'] . ']') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Item Name</label>
              <input type="text" name="item_name" id="item_name" class="form-control form-control-sm" placeholder="e.g. HVAC Filter 24x24">
            </div>
            <div class="row g-2 mb-2">
              <div class="col-6"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control form-control-sm" step="0.5" value="1" min="0.5"></div>
              <div class="col-6"><label class="form-label">Unit Cost (<?= $currency ?>)</label><input type="number" name="unit_cost" id="unit_cost" class="form-control form-control-sm" step="0.01" value="0"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea name="mat_notes" class="form-control form-control-sm" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-fm-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>Add Material</button>
            <?= form_close() ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: COSTING -->
  <div class="tab-pane fade" id="tab-costing">
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="fm-card">
          <div class="fm-card-body">
                        <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager', 'supervisor'], true)): ?>
            <div class="border-bottom pb-3 mb-3">
              <p class="small text-muted mb-2">Pull labor hours and materials from completed job cards into this work order (safe to run again).</p>
              <?= form_open(base_url('workorders/sync-job-cards/' . $wo['id']), ['class' => 'd-inline fm-submit-form', 'data-loader-msg' => 'Syncing costs from job cards…']) ?>
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Refresh costs from job cards</button>
              <?= form_close() ?>
            </div>
            <?php endif; ?>
            <h6 class="fw-bold mb-4"><i class="bi bi-calculator me-2"></i>Cost Breakdown</h6>
            <table class="table table-sm">
              <tr><td>Labor (<?= count($labor) ?> entries)</td><td class="text-end fw-semibold"><?= $currency ?> <?= number_format($laborTotal, 2) ?></td></tr>
              <tr><td>Materials / Spare Parts</td><td class="text-end fw-semibold"><?= $currency ?> <?= number_format($materialTotal, 2) ?></td></tr>
              <tr><td>Vendor / Service Cost</td><td class="text-end fw-semibold"><?= $currency ?> <?= number_format($vendorCost, 2) ?></td></tr>
              <tr class="fw-bold border-top"><td>Total Actual Cost</td><td class="text-end"><?= $currency ?> <?= number_format($totalCost, 2) ?></td></tr>
              <?php if ($wo['estimated_cost']): ?>
              <tr><td class="text-muted">Estimated Cost</td><td class="text-end text-muted"><?= $currency ?> <?= number_format($wo['estimated_cost'], 2) ?></td></tr>
              <?php $var = $totalCost - $wo['estimated_cost']; ?>
              <tr><td class="text-<?= $var > 0 ? 'danger' : 'success' ?>">Variance</td><td class="text-end text-<?= $var > 0 ? 'danger' : 'success' ?> fw-semibold"><?= $var > 0 ? '+' : '' ?><?= number_format($var, 2) ?></td></tr>
              <?php endif; ?>
            </table>
            <!-- Update vendor cost -->
            <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
            <div class="border-top pt-3 mt-2">
              <?= form_open('workorders/update/' . $wo['id']) ?>
              <input type="hidden" name="title" value="<?= esc($wo['title']) ?>">
              <input type="hidden" name="facility_id" value="<?= $wo['facility_id'] ?>">
              <input type="hidden" name="type" value="<?= $wo['type'] ?>">
              <input type="hidden" name="priority" value="<?= $wo['priority'] ?>">
              <input type="hidden" name="status" value="<?= $wo['status'] ?>">
              <label class="form-label small">Update Estimated Cost (<?= $currency ?>)</label>
              <div class="input-group input-group-sm">
                <input type="number" name="estimated_cost" class="form-control" step="0.01" value="<?= $wo['estimated_cost'] ?>">
                <button type="submit" class="btn btn-fm-primary btn-sm">Save</button>
              </div>
              <?= form_close() ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: ASSET INFO -->
  <?php if (!empty($wo['asset_id'])): ?>
  <div class="tab-pane fade" id="tab-asset">
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-cpu me-2"></i>Asset Details</h6>
            <div class="small">
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Name</span><span class="fw-semibold"><?= esc($wo['asset_name']) ?></span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Code</span><span><?= esc($wo['asset_code'] ?? '—') ?></span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Brand / Model</span><span><?= esc(($wo['asset_brand'] ?? '') . ' ' . ($wo['asset_model'] ?? '')) ?></span></div>
              <?php $warranty = $wo['asset_warranty_expiry'] ?? $wo['warranty_expiry'] ?? null; ?>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Warranty Expiry</span><span class="<?= ($warranty && strtotime($warranty) < time()) ? 'text-danger' : '' ?>"><?= $warranty ? date('d M Y', strtotime($warranty)) : '—' ?></span></div>
              <div class="d-flex justify-content-between py-1"><span class="text-muted">Next Maintenance</span><span><?php $nextMaint = $wo['asset_next_maintenance'] ?? null; ?><?= $nextMaint ? date('d M Y', strtotime($nextMaint)) : '—' ?></span></div>
            </div>
            <div class="mt-3">
              <a href="<?= base_url('asset-register/view/' . $wo['asset_id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-eye me-1"></i>View Full Asset Record</a>
            </div>
          </div>
        </div>

        <!-- Meter Readings -->
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-speedometer2"></i> Meter Readings</h5></div>
          <div class="fm-card-body p-0">
            <table class="fm-table">
              <thead><tr><th>Date</th><th>Type</th><th>Value</th><th>Recorded By</th></tr></thead>
              <tbody>
                <?php foreach ($meterReadings as $r): ?>
                <tr><td class="small"><?= date('d M Y', strtotime($r['reading_date'])) ?></td><td class="small"><?= ucfirst($r['reading_type']) ?></td><td class="small fw-semibold"><?= number_format($r['reading_value'], 2) ?></td><td class="small text-muted"><?= esc($r['recorded_by_name'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($meterReadings)): ?><tr><td colspan="4" class="text-center py-3 text-muted small">No readings recorded</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="fm-card-body border-top">
            <?= form_open('workorders/meter/' . $wo['id']) ?>
            <div class="row g-2 align-items-end">
              <div class="col-md-3"><label class="form-label small">Type</label><select name="reading_type" class="form-select form-select-sm"><option value="hours">Hours</option><option value="km">KM</option><option value="cycles">Cycles</option><option value="other">Other</option></select></div>
              <div class="col-md-3"><label class="form-label small">Value</label><input type="number" name="reading_value" class="form-control form-control-sm" step="0.01" placeholder="0.00"></div>
              <div class="col-md-3"><label class="form-label small">Date</label><input type="date" name="reading_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>"></div>
              <div class="col-md-3"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Record</button></div>
            </div>
            <?= form_close() ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ============================================================ TAB: DOCUMENTS -->
  <div class="tab-pane fade" id="tab-docs">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card mb-3">
          <div class="card-header-fm"><h5><i class="bi bi-paperclip"></i> Attachments</h5></div>
          <div class="fm-card-body">
            <?php if (empty($attachments)): ?>
            <p class="text-muted text-center py-4">No attachments uploaded yet.</p>
            <?php else: ?>
            <div class="row g-2">
              <?php foreach ($attachments as $att): ?>
              <?php $ext = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION)); $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']); ?>
              <div class="col-md-4">
                <div class="border rounded p-2 text-center small">
                  <?php if ($isImg): ?>
                  <a href="<?= base_url($att['file_path']) ?>" target="_blank"><img src="<?= base_url($att['file_path']) ?>" class="img-fluid rounded mb-1" style="max-height:100px;object-fit:cover"></a>
                  <?php else: ?>
                  <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="text-decoration-none"><i class="bi bi-file-earmark-text fs-2 text-muted d-block mb-1"></i></a>
                  <?php endif; ?>
                  <div class="text-truncate text-muted" title="<?= esc($att['file_name']) ?>"><?= esc($att['file_name']) ?></div>
                  <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="btn btn-sm btn-fm-outline mt-1 py-0">Download</a>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-upload"></i> Upload File</h5></div>
          <div class="fm-card-body">
            <form id="uploadForm" enctype="multipart/form-data">
              <input type="file" name="attachment" id="attachFile" class="form-control form-control-sm mb-2" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
              <button type="button" class="btn btn-fm-primary btn-sm w-100" onclick="uploadFile()"><i class="bi bi-upload me-1"></i>Upload</button>
            </form>
            <div id="uploadMsg" class="mt-2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ============================================================ TAB: TEAM CHAT -->
  <div class="tab-pane fade" id="tab-chat">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card">
          <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-chat-dots"></i> Team Chat</h5></div>
          <div class="fm-card-body d-flex flex-column" style="min-height:420px">
            <div id="woChatMessages" class="flex-grow-1 overflow-auto mb-3 pe-1" style="max-height:360px">
              <p class="text-muted text-center small py-4">Loading messages…</p>
            </div>
            <div class="border-top pt-3">
              <div class="input-group">
                <input type="text" id="woChatInput" class="form-control" placeholder="Message the team on this work order…" maxlength="2000" autocomplete="off">
                <button type="button" class="btn btn-fm-primary" id="woChatSend"><i class="bi bi-send"></i></button>
              </div>
              <div class="form-text">Visible to supervisors, technicians, and managers on this work order.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: ACTIVITY -->
  <div class="tab-pane fade" id="tab-activity">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2"></i>Activity Log</h6>
            <?php foreach ($comments as $c): ?>
            <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
              <div class="user-avatar flex-shrink-0"><?= strtoupper(substr($c['user_name'] ?? 'U', 0, 1)) ?></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-1">
                  <span class="fw-semibold small"><?= esc($c['user_name'] ?? 'System') ?></span>
                  <span class="text-muted x-small"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></span>
                </div>
                <div class="small text-muted"><?= nl2br(esc($c['comment'])) ?></div>
                <?php if ($c['image_path']): ?><img src="<?= base_url($c['image_path']) ?>" class="img-thumbnail mt-2" style="max-height:120px"><?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?><p class="text-muted text-center py-4">No activity yet.</p><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <!-- Add Comment -->
        <div class="fm-card mb-3">
          <div class="card-header-fm"><h5><i class="bi bi-chat-left-dots"></i> Add Note</h5></div>
          <div class="fm-card-body">
            <textarea id="commentText" class="form-control form-control-sm mb-2" rows="4" placeholder="Add a note, update, or finding..."></textarea>
            <input type="file" id="commentImage" class="form-control form-control-sm mb-2" accept="image/*">
            <button class="btn btn-fm-primary btn-sm w-100" onclick="postComment()"><i class="bi bi-send me-1"></i>Post</button>
          </div>
        </div>
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-info-circle"></i> Status</h5></div>
          <div class="fm-card-body small text-muted">
            Status updates through the workflow panel above (job card → QA → invoice). Manual status skip is disabled.
            <div class="mt-2">
              <span class="fm-badge badge-status-<?= esc($wo['status']) ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ TAB: CLOSURE WORKFLOW -->
  <div class="tab-pane fade" id="tab-closure">
    <?php if (!empty($workflow['enabled'])): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-diagram-3 me-2"></i>Closure Pipeline</h5></div>
      <div class="fm-card-body">
        <div class="row g-3 text-center mb-3">
          <?php
          $steps = [
            ['Work Done', in_array($wo['workflow_stage'] ?? '', ['inspection_qc','job_completed','wo_closed'], true) || in_array($wo['qa_status'] ?? '', ['pending','approved'], true), 'bi-check-circle'],
            ['QA', ($wo['qa_status'] ?? 'none') === 'approved', 'bi-shield-check'],
            ['Client', ($wo['client_approval_status'] ?? 'none') === 'approved', 'bi-person-check'],
            ['Invoice', !empty($workflow['invoice']), 'bi-receipt'],
          ];
          foreach ($steps as [$label, $done, $icon]):
          ?>
          <div class="col-3">
            <div class="p-2 rounded border <?= $done ? 'border-success bg-success bg-opacity-10' : 'border-light' ?>">
              <i class="bi <?= $icon ?> fs-4 <?= $done ? 'text-success' : 'text-muted' ?>"></i>
              <div class="small fw-semibold mt-1"><?= $label ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="row g-2 small">
          <div class="col-md-6"><span class="text-muted">QA Status:</span> <strong><?= ucfirst($wo['qa_status'] ?? 'none') ?></strong></div>
          <div class="col-md-6"><span class="text-muted">Client Status:</span> <strong><?= ucfirst($wo['client_approval_status'] ?? 'none') ?></strong></div>
        </div>
        <?php if (!empty($workflow['invoice'])): ?>
        <div class="alert alert-success mt-3 mb-0">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              Invoice <a href="<?= base_url('finance/invoices/view/'.$workflow['invoice']['id']) ?>" class="fw-bold"><?= esc($workflow['invoice']['invoice_number']) ?></a>
              — <?= $currency ?> <?= number_format((float)$workflow['invoice']['total'],2) ?> (<?= ucfirst($workflow['invoice']['status']) ?>)
            </div>
            <div class="d-flex gap-1">
              <a href="<?= base_url('finance/invoices/view/'.$workflow['invoice']['id']) ?>" class="btn btn-sm btn-fm-outline">View Invoice</a>
              <a href="<?= base_url('finance/invoices/print/'.$workflow['invoice']['id']) ?>" class="btn btn-sm btn-fm-outline" target="_blank">Print / PDF</a>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    
      <?php
      $showInvoicePrep = ($wo['qa_status'] ?? '') === 'approved'
        && empty($workflow['invoice'])
        && in_array(session()->get('user_role'), ['super_admin','facility_manager','finance_manager','supervisor'], true);
      ?>
      <?php if ($showInvoicePrep): ?>
      <div class="fm-card mb-3 border-primary">
        <div class="card-header-fm"><h5><i class="bi bi-receipt-cutoff me-2"></i>Create Invoice</h5></div>
        <div class="fm-card-body">
          <p class="small text-muted">QC is complete. Create an invoice with client selling prices. Internal costs remain hidden from client PDFs.</p>
          <a href="<?= base_url('workorders/prepare-invoice/'.$wo['id']) ?>" class="btn btn-fm-primary btn-sm me-2">
            <i class="bi bi-pencil-square me-1"></i>Open invoice preparation
          </a>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#invoicePrepModal">
            <i class="bi bi-window me-1"></i>Quick invoice popup
          </button>
        </div>
      </div>
      <?php endif; ?>

<?php if (in_array(session()->get('user_role'), ['super_admin','facility_manager','supervisor','qa_inspector'], true)): ?>
      <?php if (($wo['qa_status'] ?? '') === 'pending'): ?>
      <div class="fm-card mb-3">
        <div class="card-header-fm"><h5>QA Approval</h5></div>
        <div class="fm-card-body">
          <?= form_open(base_url('workorders/qa-approve/'.$wo['id']), ['class' => 'fm-submit-form fm-submit-fast', 'data-loader-msg' => 'Approving…']) ?>
          <?= csrf_field() ?>
          <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="QA notes (optional)"></textarea>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-sm">Approve QA</button>
            <?= form_close() ?>
            <?= form_open(base_url('workorders/qa-reject/'.$wo['id']), ['class' => 'fm-submit-form fm-submit-fast', 'data-loader-msg' => 'Rejecting…']) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject QA and return to in progress?')">Reject QA</button>
            <?= form_close() ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array(session()->get('user_role'), ['super_admin','facility_manager','client'], true)): ?>
      <?php if (($wo['client_approval_status'] ?? '') === 'pending' && ($wo['qa_status'] ?? '') === 'approved'): ?>
      <div class="fm-card mb-3">
        <div class="card-header-fm"><h5>Client Approval &amp; Invoice</h5></div>
        <div class="fm-card-body">
          <p class="small text-muted">Approving will auto-generate a draft invoice from labor and materials on this work order.</p>
          <?= form_open(base_url('workorders/client-approve/'.$wo['id']), ['class' => 'fm-submit-form fm-submit-fast', 'data-loader-msg' => 'Invoicing…']) ?>
          <?= csrf_field() ?>
          <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Client sign-off notes"></textarea>
          <?= view('partials/_signature_pad', ['fieldName' => 'client_signature', 'label' => 'Client signature']) ?>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-fm-primary btn-sm" onclick="return confirm('Approve and create invoice?')">Client Approve &amp; Invoice</button>
            <?= form_close() ?>
            <?= form_open(base_url('workorders/client-reject/'.$wo['id']), ['class' => 'fm-submit-form fm-submit-fast', 'data-loader-msg' => 'Saving…']) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
            <?= form_close() ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php else: ?>
    <div class="alert alert-warning">
      Extended closure workflow requires a database update. Run: <code>php spark migrate</code> or import <code>app/Database/SQL/patch_2026_workflow_company.sql</code>.
    </div>
    <?php endif; ?>
  </div>

  <!-- ============================================================ TAB: APPROVAL -->
  <div class="tab-pane fade" id="tab-approval">
    <div class="row g-3">
      <div class="col-lg-7">
        <div class="fm-card mb-3">
          <div class="fm-card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Approval Status</h6>
            <?php $apSt = $wo['approval_status'] ?? 'approved'; ?>
            <div class="alert <?= $apSt === 'approved' ? 'alert-success' : ($apSt === 'rejected' ? 'alert-danger' : 'alert-warning') ?> mb-3">
              <strong><?= ucfirst($apSt) ?></strong>
              <?php if ($wo['approved_by_name']): ?> — by <?= esc($wo['approved_by_name']) ?> <?php endif; ?>
              <?php if ($wo['approved_at']): ?>on <?= date('d M Y H:i', strtotime($wo['approved_at'])) ?><?php endif; ?>
            </div>

            <!-- Approval log -->
            <?php if (!empty($approvals)): ?>
            <h6 class="small fw-bold text-muted mb-2 text-uppercase">Approval History</h6>
            <div class="table-responsive">
              <table class="fm-table">
                <thead><tr><th>Type</th><th>Action</th><th>By</th><th>Date</th><th>Notes</th><th>Signature</th></tr></thead>
                <tbody>
                  <?php foreach ($approvals as $ap): ?>
                  <tr>
                    <td class="small"><?= ucfirst($ap['approval_type']) ?></td>
                    <td><span class="fm-badge badge-status-<?= $ap['action'] === 'approved' ? 'completed' : 'cancelled' ?>"><?= ucfirst($ap['action']) ?></span></td>
                    <td class="small"><?= esc($ap['actioned_by_name']) ?></td>
                    <td class="small"><?= date('d M Y H:i', strtotime($ap['created_at'])) ?></td>
                    <td class="small text-muted"><?= esc($ap['notes'] ?? '—') ?></td>
                    <td class="small"><?php if (! empty($ap['signature_path'])): $sigUrl = fm_logo_url($ap['signature_path']); if ($sigUrl): ?><img src="<?= esc($sigUrl) ?>" alt="Signature" style="height:36px;border:1px solid #ddd;border-radius:4px"><?php endif; else: ?>—<?php endif; ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager']) && ($wo['approval_status'] ?? 'approved') === 'pending'): ?>
      <div class="col-lg-5">
        <div class="fm-card">
          <div class="card-header-fm"><h5><i class="bi bi-check2-circle"></i> Take Action</h5></div>
          <div class="fm-card-body">
            <div class="mb-2">
              <label class="form-label">Approval Type</label>
              <select name="approval_type" id="approvalType" class="form-select form-select-sm">
                <option value="supervisor">Supervisor Review</option>
                <option value="budget">Budget Approval</option>
                <option value="completion">Completion Verification</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea id="approvalNotes" class="form-control form-control-sm" rows="3" placeholder="Optional remarks..."></textarea>
            </div>
            <div class="d-flex gap-2">
              <?= form_open('workorders/approve/' . $wo['id']) ?>
              <input type="hidden" name="approval_type" value="supervisor">
              <input type="hidden" name="approval_notes" id="approveNotesHidden">
              <button type="submit" class="btn btn-success btn-sm flex-grow-1" onclick="document.getElementById('approveNotesHidden').value=document.getElementById('approvalNotes').value"><i class="bi bi-check-lg me-1"></i>Approve</button>
              <?= form_close() ?>
              <?= form_open('workorders/reject/' . $wo['id']) ?>
              <input type="hidden" name="approval_type" value="supervisor">
              <input type="hidden" name="approval_notes" id="rejectNotesHidden">
              <button type="submit" class="btn btn-danger btn-sm flex-grow-1" onclick="document.getElementById('rejectNotesHidden').value=document.getElementById('approvalNotes').value"><i class="bi bi-x-lg me-1"></i>Reject</button>
              <?= form_close() ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- end tab-content -->

<!-- ═══ INVOICE PREP MODAL ════════════════════════════════════ -->
<div class="modal fade" id="invoicePrepModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-receipt-cutoff me-2"></i>Prepare Invoice — <?= esc($wo['wo_number']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <?= form_open(base_url('workorders/prepare-invoice/'.$wo['id']), ['id' => 'invoicePrepForm']) ?>
      <?= csrf_field() ?>
      <div class="modal-body">
        <p class="small text-muted mb-3">Adjust descriptions and set client-facing selling prices. Internal costs are hidden from the client PDF.</p>
        <div class="table-responsive mb-3">
          <table class="table table-sm" id="invoiceLineTable">
            <thead><tr class="table-light">
              <th>Description</th><th style="width:80px">Qty</th>
              <th style="width:110px">Unit Price</th><th style="width:100px">Total</th>
              <th style="width:90px">Int. Cost</th><th style="width:40px"></th>
            </tr></thead>
            <tbody id="invoiceLines">
              <?php foreach ($materials as $i => $m): ?>
              <tr>
                <td><input type="text" name="lines[<?= $i ?>][description]" class="form-control form-control-sm" value="<?= esc($m['item_name'] ?? $m['description'] ?? '') ?>"></td>
                <td><input type="number" name="lines[<?= $i ?>][qty]" class="form-control form-control-sm ipl-qty" step="0.01" value="<?= (float)($m['quantity'] ?? 1) ?>"></td>
                <td><input type="number" name="lines[<?= $i ?>][unit_price]" class="form-control form-control-sm ipl-price" step="0.01" value="<?= (float)($m['unit_price'] ?? $m['unit_cost'] ?? 0) ?>"></td>
                <td><input type="text" class="form-control form-control-sm ipl-total bg-light" readonly value="<?= number_format((float)($m['total_cost'] ?? 0), 2) ?>"></td>
                <td><input type="number" name="lines[<?= $i ?>][internal_cost]" class="form-control form-control-sm" step="0.01" value="<?= (float)($m['total_cost'] ?? 0) ?>" tabindex="-1"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger ipl-del"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php endforeach; ?>
              <?php if (!empty($labor)): ?>
              <?php $li = count($materials); ?>
              <tr>
                <td><input type="text" name="lines[<?= $li ?>][description]" class="form-control form-control-sm" value="Labor charges"></td>
                <td><input type="number" name="lines[<?= $li ?>][qty]" class="form-control form-control-sm ipl-qty" value="1"></td>
                <td><input type="number" name="lines[<?= $li ?>][unit_price]" class="form-control form-control-sm ipl-price" step="0.01" value="<?= number_format($laborTotal ?? 0, 2) ?>"></td>
                <td><input type="text" class="form-control form-control-sm ipl-total bg-light" readonly value="<?= number_format($laborTotal ?? 0, 2) ?>"></td>
                <td><input type="number" name="lines[<?= $li ?>][internal_cost]" class="form-control form-control-sm" step="0.01" value="<?= number_format($laborTotal ?? 0, 2) ?>" tabindex="-1"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger ipl-del"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="addInvoiceLine"><i class="bi bi-plus-lg me-1"></i>Add line</button>
        </div>
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Tax %</label>
            <input type="number" name="tax_rate" id="invTax" class="form-control form-control-sm" step="0.01" value="5">
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Discount (<?= esc($currency ?? 'QAR') ?>)</label>
            <input type="number" name="discount" id="invDiscount" class="form-control form-control-sm" step="0.01" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Notes / Terms</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Payment terms, references…">
          </div>
        </div>
        <div class="mt-3 p-3 bg-light rounded text-end">
          Subtotal: <strong id="invSubtotal">0.00</strong> &nbsp;|&nbsp;
          Tax: <strong id="invTaxAmt">0.00</strong> &nbsp;|&nbsp;
          <span class="text-success fs-6">Total: <strong id="invTotal">0.00</strong> <?= esc($currency ?? 'QAR') ?></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary"><i class="bi bi-receipt me-1"></i>Create Draft Invoice</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<!-- ═══ SCHEDULE SITE VISIT MODAL ═════════════════════════════ -->
<div class="modal fade" id="scheduleSiteVisitModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>Schedule Site Visit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <?= form_open(base_url('workorders/'.$wo['id'].'/site-visit')) ?>
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Scheduled Date &amp; Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="scheduled_at" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Assign Technician</label>
            <select name="technician_id" class="form-select">
              <option value="">— Unassigned —</option>
              <?php foreach ($techniciansForSv ?? [] as $t): ?>
              <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Purpose</label>
            <input type="text" name="purpose" class="form-control" placeholder="Inspection, assessment, follow-up…">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Pre-visit instructions or observations"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary"><i class="bi bi-calendar-check me-1"></i>Schedule Visit</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<!-- ═══ COMPLETE JOB CARD MODAL ═══════════════════════════════ -->
<div class="modal fade" id="completeJcModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Complete Job Card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="completeJcForm" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <p class="text-muted small mb-3">Record actual time spent and completion notes. This will sync costs back to the work order.</p>
          <div class="mb-3">
            <label class="form-label fw-semibold">Labor Hours <span class="text-danger">*</span></label>
            <input type="number" name="labor_hours" class="form-control" step="0.5" min="0.5" required placeholder="e.g. 3.5">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Completion Notes <span class="text-danger">*</span></label>
            <textarea name="completion_notes" class="form-control" rows="3" required
                      placeholder="Describe what was done, parts replaced, outcome…"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Technician Notes <span class="text-muted small">(internal)</span></label>
            <textarea name="technician_notes" class="form-control" rows="2"
                      placeholder="Internal remarks, follow-up needed…"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-semibold">
            <i class="bi bi-check-lg me-1"></i>Mark as Complete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const WO_ID = <?= $wo['id'] ?>;
function getCsrf() {
  const n = document.querySelector('meta[name="csrf-token-name"]');
  const v = document.querySelector('meta[name="csrf-token-value"]');
  if (n && v) return { name: n.content, value: v.content };
  const inp = document.querySelector('input[name^="csrf"]');
  return inp ? { name: inp.name, value: inp.value } : { name: 'csrf_test_name', value: '' };
}

// Open correct tab from hash
document.addEventListener('DOMContentLoaded', () => {
  const openTab = <?= json_encode(session()->getFlashdata('open_tab') ?? '') ?>;
  if (openTab) {
    const tabEl = document.querySelector('[href="#' + openTab + '"]');
    if (tabEl && typeof bootstrap !== 'undefined') new bootstrap.Tab(tabEl).show();
  }
  const hash = location.hash;
  if (hash) {
    const trigger = document.querySelector(`[href="${hash}"]`);
    if (trigger) new bootstrap.Tab(trigger).show();
  }
  document.querySelectorAll('#woTabs .nav-link').forEach(t => {
    t.addEventListener('shown.bs.tab', e => { history.replaceState(null, '', e.target.getAttribute('href')); });
  });
});

function quickStatus(status) {
  if (!confirm('Set status to "' + status.replace(/_/g, ' ') + '"?')) return;
  const fd = new FormData();
  const csrf = getCsrf(); fd.append(csrf.name, csrf.value);
  fd.append('status', status);
  fetch('<?= base_url('workorders/ajax/quick-status/' . $wo['id']) ?>', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
      if (d.ok) {
        // Optimistic badge update — no full reload
        document.querySelectorAll('.badge-status-' + d.status.replace('_','-')).forEach(b => b.style.display='');
        showToast('Status updated to ' + status.replace(/_/g,' '), 'success');
      }
    }).catch(() => location.reload());
}

function updateStatus() {
  const fd = new FormData();
  const csrf = getCsrf(); fd.append(csrf.name, csrf.value);
  fd.append('id', WO_ID);
  fd.append('status', document.getElementById('newStatus').value);
  fd.append('completion_notes', document.getElementById('statusNotes').value);
  fd.append('actual_cost', document.getElementById('actualCost').value);
  const photo = document.getElementById('statusPhoto').files[0];
  if (photo) fd.append('status_photo', photo);
  fetch('<?= base_url('workorders/status') ?>', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => { if (d.status) location.reload(); });
}

function postComment() {
  const text = document.getElementById('commentText').value.trim();
  if (!text) return;
  const fd = new FormData();
  const csrf = getCsrf(); fd.append(csrf.name, csrf.value);
  fd.append('comment', text);
  const img = document.getElementById('commentImage').files[0];
  if (img) fd.append('comment_image', img);
  fmFetchJson('<?= base_url('workorders/comment/' . $wo['id']) ?>', { method: 'POST', body: fd })
    .then(d => { if (d && d.status) location.reload(); else if (d && d.message) alert(d.message); });
}

function uploadFile() {
  const file = document.getElementById('attachFile').files[0];
  if (!file) return;
  const fd = new FormData();
  const csrf = getCsrf(); fd.append(csrf.name, csrf.value);
  fd.append('attachment', file);
  fmFetchJson('<?= base_url('workorders/upload/' . $wo['id']) ?>', { method: 'POST', body: fd })
    .then(d => {
      if (!d) return;
      document.getElementById('uploadMsg').innerHTML = d.status
        ? '<div class="alert alert-success small py-1">Uploaded: ' + (d.original || 'file') + '</div>'
        : '<div class="alert alert-danger small py-1">' + (d.message || 'Upload failed') + '</div>';
      if (d.status) setTimeout(() => location.reload(), 1000);
    });
}


// Team chat
let woChatLastId = 0;
let woChatPollTimer = null;

function renderWoChatMessages(msgs) {
  const box = document.getElementById('woChatMessages');
  if (!box) return;
  if (!msgs.length && woChatLastId === 0) {
    box.innerHTML = '<p class="text-muted text-center small py-4">No messages yet. Say hello to the team.</p>';
    return;
  }
  msgs.forEach(m => {
    if (m.id > woChatLastId) woChatLastId = m.id;
    const mine = m.user_id == <?= (int) session()->get('user_id') ?>;
    const el = document.createElement('div');
    el.className = 'mb-2 ' + (mine ? 'text-end' : '');
    el.innerHTML = '<div class="d-inline-block px-3 py-2 rounded-3 ' + (mine ? 'bg-primary text-white' : 'bg-light') + '" style="max-width:85%">'
      + '<div class="small fw-semibold ' + (mine ? 'text-white-50' : 'text-muted') + '">' + (m.sender_name || 'User') + ' · ' + (m.created_at || '') + '</div>'
      + '<div class="small">' + (m.message || '').replace(/</g,'&lt;') + '</div></div>';
    box.appendChild(el);
  });
  box.scrollTop = box.scrollHeight;
}

function pollWoChat() {
  fetch('<?= base_url('ajax/wo-chat/' . $wo['id']) ?>?after=' + woChatLastId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(d => {
      if (d.status && d.messages && d.messages.length) {
        if (woChatLastId === 0) document.getElementById('woChatMessages').innerHTML = '';
        renderWoChatMessages(d.messages);
        const badge = document.getElementById('chatCountBadge');
        if (badge) badge.textContent = woChatLastId;
      } else if (woChatLastId === 0) {
        document.getElementById('woChatMessages').innerHTML = '<p class="text-muted text-center small py-4">No messages yet. Say hello to the team.</p>';
      }
    })
    .catch(() => {});
}

function sendWoChat() {
  const input = document.getElementById('woChatInput');
  const text = (input && input.value || '').trim();
  if (!text) return;
  const fd = new FormData();
  const csrf = getCsrf();
  fd.append(csrf.name, csrf.value);
  fd.append('message', text);
  fetch('<?= base_url('ajax/wo-chat/' . $wo['id']) ?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(d => {
      if (d.status && d.msg) {
        if (woChatLastId === 0) document.getElementById('woChatMessages').innerHTML = '';
        renderWoChatMessages([d.msg]);
        input.value = '';
        if (d[csrf.name]) {
          const meta = document.querySelector('meta[name="csrf-token-value"]');
          if (meta) meta.content = d[csrf.name];
        }
      } else if (d.message) {
        alert(d.message);
      }
    });
}

document.addEventListener('DOMContentLoaded', () => {
  const chatTab = document.querySelector('[href="#tab-chat"]');
  const sendBtn = document.getElementById('woChatSend');
  const chatInput = document.getElementById('woChatInput');
  if (sendBtn) sendBtn.addEventListener('click', sendWoChat);
  if (chatInput) chatInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); sendWoChat(); } });
  if (chatTab) {
    chatTab.addEventListener('shown.bs.tab', () => {
      pollWoChat();
      if (!woChatPollTimer) woChatPollTimer = setInterval(pollWoChat, 8000);
    });
  }
  if (location.hash === '#tab-chat' && chatTab) {
    new bootstrap.Tab(chatTab).show();
    setTimeout(pollWoChat, 300);
  }
});


function fillItemDetails(sel) {
  const opt = sel.options[sel.selectedIndex];
  if (opt.value) {
    document.getElementById('item_name').value = opt.dataset.name || '';
    document.getElementById('unit_cost').value  = opt.dataset.cost || '0';
  }
}

/* ── Lightweight toast (no extra lib needed) ─────────────────── */
function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = 'fm-toast fm-toast-' + type;
  t.textContent = msg;
  Object.assign(t.style, {
    position:'fixed', bottom:'24px', right:'24px', zIndex:9999,
    background: type === 'success' ? '#0a3d6b' : '#991b1b',
    color:'#fff', padding:'10px 18px', borderRadius:'8px',
    fontSize:'.85rem', boxShadow:'0 4px 12px rgba(0,0,0,.25)',
    transition:'opacity .3s', opacity:'1'
  });
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; setTimeout(() => t.remove(), 300); }, 2800);
}

/* ── AJAX: Assign Supervisor form submission ─────────────────── */
document.addEventListener('DOMContentLoaded', function() {
  const assignForm = document.getElementById('assignSupervisorForm');
  if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = assignForm.querySelector('[type=submit]');
      btn.disabled = true; btn.textContent = 'Assigning…';
      const fd = new FormData(assignForm);
      fetch('<?= base_url('workorders/ajax/assign-supervisor/' . $wo['id']) ?>', { method:'POST', body:fd })
        .then(r => r.json()).then(d => {
          if (d.ok) {
            showToast('Supervisor assigned!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('assignSupervisorModal'))?.hide();
            setTimeout(() => location.reload(), 800);
          } else {
            showToast(d.msg || 'Error', 'error');
            btn.disabled = false; btn.textContent = 'Assign & Continue';
          }
        }).catch(() => assignForm.submit());
    });
  }

  /* ── Complete Job Card: modal receives the target URL ──────── */
  document.getElementById('completeJcModal')?.addEventListener('show.bs.modal', function(e) {
    const btn  = e.relatedTarget;
    const url  = btn?.dataset.url;
    const jcNo = btn?.dataset.jc;
    if (url) {
      document.getElementById('completeJcForm').action = url;
      document.querySelector('#completeJcModal .modal-title').innerHTML =
        '<i class="bi bi-check-circle me-2"></i>Complete ' + (jcNo || 'Job Card');
    }
  });

  /* ── Invoice prep modal: live totals ─────────────────────── */
  function calcInvoiceTotals() {
    let sub = 0;
    document.querySelectorAll('#invoiceLines tr').forEach(function(tr) {
      const qty   = parseFloat(tr.querySelector('.ipl-qty')?.value   || 0);
      const price = parseFloat(tr.querySelector('.ipl-price')?.value || 0);
      const tot   = qty * price;
      const totEl = tr.querySelector('.ipl-total');
      if (totEl) totEl.value = tot.toFixed(2);
      sub += tot;
    });
    const tax      = parseFloat(document.getElementById('invTax')?.value      || 0);
    const discount = parseFloat(document.getElementById('invDiscount')?.value || 0);
    const taxAmt   = sub * tax / 100;
    document.getElementById('invSubtotal').textContent = sub.toFixed(2);
    document.getElementById('invTaxAmt').textContent   = taxAmt.toFixed(2);
    document.getElementById('invTotal').textContent    = (sub + taxAmt - discount).toFixed(2);
  }
  document.getElementById('invoicePrepModal')?.addEventListener('input', calcInvoiceTotals);
  document.getElementById('addInvoiceLine')?.addEventListener('click', function() {
    const idx = document.querySelectorAll('#invoiceLines tr').length;
    const tr  = document.createElement('tr');
    tr.innerHTML = `<td><input type="text" name="extra[${idx}][description]" class="form-control form-control-sm" placeholder="Item…"></td>
      <td><input type="number" name="extra[${idx}][qty]" class="form-control form-control-sm ipl-qty" value="1" step="0.01"></td>
      <td><input type="number" name="extra[${idx}][unit_price]" class="form-control form-control-sm ipl-price" step="0.01" value="0"></td>
      <td><input type="text" class="form-control form-control-sm ipl-total bg-light" readonly value="0.00"></td>
      <td><input type="number" name="extra[${idx}][internal_cost]" class="form-control form-control-sm" step="0.01" value="0" tabindex="-1"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger ipl-del"><i class="bi bi-x"></i></button></td>`;
    document.getElementById('invoiceLines').appendChild(tr);
  });
  document.getElementById('invoiceLines')?.addEventListener('click', function(e) {
    if (e.target.closest('.ipl-del')) { e.target.closest('tr').remove(); calcInvoiceTotals(); }
  });
  calcInvoiceTotals();


  document.querySelectorAll('.btn-ajax-start-work').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting…';
      const fd = new FormData();
      const csrf = getCsrf(); fd.append(csrf.name, csrf.value);
      fetch(btn.dataset.url, { method:'POST', body:fd })
        .then(r => r.json()).then(d => {
          if (d.ok) { showToast('Work started!', 'success'); setTimeout(() => location.reload(), 600); }
          else { showToast(d.msg || 'Error', 'error'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill me-1"></i>Start Work'; }
        }).catch(() => location.reload());
    });
  });
});
</script>

</div>

<?php if (session()->getFlashdata('open_modal') === 'createJobCard'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('createJobCardModal');
  if (el && typeof bootstrap !== 'undefined') new bootstrap.Modal(el).show();
});
</script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (location.hash && (location.hash === '#wo-workflow' || location.hash === '#wo-actions')) {
    var t = document.querySelector(location.hash);
    if (t) setTimeout(function() { t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
  }
});
</script>
<?= $this->endSection() ?>
