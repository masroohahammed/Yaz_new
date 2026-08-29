<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-plus-circle me-2 text-primary"></i>Add Inventory Item</h1></div></div>
<?= form_open(base_url('inventory/store')) ?>
<div class="row g-3"><div class="col-lg-8"><div class="fm-form-section"><h6>Item Details</h6><div class="row g-3">
<div class="col-md-8"><label class="form-label">Item Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Item Code *</label><input type="text" name="item_code" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="HVAC Parts, Electrical..."></div>
<div class="col-md-3"><label class="form-label">Unit</label><select name="unit" class="form-select"><option>pcs</option><option>m</option><option>kg</option><option>L</option><option>can</option><option>box</option><option>bucket</option></select></div>
<div class="col-md-3"><label class="form-label">Initial Qty</label><input type="number" name="quantity" class="form-control" value="0"></div>
<div class="col-md-4"><label class="form-label">Min Quantity</label><input type="number" name="min_quantity" class="form-control" value="5"></div>
<div class="col-md-4"><label class="form-label">Unit Cost (<?= $currency ?>)</label><input type="number" name="unit_cost" step="0.01" class="form-control" value="0"></div>
<div class="col-md-4"><label class="form-label">Location</label><input type="text" name="location" class="form-control" placeholder="Warehouse A..."></div>
<div class="col-12"><label class="form-label">Supplier</label><input type="text" name="supplier" class="form-control"></div>
</div></div></div>
<div class="col-lg-4"><div class="fm-form-section"><h6>Actions</h6><button type="submit" class="btn btn-fm-primary w-100 mb-2">Add Item</button><a href="<?= base_url('inventory') ?>" class="btn btn-fm-outline w-100">Cancel</a></div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
