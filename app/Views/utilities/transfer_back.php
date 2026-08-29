<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Transfer Back — <?= esc($account['utility_name']) ?></h1>
<?= form_open(base_url('utilities/'.$account['id'].'/transfer-back')) ?>
<?= csrf_field() ?>
<div class="mb-2"><label class="form-label small">Transfer date *</label><input type="date" name="transfer_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
<div class="mb-2"><label class="form-label small">Reason</label><textarea name="reason" class="form-control form-control-sm" rows="2"></textarea></div>
<button type="submit" class="btn btn-sm btn-fm-primary">Transfer Back</button>
<?= form_close() ?>
<?= $this->endSection() ?>
