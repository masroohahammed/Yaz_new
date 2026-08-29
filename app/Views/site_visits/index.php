<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-geo-alt me-2"></i>Site Visits</h1></div>
  <a href="<?= base_url('site-visits/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Schedule visit</a>
</div>
<div class="fm-card mb-3"><div class="fm-card-body py-2">
  <?= form_open(base_url('site-visits'), ['method'=>'get','class'=>'row g-2 align-items-end']) ?>
  <div class="col-auto"><label class="small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
  <div class="col-auto"><label class="small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
  <div class="col-auto"><label class="small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option>
    <?php foreach (['scheduled','in_progress','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-auto"><button class="btn btn-fm-primary btn-sm">Filter</button></div>
  <?= form_close() ?>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Visit</th><th>Facility</th><th>Scheduled</th><th>Technician</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($visits as $v): ?>
<tr><td><a href="<?= base_url('site-visits/view/'.$v['id']) ?>" class="fw-semibold"><?= esc($v['visit_number']) ?></a></td>
<td><?= esc($v['facility_name']??'—') ?></td>
<td class="small"><?= $v['scheduled_at'] ? date('d M Y H:i', strtotime($v['scheduled_at'])) : '—' ?></td>
<td><?= esc($v['technician_name']??'—') ?></td>
<td><span class="fm-badge"><?= esc($v['status']) ?></span></td></tr>
<?php endforeach; ?>
<?php if (empty($visits)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No site visits</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
