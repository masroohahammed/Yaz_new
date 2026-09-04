<?php
/**
 * User property access fields — shown on create/edit user forms.
 *
 * @var list<array<string,mixed>> $facilities
 * @var list<int>                 $assignedFacilityIds
 * @var list<array<string,mixed>> $roles
 * @var array<string,mixed>|null  $user
 */
$selectedRoleId = (int) old('role_id', $user['role_id'] ?? 0);
$roleNamesById = [];
foreach ($roles ?? [] as $r) {
    $roleNamesById[(int) $r['id']] = (string) ($r['name'] ?? '');
}
$selectedRoleName = $roleNamesById[$selectedRoleId] ?? '';
$isCompanyWide = in_array($selectedRoleName, ['property_manager', 'facility_manager', 'super_admin'], true);
$needsAssignments = in_array($selectedRoleName, ['real_estate_manager', 'landlord', 'caretaker', 'manager', 'maintenance', 'maintenance_staff', 'maintenance_supervisor', 'technician'], true);
?>
<div class="mb-3" id="userPropertyAccessBlock">
  <label class="form-label">Assigned Properties</label>
  <?php if ($isCompanyWide): ?>
    <div class="alert alert-info py-2 small mb-2">
      <strong><?= esc(ucwords(str_replace('_', ' ', $selectedRoleName ?: 'This role'))) ?></strong> has access to all properties in their company. Property assignment is not required.
    </div>
  <?php else: ?>
    <select name="facility_ids[]" id="userFacilitySelect" class="form-select" multiple size="<?= min(8, max(4, count($facilities ?? []))) ?>" <?= $needsAssignments ? '' : '' ?>>
      <?php foreach ($facilities ?? [] as $f): ?>
        <?php $fid = (int) ($f['id'] ?? 0); ?>
        <option value="<?= $fid ?>" <?= in_array($fid, array_map('intval', $assignedFacilityIds ?? []), true) ? 'selected' : '' ?>>
          <?= esc(trim(($f['name'] ?? '') . (! empty($f['code']) ? ' · ' . $f['code'] : ''))) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">
      <?php if ($selectedRoleName === 'real_estate_manager'): ?>
        Real Estate Managers only see data for selected properties. Leave empty to deny access.
      <?php else: ?>
        Users only see data for selected properties. Leave empty to deny property-scoped access.
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php if (! empty($hasLandlordUserCol)): ?>
<div class="mb-3">
  <label class="form-label">Linked Landlord Record <span class="text-muted small">(landlord role only)</span></label>
  <select name="landlord_id" class="form-select">
    <option value="">— None —</option>
    <?php foreach ($landlordsList ?? [] as $ll): ?>
      <option value="<?= (int) $ll['id'] ?>" <?= (int) ($userLandlordId ?? 0) === (int) $ll['id'] ? 'selected' : '' ?>>
        <?= esc($ll['full_name'] ?? '') ?><?= ! empty($ll['email']) ? ' · ' . esc($ll['email']) : '' ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
<?php endif; ?>

<script>
(function () {
  const roleSelect = document.querySelector('select[name="role_id"]');
  const block = document.getElementById('userPropertyAccessBlock');
  if (! roleSelect || ! block) return;

  const companyWide = <?= json_encode(['property_manager', 'facility_manager', 'super_admin']) ?>;
  const needsAssign = <?= json_encode(['real_estate_manager', 'landlord', 'caretaker', 'manager', 'maintenance', 'maintenance_staff', 'maintenance_supervisor', 'technician']) ?>;
  const roleMap = <?= json_encode($roleNamesById) ?>;

  function refreshAccessUi() {
    const roleName = roleMap[roleSelect.value] || '';
    const select = block.querySelector('select');
    const info = block.querySelector('.alert-info');
    if (companyWide.includes(roleName)) {
      if (select) select.style.display = 'none';
      if (! info) {
        const div = document.createElement('div');
        div.className = 'alert alert-info py-2 small mb-0';
        div.textContent = 'This role has access to all company properties.';
        block.appendChild(div);
      }
    } else {
      if (select) select.style.display = '';
      if (info) info.remove();
    }
  }

  roleSelect.addEventListener('change', refreshAccessUi);
})();
</script>
