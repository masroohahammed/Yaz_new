<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-bar-chart me-2"></i>Compare Quotations — <?= esc($rfq['rfq_number']??'') ?></h1></div><a href="<?= base_url('procurement/rfq/view/'.$rfq['id']) ?>" class="btn btn-fm-outline btn-sm">Back to RFQ</a></div>
<?php $lowest=$quotations[0]??null; ?>
<?php if(!empty($quotations)): ?>
<div class="fm-card mb-3"><div class="card-header-fm"><h5><i class="bi bi-trophy me-2 text-warning"></i>Recommended: <?= esc($lowest['vendor_name']??'—') ?> — Lowest Price</h5></div></div>
<div class="row g-3">
<?php foreach($quotations as $i=>$q): $isBest=$i===0; ?>
<div class="col-md-4">
  <div class="fm-card <?= $isBest?'border border-2':'border' ?>" style="<?= $isBest?'border-color:var(--fm-primary)!important':'' ?>">
    <?php if($isBest): ?><div class="text-center py-1 small fw-bold text-white" style="background:var(--fm-primary)">⭐ Best Price</div><?php endif; ?>
    <div class="fm-card-body">
      <div class="fw-bold mb-2"><?= esc($q['vendor_name']??'—') ?></div>
      <?php if(!empty($q['vendor_rating'])): ?><div class="small text-warning mb-2"><?= str_repeat('★',min(5,(int)$q['vendor_rating'])) ?></div><?php endif; ?>
      <div class="d-flex justify-content-between small mb-1"><span>Unit Price</span><strong><?= $currency ?> <?= number_format($q['unit_price'],2) ?></strong></div>
      <div class="d-flex justify-content-between small mb-1"><span>Total</span><strong style="color:var(--fm-primary)"><?= $currency ?> <?= number_format($q['total_amount'],2) ?></strong></div>
      <?php if($lowest&&$q['total_amount']>$lowest['total_amount']): ?>
      <div class="small text-danger">+<?= $currency ?> <?= number_format($q['total_amount']-$lowest['total_amount'],2) ?> vs best</div>
      <?php endif; ?>
      <?php if($q['lead_time']): ?><div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i><?= esc($q['lead_time']) ?></div><?php endif; ?>
      <?php if($q['notes']): ?><div class="small text-muted mt-1"><?= esc($q['notes']) ?></div><?php endif; ?>
      <?php if($isBest): ?>
      <a href="<?= base_url('procurement/order/create?vendor_id='.$q['vendor_id'].'&rfq_id='.$rfq['id']) ?>" class="btn btn-fm-primary btn-sm w-100 mt-2">Select & Create PO</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?><p class="text-muted text-center py-5">No quotations to compare.</p><?php endif; ?>
<?= $this->endSection() ?>
