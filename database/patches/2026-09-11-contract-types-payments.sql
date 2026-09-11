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
