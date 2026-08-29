<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-list-task me-2 text-warning"></i>Collection Assignments</h4>
    <div class="d-flex gap-2">
        <?php $userRole = session()->get('user_role'); ?>
        <?php if (in_array($userRole, ['super_admin', 'property_manager', 'finance_manager'])): ?>
        <a href="<?= base_url('collector/assign') ?>" class="btn btn-warning">
            <i class="bi bi-plus-circle me-1"></i>Assign Collections
        </a>
        <?php endif; ?>
        <a href="<?= base_url('collector') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
</div>

<!-- Status Filter Tabs -->
<ul class="nav nav-pills mb-3" id="assignTab">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="pending">Pending</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="collected">Collected</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="skipped">Skipped</a></li>
</ul>

<?php if (empty($assignments)): ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        No assignments found.
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="assignTable">
            <thead class="table-light">
                <tr>
                    <th>Tenant</th>
                    <th>Payment #</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Property</th>
                    <th>Assigned</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                <tr data-status="<?= esc($a['status']) ?>">
                    <td>
                        <div class="fw-medium"><?= esc($a['tenant_name'] ?? '—') ?></div>
                        <?php if ($a['tenant_phone'] ?? ''): ?>
                            <div class="text-muted small"><i class="bi bi-phone me-1"></i><?= esc($a['tenant_phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($a['payment_number'] ?? '—') ?></td>
                    <td><?= esc($currency) ?> <?= $a['payment_amount'] !== null ? number_format((float)$a['payment_amount'], 2) : '—' ?></td>
                    <td><?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '—' ?></td>
                    <td><?= esc($a['facility_name'] ?? '—') ?></td>
                    <td><?= $a['assigned_date'] ? date('d M Y', strtotime($a['assigned_date'])) : '—' ?></td>
                    <td>
                        <?php $colors = ['pending' => 'warning', 'collected' => 'success', 'skipped' => 'secondary', 'cancelled' => 'danger']; ?>
                        <span class="badge bg-<?= $colors[$a['status']] ?? 'secondary' ?>"><?= ucfirst($a['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($a['status'] === 'pending' && $a['payment_id']): ?>
                            <a href="<?= base_url('collector/collect/' . $a['payment_id']) ?>"
                               class="btn btn-danger btn-sm py-2 px-3">
                                <i class="bi bi-cash-coin me-1"></i>Collect
                            </a>
                        <?php elseif ($a['tenant_id']): ?>
                            <a href="<?= base_url('collector/tenant/' . $a['tenant_id']) ?>"
                               class="btn btn-outline-secondary btn-sm">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        document.querySelectorAll('#assignTable tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});
</script>
<?= $this->endSection() ?>
