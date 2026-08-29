<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-building-gear me-2"></i>Companies</h1></div>
  <a href="<?= base_url('settings/companies/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Company</a>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($companies)): ?>
  <p class="text-center py-5 text-muted">No companies yet. <a href="<?= base_url('settings/companies/create') ?>">Add one</a>.</p>
  <?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Code</th><th>Company Name</th><th>Contact Person</th><th>Email</th><th>Phone</th><th>VAT #</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($companies as $c): ?>
    <tr>
      <td class="fw-bold small text-muted"><?= esc($c['code']) ?></td>
      <td>
        <div class="fw-semibold small"><?= esc($c['name']) ?></div>
        <?php if($c['address']): ?><div class="x-small text-muted"><?= esc(substr($c['address'],0,40)) ?></div><?php endif; ?>
      </td>
      <td class="small"><?= esc($c['contact_person']??'—') ?></td>
      <td class="small text-muted"><?= esc($c['email']??'—') ?></td>
      <td class="small text-muted"><?= esc($c['phone']??'—') ?></td>
      <td class="small text-muted"><?= esc($c['vat_number']??'—') ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
      <td>
        <a href="<?= base_url('settings/companies/edit/'.$c['id']) ?>" class="btn-action bg-secondary text-white"><i class="bi bi-pencil"></i></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
