<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1>Set Property Budget</h1></div>
  <a href="<?= base_url('budgets') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= base_url('budgets') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3 mb-4">
    <div class="col-md-5">
      <label class="form-label">Property <span class="text-danger">*</span></label>
      <select name="facility_id" class="form-select" required>
        <option value="">— select —</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Year <span class="text-danger">*</span></label>
      <select name="year" class="form-select" required>
        <?php for ($y=date('Y')+1; $y>=date('Y')-2; $y--): ?>
          <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Month</th>
          <?php for ($m=1;$m<=12;$m++): ?>
            <th class="text-center" style="min-width:80px"><?= date('M', mktime(0,0,0,$m,1)) ?></th>
          <?php endfor; ?>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="fw-semibold text-success">Income</td>
          <?php for ($m=1;$m<=12;$m++): ?>
            <td><input type="number" step="0.01" name="income[<?= $m ?>]" class="form-control form-control-sm" placeholder="0" style="min-width:75px"></td>
          <?php endfor; ?>
        </tr>
        <tr>
          <td class="fw-semibold text-danger">Expense</td>
          <?php for ($m=1;$m<=12;$m++): ?>
            <td><input type="number" step="0.01" name="expense[<?= $m ?>]" class="form-control form-control-sm" placeholder="0" style="min-width:75px"></td>
          <?php endfor; ?>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    <button class="btn btn-fm-primary">Save Budget</button>
    <a href="<?= base_url('budgets') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
