<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP';
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-receipt me-2"></i><?= esc($inv['invoice_number']) ?></h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('finance/invoices') ?>">Invoices</a></li>
        <li class="breadcrumb-item active"><?= esc($inv['invoice_number']) ?></li>
      </ol>
    </nav>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="fm-badge badge-status-<?= esc($inv['status']) ?> fs-6"><?= ucfirst($inv['status']) ?></span>
    <a href="<?= base_url('finance/invoices/print/'.$inv['id']) ?>" class="btn btn-fm-outline btn-sm no-print" target="_blank">
      <i class="bi bi-printer me-1"></i>Print
    </a>
    <a href="<?= base_url('finance/invoices/print/'.$inv['id'].'?pdf=1') ?>" class="btn btn-fm-outline btn-sm no-print">
      <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
    </a>
  </div>
</div>

<div class="row g-3">
  <!-- INVOICE DOCUMENT -->
  <div class="col-lg-8">
    <div class="fm-card" id="invoiceDoc">

      <!-- Document header with dynamic logo + company -->
      <div style="padding:28px 28px 16px;border-bottom:3px solid <?= esc($primaryColor) ?>">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <?php if (!empty($companyLogoUrl)): ?>
            <img src="<?= esc($companyLogoUrl) ?>" alt="<?= esc($companyName) ?>"
                 style="max-height:60px;max-width:180px;object-fit:contain;display:block;margin-bottom:6px">
            <?php else: ?>
            <div style="font-size:1.4rem;font-weight:700;color:<?= esc($primaryColor) ?>;line-height:1.1">
              <?= esc($companyName) ?>
            </div>
            <?php endif; ?>
            <div style="font-size:.68rem;color:#6b7a8d;text-transform:uppercase;letter-spacing:.8px;margin-top:3px">
              <?= esc($companyTagline) ?>
            </div>
            <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'class' => 'mt-2', 'style' => 'font-size:.78rem;color:#6b7a8d;line-height:1.45']) ?>
          </div>
          <div class="text-end">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= esc($secondaryColor) ?>;margin-bottom:4px">INVOICE</div>
            <div style="font-size:1.25rem;font-weight:700;color:<?= esc($primaryColor) ?>"><?= esc($inv['invoice_number']) ?></div>
            <span class="fm-badge badge-status-<?= esc($inv['status']) ?> mt-1"><?= ucfirst($inv['status']) ?></span>
          </div>
        </div>
      </div>

      <!-- Bill to / Dates -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:20px 28px;background:#fafafa;border-bottom:1px solid #e8edf3">
        <div>
          <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:6px">Bill To</div>
          <div class="fw-semibold"><?= esc($inv['facility_name']) ?></div>
          <?php if(!empty($inv['contract_number'])): ?>
          <div class="small text-muted">Contract: <?= esc($inv['contract_number']) ?></div>
          <?php endif; ?>
        </div>
        <div class="text-end">
          <div class="small mb-2">
            <div style="font-size:.65rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px">Issue Date</div>
            <div class="fw-semibold"><?= date('d F Y', strtotime($inv['issue_date'])) ?></div>
          </div>
          <div class="small">
            <div style="font-size:.65rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px">Due Date</div>
            <div class="fw-semibold <?= $inv['status']==='overdue'?'text-danger':'' ?>">
              <?= date('d F Y', strtotime($inv['due_date'])) ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Line items -->
      <div class="p-0">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:<?= esc($primaryColor) ?>">
              <th style="color:#fff;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:10px 28px;text-align:left">Description</th>
              <th style="color:#fff;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:10px 28px;text-align:right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($invoiceItems)): ?>
            <?php foreach ($invoiceItems as $line): ?>
            <tr>
              <td style="padding:14px 28px;border-bottom:1px solid #f0f4f8;font-size:.85rem">
                <?= esc($line['description']) ?>
                <div class="small text-muted"><?= esc(ucfirst($line['line_type'] ?? 'service')) ?><?php if(!empty($line['work_order_id'])): ?> · WO #<?= (int)$line['work_order_id'] ?><?php endif; ?></div>
              </td>
              <td style="padding:14px 28px;border-bottom:1px solid #f0f4f8;font-size:.85rem;text-align:right;font-weight:600">
                <?= $inv['currency'] ?? $currency ?> <?= number_format((float)$line['amount'], 2) ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td style="padding:14px 28px;border-bottom:1px solid #f0f4f8;font-size:.85rem">
                Facility Management Services — <?= esc($inv['facility_name']) ?>
                <?php if(!empty($inv['work_order_id'])): ?>
                <div class="small text-muted">Work Order #<?= (int)$inv['work_order_id'] ?></div>
                <?php endif; ?>
              </td>
              <td style="padding:14px 28px;border-bottom:1px solid #f0f4f8;font-size:.85rem;text-align:right;font-weight:600">
                <?= $inv['currency'] ?? $currency ?> <?= number_format($inv['subtotal'], 2) ?>
              </td>
            </tr>
            <?php endif; ?>
            <?php if($vatEnabled && $inv['vat_amount'] > 0): ?>
            <tr>
              <td style="padding:10px 28px;font-size:.8rem;color:#6b7a8d">VAT (<?= $inv['vat_rate'] ?>%)</td>
              <td style="padding:10px 28px;font-size:.8rem;text-align:right;color:#6b7a8d">
                <?= $inv['currency'] ?? $currency ?> <?= number_format($inv['vat_amount'], 2) ?>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr style="background:rgba(<?php
                $hex=ltrim($primaryColor,'#');
                if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                echo hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
            ?>,.07)">
              <td style="padding:16px 28px;font-weight:700;font-size:.9rem;text-align:right;border-top:2px solid <?= esc($primaryColor) ?>">TOTAL</td>
              <td style="padding:16px 28px;font-weight:800;font-size:1.15rem;text-align:right;color:<?= esc($primaryColor) ?>;border-top:2px solid <?= esc($primaryColor) ?>">
                <?= $inv['currency'] ?? $currency ?> <?= number_format($inv['total'], 2) ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <?php if(!empty($inv['notes'])): ?>
      <div style="padding:16px 28px;border-top:1px solid #f0f4f8">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:4px">Notes</div>
        <div class="small"><?= esc($inv['notes']) ?></div>
      </div>
      <?php endif; ?>

      <!-- Document footer -->
      <div style="padding:12px 28px;border-top:2px solid <?= esc($primaryColor) ?>;background:rgba(<?php
          $hex=ltrim($primaryColor,'#');
          if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
          echo hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
      ?>,.06);display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-weight:700;color:<?= esc($primaryColor) ?>;font-size:.8rem"><?= esc($companyName) ?></div>
          <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'style' => 'font-size:.68rem;color:#9ca3af;margin-top:4px;line-height:1.4', 'plain' => true]) ?>
        </div>
        <div style="font-size:.7rem;color:#9ca3af">Generated: <?= date('d M Y, H:i') ?></div>
      </div>

    </div><!-- /#invoiceDoc -->
  </div>

  <!-- SIDEBAR PANEL -->
  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6>Invoice Summary</h6>
      <div class="d-flex justify-content-between mb-2 small">
        <span>Subtotal</span>
        <strong><?= $currency ?> <?= number_format($inv['subtotal'], 2) ?></strong>
      </div>
      <?php if($vatEnabled): ?>
      <div class="d-flex justify-content-between mb-2 small">
        <span>VAT (<?= $inv['vat_rate'] ?>%)</span>
        <strong><?= $currency ?> <?= number_format($inv['vat_amount'], 2) ?></strong>
      </div>
      <?php endif; ?>
      <hr>
      <div class="d-flex justify-content-between fw-bold">
        <span>TOTAL</span>
        <span style="color:<?= esc($primaryColor) ?>;font-size:1.1rem"><?= $currency ?> <?= number_format($inv['total'], 2) ?></span>
      </div>
      <?php if (($paidTotal ?? 0) > 0): ?>
      <div class="d-flex justify-content-between small mt-2 text-success">
        <span>Paid</span><strong><?= $currency ?> <?= number_format((float)$paidTotal, 2) ?></strong>
      </div>
      <?php endif; ?>
      <?php if (($balanceDue ?? 0) > 0.009): ?>
      <div class="d-flex justify-content-between small fw-bold mt-2">
        <span>Balance due</span><span class="text-danger"><?= $currency ?> <?= number_format((float)($balanceDue ?? 0), 2) ?></span>
      </div>
      <?php endif; ?>
      <div class="mt-3 small text-muted">Created by: <?= esc($inv['created_by_name'] ?? '—') ?></div>
      <?php if (!empty($payments)): ?>
      <hr>
      <h6 class="small text-muted text-uppercase">Payments</h6>
      <?php foreach ($payments as $p): ?>
      <div class="d-flex justify-content-between small mb-1">
        <span><?= esc($p['paid_at'] ?? '') ?> · <?= esc($p['payment_method'] ?? '') ?></span>
        <strong><?= $currency ?> <?= number_format((float)($p['amount']??0), 2) ?></strong>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="fm-form-section no-print">
      <h6>Actions</h6>
      <?php if (!empty($canMarkAsSent)): ?>
      <?= form_open(base_url('finance/invoices/status/' . (int) $inv['id']), [
        'class' => 'mb-3 fm-submit-form',
        'data-no-loader' => '',
        'onsubmit' => "return confirm('Mark this invoice as sent? It will appear on the Payments screen for collection.');",
      ]) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="status" value="sent">
      <button type="submit" class="btn btn-warning w-100 mb-2 fm-submit-btn">
        <i class="bi bi-send me-2"></i>Mark as Sent
      </button>
      <?= form_close() ?>
      <p class="small text-muted mb-3">Send the invoice to the client, then collect payment on Payments or below.</p>
      <?php endif; ?>
      <?php if (!empty($canRecordPayment)): ?>
      <div class="mb-3 p-3 rounded border border-success border-opacity-25 bg-success bg-opacity-10">
        <h6 class="small text-success text-uppercase mb-2"><i class="bi bi-cash-coin me-1"></i>Record Payment</h6>
        <?php if ($inv['status'] === 'draft'): ?>
        <p class="small text-muted mb-2">You can record payment on draft, or use <strong>Mark as Sent</strong> first.</p>
        <?php endif; ?>
        <?= view('finance/_record_payment_form', [
          'invoiceId'   => (int) $inv['id'],
          'balanceDue'  => (float) ($balanceDue ?? 0),
          'currency'    => $currency ?? 'QAR',
          'redirectTo'  => current_url(),
        ]) ?>
      </div>
      <?php elseif (\App\Services\PaymentService::canAcceptPayment((string)$inv['status']) && ($balanceDue ?? 0) > 0.009): ?>
      <p class="small text-muted mb-2">You do not have permission to record payments. Ask Finance or Facility Manager.</p>
      <?php endif; ?>
      <a href="<?= base_url('finance/payments') ?>" class="btn btn-fm-outline w-100 mb-2">
        <i class="bi bi-cash-stack me-2"></i>Payments screen
      </a>
      <button class="btn btn-fm-primary w-100 mb-2" onclick="window.print()">
        <i class="bi bi-printer me-2"></i>Print / Save PDF
      </button>
      <a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline w-100">
        <i class="bi bi-arrow-left me-2"></i>Back to Invoices
      </a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<style>
@media print {
  .no-print, .topbar, .sidebar, .page-header nav, .breadcrumb { display:none!important }
  .main-wrapper { margin-left:0!important }
  #invoiceDoc { box-shadow:none!important; border:none!important }
  .col-lg-4 { display:none }
  .col-lg-8 { width:100%!important; max-width:100%!important; flex:0 0 100%!important }
  body { background:#fff!important }
  * { -webkit-print-color-adjust:exact; print-color-adjust:exact }
}
</style>
<?= $this->endSection() ?>
