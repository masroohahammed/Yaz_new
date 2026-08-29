<?php if (!empty($aiFlags)): ?>
<?php
$severityMeta = [
    'critical' => ['icon' => 'bi-exclamation-octagon-fill', 'label' => 'Critical', 'class' => 'ai-sev-critical'],
    'warning'  => ['icon' => 'bi-exclamation-triangle-fill', 'label' => 'Warning',  'class' => 'ai-sev-warning'],
    'info'     => ['icon' => 'bi-info-circle-fill', 'label' => 'Insight',  'class' => 'ai-sev-info'],
];
$moduleMeta = [
    'lease_contract'      => ['icon' => 'bi-file-earmark-text', 'label' => 'Lease', 'url' => 'contracts'],
    'lease_payment'       => ['icon' => 'bi-cash-coin', 'label' => 'Payment', 'url' => 'finance/invoices'],
    'cheque'              => ['icon' => 'bi-bank2', 'label' => 'Cheque', 'url' => 'finance'],
    'tenant'              => ['icon' => 'bi-person-badge', 'label' => 'Tenant', 'url' => 'tenants'],
    'maintenance_request' => ['icon' => 'bi-tools', 'label' => 'Maintenance', 'url' => 'helpdesk'],
    'facility'            => ['icon' => 'bi-building', 'label' => 'Property', 'url' => 'properties'],
];
$counts = ['critical' => 0, 'warning' => 0, 'info' => 0];
foreach ($aiFlags as $flag) {
    $sev = (string) ($flag['severity'] ?? 'warning');
    if (isset($counts[$sev])) {
        $counts[$sev]++;
    }
}
$panelId = 'aiIntel_' . substr(md5((string) count($aiFlags)), 0, 8);
?>
<div class="ai-intel-panel mb-3" id="<?= esc($panelId) ?>" role="region" aria-label="AI Intelligence Alerts">
  <div class="ai-intel-glow" aria-hidden="true"></div>
  <div class="ai-intel-inner">
    <div class="ai-intel-header">
      <div class="ai-intel-brand">
        <div class="ai-intel-orb" aria-hidden="true">
          <i class="bi bi-stars"></i>
        </div>
        <div>
          <div class="ai-intel-title">AI Intelligence</div>
          <div class="ai-intel-subtitle">Live risk radar · <?= count($aiFlags) ?> signal<?= count($aiFlags) === 1 ? '' : 's' ?></div>
        </div>
      </div>
      <div class="ai-intel-chips">
        <?php foreach ($counts as $sev => $n): ?>
          <?php if ($n > 0): ?>
            <span class="ai-intel-chip <?= esc($severityMeta[$sev]['class']) ?>">
              <i class="bi <?= esc($severityMeta[$sev]['icon']) ?>"></i>
              <?= (int) $n ?> <?= esc($severityMeta[$sev]['label']) ?>
            </span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ai-intel-track-wrap">
      <div class="ai-intel-track" data-ai-track>
        <?php foreach ($aiFlags as $i => $flag):
          $sev = (string) ($flag['severity'] ?? 'warning');
          $meta = $severityMeta[$sev] ?? $severityMeta['warning'];
          $module = (string) ($flag['module'] ?? '');
          $mod = $moduleMeta[$module] ?? ['icon' => 'bi-cpu', 'label' => ucfirst(str_replace('_', ' ', $module)), 'url' => ''];
          $refId = (int) ($flag['ref_id'] ?? 0);
          $href = '';
          if ($mod['url'] !== '' && $refId > 0) {
              $href = match ($module) {
                  'lease_contract'      => base_url('contracts/' . $refId),
                  'tenant'              => base_url('tenants/' . $refId),
                  'facility'            => fm_property_url($refId),
                  'maintenance_request' => base_url('maintenance/view/' . $refId),
                  default               => base_url($mod['url']),
              };
          } elseif ($mod['url'] !== '') {
              $href = base_url($mod['url']);
          }
        ?>
        <article class="ai-intel-card <?= esc($meta['class']) ?>" data-ai-card="<?= (int) $i ?>">
          <div class="ai-intel-card-accent" aria-hidden="true"></div>
          <div class="ai-intel-card-body">
            <div class="ai-intel-card-top">
              <span class="ai-intel-module">
                <i class="bi <?= esc($mod['icon']) ?>"></i>
                <?= esc($mod['label']) ?>
              </span>
              <span class="ai-intel-sev-badge <?= esc($meta['class']) ?>">
                <i class="bi <?= esc($meta['icon']) ?>"></i>
                <?= esc($meta['label']) ?>
              </span>
            </div>
            <h6 class="ai-intel-card-title"><?= esc($flag['title'] ?? '') ?></h6>
            <?php if (!empty($flag['message'])): ?>
              <p class="ai-intel-card-msg"><?= esc($flag['message']) ?></p>
            <?php endif; ?>
            <?php if ($href !== ''): ?>
              <a href="<?= esc($href) ?>" class="ai-intel-link">
                Investigate <i class="bi bi-arrow-up-right"></i>
              </a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php if (count($aiFlags) > 1): ?>
      <div class="ai-intel-controls">
        <button type="button" class="ai-intel-nav" data-ai-prev aria-label="Previous alert">
          <i class="bi bi-chevron-left"></i>
        </button>
        <div class="ai-intel-dots" data-ai-dots>
          <?php foreach ($aiFlags as $i => $_): ?>
            <button type="button" class="ai-intel-dot<?= $i === 0 ? ' active' : '' ?>" data-ai-dot="<?= (int) $i ?>" aria-label="Alert <?= (int) $i + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="ai-intel-nav" data-ai-next aria-label="Next alert">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if (count($aiFlags) > 1): ?>
<script>
(function () {
  var root = document.getElementById('<?= esc($panelId, 'js') ?>');
  if (!root) return;
  var track = root.querySelector('[data-ai-track]');
  var cards = track ? track.querySelectorAll('[data-ai-card]') : [];
  var dots = root.querySelectorAll('[data-ai-dot]');
  var idx = 0;
  var total = cards.length;
  function go(n) {
    idx = (n + total) % total;
    if (track) track.style.transform = 'translateX(-' + (idx * 100) + '%)';
    dots.forEach(function (d, i) { d.classList.toggle('active', i === idx); });
  }
  root.querySelector('[data-ai-prev]')?.addEventListener('click', function () { go(idx - 1); });
  root.querySelector('[data-ai-next]')?.addEventListener('click', function () { go(idx + 1); });
  dots.forEach(function (d) {
    d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-ai-dot'), 10)); });
  });
  setInterval(function () { go(idx + 1); }, 7000);
})();
</script>
<?php endif; ?>
<?php endif; ?>
