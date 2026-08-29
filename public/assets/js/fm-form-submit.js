/**
 * Form submit loader + double-submit prevention for .fm-submit-form
 */
(function () {
  'use strict';

  function showLoader(msg) {
    const el = document.getElementById('fmPageLoader');
    const txt = document.getElementById('fmPageLoaderText');
    if (!el) return;
    if (txt) txt.textContent = msg || 'Please wait…';
    el.classList.add('is-active');
    el.setAttribute('aria-hidden', 'false');
  }

  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.noSubmitLock === '1') return;
    if (!form.classList.contains('fm-submit-form') && !form.classList.contains('fm-submit-lock')) return;

    if (form.dataset.fmSubmitting === '1') {
      e.preventDefault();
      return;
    }

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    form.dataset.fmSubmitting = '1';
    const msg = form.dataset.loaderMsg || 'Submitting…';
    showLoader(msg);

    form.querySelectorAll('.fm-submit-btn, button[type="submit"]').forEach(function (btn) {
      if (btn.disabled) return;
      btn.disabled = true;
      if (!btn.dataset.fmOrigHtml) {
        btn.dataset.fmOrigHtml = btn.innerHTML;
      }
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + msg;
    });
  }, true);
})();
