<?php
/** @var list<array<string, mixed>> $timelineEvents */
?>
<div class="fm-form-section">
  <h6><i class="bi bi-clock-history"></i> Employee Timeline</h6>
  <?php if (empty($timelineEvents)): ?>
  <p class="text-muted small mb-0">No lifecycle events recorded yet.</p>
  <?php else: ?>
  <div class="timeline-list">
  <?php foreach ($timelineEvents as $ev): ?>
  <div class="border-start border-2 ps-3 mb-3 ms-2">
    <div class="small text-muted"><?= date('d M Y H:i', strtotime($ev['event_at'])) ?></div>
    <div class="fw-semibold small"><?= esc($ev['title']) ?></div>
    <?php if (!empty($ev['description'])): ?><div class="small text-muted"><?= esc($ev['description']) ?></div><?php endif; ?>
    <?php if (!empty($ev['event_code'])): ?><span class="badge bg-secondary-subtle text-secondary"><?= esc($ev['event_code']) ?></span><?php endif; ?>
  </div>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="mt-3 d-flex flex-wrap gap-2">
    <a href="<?= base_url('hr/onboarding?employee_id='.(int)$emp['id']) ?>" class="btn btn-sm btn-fm-outline">Onboarding</a>
    <a href="<?= base_url('hr/offboarding/'.(int)$emp['id']) ?>" class="btn btn-sm btn-fm-outline">Offboarding</a>
  </div>
</div>
