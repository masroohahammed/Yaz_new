<?php
/** @var string|null $endDate */
/** @var string|null $startDate */
/** @var int|null $expiryDays */
$endDate    = trim((string) ($endDate ?? ''));
$startDate  = trim((string) ($startDate ?? ''));
if (! isset($expiryDays) || $expiryDays === null) {
    if ($endDate !== '') {
        helper('fm');
        $expiryDays = fm_contract_days_until($endDate);
    } else {
        $expiryDays = null;
    }
}
$daysUntil      = ($expiryDays !== null && $expiryDays > 0) ? $expiryDays : null;
$daysExpiredAgo = ($expiryDays !== null && $expiryDays < 0) ? abs($expiryDays) : null;
$isExpired      = $daysExpiredAgo !== null;
$isExpiringSoon = $daysUntil !== null && $daysUntil <= 30;
$isCritical     = $daysUntil !== null && $daysUntil <= 7;
?>
<?php if ($endDate === '' && $startDate === ''): ?>
<span class="text-muted">—</span>
<?php else: ?>
<?php if ($startDate !== ''): ?>
<div class="x-small text-muted">Start: <?= date('d M Y', strtotime($startDate)) ?></div>
<?php endif; ?>
<?php if ($endDate !== ''): ?>
<div class="<?= $isExpired ? 'text-danger fw-bold' : ($isCritical ? 'text-danger fw-bold' : ($isExpiringSoon ? 'text-warning fw-semibold' : '')) ?>">
  End: <?= date('d M Y', strtotime($endDate)) ?>
  <?php if ($isExpired): ?>
  <br><span class="badge bg-danger mt-1">Expired <?= $daysExpiredAgo ?>d ago</span>
  <?php elseif ($isCritical): ?>
  <br><span class="badge bg-danger mt-1"><?= $daysUntil ?>d left</span>
  <?php elseif ($isExpiringSoon): ?>
  <br><span class="badge bg-warning text-dark mt-1"><?= $daysUntil ?>d left</span>
  <?php elseif ($daysUntil !== null): ?>
  <br><span class="badge bg-secondary mt-1"><?= $daysUntil ?>d left</span>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
