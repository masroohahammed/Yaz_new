<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Physical Cash Count</h1></div>
<form method="post" action="<?= base_url('finance-petty/counts/store') ?>"><?= csrf_field() ?>
<div class="fm-card mb-3"><div class="fm-card-body row g-3">
  <div class="col-md-6"><label class="form-label">Petty Cash Account</label><select name="petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?> (Bal: <?= number_format((float)$a['current_balance'],2) ?>)</option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Count Date</label><input type="date" name="count_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-3"><label class="form-label">Reason (if difference)</label><input name="reason" class="form-control"></div>
</div></div>
<div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Denominations</h5></div><div class="fm-card-body" id="denomRows">
  <?php foreach (['500','200','100','50','25','10','5','1','Coins'] as $d): ?>
  <div class="row g-2 mb-2 denom-row">
    <div class="col-md-4"><input name="denomination[]" class="form-control" value="QAR <?= $d === 'Coins' ? 'Coins' : $d ?>"></div>
    <div class="col-md-4"><input type="number" name="quantity[]" class="form-control" value="0" min="0"></div>
  </div>
  <?php endforeach; ?>
</div></div>
<button class="btn btn-fm-primary mt-3">Submit Count</button>
</form>
<?= $this->endSection() ?>
