<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-bar-chart-line me-2 text-primary"></i>CRM Reports</h1></div>
  <a href="<?= base_url('crm') ?>" class="btn btn-fm-outline btn-sm">Back to CRM</a>
</div>

<div class="row g-3">
  <!-- Funnel -->
  <div class="col-lg-5">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Lead Funnel by Stage</h6>
      <?php $total = array_sum($funnel ?: []); ?>
      <?php foreach ($funnel as $stage => $cnt): ?>
        <?php $pct = $total > 0 ? round($cnt/$total*100) : 0; ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1">
            <span><?= ucfirst(esc($stage)) ?></span>
            <strong><?= $cnt ?></strong>
          </div>
          <div class="progress" style="height:8px">
            <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($funnel)): ?>
        <p class="text-muted text-center py-4">No data available.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sources -->
  <div class="col-lg-4">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Leads by Source</h6>
      <table class="table table-sm">
        <thead><tr><th>Source</th><th class="text-end">Count</th></tr></thead>
        <tbody>
          <?php foreach ($sources as $src => $cnt): ?>
            <tr><td><?= esc($src) ?></td><td class="text-end"><?= $cnt ?></td></tr>
          <?php endforeach; ?>
          <?php if (empty($sources)): ?>
            <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Locations -->
  <div class="col-lg-3">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Top Preferred Locations</h6>
      <ol class="mb-0 ps-3">
        <?php foreach ($topLocs as $loc => $cnt): ?>
          <li class="mb-1 small"><?= esc($loc) ?> <span class="text-muted">(<?= $cnt ?>)</span></li>
        <?php endforeach; ?>
        <?php if (empty($topLocs)): ?>
          <li class="text-muted">No data.</li>
        <?php endif; ?>
      </ol>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
