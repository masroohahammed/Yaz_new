<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><?= $deal ? 'Edit Sales Deal' : 'New Sales Deal' ?></h1></div>
  <a href="<?= base_url('sales') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= $deal ? base_url('sales/'.$deal['id'].'/update') : base_url('sales') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Deal Type</label>
      <select name="deal_type" class="form-select">
        <option value="Sale" <?= ($deal['deal_type']??'')=='Sale'?'selected':'' ?>>Sale</option>
        <option value="Lease" <?= ($deal['deal_type']??'Lease')=='Lease'?'selected':'' ?>>Lease</option>
      </select>
    </div>
    <div class="col-md-8">
      <label class="form-label">Buyer Name <span class="text-danger">*</span></label>
      <input name="buyer_name" class="form-control" required value="<?= esc($deal['buyer_name']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Buyer Phone</label>
      <input name="buyer_phone" class="form-control" value="<?= esc($deal['buyer_phone']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Buyer Email</label>
      <input name="buyer_email" type="email" class="form-control" value="<?= esc($deal['buyer_email']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Stage</label>
      <select name="stage" class="form-select">
        <?php foreach (['prospect','qualified','proposal','negotiation','won','lost'] as $s): ?>
          <option value="<?= $s ?>" <?= ($deal['stage']??'prospect')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Property</label>
      <select name="facility_id" class="form-select">
        <option value="">—</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= ($deal['facility_id']??'')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Deal Value</label>
      <input type="number" step="0.01" name="deal_value" class="form-control" value="<?= esc($deal['deal_value']??'') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Agreed Price</label>
      <input type="number" step="0.01" name="agreed_price" class="form-control" value="<?= esc($deal['agreed_price']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Agent</label>
      <select name="agent_id" class="form-select">
        <option value="">— none —</option>
        <?php foreach ($agents as $a): ?>
          <option value="<?= $a['id'] ?>" <?= ($deal['agent_id']??'')==$a['id']?'selected':'' ?>><?= esc($a['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($commRules)): ?>
    <div class="col-md-4">
      <label class="form-label">Commission Rule</label>
      <select name="commission_rule_id" class="form-select">
        <option value="">— none —</option>
        <?php foreach ($commRules as $r): ?>
          <option value="<?= $r['id'] ?>" <?= ($deal['commission_rule_id']??'')==$r['id']?'selected':'' ?>><?= esc($r['rule_name']) ?> (A:<?= $r['agent_rate'] ?>% / Co:<?= $r['company_rate'] ?>%)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-md-4">
      <label class="form-label">Expected Close Date</label>
      <input type="date" name="expected_close_date" class="form-control" value="<?= esc($deal['expected_close_date']??'') ?>">
    </div>
    <?php if (!empty($leads)): ?>
    <div class="col-md-6">
      <label class="form-label">Link to CRM Lead</label>
      <select name="lead_id" class="form-select">
        <option value="">— none —</option>
        <?php foreach ($leads as $l): ?>
          <option value="<?= $l['id'] ?>" <?= ($deal['lead_id']??'')==$l['id']?'selected':'' ?>><?= esc($l['lead_number']) ?> — <?= esc($l['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"><?= esc($deal['notes']??'') ?></textarea>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-fm-primary"><?= $deal ? 'Update Deal' : 'Create Deal' ?></button>
    <a href="<?= base_url('sales') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
