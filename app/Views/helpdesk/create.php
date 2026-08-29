<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-headset me-2 text-primary"></i>Submit Complaint</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('helpdesk') ?>">Helpdesk</a></li><li class="breadcrumb-item active">Submit Complaint</li></ol></nav>
  </div>
  <a href="<?= base_url('helpdesk') ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="fm-card p-4">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($linkedAsset)): ?>
            <div class="alert alert-info small mb-3">
              <i class="bi bi-cpu me-1"></i>Linked asset: <strong><?= esc($linkedAsset['name']) ?></strong> (<?= esc($linkedAsset['asset_code']) ?>)
            </div>
            <input type="hidden" name="asset_id" value="<?= (int)$linkedAsset['id'] ?>">
            <?php endif; ?>

            <form action="/helpdesk" method="post" enctype="multipart/form-data" class="fm-submit-form">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-medium">Requester Name <span class="text-danger">*</span></label>
                    <input type="text" name="requester_name" class="form-control" required value="<?= old('requester_name') ?>">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="requester_email" class="form-control" value="<?= old('requester_email') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Phone</label>
                        <input type="text" name="requester_phone" class="form-control" value="<?= old('requester_phone') ?>">
                    </div>
                </div>

                <!-- Step 1: Choose facility -->
                <div class="mb-3">
                    <label class="form-label fw-medium">Facility <span class="text-muted small">(optional)</span></label>
                    <select name="facility_id" id="complaintFacility" class="form-select">
                        <option value="">— Select facility first —</option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= (string)(old('facility_id') ?: ($facilityPrefill ?? '')) === (string)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Step 2: Unit (loaded after facility is chosen) -->
                <div class="mb-3" id="unitWrapper" <?= old('facility_id') ? '' : 'style="display:none"' ?>>
                    <label class="form-label fw-medium">Unit <span class="text-muted small">(optional)</span></label>
                    <select name="unit_id" id="complaintUnit" class="form-select">
                        <option value="">— No unit —</option>
                    </select>
                    <div id="unitSpinner" class="text-muted small mt-1" style="display:none"><i class="bi bi-hourglass-split me-1"></i>Loading units…</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select…</option>
                            <?php foreach (['Electrical','HVAC','Plumbing','Cleaning','Civil','IT','Fire Safety','Security','Other'] as $c): ?>
                                <option value="<?= $c ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <?php foreach (['critical'=>'Critical','high'=>'High','medium'=>'Medium','low'=>'Low'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= old('priority','medium') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" required><?= old('description') ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Attach Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-brand fm-submit-btn">Submit Complaint</button>
                    <a href="/helpdesk" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    const facSel    = document.getElementById('complaintFacility');
    const unitSel   = document.getElementById('complaintUnit');
    const unitWrap  = document.getElementById('unitWrapper');
    const spinner   = document.getElementById('unitSpinner');
    const oldUnitId = '<?= old('unit_id') ?>';

    function loadUnits(facilityId, preselectId) {
        if (!facilityId) {
            unitWrap.style.display = 'none';
            unitSel.innerHTML = '<option value="">— No unit —</option>';
            return;
        }
        spinner.style.display = '';
        unitWrap.style.display = '';
        unitSel.disabled = true;

        fetch('/helpdesk/ajax/units/' + facilityId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(units => {
            unitSel.innerHTML = '<option value="">— No unit —</option>';
            units.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.unit_number;
                if (String(u.id) === String(preselectId)) opt.selected = true;
                unitSel.appendChild(opt);
            });
        })
        .catch(() => {
            unitSel.innerHTML = '<option value="">— Error loading units —</option>';
        })
        .finally(() => {
            spinner.style.display = 'none';
            unitSel.disabled = false;
        });
    }

    facSel.addEventListener('change', function () {
        loadUnits(this.value, '');
    });

    // On page load (e.g. validation redirect with old values): pre-load units
    if (facSel.value) {
        loadUnits(facSel.value, oldUnitId);
    }
})();
</script>
<?= $this->endSection() ?>
