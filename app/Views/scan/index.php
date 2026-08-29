<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-qr-code-scan me-2"></i>QR Scanner</h1>
    <p class="text-muted small mb-0">Scan a property, unit, or asset QR code to open its details and actions.</p>
  </div>
</div>

<div class="fm-card">
  <div class="fm-card-body">
    <div id="qr-reader" class="mx-auto" style="max-width:420px"></div>
    <div id="qr-status" class="text-center small text-muted mt-3">Allow camera access, then point at a QR code.</div>
    <div id="qr-result" class="alert alert-success mt-3 d-none small mb-0"></div>
  </div>
</div>

<div class="fm-card mt-3">
  <div class="fm-card-body small">
    <h6 class="fw-semibold">Supported codes</h6>
    <ul class="mb-0">
      <li>Property QR — view details, inspections, maintenance</li>
      <li>Unit QR — view details, inspections, maintenance</li>
      <li>Asset QR — view details, inspections, maintenance</li>
    </ul>
  </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
  var statusEl = document.getElementById('qr-status');
  var resultEl = document.getElementById('qr-result');
  var reader = new Html5Qrcode('qr-reader');

  function showResult(text) {
    resultEl.textContent = 'Opening: ' + text;
    resultEl.classList.remove('d-none');
    window.location.href = text;
  }

  function onScanSuccess(decodedText) {
    if (!decodedText) return;
    var url = decodedText.trim();
    if (!/^https?:\/\//i.test(url)) {
      if (url.charAt(0) === '/') {
        url = window.location.origin + url;
      } else if (url.indexOf('scan/') === 0) {
        url = '<?= rtrim(base_url(), '/') ?>/' + url;
      }
    }
    if (url.indexOf('/scan/') === -1) {
      statusEl.textContent = 'Not a valid FM ERP scan URL.';
      return;
    }
    reader.stop().then(function () {
      showResult(url);
    }).catch(function () {
      showResult(url);
    });
  }

  Html5Qrcode.getCameras().then(function (cameras) {
    if (!cameras || !cameras.length) {
      statusEl.textContent = 'No camera found on this device.';
      return;
    }
    var camId = cameras[cameras.length - 1].id;
    reader.start(
      camId,
      { fps: 10, qrbox: { width: 250, height: 250 } },
      onScanSuccess,
      function () {}
    ).catch(function (err) {
      statusEl.textContent = 'Camera error: ' + (err || 'permission denied');
    });
  }).catch(function () {
    statusEl.textContent = 'Unable to access camera.';
  });
})();
</script>

<?= $this->endSection() ?>
