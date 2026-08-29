<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-pdf me-2"></i>Parking Rental Agreement</h1>
    <div class="small text-muted">
      Unit <?= esc($unit['unit_number'] ?? '') ?> · <?= esc($unit['facility_name'] ?? '') ?>
      <?php if (!empty($d['plate_number'])): ?> · Plate <?= esc($d['plate_number']) ?><?php endif; ?>
    </div>
  </div>
  <a href="<?= esc($backUrl) ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>

<div class="form-card mb-3">
  <p class="small text-muted mb-3">
    Review and edit fields below. English appears on the left and Arabic on the right in the printed contract.
    Plate number and unit details are pre-filled from this parking unit.
  </p>
  <form method="post" action="<?= esc($printUrl) ?>" target="_blank" id="parkingContractForm">
    <?= csrf_field() ?>
    <?php if (!empty($d['lease_contract_id'])): ?>
    <input type="hidden" name="lease_contract_id" value="<?= (int) $d['lease_contract_id'] ?>">
    <?php endif; ?>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Contract number</label>
        <input type="text" name="contract_number" class="form-control form-control-sm" value="<?= esc($d['contract_number'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Contract date</label>
        <input type="date" name="contract_date" class="form-control form-control-sm" value="<?= esc($d['contract_date'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Parking unit no.</label>
        <input type="text" name="parking_unit_no" class="form-control form-control-sm" value="<?= esc($d['parking_unit_no'] ?? '') ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Plate number</label>
        <input type="text" name="plate_number" class="form-control form-control-sm" value="<?= esc($d['plate_number'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Vehicle type</label>
        <input type="text" name="vehicle_type" class="form-control form-control-sm" value="<?= esc($d['vehicle_type'] ?? '') ?>" placeholder="e.g. Motorcycle, Car">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Vehicle description</label>
        <input type="text" name="vehicle_description" class="form-control form-control-sm" value="<?= esc($d['vehicle_description'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tenant name</label>
        <input type="text" name="tenant_name" class="form-control form-control-sm" value="<?= esc($d['tenant_name'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">QID / Passport</label>
        <input type="text" name="tenant_qid" class="form-control form-control-sm" value="<?= esc($d['tenant_qid'] ?? '') ?>" placeholder="Qatar ID">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Mobile</label>
        <input type="text" name="tenant_phone" class="form-control form-control-sm" value="<?= esc($d['tenant_phone'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Nationality</label>
        <input type="text" name="tenant_nationality" class="form-control form-control-sm" value="<?= esc($d['tenant_nationality'] ?? '') ?>">
      </div>
      <div class="col-md-8">
        <label class="form-label small fw-semibold">Tenant address</label>
        <input type="text" name="tenant_address" class="form-control form-control-sm" value="<?= esc($d['tenant_address'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Title deed no.</label>
        <input type="text" name="title_deed_no" class="form-control form-control-sm" value="<?= esc($d['title_deed_no'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">City</label>
        <input type="text" name="property_city" class="form-control form-control-sm" value="<?= esc($d['property_city'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Property / building name</label>
        <input type="text" name="property_name" class="form-control form-control-sm" value="<?= esc($d['property_name'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Building no.</label>
        <input type="text" name="building_no" class="form-control form-control-sm" value="<?= esc($d['building_no'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Zone no.</label>
        <input type="text" name="zone_no" class="form-control form-control-sm" value="<?= esc($d['zone_no'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Street no.</label>
        <input type="text" name="street_no" class="form-control form-control-sm" value="<?= esc($d['street_no'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Property address</label>
        <input type="text" name="property_address" class="form-control form-control-sm" value="<?= esc($d['property_address'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Start date</label>
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($d['start_date'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">End date</label>
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($d['end_date'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Monthly rent</label>
        <input type="number" step="0.01" name="rent_amount" class="form-control form-control-sm" value="<?= esc($d['rent_amount'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Payment method</label>
        <select name="payment_terms" class="form-select form-select-sm">
          <?php foreach (['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($d['payment_terms'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Landlord / company name</label>
        <input type="text" name="landlord_name" class="form-control form-control-sm" value="<?= esc($d['landlord_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Company CR</label>
        <input type="text" name="landlord_cr" class="form-control form-control-sm" value="<?= esc($d['landlord_cr'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Company email</label>
        <input type="email" name="landlord_email" class="form-control form-control-sm" value="<?= esc($d['landlord_email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Payment collector</label>
        <input type="text" name="collector_company" class="form-control form-control-sm" value="<?= esc($d['collector_company'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Collector CR</label>
        <input type="text" name="collector_cr" class="form-control form-control-sm" value="<?= esc($d['collector_cr'] ?? '') ?>">
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap mt-4">
      <button type="submit" class="btn btn-fm-primary btn-sm"><i class="bi bi-printer me-1"></i>Preview &amp; Print</button>
      <button type="submit" class="btn btn-fm-outline btn-sm" name="pdf" value="1"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</button>
    </div>
  </form>
</div>
<?= $this->endSection() ?>
