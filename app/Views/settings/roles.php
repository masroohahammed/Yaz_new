<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$groups = $permissionGroups ?? \App\Services\RbacService::permissionGroups();
$activeRole = old('active_role', (string) ($roles[0]['name'] ?? ''));
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-shield-lock me-2"></i>Roles & Access</h1>
    <div class="small text-muted mt-1">
      Choose what each role can <strong>see in the sidebar</strong> and <strong>open</strong>.
      Use the module matrix for fine-grained View / Create / Edit / Delete on list pages.
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= base_url('settings/permissions') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid-3x3-gap me-1"></i>Module matrix (V/C/E/D)</a>
    <a href="<?= base_url('settings/workspaces') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-diagram-3 me-1"></i>Workspaces</a>
    <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="fm-card h-100 p-3">
      <div class="small text-muted mb-2">Roles</div>
      <div class="list-group list-group-flush role-picker" id="rolePicker">
        <?php foreach ($roles as $r):
          $rn = (string) $r['name'];
          $isAdmin = $rn === 'super_admin';
          $count = $isAdmin ? count($permissions) : count(array_unique($permMap[$rn] ?? []));
        ?>
        <button type="button"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center<?= $activeRole === $rn ? ' active' : '' ?>"
                data-role="<?= esc($rn) ?>">
          <span>
            <span class="fw-semibold"><?= esc($r['display_name'] ?? $rn) ?></span>
            <span class="d-block small opacity-75"><?= esc(str_replace('_', ' ', $rn)) ?></span>
          </span>
          <?php if ($isAdmin): ?>
          <span class="badge bg-success">Full</span>
          <?php else: ?>
          <span class="badge bg-secondary"><?= (int) $count ?></span>
          <?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <?= form_open(base_url('settings/roles/save'), ['id' => 'rolesForm']) ?>
    <input type="hidden" name="active_role" id="activeRoleField" value="<?= esc($activeRole) ?>">

    <div class="fm-card p-3 mb-3">
      <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="input-group input-group-sm" style="max-width:320px">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" class="form-control" id="permSearch" placeholder="Filter permissions…">
        </div>
        <div class="small text-muted">Tip: uncheck a module to hide it from the sidebar for that role.</div>
      </div>
    </div>

    <?php foreach ($roles as $r):
      $rn = (string) $r['name'];
      $isAdmin = $rn === 'super_admin';
    ?>
    <div class="role-panel<?= $activeRole === $rn ? '' : ' d-none' ?>" data-role-panel="<?= esc($rn) ?>">
      <?php if ($isAdmin): ?>
      <div class="alert alert-success small mb-3">
        <i class="bi bi-shield-check me-1"></i>
        <strong>Super Admin</strong> always has full access. Other roles inherit defaults until you customize them here.
      </div>
      <?php else: ?>
      <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary role-select-all" data-role="<?= esc($rn) ?>">Select all</button>
        <button type="button" class="btn btn-sm btn-outline-secondary role-clear-all" data-role="<?= esc($rn) ?>">Clear all</button>
      </div>
      <?php endif; ?>

      <?php foreach ($groups as $groupLabel => $groupKeys): ?>
      <div class="fm-card mb-3 perm-group" data-group>
        <div class="card-header-fm d-flex justify-content-between align-items-center py-2 px-3">
          <h6 class="mb-0"><?= esc($groupLabel) ?></h6>
          <?php if (! $isAdmin): ?>
          <button type="button" class="btn btn-link btn-sm text-decoration-none group-toggle" data-role="<?= esc($rn) ?>">Toggle group</button>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <div class="row g-2">
            <?php foreach ($groupKeys as $perm):
              if (! isset($labels[$perm])) {
                  continue;
              }
              $checked = $isAdmin || in_array($perm, $permMap[$rn] ?? [], true)
                  || in_array('*', $permMap[$rn] ?? [], true);
            ?>
            <div class="col-md-6 col-lg-4 perm-item" data-label="<?= esc(strtolower($labels[$perm] . ' ' . $perm)) ?>">
              <label class="d-flex align-items-start gap-2 border rounded p-2 h-100 mb-0<?= $isAdmin ? ' bg-light' : '' ?>" style="cursor:<?= $isAdmin ? 'default' : 'pointer' ?>">
                <?php if ($isAdmin): ?>
                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                <?php else: ?>
                <input type="checkbox" class="form-check-input mt-1 perm-check" name="perm[<?= esc($rn) ?>][]" value="<?= esc($perm) ?>" <?= $checked ? 'checked' : '' ?>>
                <?php endif; ?>
                <span>
                  <span class="small fw-semibold d-block"><?= esc($labels[$perm]) ?></span>
                  <code class="small text-muted" style="font-size:.65rem"><?= esc($perm) ?></code>
                </span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="sticky-bottom bg-white border-top py-3 px-2 mt-2 d-flex justify-content-between align-items-center" style="bottom:0;z-index:5">
      <span class="small text-muted">Changes apply after save — sidebar and page access update immediately for each role.</span>
      <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-1"></i>Save all roles</button>
    </div>
    <?= form_close() ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  const picker = document.getElementById('rolePicker');
  const panels = document.querySelectorAll('[data-role-panel]');
  const activeField = document.getElementById('activeRoleField');
  const search = document.getElementById('permSearch');

  function showRole(name) {
    panels.forEach(p => p.classList.toggle('d-none', p.dataset.rolePanel !== name));
    picker?.querySelectorAll('[data-role]').forEach(btn => btn.classList.toggle('active', btn.dataset.role === name));
    if (activeField) activeField.value = name;
  }

  picker?.addEventListener('click', e => {
    const btn = e.target.closest('[data-role]');
    if (!btn) return;
    showRole(btn.dataset.role);
  });

  search?.addEventListener('input', () => {
    const q = search.value.trim().toLowerCase();
    document.querySelectorAll('.perm-item').forEach(el => {
      el.classList.toggle('d-none', q !== '' && !(el.dataset.label || '').includes(q));
    });
  });

  document.querySelectorAll('.role-select-all').forEach(btn => {
    btn.addEventListener('click', () => {
      const role = btn.dataset.role;
      document.querySelectorAll(`.role-panel[data-role-panel="${role}"] .perm-check`).forEach(c => { c.checked = true; });
    });
  });

  document.querySelectorAll('.role-clear-all').forEach(btn => {
    btn.addEventListener('click', () => {
      const role = btn.dataset.role;
      document.querySelectorAll(`.role-panel[data-role-panel="${role}"] .perm-check`).forEach(c => { c.checked = false; });
    });
  });

  document.querySelectorAll('.group-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.perm-group');
      const checks = group?.querySelectorAll('.perm-check');
      if (!checks?.length) return;
      const allOn = Array.from(checks).every(c => c.checked);
      checks.forEach(c => { c.checked = !allOn; });
    });
  });
})();
</script>
<?= $this->endSection() ?>
