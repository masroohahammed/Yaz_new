-- Contract types, template linkage, utility transfer, payment/cheque tracking
-- Safe to run multiple times (MySQL 8+)

CREATE TABLE IF NOT EXISTS `contract_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(40) NOT NULL,
  `name_en` varchar(120) NOT NULL,
  `name_ar` varchar(120) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contract_types_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `contract_types` (`slug`, `name_en`, `name_ar`, `is_system`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('parking', 'Parking Contract', 'عقد موقف', 1, 1, 1, NOW(), NOW()),
('residential', 'Residential Contract', 'عقد سكني', 1, 1, 2, NOW(), NOW()),
('commercial', 'Commercial Contract', 'عقد تجاري', 1, 1, 3, NOW(), NOW()),
('other', 'Other Contract', 'عقد آخر', 1, 1, 4, NOW(), NOW());

ALTER TABLE `contract_templates`
  ADD COLUMN IF NOT EXISTS `contract_type_id` int unsigned DEFAULT NULL AFTER `name`,
  ADD COLUMN IF NOT EXISTS `terms_en` text DEFAULT NULL AFTER `content_ar`,
  ADD COLUMN IF NOT EXISTS `terms_ar` text DEFAULT NULL AFTER `terms_en`;

ALTER TABLE `lease_contracts`
  ADD COLUMN IF NOT EXISTS `contract_type_id` int unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `utility_transfer_applicable` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `utility_transfer_details` text DEFAULT NULL;

ALTER TABLE `lease_payments`
  ADD COLUMN IF NOT EXISTS `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `original_due_date` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `postponed_from_date` date DEFAULT NULL;

ALTER TABLE `cheques`
  ADD COLUMN IF NOT EXISTS `payment_id` int unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `landlord_id` int unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `payable_to_type` varchar(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `payable_to_id` int unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `due_date` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `image_path` varchar(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `payment_status_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` int unsigned NOT NULL,
  `contract_id` int unsigned DEFAULT NULL,
  `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) NOT NULL,
  `amount` decimal(14,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_psh_payment` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `lease_payments`
  MODIFY COLUMN `status` enum(
    'pending','paid','partial','overdue','cancelled','postponed',
    'cheque_received','cheque_bounced','converted_to_cash'
  ) NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS `cheque_status_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cheque_id` int unsigned NOT NULL,
  `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_csh_cheque` (`cheque_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `contract_templates` (`name`, `contract_type_id`, `content_en`, `content_ar`, `is_active`, `created_at`, `updated_at`)
SELECT 'Default Parking Agreement', ct.id,
  '<p>Parking lease for unit {{unit_number}}. Plate {{plate_number}}. Rent {{rent_amount}} {{currency}}.</p>',
  '<p>عقد موقف للوحدة {{unit_number}}. لوحة {{plate_number}}.</p>', 1, NOW(), NOW()
FROM `contract_types` ct WHERE ct.slug = 'parking'
AND NOT EXISTS (SELECT 1 FROM `contract_templates` t WHERE t.contract_type_id = ct.id AND t.name = 'Default Parking Agreement');

INSERT IGNORE INTO `contract_templates` (`name`, `contract_type_id`, `content_en`, `content_ar`, `is_active`, `created_at`, `updated_at`)
SELECT 'Default Residential Lease', ct.id,
  '<p>Residential lease for {{tenant_name}} at {{property_name}}, unit {{unit_number}}.</p>',
  '<p>عقد سكني للمستأجر {{tenant_name}}.</p>', 1, NOW(), NOW()
FROM `contract_types` ct WHERE ct.slug = 'residential'
AND NOT EXISTS (SELECT 1 FROM `contract_templates` t WHERE t.contract_type_id = ct.id AND t.name = 'Default Residential Lease');

-- Align parking template body with legacy bilingual print (also in 2026-09-11-parking-template-sync.sql)
UPDATE `contract_templates` t
JOIN `contract_types` ct ON ct.id = t.contract_type_id
SET t.content_en = '<p><strong>Article One: Term and Rent</strong></p>
<p>Term: {{duration_en}}, from {{start_date_en}} to {{end_date_en}}.<br>
Monthly rent QAR {{rent_amount_fmt}} ({{rent_words_en}}), payable in advance via {{cheque_count}} cheques to {{collector_company}} {{collector_account}}.</p>
<p><strong>Article Two: Lessor Obligations</strong></p>
<p>Hand over Parking No. ({{parking_unit_no}}) for vehicle Reg. {{plate_number}}.</p>
<p><strong>Article Three: Lessee Obligations</strong></p>
<ol>
<li>Pay rent on due dates.</li>
<li>Not sublease the parking space.</li>
<li>No mechanical work; keep the space clean.</li>
<li>Use only for the specified vehicle unless approved in writing.</li>
<li>Return the space in the same condition.</li>
</ol>
<p><strong>Article Four: General Terms</strong></p>
<p>Late payment or breach terminates the Agreement; Lessor may remove the vehicle without legal proceedings. Governed by Qatari law; Qatari courts have jurisdiction. Executed in two original copies.</p>',
    t.content_ar = '<p><strong>البند الأول: المدة والأجرة</strong></p>
<p>مدة {{duration_ar}} من {{start_date_ar}} إلى {{end_date_ar}}.<br>
الأجرة الشهرية {{rent_amount_fmt}} ريال قطري، {{cheque_count}} شيكاً لحساب {{collector_company}} {{collector_account}}.</p>
<p><strong>البند الثاني: التزامات المالك</strong></p>
<p>تسليم الموقف ({{parking_unit_no}}) للمركبة ({{plate_number}}).</p>
<p><strong>البند الثالث: التزامات المستأجر</strong></p>
<ol>
<li>تسديد الأجرة في موعدها.</li>
<li>عدم تأجير الموقف من الباطن.</li>
<li>عدم أعمال ميكانيكية والمحافظة على النظافة.</li>
<li>تخصيص الموقف للمركبة المذكورة فقط.</li>
<li>إعادة الموقف بالحالة الأصلية.</li>
</ol>
<p><strong>البند الرابع: الشروط العامة</strong></p>
<p>التأخر أو المخالفة يلغي العقد تلقائياً ويحق للمالك إخلاء المركبة. يخضع لقوانين قطر وتختص المحاكم القطرية. أُبرم من نسختين أصليتين.</p>',
    t.updated_at = NOW()
WHERE ct.slug = 'parking' AND t.name = 'Default Parking Agreement';
