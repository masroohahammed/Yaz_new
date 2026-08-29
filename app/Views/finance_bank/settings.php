<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-gear me-2"></i>Finance Settings</h1></div>
<form method="post" action="<?= base_url('finance-bank/settings/save') ?>"><?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-6"><div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Number Prefixes</h5></div><div class="fm-card-body row g-2">
    <?php foreach (['fin_prefix_income'=>'Income','fin_prefix_expense'=>'Expense','fin_prefix_deposit'=>'Deposit','fin_prefix_withdrawal'=>'Withdrawal','fin_prefix_transfer'=>'Transfer','fin_prefix_receipt'=>'Receipt','fin_prefix_payment'=>'Payment'] as $k=>$l): ?>
    <div class="col-6"><label class="form-label small"><?= $l ?></label><input name="<?= $k ?>" class="form-control form-control-sm" value="<?= esc($settings[$k] ?? '') ?>"></div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-lg-6"><div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Approval Thresholds (QAR)</h5></div><div class="fm-card-body row g-2">
    <div class="col-6"><label class="form-label small">Tier 1 max</label><input name="fin_approval_tier1_max" class="form-control form-control-sm" value="<?= esc($settings['fin_approval_tier1_max'] ?? '') ?>"></div>
    <div class="col-6"><label class="form-label small">Tier 2 max</label><input name="fin_approval_tier2_max" class="form-control form-control-sm" value="<?= esc($settings['fin_approval_tier2_max'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label small">Tier 1 roles (comma-separated)</label><input name="fin_approval_tier1_roles" class="form-control form-control-sm" value="<?= esc($settings['fin_approval_tier1_roles'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label small">Tier 2 roles</label><input name="fin_approval_tier2_roles" class="form-control form-control-sm" value="<?= esc($settings['fin_approval_tier2_roles'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label small">Tier 3 roles</label><input name="fin_approval_tier3_roles" class="form-control form-control-sm" value="<?= esc($settings['fin_approval_tier3_roles'] ?? '') ?>"></div>
    <div class="col-6"><label class="form-label small">Approval enabled</label><select name="fin_approval_enabled" class="form-select form-select-sm"><option value="1" <?= ($settings['fin_approval_enabled']??'1')==='1'?'selected':'' ?>>Yes</option><option value="0">No</option></select></div>
    <div class="col-6"><label class="form-label small">Allow self-approval override</label><select name="fin_self_approval_override" class="form-select form-select-sm"><option value="0" <?= ($settings['fin_self_approval_override']??'0')==='0'?'selected':'' ?>>No</option><option value="1">Yes</option></select></div>
    <div class="col-12"><label class="form-label small">Default low balance alert</label><input name="fin_low_balance_default" class="form-control form-control-sm" value="<?= esc($settings['fin_low_balance_default'] ?? '') ?>"></div>
  </div></div></div>
</div>
<button class="btn btn-fm-primary mt-3">Save Settings</button>
</form>
<?= $this->endSection() ?>
