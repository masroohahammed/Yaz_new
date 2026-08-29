<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-clipboard2-plus me-2 text-primary"></i>Create Work Order</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('work-orders') ?>">Work Orders</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
  </div>
  <a href="<?= base_url('work-orders') ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>

<form action="/work-orders" method="post" class="row g-4">
    <?= csrf_field() ?>

    <!-- Left column -->
    <div class="col-lg-8">
        <div class="fm-card p-4">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= old('title') ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Facility <span class="text-danger">*</span></label>
                    <select name="facility_id" class="form-select" required id="facilitySelect">
                        <option value="">Select facility…</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= old('facility_id') == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Asset</label>
                    <select name="asset_id" class="form-select" id="assetSelect">
                        <option value="">No specific asset</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['id'] ?>" data-facility="<?= $a['facility_id'] ?>"><?= esc($a['name']) ?> — <?= esc($a['asset_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <?php foreach (['corrective'=>'Corrective','preventive'=>'Preventive','predictive'=>'Predictive','breakdown'=>'Breakdown','inspection'=>'Inspection','emergency'=>'Emergency','project'=>'Project'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= old('type') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Category</label>
                    <select name="category" class="form-select">
                        <option value="">Select…</option>
                        <?php foreach (['electrical','hvac','plumbing','cleaning','civil','it','fire_safety','security','other'] as $c): ?>
                            <option value="<?= $c ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select" required>
                        <?php foreach (['critical','high','medium','low'] as $p): ?>
                            <option value="<?= $p ?>" <?= old('priority','medium') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Estimated Cost (QAR)</label>
                    <input type="number" name="estimated_cost" class="form-control" step="0.01" min="0" value="<?= old('estimated_cost') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= old('description') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="col-lg-4">
        <!-- Assignment -->
        <div class="fm-card p-3 mb-4">
            <div class="fw-semibold mb-3">Assignment</div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Assign Supervisor</label>
                <select name="supervisor_id" class="form-select form-select-sm">
                    <option value="">Assign later…</option>
                    <?php foreach ($supervisors as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= old('supervisor_id') == $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label small fw-medium">Assign Technician</label>
                <select name="assigned_to" class="form-select form-select-sm">
                    <option value="">Assign later…</option>
                    <?php foreach ($technicians as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= old('assigned_to') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Schedule -->
        <div class="fm-card p-3 mb-4">
            <div class="fw-semibold mb-3">Schedule</div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Planned Start</label>
                <input type="datetime-local" name="planned_start" class="form-control form-control-sm" value="<?= old('planned_start') ?>">
            </div>
            <div>
                <label class="form-label small fw-medium">Planned End</label>
                <input type="datetime-local" name="planned_end" class="form-control form-control-sm" value="<?= old('planned_end') ?>">
            </div>
        </div>

        <!-- Requester -->
        <div class="fm-card p-3 mb-4">
            <div class="fw-semibold mb-3">Requester Info</div>
            <input type="text" name="requester_name"  class="form-control form-control-sm mb-2" placeholder="Requester Name" value="<?= old('requester_name') ?>">
            <input type="text" name="requester_phone" class="form-control form-control-sm mb-2" placeholder="Phone" value="<?= old('requester_phone') ?>">
            <input type="email" name="requester_email" class="form-control form-control-sm" placeholder="Email" value="<?= old('requester_email') ?>">
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary-brand">Create Work Order</button>
            <a href="/work-orders" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>

</form>

<?= $this->section('scripts') ?>
<script>
// Filter assets by facility
const facilitySelect = document.getElementById('facilitySelect');
const assetSelect    = document.getElementById('assetSelect');
const allAssets      = Array.from(assetSelect.options);
facilitySelect.addEventListener('change', () => {
    const fId = facilitySelect.value;
    Array.from(assetSelect.options).forEach(o => {
        if (o.value === '') return;
        o.hidden = fId && o.dataset.facility !== fId;
    });
    assetSelect.value = '';
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
