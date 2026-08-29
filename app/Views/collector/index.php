<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1><i class="bi bi-wallet2 me-2 text-primary"></i>Cash Collector</h1>
  <p class="text-muted">Field collection sessions and assignments.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="fm-card p-4">
      <h5 class="mb-3">Start Session</h5>
      <?= form_open(base_url('collector/session/start')) ?>
      <div class="mb-2">
        <label class="form-label small">Opening Float</label>
        <input type="number" step="0.01" name="opening_float" class="form-control form-control-sm" value="0">
      </div>
      <div class="mb-2">
        <label class="form-label small">Notes</label>
        <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>
      </div>
      <button type="submit" class="btn btn-fm-primary btn-sm">Start Session</button>
      <?= form_close() ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="fm-card p-4">
      <h5 class="mb-3">New Assignment</h5>
      <?= form_open(base_url('collector/assignment')) ?>
      <div class="mb-2">
        <label class="form-label small">Tenant ID</label>
        <input type="number" name="tenant_id" class="form-control form-control-sm">
      </div>
      <div class="mb-2">
        <label class="form-label small">Property ID</label>
        <input type="number" name="facility_id" class="form-control form-control-sm">
      </div>
      <div class="mb-2">
        <label class="form-label small">Payment ID</label>
        <input type="number" name="payment_id" class="form-control form-control-sm">
      </div>
      <div class="mb-2">
        <label class="form-label small">Assigned Date</label>
        <input type="date" name="assigned_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
      </div>
      <button type="submit" class="btn btn-fm-primary btn-sm">Assign</button>
      <?= form_close() ?>
    </div>
  </div>
</div>

<div class="fm-card p-0 mb-4">
  <div class="p-3 border-bottom"><strong>Collection Sessions</strong></div>
  <div class="table-responsive">
    <table class="table table-registry table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Code</th><th>Started</th><th>Closed</th><th>Status</th><th>Closing Cash</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($sessions)): ?>
          <tr><td colspan="6" class="text-center text-muted py-3">No sessions.</td></tr>
        <?php else: foreach ($sessions as $s): ?>
          <tr>
            <td><?= esc($s['session_code'] ?? $s['id']) ?></td>
            <td><?= esc($s['started_at'] ?? '') ?></td>
            <td><?= esc($s['closed_at'] ?? '—') ?></td>
            <td><?= esc($s['status'] ?? '') ?></td>
            <td><?= esc($s['closing_cash'] ?? '—') ?></td>
            <td>
              <?php if (($s['status'] ?? '') === 'open'): ?>
              <?= form_open(base_url('collector/session/' . $s['id'] . '/close'), ['class' => 'd-inline']) ?>
                <input type="hidden" name="closing_cash" value="0">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Close</button>
              <?= form_close() ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="fm-card p-0">
  <div class="p-3 border-bottom"><strong>Assignments</strong></div>
  <div class="table-responsive">
    <table class="table table-registry table-hover mb-0">
      <thead class="table-light">
        <tr><th>ID</th><th>Tenant</th><th>Property</th><th>Date</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($assignments)): ?>
          <tr><td colspan="6" class="text-center text-muted py-3">No assignments.</td></tr>
        <?php else: foreach ($assignments as $a): ?>
          <tr>
            <td><?= (int) $a['id'] ?></td>
            <td><?= esc($a['tenant_id'] ?? '') ?></td>
            <td><?= esc($a['facility_id'] ?? '') ?></td>
            <td><?= esc($a['assigned_date'] ?? '') ?></td>
            <td><?= esc($a['status'] ?? '') ?></td>
            <td>
              <?php if (($a['status'] ?? '') === 'pending'): ?>
              <?= form_open(base_url('collector/assignment/' . $a['id'] . '/collected'), ['class' => 'd-inline']) ?>
                <button type="submit" class="btn btn-sm btn-success">Collected</button>
              <?= form_close() ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
