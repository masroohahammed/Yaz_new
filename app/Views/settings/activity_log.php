<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-journal-text me-2"></i>System Activity Log</h1>
    <div class="small text-muted">All actions — who, when, IP, and detail (super admin only)</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Settings</a>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small">Module</label>
        <select name="module" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach (['work_orders','job_cards','helpdesk','finance','settings','users','facilities'] as $m): ?>
          <option value="<?= $m ?>" <?= ($filterModule ?? '') === $m ? 'selected' : '' ?>><?= esc(ucwords(str_replace('_', ' ', $m))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Action</label>
        <input type="text" name="action" class="form-control form-select-sm" value="<?= esc($filterAction ?? '') ?>" placeholder="e.g. complete">
      </div>
      <div class="col-md-3">
        <label class="form-label small">User</label>
        <select name="user_id" class="form-select form-select-sm">
          <option value="">All users</option>
          <?php foreach ($users ?? [] as $u): ?>
          <option value="<?= $u['id'] ?>" <?= (int)($filterUser ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-2">
        <a href="<?= base_url('settings/activity-log') ?>" class="btn btn-fm-outline btn-sm w-100">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><?= (int)($total ?? 0) ?> entries</h5>
    <span class="small text-muted">Page <?= (int)($currentPage ?? 1) ?></span>
  </div>
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table table-sm">
        <thead>
          <tr>
            <th>Date / Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Record</th>
            <th>Description</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($logs ?? [] as $l): ?>
        <tr>
          <td class="small text-muted text-nowrap"><?= date('d M Y H:i:s', strtotime($l['created_at'])) ?></td>
          <td class="small">
            <div class="fw-semibold"><?= esc($l['user_name'] ?? 'System') ?></div>
            <?php if (!empty($l['user_email'])): ?><div class="x-small text-muted"><?= esc($l['user_email']) ?></div><?php endif; ?>
          </td>
          <td><span class="fm-badge"><?= esc($l['action']) ?></span></td>
          <td class="small"><?= esc($l['module']) ?></td>
          <td class="small"><?= $l['record_id'] ? '#' . (int)$l['record_id'] : '—' ?></td>
          <td class="small text-muted" style="max-width:320px"><?= esc($l['description'] ?? '') ?></td>
          <td class="x-small text-muted text-nowrap"><?= esc($l['ip_address'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No activity logged yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (($total ?? 0) > ($perPage ?? 50)): ?>
  <div class="fm-card-body border-top py-2">
    <nav>
      <ul class="pagination pagination-sm mb-0 justify-content-center">
        <?php $pages = (int) ceil($total / $perPage); ?>
        <?php for ($i = 1; $i <= min($pages, 10); $i++): ?>
        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&module=<?= esc($filterModule ?? '') ?>&action=<?= esc($filterAction ?? '') ?>&user_id=<?= (int)($filterUser ?? 0) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
