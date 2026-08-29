'use strict';

/* ── FM ERP Main JS ─────────────────────────────────────────────────────── */

// ── BOOTSTRAP TOOLTIPS + CONFIRM DIALOGS ─────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger: 'hover', delay: { show: 400, hide: 100 } });
  });

  // Confirm dialogs
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert:not(.alert-danger)').forEach(el => {
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 5000);
    el.style.transition = 'opacity .4s';
  });

  // Bootstrap form validation
  document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
      form.classList.add('was-validated');
    });
  });

  // Start polling for notifications
  if (document.getElementById('notifCount')) {
    pollNotifications();
    setInterval(pollNotifications, 60000); // every 60s
  }
});

// ── NOTIFICATIONS ──────────────────────────────────────────────────────────
function loadNotifications() {
  fetch(BASE_URL + 'ajax/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(d => {
      const list = document.getElementById('notifList');
      const badge = document.getElementById('notifCount');
      if (!list) return;
      if (d.notifications.length === 0) {
        list.innerHTML = '<li class="px-3 py-3 text-secondary small text-center"><i class="bi bi-bell-slash d-block mb-1 fs-5"></i>No new notifications</li>';
      } else {
        list.innerHTML = d.notifications.map(n =>
          `<li class="d-flex align-items-start gap-2 px-3 py-2 border-bottom border-white border-opacity-10">
            <i class="bi bi-${n.type === 'sla_breach' ? 'exclamation-triangle text-danger' : n.type === 'invoice' ? 'receipt text-success' : 'info-circle text-info'} mt-1"></i>
            <div><div class="text-white small fw-semibold">${escHtml(n.title)}</div><div class="text-secondary x-small">${timeAgo(n.created_at)}</div></div>
          </li>`
        ).join('');
      }
      if (badge) {
        if (d.count > 0) { badge.textContent = d.count; badge.style.display = ''; }
        else { badge.style.display = 'none'; }
      }
    }).catch(() => {});
}

function pollNotifications() {
  fetch(BASE_URL + 'ajax/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(d => {
      const badge = document.getElementById('notifCount');
      if (badge) {
        if (d.count > 0) { badge.textContent = d.count; badge.style.display = ''; }
        else { badge.style.display = 'none'; }
      }
    }).catch(() => {});
}

function markAllRead() {
  fetch(BASE_URL + 'notifications/markAllRead', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.content || '')
  }).then(() => loadNotifications()).catch(() => {});
}

// ── CHART HELPERS ──────────────────────────────────────────────────────────
function createLineChart(canvasId, labels, datasets, options = {}) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: datasets.map(ds => ({
      fill: true,
      tension: 0.4,
      borderWidth: 2.5,
      pointRadius: 3,
      pointBackgroundColor: ds.borderColor,
      backgroundColor: ds.backgroundColor || 'transparent',
      ...ds
    }))},
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: 'rgba(255,255,255,.75)', font: { size: 11 } } }, tooltip: { mode: 'index', intersect: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.08)' }, ticks: { color: 'rgba(255,255,255,.6)', font: { size: 10 } } },
        y: { grid: { color: 'rgba(255,255,255,.08)' }, ticks: { color: 'rgba(255,255,255,.6)', font: { size: 10 } } }
      },
      ...options
    }
  });
}

function createBarChart(canvasId, labels, datasets, options = {}) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: 'rgba(255,255,255,.75)', font: { size: 11 } } } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: 'rgba(255,255,255,.6)', font: { size: 10 } } },
        y: { grid: { color: 'rgba(255,255,255,.08)' }, ticks: { color: 'rgba(255,255,255,.6)', font: { size: 10 } } }
      },
      ...options
    }
  });
}

function createDoughnutChart(canvasId, labels, data, colors) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: 'transparent' }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: { legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,.75)', font: { size: 10 }, padding: 8 } } }
    }
  });
}

// ── LIVE FEED ──────────────────────────────────────────────────────────────
function loadLiveFeed() {
  const feed = document.getElementById('liveFeed');
  if (!feed) return;
  fetch(BASE_URL + 'ajax/workorders/live', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(d => {
      if (!d.data || d.data.length === 0) {
        feed.innerHTML = '<li class="px-3 py-3 text-secondary small text-center">No active work orders</li>';
        return;
      }
      const priorityColors = { critical: '#e74c3c', high: '#e67e22', medium: '#3498db', low: '#27ae60' };
      feed.innerHTML = d.data.map(w =>
        `<li class="d-flex align-items-start gap-2 px-3 py-2 border-bottom border-white border-opacity-10">
          <span style="width:8px;height:8px;border-radius:50%;background:${priorityColors[w.priority]||'#95a5a6'};margin-top:6px;flex-shrink:0"></span>
          <div class="flex-grow-1">
            <a href="${BASE_URL}workorders/view/${w.id}" class="text-white text-decoration-none fw-semibold x-small">${escHtml(w.wo_number)}</a>
            <div class="text-secondary x-small">${escHtml(w.title.substring(0,40))}${w.title.length>40?'...':''}</div>
          </div>
          <span class="fm-badge badge-status-${w.status} x-small">${w.status.replace('_',' ')}</span>
        </li>`
      ).join('');
    }).catch(() => {
      feed.innerHTML = '<li class="px-3 py-3 text-secondary small text-center">Unable to load feed</li>';
    });
}

// ── UTILITIES ──────────────────────────────────────────────────────────────
function escHtml(str) {
  const div = document.createElement('div');
  div.textContent = str || '';
  return div.innerHTML;
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return Math.floor(diff/60) + 'm ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}

function numberFormat(num, dec = 0) {
  return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
}
