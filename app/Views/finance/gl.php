<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'General Ledger', 'subtitle' => 'Auto-posted from payments, expenses & vendor bills', 'backUrl' => 'finance']) ?>
<a href="<?= base_url('finance/coa') ?>" class="btn btn-fm-outline btn-sm mb-3">Chart of Accounts</a>
<?php if (empty($entries)): ?>
<div class="alert alert-info">No journal entries yet. Posting occurs when you approve expenses, record payments, or approve purchase orders.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="table table-sm mb-0">
  <thead><tr><th>Entry #</th><th>Date</th><th>Description</th><th>Source</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach ($entries as $e): ?>
  <tr>
    <td><code><?= esc($e['entry_number']) ?></code></td>
    <td><?= esc($e['entry_date']) ?></td>
    <td class="small"><?= esc($e['description'] ?? '') ?></td>
    <td class="small text-muted"><?= esc($e['source_module'] ?? '') ?> / <?= esc($e['source_type'] ?? '') ?> #<?= (int)($e['source_id']??0) ?></td>
    <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= esc($e['status']) ?></span></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
