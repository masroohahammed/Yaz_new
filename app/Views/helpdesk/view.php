<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-ticket me-2 text-primary"></i><?= esc($req['ticket_number']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('helpdesk') ?>">Help Desk</a></li><li class="breadcrumb-item active"><?= esc($req['ticket_number']) ?></li></ol></nav>
  </div>
  <span class="fm-badge badge-status-<?= esc($req['status']) ?> fs-6"><?= ucfirst($req['status']) ?></span>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Request Details</h6>
      <div class="row g-3">
        <div class="col-md-6"><div class="small text-muted">Requester</div><div class="fw-semibold"><?= esc($req['requester_name']) ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Contact</div><div class="small"><?= esc($req['requester_email']??'—') ?> / <?= esc($req['requester_phone']??'—') ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Facility</div><div><?= esc($req['facility_name']??'Not specified') ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Category</div><div><?= esc($req['category']??'—') ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Priority</div><span class="fm-badge badge-priority-<?= esc($req['priority']) ?>"><?= ucfirst($req['priority']) ?></span></div>
        <div class="col-md-6"><div class="small text-muted">Submitted</div><div class="small"><?= date('d M Y H:i', strtotime($req['created_at'])) ?></div></div>
        <?php if(!empty($req['reviewed_by_name'])): ?>
        <div class="col-md-6"><div class="small text-muted">Reviewed by</div><div class="small"><?= esc($req['reviewed_by_name']) ?> · <?= $req['reviewed_at']?date('d M Y H:i',strtotime($req['reviewed_at'])):'' ?></div></div>
        <?php endif; ?>
        <div class="col-12"><div class="small text-muted">Description</div><div class="p-3 rounded" style="background:#f7f9fc"><?= nl2br(esc($req['description'])) ?></div></div>
        <?php if($req['image_path']): ?><div class="col-12"><img src="<?= base_url($req['image_path']) ?>" class="img-fluid rounded" style="max-height:200px" alt="Attachment"></div><?php endif; ?>
      </div>
    </div>
    <?php if($workOrder): ?>
    <div class="fm-form-section">
      <h6><i class="bi bi-check-circle text-success"></i>Work Order Created</h6>
      <a href="<?= base_url('workorders/view/'.$workOrder['id']) ?>" class="btn btn-fm-outline btn-sm">View <?= esc($workOrder['wo_number']) ?></a>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <?php if(!empty($canManage) && $req['status']==='pending'): ?>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-shield-check text-success"></i>Step 2: Verify</h6>
      <p class="small text-muted mb-2">Validate contract eligibility before creating a work order.</p>
      <?= form_open(base_url('helpdesk/verify/'.$req['id'])) ?>
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-success w-100 mb-2" onclick="return confirm('Mark ticket as verified?')">Verify Ticket</button>
      <?= form_close() ?>
      <?= form_open(base_url('helpdesk/reject/'.$req['id'])) ?>
      <?= csrf_field() ?>
      <input type="text" name="reject_reason" class="form-control form-control-sm mb-2" placeholder="Rejection reason (optional)">
      <button type="submit" class="btn btn-outline-danger w-100 btn-sm" onclick="return confirm('Reject this ticket?')">Reject</button>
      <?= form_close() ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($canManage) && in_array($req['status'], ['reviewed','pending'], true) && !$workOrder): ?>
    <div class="fm-form-section">
      <h6><i class="bi bi-arrow-right-circle text-primary"></i>Step 3: Create Work Order</h6>
      <?= form_open(base_url('helpdesk/convert/'.$req['id'])) ?>
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Facility</label>
        <select name="facility_id" class="form-select"><option value="">— Same as request —</option>
        <?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= ($req['facility_id']??'')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Priority</label>
        <select name="priority" class="form-select"><?php foreach(['low','medium','high','critical'] as $p): ?>
        <option value="<?= $p ?>" <?= $req['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Assign Technician</label>
        <select name="assigned_to" class="form-select"><option value="">— Assign later —</option>
        <?php foreach($technicians as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-fm-primary w-100">Convert to Work Order</button>
      <?= form_close() ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
