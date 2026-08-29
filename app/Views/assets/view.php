<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$warrantyExpired = !empty($asset['warranty_expiry']) && strtotime($asset['warranty_expiry']) < time();
$criticality = $asset['criticality'] ?? 'medium';
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-cpu me-2 text-primary"></i><?= esc($asset['name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= base_url('asset-register') ?>">Assets</a></li><li class="breadcrumb-item active"><?= esc($asset['asset_code']) ?></li></ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <span class="fm-badge badge-status-<?= esc($asset['status']) ?>"><?= ucfirst(str_replace('_',' ',$asset['status'])) ?></span>
    <?php if ($criticality === 'critical' || $criticality === 'high'): ?>
    <span class="badge bg-danger-subtle text-danger border"><?= ucfirst($criticality) ?> criticality</span>
    <?php endif; ?>
    <a href="<?= base_url('workorders/create?asset_id='.$asset['id'].'&facility_id='.(int)($asset['facility_id']??0)) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-tools me-1"></i>Create WO</a>
    <a href="<?= base_url('helpdesk/create?asset_id='.$asset['id'].'&facility_id='.(int)($asset['facility_id']??0)) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-megaphone me-1"></i>Report Complaint</a>
    <a href="<?= base_url('asset-register/qrcode/'.$asset['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-qr-code me-1"></i>QR</a>
    <a href="<?= base_url('asset-register/print-label/'.$asset['id']) ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print Label</a>
    <a href="<?= base_url('asset-register/edit/'.$asset['id']) ?>" class="btn btn-fm-outline btn-sm">Edit</a>
  </div>
</div>

