<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-person-plus me-2 text-warning"></i>Assign Collections to Collector</h4>
    <a href="<?= base_url('collector/assignments') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= base_url('collector/assign') ?>">
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
        <div class="card-header bg-transparent fw-semibold py-3 border-bottom">
            <i class="bi bi-gear me-2"></i>Assignment Settings
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                    <select name="collector_id" class="form-select form-select-lg" required>
                        <option value="">— Select Collector —</option>
                        <?php foreach ($collectors as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Assigned Date</label>
                    <input type="date" name="assigned_date" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="notes" class="form-control form-control-lg" placeholder="Optional instructions…">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px">
        <div class="card-header bg-transparent py-3 fw-semibold border-bottom d-flex align-items-center justify-content-between">
            <span><i class="bi bi-cash-stack me-2 text-danger"></i>Select Payments to Assign</span>
            <label class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="selectAll">
                <span class="form-check-label small">Select All</span>
            </label>
        </div>

        <?php if (empty($openPayments)): ?>
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>No open payments to assign.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40px"></th>
                        <th>Payment #</th>
                        <th>Tenant</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Property</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($openPayments as $p): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input pay-check"
                                   name="payment_ids[]" value="<?= $p['id'] ?>">
                        </td>
                        <td class="fw-medium"><?= esc($p['payment_number']) ?></td>
                        <td><?= esc($p['tenant_name'] ?? '—') ?></td>
                        <td><?= esc($currency) ?> <?= number_format((float)$p['amount'], 2) ?></td>
                        <td><?= $p['due_date'] ? date('d M Y', strtotime($p['due_date'])) : '—' ?></td>
                        <td>
                            <?php $statusColors = ['pending' => 'secondary', 'partial' => 'warning', 'overdue' => 'danger']; ?>
                            <span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
                        </td>
                        <td><?= esc($p['facility_name'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($openPayments)): ?>
    <div class="mt-4">
        <button type="submit" class="btn btn-warning btn-lg px-5">
            <i class="bi bi-person-check me-2"></i>Create Assignments
        </button>
    </div>
    <?php endif; ?>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const selectAll = document.getElementById('selectAll');
selectAll?.addEventListener('change', () => {
    document.querySelectorAll('.pay-check').forEach(cb => cb.checked = selectAll.checked);
});
</script>
<?= $this->endSection() ?>
