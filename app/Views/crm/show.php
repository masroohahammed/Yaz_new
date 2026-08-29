<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><?= esc($lead['lead_number']) ?> — <?= esc($lead['full_name']) ?></h1>
    <div class="small text-muted">Stage: <strong><?= esc($lead['stage']) ?></strong> · <?= esc($lead['interest_type']) ?>
      <?php if (!empty($lead['temperature'])): ?>
        · <span class="badge bg-<?= $lead['temperature']==='Hot'?'danger':($lead['temperature']==='Warm'?'warning':'secondary') ?>"><?= esc($lead['temperature']) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-fm-outline btn-sm" data-bs-toggle="modal" data-bs-target="#stageModal"><i class="bi bi-funnel me-1"></i>Stage</button>
    <button class="btn btn-fm-outline btn-sm" data-bs-toggle="modal" data-bs-target="#visitModal"><i class="bi bi-calendar-check me-1"></i>Schedule Visit</button>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#convertModal"><i class="bi bi-arrow-right-circle me-1"></i>Convert</button>
    <a href="<?= base_url('crm/'.$lead['id'].'/edit') ?>" class="btn btn-fm-outline btn-sm">Edit</a>
    <a href="<?= base_url('crm') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small mb-3">Lead Details</h6>
      <dl class="row mb-0">
        <dt class="col-5">Phone</dt><dd class="col-7"><?= esc($lead['phone']??'—') ?></dd>
        <dt class="col-5">Email</dt><dd class="col-7"><?= esc($lead['email']??'—') ?></dd>
        <dt class="col-5">Nationality</dt><dd class="col-7"><?= esc($lead['nationality']??'—') ?></dd>
        <dt class="col-5">Source</dt><dd class="col-7"><?= esc($lead['source']??'—') ?></dd>
        <dt class="col-5">Budget</dt><dd class="col-7"><?= $lead['budget_min']?number_format((float)$lead['budget_min']):'—' ?> – <?= $lead['budget_max']?number_format((float)$lead['budget_max']):'—' ?></dd>
        <dt class="col-5">Bedrooms</dt><dd class="col-7"><?= $lead['bedrooms']??'—' ?></dd>
        <dt class="col-5">Preferred Location</dt><dd class="col-7"><?= esc($lead['preferred_location']??'—') ?></dd>
        <dt class="col-5">Assigned</dt><dd class="col-7"><?= esc($lead['assigned_name']??'—') ?></dd>
        <dt class="col-5">Follow-up</dt><dd class="col-7"><?= esc($lead['follow_up_date']??'—') ?></dd>
        <?php if (!empty($lead['lost_reason'])): ?>
          <dt class="col-5">Lost Reason</dt><dd class="col-7"><?= esc($lead['lost_reason']) ?></dd>
        <?php endif; ?>
      </dl>
      <?php if (!empty($lead['notes'])): ?>
        <hr><p class="mb-0 small text-muted"><?= nl2br(esc($lead['notes'])) ?></p>
      <?php endif; ?>
    </div>

    <?php if (!empty($visits)): ?>
    <div class="form-card mt-3">
      <h6 class="text-muted text-uppercase small mb-3">Visits (<?= count($visits) ?>)</h6>
      <?php foreach ($visits as $v): ?>
      <div class="border rounded p-2 mb-2 small">
        <strong><?= esc($v['visit_date']) ?></strong><?= $v['visit_time'] ? ' ' . esc($v['visit_time']) : '' ?>
        <?php if ($v['visit_type']): ?> · <?= esc($v['visit_type']) ?><?php endif; ?>
        <?php if ($v['facility_name']): ?><br><i class="bi bi-building me-1"></i><?= esc($v['facility_name']) ?><?php endif; ?>
        <?php if ($v['unit_number']): ?> / <?= esc($v['unit_number']) ?><?php endif; ?>
        <?php if ($v['rating']): ?> · ⭐<?= esc($v['rating']) ?>/5<?php endif; ?>
        <?php if ($v['customer_feedback']): ?><br class="text-muted"><?= esc($v['customer_feedback']) ?><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-7">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small">Activities</h6>
      <table class="table table-registry table-sm"><thead><tr><th>Type</th><th>Subject</th><th>When</th></tr></thead>
      <tbody>
        <?php foreach ($activities as $a): ?>
        <tr>
          <td><?= esc($a['activity_type']) ?></td>
          <td><?= esc($a['subject']??$a['description']??'—') ?></td>
          <td class="small text-muted"><?= esc($a['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($activities)): ?>
          <tr><td colspan="3" class="text-muted text-center py-3">No activities yet.</td></tr>
        <?php endif; ?>
      </tbody></table>
      <form method="post" action="<?= base_url('crm/'.$lead['id'].'/activity') ?>" class="mt-3"><?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-md-3">
            <select name="activity_type" class="form-select form-select-sm" required>
              <option value="call">Call</option>
              <option value="email">Email</option>
              <option value="meeting">Meeting</option>
              <option value="note">Note</option>
            </select>
          </div>
          <div class="col-md-5"><input name="subject" class="form-control form-control-sm" placeholder="Subject"></div>
          <div class="col-md-4"><button class="btn btn-fm-primary btn-sm w-100">Add Activity</button></div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Stage Modal -->
<div class="modal fade" id="stageModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= base_url('crm/'.$lead['id'].'/stage') ?>"><?= csrf_field() ?>
    <div class="modal-header"><h5 class="modal-title">Update Stage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3">
        <label class="form-label">Stage</label>
        <select name="stage" class="form-select" id="stageSelect">
          <?php foreach ($stages as $s): ?>
            <option value="<?= $s ?>" <?= $lead['stage']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3" id="lostReasonRow" style="display:<?= $lead['stage']==='lost'?'block':'none' ?>">
        <label class="form-label">Lost Reason</label>
        <textarea name="lost_reason" class="form-control" rows="2"><?= esc($lead['lost_reason']??'') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2"><?= esc($lead['notes']??'') ?></textarea>
      </div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-fm-primary">Update</button></div>
    </form>
  </div></div>
</div>

<!-- Visit Modal -->
<div class="modal fade" id="visitModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post" action="<?= base_url('crm/'.$lead['id'].'/visit') ?>"><?= csrf_field() ?>
    <div class="modal-header"><h5 class="modal-title">Schedule Property Visit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Visit Date <span class="text-danger">*</span></label>
          <input type="date" name="visit_date" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Visit Time</label>
          <input type="time" name="visit_time" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Visit Type</label>
          <select name="visit_type" class="form-select">
            <option value="">— select —</option>
            <option value="in_person">In Person</option>
            <option value="virtual">Virtual</option>
            <option value="drive_by">Drive By</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Property</label>
          <select name="facility_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($facilities??[] as $f): ?>
              <option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Agent</label>
          <select name="agent_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($users??[] as $u): ?>
              <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Rating (1–5)</label>
          <select name="rating" class="form-select">
            <option value="">—</option>
            <?php for ($i=1;$i<=5;$i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label">Customer Feedback</label>
          <textarea name="customer_feedback" class="form-control" rows="2"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-fm-primary">Schedule Visit</button></div>
    </form>
  </div></div>
</div>

<!-- Convert Modal -->
<div class="modal fade" id="convertModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= base_url('crm/'.$lead['id'].'/convert') ?>"><?= csrf_field() ?>
    <div class="modal-header"><h5 class="modal-title">Convert Lead</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p class="text-muted mb-3">Converting <strong><?= esc($lead['full_name']) ?></strong> will mark this lead as <em>Won</em>.</p>
      <div class="mb-3">
        <label class="form-label">Convert To</label>
        <select name="convert_to" class="form-select" required>
          <option value="">— select —</option>
          <option value="tenant">Tenant (create tenant profile)</option>
          <option value="deal">Sales Deal</option>
          <option value="contract">Lease Contract (redirect to create)</option>
        </select>
      </div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-success">Convert</button></div>
    </form>
  </div></div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('stageSelect')?.addEventListener('change', function() {
  document.getElementById('lostReasonRow').style.display = this.value === 'lost' ? 'block' : 'none';
});
</script>
<?= $this->endSection() ?>
