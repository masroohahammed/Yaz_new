<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-phone me-2"></i>Mobile App Log</h1>
    <div class="small text-muted">Flutter app CTA actions, errors, and splash events (datewise)</div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Reports</a>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from ?? date('Y-m-01')) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Action</label>
        <input type="text" name="action" class="form-control form-control-sm" value="<?= esc($filterAction ?? '') ?>" placeholder="e.g. cta_login">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="success" <?= ($filterStatus ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
          <option value="error" <?= ($filterStatus ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
          <option value="info" <?= ($filterStatus ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">User</label>
        <select name="user_id" class="form-select form-select-sm">
          <option value="">All users</option>
          <?php foreach ($users ?? [] as $u): ?>
          <option value="<?= $u['id'] ?>" <?= (int)($filterUser ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-1">
        <button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-1">
        <a href="<?= base_url('reports/app-log') ?>" class="btn btn-fm-outline btn-sm w-100">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><?= (int)($total ?? 0) ?> entries</h5>
    <span class="small text-muted">Page <?= (int)($currentPage ?? 1) ?> · <?= esc($from ?? '') ?> to <?= esc($to ?? '') ?></span>
  </div>
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table table-sm">
        <thead>
          <tr>
            <th>Date / Time</th>
            <th>Status</th>
            <th>Action</th>
            <th>User</th>
            <th>Message</th>
            <th>App</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($logs ?? [] as $l): ?>
        <tr>
          <td class="small text-muted text-nowrap"><?= date('d M Y H:i:s', strtotime($l['created_at'])) ?></td>
          <td>
            <span class="badge <?= $l['status'] === 'error' ? 'bg-danger' : ($l['status'] === 'success' ? 'bg-success' : 'bg-secondary') ?>">
              <?= esc(ucfirst($l['status'])) ?>
            </span>
          </td>
          <td class="small fw-semibold"><?= esc($l['action']) ?></td>
          <td class="small"><?= esc($l['user_name'] ?? '—') ?></td>
          <td class="small" style="max-width:320px">
            <?= esc($l['message'] ?? '') ?>
            <?php if (! empty($l['context_json'])): ?>
            <details class="mt-1"><summary class="text-muted">Context</summary><pre class="small mb-0"><?= esc($l['context_json']) ?></pre></details>
            <?php endif; ?>
          </td>
          <td class="small text-muted"><?= esc($l['app_version'] ?? '') ?> · <?= esc($l['platform'] ?? '') ?></td>
          <td class="small text-muted"><?= esc($l['ip_address'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No app log entries for this period.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
