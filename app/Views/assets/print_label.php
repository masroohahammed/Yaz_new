<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title ?? 'Asset Labels') ?></title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    @page { margin: 8mm; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 8px; }
    .label-grid { display: flex; flex-wrap: wrap; gap: 8px; }
    .asset-label {
      border: 1px solid #333; padding: 8px; box-sizing: border-box;
      page-break-inside: avoid; display: flex; flex-direction: column; align-items: center; text-align: center;
    }
    .label-small { width: 50mm; min-height: 25mm; font-size: 7pt; }
    .label-standard { width: 75mm; min-height: 50mm; font-size: 8pt; }
    .label-large { width: 100mm; min-height: 75mm; font-size: 9pt; }
    .label-logo { max-height: 18px; max-width: 60px; margin-bottom: 4px; }
    .label-title { font-weight: bold; font-size: 1.1em; line-height: 1.2; }
    .label-code { font-family: monospace; margin: 2px 0; }
    .label-loc { color: #555; font-size: .9em; }
    .label-qr { width: 70px; height: 70px; }
    .label-barcode { max-width: 95%; height: 28px; }
    .no-print { margin-bottom: 12px; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()">Print Labels</button>
  <a href="javascript:history.back()">Back</a>
</div>

<?php
$sizeClass = match ($labelSize ?? 'standard') {
    'small' => 'label-small',
    'large' => 'label-large',
    default => 'label-standard',
};
$logoB64 = function_exists('fm_logo_data_uri') ? fm_logo_data_uri() : '';
if (!empty($bulk)) {
  $items = $assets ?? [];
} else {
  $items = [[
    'id' => $asset['id'], 'name' => $asset['name'], 'asset_code' => $asset['asset_code'],
    'tag_number' => $asset['tag_number'] ?? '', 'barcode_value' => $asset['barcode_value'] ?? $asset['asset_code'],
    'location_in_facility' => $asset['location_in_facility'] ?? '', 'floor_room' => $asset['floor_room'] ?? '',
    'scan_url' => $scanUrl ?? '', 'qr_image_url' => $qrImageUrl ?? '',
  ]];
}
?>

<div class="label-grid">
<?php foreach ($items as $item):
  $a = $item;
  $qr  = $item['qr_image_url'] ?? ($qrImageUrl ?? '');
  $barcode = $a['barcode_value'] ?? $a['asset_code'];
?>
  <div class="asset-label <?= $sizeClass ?>">
    <?php if ($logoB64): ?><img src="<?= $logoB64 ?>" class="label-logo" alt=""><?php endif; ?>
    <div class="label-title"><?= esc($a['name']) ?></div>
    <div class="label-code"><?= esc($a['asset_code']) ?></div>
    <?php if (!empty($a['tag_number'])): ?><div class="small"><?= esc($a['tag_number']) ?></div><?php endif; ?>
    <img src="<?= esc($qr) ?>" class="label-qr" alt="QR">
    <svg class="label-barcode barcode-<?= (int)$a['id'] ?>"></svg>
    <div class="label-loc"><?= esc($a['location_in_facility'] ?? $a['floor_room'] ?? '') ?></div>
  </div>
  <script>JsBarcode(".barcode-<?= (int)$a['id'] ?>", <?= json_encode($barcode) ?>, {format:"CODE128", displayValue:true, fontSize:10, height:24, margin:0});</script>
<?php endforeach; ?>
</div>
</body>
</html>
