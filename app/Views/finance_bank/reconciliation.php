<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-clipboard-check me-2"></i>Bank Reconciliation</h1></div>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Start Reconciliation</h5></div><div class="fm-card-body">
      <form method="post" action="<?= base_url('finance-bank/reconciliation/store') ?>"><?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Bank Account</label><select name="bank_account_id" class="form-select" required><?php foreach ($banks as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label">Statement Date</label><input type="date" name="statement_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        <div class="mb-3"><label class="form-label">Statement Opening</label><input type="number" step="0.01" name="statement_opening" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Statement Closing</label><input type="number" step="0.01" name="statement_closing" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        <button class="btn btn-fm-primary">Start</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Reconciliation History</h5></div><div class="fm-card-body p-0">
      <?php if (empty($recs)): ?><p class="text-center py-4 text-muted">No reconciliations yet.</p><?php else: ?>
      <table class="fm-table"><thead><tr><th>Date</th><th>Account</th><th>System</th><th>Statement</th><th>Diff</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($recs as $r): ?>
      <tr>
        <td class="small"><?= date('d M Y', strtotime($r['statement_date'])) ?></td>
        <td class="small"><?= esc($r['bank_account_name'] ?? '') ?></td>
        <td class="small"><?= number_format((float)$r['system_balance'],2) ?></td>
        <td class="small"><?= number_format((float)$r['statement_closing'],2) ?></td>
        <td class="small fw-bold <?= abs((float)$r['difference']) > 0.01 ? 'text-danger' : 'text-success' ?>"><?= number_format((float)$r['difference'],2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$r['status'])) ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?= $this->endSection() ?>
