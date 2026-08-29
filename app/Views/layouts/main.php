<?php
/**
 * Main layout — loads fm helper to get system settings safely.
 * DO NOT use CI4-Shield's setting() here; use fm_setting() instead.
 */
helper('fm');
$_companyName = fm_setting('company_name', 'FM ERP');
$_primaryColor = fm_setting('primary_color', '#76002b');
$_unread = fm_unread_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-value" content="<?= csrf_hash() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'FM ERP') ?> — <?= esc($_companyName) ?></title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></noscript>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- App CSS (static — no PHP) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fm-extra.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/commandcenter.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/crimson-horizon.css') ?>">
    <!-- Dynamic colour vars (injected here so CSS file stays static) -->
    <style>
        :root {
            --font-sans: "DM Sans", "Urbanist", system-ui, -apple-system, sans-serif;
            --sidebar-width: 220px;
            --primary:       <?= esc($_primaryColor) ?>;
            --primary-color: <?= esc($_primaryColor) ?>;
            --secondary-color: <?= esc(fm_setting('secondary_color', '#c7ba9a')) ?>;
        }
    </style>
    <?= $this->renderSection('head') ?>
</head>
<body class="fm-layout" style="font-family: var(--font-sans);">

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay d-xl-none" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?= $this->include('layouts/_sidebar') ?>

    <!-- Main wrapper -->
    <div class="main-wrapper">
        <!-- Top navbar -->
        <header class="topbar cc-topbar d-flex align-items-center px-3 px-md-4">
            <!-- Hamburger (mobile / tablet) -->
            <button class="btn btn-sm me-3 d-xl-none" id="sidebarToggle" aria-label="Open menu">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="cc-search position-relative d-none d-md-block">
                <i class="bi bi-search"></i>
                <input type="search" class="form-control" placeholder="Search assets, work orders, tickets…" id="globalSearch" autocomplete="off">
            </div>

            <div class="topbar-actions d-flex align-items-center gap-1 ms-auto">
            <!-- Notifications bell -->
            <div class="dropdown me-2">
                <button class="btn btn-sm position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if ($_unread > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size:.6rem" id="notifBadge">
                            <?= min($_unread, 99) ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0"
                     style="width:320px;max-height:380px;overflow-y:auto">
                    <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <strong>Notifications</strong>
                        <a href="/notifications/markAllRead" class="small text-decoration-none">Mark all read</a>
                    </div>
                    <div id="notifList">
                        <div class="px-3 py-3 text-muted small text-center">Loading…</div>
                    </div>
                    <div class="text-center py-2 border-top">
                        <a href="/notifications" class="small text-decoration-none">View all</a>
                    </div>
                </div>
            </div>
            <a href="<?= base_url('profile') ?>" class="btn btn-sm ms-1" title="Profile"><span class="avatar-circle"><?= strtoupper(substr((string) session()->get('user_name'), 0, 1)) ?></span></a>
            </div>
        </header>

        <!-- Page content -->
        <main class="main-content container-fluid px-3 px-md-4 pb-4 pt-3">
            <?php if ($msg = session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= esc($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($msg = session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= esc($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errors = session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        <?php foreach ((array) $errors as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- App JS -->
    <script src="<?= base_url('assets/js/app.js') ?>?v=20260522" defer></script>
    <?= $this->renderSection('scripts') ?>

    <script>
    // ---- Sidebar toggle (mobile) ----
    const _toggle   = document.getElementById('sidebarToggle');
    const _sidebar  = document.getElementById('sidebar');
    const _overlay  = document.getElementById('sidebarOverlay');
    const _closeBtn = document.getElementById('sidebarClose');
    const _open  = () => { _sidebar?.classList.add('open');    _overlay?.classList.add('active'); };
    const _close = () => { _sidebar?.classList.remove('open'); _overlay?.classList.remove('active'); };
    _toggle?.addEventListener('click', _open);
    _overlay?.addEventListener('click', _close);
    _closeBtn?.addEventListener('click', _close);

    // ---- Lazy-load notification dropdown ----
    let _notifLoaded = false;
    document.querySelector('[aria-label="Notifications"]')
        ?.closest('.dropdown')
        ?.addEventListener('show.bs.dropdown', () => {
            if (_notifLoaded) return;
            _notifLoaded = true;
            fetch('<?= base_url('notifications/recent') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => {
                    if (!r.ok) return [];
                    const ct = r.headers.get('content-type') || '';
                    if (!ct.includes('json')) return [];
                    return r.json();
                })
                .then(data => {
                    const list = document.getElementById('notifList');
                    if (! list) return;
                    if (! data.length) {
                        list.innerHTML = '<div class="px-3 py-3 text-muted small text-center">No new notifications</div>';
                        return;
                    }
                    list.innerHTML = data.map(n => `
                        <a href="/notifications/${n.id}/read"
                           class="dropdown-item py-2 px-3 border-bottom ${n.is_read ? '' : 'fw-semibold'}">
                            <div class="small">${n.title ?? ''}</div>
                            <div class="text-muted" style="font-size:.72rem">${n.time_ago ?? ''}</div>
                        </a>`).join('');
                })
                .catch(() => {});
        });
    </script>
    <div id="fmPageLoader" class="fm-page-loader" aria-hidden="true" role="status">
        <div class="fm-page-loader-box">
            <div class="spinner-border" role="presentation"></div>
            <div class="fm-page-loader-text" id="fmPageLoaderText">Loading…</div>
        </div>
    </div>
</body>
</html>
