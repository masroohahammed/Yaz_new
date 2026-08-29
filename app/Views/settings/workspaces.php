<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-diagram-3 me-2"></i>Role Workspaces</h1>
    <div class="small text-muted">Assign PM, FM, both, or portal workspace per role.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<?= form_open(base_url('settings/workspaces/save')) ?>
<div class="fm-card"><div class="fm-card-body p-0" style="overflow-x:auto">
  <table class="fm-table table-sm">
    <thead>
      <tr>
        <th>Role</th>
        <th>Slug</th>
        <th style="min-width:220px">Workspace</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($roles as $r): ?>
      <tr>
        <td class="fw-semibold"><?= esc($r['display_name']) ?></td>
        <td class="small text-muted"><code><?= esc($r['name']) ?></code></td>
        <td>
          <select name="workspace[<?= esc($r['name']) ?>]" class="form-select form-select-sm">
            <?php foreach ($workspaces as $val => $label): ?>
            <option value="<?= esc($val) ?>" <?= ($r['workspace'] ?? '') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<button type="submit" class="btn btn-fm-primary mt-3"><i class="bi bi-check-lg me-1"></i>Save Workspaces</button>
<?= form_close() ?>
<?= $this->endSection() ?>
