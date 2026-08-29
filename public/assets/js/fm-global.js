/**
 * FM ERP — Global JavaScript
 * Include in layouts/main.php BEFORE page scripts:
 *   <script>window.BASE_URL = '<?= base_url() ?>';</script>
 *   <script src="<?= base_url('assets/js/fm-global.js') ?>"></script>
 */
'use strict';

window.FM = (function () {

  // ── CSRF helpers ─────────────────────────────────────────
  function csrfName()  { return document.querySelector('meta[name="csrf-token-name"]')?.content  || ''; }
  function csrfToken() { return document.querySelector('meta[name="csrf-token-value"]')?.content || ''; }
  function refreshCsrf(data) {
    if (!data) return;
    const n = csrfName();
    if (n && data[n]) document.querySelector('meta[name="csrf-token-value"]').content = data[n];
  }

  // ── Core POST (JSON) ──────────────────────────────────────
  async function post(url, data = {}) {
    const payload = { ...data, [csrfName()]: csrfToken() };
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
      });
      const json = await r.json().catch(() => ({ status: false, message: 'Server error' }));
      refreshCsrf(json);
      return json;
    } catch (e) {
      console.error('FM.post error:', e);
      return { status: false, message: 'Network error. Please check your connection.' };
    }
  }

  // ── FormData POST (file uploads) ──────────────────────────
  async function postForm(url, formData) {
    formData.append(csrfName(), csrfToken());
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
      });
      const json = await r.json().catch(() => ({ status: false, message: 'Server error' }));
      refreshCsrf(json);
      return json;
    } catch (e) {
      return { status: false, message: 'Upload failed.' };
    }
  }

  // ── Toast notification ────────────────────────────────────
  let _toastEl = null;
  function toast(message, type = 'success', duration = 4000) {
    const palette = {
      success: { bg: '#dcfce7', color: '#166534', border: '#4ade80' },
      error:   { bg: '#fee2e2', color: '#991b1b', border: '#f87171' },
      warning: { bg: '#fef9c3', color: '#854d0e', border: '#fcd34d' },
      info:    { bg: '#dbeafe', color: '#1e40af', border: '#60a5fa' },
    };
    const pal = palette[type] || palette.info;
    if (_toastEl) _toastEl.remove();
    _toastEl = document.createElement('div');
    Object.assign(_toastEl.style, {
      position: 'fixed', bottom: '20px', right: '20px', zIndex: '9999',
      background: pal.bg, color: pal.color,
      borderLeft: '4px solid ' + pal.border,
      padding: '12px 18px', borderRadius: '10px',
      fontSize: '.85rem', fontWeight: '600', maxWidth: '360px',
      boxShadow: '0 4px 16px rgba(0,0,0,.15)',
      animation: 'fmToastIn .3s ease', lineHeight: '1.4',
    });
    _toastEl.textContent = message;
    document.body.appendChild(_toastEl);
    setTimeout(() => { if (_toastEl) { _toastEl.style.opacity = '0'; _toastEl.style.transition = 'opacity .3s'; setTimeout(() => { if (_toastEl) _toastEl.remove(); _toastEl = null; }, 300); } }, duration);
  }

  // ── Work Order status update ──────────────────────────────
  function updateWoStatus(woId, status, completionNotes, actualCost) {
    return post(window.BASE_URL + 'workorders/status', {
      id: woId, status, completion_notes: completionNotes || '', actual_cost: actualCost || '',
    }).then(data => {
      if (data.status) {
        toast('Status updated: ' + status.replace(/_/g, ' '), 'success');
        setTimeout(() => location.reload(), 900);
      } else {
        toast(data.message || 'Update failed', 'error');
      }
      return data;
    });
  }

  // ── WO Chat ───────────────────────────────────────────────
  function initWoChat(woId, currentUserId, primaryColor) {
    const msgArea = document.getElementById('chatMessages');
    const input   = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSend');
    if (!msgArea || !input || !sendBtn) return;

    primaryColor = primaryColor || '#76002b';
    let lastId = 0;
    // Find highest existing msg id
    msgArea.querySelectorAll('[data-msg-id]').forEach(el => {
      lastId = Math.max(lastId, parseInt(el.dataset.msgId) || 0);
    });

    function scrollBottom() { msgArea.scrollTop = msgArea.scrollHeight; }
    scrollBottom();

    function buildBubble(msg) {
      const isMe = parseInt(msg.user_id) === parseInt(currentUserId);
      const wrap = document.createElement('div');
      wrap.className = 'd-flex ' + (isMe ? 'justify-content-end' : '') + ' gap-2';
      wrap.dataset.msgId = msg.id;
      const avatar = `<div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,${primaryColor},#c7ba9a);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0">${(msg.sender_name||'?')[0].toUpperCase()}</div>`;
      const text = (msg.message||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
      wrap.innerHTML = (!isMe ? avatar : '') + `
        <div style="max-width:72%">
          ${!isMe ? `<div style="font-size:.68rem;font-weight:700;color:#6b7a8d;margin-bottom:2px">${(msg.sender_name||'').replace(/</g,'&lt;')}</div>` : ''}
          <div style="padding:8px 12px;border-radius:${isMe?'16px 16px 4px 16px':'16px 16px 16px 4px'};font-size:.83rem;line-height:1.5;${isMe?'background:'+primaryColor+';color:#fff':'background:#fff;border:1px solid #e5e7eb'}">${text}</div>
          <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;${isMe?'text-align:right':''}">${msg.created_at||''}</div>
        </div>
      ` + (isMe ? avatar : '');
      return wrap;
    }

    // Poll every 8s
    const pollInterval = setInterval(() => {
      fetch(`${window.BASE_URL}ajax/wo-chat/${woId}?after=${lastId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
        if (data.messages?.length) {
          data.messages.forEach(msg => {
            if (!msgArea.querySelector(`[data-msg-id="${msg.id}"]`)) {
              msgArea.appendChild(buildBubble(msg));
              lastId = Math.max(lastId, msg.id);
            }
          });
          scrollBottom();
        }
      })
      .catch(() => {});
    }, 8000);

    function sendMsg() {
      const text = input.value.trim();
      if (!text) return;
      sendBtn.disabled = true;
      input.value = '';
      post(`${window.BASE_URL}ajax/wo-chat/${woId}`, { message: text })
        .then(data => {
          if (data.status && data.msg) {
            msgArea.appendChild(buildBubble(data.msg));
            lastId = Math.max(lastId, data.msg.id);
            scrollBottom();
          } else {
            toast(data.message || 'Failed to send', 'error');
            input.value = text;
          }
        })
        .catch(() => { toast('Network error', 'error'); input.value = text; })
        .finally(() => { sendBtn.disabled = false; input.focus(); });
    }

    sendBtn.addEventListener('click', sendMsg);
    input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } });
    return { stop: () => clearInterval(pollInterval) };
  }

  // ── Notification badge refresh ────────────────────────────
  function refreshNotifBadge() {
    fetch(`${window.BASE_URL}ajax/notifications`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(d => {
        const b = document.querySelector('.notif-badge');
        if (!b) return;
        if (d.count > 0) { b.textContent = d.count > 99 ? '99+' : d.count; b.style.display = 'inline'; }
        else b.style.display = 'none';
      })
      .catch(() => {});
  }

  // ── Inventory price lookup ────────────────────────────────
  function lookupInventoryPrice(itemId, unitCostEl, totalEl, qtyEl) {
    if (!itemId) return;
    fetch(`${window.BASE_URL}ajax/inventory-price/${itemId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(d => {
        if (d.unit_cost !== undefined) {
          if (unitCostEl) unitCostEl.value = parseFloat(d.unit_cost).toFixed(2);
          if (totalEl && qtyEl) totalEl.value = (parseFloat(d.unit_cost) * (parseFloat(qtyEl.value) || 1)).toFixed(2);
        }
      })
      .catch(() => {});
  }

  // ── Pagination helper ─────────────────────────────────────
  function buildPagination(container, currentPage, totalPages, baseUrl) {
    if (!container || totalPages <= 1) return;
    let html = '<div class="fm-pagination">';
    html += currentPage > 1
      ? `<a href="${baseUrl}page=${currentPage-1}">‹</a>`
      : '<span class="disabled">‹</span>';

    const range = [];
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentPage-2 && i <= currentPage+2)) range.push(i);
      else if (range[range.length-1] !== '…') range.push('…');
    }
    range.forEach(p => {
      if (p === '…') html += '<span class="dots">…</span>';
      else if (p === currentPage) html += `<span class="active">${p}</span>`;
      else html += `<a href="${baseUrl}page=${p}">${p}</a>`;
    });

    html += currentPage < totalPages
      ? `<a href="${baseUrl}page=${currentPage+1}">›</a>`
      : '<span class="disabled">›</span>';
    html += '</div>';
    container.innerHTML = html;
  }

  // ── Confirm delete ────────────────────────────────────────
  function confirmDelete(form, msg) {
    if (confirm(msg || 'Are you sure you want to delete this? This cannot be undone.')) {
      form.submit();
    }
    return false;
  }

  // ── Auto-calculate VAT ────────────────────────────────────
  function setupVatCalc(subtotalId, vatDisplayId, totalDisplayId, vatRate, vatEnabled) {
    const el = document.getElementById(subtotalId);
    if (!el) return;
    function calc() {
      const s = parseFloat(el.value) || 0;
      const v = vatEnabled ? Math.round(s * vatRate / 100 * 100) / 100 : 0;
      if (document.getElementById(vatDisplayId)) document.getElementById(vatDisplayId).value = v.toFixed(2);
      if (document.getElementById(totalDisplayId)) document.getElementById(totalDisplayId).value = (s + v).toFixed(2);
    }
    el.addEventListener('input', calc);
    calc();
  }

  // ── Image preview ─────────────────────────────────────────
  function previewImage(inputEl, imgEl) {
    if (!inputEl || !imgEl) return;
    inputEl.addEventListener('change', () => {
      if (inputEl.files?.[0]) {
        const r = new FileReader();
        r.onload = e => { imgEl.src = e.target.result; imgEl.style.display = 'block'; };
        r.readAsDataURL(inputEl.files[0]);
      }
    });
  }

  // ── Dark mode toggle (stored in localStorage via parent window) ──
  function toggleDarkMode() {
    const body = document.body;
    const isDark = body.classList.toggle('fm-dark');
    localStorage.setItem('fm-dark', isDark ? '1' : '0');
  }
  // Apply on load
  if (localStorage.getItem('fm-dark') === '1') document.body.classList.add('fm-dark');

  // ── DOM Ready init ────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    // Inject toast keyframe CSS once
    if (!document.getElementById('fm-toast-css')) {
      const s = document.createElement('style');
      s.id = 'fm-toast-css';
      s.textContent = '@keyframes fmToastIn{from{transform:translateX(110%);opacity:0}to{transform:none;opacity:1}}';
      document.head.appendChild(s);
    }

    // Auto-dismiss Bootstrap alerts
    document.querySelectorAll('.alert.fade.show').forEach(el => {
      setTimeout(() => { try { new bootstrap.Alert(el).close(); } catch(e) {} }, 5000);
    });

    // Confirm delete buttons
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
      });
    });

    // All forms with data-confirm-submit
    document.querySelectorAll('form[data-confirm-submit]').forEach(form => {
      form.addEventListener('submit', e => {
        if (!confirm(form.dataset.confirmSubmit || 'Are you sure?')) e.preventDefault();
      });
    });

    // Notification badge
    refreshNotifBadge();
    setInterval(refreshNotifBadge, 60000);

    // Auto-uppercase item codes
    document.querySelectorAll('[data-uppercase]').forEach(el => {
      el.addEventListener('input', () => { const p = el.selectionStart; el.value = el.value.toUpperCase(); el.setSelectionRange(p,p); });
    });
  });

  // Public API
  return {
    post,
    postForm,
    toast,
    updateWoStatus,
    initWoChat,
    refreshNotifBadge,
    lookupInventoryPrice,
    buildPagination,
    confirmDelete,
    setupVatCalc,
    previewImage,
    toggleDarkMode,
    csrfName,
    csrfToken,
  };
})();
