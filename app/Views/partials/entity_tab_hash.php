<script>
(function () {
  var tabLinks = document.querySelectorAll('.fm-entity-tabs a[data-bs-toggle="tab"]');
  function showTabFromHash() {
    var hash = location.hash;
    if (!hash) return;
    var link = document.querySelector('.fm-entity-tabs a[href="' + hash + '"]');
    if (link && window.bootstrap && bootstrap.Tab) {
      bootstrap.Tab.getOrCreateInstance(link).show();
    }
  }
  tabLinks.forEach(function (a) {
    a.addEventListener('shown.bs.tab', function () {
      var href = a.getAttribute('href');
      if (href && location.hash !== href) {
        history.replaceState(null, '', href);
      }
    });
  });
  showTabFromHash();
  window.addEventListener('hashchange', showTabFromHash);
})();
</script>
