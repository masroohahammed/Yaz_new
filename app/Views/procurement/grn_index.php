<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-truck me-2"></i>Goods Received Notes (GRN)</h1>
    <p class="text-muted small mb-0">Delivery records and stock update confirmations</p>
  </div>
  <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Procurement</a>
</div>

<!-- Date filter -->
<form class="fm-card fm-card-body mb-3 d-flex flex-wrap gap-2 align-items-end" method="get">
  <div>
    <label class="form-label small fw-semibold">From</label>
    <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>">
  </div>
  <div>
    <label class="form-label small fw-semibold">To</label>
    <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>">
  </div>
  <button type="submit" class="btn btn-fm-primary btn-sm">Filter</button>
</form>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr>
            <th>GRN #</th><th>PO #</th><th>Vendor</th><th>Received Date</th><th>Received By</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($grns)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No GRNs found for the selected period.</td></tr>
          <?php else: ?>
          <?php foreach ($grns as $g): ?>
          <tr>
            <td class="fw-semibold"><?= esc($g['grn_number'] ?? $g['id']) ?></td>
            <td><?= esc($g['po_number'] ?? '—') ?></td>
            <td><?= esc($g['vendor_name'] ?? '—') ?></td>
            <td class="small"><?= !empty($g['received_date']) ? date('d M Y', strtotime($g['received_date'])) : '—' ?></td>
            <td><?= esc($g['received_by_name'] ?? '—') ?></td>
            <td><span class="fm-badge fm-badge-<?= esc($g['status'] ?? 'received') ?>"><?= ucfirst($g['status'] ?? 'received') ?></span></td>
            <td class="text-end">
              <a href="<?= base_url('procurement/grn/view/' . $g['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
