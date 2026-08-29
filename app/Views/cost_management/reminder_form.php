<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><?= $reminder ? 'Edit Reminder' : 'New Cost Reminder' ?></h1></div>
  <a href="<?= base_url('cost-management?section=reminders') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= $reminder ? base_url('cost-management/reminders/'.$reminder['id'].'/update') : base_url('cost-management/reminders/store') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Title <span class="text-danger">*</span></label>
      <input name="title" class="form-control" required value="<?= esc($reminder['title']??old('title')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Type</label>
      <select name="type" class="form-select">
        <?php foreach (['general','insurance','tax','service_contract','utility','maintenance','other'] as $t): ?>
          <option value="<?= $t ?>" <?= ($reminder['type']??'general')===$t?'selected':'' ?>><?= str_replace('_',' ',ucfirst($t)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <label class="form-label">Property</label>
      <select name="facility_id" class="form-select">
        <option value="">—</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= ($reminder['facility_id']??'')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Due Date</label>
      <input type="date" name="due_date" class="form-control" value="<?= esc($reminder['due_date']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Recurrence</label>
      <select name="recurrence" class="form-select">
        <option value="">One-time</option>
        <?php foreach (['monthly','quarterly','bi-annual','annual'] as $r): ?>
          <option value="<?= $r ?>" <?= ($reminder['recurrence']??'')===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Amount</label>
      <input type="number" step="0.01" name="amount" class="form-control" value="<?= esc($reminder['amount']??'') ?>">
    </div>
    <?php if ($reminder): ?>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <?php foreach (['pending','done','snoozed'] as $s): ?>
          <option value="<?= $s ?>" <?= ($reminder['status']??'pending')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"><?= esc($reminder['notes']??'') ?></textarea>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-fm-primary"><?= $reminder ? 'Update' : 'Create' ?> Reminder</button>
    <a href="<?= base_url('cost-management?section=reminders') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
