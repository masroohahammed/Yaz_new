<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-shield-lock me-2 text-primary"></i>Permissions Matrix</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('settings') ?>">Settings</a></li><li class="breadcrumb-item active">Permissions</li></ol></nav>
  </div>
</div>

<?php if(!empty($migrationRequired)): ?>
<div class="alert alert-warning">Role permissions table is not available. Run database migration first.</div>
<?php else: ?>

<form action="<?= base_url('settings/permissions/save') ?>" method="post">
  <?= csrf_field() ?>

  <div class="fm-card">
    <div class="fm-card-body p-0">
      <div class="table-responsive">
        <table class="fm-table" style="font-size:.78rem">
          <thead>
            <tr>
              <th style="min-width:140px">Module</th>
              <?php foreach($roles as $role): ?>
              <th colspan="4" class="text-center"><?= esc($role['display_name'] ?? $role['name']) ?></th>
              <?php endforeach; ?>
            </tr>
            <tr class="table-light">
              <th></th>
              <?php foreach($roles as $role): ?>
              <th class="text-center px-1" style="min-width:34px" title="View">V</th>
              <th class="text-center px-1" style="min-width:34px" title="Create">C</th>
              <th class="text-center px-1" style="min-width:34px" title="Edit">E</th>
              <th class="text-center px-1" style="min-width:34px" title="Delete">D</th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach($modules as $module): ?>
          <tr>
            <td class="fw-semibold"><?= ucfirst(str_replace('_', ' ', $module)) ?></td>
            <?php foreach($roles as $role): ?>
            <?php
            $rId  = $role['id'];
            $perm = $matrix[$rId][$module] ?? [];
            $isAdmin = $role['name'] === 'super_admin';
            ?>
            <?php foreach(['view','create','edit','delete'] as $flag): ?>
            <td class="text-center px-1">
              <?php if($isAdmin): ?>
              <input type="hidden" name="perm[<?= $rId ?>][<?= $module ?>][<?= $flag ?>]" value="1">
              <i class="bi bi-check-circle-fill text-success"></i>
              <?php else: ?>
              <input type="checkbox" name="perm[<?= $rId ?>][<?= $module ?>][<?= $flag ?>]" value="1" class="form-check-input m-0"
                <?= !empty($perm['can_'.$flag]) ? 'checked' : '' ?>>
              <?php endif; ?>
            </td>
            <?php endforeach; ?>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-primary-brand"><i class="bi bi-save me-1"></i>Save Permissions</button>
    <span class="text-muted small ms-3">V = View &nbsp; C = Create &nbsp; E = Edit &nbsp; D = Delete</span>
  </div>
</form>
<?php endif; ?>

<?= $this->endSection() ?>
