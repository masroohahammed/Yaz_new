<?php
/** @var array<string, mixed> $emp */
/** @var array<string, mixed>|null $salaryStructure */
/** @var list<array<string, mixed>> $salaryRevisions */
/** @var list<array<string, mixed>> $advances */
/** @var list<array<string, mixed>> $loans */
/** @var array<string, bool> $perms */
?>
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-cash-stack"></i> Current Salary</h6>
  <?php if (empty($salaryStructure)): ?>
  <p class="text-muted small mb-0">No salary structure on file.</p>
  <?php else: ?>
  <div class="row g-2 small mb-2">
    <div class="col-md-4"><span class="text-muted">Gross</span><div class="fw-semibold"><?= number_format((float)$salaryStructure['gross_salary'], 2) ?> <?= esc($salaryStructure['currency'] ?? 'QAR') ?></div></div>
    <div class="col-md-4"><span class="text-muted">Net</span><div class="fw-semibold"><?= number_format((float)$salaryStructure['net_salary'], 2) ?> <?= esc($salaryStructure['currency'] ?? 'QAR') ?></div></div>
    <div class="col-md-4"><span class="text-muted">Effective</span><div><?= date('d M Y', strtotime($salaryStructure['effective_from'])) ?></div></div>
  </div>
  <?php if (!empty($salaryStructure['lines'])): ?>
  <table class="fm-table table-sm"><thead><tr><th>Component</th><th>Amount</th></tr></thead><tbody>
  <?php foreach ($salaryStructure['lines'] as $l): ?>
  <tr><td class="small"><?= esc($l['name']) ?></td><td><?= number_format((float)$l['amount'], 2) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
  <?php if (!empty($perms['employee.salary.edit'])): ?>
  <a href="<?= base_url('hr/salary?employee_id='.(int)$emp['id']) ?>" class="btn btn-sm btn-fm-outline mt-2">Manage Salary</a>
  <?php endif; ?>
  <?php endif; ?>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="fm-form-section"><h6>Advances</h6>
    <?php if (empty($advances)): ?><p class="text-muted small">None</p><?php else: ?>
    <table class="fm-table table-sm"><thead><tr><th>Amount</th><th>Balance</th><th>Status</th></tr></thead><tbody>
    <?php foreach (array_slice($advances, 0, 5) as $a): ?>
    <tr><td><?= number_format((float)$a['amount'],2) ?></td><td><?= number_format((float)$a['balance'],2) ?></td><td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= esc(ucfirst($a['status'])) ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table><?php endif; ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="fm-form-section"><h6>Loans</h6>
    <?php if (empty($loans)): ?><p class="text-muted small">None</p><?php else: ?>
    <table class="fm-table table-sm"><thead><tr><th>Principal</th><th>Balance</th><th>Status</th></tr></thead><tbody>
    <?php foreach (array_slice($loans, 0, 5) as $l): ?>
    <tr><td><?= number_format((float)$l['principal'],2) ?></td><td><?= number_format((float)$l['balance'],2) ?></td><td><span class="fm-badge badge-status-<?= esc($l['status']) ?>"><?= esc(ucfirst($l['status'])) ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table><?php endif; ?>
    </div>
  </div>
</div>
