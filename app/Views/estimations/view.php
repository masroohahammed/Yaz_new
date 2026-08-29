<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $canViewInternal = $canViewInternal ?? true; ?>
<div class="page-header">
  <div><h1><i class="bi bi-calculator me-2"></i><?= esc($est['est_number']) ?></h1><span class="fm-badge badge-status-<?= esc($est['status']) ?>"><?= ucfirst(str_replace('_',' ',$est['status'])) ?></span></div>
  <div class="d-flex gap-2">
    <?php if($est['status']==='pending_approval' && in_array(session()->get('user_role'),['super_admin','facility_manager','finance_manager'])): ?>
    <?= form_open(base_url('estimations/approve/'.$est['id'])) ?>
    <button type="submit" class="btn btn-fm-primary btn-sm" onclick="return confirm('Approve this estimation?')"><i class="bi bi-check-circle me-1"></i>Approve</button>
    <?= form_close() ?>
    <?php endif; ?>
    <?php if($est['status']==='approved' && in_array(session()->get('user_role'),['super_admin','facility_manager'])): ?>
    <?= form_open(base_url('estimations/convert/'.$est['id'])) ?>
    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Convert to Work Order?')"><i class="bi bi-arrow-right-circle me-1"></i>Convert to WO</button>
    <?= form_close() ?>
    <?php endif; ?>
    <a href="<?= base_url('estimations/print/'.$est['id']) ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <?php if(in_array($est['status'],['draft','pending_approval'])): ?>
    <a href="<?= base_url('estimations/edit/'.$est['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php endif; ?>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-info-circle"></i>Details</h6>
      <div class="small mb-2"><span class="text-muted">EST #:</span> <strong><?= esc($est['est_number']) ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Title:</span> <?= esc($est['title']) ?></div>
      <div class="small mb-2"><span class="text-muted">Facility:</span> <strong><?= esc($est['facility_name']??'—') ?></strong></div>
      <?php if($est['wo_number']): ?><div class="small mb-2"><span class="text-muted">Work Order:</span> <a href="<?= base_url('workorders/view/'.$est['wo_id']) ?>"><?= esc($est['wo_number']) ?></a></div><?php endif; ?>
      <div class="small mb-2"><span class="text-muted">Revision:</span> v<?= $est['revision'] ?></div>
      <div class="small mb-2"><span class="text-muted">Created by:</span> <?= esc($est['created_by_name']??'—') ?></div>
      <div class="small"><span class="text-muted">Created:</span> <?= date('d M Y',strtotime($est['created_at'])) ?></div>
      <?php if($est['approved_by_name']): ?><div class="small mt-1"><span class="text-muted">Approved by:</span> <?= esc($est['approved_by_name']) ?></div><?php endif; ?>
    </div>

    <div class="fm-form-section">
      <h6><i class="bi bi-receipt"></i>Customer Pricing</h6>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Selling Subtotal</span><strong><?= $currency ?> <?= number_format($est['selling_subtotal'] ?? $est['subtotal'],2) ?></strong></div>
      <?php if($est['vat_amount']>0): ?><div class="d-flex justify-content-between small mb-1"><span>VAT (<?= $est['vat_rate'] ?>%)</span><strong><?= $currency ?> <?= number_format($est['vat_amount'],2) ?></strong></div><?php endif; ?>
      <div class="d-flex justify-content-between fw-bold mt-2" style="font-size:1.05rem"><span>CUSTOMER TOTAL</span><span style="color:var(--fm-primary)"><?= $currency ?> <?= number_format($est['total'],2) ?></span></div>
    </div>

    <?php if($canViewInternal): ?>
    <div class="fm-form-section mt-3">
      <h6><i class="bi bi-lock"></i>Internal Financial Summary</h6>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Estimated Cost</span><strong><?= $currency ?> <?= number_format($est['estimated_subtotal'] ?? $est['subtotal'],2) ?></strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Actual Cost (items)</span><strong class="text-warning"><?= $currency ?> <?= number_format($est['actual_subtotal'] ?? 0,2) ?></strong></div>
      <?php if(($est['actual_total_cost'] ?? 0) > 0): ?>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Actual Breakdown Total</span><strong class="text-warning"><?= $currency ?> <?= number_format($est['actual_total_cost'],2) ?></strong></div>
      <?php endif; ?>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Profit</span><strong class="text-success"><?= $currency ?> <?= number_format($est['total_profit'] ?? 0,2) ?></strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Margin</span><span class="badge bg-secondary"><?= number_format($est['total_margin'] ?? 0,1) ?>%</span></div>
      <div class="d-flex justify-content-between small"><span class="text-muted">Cost Variance</span><strong class="<?= ($est['cost_variance']??0)<=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($est['cost_variance'] ?? 0,2) ?></strong></div>
    </div>

    <?php if(($est['actual_total_cost'] ?? $est['actual_total'] ?? 0) > 0): ?>
    <div class="fm-form-section mt-3">
      <h6><i class="bi bi-currency-exchange"></i>Actual Cost Breakdown</h6>
      <?php foreach([
        'actual_labor_cost'=>'Labor','actual_material_cost'=>'Materials','actual_transport_cost'=>'Transportation',
        'actual_equipment_cost'=>'Equipment','actual_misc_cost'=>'Miscellaneous','actual_other_cost'=>'Other',
      ] as $k=>$l): ?>
      <?php if(($est[$k]??0) > 0): ?>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted"><?= $l ?></span><strong><?= $currency ?> <?= number_format($est[$k],2) ?></strong></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    $woCompleted = isset($linkedWo) && $linkedWo && in_array($linkedWo['status'], ['completed','closed']);
    $invoiceReady = !empty($woInvoices);
    $showPnl = $canViewInternal && $woCompleted && $invoiceReady;
    ?>
    <?php if($showPnl && $linkedInvoice): ?>
    <?php
      $revenue    = (float)$linkedInvoice['total'];
      $actualCost = (float)($est['actual_total_cost'] ?? $est['actual_subtotal'] ?? 0);
      $estCost    = (float)($est['estimated_subtotal'] ?? $est['subtotal']);
      $profit     = $revenue - $actualCost;
      $margin     = $revenue > 0 ? round(($profit/$revenue)*100,1) : 0;
    ?>
    <div class="fm-form-section mt-3" style="border:2px solid <?= $profit>=0?'#28a745':'#dc3545' ?>">
      <h6><i class="bi bi-bar-chart me-1"></i>Work Order P&amp;L</h6>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Invoice Revenue</span><span class="text-success fw-bold"><?= $currency ?> <?= number_format($revenue,2) ?></span></div>
      <p class="small mb-0"><a href="<?= base_url('finance/invoices/view/'.$linkedInvoice['id']) ?>"><?= esc($linkedInvoice['invoice_number']) ?></a> — <?= ucfirst($linkedInvoice['status']) ?></p>
    </div>
    <?php elseif($est['status']==='converted'): ?>
    <div class="alert alert-info small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>P&amp;L updates when the work order is completed and invoiced.</div>
    <?php endif; ?>

    <?php if($est['status']==='draft'): ?>
    <div class="mt-3">
      <?= form_open(base_url('estimations/submit/'.$est['id'])) ?>
      <button type="submit" class="btn btn-fm-outline w-100" onclick="return confirm('Submit for approval?')">Submit for Approval</button>
      <?= form_close() ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-8">
    <?php if($est['description']): ?>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-card-text"></i>Scope of Work</h6>
      <div style="white-space:pre-wrap;font-size:.85rem;line-height:1.7"><?= esc($est['description']) ?></div>
    </div>
    <?php endif; ?>

    <?php if(!empty($items)): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-list-ol me-2"></i>Line Items</h5></div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead>
            <tr>
              <th>Item</th><th>Description</th><th>Qty</th><th>Unit</th><th>Sell Price</th><th>Line Total</th>
              <?php if($canViewInternal): ?><th>Est. Cost</th><th>Act. Cost</th><th>Profit</th><th>Margin</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach($items as $item): ?>
          <tr>
            <td class="small fw-semibold"><?= esc($item['item_name'] ?? $item['description']) ?></td>
            <td class="small"><?= esc($item['description'] ?? '') ?></td>
            <td class="small"><?= $item['quantity'] ?></td>
            <td class="small"><?= esc($item['unit'] ?? 'unit') ?></td>
            <td class="small"><?= $currency ?> <?= number_format($item['unit_price'] ?? 0,2) ?></td>
            <td class="small fw-bold"><?= $currency ?> <?= number_format($item['line_total'] ?? $item['total_cost'] ?? 0,2) ?></td>
            <?php if($canViewInternal): ?>
            <td class="small"><?= $currency ?> <?= number_format($item['estimated_total'] ?? 0,2) ?></td>
            <td class="small text-warning"><?= $currency ?> <?= number_format($item['actual_total'] ?? 0,2) ?></td>
            <td class="small"><?= $currency ?> <?= number_format($item['profit'] ?? 0,2) ?></td>
            <td class="small"><?= number_format($item['margin_percent'] ?? 0,1) ?>%</td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($woInvoices)): ?>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-receipt-cutoff"></i>Linked Invoices</h6>
      <?php foreach($woInvoices as $inv): ?>
      <div class="d-flex justify-content-between small mb-2">
        <a href="<?= base_url('finance/invoices/view/'.$inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a>
        <span><?= $currency ?> <?= number_format($inv['total'],2) ?> — <?= ucfirst($inv['status']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($est['notes'] && $canViewInternal): ?>
    <div class="fm-form-section"><h6><i class="bi bi-sticky"></i>Internal Notes</h6><p class="small mb-0"><?= nl2br(esc($est['notes'])) ?></p></div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
