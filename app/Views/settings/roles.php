<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-shield-lock me-2"></i>Roles & Permissions</h1>
    <div class="small text-muted">Check modules each role can access. Super Admin always has full access.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<?= form_open(base_url('settings/roles/save')) ?>
<div class="fm-card"><div class="fm-card-body p-0" style="overflow-x:auto">
  <table class="fm-table table-sm">
    <thead>
      <tr>
        <th style="min-width:180px">Permission</th>
        <?php foreach ($roles as $r): ?>
        <th class="text-center small"><?= esc($r['display_name']) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($permissions as $perm): ?>
      <tr>
        <td class="small fw-semibold"><?= esc($labels[$perm] ?? $perm) ?></td>
        <?php foreach ($roles as $r):
          $rn = $r['name'];
          $checked = $rn === 'super_admin' || in_array($perm, $permMap[$rn] ?? [], true)
            || in_array('*', $permMap[$rn] ?? [], true);
          $disabled = $rn === 'super_admin';
        ?>
        <td class="text-center">
          <?php if ($disabled): ?>
            <i class="bi bi-check-circle-fill text-success"></i>
          <?php else: ?>
            <input type="checkbox" name="perm[<?= esc($rn) ?>][]" value="<?= esc($perm) ?>" <?= $checked ? 'checked' : '' ?> class="form-check-input">
          <?php endif; ?>
        </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<button type="submit" class="btn btn-fm-primary mt-3"><i class="bi bi-check-lg me-1"></i>Save Permissions</button>
<?= form_close() ?>
<?= $this->endSection() ?>
