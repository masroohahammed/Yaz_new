<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Owner Statement</h1></div>
<form method="get" class="row g-2 mb-3">
  <div class="col-md-3"><select name="landlord_id" class="form-select form-select-sm" required>
    <option value="">Select landlord</option>
    <?php foreach ($landlords as $l): ?><option value="<?= $l['id'] ?>" <?= $landlordId==$l['id']?'selected':'' ?>><?= esc($l['full_name']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-auto"><input type="date" name="date_from" value="<?= esc($from) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><input type="date" name="date_to" value="<?= esc($to) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><button class="btn btn-sm btn-secondary">Generate</button></div>
</form>
<?php if ($statement): ?>
<h6><?= esc($statement['landlord']['full_name'] ?? '') ?></h6>
<p class="small">Gross income: <?= number_format($statement['grossIncome'],2) ?> · Payouts: <?= number_format($statement['totalPayouts'],2) ?></p>
<table class="table table-sm"><thead><tr><th>Period</th><th>Net</th><th>Status</th></tr></thead>
<tbody><?php foreach ($statement['payouts'] as $p): ?><tr>
  <td><?= esc($p['period_from']) ?> — <?= esc($p['period_to']) ?></td>
  <td><?= number_format((float)$p['net_amount'],2) ?></td><td><?= esc($p['status']) ?></td>
</tr><?php endforeach; ?></tbody></table>
<?php endif; ?>
<?= $this->endSection() ?>
