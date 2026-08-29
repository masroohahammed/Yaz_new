<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-diagram-3 me-2"></i>Workflow Configuration</h1>
    <div class="small text-muted">Control gates for work order completion, QA, client sign-off, and invoicing.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<?= form_open(base_url('settings/workflow/save')) ?>
<div class="fm-card"><div class="fm-card-body">
  <?php
  $items = [
    'wf_require_supervisor_approval' => ['Supervisor / budget approval', 'High/critical WOs must be approved before completion.'],
    'wf_require_labor_or_material'   => ['Labor or materials on complete', 'Require labor, materials, or actual cost before marking completed.'],
    'wf_require_qa_on_complete'      => ['QA review after completion', 'Move completed WOs to QA pending automatically.'],
    'wf_require_client_approval'     => ['Client approval before close', 'Client must approve after QA before closure.'],
    'wf_require_invoice_before_close'=> ['Invoice before close', 'Generate invoice before WO can be closed.'],
    'wf_auto_invoice_on_client_approve' => ['Auto-invoice on client approve (legacy)', 'OFF = use Invoice Preparation popup after QC instead of auto-creating invoice.'],
  ];
  foreach ($items as $key => [$label, $help]):
    $on = ($workflow[$key] ?? '1') === '1';
  ?>
  <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
    <div>
      <div class="fw-semibold"><?= esc($label) ?></div>
      <div class="small text-muted"><?= esc($help) ?></div>
    </div>
    <div class="form-check form-switch">
      <input type="checkbox" name="<?= esc($key) ?>" value="1" class="form-check-input" role="switch" style="width:48px;height:24px" <?= $on ? 'checked' : '' ?>>
    </div>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-fm-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i>Save Workflow Settings</button>
</div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
