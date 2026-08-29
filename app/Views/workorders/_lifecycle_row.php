<?php
/** Work order lifecycle — single horizontal row */
if (empty($stageFlow)) {
    return;
}
$currentStage = $wo['workflow_stage'] ?? 'converted_to_wo';
?>
<div class="fm-card mb-3 wo-lifecycle-card" id="wo-workflow">
  <div class="fm-card-body py-3">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
      <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>Work Order Lifecycle</h6>
      <span class="small text-muted">Current: <strong class="text-primary-brand"><?= esc(ucwords(str_replace('_', ' ', $currentStage))) ?></strong></span>
    </div>
    <div class="wo-lifecycle-row">
      <div class="wo-lifecycle-track">
        <?php foreach ($stageFlow as $i => $step):
          $short = $step['short'] ?? preg_replace('/^\d+\.\s*/', '', $step['label']);
        ?>
        <div class="wo-lifecycle-step <?= $step['current'] ? 'is-current' : ($step['done'] ? 'is-done' : '') ?>" title="<?= esc($step['label']) ?>">
          <div class="wo-lifecycle-icon"><i class="bi <?= esc($step['icon']) ?>"></i></div>
          <div class="wo-lifecycle-label"><?= esc($short) ?></div>
        </div>
        <?php if ($i < count($stageFlow) - 1): ?>
        <div class="wo-lifecycle-line <?= ($step['done'] || $step['current']) ? 'is-done' : '' ?>"></div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
