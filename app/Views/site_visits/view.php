<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><?= esc($visit['visit_number']) ?></h1></div>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-card mb-3"><div class="fm-card-body">
      <p><strong>Facility:</strong> <?= esc($visit['facility_name']??'—') ?> · <strong>Unit:</strong> <?= esc($visit['unit_number']??'—') ?></p>
      <p><strong>Status:</strong> <?= esc($visit['status']) ?> · <strong>Scheduled:</strong> <?= $visit['scheduled_at'] ? date('d M Y H:i', strtotime($visit['scheduled_at'])) : '—' ?></p>
      <p><strong>Purpose:</strong><br><?= nl2br(esc($visit['purpose']??'')) ?></p>
      <p><strong>Requirements:</strong><br><?= nl2br(esc($visit['requirements']??'')) ?></p>
      <?php if (!empty($visit['observations'])): ?><p><strong>Observations:</strong><br><?= nl2br(esc($visit['observations'])) ?></p><?php endif; ?>
      <?php if ($visit['status'] === 'completed'): ?>
      <?php foreach (['technician_signature' => 'Technician', 'client_signature' => 'Client'] as $field => $lbl):
        $url = ! empty($visit[$field]) ? fm_logo_url($visit[$field]) : '';
        if ($url): ?>
      <p class="mb-2"><strong><?= $lbl ?> signature:</strong><br><img src="<?= esc($url) ?>" alt="<?= esc($lbl) ?>" style="max-width:100%;height:80px;border:1px solid #ddd;border-radius:4px"></p>
      <?php endif; endforeach; ?>
      <?php endif; ?>
    </div></div>
  </div>
  <div class="col-lg-5">
    <?php if ($visit['status'] !== 'completed'): ?>
    <div class="fm-card"><div class="card-header-fm"><h5>Complete visit</h5></div><div class="fm-card-body">
      <?= form_open(base_url('site-visits/complete/'.$visit['id']), ['class'=>'fm-submit-form','enctype'=>'multipart/form-data']) ?>
      <?= csrf_field() ?>
      <label class="form-label small">Observations</label><textarea name="observations" class="form-control form-control-sm mb-2" rows="3"></textarea>
      <label class="form-label small">Technician remarks</label><textarea name="technician_remarks" class="form-control form-control-sm mb-2" rows="2"></textarea>
      <label class="form-label small">Photo</label><input type="file" name="photo" class="form-control form-control-sm mb-2" accept="image/*">
      <?= view('partials/_signature_pad', ['fieldName'=>'technician_signature','label'=>'Technician signature']) ?>
      <?= view('partials/_signature_pad', ['fieldName'=>'client_signature','label'=>'Client signature']) ?>
      <button type="submit" class="btn btn-success w-100 fm-submit-btn">Complete visit</button>
      <?= form_close() ?>
    </div></div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/signature-pad.js') ?>?v=1"></script>
<?= $this->endSection() ?>
