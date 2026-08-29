<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-clipboard2-check me-2 text-success"></i><?= esc($checklist['title']) ?></h1>
    <div class="small text-muted"><?= esc($checklist['facility_name']) ?> &bull; <?= date('d M Y', strtotime($checklist['inspection_date'])) ?> &bull; <?= esc($checklist['type']) ?></div>
  </div>
  <a href="<?= base_url('compliance/inspections') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?php
$isPending    = $checklist['status'] === 'pending' || $checklist['status'] === 'in_progress';
$passed       = count(array_filter($items, fn($i) => $i['result'] === 'pass'));
$failed       = count(array_filter($items, fn($i) => $i['result'] === 'fail'));
$naCount      = count(array_filter($items, fn($i) => $i['result'] === 'na'));
$total        = count($items);
$score        = $checklist['score'] ?? ($total > 0 ? round($passed / $total * 100) : 0);
?>

<?php if(!$isPending): ?>
<!-- Score Summary -->
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="kpi-card kpi-<?= $score>=80?'green':($score>=60?'gold':'red') ?>"><div class="kpi-label">Overall Score</div><div class="kpi-value"><?= $score ?>%</div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-green"><div class="kpi-label">Passed Items</div><div class="kpi-value"><?= $passed ?></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-red"><div class="kpi-label">Failed Items</div><div class="kpi-value"><?= $failed ?></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-label">N/A Items</div><div class="kpi-value"><?= $naCount ?></div></div></div>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-list-check me-2"></i>Checklist Items (<?= $total ?>)</h5>
        <?php if($isPending): ?>
        <span class="fm-badge badge-status-pending">Pending Submission</span>
        <?php else: ?>
        <span class="fm-badge badge-status-<?= $checklist['status']==='passed'?'completed':'cancelled' ?>"><?= ucfirst($checklist['status']) ?></span>
        <?php endif; ?>
      </div>
      <form action="<?= base_url('compliance/inspections/submit/'.$checklist['id']) ?>" method="post">
      <?= csrf_field() ?>
      <div class="fm-card-body p-0">
        <?php if(empty($items)): ?>
        <div class="text-center py-5 text-muted"><i class="bi bi-clipboard2 d-block mb-2" style="font-size:2rem"></i>No checklist items defined.</div>
        <?php endif; ?>
        <?php foreach($items as $idx => $item): ?>
        <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom <?= !$isPending && $item['result']==='fail'?'sla-warn':'' ?>">
          <div class="text-muted small pt-1" style="min-width:24px"><?= $idx+1 ?>.</div>
          <div class="flex-grow-1">
            <div class="fw-semibold small mb-2"><?= esc($item['item_text']) ?></div>
            <?php if($isPending): ?>
            <div class="d-flex gap-2 flex-wrap">
              <label class="d-flex align-items-center gap-1 small">
                <input type="radio" name="results[<?= $item['id'] ?>]" value="pass" <?= $item['result']==='pass'?'checked':'' ?> required>
                <span class="text-success fw-semibold">✓ Pass</span>
              </label>
              <label class="d-flex align-items-center gap-1 small">
                <input type="radio" name="results[<?= $item['id'] ?>]" value="fail" <?= $item['result']==='fail'?'checked':'' ?>>
                <span class="text-danger fw-semibold">✗ Fail</span>
              </label>
              <label class="d-flex align-items-center gap-1 small">
                <input type="radio" name="results[<?= $item['id'] ?>]" value="na" <?= $item['result']==='na'?'checked':'' ?>>
                <span class="text-muted fw-semibold">N/A</span>
              </label>
            </div>
            <input type="text" name="remarks[<?= $item['id'] ?>]" class="form-control form-control-sm mt-2" placeholder="Remarks (optional)" value="<?= esc($item['remarks'] ?? '') ?>">
            <?php else: ?>
            <div class="d-flex align-items-center gap-2">
              <?php if($item['result']==='pass'): ?>
              <span class="fm-badge badge-status-completed"><i class="bi bi-check-lg me-1"></i>Pass</span>
              <?php elseif($item['result']==='fail'): ?>
              <span class="fm-badge badge-status-cancelled"><i class="bi bi-x-lg me-1"></i>Fail</span>
              <?php else: ?>
              <span class="fm-badge badge-status-closed">N/A</span>
              <?php endif; ?>
              <?php if(!empty($item['remarks'])): ?>
              <span class="small text-muted"><?= esc($item['remarks']) ?></span>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if($isPending && !empty($items)): ?>
        <div class="px-4 py-3 border-bottom">
          <label class="form-label">Overall Remarks</label>
          <textarea name="overall_remarks" class="form-control" rows="3" placeholder="Summary remarks, recommendations..."><?= esc($checklist['overall_remarks'] ?? '') ?></textarea>
        </div>
        <div class="px-4 py-3">
          <button type="submit" class="btn btn-fm-primary" onclick="return confirm('Submit this inspection? This action marks it as complete.')">
            <i class="bi bi-check-circle me-1"></i>Submit Inspection
          </button>
        </div>
        <?php endif; ?>
      </div>
      </form>
    </div>
  </div>

    <div class="col-lg-4">
    <?php if(!$isPending && $failed > 0): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-cash-coin me-2 text-warning"></i>Damage Charge</h5></div>
      <div class="fm-card-body">
        <p class="small text-muted mb-3"><?= $failed ?> failed item(s) detected. Create a damage invoice or lease payment charge.</p>
        <button class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#convertInvoiceModal">
          <i class="bi bi-receipt me-1"></i>Convert to Invoice / Charge
        </button>
      </div>
    </div>
    <?php endif; ?>

    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-info-circle me-2"></i>Details</h5></div>
      <div class="fm-card-body">
        <table class="w-100" style="font-size:.83rem">
          <tr><td class="text-muted py-1" style="width:45%">Facility</td><td class="fw-semibold"><?= esc($checklist['facility_name']) ?></td></tr>
          <tr><td class="text-muted py-1">Type</td><td><?= esc($checklist['type']) ?></td></tr>
          <tr><td class="text-muted py-1">Date</td><td><?= date('d M Y', strtotime($checklist['inspection_date'])) ?></td></tr>
          <tr><td class="text-muted py-1">Inspector</td><td><?= esc($checklist['inspector_name'] ?: $checklist['created_by_name']) ?></td></tr>
          <tr><td class="text-muted py-1">Status</td><td><span class="fm-badge badge-status-<?= match($checklist['status']){
            'passed'=>'completed','failed'=>'cancelled','in_progress'=>'in_progress',default=>'pending'
          } ?>"><?= ucfirst(str_replace('_',' ',$checklist['status'])) ?></span></td></tr>
          <?php if($checklist['completed_at']): ?>
          <tr><td class="text-muted py-1">Completed</td><td><?= date('d M Y H:i', strtotime($checklist['completed_at'])) ?></td></tr>
          <?php endif; ?>
        </table>
        <?php if(!empty($checklist['notes'])): ?>
        <hr>
        <div class="small text-muted"><?= nl2br(esc($checklist['notes'])) ?></div>
        <?php endif; ?>
        <?php if(!empty($checklist['overall_remarks']) && !$isPending): ?>
        <hr>
        <div class="small fw-semibold mb-1">Overall Remarks</div>
        <div class="small text-muted"><?= nl2br(esc($checklist['overall_remarks'])) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if(!$isPending && $total > 0): ?>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5><i class="bi bi-pie-chart me-2"></i>Summary</h5></div>
      <div class="fm-card-body">
        <div class="mb-2">
          <div class="d-flex justify-content-between small mb-1"><span>Pass</span><span class="text-success fw-bold"><?= $passed ?> / <?= $total ?></span></div>
          <div class="health-bar"><div style="width:<?= $total>0?round($passed/$total*100):0 ?>%;background:var(--fm-green);height:6px;border-radius:99px"></div></div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between small mb-1"><span>Fail</span><span class="text-danger fw-bold"><?= $failed ?></span></div>
          <div class="health-bar"><div style="width:<?= $total>0?round($failed/$total*100):0 ?>%;background:var(--fm-red);height:6px;border-radius:99px"></div></div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<!-- Convert to Invoice Modal -->
<div class="modal fade" id="convertInvoiceModal" tabindex="-1" aria-labelledby="convertInvoiceLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('compliance/inspections/'.$checklist['id'].'/convert-invoice') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="convertInvoiceLabel"><i class="bi bi-receipt me-2"></i>Convert Inspection to Charge</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Amount <span class="text-danger">*</span></label>
            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Link To</label>
            <select name="link_to" class="form-select">
              <option value="invoice">Invoice (Draft)</option>
              <option value="lease_payment">Lease Payment (Damage Charge)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Damage description, items..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="bi bi-receipt me-1"></i>Create Charge</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
