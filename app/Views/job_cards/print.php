<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card <?= esc($jc['jc_number']) ?></title>
    <style>
        /* ======================================================
           Job Card Print Stylesheet — A4 Portrait
           Works with: browser Print → Save as PDF  OR  DOMPDF
        ====================================================== */

        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            background: #fff;
            line-height: 1.4;
        }

        /* ---- Header ---- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid <?= esc($primaryColor ?? '#76002b') ?>;
            padding-bottom: 8pt;
            margin-bottom: 10pt;
        }
        .header-logo img {
            max-height: 55pt;
            max-width: 130pt;
            object-fit: contain;
        }
        .header-logo .logo-placeholder {
            width: 55pt; height: 55pt;
            background: <?= esc($primaryColor ?? '#76002b') ?>;
            display: flex; align-items: center; justify-content: center;
            border-radius: 4pt;
            color: #fff; font-size: 22pt; font-weight: bold;
        }
        .header-company h1 {
            font-size: 13pt; font-weight: bold;
            color: <?= esc($primaryColor ?? '#76002b') ?>;
        }
        .header-company p {
            font-size: 8.5pt; color: #555; margin-top: 2pt;
        }
        .header-title {
            text-align: right;
        }
        .header-title .doc-title {
            font-size: 16pt; font-weight: bold; text-transform: uppercase;
            color: <?= esc($primaryColor ?? '#76002b') ?>;
            letter-spacing: .5pt;
        }
        .header-title .doc-number {
            font-size: 11pt; font-weight: bold; margin-top: 3pt;
        }
        .header-title .doc-status {
            display: inline-block;
            margin-top: 4pt;
            padding: 2pt 8pt;
            border-radius: 10pt;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3pt;
        }
        .status-draft       { background: #e5e7eb; color: #374151; }
        .status-in_progress { background: #fce7f3; color: #9d174d; }
        .status-completed   { background: #d1fae5; color: #065f46; }
        .status-approved    { background: #dbeafe; color: #1e40af; }

        /* ---- Section header ---- */
        .section-header {
            background: <?= esc($primaryColor ?? '#76002b') ?>;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4pt;
            padding: 4pt 8pt;
            margin-top: 10pt;
            margin-bottom: 0;
        }

        /* ---- Info grid ---- */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid #d1d5db;
            border-top: none;
        }
        .info-grid.three-col { grid-template-columns: 1fr 1fr 1fr; }
        .info-cell {
            padding: 5pt 8pt;
            border-right: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
        }
        .info-cell:nth-child(2n), .three-col .info-cell:nth-child(3n) {
            border-right: none;
        }
        .info-cell .label {
            font-size: 7.5pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .3pt;
            margin-bottom: 2pt;
        }
        .info-cell .value {
            font-size: 10pt;
            font-weight: 600;
            color: #111;
        }
        .info-cell .value.small { font-size: 9pt; }

        /* Priority badge */
        .badge-critical { color: #991b1b; }
        .badge-high     { color: #9a3412; }
        .badge-medium   { color: #713f12; }
        .badge-low      { color: #14532d; }

        /* ---- Description box ---- */
        .description-box {
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 7pt 8pt;
            min-height: 36pt;
            font-size: 10pt;
            line-height: 1.5;
        }

        /* ---- Materials table ---- */
        .materials-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .materials-table th {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 4pt 7pt;
            font-size: 8.5pt;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
        }
        .materials-table td {
            border: 1px solid #d1d5db;
            padding: 4pt 7pt;
            font-size: 9.5pt;
        }
        .materials-table .text-right { text-align: right; }
        .materials-table .total-row td {
            background: #f3f4f6;
            font-weight: bold;
        }

        /* ---- Image boxes ---- */
        .image-row {
            display: flex;
            gap: 10pt;
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 7pt 8pt;
        }
        .image-box {
            flex: 1;
            text-align: center;
        }
        .image-box .img-label {
            font-size: 8pt; color: #6b7280; font-weight: bold;
            text-transform: uppercase; margin-bottom: 4pt;
        }
        .image-box img {
            max-width: 100%; max-height: 120pt;
            border: 1px solid #e5e7eb;
            border-radius: 3pt;
        }
        .image-box .img-placeholder {
            height: 100pt;
            border: 1px dashed #d1d5db;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af; font-size: 8pt; border-radius: 3pt;
        }

        /* ---- Notes boxes ---- */
        .notes-box {
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 7pt 8pt;
            min-height: 50pt;
            font-size: 9.5pt;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        /* ---- Signature table ---- */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
        }
        .signature-table td {
            width: 33.33%;
            padding: 0 8pt;
            vertical-align: top;
        }
        .sig-box {
            border: 1px solid #d1d5db;
            border-radius: 4pt;
            padding: 6pt 8pt;
        }
        .sig-box .sig-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: <?= esc($primaryColor ?? '#76002b') ?>;
            margin-bottom: 4pt;
            letter-spacing: .3pt;
        }
        .sig-box .sig-name {
            font-size: 9pt;
            font-weight: 600;
            margin-bottom: 2pt;
        }
        .sig-box .sig-date {
            font-size: 8pt; color: #6b7280;
        }
        .sig-line {
            margin-top: 22pt;
            border-top: 1px solid #9ca3af;
            padding-top: 3pt;
            font-size: 7.5pt;
            color: #9ca3af;
        }

        /* ---- Footer ---- */
        .page-footer {
            position: fixed;
            bottom: 8mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4pt;
        }

        /* ---- QR / barcode placeholder ---- */
        .qr-area {
            text-align: center;
            padding: 8pt;
        }
        .qr-area .qr-label { font-size: 7pt; color: #9ca3af; margin-top: 3pt; }

        /* ---- Utilities ---- */
        .mt-6 { margin-top: 6pt; }
        .text-muted { color: #6b7280; }
        .text-bold  { font-weight: bold; }
        .page-break { page-break-before: always; }

        /* Screen-only print button */
        @media screen {
            .print-btn-bar {
                position: fixed;
                top: 12px; right: 16px;
                z-index: 999;
                display: flex; gap: 8px;
            }
            .print-btn {
                padding: 8px 18px;
                background: <?= esc($primaryColor ?? '#76002b') ?>;
                color: #fff;
                border: none; border-radius: 6px;
                font-size: 13px; font-weight: 600;
                cursor: pointer;
            }
            .print-btn:hover { opacity: .9; }
            .close-btn {
                padding: 8px 18px;
                background: #6b7280;
                color: #fff;
                border: none; border-radius: 6px;
                font-size: 13px; cursor: pointer;
                text-decoration: none;
            }
        }
        @media print {
            .print-btn-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<!-- Screen print bar -->
<div class="print-btn-bar">
    <button class="print-btn" onclick="window.print()">🖨 Print / Save PDF</button>
    <a href="javascript:history.back()" class="close-btn">← Back</a>
</div>

<!-- ============================================================
     HEADER
============================================================ -->
<div class="header">
    <div class="header-logo">
        <?php if (! empty($companyLogo)): ?>
            <img src="<?= base_url($companyLogo) ?>" alt="<?= esc($companyName) ?>">
        <?php else: ?>
            <div class="logo-placeholder"><?= strtoupper(substr($companyName, 0, 1)) ?></div>
        <?php endif; ?>
    </div>
    <div class="header-company">
        <h1><?= esc($companyName) ?></h1>
        <?php if ($companyAddress): ?><p><?= esc($companyAddress) ?></p><?php endif; ?>
        <?php if ($companyPhone):   ?><p>Tel: <?= esc($companyPhone) ?></p><?php endif; ?>
        <?php if ($companyEmail):   ?><p><?= esc($companyEmail) ?></p><?php endif; ?>
    </div>
    <div class="header-title">
        <div class="doc-title">Job Card</div>
        <div class="doc-number"><?= esc($jc['jc_number']) ?></div>
        <div>
            <span class="doc-status status-<?= $jc['status'] ?>">
                <?= ucwords(str_replace('_', ' ', $jc['status'])) ?>
            </span>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 1: WORK ORDER REFERENCE
============================================================ -->
<div class="section-header">1 — Work Order Reference</div>
<div class="info-grid three-col">
    <div class="info-cell">
        <div class="label">Work Order #</div>
        <div class="value"><?= esc($jc['wo_number']) ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Facility</div>
        <div class="value small"><?= esc($jc['facility_name']) ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Priority</div>
        <div class="value badge-<?= $jc['priority'] ?>"><?= ucfirst($jc['priority']) ?> &#9679;</div>
    </div>
    <div class="info-cell" style="grid-column:span 3">
        <div class="label">Work Order Title</div>
        <div class="value small"><?= esc($jc['wo_title']) ?></div>
    </div>
</div>

<!-- ============================================================
     SECTION 2: JOB CARD DETAILS
============================================================ -->
<div class="section-header">2 — Job Card Details</div>
<div class="info-grid three-col">
    <div class="info-cell">
        <div class="label">Job Card #</div>
        <div class="value"><?= esc($jc['jc_number']) ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Issue Date</div>
        <div class="value"><?= date('d M Y', strtotime($jc['created_at'])) ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Scheduled Date</div>
        <div class="value"><?= $jc['scheduled_date'] ? date('d M Y', strtotime($jc['scheduled_date'])) : '—' ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Supervisor</div>
        <div class="value small"><?= esc($jc['supervisor_name'] ?? '—') ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Assigned Technician</div>
        <div class="value small"><?= esc($jc['technician_name'] ?? '—') ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Estimated Hours</div>
        <div class="value"><?= $jc['scheduled_hours'] ? number_format($jc['scheduled_hours'], 1) . ' hrs' : '—' ?></div>
    </div>
</div>

<!-- ============================================================
     SECTION 3: JOB DESCRIPTION
============================================================ -->
<div class="section-header">3 — Job Description</div>
<div class="description-box"><?= nl2br(esc($jc['description'])) ?></div>

<!-- ============================================================
     SECTION 4: MATERIALS / SPARE PARTS USED
============================================================ -->
<div class="section-header">4 — Materials &amp; Spare Parts Used</div>
<?php if (! empty($materials)): ?>
    <?php
    $totalMaterials = 0;
    foreach ($materials as $m) $totalMaterials += (float) $m['total_cost'];
    ?>
    <table class="materials-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:42%">Item / Description</th>
                <th style="width:13%" class="text-right">Qty</th>
                <th style="width:20%" class="text-right">Unit Cost</th>
                <th style="width:21%" class="text-right">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materials as $i => $m): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($m['item_name']) ?></td>
                    <td class="text-right"><?= number_format((float)$m['quantity'], 2) ?></td>
                    <td class="text-right"><?= number_format((float)$m['unit_cost'], 2) ?> <?= esc($currency) ?></td>
                    <td class="text-right"><?= number_format((float)$m['total_cost'], 2) ?> <?= esc($currency) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right text-bold">Total Materials Cost:</td>
                <td class="text-right"><?= number_format($totalMaterials, 2) ?> <?= esc($currency) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right text-bold">Labour Cost (<?= number_format((float)($jc['labor_hours'] ?? 0), 1) ?> hrs @ <?= number_format((float)$laborRate, 2) ?> <?= esc($currency) ?>/hr):</td>
                <td class="text-right"><?= number_format((float)($jc['labor_hours'] ?? 0) * (float)$laborRate, 2) ?> <?= esc($currency) ?></td>
            </tr>
            <?php $grand = $totalMaterials + (float)($jc['labor_hours'] ?? 0) * (float)$laborRate; ?>
            <tr class="total-row">
                <td colspan="4" class="text-right text-bold" style="font-size:10.5pt">Grand Total:</td>
                <td class="text-right text-bold" style="font-size:10.5pt"><?= number_format($grand, 2) ?> <?= esc($currency) ?></td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div class="description-box text-muted" style="min-height:24pt">No materials recorded.</div>
<?php endif; ?>

<!-- ============================================================
     SECTION 5: LABOUR
============================================================ -->
<div class="section-header">5 — Labour Record</div>
<div class="info-grid three-col">
    <div class="info-cell">
        <div class="label">Work Started</div>
        <div class="value"><?= $jc['started_at'] ?? $jc['wo_started_at'] ?? '—' ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Work Completed</div>
        <div class="value"><?= $jc['completed_at'] ? date('d M Y H:i', strtotime($jc['completed_at'])) : '—' ?></div>
    </div>
    <div class="info-cell">
        <div class="label">Actual Hours</div>
        <div class="value"><?= $jc['labor_hours'] ? number_format($jc['labor_hours'], 1) . ' hrs' : '—' ?></div>
    </div>
</div>

<!-- ============================================================
     SECTION 6: BEFORE / AFTER IMAGES
============================================================ -->
<div class="section-header">6 — Photographic Evidence</div>
<div class="image-row">
    <div class="image-box">
        <div class="img-label">Before Work</div>
        <?php if ($jc['before_image']): ?>
            <img src="<?= base_url($jc['before_image']) ?>" alt="Before">
        <?php else: ?>
            <div class="img-placeholder">No image attached</div>
        <?php endif; ?>
    </div>
    <div class="image-box">
        <div class="img-label">After Work</div>
        <?php if ($jc['after_image']): ?>
            <img src="<?= base_url($jc['after_image']) ?>" alt="After">
        <?php else: ?>
            <div class="img-placeholder">No image attached</div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     SECTION 7: COMPLETION NOTES
============================================================ -->
<div class="section-header">7 — Technician Notes &amp; Findings</div>
<div class="notes-box"><?= $jc['technician_notes'] ? nl2br(esc($jc['technician_notes'])) : '<span class="text-muted">No notes.</span>' ?></div>

<div class="section-header">8 — Completion Report</div>
<div class="notes-box"><?= $jc['completion_notes'] ? nl2br(esc($jc['completion_notes'])) : '<span class="text-muted">No completion notes.</span>' ?></div>

<?php if ($jc['qa_notes']): ?>
<div class="section-header">QC / Inspection Notes</div>
<div class="notes-box"><?= nl2br(esc($jc['qa_notes'])) ?></div>
<?php endif; ?>

<!-- ============================================================
     SECTION 9: SIGNATURE BLOCK
============================================================ -->
<table class="signature-table mt-6">
    <tr>
        <!-- Technician -->
        <td style="padding-left:0">
            <div class="sig-box">
                <div class="sig-label">Technician</div>
                <div class="sig-name"><?= esc($jc['technician_name'] ?? '___________________________') ?></div>
                <div class="sig-date">Employee ID: <?= esc($jc['assigned_to'] ?? '—') ?></div>
                <div class="sig-line">Signature &nbsp;&nbsp;&nbsp; Date: ___________</div>
            </div>
        </td>
        <!-- Supervisor -->
        <td>
            <div class="sig-box">
                <div class="sig-label">Supervisor Approval</div>
                <?php if ($jc['approved_by_name']): ?>
                    <div class="sig-name"><?= esc($jc['approved_by_name']) ?></div>
                    <div class="sig-date">Approved: <?= $jc['approved_at'] ? date('d M Y', strtotime($jc['approved_at'])) : '—' ?></div>
                <?php else: ?>
                    <div class="sig-name">___________________________</div>
                    <div class="sig-date">Pending approval</div>
                <?php endif; ?>
                <div class="sig-line">Signature &nbsp;&nbsp;&nbsp; Date: ___________</div>
            </div>
        </td>
        <!-- Client / FM -->
        <td style="padding-right:0">
            <div class="sig-box">
                <div class="sig-label">Client / Facility Manager</div>
                <div class="sig-name">___________________________</div>
                <div class="sig-date">Satisfactory: ☐ Yes &nbsp; ☐ No</div>
                <div class="sig-line">Signature &nbsp;&nbsp;&nbsp; Date: ___________</div>
            </div>
        </td>
    </tr>
</table>

<!-- ============================================================
     PAGE FOOTER
============================================================ -->
<div class="page-footer">
    <?= esc($companyName) ?> &nbsp;|&nbsp; Job Card <?= esc($jc['jc_number']) ?>
    &nbsp;|&nbsp; Work Order <?= esc($jc['wo_number']) ?>
    &nbsp;|&nbsp; Generated: <?= date('d M Y H:i') ?>
    &nbsp;|&nbsp; CONFIDENTIAL
</div>

<script>
// Auto-trigger print dialog when opened from the print action
<?php if ($autoPrint ?? false): ?>
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
<?php endif; ?>
</script>

</body>
</html>
