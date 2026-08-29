<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-book me-2"></i>Inspection Checklists Guide</h1>
    <div class="small text-muted">Weekly, monthly, and regular schedules for properties and units</div>
  </div>
  <a href="<?= base_url('compliance') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Compliance</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5>Schedules</h5></div>
      <div class="fm-card-body">
        <table class="table table-sm">
          <thead><tr><th>Schedule</th><th>Use for</th></tr></thead>
          <tbody>
            <tr><td><strong>Weekly</strong></td><td>Quick walk — common areas, elevators, fire exits, cleanliness</td></tr>
            <tr><td><strong>Monthly</strong></td><td>Deep checks — HVAC, fire equipment, generators, roof drains</td></tr>
            <tr><td><strong>Regular / Routine</strong></td><td>Standard periodic unit or building walkthrough</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5>Where to start</h5></div>
      <div class="fm-card-body small">
        <p><strong>Property / facility</strong> — <a href="<?= base_url('compliance/inspections') ?>">Property Inspections</a> → New → pick schedule (weekly/monthly/regular) → template loads automatically.</p>
        <p><strong>Units</strong> — <a href="<?= base_url('compliance/unit-inspections') ?>">Move-In / Move-Out</a> → Weekly / Monthly / Regular buttons per unit, or Move-In / Move-Out for handover.</p>
      </div>
    </div>

    <div class="fm-card">
      <div class="card-header-fm"><h5>Print &amp; PDF</h5></div>
      <div class="fm-card-body small">
        <ul class="mb-0">
          <li>From inspection view: <strong>Print</strong> or <strong>Download PDF</strong></li>
          <li>Property PDF: <code>/compliance/inspections/print/{id}?pdf=1</code></li>
          <li>Unit PDF: <code>/units/checklist/print/{id}?pdf=1</code></li>
          <li>PDF requires <code>dompdf/dompdf</code> (Composer). Without it, use browser print.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5>API (mobile)</h5></div>
      <div class="fm-card-body small">
        <p>JWT required. Base: <code>/api/v1/inspections/</code></p>
        <ul class="mb-0">
          <li><code>GET property</code> — filters: facility_id, status, frequency</li>
          <li><code>GET property/{id}</code></li>
          <li><code>GET units</code> — filters: facility_id, type, frequency</li>
          <li><code>GET units/{id}</code></li>
        </ul>
      </div>
    </div>
    <div class="fm-card">
      <div class="card-header-fm"><h5>Database</h5></div>
      <div class="fm-card-body small">
        <p>Run on server if tables missing:</p>
        <code class="d-block">database/compliance_inspections_patch.sql</code>
        <code class="d-block mt-1">database/compliance_inspections_frequency_patch.sql</code>
        <p class="mt-2 mb-0">Full reference: <code>docs/compliance-inspections.md</code> in repository.</p>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
