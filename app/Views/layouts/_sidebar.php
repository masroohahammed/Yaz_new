<?php
/**
 * Workspace-aware sidebar — Crimson Horizon / CommandCenter shell.
 * Menu source: Config\PmMenu + Config\FmMenu via WorkspaceService.
 */

helper('fm');

$role            = session()->get('user_role') ?? '';
$workspace       = session()->get('workspace') ?? 'fm';
$workspaceSvc    = new \App\Services\WorkspaceService(\Config\Database::connect());
$menu            = $workspaceSvc->buildMenu((string) $role);

// Admin system tools appended for super_admin
if ($role === 'super_admin') {
    $menu[] = ['type' => 'heading', 'label' => 'System'];
    $menu[] = ['key' => 'companies', 'label' => 'Companies', 'icon' => 'bi-building-gear', 'url' => 'companies'];
    $menu[] = ['key' => 'users', 'label' => 'Users', 'icon' => 'bi-people-fill', 'url' => 'users'];
    $menu[] = ['key' => 'settings_roles', 'label' => 'Roles & Permissions', 'icon' => 'bi-shield-lock', 'url' => 'settings/roles'];
    $menu[] = ['key' => 'settings_workflow', 'label' => 'Workflow Config', 'icon' => 'bi-diagram-3', 'url' => 'settings/workflow'];
    $menu[] = ['key' => 'settings_login_history', 'label' => 'Login History', 'icon' => 'bi-door-open', 'url' => 'settings/login-history'];
    $menu[] = ['key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear-fill', 'url' => 'settings'];
}

foreach ($menu as &$item) {
    if (($item['type'] ?? '') === 'heading') {
        continue;
    }
    if (! empty($item['url']) && ! str_starts_with((string) $item['url'], 'http')) {
        $item['url'] = base_url(ltrim((string) $item['url'], '/'));
    }
}
unset($item);

$currentPath = service('uri')->getPath();
$cname       = fm_setting('company_name', 'FM ERP');
$clogoUrl    = fm_logo_url(fm_setting('company_logo', ''));
$wsLabel     = match ($workspace) {
    'pm'   => 'Property Management',
    'fm'   => 'Facility Management',
    'both' => 'Admin',
    default => ucwords(str_replace('_', ' ', (string) $workspace)),
};
?>
<aside id="sidebar" class="sidebar cc-sidebar d-flex flex-column ch-sidebar">

<div class="sidebar-brand d-flex align-items-center px-3 py-3">
        <?php if ($clogoUrl): ?>
            <img src="<?= esc($clogoUrl) ?>" alt="<?= esc($cname) ?>" class="sidebar-logo me-2">
        <?php else: ?>
            <span class="sidebar-logo-placeholder me-2"><i class="bi bi-buildings"></i></span>
        <?php endif; ?>
        <div class="flex-grow-1 overflow-hidden">
            <span class="sidebar-brand-text fw-semibold text-truncate d-block"><?= esc($cname) ?></span>
            <span class="sidebar-workspace-badge"><?= esc($wsLabel) ?></span>
        </div>
        <button class="btn btn-sm ms-auto d-xl-none text-secondary" id="sidebarClose" aria-label="Close sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-nav flex-grow-1 overflow-y-auto px-2 py-1" aria-label="Main navigation">
        <?php foreach ($menu as $item): ?>
            <?php if (($item['type'] ?? '') === 'heading'): ?>
                <div class="sidebar-section-label"><?= esc($item['label']) ?></div>
            <?php else: ?>
            <?php
                $url      = $item['url'] ?? base_url('/');
                $urlPath  = parse_url($url, PHP_URL_PATH) ?: '/';
                $isActive = ($active ?? '') === ($item['key'] ?? '')
                    || ($url !== base_url('/') && str_starts_with('/' . $currentPath, $urlPath));
            ?>
            <a href="<?= esc($url) ?>"
               class="sidebar-item d-flex align-items-center gap-2 px-3 py-2 rounded text-decoration-none mb-1<?= $isActive ? ' active' : '' ?>"
               style="font-size:0.8rem">
                <i class="bi <?= esc($item['icon'] ?? 'bi-circle') ?> fs-5 flex-shrink-0"></i>
                <span class="sidebar-label flex-grow-1"><?= esc($item['label']) ?></span>
                <?php if (($item['badge'] ?? '') === 'view'): ?>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size:.6rem">View</span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer px-3 py-3 border-top">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar-circle flex-shrink-0"><?= strtoupper(substr((string) session()->get('user_name'), 0, 1)) ?></span>
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-truncate small fw-medium text-dark"><?= esc((string) session()->get('user_name')) ?></div>
                <div class="text-muted text-truncate" style="font-size:.72rem"><?= esc(ucwords(str_replace('_', ' ', $role))) ?></div>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="btn btn-sm text-secondary" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</aside>
