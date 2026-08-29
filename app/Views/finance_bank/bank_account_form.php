<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-bank2 me-2"></i><?= esc($title) ?></h1></div>
<div class="row"><div class="col-lg-8">
<form method="post" action="<?= base_url($account ? 'finance-bank/bank-accounts/update/'.$account['id'] : 'finance-bank/bank-accounts/store') ?>">
<?= csrf_field() ?>
<div class="fm-card mb-3"><div class="fm-card-body">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Account Name *</label><input name="name" class="form-control" required value="<?= esc($account['name'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Bank Name</label><input name="bank_name" class="form-control" value="<?= esc($account['bank_name'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Branch Name</label><input name="branch_name" class="form-control" value="<?= esc($account['branch_name'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Account Number</label><input name="account_number" class="form-control" value="<?= esc($account['account_number'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">IBAN</label><input name="iban" class="form-control" value="<?= esc($account['iban'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">SWIFT/BIC</label><input name="swift_bic" class="form-control" value="<?= esc($account['swift_bic'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Currency</label><input name="currency" class="form-control" value="<?= esc($account['currency'] ?? $currency) ?>"></div>
    <div class="col-md-4"><label class="form-label">Account Type</label>
      <select name="account_type" class="form-select">
        <?php foreach (['current','savings','corporate','other'] as $t): ?>
        <option value="<?= $t ?>" <?= ($account['account_type'] ?? 'current') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4"><label class="form-label">Scope</label>
      <select name="scope_type" class="form-select">
        <?php foreach (['company'=>'Company-wide','branch'=>'Branch','property'=>'Property'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($account['scope_type'] ?? 'company') === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (! $account): ?>
    <div class="col-md-4"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="0"></div>
    <div class="col-md-4"><label class="form-label">Opening Balance Date</label><input type="date" name="opening_balance_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <?php endif; ?>
    <div class="col-md-4"><label class="form-label">Min Balance Alert</label><input type="number" step="0.01" name="min_balance_alert" class="form-control" value="<?= esc($account['min_balance_alert'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Branch</label>
      <select name="branch_id" class="form-select"><option value="">—</option>
      <?php foreach ($branches as $br): ?><option value="<?= $br['id'] ?>" <?= (int)($account['branch_id']??0)===(int)$br['id']?'selected':'' ?>><?= esc($br['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6"><label class="form-label">Property</label>
      <select name="facility_id" class="form-select"><option value="">—</option>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= (int)($account['facility_id']??0)===(int)$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc($account['notes'] ?? '') ?></textarea></div>
    <?php if ($account): ?>
    <div class="col-md-4"><label class="form-label">Status</label>
      <select name="status" class="form-select">
        <?php foreach (['active','inactive','closed'] as $s): ?><option value="<?= $s ?>" <?= ($account['status']??'active')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
  </div>
</div></div>
<button class="btn btn-fm-primary"><?= $account ? 'Update Account' : 'Create Account' ?></button>
<a href="<?= base_url('finance-bank/bank-accounts') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
</form>
</div></div>
<?= $this->endSection() ?>
