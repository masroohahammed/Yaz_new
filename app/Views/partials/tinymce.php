<?php
/**
 * TinyMCE for rich text fields. Add class `fm-tinymce` (or `fm-tinymce-rtl` for Arabic) on textareas.
 * Include in the view's `scripts` section: <?= $this->include('partials/tinymce') ?>
 */
$tinymceHeight = (int) ($tinymceHeight ?? 180);
?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js"></script>
<script>
(function () {
  if (typeof tinymce === 'undefined') return;

  const baseConfig = {
    menubar: false,
    statusbar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link autolink autoresize',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat',
    content_style: 'body { font-family: var(--font-sans, "DM Sans", system-ui, sans-serif); font-size: 14px; }',
    autoresize_bottom_margin: 12,
    min_height: <?= $tinymceHeight ?>,
    setup: function (editor) {
      editor.on('change input blur', function () { editor.save(); });
    }
  };

  tinymce.init(Object.assign({}, baseConfig, {
    selector: 'textarea.fm-tinymce:not(.fm-tinymce-rtl)',
    height: <?= $tinymceHeight ?>
  }));

  tinymce.init(Object.assign({}, baseConfig, {
    selector: 'textarea.fm-tinymce-rtl',
    height: <?= $tinymceHeight ?>,
    directionality: 'rtl',
    content_style: 'body { font-family: var(--font-sans, "DM Sans", system-ui, sans-serif); font-size: 14px; direction: rtl; }'
  }));

  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (typeof tinymce !== 'undefined') tinymce.triggerSave();
    });
  });

  document.querySelectorAll('.modal').forEach(function (modalEl) {
    modalEl.addEventListener('shown.bs.modal', function () {
      if (typeof tinymce === 'undefined') return;
      tinymce.editors.forEach(function (ed) {
        if (modalEl.contains(ed.getElement())) {
          ed.fire('ResizeEditor');
        }
      });
    });
  });
})();
</script>
