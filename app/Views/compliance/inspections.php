<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-clipboard2-check me-2 text-success"></i>Inspection Checklists</h1><div class="small text-muted">Create and track facility inspection checklists</div></div>
  <a href="<?= base_url('compliance/inspections/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Inspection</a>
</div>

<!-- Filters -->
<div class="fm-card mb-3">
  <div class="fm-card-body py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Facility</label>
        <select name="facility_id" class="form-select form-select-sm">
          <option value="">All Facilities</option>
          <?php foreach($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach(['pending','in_progress','passed','failed'] as $s): ?>
          <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-fm-outline btn-sm w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead><tr><th>Title</th><th>Facility</th><th>Type</th><th>Date</th><th>Inspector</th><th>Score</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($checklists as $cl): ?>
        <tr>
          <td class="fw-semibold"><a href="<?= base_url('compliance/inspections/view/'.$cl['id']) ?>" class="text-primary"><?= esc($cl['title']) ?></a></td>
          <td class="small"><?= esc($cl['facility_name']) ?></td>
          <td><span class="fm-badge" style="background:#eff6ff;color:#1d4ed8"><?= esc($cl['type']) ?></span></td>
          <td class="small"><?= date('d M Y', strtotime($cl['inspection_date'])) ?></td>
          <td class="small"><?= esc($cl['inspector_name'] ?: $cl['created_by_name']) ?></td>
          <td>
            <?php if($cl['score'] !== null): ?>
            <span class="fw-bold <?= $cl['score']>=80?'text-success':($cl['score']>=60?'text-warning':'text-danger') ?>"><?= $cl['score'] ?>%</span>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
          </td>
          <td>
            <?php $sc = match($cl['status']) {
              'passed'      => 'completed',
              'failed'      => 'cancelled',
              'in_progress' => 'in_progress',
              default       => 'pending'
            }; ?>
            <span class="fm-badge badge-status-<?= $sc ?>"><?= ucfirst(str_replace('_',' ',$cl['status'])) ?></span>
          </td>
          <td>
            <a href="<?= base_url('compliance/inspections/view/'.$cl['id']) ?>" class="btn-action btn-light" title="View"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($checklists)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-clipboard2-check d-block mb-2" style="font-size:2rem"></i>No inspections found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
