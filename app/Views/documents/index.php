<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('fm'); ?>

<div class="page-header mb-3">
  <h1 class="h4 mb-0"><i class="bi bi-folder2-open me-2"></i>Document Management</h1>
  <p class="text-muted small mb-0">Properties, units, tenants, contracts, inspections, and general files.</p>
</div>

<?= $this->include('documents/_tab', [
  'module'            => $module ?? '',
  'refId'             => (int) ($refId ?? 0),
  'embed'             => false,
  'documents'         => $documents ?? [],
  'docTypes'          => $docTypes ?? fm_document_types(),
  'filters'           => $filters ?? [],
  'linkOptions'       => $linkOptions ?? [],
  'migrationRequired' => ! empty($migrationRequired),
]) ?>

<?= $this->endSection() ?>
