<?php
$role          = session()->get('user_role') ?? '';
$canManage     = in_array($role, ['super_admin', 'facility_manager'], true);
$canSupervisor = in_array($role, ['super_admin', 'facility_manager', 'supervisor'], true);
$stage         = $wo['workflow_stage'] ?? 'converted_to_wo';
$hasJobCards   = ! empty($jobCards);
$primaryJc     = $hasJobCards ? $jobCards[0] : null;
?>
<div class="fm-card mb-3" id="wo-actions">
  <div class="card-header-fm">
    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Workflow Actions</h5>
  </div>
  <div class="fm-card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php if ($canManage && $stage === 'converted_to_wo'): ?>
      <button type="button" class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignSupervisorModal">
        <i class="bi bi-person-check me-1"></i>Assign Supervisor
      </button>
      <?php endif; ?>

      <?php if ($canSupervisor && in_array($stage, ['assigned_to_supervisor', 'job_card_created'], true) && ! $hasJobCards): ?>
      <button type="button" class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createJobCardModal">
        <i class="bi bi-card-checklist me-1"></i>Create Job Card
      </button>
      <?php endif; ?>

      <?php if ($canSupervisor && in_array($stage, ['technician_assigned', 'planning_scheduling'], true) && $primaryJc && ($primaryJc['status'] ?? '') === 'draft'): ?>
      <button type="button" class="btn btn-success btn-sm"
              data-bs-toggle="modal" data-bs-target="#startWorkModal">
        <i class="bi bi-play-fill me-1"></i>Start Work
      </button>
      <?php endif; ?>

      <?php if (in_array($stage, ['technician_assigned', 'planning_scheduling', 'work_execution'], true) && $primaryJc && ($primaryJc['status'] ?? '') === 'in_progress'): ?>
      <button type="button" class="btn btn-fm-primary btn-sm"
              data-bs-toggle="modal" data-bs-target="#completeJobCardModal">
        <i class="bi bi-check2-circle me-1"></i>Complete Job Card
      </button>
      <?php endif; ?>

      <?php if ($canManage && $stage === 'technician_assigned'): ?>
      <button type="button" class="btn btn-fm-outline btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleWoModal">
        <i class="bi bi-calendar-event me-1"></i>Set Schedule
      </button>
      <?php endif; ?>

      <?php if ($stage === 'inspection_qc' && $canManage): ?>
      <a href="<?= base_url('workorders/view/' . $wo['id'] . '#tab-closure') ?>" class="btn btn-fm-outline btn-sm">
        <i class="bi bi-shield-check me-1"></i>QA / Closure
      </a>
      <?php endif; ?>
    </div>

    <?php if ($hasJobCards): ?>
    <div class="table-responsive border rounded">
      <table class="fm-table table-sm mb-0">
        <thead><tr><th>Job Card</th><th>Technician</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($jobCards as $jc): ?>
          <tr>
            <td class="small fw-semibold"><?= esc($jc['jc_number']) ?></td>
            <td class="small"><?= esc($jc['technician_name'] ?? '—') ?></td>
            <td><span class="fm-badge badge-status-<?= esc($jc['status']) ?>"><?= ucfirst(str_replace('_', ' ', $jc['status'])) ?></span></td>
            <td class="text-end text-nowrap">
              <?php if (($jc['status'] ?? '') === 'draft' && $canSupervisor): ?>
              <button type="button" class="btn btn-sm btn-success py-0"
                      data-bs-toggle="modal"
                      data-bs-target="#startWorkModal"
                      data-jc-id="<?= (int)$jc['id'] ?>"
                      data-start-url="<?= base_url('job-cards/' . $jc['id'] . '/start') ?>">Start</button>
              <?php endif; ?>
              <?php if (($jc['status'] ?? '') === 'in_progress'): ?>
              <button type="button" class="btn btn-sm btn-fm-primary py-0"
                      data-bs-toggle="modal" data-bs-target="#completeJobCardModal">Complete</button>
              <?php endif; ?>
              <a href="<?= base_url('job-cards/' . $jc['id']) ?>" class="btn btn-sm btn-outline-secondary py-0" target="_blank" title="Open in new tab">Details</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="small text-muted mb-0 mt-2"><i class="bi bi-info-circle me-1"></i>All steps stay on this work order — use the lifecycle bar above to track progress.</p>
    <?php elseif (! $canManage && ! $canSupervisor): ?>
    <p class="small text-muted mb-0">No actions available for your role at this stage.</p>
    <?php endif; ?>
  </div>
