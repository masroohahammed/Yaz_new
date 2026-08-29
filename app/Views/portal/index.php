<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1><i class="bi bi-house-door me-2 text-primary"></i>Tenant Portal</h1>
  <p class="text-muted">Your maintenance requests and invoices.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="fm-card p-4">
      <h5 class="mb-3">Maintenance</h5>
      <a href="<?= base_url('helpdesk/create') ?>" class="btn btn-fm-primary btn-sm me-2">New Ticket</a>
      <a href="<?= base_url('helpdesk') ?>" class="btn btn-outline-secondary btn-sm">All Tickets</a>
    </div>
  </div>
  <div class="col-md-6">
    <div class="fm-card p-4">
      <h5 class="mb-3">Invoices</h5>
      <a href="<?= base_url('finance/invoices') ?>" class="btn btn-outline-secondary btn-sm">View All Invoices</a>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card p-0">
      <div class="p-3 border-bottom fw-medium">My Tickets</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Ticket</th><th>Subject</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($tickets)): ?>
              <tr><td colspan="3" class="text-muted text-center py-3">No tickets.</td></tr>
            <?php else: foreach ($tickets as $t): ?>
              <tr>
                <td><a href="<?= base_url('helpdesk/' . $t['id']) ?>"><?= esc($t['ticket_number'] ?? '') ?></a></td>
                <td><?= esc($t['title'] ?? '') ?></td>
                <td><?= esc($t['status'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card p-0">
      <div class="p-3 border-bottom fw-medium">Recent Invoices</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Invoice</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($invoices)): ?>
              <tr><td colspan="4" class="text-muted text-center py-3">No invoices.</td></tr>
            <?php else: foreach ($invoices as $i): ?>
              <tr>
                <td><?= esc($i['invoice_number'] ?? $i['id']) ?></td>
                <td><?= esc($i['total'] ?? '') ?></td>
                <td><?= esc($i['due_date'] ?? '') ?></td>
                <td><?= esc($i['status'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
