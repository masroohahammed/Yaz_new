<?php
/** Non-facility customer picker — show only when location_mode = non_facility */
$customers = $customers ?? [];
$prefix = $prefix ?? 'hd';
?>
<div class="border rounded p-3 mb-3 bg-light d-none" id="<?= $prefix ?>nonFacilityBlock">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <label class="form-label fw-medium mb-0">Customer <span class="text-muted small">(non-facility)</span></label>
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#<?= $prefix ?>addCustomerModal">
      <i class="bi bi-plus-lg"></i> Add Customer
    </button>
  </div>
  <select name="service_customer_id" id="<?= $prefix ?>serviceCustomerId" class="form-select">
    <option value="">— Select existing customer —</option>
    <?php foreach ($customers as $c): ?>
    <option value="<?= (int)$c['id'] ?>" data-phone="<?= esc($c['phone'] ?? '') ?>" data-location="<?= esc($c['location'] ?? '') ?>">
      <?= esc($c['name']) ?><?= !empty($c['phone']) ? ' · '.esc($c['phone']) : '' ?>
    </option>
    <?php endforeach; ?>
  </select>
  <div class="mt-2">
    <input type="text" name="requester_location" id="<?= $prefix ?>requesterLocation" class="form-control form-control-sm" placeholder="Service location / address" value="<?= old('requester_location') ?>">
  </div>
</div>

<div class="modal fade" id="<?= $prefix ?>addCustomerModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Add Customer</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label small">Name *</label><input type="text" id="<?= $prefix ?>newCustName" class="form-control form-control-sm"></div>
        <div class="mb-2"><label class="form-label small">Phone</label><input type="text" id="<?= $prefix ?>newCustPhone" class="form-control form-control-sm"></div>
        <div class="mb-2"><label class="form-label small">Location</label><input type="text" id="<?= $prefix ?>newCustLocation" class="form-control form-control-sm"></div>
        <div id="<?= $prefix ?>newCustErr" class="text-danger small d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-fm-primary btn-sm" id="<?= $prefix ?>saveNewCustomer">Save &amp; Select</button>
      </div>
    </div>
  </div>
</div>
