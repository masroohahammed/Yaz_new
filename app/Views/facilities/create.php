<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ! empty($facility['id']); ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-building me-2 text-primary"></i><?= $isEdit ? 'Edit Facility' : 'Add Facility' ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('facilities') ?>">Facilities</a></li><li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?></li></ol></nav>
  </div>
  <a href="<?= base_url('facilities') ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="fm-card p-4">
            <form action="<?= $isEdit ? '/facilities/'.$facility['id'].'/update' : '/facilities' ?>" method="post">
                <?= csrf_field() ?>

                <!-- Company (required) -->
                <div class="mb-3">
                    <label class="form-label fw-medium">Company <span class="text-danger">*</span></label>
                    <select name="company_id" class="form-select" required>
                        <option value="">Select company…</option>
                        <?php foreach ($companies as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= (old('company_id', $facility['company_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                <?= esc($c['name']) ?> (<?= esc($c['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Every facility must belong to a company.</div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-8">
                        <label class="form-label fw-medium">Facility Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= old('name', $facility['name'] ?? '') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" required maxlength="20"
                               value="<?= old('code', $facility['code'] ?? '') ?>">
                        <div class="form-text">e.g. ART-001</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= old('address', $facility['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control" required
                               value="<?= old('city', $facility['city'] ?? '') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Country <span class="text-danger">*</span></label>
                        <input type="text" name="country" class="form-control" required
                               value="<?= old('country', $facility['country'] ?? 'Qatar') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Area (sqm)</label>
                        <input type="number" name="area_sqm" class="form-control" step="0.01" min="0"
                               value="<?= old('area_sqm', $facility['area_sqm'] ?? '') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Floors</label>
                        <input type="number" name="floors" class="form-control" min="1"
                               value="<?= old('floors', $facility['floors'] ?? 1) ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['active'=>'Active','inactive'=>'Inactive','under_maintenance'=>'Under Maintenance'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= old('status', $facility['status'] ?? 'active') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Facility Manager</label>
                        <select name="manager_id" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($managers as $m): ?>
                                <option value="<?= $m['id'] ?>"
                                    <?= old('manager_id', $facility['manager_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                    <?= esc($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (! empty($propertyManagers)): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Property Managers <span class="text-muted small">(multiple allowed)</span></label>
                        <select name="property_manager_ids[]" class="form-select" multiple size="<?= min(6, max(3, count($propertyManagers))) ?>">
                            <?php foreach ($propertyManagers as $pm): ?>
                                <?php
                                $selected = in_array((int) $pm['id'], array_map('intval', $assignedStaff['property_manager'] ?? $assignedManagerIds ?? []), true);
                                ?>
                                <option value="<?= (int) $pm['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= esc($pm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Property Managers have company-wide access; assignments are for reference and reporting.</div>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($realEstateManagers)): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Real Estate Managers <span class="text-muted small">(scoped to selected properties)</span></label>
                        <select name="real_estate_manager_ids[]" class="form-select" multiple size="<?= min(6, max(3, count($realEstateManagers))) ?>">
                            <?php foreach ($realEstateManagers as $rem): ?>
                                <?php $selected = in_array((int) $rem['id'], array_map('intval', $assignedStaff['real_estate_manager'] ?? []), true); ?>
                                <option value="<?= (int) $rem['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= esc($rem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($landlordUsers)): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Landlord Users <span class="text-muted small">(portal access for this property)</span></label>
                        <select name="landlord_user_ids[]" class="form-select" multiple size="<?= min(6, max(3, count($landlordUsers))) ?>">
                            <?php foreach ($landlordUsers as $lu): ?>
                                <?php $selected = in_array((int) $lu['id'], array_map('intval', $assignedStaff['landlord'] ?? []), true); ?>
                                <option value="<?= (int) $lu['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= esc($lu['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Finance & Property Classification -->
                <hr class="my-4">
                <h6 class="fw-semibold mb-3 text-primary"><i class="bi bi-currency-dollar me-2"></i>Finance & Property Details</h6>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Category</label>
                        <select name="category" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach (['Residential'=>'Residential','Commercial'=>'Commercial'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= old('category', $facility['category'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Property Type</label>
                        <input type="text" name="property_type" class="form-control" placeholder="e.g. Apartment, Villa"
                               value="<?= old('property_type', $facility['property_type'] ?? '') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Listing Status</label>
                        <select name="listing_status" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach (['available'=>'Available','leased'=>'Leased','off_market'=>'Off Market','sold'=>'Sold'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= old('listing_status', $facility['listing_status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label fw-medium">For Sale?</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="for_sale" id="for_sale" value="1"
                                   <?= old('for_sale', $facility['for_sale'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="for_sale">Listed for sale</label>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label fw-medium">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                               value="<?= old('sale_price', $facility['sale_price'] ?? '') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Landlord</label>
                        <select name="landlord_id" class="form-select">
                            <option value="">— None —</option>
                            <?php foreach ($landlords ?? [] as $ll): ?>
                                <option value="<?= $ll['id'] ?>" <?= old('landlord_id', $facility['landlord_id'] ?? '') == $ll['id'] ? 'selected' : '' ?>>
                                    <?= esc($ll['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Expected Monthly Income</label>
                        <input type="number" name="expected_monthly_income" class="form-control" step="0.01" min="0"
                               value="<?= old('expected_monthly_income', $facility['expected_monthly_income'] ?? '') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Landlord Share %</label>
                        <input type="number" name="landlord_share_pct" class="form-control" step="0.01" min="0" max="100"
                               value="<?= old('landlord_share_pct', $facility['landlord_share_pct'] ?? '') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-medium">Management Fee %</label>
                        <input type="number" name="management_fee_pct" class="form-control" step="0.01" min="0" max="100"
                               value="<?= old('management_fee_pct', $facility['management_fee_pct'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Finance Notes</label>
                        <textarea name="finance_notes" class="form-control" rows="2"><?= old('finance_notes', $facility['finance_notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary-brand">
                        <?= $isEdit ? 'Save Changes' : 'Create Facility' ?>
                    </button>
                    <a href="/facilities" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
