<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-percent me-2 text-primary"></i>Commission Rules</h1></div>
  <a href="<?= base_url('sales') ?>" class="btn btn-fm-outline btn-sm">Back to Sales</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small mb-3">Add New Rule</h6>
      <form method="post" action="<?= base_url('sales/commission-rules/store') ?>"><?= csrf_field() ?>
        <?php if (session()->getFlashdata('errors')): ?>
          <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Rule Name <span class="text-danger">*</span></label>
          <input name="rule_name" class="form-control" required value="<?= esc(old('rule_name')) ?>">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Deal Type</label>
            <select name="deal_type" class="form-select">
              <option value="">Any</option>
              <option value="Sale">Sale</option>
              <option value="Lease">Lease</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Commission Type</label>
            <select name="commission_type" class="form-select">
              <option value="percentage">Percentage</option>
              <option value="flat">Flat Amount</option>
            </select>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Agent Rate</label>
            <input type="number" step="0.01" name="agent_rate" class="form-control" required value="<?= esc(old('agent_rate')) ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Company Rate</label>
            <input type="number" step="0.01" name="company_rate" class="form-control" required value="<?= esc(old('company_rate')) ?>">
          </div>
        </div>
        <button class="btn btn-fm-primary btn-sm">Add Rule</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="form-card p-0">
      <table class="table table-registry table-sm mb-0">
        <thead><tr><th>Name</th><th>Type</th><th>Mode</th><th>Agent</th><th>Company</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rules as $r): ?>
          <tr>
            <td><?= esc($r['rule_name']) ?></td>
            <td><?= esc($r['deal_type']??'Any') ?></td>
            <td><?= esc($r['commission_type']) ?></td>
            <td><?= $r['agent_rate'] ?><?= $r['commission_type']==='percentage'?'%':'' ?></td>
            <td><?= $r['company_rate'] ?><?= $r['commission_type']==='percentage'?'%':'' ?></td>
            <td>
              <form method="post" action="<?= base_url('sales/commission-rules/'.$r['id'].'/delete') ?>" onsubmit="return confirm('Delete this rule?')"><?= csrf_field() ?>
                <button class="btn btn-danger btn-sm">Del</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($rules)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No commission rules defined.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
