<div class="mb-3">
  <label class="form-label">Assigned Properties <span class="text-muted small">(property managers, landlords, caretakers)</span></label>
  <select name="facility_ids[]" class="form-select" multiple size="<?= min(8, max(4, count($facilities ?? []))) ?>">
    <?php foreach ($facilities ?? [] as $f): ?>
      <?php $fid = (int) ($f['id'] ?? 0); ?>
      <option value="<?= $fid ?>" <?= in_array($fid, array_map('intval', $assignedFacilityIds ?? []), true) ? 'selected' : '' ?>>
        <?= esc(trim(($f['name'] ?? '') . (! empty($f['code']) ? ' · ' . $f['code'] : ''))) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <div class="form-text">Users only see data for selected properties. Leave empty to deny property-scoped access.</div>
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
