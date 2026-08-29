<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-pencil me-2 text-primary"></i>Edit Item: <?= esc($item['name']) ?></h1></div></div>
<?= form_open(base_url('inventory/update/'.$item['id'])) ?>
<div class="row g-3"><div class="col-lg-8"><div class="fm-form-section"><h6>Item Details</h6><div class="row g-3">
<div class="col-md-8"><label class="form-label">Item Name</label><input type="text" name="name" class="form-control" value="<?= esc($item['name']) ?>"></div>
<div class="col-md-4"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?= esc($item['category']) ?>"></div>
<div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" value="<?= esc($item['unit']) ?>"></div>
<div class="col-md-4"><label class="form-label">Min Quantity</label><input type="number" name="min_quantity" class="form-control" value="<?= $item['min_quantity'] ?>"></div>
<div class="col-md-4"><label class="form-label">Unit Cost (<?= $currency ?>)</label><input type="number" name="unit_cost" step="0.01" class="form-control" value="<?= $item['unit_cost'] ?>"></div>
<div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= esc($item['location']) ?>"></div>
<div class="col-md-6"><label class="form-label">Supplier</label><input type="text" name="supplier" class="form-control" value="<?= esc($item['supplier']) ?>"></div>
</div></div></div>
<div class="col-lg-4"><div class="fm-form-section"><h6>Current Stock: <?= $item['quantity'] ?> <?= esc($item['unit']) ?></h6><div class="small text-muted mb-3">Use Stock Movements to change quantity</div><button type="submit" class="btn btn-fm-primary w-100 mb-2">Save</button><a href="<?= base_url('inventory') ?>" class="btn btn-fm-outline w-100">Cancel</a><a href="<?= base_url('inventory/movement') ?>" class="btn btn-sm w-100 mt-2" style="border:1.5px solid #e2e8f0;border-radius:8px;color:#6b7c93">Stock Movement</a></div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
