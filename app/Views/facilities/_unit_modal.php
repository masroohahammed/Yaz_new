<!-- Add Unit Modal -->
<div class="modal fade" id="unitAddModal" tabindex="-1" aria-labelledby="unitAddModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <?= form_open(base_url('facilities/'.$facility['id'].'/units/store')) ?>
      <div class="modal-header">
        <h5 class="modal-title" id="unitAddModalLabel"><i class="bi bi-grid-plus me-2"></i>Add Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Unit Number <span class="text-danger">*</span></label>
            <input type="text" name="unit_number" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Floor</label>
            <input type="text" name="floor" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Type</label>
            <select name="unit_type" class="form-select" id="modalUnitTypeSelect" onchange="toggleModalPlateNumber()">
              <option value="">—</option>
              <option value="office">Office</option>
              <option value="retail">Retail</option>
              <option value="warehouse">Warehouse</option>
              <option value="apartment">Apartment</option>
              <option value="parking">Parking</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="col-md-4" id="modalPlateNumberWrap" style="display:none">
            <label class="form-label">Plate Number</label>
            <input type="text" name="plate_number" class="form-control" placeholder="e.g. ABC 1234">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
              <option value="vacant">Vacant</option>
              <option value="occupied">Occupied</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Area (sqft)</label>
            <input type="number" name="area_sqft" class="form-control" step="0.01">
          </div>
          <div class="col-md-4">
            <label class="form-label">Rent (<?= $currency ?>)</label>
            <input type="number" name="rent_amount" class="form-control" step="0.01">
          </div>
          <div class="col-md-6">
            <label class="form-label">Tenant Name</label>
            <input type="text" name="tenant_name" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Tenant Mobile</label>
            <input type="text" name="tenant_mobile" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Contract Start</label>
            <input type="date" name="contract_start" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Contract End</label>
            <input type="date" name="contract_end" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-1"></i>Save Unit</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<script>
function toggleModalPlateNumber() {
  const type = document.getElementById('modalUnitTypeSelect').value;
  const wrap = document.getElementById('modalPlateNumberWrap');
  if (wrap) wrap.style.display = type === 'parking' ? '' : 'none';
}
</script>
