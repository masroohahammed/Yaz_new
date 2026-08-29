<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $i = $inspection; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-link-45deg me-2 text-primary"></i>Link Inspection</h1>
    <div class="small text-muted">Unit <?= esc($i['unit_number']) ?> · <?= esc(ucfirst(str_replace('_', ' ', (string) ($i['type'] ?? '')))) ?></div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 mt-1">
      <li class="breadcrumb-item"><a href="<?= base_url('pm-inspections/view/' . $i['id']) ?>">Inspection</a></li>
      <li class="breadcrumb-item active">Link</li>
    </ol></nav>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <?= form_open(base_url('pm-inspections/link/' . $i['id'])) ?>
    <?= csrf_field() ?>
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0">Link to record</h5></div>
      <div class="fm-card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold small">Link type</label>
          <select name="link_to" id="linkTo" class="form-select">
            <option value="maintenance_request" <?= ($i['link_to'] ?? '') === 'maintenance_request' ? 'selected' : '' ?>>Maintenance request / Work order</option>
            <option value="contract" <?= ($i['link_to'] ?? '') === 'contract' ? 'selected' : '' ?>>Lease contract</option>
          </select>
        </div>
        <div class="mb-3" id="workOrderGroup">
          <label class="form-label fw-semibold small">Work order</label>
          <select id="refWorkOrder" class="form-select ref-select">
            <option value="">Select work order…</option>
            <?php foreach ($workOrders as $w): ?>
            <option value="<?= $w['id'] ?>" data-type="maintenance_request" <?= (int) ($i['ref_id'] ?? 0) === (int) $w['id'] && ($i['link_to'] ?? '') !== 'contract' ? 'selected' : '' ?>>
              WO <?= esc($w['wo_number'] ?? $w['id']) ?> — <?= esc($w['title'] ?? '') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3 d-none" id="contractGroup">
          <label class="form-label fw-semibold small">Contract</label>
          <select id="refContract" class="form-select ref-select">
            <option value="">Select contract…</option>
            <?php foreach ($contracts as $c): ?>
            <option value="<?= $c['id'] ?>" data-type="contract" <?= (int) ($i['ref_id'] ?? 0) === (int) $c['id'] && ($i['link_to'] ?? '') === 'contract' ? 'selected' : '' ?>>
              <?= esc($c['contract_number'] ?? 'Contract #' . $c['id']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <input type="hidden" name="ref_id" id="refIdHidden" value="<?= (int) ($i['ref_id'] ?? 0) ?>">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-fm-primary"><i class="bi bi-link me-1"></i>Save Link</button>
          <a href="<?= base_url('pm-inspections/view/' . $i['id']) ?>" class="btn btn-fm-outline">Cancel</a>
        </div>
      </div>
    </div>
    <?= form_close() ?>
  </div>
</div>

<script>
(function() {
  const linkTo = document.getElementById('linkTo');
  const woGroup = document.getElementById('workOrderGroup');
  const contractGroup = document.getElementById('contractGroup');
  const refWo = document.getElementById('refWorkOrder');
  const refContract = document.getElementById('refContract');
  const refHidden = document.getElementById('refIdHidden');

  function sync() {
    const isContract = linkTo.value === 'contract';
    woGroup.classList.toggle('d-none', isContract);
    contractGroup.classList.toggle('d-none', !isContract);
    refHidden.value = isContract ? (refContract.value || '') : (refWo.value || '');
  }

  linkTo.addEventListener('change', sync);
  refWo.addEventListener('change', function() { refHidden.value = refWo.value; });
  refContract.addEventListener('change', function() { refHidden.value = refContract.value; });
  sync();
})();
</script>
<?= $this->endSection() ?>