<ul class="nav nav-tabs fm-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">Information</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-maintenance" type="button">Maintenance</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-workorders" type="button">Work Orders (<?= count($workOrders) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs" type="button">Documents (<?= count($documents ?? []) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-scans" type="button">Scan Logs (<?= count($scanLogs ?? []) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-qr" type="button">QR / Barcode</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-info">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-form-section">
          <h6><i class="bi bi-info-circle"></i>Asset Master</h6>
          <div class="row g-3 small">
            <div class="col-md-4"><div class="text-muted">Asset ID</div><div class="fw-semibold"><?= esc($asset['asset_code']) ?></div></div>
            <div class="col-md-4"><div class="text-muted">Tag Number</div><div><?= esc($asset['tag_number'] ?? '—') ?></div></div>
            <div class="col-md-4"><div class="text-muted">Facility</div><div class="fw-semibold"><?= esc($asset['facility_name']) ?></div></div>
            <div class="col-md-4"><div class="text-muted">Category / Type</div><div><?= ucfirst(str_replace('_',' ',$asset['category'])) ?><?= !empty($asset['asset_type']) ? ' · '.esc($asset['asset_type']) : '' ?></div></div>
            <div class="col-md-4"><div class="text-muted">Manufacturer / Brand</div><div><?= esc($asset['manufacturer'] ?? $asset['brand'] ?? '—') ?></div></div>
            <div class="col-md-4"><div class="text-muted">Model / Serial</div><div><?= esc($asset['model'] ?? '—') ?> / <?= esc($asset['serial_number'] ?? '—') ?></div></div>
            <div class="col-md-4"><div class="text-muted">Location</div><div><?= esc($asset['location_in_facility'] ?: '—') ?><?= !empty($asset['floor_room']) ? '<br>'.esc($asset['floor_room']) : '' ?></div></div>
            <div class="col-md-4"><div class="text-muted">Department</div><div><?= esc($asset['department'] ?? '—') ?></div></div>
            <div class="col-md-4"><div class="text-muted">Assigned To</div><div><?= esc($asset['assigned_name'] ?? '—') ?></div></div>
            <div class="col-md-4"><div class="text-muted">Purchase</div><div><?= $asset['purchase_date'] ? date('d M Y', strtotime($asset['purchase_date'])) : '—' ?><?= $asset['purchase_cost'] ? ' · '.$currency.' '.number_format($asset['purchase_cost'],2) : '' ?></div></div>
            <div class="col-md-4"><div class="text-muted">Warranty</div><div class="<?= $warrantyExpired ? 'text-danger fw-bold' : '' ?>"><?= !empty($asset['warranty_start']) ? date('d M Y', strtotime($asset['warranty_start'])).' — ' : '' ?><?= $asset['warranty_expiry'] ? date('d M Y', strtotime($asset['warranty_expiry'])) : '—' ?></div></div>
            <div class="col-md-4"><div class="text-muted">AMC Expiry</div><div><?= $asset['amc_expiry'] ? date('d M Y', strtotime($asset['amc_expiry'])) : '—' ?></div></div>
            <div class="col-12"><div class="text-muted">Health Score</div>
              <?php $h = (int)$asset['health_score']; ?>
              <div class="d-flex align-items-center gap-3 mt-1"><div class="health-bar flex-grow-1"><div class="health-bar-fill <?= $h>=80?'good':($h>=50?'warn':'bad') ?>" style="width:<?= $h ?>%"></div></div><span class="fw-bold"><?= $h ?>%</span></div>
            </div>
            <?php if (!empty($asset['notes'])): ?><div class="col-12"><div class="text-muted">Notes</div><div><?= nl2br(esc($asset['notes'])) ?></div></div><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="fm-form-section">
          <h6><i class="bi bi-lightning"></i>Quick Actions</h6>
          <div class="d-grid gap-2">
            <a href="<?= esc($scanUrl) ?>" target="_blank" class="btn btn-sm btn-fm-outline"><i class="bi bi-phone me-1"></i>Preview Scan Page</a>
            <?php if (in_array(session()->get('user_role'), ['super_admin','facility_manager'])): ?>
            <?= form_open(base_url('asset-register/deactivate/'.$asset['id']), ['class' => 'd-grid']) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this asset?')">Deactivate Asset</button>
            <?= form_close() ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-maintenance">
    <div class="fm-form-section">
      <h6><i class="bi bi-calendar-check"></i>Maintenance Schedule</h6>
      <div class="row g-3 small">
        <div class="col-md-4">Last: <strong><?= $asset['last_maintenance'] ? date('d M Y', strtotime($asset['last_maintenance'])) : '—' ?></strong></div>
        <div class="col-md-4">Next: <strong class="<?= ($asset['next_maintenance'] && strtotime($asset['next_maintenance']) < time()) ? 'text-danger' : '' ?>"><?= $asset['next_maintenance'] ? date('d M Y', strtotime($asset['next_maintenance'])) : '—' ?></strong></div>
      </div>
    </div>
    <?php if (!empty($complaints)): ?>
    <div class="fm-card mt-3"><div class="card-header-fm"><h5 class="mb-0">Complaints</h5></div>
      <div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Ticket</th><th>Status</th><th>Priority</th><th>Date</th></tr></thead><tbody>
      <?php foreach ($complaints as $c): ?>
      <tr><td><a href="<?= base_url('helpdesk/'.$c['id']) ?>"><?= esc($c['ticket_number']) ?></a></td><td><?= ucfirst($c['status']) ?></td><td><?= ucfirst($c['priority']) ?></td><td class="small"><?= date('d M Y', strtotime($c['created_at'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div></div>
    <?php endif; ?>
  </div>

  <div class="tab-pane fade" id="tab-workorders">
    <div class="fm-card"><div class="card-header-fm d-flex justify-content-between"><h5 class="mb-0">Work Orders</h5>
      <a href="<?= base_url('workorders/create?asset_id='.$asset['id']) ?>" class="btn btn-fm-primary btn-sm">New WO</a></div>
      <div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>WO #</th><th>Title</th><th>Type</th><th>Status</th><th>Date</th></tr></thead><tbody>
      <?php foreach ($workOrders as $w): ?>
      <tr><td><a href="<?= base_url('workorders/view/'.$w['id']) ?>"><?= esc($w['wo_number']) ?></a></td><td class="small"><?= esc(substr($w['title'],0,50)) ?></td><td><?= ucfirst($w['type']) ?></td><td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td><td class="small"><?= date('d M Y', strtotime($w['created_at'])) ?></td></tr>
      <?php endforeach; ?>
      <?php if (empty($workOrders)): ?><tr><td colspan="5" class="text-center text-muted py-4">No work orders</td></tr><?php endif; ?>
      </tbody></table></div></div>
  </div>

  <div class="tab-pane fade" id="tab-docs">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-upload"></i>Upload Document</h6>
      <?= form_open_multipart(base_url('asset-register/upload-document/'.$asset['id'])) ?>
      <div class="row g-2 align-items-end">
        <div class="col-md-5"><input type="file" name="document" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
        <div class="col-md-3"><select name="doc_type" class="form-select form-select-sm"><option value="manual">Manual</option><option value="warranty">Warranty</option><option value="certificate">Certificate</option><option value="general">General</option></select></div>
        <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm">Upload</button></div>
      </div>
      <?= form_close() ?>
    </div>
    <?php if (!empty($documents)): ?>
    <table class="fm-table"><thead><tr><th>File</th><th>Type</th><th>By</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($documents as $d): ?>
    <tr><td><a href="<?= base_url($d['file_path']) ?>" target="_blank"><?= esc($d['file_name']) ?></a></td><td><?= ucfirst($d['doc_type']) ?></td><td class="small"><?= esc($d['uploaded_by_name'] ?? '—') ?></td><td class="small"><?= date('d M Y', strtotime($d['created_at'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="text-muted small">No documents uploaded.</p><?php endif; ?>
  </div>

  <div class="tab-pane fade" id="tab-scans">
    <table class="fm-table"><thead><tr><th>When</th><th>By</th><th>Source</th><th>Action</th><th>IP</th></tr></thead><tbody>
    <?php foreach ($scanLogs ?? [] as $s): ?>
    <tr><td class="small"><?= date('d M Y H:i', strtotime($s['created_at'])) ?></td><td class="small"><?= esc($s['scanned_by_name'] ?? 'Public') ?></td><td><?= esc($s['scan_source']) ?></td><td><?= esc($s['action_taken'] ?? '—') ?></td><td class="small text-muted"><?= esc($s['ip_address'] ?? '') ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($scanLogs)): ?><tr><td colspan="5" class="text-center text-muted py-4">No scans recorded yet</td></tr><?php endif; ?>
    </tbody></table>
  </div>

  <div class="tab-pane fade" id="tab-qr">
    <div class="row g-3">
      <div class="col-md-4 text-center">
        <div class="fm-form-section">
          <h6>QR Code</h6>
          <img src="<?= esc($qrImageUrl) ?>" alt="QR" class="img-fluid mb-2" style="max-width:200px">
          <div class="small text-muted text-break"><?= esc($scanUrl) ?></div>
          <a href="<?= base_url('asset-register/qrcode/'.$asset['id']) ?>" class="btn btn-fm-outline btn-sm mt-2">Full QR Page</a>
        </div>
      </div>
      <div class="col-md-8">
        <div class="fm-form-section">
          <h6>Barcode</h6>
          <p class="small">Value: <code><?= esc($asset['barcode_value'] ?? $asset['asset_code']) ?></code></p>
          <svg id="assetBarcode"></svg>
          <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="<?= base_url('asset-register/print-label/'.$asset['id'].'?size=small') ?>" class="btn btn-sm btn-fm-outline" target="_blank">Print Small (50×25)</a>
            <a href="<?= base_url('asset-register/print-label/'.$asset['id'].'?size=standard') ?>" class="btn btn-sm btn-fm-primary" target="_blank">Print Standard (75×50)</a>
            <a href="<?= base_url('asset-register/print-label/'.$asset['id'].'?size=large') ?>" class="btn btn-sm btn-fm-outline" target="_blank">Print Large (100×75)</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>JsBarcode("#assetBarcode", <?= json_encode($asset['barcode_value'] ?? $asset['asset_code']) ?>, {format:"CODE128", width:2, height:50, displayValue:true});</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
