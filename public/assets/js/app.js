
window.fmFetchJson = async function (url, options) {
    const opts = options || {};
    opts.headers = Object.assign({
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }, opts.headers || {});
    const res = await fetch(url, opts);
    const ct = res.headers.get('content-type') || '';
    if (!res.ok || !ct.includes('json')) {
        return { status: 'error', message: res.status === 403 ? 'Session expired or access denied. Refresh the page.' : 'Request failed.' };
    }
    return res.json();
};

/**
 * FM ERP — Application JavaScript
 * Requires Bootstrap 5 (bundled)
 */

'use strict';

// ================================================================
// Auto-dismiss alerts after 6 seconds
// ================================================================
document.querySelectorAll('.alert-dismissible').forEach(el => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
        bsAlert?.close();
    }, 6000);
});

// ================================================================
// Confirm-before-submit on delete / destructive forms
// ================================================================
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        const msg = el.dataset.confirm || 'Are you sure?';
        if (! confirm(msg)) e.preventDefault();
    });
});

// ================================================================
// Code field auto-uppercase
// ================================================================
document.querySelectorAll('input.text-uppercase').forEach(el => {
    el.addEventListener('input', () => { el.value = el.value.toUpperCase(); });
});

// ================================================================
// Inline status-badge update via AJAX (used on WO list)
// ================================================================
document.querySelectorAll('.status-change-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const { url, status, row } = btn.dataset;
        if (! url || ! status) return;

        try {
            const data = await fmFetchJson(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ status }),
            });
            if (data.status === 'ok') {
                const cell = document.querySelector(`[data-row="${row}"] .status-cell`);
                if (cell) cell.innerHTML = data.badge_html;
            } else {
                alert(data.message || 'Update failed.');
            }
        } catch (err) {
            console.error(err);
        }
    });
});

// ================================================================
// Sidebar active link highlight (handles SPA-style navigation)
// ================================================================
function highlightActiveNav() {
    const path = location.pathname;
    document.querySelectorAll('.sidebar-item').forEach(link => {
        const href = link.getAttribute('href');
        if (href && path.startsWith(href) && href !== '/') {
            link.classList.add('active');
        } else if (href === '/' && path === '/') {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}
highlightActiveNav();

// ================================================================
// Date range picker helper (requires Flatpickr if loaded)
// ================================================================
if (typeof flatpickr !== 'undefined') {
    flatpickr('.datepicker',      { dateFormat: 'Y-m-d' });
    flatpickr('.datetimepicker',  { dateFormat: 'Y-m-d H:i', enableTime: true, time_24hr: true });
}

// ================================================================
// Generic AJAX form (data-ajax-form attribute)
// Submits form, shows inline feedback, reloads on success
// ================================================================
document.querySelectorAll('[data-ajax-form]').forEach(form => {
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = form.querySelector('[type=submit]');
        const originalText = btn?.textContent;
        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

        try {
            const res  = await fetch(form.action, { method: form.method || 'POST', body: new FormData(form) });
            const data = await res.json();

            const fb = form.querySelector('.ajax-feedback') || document.createElement('div');
            fb.className = `ajax-feedback alert alert-${data.status === 'ok' ? 'success' : 'danger'} mt-2`;
            fb.textContent = data.message || (data.status === 'ok' ? 'Saved.' : 'Error.');
            if (! form.querySelector('.ajax-feedback')) form.appendChild(fb);

            if (data.status === 'ok' && data.redirect) {
                setTimeout(() => { location.href = data.redirect; }, 800);
            }
        } catch (err) {
            console.error(err);
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = originalText; }
        }
    });
});

// ================================================================
// Notification mark-read on dropdown item click
// ================================================================
document.addEventListener('click', e => {
    const link = e.target.closest('a[href*="/notifications/"][href*="/read"]');
    if (link) {
        const badge = document.querySelector('.badge.rounded-pill.bg-danger');
        if (badge) {
            const count = parseInt(badge.textContent) - 1;
            count > 0 ? badge.textContent = count : badge.remove();
        }
    }
});


document.getElementById('globalSearch')?.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = e.target.value.trim();
    if (!q) return;
    window.location.href = '/workorders?search=' + encodeURIComponent(q);
});