</div>

<?php if ($canManage && $stage === 'converted_to_wo'): ?>
<div class="modal fade" id="assignSupervisorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <?= form_open(base_url('workorders/assign-supervisor/' . $wo['id']), ['id' => 'assignSupervisorForm']) ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-check me-2"></i>Assign Supervisor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Supervisor <span class="text-danger">*</span></label>
        <select name="supervisor_id" class="form-select" required>
          <option value="">Select supervisor…</option>
          <?php foreach ($supervisors ?? [] as $s): ?>
          <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary">Assign &amp; Continue</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($canSupervisor && in_array($stage, ['assigned_to_supervisor', 'job_card_created'], true) && ! $hasJobCards): ?>
<div class="modal fade" id="createJobCardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <?= form_open(base_url('job-cards/' . $wo['id'])) ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-card-checklist me-2"></i>Create Job Card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-light border small mb-3 mb-0">
          <strong><?= esc($wo['wo_number']) ?></strong> — <?= esc($wo['title']) ?>
        </div>
        <div class="mb-3">
          <label class="form-label">Assign Technician <span class="text-danger">*</span></label>
          <select name="assigned_to" class="form-select" required>
            <option value="">Select technician…</option>
            <?php foreach ($technicians ?? [] as $t): ?>
            <option value="<?= $t['id'] ?>" <?= (string) old('assigned_to') === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Job Description <span class="text-danger">*</span></label>
          <textarea name="description" class="form-control" rows="4" required><?= esc(old('description') ?: ($wo['description'] ?? $wo['title'])) ?></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Scheduled Date</label>
            <input type="date" name="scheduled_date" class="form-control" value="<?= esc(old('scheduled_date') ?: date('Y-m-d')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Estimated Hours</label>
            <input type="number" name="scheduled_hours" class="form-control" step="0.5" min="0.5" value="<?= esc(old('scheduled_hours') ?: '2') ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary">Create &amp; Stay on Work Order</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($canManage && $stage === 'technician_assigned'): ?>
<div class="modal fade" id="scheduleWoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <?= form_open(base_url('workorders/schedule/' . $wo['id'])) ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>Planning &amp; Scheduling</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Planned Start</label>
          <input type="datetime-local" name="planned_start" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Planned End</label>
          <input type="datetime-local" name="planned_end" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary">Save &amp; Continue</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($primaryJc): ?>
<!-- ── Start Work Confirmation Modal ───────────────────────────────────── -->
<div class="modal fade" id="startWorkModal" tabindex="-1" aria-labelledby="startWorkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <?= form_open(base_url('job-cards/' . $primaryJc['id'] . '/start'), ['id' => 'startWorkForm']) ?>
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="startWorkModalLabel"><i class="bi bi-play-fill me-2 text-success"></i>Start Work</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">You are about to start work on:</p>
        <div class="alert alert-light border small mb-0">
          <strong><?= esc($wo['wo_number']) ?></strong> — <?= esc($wo['title']) ?><br>
          <span class="text-muted">Job Card: <?= esc($primaryJc['jc_number']) ?></span>
        </div>
        <p class="small text-muted mt-3 mb-0">This will set the job card status to <strong>In Progress</strong> and record the start time.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success"><i class="bi bi-play-fill me-1"></i>Confirm Start</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<!-- ── Complete Job Card Modal ─────────────────────────────────────────── -->
<?php if ($primaryJc['status'] === 'in_progress'): ?>
<?= view('workorders/_jc_complete_modal', ['jc' => $primaryJc, 'wo' => $wo]) ?>
<?php endif; ?>
<?php endif; ?>

<script>
// Update startWorkForm action when Start triggered from table row button
document.addEventListener('DOMContentLoaded', function () {
  var startModal = document.getElementById('startWorkModal');
  if (!startModal) return;
  startModal.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    if (btn && btn.dataset.startUrl) {
      var form = document.getElementById('startWorkForm');
      if (form) form.action = btn.dataset.startUrl;
    }
  });
});
</script>
