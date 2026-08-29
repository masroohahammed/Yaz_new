-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 28, 2026 at 10:21 PM
-- Server version: 10.11.18-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pfmsalyazwa_pfms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `ip_address`, `created_at`) VALUES
(0, 1, 'update', 'companies', 1, 'Super Admin — (super_admin) — POST /public/settings/companies/update/1', '212.70.114.12', '2026-08-08 14:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `ai_flags`
--

CREATE TABLE `ai_flags` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL,
  `flag_type` varchar(80) NOT NULL,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'warning',
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `workspace` enum('pm','fm','both') NOT NULL DEFAULT 'both',
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_flags`
--

INSERT INTO `ai_flags` (`id`, `company_id`, `module`, `ref_id`, `flag_type`, `severity`, `title`, `message`, `workspace`, `is_resolved`, `resolved_at`, `created_at`) VALUES
(1, NULL, 'lease_contract', 9116, 'expiring_contract', 'warning', 'Contract LC-3202 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(2, NULL, 'lease_contract', 9117, 'expiring_contract', 'critical', 'Contract LC-3203 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(3, NULL, 'lease_contract', 9118, 'expiring_contract', 'warning', 'Contract LC-3204 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(4, NULL, 'lease_contract', 9130, 'expiring_contract', 'warning', 'Contract LC-3304 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(5, NULL, 'lease_contract', 9139, 'expiring_contract', 'warning', 'Contract LC-3501 expires in 52 days', 'Lease ends 2026-10-14', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(6, NULL, 'lease_contract', 9144, 'expiring_contract', 'critical', 'Contract LC-3605 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(7, NULL, 'lease_contract', 9146, 'expiring_contract', 'warning', 'Contract LC-3607 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(8, NULL, 'lease_contract', 9153, 'expiring_contract', 'warning', 'Contract LC-3614 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(9, NULL, 'lease_contract', 9164, 'expiring_contract', 'warning', 'Contract LC-1110 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(10, NULL, 'lease_contract', 9167, 'expiring_contract', 'warning', 'Contract LC-1113 expires in 52 days', 'Lease ends 2026-10-14', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(11, NULL, 'lease_contract', 9168, 'expiring_contract', 'warning', 'Contract LC-1114 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(12, NULL, 'lease_contract', 9178, 'expiring_contract', 'critical', 'Contract LC-1124 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(13, NULL, 'lease_contract', 9180, 'expiring_contract', 'critical', 'Contract LC-1201 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(14, NULL, 'lease_contract', 9192, 'expiring_contract', 'warning', 'Contract LC-1213 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(15, NULL, 'lease_contract', 9194, 'expiring_contract', 'warning', 'Contract LC-1215 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(16, NULL, 'lease_contract', 9196, 'expiring_contract', 'warning', 'Contract LC-1217 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(17, NULL, 'lease_contract', 9202, 'expiring_contract', 'critical', 'Contract LC-1225 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(18, NULL, 'lease_contract', 9213, 'expiring_contract', 'warning', 'Contract LC-1501 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(19, NULL, 'lease_contract', 9214, 'expiring_contract', 'warning', 'Contract LC-1502 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(20, NULL, 'lease_contract', 9215, 'expiring_contract', 'warning', 'Contract LC-1503 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(21, NULL, 'lease_contract', 9216, 'expiring_contract', 'critical', 'Contract LC-1504 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(22, NULL, 'lease_contract', 9220, 'expiring_contract', 'warning', 'Contract LC-1508 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(23, NULL, 'lease_contract', 9227, 'expiring_contract', 'warning', 'Contract LC-5201 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(24, NULL, 'lease_contract', 9232, 'expiring_contract', 'warning', 'Contract LC-5305 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(25, NULL, 'lease_contract', 9235, 'expiring_contract', 'critical', 'Contract LC-5308 expires in 8 days', 'Lease ends 2026-08-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(26, NULL, 'lease_contract', 9275, 'expiring_contract', 'critical', 'Contract LC-AUTO-9275 expires in 22 days', 'Lease ends 2026-09-14', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(27, NULL, 'lease_contract', 9278, 'expiring_contract', 'warning', 'Contract LC-3121 expires in 57 days', 'Lease ends 2026-10-19', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(28, NULL, 'lease_contract', 9287, 'expiring_contract', 'critical', 'Contract LC-3130 expires in 7 days', 'Lease ends 2026-08-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(29, NULL, 'lease_contract', 9288, 'expiring_contract', 'warning', 'Contract LC-3131 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(30, NULL, 'lease_contract', 9289, 'expiring_contract', 'warning', 'Contract LC-3132 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(31, NULL, 'lease_contract', 9290, 'expiring_contract', 'warning', 'Contract LC-3133 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(32, NULL, 'lease_contract', 9292, 'expiring_contract', 'critical', 'Contract LC-3135 expires in 7 days', 'Lease ends 2026-08-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(33, NULL, 'lease_contract', 9309, 'expiring_contract', 'warning', 'Contract LC-3155 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(34, NULL, 'lease_contract', 9310, 'expiring_contract', 'warning', 'Contract LC-3156 expires in 69 days', 'Lease ends 2026-10-31', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(35, NULL, 'lease_contract', 9313, 'expiring_contract', 'warning', 'Contract LC-3159 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(36, NULL, 'lease_contract', 9314, 'expiring_contract', 'warning', 'Contract LC-3160 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(37, NULL, 'lease_contract', 9315, 'expiring_contract', 'warning', 'Contract LC-3161 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(38, NULL, 'lease_contract', 9316, 'expiring_contract', 'warning', 'Contract LC-3162 expires in 38 days', 'Lease ends 2026-09-30', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(39, NULL, 'lease_contract', 9317, 'expiring_contract', 'critical', 'Contract LC-3163 expires in 16 days', 'Lease ends 2026-09-08', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(40, NULL, 'lease_contract', 9318, 'expiring_contract', 'critical', 'Contract LC-3164 expires in 16 days', 'Lease ends 2026-09-08', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(41, NULL, 'lease_contract', 9319, 'expiring_contract', 'critical', 'Contract LC-3165 expires in 16 days', 'Lease ends 2026-09-08', 'pm', 0, NULL, '2026-08-22 11:27:55'),
(42, NULL, 'lease_contract', 9320, 'expiring_contract', 'critical', 'Contract LC-3166 expires in 16 days', 'Lease ends 2026-09-08', 'pm', 0, NULL, '2026-08-22 11:27:55');

-- --------------------------------------------------------

--
-- Table structure for table `ai_property_scores`
--

CREATE TABLE `ai_property_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `occupancy_health` tinyint(3) UNSIGNED DEFAULT NULL,
  `revenue_health` tinyint(3) UNSIGNED DEFAULT NULL,
  `maintenance_index` tinyint(3) UNSIGNED DEFAULT NULL,
  `calculated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_property_scores`
--

INSERT INTO `ai_property_scores` (`id`, `facility_id`, `score`, `occupancy_health`, `revenue_health`, `maintenance_index`, `calculated_at`) VALUES
(1, 9001, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(2, 0, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(3, 9101, 95, 87, 97, 100, '2026-08-24 08:01:10'),
(4, 9102, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(5, 9103, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(6, 9104, 97, 90, 100, 100, '2026-08-24 08:01:10'),
(7, 9105, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(8, 9106, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(9, 9107, 98, 93, 100, 100, '2026-08-24 08:01:10'),
(10, 9108, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(11, 9109, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(12, 9110, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(13, 9111, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(14, 9112, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(15, 9113, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(16, 9114, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(17, 9115, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(18, 9116, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(19, 9117, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(20, 9118, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(21, 9119, 97, 90, 100, 100, '2026-08-24 08:01:10'),
(22, 9120, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(23, 9121, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(24, 9122, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(25, 9123, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(26, 9124, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(27, 9125, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(28, 9126, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(29, 9127, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(30, 9128, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(31, 9129, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(32, 9130, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(33, 9131, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(34, 9132, 99, 96, 100, 100, '2026-08-24 08:01:10'),
(35, 9133, 100, 100, 100, 100, '2026-08-24 08:01:10'),
(36, 9134, 100, 100, 100, 100, '2026-08-24 08:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `ai_tenant_scores`
--

CREATE TABLE `ai_tenant_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `risk_level` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `factors_json` text DEFAULT NULL,
  `calculated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_mobile_logs`
--

CREATE TABLE `app_mobile_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'info',
  `message` text DEFAULT NULL,
  `context_json` text DEFAULT NULL,
  `app_version` varchar(32) DEFAULT NULL,
  `platform` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `tag_number` varchar(50) DEFAULT NULL,
  `category` enum('hvac','elevator','electrical','plumbing','fire_safety','security','it','civil','other') NOT NULL,
  `asset_type` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(120) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_start` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `amc_expiry` date DEFAULT NULL,
  `location_in_facility` varchar(200) DEFAULT NULL,
  `department` varchar(120) DEFAULT NULL,
  `cost_center` varchar(80) DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `floor_room` varchar(120) DEFAULT NULL,
  `status` enum('active','under_maintenance','faulty','retired','disposed') NOT NULL DEFAULT 'active',
  `criticality` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `health_score` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '0–100',
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL,
  `barcode_value` varchar(80) DEFAULT NULL,
  `qr_generated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `company_id`, `facility_id`, `name`, `asset_code`, `tag_number`, `category`, `asset_type`, `brand`, `manufacturer`, `model`, `serial_number`, `purchase_date`, `warranty_start`, `purchase_cost`, `warranty_expiry`, `amc_expiry`, `location_in_facility`, `department`, `cost_center`, `assigned_to`, `floor_room`, `status`, `criticality`, `health_score`, `last_maintenance`, `next_maintenance`, `notes`, `qr_token`, `barcode_value`, `qr_generated_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(0, NULL, 0, 'Lift', '01-AH', 'TAG-01AH', 'elevator', 'Lift', 'Comfort', '', '100', '101', NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, '', 'active', 'low', 100, NULL, NULL, 'dsdsd', 'a8254cf4723547ec897f4fa38ce2edd6', '01-AH', '2026-08-08 15:07:10', '2026-08-08 18:07:10', '2026-08-08 18:07:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `asset_documents`
--

CREATE TABLE `asset_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `doc_type` varchar(50) NOT NULL DEFAULT 'general',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_meter_readings`
--

CREATE TABLE `asset_meter_readings` (
  `id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED DEFAULT NULL,
  `reading_type` varchar(100) NOT NULL DEFAULT 'hours',
  `reading_value` decimal(12,2) NOT NULL,
  `reading_date` date NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_scan_logs`
--

CREATE TABLE `asset_scan_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `scanned_by` int(10) UNSIGNED DEFAULT NULL,
  `scan_source` varchar(30) NOT NULL DEFAULT 'qr',
  `action_taken` varchar(80) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `gps_lat` decimal(10,7) DEFAULT NULL,
  `gps_lng` decimal(10,7) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_scan_logs`
--

INSERT INTO `asset_scan_logs` (`id`, `asset_id`, `scanned_by`, `scan_source`, `action_taken`, `ip_address`, `user_agent`, `gps_lat`, `gps_lng`, `created_at`) VALUES
(0, 0, NULL, 'qr', 'public_view', '212.70.114.8', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.6 Mobile/15E148 Safari/604.1', NULL, NULL, '2026-08-08 15:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `shift_id` int(10) UNSIGNED DEFAULT NULL,
  `attendance_source` enum('manual','mobile','web','biometric','regularization') DEFAULT 'web',
  `raw_log_in_id` int(10) UNSIGNED DEFAULT NULL,
  `raw_log_out_id` int(10) UNSIGNED DEFAULT NULL,
  `regularization_id` int(10) UNSIGNED DEFAULT NULL,
  `supervisor_id` int(10) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `status` enum('present','absent','late','half_day','leave') NOT NULL DEFAULT 'present',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `overtime_hrs` decimal(5,2) DEFAULT NULL,
  `early_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cheques`
--

CREATE TABLE `cheques` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `cheque_no` varchar(50) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `status` enum('pending','deposited','cleared','bounced','cancelled','replaced') NOT NULL DEFAULT 'pending',
  `bank_name` varchar(120) DEFAULT NULL,
  `account_name` varchar(120) DEFAULT NULL,
  `account_no` varchar(50) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `bounce_reason` text DEFAULT NULL,
  `file_legal` tinyint(1) NOT NULL DEFAULT 0,
  `case_no` varchar(80) DEFAULT NULL,
  `filed_date` date DEFAULT NULL,
  `case_notes` text DEFAULT NULL,
  `cash_conversion_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection_assignments`
--

CREATE TABLE `collection_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `collector_id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `status` enum('pending','collected','skipped','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collector_handoffs`
--

CREATE TABLE `collector_handoffs` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` int(10) UNSIGNED DEFAULT NULL,
  `collector_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `acknowledged_by` int(10) UNSIGNED DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `status` enum('pending','acknowledged') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collector_sessions`
--

CREATE TABLE `collector_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `collector_id` int(10) UNSIGNED NOT NULL,
  `session_code` varchar(30) NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `opening_float` decimal(14,2) NOT NULL DEFAULT 0.00,
  `closing_cash` decimal(14,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_records`
--

CREATE TABLE `commission_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `deal_id` int(10) UNSIGNED NOT NULL,
  `rule_id` int(10) UNSIGNED DEFAULT NULL,
  `agent_id` int(10) UNSIGNED DEFAULT NULL,
  `agent_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `company_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_rules`
--

CREATE TABLE `commission_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `rule_name` varchar(120) NOT NULL,
  `deal_type` varchar(30) DEFAULT NULL,
  `commission_type` varchar(30) NOT NULL DEFAULT 'percentage',
  `agent_rate` decimal(5,2) NOT NULL,
  `company_rate` decimal(5,2) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `vat_number` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `code`, `address`, `contact_person`, `email`, `phone`, `vat_number`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Al Yazwa Real estate', 'YAZWA', '', 'Administrator', 'admin@fmerp.com', '', '', NULL, 'active', '2026-07-31 13:03:37', '2026-08-08 17:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `company_roles`
--

CREATE TABLE `company_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_users`
--

CREATE TABLE `company_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_audits`
--

CREATE TABLE `compliance_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `audit_type` varchar(100) NOT NULL,
  `audit_date` date NOT NULL,
  `score` tinyint(3) UNSIGNED DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `status` enum('open','in_progress','closed','passed','failed') NOT NULL DEFAULT 'open',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `compliance_audits`
--

INSERT INTO `compliance_audits` (`id`, `facility_id`, `audit_type`, `audit_date`, `score`, `findings`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(0, 9001, 'Electrical Safety', '2026-08-08', 0, '', 'open', 1, '2026-08-08 03:01:15', '2026-08-08 03:01:15');

-- --------------------------------------------------------

--
-- Table structure for table `compliance_documents`
--

CREATE TABLE `compliance_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('valid','expiring','expired') NOT NULL DEFAULT 'valid',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complimentary_offers`
--

CREATE TABLE `complimentary_offers` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `offer_type` varchar(50) NOT NULL,
  `free_period_value` int(11) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `contract_number` varchar(30) NOT NULL,
  `client_name` varchar(200) NOT NULL,
  `contract_type` enum('fm_services','amc','cleaning','security','it_support','other') NOT NULL DEFAULT 'fm_services',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `value` decimal(14,2) NOT NULL,
  `payment_terms` varchar(200) DEFAULT NULL,
  `status` enum('active','expired','terminated','draft') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_mobile` varchar(30) DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `company_id`, `facility_id`, `contract_number`, `client_name`, `contract_type`, `start_date`, `end_date`, `value`, `payment_terms`, `status`, `notes`, `attachment_path`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `client_email`, `client_mobile`, `unit_id`) VALUES
(0, NULL, 0, 'CON-2026-0001', 'Muhammed Hashir', '', '2026-05-01', '2027-05-08', 4000.00, NULL, 'active', NULL, NULL, 1, '2026-08-08 17:49:14', '2026-08-08 17:49:14', NULL, '', '30976558', 0);

-- --------------------------------------------------------

--
-- Table structure for table `contract_rent_schedule`
--

CREATE TABLE `contract_rent_schedule` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `year_number` smallint(5) UNSIGNED NOT NULL,
  `rent_amount` decimal(14,2) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contract_rent_schedule`
--

INSERT INTO `contract_rent_schedule` (`id`, `contract_id`, `year_number`, `rent_amount`, `created_at`) VALUES
(1, 9001, 1, 8500.00, '2026-08-03 16:01:41');

-- --------------------------------------------------------

--
-- Table structure for table `contract_templates`
--

CREATE TABLE `contract_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `content_en` mediumtext DEFAULT NULL,
  `content_ar` mediumtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contract_templates`
--

INSERT INTO `contract_templates` (`id`, `company_id`, `name`, `content_en`, `content_ar`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Standard Residential Lease (EN/AR)', '<p>This lease agreement is between the landlord and tenant for unit {{unit_number}} at {{property_name}}.</p><p>Rent: {{rent_amount}} {{currency}} per {{payment_frequency}}.</p><p>Period: {{start_date}} to {{end_date}}.</p>', '<p>عقد إيجار بين المالك والمستأجر للوحدة {{unit_number}} في {{property_name}}.</p>', 1, '2026-07-31 13:03:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cost_reminders`
--

CREATE TABLE `cost_reminders` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(200) NOT NULL,
  `due_date` date DEFAULT NULL,
  `recurrence` varchar(30) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','done','snoozed') NOT NULL DEFAULT 'pending',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_activities`
--

CREATE TABLE `crm_activities` (
  `id` int(10) UNSIGNED NOT NULL,
  `lead_id` int(10) UNSIGNED NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `outcome` varchar(80) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `next_follow_up` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_leads`
--

CREATE TABLE `crm_leads` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `lead_number` varchar(30) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `nationality` varchar(80) DEFAULT NULL,
  `source` varchar(80) DEFAULT NULL,
  `interest_type` enum('Buy','Rent','Both') NOT NULL DEFAULT 'Rent',
  `preferred_location` varchar(150) DEFAULT NULL,
  `budget_min` decimal(14,2) DEFAULT NULL,
  `budget_max` decimal(14,2) DEFAULT NULL,
  `bedrooms` tinyint(3) UNSIGNED DEFAULT NULL,
  `temperature` enum('Hot','Warm','Cold') NOT NULL DEFAULT 'Warm',
  `stage` varchar(50) NOT NULL DEFAULT 'new',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_time` time DEFAULT NULL,
  `lost_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `referral_name` varchar(150) DEFAULT NULL,
  `portal_name` varchar(150) DEFAULT NULL,
  `preferred_property_type` varchar(200) DEFAULT NULL,
  `additional_requirements` text DEFAULT NULL,
  `lost_notes` text DEFAULT NULL,
  `converted` tinyint(1) NOT NULL DEFAULT 0,
  `converted_to` varchar(30) DEFAULT NULL,
  `converted_ref_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_visits`
--

CREATE TABLE `crm_visits` (
  `id` int(10) UNSIGNED NOT NULL,
  `lead_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time DEFAULT NULL,
  `visit_type` varchar(50) DEFAULT NULL,
  `agent_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `customer_feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `doc_type` varchar(80) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('valid','expiring','expired') NOT NULL DEFAULT 'valid',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `module` varchar(50) DEFAULT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `doc_number` varchar(80) DEFAULT NULL,
  `issued_by` varchar(150) DEFAULT NULL,
  `doc_date` date DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_confidential` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `folder` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `property_id` int(10) UNSIGNED DEFAULT NULL,
  `project_id` int(10) UNSIGNED DEFAULT NULL,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `emp_code` varchar(20) NOT NULL,
  `first_name` varchar(80) DEFAULT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) DEFAULT NULL,
  `name_ar` varchar(200) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(80) DEFAULT NULL,
  `marital_status` varchar(30) DEFAULT NULL,
  `personal_mobile` varchar(30) DEFAULT NULL,
  `personal_email` varchar(120) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `emergency_contact_name` varchar(120) DEFAULT NULL,
  `emergency_contact_relationship` varchar(60) DEFAULT NULL,
  `emergency_contact_phone` varchar(30) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `designation_id` int(10) UNSIGNED DEFAULT NULL,
  `grade_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_type_id` int(10) UNSIGNED DEFAULT NULL,
  `employment_source_id` int(10) UNSIGNED DEFAULT NULL,
  `status_id` int(10) UNSIGNED DEFAULT NULL,
  `reporting_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `secondary_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `hr_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `shift_start` time NOT NULL DEFAULT '08:00:00',
  `shift_end` time NOT NULL DEFAULT '17:00:00',
  `shift_id` int(10) UNSIGNED DEFAULT NULL,
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `hire_date` date DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `confirmation_date` date DEFAULT NULL,
  `probation_end_date` date DEFAULT NULL,
  `last_working_date` date DEFAULT NULL,
  `wps_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `payroll_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `leave_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `attendance_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `overtime_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `payroll_responsibility` enum('our_company','supplier','external','consultant','none') NOT NULL DEFAULT 'our_company',
  `current_employment_period_id` int(10) UNSIGNED DEFAULT NULL,
  `qid_number` varchar(30) DEFAULT NULL,
  `qid_issue_date` date DEFAULT NULL,
  `qid_expiry` date DEFAULT NULL,
  `passport_number` varchar(30) DEFAULT NULL,
  `passport_issue_date` date DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `passport_country` varchar(80) DEFAULT NULL,
  `visa_number` varchar(30) DEFAULT NULL,
  `visa_type` varchar(60) DEFAULT NULL,
  `visa_issue_date` date DEFAULT NULL,
  `visa_expiry` date DEFAULT NULL,
  `work_permit_number` varchar(30) DEFAULT NULL,
  `work_permit_expiry` date DEFAULT NULL,
  `health_card_number` varchar(30) DEFAULT NULL,
  `health_card_expiry` date DEFAULT NULL,
  `driving_licence_number` varchar(30) DEFAULT NULL,
  `driving_licence_expiry` date DEFAULT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `bank_account_number` varchar(40) DEFAULT NULL,
  `iban` varchar(40) DEFAULT NULL,
  `salary_frequency` enum('monthly','weekly','daily') NOT NULL DEFAULT 'monthly',
  `status` enum('active','on_leave','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_breaks`
--

CREATE TABLE `employee_breaks` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `break_start` datetime NOT NULL,
  `break_end` datetime DEFAULT NULL,
  `duration_mins` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_profiles`
--

CREATE TABLE `employee_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fm_employee_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_code` varchar(30) NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `workspace` enum('pm','fm','both') NOT NULL DEFAULT 'fm',
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `designation_id` int(10) UNSIGNED DEFAULT NULL,
  `team_id` int(10) UNSIGNED DEFAULT NULL,
  `manager_user_id` int(10) UNSIGNED DEFAULT NULL,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','on_leave','resigned','terminated') NOT NULL DEFAULT 'active',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(80) DEFAULT NULL,
  `marital_status` varchar(30) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','intern') DEFAULT 'full_time',
  `basic_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(14,2) NOT NULL DEFAULT 0.00,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_phone` varchar(30) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `bank_iban` varchar(50) DEFAULT NULL,
  `passport_no` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `visa_no` varchar(50) DEFAULT NULL,
  `visa_expiry` date DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `national_id_expiry` date DEFAULT NULL,
  `driving_license_no` varchar(50) DEFAULT NULL,
  `driving_license_expiry` date DEFAULT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `skills_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_json`)),
  `certifications_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certifications_json`)),
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimations`
--

CREATE TABLE `estimations` (
  `id` int(10) UNSIGNED NOT NULL,
  `est_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `wo_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `labor_cost` decimal(12,2) DEFAULT 0.00,
  `material_cost` decimal(12,2) DEFAULT 0.00,
  `other_cost` decimal(12,2) DEFAULT 0.00,
  `actual_labor_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_material_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_transport_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_equipment_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_misc_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_other_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `selling_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estimated_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_profit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_margin` decimal(8,2) NOT NULL DEFAULT 0.00,
  `cost_variance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 0.00,
  `vat_amount` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` enum('draft','pending_approval','approved','rejected','converted') DEFAULT 'draft',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `revision` tinyint(3) UNSIGNED DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `salesman_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `maintenance_request_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimation_items`
--

CREATE TABLE `estimation_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `est_id` int(10) UNSIGNED NOT NULL,
  `type` enum('labor','material','other') DEFAULT 'material',
  `item_name` varchar(200) DEFAULT NULL,
  `description` varchar(300) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `unit` varchar(30) NOT NULL DEFAULT 'unit',
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estimated_unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `total_cost` decimal(12,2) DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estimated_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `margin_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
  `variance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `category` enum('labor','spare_parts','vendor','utility','administrative','emergency','other') NOT NULL DEFAULT 'other',
  `description` varchar(500) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'QAR',
  `expense_date` date NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Qatar',
  `manager_id` int(10) UNSIGNED DEFAULT NULL,
  `area_sqm` decimal(10,2) DEFAULT NULL,
  `floors` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('active','inactive','under_maintenance') NOT NULL DEFAULT 'active',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `total_units` int(10) UNSIGNED DEFAULT 0,
  `category` enum('Residential','Commercial') DEFAULT NULL,
  `property_type` varchar(80) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `listing_status` varchar(50) DEFAULT NULL,
  `for_sale` tinyint(1) NOT NULL DEFAULT 0,
  `sale_price` decimal(14,2) DEFAULT NULL,
  `price_per_sqm` decimal(12,2) DEFAULT NULL,
  `landlord_id` int(10) UNSIGNED DEFAULT NULL,
  `expected_monthly_income` decimal(14,2) DEFAULT NULL,
  `landlord_share_pct` decimal(5,2) DEFAULT NULL,
  `management_fee_pct` decimal(5,2) DEFAULT NULL,
  `finance_notes` text DEFAULT NULL,
  `owner_name` varchar(200) DEFAULT NULL,
  `owner_contact` varchar(50) DEFAULT NULL,
  `owner_email` varchar(150) DEFAULT NULL,
  `caretaker_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `code`, `address`, `city`, `country`, `manager_id`, `area_sqm`, `floors`, `status`, `latitude`, `longitude`, `created_at`, `updated_at`, `deleted_at`, `company_id`, `total_units`, `category`, `property_type`, `area`, `listing_status`, `for_sale`, `sale_price`, `price_per_sqm`, `landlord_id`, `expected_monthly_income`, `landlord_share_pct`, `management_fee_pct`, `finance_notes`, `owner_name`, `owner_contact`, `owner_email`, `caretaker_id`, `description`) VALUES
(0, 'Abu Hamour Building ', 'AHB-01', '', 'Doha', 'Qatar', NULL, NULL, 3, 'active', NULL, NULL, '2026-08-08 14:47:03', '2026-08-08 14:47:03', NULL, 1, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9001, 'Demo Residential Tower', 'DEMO-BLD-01', 'Demo Street, West Bay', 'Doha', 'Qatar', 9002, NULL, 5, 'active', NULL, NULL, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 1, 1, 'Residential', 'Apartment', NULL, NULL, 0, NULL, NULL, 9001, 8500.00, 95.00, 5.00, NULL, NULL, NULL, NULL, NULL, NULL),
(9101, 'Wakra-14Villa', 'MOHD-WAKRA-14VILLA-9', 'Building#69,Street#318,Zone#90,Wakra', 'Al Wakra', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 16, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 112000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9102, 'Hilal#15', 'MOHD-HILAL-15-9102', 'Building#15,Street#805,Zone#42,Al Hilal', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 11, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 40950.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9103, 'Hilal#26', 'MOHD-HILAL-26-9103', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 13000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9104, 'Old Airport#60', 'MOHD-OLD-AIRPORT-60-', 'Building#60,Street#630,Zone#45,Old Airport', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 11, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 32100.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9105, 'West Bay Villa', 'MOHD-WEST-BAY-VILLA-', 'Villa#50,Street#826,Zone#66,West Bay', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 2, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 24000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9106, 'Mamoura Villa', 'MOHD-MAMOURA-VILLA-9', 'Building#14,Street#759,Zone#43,Mamoura', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 12000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9107, 'Munthaza', 'MOHD-MUNTHAZA-9107', 'BUILDING NO # 05, ZONE # 24, STREET # 932, MUNTAZA', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 16, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9012, 58150.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9108, 'Hemple - Tenant Details Old Ghanim', 'IM-HEMPLE-TENANT-DET', 'building No 46, Old Ghanim,', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 25, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 159750.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9109, 'Hilal#01 (6 Villas)', 'IM-HILAL-01-6-VILLAS', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 6, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 55800.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9110, 'Hilal#02 (16 Appt)', 'IM-HILAL-02-16-APPT-', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 16, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 46100.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9111, 'Hilal#04 (Aliya)', 'IM-HILAL-04-ALIYA-91', 'Villa No:6, Street 240, Zone 42', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 6, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 55000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9112, 'Old Airport', 'IM-OLD-AIRPORT-9112', 'Villa No:15, Street: 876,Zone:45', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 4, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 45450.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9113, 'Industrial Area', 'IM-INDUSTRIAL-AREA-9', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 42000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9114, 'Najma (6 Villa)', 'IM-NAJMA-6-VILLA-911', 'Villa No 32, Zone 26', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 8, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 34800.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9115, 'Umm Guwailina Appt', 'IM-UMM-GUWAILINA-APP', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 21000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9116, 'Office Building', 'IM-OFFICE-BUILDING-9', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9013, 12000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9117, 'Wakra 4 Villas', 'BDR-WAKRA-4-VILLAS-9', NULL, 'Al Wakra', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 4, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9014, 36000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9118, 'Najma', 'BDR-NAJMA-9118', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9014, 17000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9119, 'Bin Omran', 'BDR-BIN-OMRAN-9119', 'Building No:24, Street No:913, Zone:37', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 11, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9014, 43200.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9120, 'Thumama', 'BDR-THUMAMA-9120', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9014, 12250.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9121, 'Markhiya', 'A-M-MARKHIYA-9121', 'Building No: 14, Zone:64, Street:956', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 2, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9015, 24500.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9122, 'Thumama', 'A-M-THUMAMA-9122', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 2, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9015, 25000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9123, 'Abu Hamour', 'A-M-ABU-HAMOUR-9123', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 19, NULL, NULL, NULL, NULL, 0, NULL, NULL, 9015, 121750.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9124, 'Al Rawda', 'OTHER-RENT-AL-RAWDA-', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 4, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 41250.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9125, 'Al Keesa', 'OTHER-RENT-AL-KEESA-', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 3, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 42000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9126, 'Ahmed Ali', 'OTHER-RENT-AHMED-ALI', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 2, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 25000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9127, 'Mahmoud', 'OTHER-RENT-MAHMOUD-9', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 19000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9128, 'Mohammed Kamber Hilal Villa', 'OTHER-RENT-MOHAMMED-', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 9500.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9129, 'Amina Ismail Mandani Villa', 'OTHER-RENT-AMINA-ISM', NULL, 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 10000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9130, 'Fahad Abdulrahman Y A Badar Mesaimeer', 'OTHER-RENT-FAHAD-ABD', 'Building No: 32, Street No: 610, Area No: 56.', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 11000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9131, 'Abubacker Mohd Abu Hamour Villa', 'OTHER-RENT-ABUBACKER', 'Building no: 31, Street No: 507, Area No: 56.', 'Doha', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 2, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 22000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9132, 'Wakara #42', 'WAKRA-42-WAKARA-42-9', '10', 'Al Wakra', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 28, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 177250.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9133, 'Wakara #43', 'WAKRA-43-WAKARA-43-9', NULL, 'Al Wakra', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL, 1, 29, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 174000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9134, 'Wakra Parking ', 'WAKRA PARKING', '', 'Al Wakra', 'Qatar', NULL, NULL, 1, 'active', NULL, NULL, '2026-08-22 14:06:27', '2026-08-22 11:07:38', NULL, 1, 43, NULL, 'Parking', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `finance_accounts`
--

CREATE TABLE `finance_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `is_control` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_accounts`
--

INSERT INTO `finance_accounts` (`id`, `group_id`, `code`, `name`, `branch_id`, `cost_center_id`, `is_control`, `is_active`, `opening_balance`, `created_at`) VALUES
(0, 0, '2120', 'Salaries Payable', NULL, NULL, 0, 1, 0.00, '2026-08-04 00:28:22');

-- --------------------------------------------------------

--
-- Table structure for table `finance_account_groups`
--

CREATE TABLE `finance_account_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(80) NOT NULL,
  `account_type` enum('asset','liability','equity','income','expense') NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_account_groups`
--

INSERT INTO `finance_account_groups` (`id`, `code`, `name`, `account_type`, `sort_order`) VALUES
(0, '2000', 'Liabilities', 'liability', 20);

-- --------------------------------------------------------

--
-- Table structure for table `finance_amc_schedules`
--

CREATE TABLE `finance_amc_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `frequency` enum('monthly','quarterly','annual') NOT NULL DEFAULT 'quarterly',
  `next_bill_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `auto_invoice` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_invoiced_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_audit_logs`
--

CREATE TABLE `finance_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `user_role` varchar(60) DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `transaction_id` int(10) UNSIGNED DEFAULT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_bank_accounts`
--

CREATE TABLE `finance_bank_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `scope_type` enum('company','branch','property') NOT NULL DEFAULT 'company',
  `name` varchar(120) NOT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `branch_name` varchar(120) DEFAULT NULL,
  `account_number` varchar(40) DEFAULT NULL,
  `iban` varchar(40) DEFAULT NULL,
  `swift_bic` varchar(20) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `account_type` enum('current','savings','corporate','other') NOT NULL DEFAULT 'current',
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `opening_balance_date` date DEFAULT NULL,
  `opening_balance_notes` text DEFAULT NULL,
  `opening_balance_created_by` int(10) UNSIGNED DEFAULT NULL,
  `opening_balance_created_at` datetime DEFAULT NULL,
  `bank_contact` varchar(120) DEFAULT NULL,
  `bank_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive','closed') NOT NULL DEFAULT 'active',
  `current_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `available_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `min_balance_alert` decimal(14,2) DEFAULT NULL,
  `last_transaction_date` date DEFAULT NULL,
  `account_opening_date` date DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `gl_account_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_branches`
--

CREATE TABLE `finance_branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `establishment_id` varchar(30) DEFAULT NULL,
  `employer_bank_account_id` int(10) UNSIGNED DEFAULT NULL,
  `wps_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `default_payroll_group_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_budgets`
--

CREATE TABLE `finance_budgets` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `fiscal_year` year(4) NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','approved','closed') NOT NULL DEFAULT 'draft',
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_budget_lines`
--

CREATE TABLE `finance_budget_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `budget_id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED DEFAULT NULL,
  `category` varchar(60) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_cash_accounts`
--

CREATE TABLE `finance_cash_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `account_type` enum('main','branch','petty','property','other') NOT NULL DEFAULT 'main',
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `opening_balance_date` date DEFAULT NULL,
  `opening_balance_notes` text DEFAULT NULL,
  `responsible_user_id` int(10) UNSIGNED DEFAULT NULL,
  `current_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `available_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `min_balance_alert` decimal(14,2) DEFAULT NULL,
  `last_transaction_date` date DEFAULT NULL,
  `status` enum('active','inactive','closed') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_categories`
--

CREATE TABLE `finance_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_type` enum('income','expense','deposit','withdrawal','transfer','adjustment','refund') NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_categories`
--

INSERT INTO `finance_categories` (`id`, `category_type`, `code`, `name`, `is_active`, `sort_order`) VALUES
(1, 'deposit', 'client_payment', 'Client Payment', 1, 1),
(2, 'deposit', 'rental_income', 'Rental Income', 1, 2),
(3, 'deposit', 'service_income', 'Service Income', 1, 3),
(4, 'deposit', 'maintenance_income', 'Maintenance Income', 1, 4),
(5, 'deposit', 'property_income', 'Property Income', 1, 5),
(6, 'deposit', 'cash_deposit', 'Cash Deposit', 1, 6),
(7, 'deposit', 'cheque_deposit', 'Cheque Deposit', 1, 7),
(8, 'deposit', 'manual_deposit', 'Manual Deposit', 1, 8),
(9, 'deposit', 'other_income', 'Other Income', 1, 9),
(10, 'withdrawal', 'vendor_payment', 'Vendor Payment', 1, 1),
(11, 'withdrawal', 'salary', 'Salary', 1, 2),
(12, 'withdrawal', 'utility', 'Utility', 1, 3),
(13, 'withdrawal', 'maintenance', 'Maintenance', 1, 4),
(14, 'withdrawal', 'property_expense', 'Property Expense', 1, 5),
(15, 'withdrawal', 'purchase', 'Purchase', 1, 6),
(16, 'withdrawal', 'refund', 'Refund', 1, 7),
(17, 'withdrawal', 'petty_cash', 'Petty Cash Transfer', 1, 8),
(18, 'withdrawal', 'other_expense', 'Other Expense', 1, 9),
(19, 'income', 'invoice', 'Client Invoice', 1, 1),
(20, 'income', 'rent', 'Property Rent', 1, 2),
(21, 'income', 'service', 'Service Contract', 1, 3),
(22, 'income', 'maintenance', 'Maintenance Service', 1, 4),
(23, 'income', 'facility', 'Facility Service', 1, 5),
(24, 'income', 'other', 'Other Income', 1, 6),
(25, 'expense', 'vendor', 'Vendor Bill', 1, 1),
(26, 'expense', 'utility', 'Utility', 1, 2),
(27, 'expense', 'maintenance', 'Maintenance', 1, 3),
(28, 'expense', 'salary', 'Salary', 1, 4),
(29, 'expense', 'purchase', 'Purchase', 1, 5),
(30, 'expense', 'property', 'Property Expense', 1, 6),
(31, 'expense', 'other', 'Other Expense', 1, 7),
(32, 'expense', 'petty_office', 'Office Supplies', 1, 10),
(33, 'expense', 'petty_maintenance', 'Maintenance', 1, 11),
(34, 'expense', 'petty_transport', 'Transport', 1, 12),
(35, 'expense', 'petty_meals', 'Meals & Refreshments', 1, 13),
(36, 'expense', 'petty_emergency', 'Emergency', 1, 14);

-- --------------------------------------------------------

--
-- Table structure for table `finance_cost_centers`
--

CREATE TABLE `finance_cost_centers` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `department` varchar(80) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_deposits`
--

CREATE TABLE `finance_deposits` (
  `id` int(10) UNSIGNED NOT NULL,
  `deposit_number` varchar(30) NOT NULL,
  `deposit_date` date NOT NULL,
  `bank_account_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `deposit_source` varchar(60) DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(60) DEFAULT NULL,
  `payment_method` varchar(40) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','posted','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_entries`
--

CREATE TABLE `finance_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `entry_type` varchar(50) NOT NULL,
  `direction` enum('income','expense') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `landlord_id` int(10) UNSIGNED DEFAULT NULL,
  `cost_type_id` int(10) UNSIGNED DEFAULT NULL,
  `ref_module` varchar(50) DEFAULT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(80) DEFAULT NULL,
  `paid_by` varchar(80) DEFAULT NULL,
  `paid_to` varchar(80) DEFAULT NULL,
  `frequency` varchar(30) DEFAULT 'one-off',
  `entry_date` date NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_expense_records`
--

CREATE TABLE `finance_expense_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `expense_number` varchar(30) NOT NULL,
  `expense_date` date NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `payment_method` varchar(40) DEFAULT NULL,
  `bank_account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_account_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(60) DEFAULT NULL,
  `reference_number` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','submitted','pending_approval','approved','paid','posted','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_income_records`
--

CREATE TABLE `finance_income_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `income_number` varchar(30) NOT NULL,
  `income_date` date NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `payment_method` varchar(40) DEFAULT NULL,
  `bank_account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_account_id` int(10) UNSIGNED DEFAULT NULL,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `reference` varchar(60) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','posted','cancelled') NOT NULL DEFAULT 'draft',
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_integration_log`
--

CREATE TABLE `finance_integration_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `module` varchar(40) NOT NULL,
  `event` varchar(60) NOT NULL,
  `source_type` varchar(40) NOT NULL,
  `source_id` int(10) UNSIGNED NOT NULL,
  `target_type` varchar(40) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_journal_entries`
--

CREATE TABLE `finance_journal_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `entry_number` varchar(30) NOT NULL,
  `entry_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source_module` varchar(40) DEFAULT NULL,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','pending','approved','posted','reversed') NOT NULL DEFAULT 'draft',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `reversal_of` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_journal_lines`
--

CREATE TABLE `finance_journal_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `journal_id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `debit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_advances`
--

CREATE TABLE `finance_petty_advances` (
  `id` int(10) UNSIGNED NOT NULL,
  `advance_number` varchar(30) NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `purpose` varchar(255) DEFAULT NULL,
  `required_date` date DEFAULT NULL,
  `expected_settlement_date` date DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('requested','pending_approval','approved','issued','outstanding','settled','rejected','cancelled') NOT NULL DEFAULT 'requested',
  `issued_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `settled_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `returned_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `issued_by` int(10) UNSIGNED DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_advance_settlements`
--

CREATE TABLE `finance_petty_advance_settlements` (
  `id` int(10) UNSIGNED NOT NULL,
  `advance_id` int(10) UNSIGNED NOT NULL,
  `settlement_date` date NOT NULL,
  `expense_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `return_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `additional_payment` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved','posted','rejected') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_audit_logs`
--

CREATE TABLE `finance_petty_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `user_role` varchar(60) DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_cash_accounts`
--

CREATE TABLE `finance_petty_cash_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `custodian_user_id` int(10) UNSIGNED DEFAULT NULL,
  `custodian_assigned_at` datetime DEFAULT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `opening_balance_date` date DEFAULT NULL,
  `max_cash_limit` decimal(14,2) DEFAULT NULL,
  `replenishment_level` decimal(14,2) DEFAULT NULL,
  `current_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `physical_balance` decimal(14,2) DEFAULT NULL,
  `last_count_date` date DEFAULT NULL,
  `last_transaction_date` date DEFAULT NULL,
  `status` enum('active','suspended','closed') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_counts`
--

CREATE TABLE `finance_petty_counts` (
  `id` int(10) UNSIGNED NOT NULL,
  `count_number` varchar(30) NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `count_date` date NOT NULL,
  `system_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `physical_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `difference` decimal(14,2) NOT NULL DEFAULT 0.00,
  `shortage` decimal(14,2) NOT NULL DEFAULT 0.00,
  `excess` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved','posted','rejected') NOT NULL DEFAULT 'draft',
  `counted_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_count_lines`
--

CREATE TABLE `finance_petty_count_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `count_id` int(10) UNSIGNED NOT NULL,
  `denomination` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `line_total` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_custodian_history`
--

CREATE TABLE `finance_petty_custodian_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `from_user_id` int(10) UNSIGNED DEFAULT NULL,
  `to_user_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('active','transferred','ended') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_expenses`
--

CREATE TABLE `finance_petty_expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `expense_number` varchar(30) NOT NULL,
  `expense_date` date NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `custodian_user_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `asset_id` int(10) UNSIGNED DEFAULT NULL,
  `purchase_request_id` int(10) UNSIGNED DEFAULT NULL,
  `receipt_number` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','submitted','pending_approval','approved','paid','posted','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_reconciliations`
--

CREATE TABLE `finance_petty_reconciliations` (
  `id` int(10) UNSIGNED NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `reconciliation_date` date NOT NULL,
  `custodian_user_id` int(10) UNSIGNED DEFAULT NULL,
  `system_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `physical_cash` decimal(14,2) NOT NULL DEFAULT 0.00,
  `pending_advances` decimal(14,2) NOT NULL DEFAULT 0.00,
  `pending_expenses` decimal(14,2) NOT NULL DEFAULT 0.00,
  `shortage` decimal(14,2) NOT NULL DEFAULT 0.00,
  `excess` decimal(14,2) NOT NULL DEFAULT 0.00,
  `final_difference` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','in_progress','reconciled','difference_found','approved') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `locked_by` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_replenishments`
--

CREATE TABLE `finance_petty_replenishments` (
  `id` int(10) UNSIGNED NOT NULL,
  `replenishment_number` varchar(30) NOT NULL,
  `replenishment_date` date NOT NULL,
  `petty_account_id` int(10) UNSIGNED NOT NULL,
  `source_account_type` enum('bank','cash') NOT NULL,
  `source_account_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','posted','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_petty_transfers`
--

CREATE TABLE `finance_petty_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `transfer_number` varchar(30) NOT NULL,
  `transfer_date` date NOT NULL,
  `from_petty_account_id` int(10) UNSIGNED NOT NULL,
  `to_petty_account_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `purpose` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','posted','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_reconciliations`
--

CREATE TABLE `finance_reconciliations` (
  `id` int(10) UNSIGNED NOT NULL,
  `bank_account_id` int(10) UNSIGNED NOT NULL,
  `statement_date` date NOT NULL,
  `statement_opening` decimal(14,2) NOT NULL DEFAULT 0.00,
  `statement_closing` decimal(14,2) NOT NULL DEFAULT 0.00,
  `system_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `difference` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_started','in_progress','reconciled') NOT NULL DEFAULT 'not_started',
  `notes` text DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `locked_by` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_reconciliation_items`
--

CREATE TABLE `finance_reconciliation_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `reconciliation_id` int(10) UNSIGNED NOT NULL,
  `transaction_id` int(10) UNSIGNED DEFAULT NULL,
  `statement_ref` varchar(60) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `match_status` enum('matched','unmatched','partial','bank_charge','bank_interest','adjustment') NOT NULL DEFAULT 'unmatched',
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transactions`
--

CREATE TABLE `finance_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_number` varchar(30) NOT NULL,
  `transaction_date` date NOT NULL,
  `account_type` enum('bank','cash','petty') NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `transaction_type` enum('opening_balance','income','expense','deposit','withdrawal','bank_transfer','cash_transfer','refund','adjustment','payment','receipt','cash_received','cash_expense','cash_advance','advance_settlement','replenishment','petty_transfer','cash_return','cash_adjustment') NOT NULL,
  `debit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `exchange_rate` decimal(12,6) DEFAULT NULL,
  `base_amount` decimal(14,2) DEFAULT NULL,
  `reference_type` varchar(40) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `linked_transaction_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method` varchar(40) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','posted','cancelled','reversed') NOT NULL DEFAULT 'draft',
  `reversal_of` int(10) UNSIGNED DEFAULT NULL,
  `is_reversal` tinyint(1) NOT NULL DEFAULT 0,
  `counts_as_income` tinyint(1) NOT NULL DEFAULT 0,
  `counts_as_expense` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transaction_approvals`
--

CREATE TABLE `finance_transaction_approvals` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_ref_type` varchar(40) NOT NULL,
  `transaction_ref_id` int(10) UNSIGNED NOT NULL,
  `approval_level` int(11) NOT NULL DEFAULT 1,
  `required_role` varchar(60) DEFAULT NULL,
  `approver_user_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `acted_by` int(10) UNSIGNED DEFAULT NULL,
  `acted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transfers`
--

CREATE TABLE `finance_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `transfer_number` varchar(30) NOT NULL,
  `transfer_date` date NOT NULL,
  `from_account_type` enum('bank','cash') NOT NULL,
  `from_account_id` int(10) UNSIGNED NOT NULL,
  `to_account_type` enum('bank','cash') NOT NULL,
  `to_account_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `transfer_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reference` varchar(60) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','posted','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_vendor_bills`
--

CREATE TABLE `finance_vendor_bills` (
  `id` int(10) UNSIGNED NOT NULL,
  `bill_number` varchar(30) NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `purchase_order_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `bill_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `status` enum('draft','pending','approved','paid','cancelled') NOT NULL DEFAULT 'draft',
  `retention_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_withdrawals`
--

CREATE TABLE `finance_withdrawals` (
  `id` int(10) UNSIGNED NOT NULL,
  `withdrawal_number` varchar(30) NOT NULL,
  `withdrawal_date` date NOT NULL,
  `bank_account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_account_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `expense_reference` varchar(60) DEFAULT NULL,
  `payment_reference` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','posted','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grn`
--

CREATE TABLE `grn` (
  `id` int(10) UNSIGNED NOT NULL,
  `grn_number` varchar(30) NOT NULL,
  `po_id` int(10) UNSIGNED NOT NULL,
  `received_date` date NOT NULL,
  `received_by` int(10) UNSIGNED NOT NULL,
  `status` enum('partial','complete','rejected') DEFAULT 'partial',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grn_items`
--

CREATE TABLE `grn_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `grn_id` int(10) UNSIGNED NOT NULL,
  `pr_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `received_qty` int(10) UNSIGNED DEFAULT 0,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_feedback`
--

CREATE TABLE `helpdesk_feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_approval_actions`
--

CREATE TABLE `hr_approval_actions` (
  `id` int(10) UNSIGNED NOT NULL,
  `approval_request_id` int(10) UNSIGNED NOT NULL,
  `step_no` int(11) NOT NULL DEFAULT 0,
  `action` enum('submitted','approved','rejected','returned','delegated','comment') NOT NULL,
  `notes` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `actioned_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_approval_requests`
--

CREATE TABLE `hr_approval_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `workflow_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_request_id` int(10) UNSIGNED DEFAULT NULL,
  `module` varchar(40) NOT NULL,
  `source_table` varchar(60) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected','returned','cancelled') NOT NULL DEFAULT 'pending',
  `current_step_no` int(11) NOT NULL DEFAULT 1,
  `initiated_by` int(10) UNSIGNED DEFAULT NULL,
  `initiated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_approval_steps`
--

CREATE TABLE `hr_approval_steps` (
  `id` int(10) UNSIGNED NOT NULL,
  `workflow_id` int(10) UNSIGNED NOT NULL,
  `step_no` int(11) NOT NULL,
  `approver_type` enum('reporting_manager','secondary_manager','hr_manager','department_head','role','user','finance') NOT NULL DEFAULT 'reporting_manager',
  `approver_role` varchar(60) DEFAULT NULL,
  `approver_user_id` int(10) UNSIGNED DEFAULT NULL,
  `can_delegate` tinyint(1) NOT NULL DEFAULT 0,
  `sla_hours` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_approval_steps`
--

INSERT INTO `hr_approval_steps` (`id`, `workflow_id`, `step_no`, `approver_type`, `approver_role`, `approver_user_id`, `can_delegate`, `sla_hours`) VALUES
(1, 1, 1, 'reporting_manager', NULL, NULL, 0, NULL),
(2, 1, 2, 'hr_manager', NULL, NULL, 0, NULL),
(3, 2, 1, 'hr_manager', NULL, NULL, 0, NULL),
(4, 2, 2, 'finance', NULL, NULL, 0, NULL),
(5, 3, 1, 'reporting_manager', NULL, NULL, 0, NULL),
(6, 3, 2, 'hr_manager', NULL, NULL, 0, NULL),
(7, 4, 1, 'hr_manager', NULL, NULL, 0, NULL),
(8, 4, 2, 'finance', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hr_approval_workflows`
--

CREATE TABLE `hr_approval_workflows` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) NOT NULL,
  `module` varchar(40) NOT NULL,
  `request_type` varchar(60) DEFAULT NULL,
  `operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `grade_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `min_amount` decimal(14,2) DEFAULT NULL,
  `max_amount` decimal(14,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_approval_workflows`
--

INSERT INTO `hr_approval_workflows` (`id`, `company_id`, `name`, `code`, `module`, `request_type`, `operating_company_id`, `department_id`, `grade_id`, `facility_id`, `min_amount`, `max_amount`, `is_active`, `priority`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Transfer — Manager then HR', 'WF_TRANSFER', 'transfer', 'org_transfer', NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, '2026-08-22 13:42:12', '2026-08-22 13:42:12'),
(2, NULL, 'Settlement — HR then Finance', 'WF_SETTLEMENT', 'settlement', 'final', NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, '2026-08-22 13:42:12', '2026-08-22 13:42:12'),
(3, NULL, 'Transfer — Manager then HR', 'WF_TRANSFER', 'transfer', 'org_transfer', NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(4, NULL, 'Settlement — HR then Finance', 'WF_SETTLEMENT', 'settlement', 'final', NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_attendance_raw_logs`
--

CREATE TABLE `hr_attendance_raw_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `log_type` enum('check_in','check_out','break_start','break_end','biometric','api') NOT NULL DEFAULT 'check_in',
  `logged_at` datetime NOT NULL,
  `source` enum('mobile','web','biometric','api','manual') NOT NULL DEFAULT 'web',
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `device_id` varchar(80) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_attendance_regularizations`
--

CREATE TABLE `hr_attendance_regularizations` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `attendance_id` int(10) UNSIGNED DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `requested_check_in` datetime DEFAULT NULL,
  `requested_check_out` datetime DEFAULT NULL,
  `requested_status` enum('present','absent','late','half_day','leave') DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_audit_logs`
--

CREATE TABLE `hr_audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `module` varchar(40) NOT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_branches`
--

CREATE TABLE `hr_branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_clearance_checklists`
--

CREATE TABLE `hr_clearance_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `separation_type` enum('resignation','termination','contract_end','all') NOT NULL DEFAULT 'all',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_clearance_checklists`
--

INSERT INTO `hr_clearance_checklists` (`id`, `company_id`, `code`, `name`, `separation_type`, `is_active`, `created_at`) VALUES
(1, NULL, 'DEFAULT', 'Standard Exit Clearance', 'all', 1, '2026-08-22 13:42:11'),
(2, NULL, 'DEFAULT', 'Standard Exit Clearance', 'all', 1, '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_clearance_instances`
--

CREATE TABLE `hr_clearance_instances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `employment_period_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','in_progress','cleared','blocked') NOT NULL DEFAULT 'pending',
  `cleared_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_clearance_items`
--

CREATE TABLE `hr_clearance_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `department` varchar(60) DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_clearance_items`
--

INSERT INTO `hr_clearance_items` (`id`, `checklist_id`, `code`, `name`, `department`, `is_mandatory`, `sort_order`) VALUES
(1, 1, 'IT', 'Return IT assets / revoke access', 'IT', 1, 10),
(2, 1, 'FIN', 'Clear petty cash / advances', 'Finance', 1, 20),
(3, 1, 'HR', 'Exit interview completed', 'HR', 1, 30),
(4, 1, 'OPS', 'Handover site assignments', 'Operations', 1, 40),
(8, 1, 'IT', 'Return IT assets / revoke access', 'IT', 1, 10),
(9, 2, 'IT', 'Return IT assets / revoke access', 'IT', 1, 10),
(10, 1, 'FIN', 'Clear petty cash / advances', 'Finance', 1, 20),
(11, 2, 'FIN', 'Clear petty cash / advances', 'Finance', 1, 20),
(12, 1, 'HR', 'Exit interview completed', 'HR', 1, 30),
(13, 2, 'HR', 'Exit interview completed', 'HR', 1, 30),
(14, 1, 'OPS', 'Handover site assignments', 'Operations', 1, 40),
(15, 2, 'OPS', 'Handover site assignments', 'Operations', 1, 40);

-- --------------------------------------------------------

--
-- Table structure for table `hr_clearance_item_status`
--

CREATE TABLE `hr_clearance_item_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `instance_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','cleared','waived','blocked') NOT NULL DEFAULT 'pending',
  `cleared_by` int(10) UNSIGNED DEFAULT NULL,
  `cleared_at` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_contract_expiry_alerts`
--

CREATE TABLE `hr_contract_expiry_alerts` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `alert_type` varchar(30) NOT NULL,
  `contract_end_date` date DEFAULT NULL,
  `notified_user_id` int(10) UNSIGNED DEFAULT NULL,
  `notified_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_cost_centers`
--

CREATE TABLE `hr_cost_centers` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_departments`
--

CREATE TABLE `hr_departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_designations`
--

CREATE TABLE `hr_designations` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `name` varchar(120) NOT NULL DEFAULT '',
  `grade_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `level` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_document_categories`
--

CREATE TABLE `hr_document_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(120) NOT NULL,
  `requires_expiry` tinyint(1) NOT NULL DEFAULT 1,
  `notify_days_before` int(11) NOT NULL DEFAULT 30,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_document_categories`
--

INSERT INTO `hr_document_categories` (`id`, `company_id`, `code`, `name`, `requires_expiry`, `notify_days_before`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'passport', 'Passport', 1, 90, 10, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(2, NULL, 'qid', 'Qatar ID / National ID', 1, 90, 20, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(3, NULL, 'visa', 'Visa', 1, 60, 30, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(4, NULL, 'work_permit', 'Work Permit / Labour Card', 1, 60, 40, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(5, NULL, 'employment_contract', 'Employment Contract', 1, 30, 50, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(6, NULL, 'supplier_contract', 'Supplier Contract Reference', 1, 30, 55, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(7, NULL, 'offer_letter', 'Offer Letter', 0, 0, 60, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(8, NULL, 'labour_contract', 'Labour Contract', 1, 30, 70, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(9, NULL, 'medical_certificate', 'Medical Certificate', 1, 30, 80, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(10, NULL, 'health_card', 'Health Card', 1, 30, 90, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(11, NULL, 'driving_licence', 'Driving Licence', 1, 30, 100, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(12, NULL, 'insurance', 'Insurance', 1, 30, 110, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(13, NULL, 'educational_certificate', 'Educational Certificate', 0, 0, 120, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(14, NULL, 'experience_certificate', 'Experience Certificate', 0, 0, 130, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(15, NULL, 'training_certificate', 'Training Certificate', 1, 365, 140, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(16, NULL, 'skill_certificate', 'Skill Certificate', 1, 365, 150, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(17, NULL, 'increment_letter', 'Increment Letter', 0, 0, 160, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(18, NULL, 'promotion_letter', 'Promotion Letter', 0, 0, 170, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(19, NULL, 'warning_letter', 'Warning Letter', 0, 0, 180, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(20, NULL, 'transfer_letter', 'Transfer Letter', 0, 0, 190, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(21, NULL, 'salary_certificate', 'Salary Certificate', 0, 0, 200, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(22, NULL, 'noc', 'NOC', 0, 0, 210, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(23, NULL, 'resignation', 'Resignation', 0, 0, 220, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(24, NULL, 'termination', 'Termination', 0, 0, 230, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(25, NULL, 'final_settlement', 'Final Settlement', 0, 0, 240, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(26, NULL, 'client_approval', 'Client Approval', 0, 0, 250, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(27, NULL, 'site_access', 'Site Access Document', 1, 30, 260, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(28, NULL, 'other_hr', 'Other HR Document', 0, 0, 270, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(29, NULL, 'passport', 'Passport', 1, 90, 10, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(30, NULL, 'qid', 'Qatar ID / National ID', 1, 90, 20, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(31, NULL, 'visa', 'Visa', 1, 60, 30, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(32, NULL, 'work_permit', 'Work Permit / Labour Card', 1, 60, 40, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(33, NULL, 'employment_contract', 'Employment Contract', 1, 30, 50, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(34, NULL, 'supplier_contract', 'Supplier Contract Reference', 1, 30, 55, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(35, NULL, 'offer_letter', 'Offer Letter', 0, 0, 60, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(36, NULL, 'labour_contract', 'Labour Contract', 1, 30, 70, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(37, NULL, 'medical_certificate', 'Medical Certificate', 1, 30, 80, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(38, NULL, 'health_card', 'Health Card', 1, 30, 90, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(39, NULL, 'driving_licence', 'Driving Licence', 1, 30, 100, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(40, NULL, 'insurance', 'Insurance', 1, 30, 110, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(41, NULL, 'educational_certificate', 'Educational Certificate', 0, 0, 120, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(42, NULL, 'experience_certificate', 'Experience Certificate', 0, 0, 130, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(43, NULL, 'training_certificate', 'Training Certificate', 1, 365, 140, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(44, NULL, 'skill_certificate', 'Skill Certificate', 1, 365, 150, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(45, NULL, 'increment_letter', 'Increment Letter', 0, 0, 160, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(46, NULL, 'promotion_letter', 'Promotion Letter', 0, 0, 170, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(47, NULL, 'warning_letter', 'Warning Letter', 0, 0, 180, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(48, NULL, 'transfer_letter', 'Transfer Letter', 0, 0, 190, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(49, NULL, 'salary_certificate', 'Salary Certificate', 0, 0, 200, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(50, NULL, 'noc', 'NOC', 0, 0, 210, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(51, NULL, 'resignation', 'Resignation', 0, 0, 220, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(52, NULL, 'termination', 'Termination', 0, 0, 230, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(53, NULL, 'final_settlement', 'Final Settlement', 0, 0, 240, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(54, NULL, 'client_approval', 'Client Approval', 0, 0, 250, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(55, NULL, 'site_access', 'Site Access Document', 1, 30, 260, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(56, NULL, 'other_hr', 'Other HR Document', 0, 0, 270, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(57, NULL, 'passport', 'Passport', 1, 90, 10, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(58, NULL, 'qid', 'Qatar ID / National ID', 1, 90, 20, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(59, NULL, 'visa', 'Visa', 1, 60, 30, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(60, NULL, 'work_permit', 'Work Permit / Labour Card', 1, 60, 40, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(61, NULL, 'employment_contract', 'Employment Contract', 1, 30, 50, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(62, NULL, 'supplier_contract', 'Supplier Contract Reference', 1, 30, 55, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(63, NULL, 'offer_letter', 'Offer Letter', 0, 0, 60, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(64, NULL, 'labour_contract', 'Labour Contract', 1, 30, 70, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(65, NULL, 'medical_certificate', 'Medical Certificate', 1, 30, 80, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(66, NULL, 'health_card', 'Health Card', 1, 30, 90, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(67, NULL, 'driving_licence', 'Driving Licence', 1, 30, 100, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(68, NULL, 'insurance', 'Insurance', 1, 30, 110, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(69, NULL, 'educational_certificate', 'Educational Certificate', 0, 0, 120, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(70, NULL, 'experience_certificate', 'Experience Certificate', 0, 0, 130, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(71, NULL, 'training_certificate', 'Training Certificate', 1, 365, 140, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(72, NULL, 'skill_certificate', 'Skill Certificate', 1, 365, 150, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(73, NULL, 'increment_letter', 'Increment Letter', 0, 0, 160, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(74, NULL, 'promotion_letter', 'Promotion Letter', 0, 0, 170, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(75, NULL, 'warning_letter', 'Warning Letter', 0, 0, 180, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(76, NULL, 'transfer_letter', 'Transfer Letter', 0, 0, 190, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(77, NULL, 'salary_certificate', 'Salary Certificate', 0, 0, 200, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(78, NULL, 'noc', 'NOC', 0, 0, 210, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(79, NULL, 'resignation', 'Resignation', 0, 0, 220, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(80, NULL, 'termination', 'Termination', 0, 0, 230, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(81, NULL, 'final_settlement', 'Final Settlement', 0, 0, 240, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(82, NULL, 'client_approval', 'Client Approval', 0, 0, 250, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(83, NULL, 'site_access', 'Site Access Document', 1, 30, 260, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(84, NULL, 'other_hr', 'Other HR Document', 0, 0, 270, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(85, NULL, 'passport', 'Passport', 1, 90, 10, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(86, NULL, 'qid', 'Qatar ID / National ID', 1, 90, 20, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(87, NULL, 'visa', 'Visa', 1, 60, 30, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(88, NULL, 'work_permit', 'Work Permit / Labour Card', 1, 60, 40, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(89, NULL, 'employment_contract', 'Employment Contract', 1, 30, 50, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(90, NULL, 'supplier_contract', 'Supplier Contract Reference', 1, 30, 55, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(91, NULL, 'offer_letter', 'Offer Letter', 0, 0, 60, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(92, NULL, 'labour_contract', 'Labour Contract', 1, 30, 70, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(93, NULL, 'medical_certificate', 'Medical Certificate', 1, 30, 80, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(94, NULL, 'health_card', 'Health Card', 1, 30, 90, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(95, NULL, 'driving_licence', 'Driving Licence', 1, 30, 100, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(96, NULL, 'insurance', 'Insurance', 1, 30, 110, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(97, NULL, 'educational_certificate', 'Educational Certificate', 0, 0, 120, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(98, NULL, 'experience_certificate', 'Experience Certificate', 0, 0, 130, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(99, NULL, 'training_certificate', 'Training Certificate', 1, 365, 140, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(100, NULL, 'skill_certificate', 'Skill Certificate', 1, 365, 150, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(101, NULL, 'increment_letter', 'Increment Letter', 0, 0, 160, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(102, NULL, 'promotion_letter', 'Promotion Letter', 0, 0, 170, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(103, NULL, 'warning_letter', 'Warning Letter', 0, 0, 180, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(104, NULL, 'transfer_letter', 'Transfer Letter', 0, 0, 190, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(105, NULL, 'salary_certificate', 'Salary Certificate', 0, 0, 200, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(106, NULL, 'noc', 'NOC', 0, 0, 210, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(107, NULL, 'resignation', 'Resignation', 0, 0, 220, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(108, NULL, 'termination', 'Termination', 0, 0, 230, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(109, NULL, 'final_settlement', 'Final Settlement', 0, 0, 240, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(110, NULL, 'client_approval', 'Client Approval', 0, 0, 250, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(111, NULL, 'site_access', 'Site Access Document', 1, 30, 260, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(112, NULL, 'other_hr', 'Other HR Document', 0, 0, 270, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(113, NULL, 'passport', 'Passport', 1, 90, 10, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(114, NULL, 'qid', 'Qatar ID / National ID', 1, 90, 20, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(115, NULL, 'visa', 'Visa', 1, 60, 30, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(116, NULL, 'work_permit', 'Work Permit / Labour Card', 1, 60, 40, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(117, NULL, 'employment_contract', 'Employment Contract', 1, 30, 50, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(118, NULL, 'supplier_contract', 'Supplier Contract Reference', 1, 30, 55, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(119, NULL, 'offer_letter', 'Offer Letter', 0, 0, 60, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(120, NULL, 'labour_contract', 'Labour Contract', 1, 30, 70, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(121, NULL, 'medical_certificate', 'Medical Certificate', 1, 30, 80, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(122, NULL, 'health_card', 'Health Card', 1, 30, 90, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(123, NULL, 'driving_licence', 'Driving Licence', 1, 30, 100, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(124, NULL, 'insurance', 'Insurance', 1, 30, 110, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(125, NULL, 'educational_certificate', 'Educational Certificate', 0, 0, 120, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(126, NULL, 'experience_certificate', 'Experience Certificate', 0, 0, 130, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(127, NULL, 'training_certificate', 'Training Certificate', 1, 365, 140, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(128, NULL, 'skill_certificate', 'Skill Certificate', 1, 365, 150, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(129, NULL, 'increment_letter', 'Increment Letter', 0, 0, 160, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(130, NULL, 'promotion_letter', 'Promotion Letter', 0, 0, 170, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(131, NULL, 'warning_letter', 'Warning Letter', 0, 0, 180, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(132, NULL, 'transfer_letter', 'Transfer Letter', 0, 0, 190, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(133, NULL, 'salary_certificate', 'Salary Certificate', 0, 0, 200, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(134, NULL, 'noc', 'NOC', 0, 0, 210, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(135, NULL, 'resignation', 'Resignation', 0, 0, 220, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(136, NULL, 'termination', 'Termination', 0, 0, 230, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(137, NULL, 'final_settlement', 'Final Settlement', 0, 0, 240, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(138, NULL, 'client_approval', 'Client Approval', 0, 0, 250, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(139, NULL, 'site_access', 'Site Access Document', 1, 30, 260, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(140, NULL, 'other_hr', 'Other HR Document', 0, 0, 270, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_document_expiry_alerts`
--

CREATE TABLE `hr_document_expiry_alerts` (
  `id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `alert_type` varchar(30) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `notified_user_id` int(10) UNSIGNED DEFAULT NULL,
  `notified_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_assets`
--

CREATE TABLE `hr_employee_assets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED DEFAULT NULL,
  `asset_type` enum('laptop','mobile','sim','access_card','uniform','keys','equipment','other') NOT NULL DEFAULT 'other',
  `asset_tag` varchar(80) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `serial_number` varchar(120) DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('assigned','returned','lost','damaged') NOT NULL DEFAULT 'assigned',
  `notes` text DEFAULT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_assignments`
--

CREATE TABLE `hr_employee_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `project_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `assignment_type` enum('primary','secondary','temporary','project','replacement') NOT NULL DEFAULT 'primary',
  `assignment_status` enum('draft','active','completed','cancelled','transferred') NOT NULL DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `allocation_pct` decimal(5,2) NOT NULL DEFAULT 100.00,
  `role_on_site` varchar(80) DEFAULT NULL,
  `client_name` varchar(120) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_assignment_history`
--

CREATE TABLE `hr_employee_assignment_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL,
  `snapshot` longtext DEFAULT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_loans`
--

CREATE TABLE `hr_employee_loans` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `loan_number` varchar(40) DEFAULT NULL,
  `principal` decimal(14,2) NOT NULL,
  `interest_rate` decimal(6,2) NOT NULL DEFAULT 0.00,
  `tenure_months` int(11) NOT NULL DEFAULT 12,
  `monthly_installment` decimal(14,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','active','closed') NOT NULL DEFAULT 'pending',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_requests`
--

CREATE TABLE `hr_employee_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_number` varchar(40) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `module` enum('leave','overtime','advance','loan','transfer','regularization','increment','settlement','payroll','onboarding','other') NOT NULL DEFAULT 'other',
  `request_type` varchar(60) DEFAULT NULL,
  `source_table` varchar(60) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT NULL,
  `status` enum('draft','submitted','pending','approved','rejected','returned','cancelled') NOT NULL DEFAULT 'submitted',
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `submitted_by` int(10) UNSIGNED DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `current_step_no` int(11) NOT NULL DEFAULT 0,
  `approval_request_id` int(10) UNSIGNED DEFAULT NULL,
  `metadata` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_shifts`
--

CREATE TABLE `hr_employee_shifts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `shift_template_id` int(10) UNSIGNED NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_statuses`
--

CREATE TABLE `hr_employee_statuses` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `legacy_status` enum('active','on_leave','inactive') DEFAULT NULL,
  `allows_attendance` tinyint(1) NOT NULL DEFAULT 1,
  `allows_leave` tinyint(1) NOT NULL DEFAULT 1,
  `allows_payroll` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_employee_statuses`
--

INSERT INTO `hr_employee_statuses` (`id`, `company_id`, `code`, `name`, `legacy_status`, `allows_attendance`, `allows_leave`, `allows_payroll`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(2, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(3, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(4, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(5, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(6, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(7, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(8, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(9, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(10, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(11, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(12, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(13, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(14, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(15, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(16, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(17, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(18, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(19, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(20, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(21, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(22, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(23, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(24, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(25, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(26, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(27, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(28, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(29, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(30, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(31, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(32, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(33, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(34, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(35, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(36, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(37, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(38, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(39, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(40, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(41, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(42, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(43, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(44, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(45, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(46, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(47, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(48, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(49, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(50, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(51, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(52, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(53, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(54, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(55, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(56, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(57, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(58, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(59, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(60, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(61, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(62, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(63, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(64, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(65, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(66, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(67, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(68, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(69, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(70, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(71, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(72, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(73, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(74, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(75, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(76, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(77, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(78, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(79, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(80, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(81, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(82, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(83, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(84, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(85, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(86, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(87, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(88, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(89, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(90, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(91, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(92, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(93, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(94, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(95, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(96, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(97, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(98, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(99, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(100, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(101, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(102, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(103, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(104, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(105, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(106, NULL, 'draft', 'Draft', NULL, 0, 0, 0, 1, 5, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(107, NULL, 'pre_joining', 'Pre-Joining', NULL, 0, 0, 0, 1, 10, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(108, NULL, 'active', 'Active', 'active', 1, 1, 1, 1, 20, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(109, NULL, 'probation', 'Probation', 'active', 1, 1, 1, 1, 30, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(110, NULL, 'confirmed', 'Confirmed', 'active', 1, 1, 1, 1, 40, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(111, NULL, 'on_leave', 'On Leave', 'on_leave', 0, 1, 1, 1, 50, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(112, NULL, 'suspended', 'Suspended', 'active', 0, 0, 0, 1, 60, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(113, NULL, 'notice_period', 'Notice Period', 'active', 1, 0, 1, 1, 70, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(114, NULL, 'resigned', 'Resigned', NULL, 0, 0, 0, 1, 80, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(115, NULL, 'terminated', 'Terminated', NULL, 0, 0, 0, 1, 90, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(116, NULL, 'contract_completed', 'Contract Completed', NULL, 0, 0, 0, 1, 100, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(117, NULL, 'released', 'Released', NULL, 0, 0, 0, 1, 110, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(118, NULL, 'transferred', 'Transferred', NULL, 0, 0, 0, 1, 120, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(119, NULL, 'rejoined', 'Rejoined', 'active', 1, 1, 1, 1, 130, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(120, NULL, 'inactive', 'Inactive', 'inactive', 0, 0, 0, 1, 140, '2026-08-22 14:04:28', '2026-08-22 14:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_timeline`
--

CREATE TABLE `hr_employee_timeline` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `employment_period_id` int(10) UNSIGNED DEFAULT NULL,
  `event_type` varchar(60) NOT NULL,
  `event_code` varchar(60) DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `ref_module` varchar(40) DEFAULT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `metadata` longtext DEFAULT NULL,
  `event_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_transfers`
--

CREATE TABLE `hr_employee_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_number` varchar(40) DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `from_department_id` int(10) UNSIGNED DEFAULT NULL,
  `to_department_id` int(10) UNSIGNED DEFAULT NULL,
  `from_facility_id` int(10) UNSIGNED DEFAULT NULL,
  `to_facility_id` int(10) UNSIGNED DEFAULT NULL,
  `from_operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `to_operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `from_reporting_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `to_reporting_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `effective_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `approval_request_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employee_types`
--

CREATE TABLE `hr_employee_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_employee_types`
--

INSERT INTO `hr_employee_types` (`id`, `company_id`, `code`, `name`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(2, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(3, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(4, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(5, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(6, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(7, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(8, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(9, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(10, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(11, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(12, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(13, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(14, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(15, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(16, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(17, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(18, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(19, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(20, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(21, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(22, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(23, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(24, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(25, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(26, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(27, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(28, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(29, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(30, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(31, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(32, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(33, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(34, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(35, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(36, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(37, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(38, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(39, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(40, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(41, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(42, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(43, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(44, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(45, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(46, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(47, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(48, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(49, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(50, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(51, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(52, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(53, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(54, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(55, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(56, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(57, NULL, 'permanent', 'Permanent', NULL, 10, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(58, NULL, 'fixed_term', 'Fixed-Term Contract', NULL, 20, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(59, NULL, 'temporary', 'Temporary', NULL, 30, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(60, NULL, 'part_time', 'Part-Time', NULL, 40, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(61, NULL, 'probation', 'Probation', NULL, 50, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(62, NULL, 'intern', 'Intern / Trainee', NULL, 60, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(63, NULL, 'consultant', 'Consultant', NULL, 70, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(64, NULL, 'outsourced', 'Outsourced / External Contract', NULL, 80, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `hr_employment_contracts`
--

CREATE TABLE `hr_employment_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_type` varchar(60) NOT NULL DEFAULT 'fixed_term',
  `contract_number` varchar(60) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `contract_duration_months` int(11) DEFAULT NULL,
  `expected_release_date` date DEFAULT NULL,
  `actual_release_date` date DEFAULT NULL,
  `notice_period_days` int(11) DEFAULT NULL,
  `contract_status` enum('draft','pending_approval','upcoming','active','expiring_soon','renewal_pending','renewed','completed','released','terminated','cancelled') NOT NULL DEFAULT 'draft',
  `renewal_status` varchar(40) DEFAULT NULL,
  `payroll_responsibility` enum('our_company','supplier','external','consultant','none') NOT NULL DEFAULT 'our_company',
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `supplier_employee_ref` varchar(80) DEFAULT NULL,
  `supplier_contract_ref` varchar(80) DEFAULT NULL,
  `billing_type` enum('monthly_rate','daily_rate','hourly_rate','shift_rate','attendance_day','timesheet','supplier_invoice') DEFAULT NULL,
  `cost_rate` decimal(14,2) DEFAULT NULL,
  `billing_rate` decimal(14,2) DEFAULT NULL,
  `client_billing_rate` decimal(14,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employment_contract_history`
--

CREATE TABLE `hr_employment_contract_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL,
  `snapshot` longtext DEFAULT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employment_periods`
--

CREATE TABLE `hr_employment_periods` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `operating_company_id` int(10) UNSIGNED DEFAULT NULL,
  `period_number` int(11) NOT NULL DEFAULT 1,
  `joining_date` date DEFAULT NULL,
  `confirmation_date` date DEFAULT NULL,
  `last_working_date` date DEFAULT NULL,
  `status_id` int(10) UNSIGNED DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_employment_sources`
--

CREATE TABLE `hr_employment_sources` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `payroll_responsibility` enum('our_company','supplier','external','consultant','none') NOT NULL DEFAULT 'our_company',
  `description` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_employment_sources`
--

INSERT INTO `hr_employment_sources` (`id`, `company_id`, `code`, `name`, `payroll_responsibility`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(2, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(3, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(4, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(5, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:05:53', '2026-08-22 13:05:53'),
(6, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(7, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(8, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(9, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(10, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:13:38', '2026-08-22 13:13:38'),
(11, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(12, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(13, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(14, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(15, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:13:49', '2026-08-22 13:13:49'),
(16, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(17, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(18, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(19, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(20, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:26:52', '2026-08-22 13:26:52'),
(21, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(22, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(23, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(24, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(25, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:35:29', '2026-08-22 13:35:29'),
(26, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(27, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(28, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(29, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(30, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(31, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(32, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(33, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(34, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(35, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(36, NULL, 'direct', 'Direct Employee', 'our_company', NULL, 10, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(37, NULL, 'internal_contract', 'Internal Contract Employee', 'our_company', NULL, 20, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(38, NULL, 'external_contract', 'External Contract Employee', 'supplier', NULL, 30, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(39, NULL, 'outsourced', 'Outsourced Manpower', 'supplier', NULL, 40, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28'),
(40, NULL, 'consultant', 'Consultant', 'consultant', NULL, 50, 1, '2026-08-22 14:04:28', '2026-08-22 14:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `hr_expense_claims`
--

CREATE TABLE `hr_expense_claims` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(80) DEFAULT 'general',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `journal_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_final_settlements`
--

CREATE TABLE `hr_final_settlements` (
  `id` int(10) UNSIGNED NOT NULL,
  `settlement_number` varchar(40) DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `employment_period_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `last_working_date` date NOT NULL,
  `status` enum('draft','calculated','pending_approval','approved','paid','cancelled') NOT NULL DEFAULT 'draft',
  `total_earnings` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(14,2) NOT NULL DEFAULT 0.00,
  `journal_entry_id` int(10) UNSIGNED DEFAULT NULL,
  `approval_request_id` int(10) UNSIGNED DEFAULT NULL,
  `calculated_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_final_settlement_lines`
--

CREATE TABLE `hr_final_settlement_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `settlement_id` int(10) UNSIGNED NOT NULL,
  `component_code` varchar(30) DEFAULT NULL,
  `component_name` varchar(120) NOT NULL,
  `component_type` enum('earning','deduction') NOT NULL DEFAULT 'earning',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_grades`
--

CREATE TABLE `hr_grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_leave_balances`
--

CREATE TABLE `hr_leave_balances` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `balance` decimal(6,2) NOT NULL DEFAULT 0.00,
  `used` decimal(6,2) NOT NULL DEFAULT 0.00,
  `employee_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `balance_year` smallint(4) NOT NULL DEFAULT 0,
  `opening_balance` decimal(6,2) NOT NULL DEFAULT 0.00,
  `accrued` decimal(6,2) NOT NULL DEFAULT 0.00,
  `pending` decimal(6,2) NOT NULL DEFAULT 0.00,
  `adjusted` decimal(6,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_leave_policies`
--

CREATE TABLE `hr_leave_policies` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `grade_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_type_id` int(10) UNSIGNED DEFAULT NULL,
  `annual_entitlement` decimal(6,2) NOT NULL DEFAULT 0.00,
  `accrual_per_month` decimal(6,2) DEFAULT NULL,
  `max_balance` decimal(6,2) DEFAULT NULL,
  `min_service_days` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_leave_policies`
--

INSERT INTO `hr_leave_policies` (`id`, `company_id`, `leave_type_id`, `grade_id`, `employee_type_id`, `annual_entitlement`, `accrual_per_month`, `max_balance`, `min_service_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, NULL, NULL, 30.00, 2.50, 30.00, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(2, NULL, 4, NULL, NULL, 30.00, 2.50, 30.00, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(4, NULL, 2, NULL, NULL, 14.00, 1.17, 14.00, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(5, NULL, 5, NULL, NULL, 14.00, 1.17, 14.00, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(7, NULL, 8, NULL, NULL, 30.00, 2.50, 30.00, 0, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(8, NULL, 9, NULL, NULL, 14.00, 1.17, 14.00, 0, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_leave_requests`
--

CREATE TABLE `hr_leave_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` decimal(6,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_by` int(10) UNSIGNED DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `employee_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `days_requested` decimal(5,2) NOT NULL DEFAULT 0.00,
  `half_day` enum('none','first_half','second_half') NOT NULL DEFAULT 'none',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_leave_request_history`
--

CREATE TABLE `hr_leave_request_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL,
  `snapshot` longtext DEFAULT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_leave_types`
--

CREATE TABLE `hr_leave_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `days_per_year` decimal(6,2) DEFAULT 0.00,
  `carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `max_days_per_year` decimal(6,2) DEFAULT NULL,
  `allow_half_day` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hr_leave_types`
--

INSERT INTO `hr_leave_types` (`id`, `code`, `name`, `days_per_year`, `carry_forward`, `status`, `company_id`, `is_paid`, `requires_approval`, `max_days_per_year`, `allow_half_day`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'annual', 'Annual Leave', 21.00, 0, 'active', NULL, 1, 1, NULL, 1, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(2, 'sick', 'Sick Leave', 14.00, 0, 'active', NULL, 1, 1, NULL, 1, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(3, 'unpaid', 'Unpaid Leave', 0.00, 0, 'active', NULL, 1, 1, NULL, 1, 0, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(4, 'annual', 'Annual Leave', 0.00, 1, 'active', NULL, 1, 1, 30.00, 1, 10, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(5, 'sick', 'Sick Leave', 0.00, 0, 'active', NULL, 1, 1, 14.00, 1, 20, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(6, 'unpaid', 'Unpaid Leave', 0.00, 0, 'active', NULL, 0, 1, NULL, 1, 30, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(7, 'emergency', 'Emergency Leave', 0.00, 0, 'active', NULL, 1, 1, 5.00, 0, 40, 1, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(8, 'annual', 'Annual Leave', 0.00, 1, 'active', NULL, 1, 1, 30.00, 1, 10, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(9, 'sick', 'Sick Leave', 0.00, 0, 'active', NULL, 1, 1, 14.00, 1, 20, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(10, 'unpaid', 'Unpaid Leave', 0.00, 0, 'active', NULL, 0, 1, NULL, 1, 30, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(11, 'emergency', 'Emergency Leave', 0.00, 0, 'active', NULL, 1, 1, 5.00, 0, 40, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_loan_installments`
--

CREATE TABLE `hr_loan_installments` (
  `id` int(10) UNSIGNED NOT NULL,
  `loan_id` int(10) UNSIGNED NOT NULL,
  `installment_no` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `interest_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','skipped') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_manpower_requirements`
--

CREATE TABLE `hr_manpower_requirements` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `designation_id` int(10) UNSIGNED DEFAULT NULL,
  `required_headcount` int(11) NOT NULL DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','fulfilled','cancelled') NOT NULL DEFAULT 'active',
  `remarks` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_onboarding_checklists`
--

CREATE TABLE `hr_onboarding_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `employee_type_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_onboarding_checklists`
--

INSERT INTO `hr_onboarding_checklists` (`id`, `company_id`, `code`, `name`, `employee_type_id`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'DEFAULT', 'Standard Onboarding', NULL, 1, 10, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(2, NULL, 'DEFAULT', 'Standard Onboarding', NULL, 1, 10, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_onboarding_instances`
--

CREATE TABLE `hr_onboarding_instances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `employment_period_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','cancelled') NOT NULL DEFAULT 'not_started',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_onboarding_tasks`
--

CREATE TABLE `hr_onboarding_tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `assignee_role` enum('hr','manager','employee','it') NOT NULL DEFAULT 'hr',
  `due_days_from_joining` int(11) NOT NULL DEFAULT 7,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_onboarding_tasks`
--

INSERT INTO `hr_onboarding_tasks` (`id`, `checklist_id`, `code`, `name`, `description`, `assignee_role`, `due_days_from_joining`, `is_mandatory`, `sort_order`, `is_active`) VALUES
(1, 1, 'DOC_QID', 'Collect QID copy', NULL, 'hr', 3, 1, 10, 1),
(2, 1, 'DOC_CONTRACT', 'Employment contract signed', NULL, 'hr', 5, 1, 20, 1),
(3, 1, 'IT_ACCESS', 'Create system access', NULL, 'it', 2, 1, 30, 1),
(4, 1, 'BANK_DETAILS', 'Collect bank / IBAN details', NULL, 'hr', 7, 1, 40, 1),
(5, 1, 'ORIENTATION', 'HR orientation session', NULL, 'hr', 14, 1, 50, 1),
(8, 1, 'DOC_QID', 'Collect QID copy', NULL, 'hr', 3, 1, 10, 1),
(9, 2, 'DOC_QID', 'Collect QID copy', NULL, 'hr', 3, 1, 10, 1),
(10, 1, 'DOC_CONTRACT', 'Employment contract signed', NULL, 'hr', 5, 1, 20, 1),
(11, 2, 'DOC_CONTRACT', 'Employment contract signed', NULL, 'hr', 5, 1, 20, 1),
(12, 1, 'IT_ACCESS', 'Create system access', NULL, 'it', 2, 1, 30, 1),
(13, 2, 'IT_ACCESS', 'Create system access', NULL, 'it', 2, 1, 30, 1),
(14, 1, 'BANK_DETAILS', 'Collect bank / IBAN details', NULL, 'hr', 7, 1, 40, 1),
(15, 2, 'BANK_DETAILS', 'Collect bank / IBAN details', NULL, 'hr', 7, 1, 40, 1),
(16, 1, 'ORIENTATION', 'HR orientation session', NULL, 'hr', 14, 1, 50, 1),
(17, 2, 'ORIENTATION', 'HR orientation session', NULL, 'hr', 14, 1, 50, 1);

-- --------------------------------------------------------

--
-- Table structure for table `hr_onboarding_task_status`
--

CREATE TABLE `hr_onboarding_task_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `instance_id` int(10) UNSIGNED NOT NULL,
  `task_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','done','skipped','na') NOT NULL DEFAULT 'pending',
  `completed_by` int(10) UNSIGNED DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_allocations`
--

CREATE TABLE `hr_payroll_allocations` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_line_id` int(10) UNSIGNED NOT NULL,
  `cost_center_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `allocation_pct` decimal(6,2) NOT NULL DEFAULT 100.00,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_groups`
--

CREATE TABLE `hr_payroll_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_lines`
--

CREATE TABLE `hr_payroll_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_run_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `structure_id` int(10) UNSIGNED DEFAULT NULL,
  `emp_code` varchar(40) DEFAULT NULL,
  `employee_name` varchar(160) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `wps_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `working_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `paid_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `gross_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_earnings` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('included','excluded','error') NOT NULL DEFAULT 'included',
  `error_message` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_line_components`
--

CREATE TABLE `hr_payroll_line_components` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_line_id` int(10) UNSIGNED NOT NULL,
  `component_id` int(10) UNSIGNED DEFAULT NULL,
  `component_code` varchar(30) DEFAULT NULL,
  `component_name` varchar(120) DEFAULT NULL,
  `component_type` enum('earning','deduction') NOT NULL DEFAULT 'earning',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_locks`
--

CREATE TABLE `hr_payroll_locks` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_run_id` int(10) UNSIGNED NOT NULL,
  `action` enum('lock','unlock') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `performed_by` int(10) UNSIGNED DEFAULT NULL,
  `performed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_payroll_runs`
--

CREATE TABLE `hr_payroll_runs` (
  `id` int(10) UNSIGNED NOT NULL,
  `run_number` varchar(40) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `payroll_group_id` int(10) UNSIGNED DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `pay_date` date DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `employee_count` int(11) NOT NULL DEFAULT 0,
  `total_gross` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_net` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','calculated','approved','locked','posted','cancelled') NOT NULL DEFAULT 'draft',
  `validation_summary` text DEFAULT NULL,
  `journal_entry_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_performance_goals`
--

CREATE TABLE `hr_performance_goals` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `kpi_target` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `progress_pct` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_performance_reviews`
--

CREATE TABLE `hr_performance_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reviewer_user_id` int(10) UNSIGNED DEFAULT NULL,
  `period_label` varchar(80) NOT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `improvements` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('draft','submitted','acknowledged') NOT NULL DEFAULT 'draft',
  `review_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salary_advances`
--

CREATE TABLE `hr_salary_advances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `request_date` date NOT NULL,
  `recovery_start_date` date DEFAULT NULL,
  `installments` int(11) NOT NULL DEFAULT 1,
  `installment_amount` decimal(14,2) DEFAULT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','active','closed') NOT NULL DEFAULT 'pending',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salary_components`
--

CREATE TABLE `hr_salary_components` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `component_type` enum('earning','deduction') NOT NULL DEFAULT 'earning',
  `calculation_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_salary_components`
--

INSERT INTO `hr_salary_components` (`id`, `company_id`, `code`, `name`, `component_type`, `calculation_type`, `is_taxable`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'BASIC', 'Basic Salary', 'earning', 'fixed', 1, 1, 10, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(2, NULL, 'HRA', 'Housing Allowance', 'earning', 'fixed', 1, 1, 20, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(3, NULL, 'TRANSPORT', 'Transport Allowance', 'earning', 'fixed', 1, 1, 30, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(4, NULL, 'OT', 'Overtime', 'earning', 'fixed', 1, 1, 40, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(5, NULL, 'GOSI', 'GOSI Deduction', 'deduction', 'percentage', 1, 1, 100, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(6, NULL, 'LOAN_DED', 'Loan Deduction', 'deduction', 'fixed', 1, 1, 110, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(7, NULL, 'ADV_DED', 'Advance Deduction', 'deduction', 'fixed', 1, 1, 120, '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(8, NULL, 'BASIC', 'Basic Salary', 'earning', 'fixed', 1, 1, 10, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(9, NULL, 'HRA', 'Housing Allowance', 'earning', 'fixed', 1, 1, 20, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(10, NULL, 'TRANSPORT', 'Transport Allowance', 'earning', 'fixed', 1, 1, 30, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(11, NULL, 'OT', 'Overtime', 'earning', 'fixed', 1, 1, 40, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(12, NULL, 'GOSI', 'GOSI Deduction', 'deduction', 'percentage', 1, 1, 100, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(13, NULL, 'LOAN_DED', 'Loan Deduction', 'deduction', 'fixed', 1, 1, 110, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(14, NULL, 'ADV_DED', 'Advance Deduction', 'deduction', 'fixed', 1, 1, 120, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_salary_revisions`
--

CREATE TABLE `hr_salary_revisions` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `structure_id` int(10) UNSIGNED DEFAULT NULL,
  `revision_type` varchar(40) NOT NULL DEFAULT 'increment',
  `old_gross` decimal(14,2) DEFAULT NULL,
  `new_gross` decimal(14,2) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `snapshot` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salary_structures`
--

CREATE TABLE `hr_salary_structures` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `gross_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'QAR',
  `status` enum('draft','active','superseded') NOT NULL DEFAULT 'active',
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salary_structure_lines`
--

CREATE TABLE `hr_salary_structure_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `structure_id` int(10) UNSIGNED NOT NULL,
  `component_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `percentage` decimal(6,2) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_settings`
--

CREATE TABLE `hr_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_settings`
--

INSERT INTO `hr_settings` (`id`, `company_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, NULL, 'gl_payroll_expense', '5300', '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(2, NULL, 'gl_salary_payable', '2310', '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(3, NULL, 'gl_employee_advance', '1310', '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(4, NULL, 'gl_employee_loan', '1315', '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(5, NULL, 'gl_bank', '1200', '2026-08-22 13:42:11', '2026-08-22 13:42:11'),
(6, NULL, 'gl_payroll_expense', '5300', '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(7, NULL, 'gl_salary_payable', '2310', '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(8, NULL, 'gl_employee_advance', '1310', '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(9, NULL, 'gl_employee_loan', '1315', '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(10, NULL, 'gl_bank', '1200', '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_shifts`
--

CREATE TABLE `hr_shifts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_minutes` int(11) NOT NULL DEFAULT 0,
  `grace_in_minutes` int(11) NOT NULL DEFAULT 0,
  `grace_out_minutes` int(11) NOT NULL DEFAULT 0,
  `is_overnight` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_shifts`
--

INSERT INTO `hr_shifts` (`id`, `company_id`, `code`, `name`, `start_time`, `end_time`, `break_minutes`, `grace_in_minutes`, `grace_out_minutes`, `is_overnight`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'DAY', 'Day Shift', '08:00:00', '17:00:00', 60, 15, 15, 0, 1, '2026-08-22 13:26:53', '2026-08-22 13:26:53'),
(2, NULL, 'EVENING', 'Evening Shift', '14:00:00', '23:00:00', 30, 15, 15, 0, 1, '2026-08-22 13:26:53', '2026-08-22 13:26:53'),
(3, NULL, 'NIGHT', 'Night Shift', '22:00:00', '06:00:00', 30, 15, 15, 1, 1, '2026-08-22 13:26:53', '2026-08-22 13:26:53'),
(4, NULL, 'DAY', 'Day Shift', '08:00:00', '17:00:00', 60, 15, 15, 0, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(5, NULL, 'EVENING', 'Evening Shift', '14:00:00', '23:00:00', 30, 15, 15, 0, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(6, NULL, 'NIGHT', 'Night Shift', '22:00:00', '06:00:00', 30, 15, 15, 1, 1, '2026-08-22 13:35:30', '2026-08-22 13:35:30'),
(7, NULL, 'DAY', 'Day Shift', '08:00:00', '17:00:00', 60, 15, 15, 0, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(8, NULL, 'EVENING', 'Evening Shift', '14:00:00', '23:00:00', 30, 15, 15, 0, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(9, NULL, 'NIGHT', 'Night Shift', '22:00:00', '06:00:00', 30, 15, 15, 1, 1, '2026-08-22 13:35:37', '2026-08-22 13:35:37'),
(10, NULL, 'DAY', 'Day Shift', '08:00:00', '17:00:00', 60, 15, 15, 0, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(11, NULL, 'EVENING', 'Evening Shift', '14:00:00', '23:00:00', 30, 15, 15, 0, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(12, NULL, 'NIGHT', 'Night Shift', '22:00:00', '06:00:00', 30, 15, 15, 1, 1, '2026-08-22 13:42:10', '2026-08-22 13:42:10'),
(13, NULL, 'DAY', 'Day Shift', '08:00:00', '17:00:00', 60, 15, 15, 0, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(14, NULL, 'EVENING', 'Evening Shift', '14:00:00', '23:00:00', 30, 15, 15, 0, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29'),
(15, NULL, 'NIGHT', 'Night Shift', '22:00:00', '06:00:00', 30, 15, 15, 1, 1, '2026-08-22 14:04:29', '2026-08-22 14:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `hr_shift_assignments`
--

CREATE TABLE `hr_shift_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `shift_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_shift_templates`
--

CREATE TABLE `hr_shift_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_night` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_teams`
--

CREATE TABLE `hr_teams` (
  `id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_wps_batches`
--

CREATE TABLE `hr_wps_batches` (
  `id` int(10) UNSIGNED NOT NULL,
  `batch_number` varchar(40) NOT NULL,
  `payroll_run_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `record_count` int(11) NOT NULL DEFAULT 0,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','generated','submitted','failed') NOT NULL DEFAULT 'draft',
  `file_name` varchar(255) DEFAULT NULL,
  `file_content` longtext DEFAULT NULL,
  `generated_by` int(10) UNSIGNED DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_wps_records`
--

CREATE TABLE `hr_wps_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `batch_id` int(10) UNSIGNED NOT NULL,
  `payroll_line_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `emp_code` varchar(40) DEFAULT NULL,
  `employee_name` varchar(160) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `qid_number` varchar(30) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `record_type` varchar(20) NOT NULL DEFAULT 'salary',
  `status` enum('valid','invalid','excluded') NOT NULL DEFAULT 'valid',
  `validation_message` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_wps_settings`
--

CREATE TABLE `hr_wps_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `employer_eid` varchar(30) DEFAULT NULL,
  `payer_eid` varchar(30) DEFAULT NULL,
  `payer_qid` varchar(20) DEFAULT NULL,
  `payer_iban` varchar(50) DEFAULT NULL,
  `bank_code` varchar(10) DEFAULT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `file_format` varchar(20) NOT NULL DEFAULT 'csv',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `incident_date` date NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','investigating','resolved','closed') NOT NULL DEFAULT 'open',
  `reported_by` int(10) UNSIGNED NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_checklists`
--

CREATE TABLE `inspection_checklists` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL DEFAULT 0,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `frequency` varchar(20) NOT NULL DEFAULT 'regular' COMMENT 'weekly|monthly|regular',
  `inspection_date` date NOT NULL,
  `inspector_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_progress','passed','failed') NOT NULL DEFAULT 'pending',
  `score` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Score 0-100',
  `notes` text DEFAULT NULL,
  `overall_remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `damage_amount` decimal(14,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_checklists`
--

INSERT INTO `inspection_checklists` (`id`, `facility_id`, `unit_id`, `title`, `type`, `frequency`, `inspection_date`, `inspector_name`, `status`, `score`, `notes`, `overall_remarks`, `created_by`, `completed_by`, `completed_at`, `created_at`, `updated_at`, `damage_amount`) VALUES
(1, 9001, NULL, 'Routine Property Inspection', 'General Facility', 'weekly', '2026-08-08', '', 'pending', NULL, '', NULL, 1, NULL, NULL, '2026-08-08 03:27:04', '2026-08-08 03:30:43', NULL),
(2, 9001, NULL, 'Routine Property Inspection', 'General Facility', 'weekly', '2026-08-08', '', 'pending', NULL, '', NULL, 1, NULL, NULL, '2026-08-08 03:31:32', NULL, NULL),
(3, 0, NULL, 'dffd', 'Fire Safety', 'weekly', '2026-08-08', '', 'pending', NULL, '', NULL, 1, NULL, NULL, '2026-08-08 15:11:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inspection_items`
--

CREATE TABLE `inspection_items` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL DEFAULT 0,
  `item_text` varchar(500) NOT NULL,
  `result` enum('pending','pass','fail','na') NOT NULL DEFAULT 'pending',
  `remarks` varchar(500) DEFAULT NULL,
  `photos_json` text DEFAULT NULL COMMENT 'JSON array of upload paths',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_items`
--

INSERT INTO `inspection_items` (`id`, `checklist_id`, `item_text`, `result`, `remarks`, `photos_json`, `sort_order`) VALUES
(1, 0, 'General building condition walkthrough', 'pending', NULL, NULL, 0),
(2, 2, 'General building condition walkthrough', 'pending', NULL, NULL, 0),
(3, 2, 'Lighting in common areas', 'pending', NULL, NULL, 0),
(4, 2, 'Plumbing — visible leaks, water pressure', 'pending', NULL, NULL, 0),
(5, 2, 'Doors and locks — common access points', 'pending', NULL, NULL, 0),
(6, 2, 'Signage and wayfinding', 'pending', NULL, NULL, 0),
(7, 2, 'Housekeeping standards', 'pending', NULL, NULL, 0),
(8, 2, 'Health &amp; safety hazards noted', 'pending', NULL, NULL, 0),
(9, 2, 'Tenant / occupant feedback logged', 'pending', NULL, NULL, 0),
(10, 3, 'General building condition walkthrough', 'pending', NULL, NULL, 0),
(11, 3, 'Lighting in common areas', 'pending', NULL, NULL, 1),
(12, 3, 'Plumbing — visible leaks, water pressure', 'pending', NULL, NULL, 2),
(13, 3, 'Doors and locks — common access points', 'pending', NULL, NULL, 3),
(14, 3, 'Signage and wayfinding', 'pending', NULL, NULL, 4),
(15, 3, 'Housekeeping standards', 'pending', NULL, NULL, 5),
(16, 3, 'Health &amp; safety hazards noted', 'pending', NULL, NULL, 6),
(17, 3, 'Tenant / occupant feedback logged', 'pending', NULL, NULL, 7);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'pcs',
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `min_quantity` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `bill_to_name` varchar(200) DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `estimation_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_type` enum('contract','work_order','adhoc','advance','partial','final','monthly','quarterly','annual','wo_based') NOT NULL DEFAULT 'adhoc',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pending_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_partial` tinyint(1) NOT NULL DEFAULT 0,
  `currency` varchar(10) NOT NULL DEFAULT 'QAR',
  `status` enum('draft','sent','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `paid_by` int(10) UNSIGNED DEFAULT NULL,
  `bill_to_email` varchar(120) DEFAULT NULL,
  `bill_to_phone` varchar(30) DEFAULT NULL,
  `bill_to_address` varchar(255) DEFAULT NULL,
  `service_customer_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_edit_logs`
--

CREATE TABLE `invoice_edit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'update',
  `summary` text DEFAULT NULL,
  `changes_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `line_type` enum('service','labor','material','amc','rental','other') NOT NULL DEFAULT 'service',
  `description` varchar(500) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `unit_cost_internal` decimal(14,2) DEFAULT NULL COMMENT 'Internal cost — not on client PDF',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','bank','card','cheque','online') NOT NULL DEFAULT 'bank',
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jc_attachments`
--

CREATE TABLE `jc_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `jc_id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jc_materials`
--

CREATE TABLE `jc_materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `jc_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `total_cost` decimal(12,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_cards`
--

CREATE TABLE `job_cards` (
  `id` int(10) UNSIGNED NOT NULL,
  `jc_number` varchar(30) NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `supervisor_id` int(10) UNSIGNED DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_hours` decimal(5,2) DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','in_progress','completed','approved') DEFAULT 'draft',
  `labor_hours` decimal(6,2) DEFAULT 0.00,
  `completion_notes` text DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `qa_notes` text DEFAULT NULL,
  `before_image` varchar(255) DEFAULT NULL,
  `after_image` varchar(255) DEFAULT NULL,
  `customer_signature` varchar(255) DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_cards`
--

INSERT INTO `job_cards` (`id`, `jc_number`, `wo_id`, `supervisor_id`, `scheduled_date`, `scheduled_hours`, `assigned_to`, `description`, `status`, `labor_hours`, `completion_notes`, `technician_notes`, `qa_notes`, `before_image`, `after_image`, `customer_signature`, `approved_by`, `approved_at`, `completed_at`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9001, 'JC-DEMO-9001', 9001, 9002, '2026-08-03', NULL, 9003, 'Replace washer and reseal kitchen sink trap.', 'in_progress', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `landlords`
--

CREATE TABLE `landlords` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(200) NOT NULL,
  `full_name_ar` varchar(200) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone2` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `nationality` varchar(80) DEFAULT NULL,
  `id_type` varchar(30) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `id_expiry` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `bank_account` varchar(80) DEFAULT NULL,
  `bank_iban` varchar(50) DEFAULT NULL,
  `commission_pct` decimal(5,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlords`
--

INSERT INTO `landlords` (`id`, `company_id`, `full_name`, `full_name_ar`, `phone`, `phone2`, `email`, `nationality`, `id_type`, `id_number`, `id_expiry`, `address`, `bank_name`, `bank_account`, `bank_iban`, `commission_pct`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9002, 1, 'Maryam Mohd A Abdulkarim', NULL, '+97455156111', NULL, NULL, NULL, 'QID', '24063400484', '2028-11-05', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9003, 1, 'Mohd Ismail M Mandani Al Emadi', NULL, '+97455520033', NULL, NULL, NULL, 'QID', '25963400573', '2031-03-21', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9004, 1, 'Mahmoud Ismail M Mandani Al Emadi', NULL, '+97455500181', NULL, NULL, NULL, 'QID', '27163401219', '2031-10-13', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9005, 1, 'AbdulHameed Esmaeil M Mandani Al Emadi', NULL, '+97455564261', NULL, NULL, NULL, 'QID', '26363400630', '2030-05-26', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9006, 1, 'Amina Ismail M Mandani Al Emadi', NULL, '+97455528520', NULL, NULL, NULL, 'QID', '26863401351', '2029-08-03', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9007, 1, 'Maha Esmael M Mandani Al Emadi', NULL, '+97433567777', NULL, NULL, NULL, 'QID', '27363402669', '2032-02-27', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9008, 1, 'Fatima Esmael M Mandani Al Emadi', NULL, '+97455525130', NULL, NULL, NULL, 'QID', '26063401208', '2028-11-03', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9009, 1, 'Shaikha Esmael M Mandani Al Emadi', NULL, '+97455549372', NULL, NULL, NULL, 'QID', '26263401227', '2028-01-08', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9010, 1, 'Sumaya Ismail M Mandani Al Emadi', NULL, '+97466667473', NULL, NULL, NULL, 'QID', '26663401169', '2029-12-23', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9011, 1, 'Aisha Ismail M Mandani Al Emadi', NULL, '+97455818443', NULL, NULL, NULL, 'QID', '25663401124', '2032-03-01', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 21:51:44', '2026-08-17 21:51:44', NULL),
(9012, 1, 'Mohammed Mandani Al Emadi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Code: MOHD', '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL),
(9013, 1, 'Legal Heirs of Ismail Mandani', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Code: IM', '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL),
(9014, 1, 'Badriya Al Emadi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Code: BDR', '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL),
(9015, 1, 'Amina & Maha Ismail Mandani Al Emadi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Code: A&M', '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL),
(9016, 1, 'Hilal Sharq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Code: HS', '2026-08-17 22:08:44', '2026-08-17 22:08:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `landlord_payouts`
--

CREATE TABLE `landlord_payouts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `landlord_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `gross_rent` decimal(14,2) DEFAULT NULL,
  `commission` decimal(14,2) DEFAULT NULL,
  `deductions` decimal(14,2) DEFAULT NULL,
  `net_amount` decimal(14,2) DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(80) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lease_amendments`
--

CREATE TABLE `lease_amendments` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `new_rent` decimal(14,2) DEFAULT NULL,
  `new_end_date` date DEFAULT NULL,
  `effective_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lease_contracts`
--

CREATE TABLE `lease_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_number` varchar(30) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `tenant_qid` varchar(30) DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `contract_kind` varchar(20) NOT NULL DEFAULT 'standard',
  `plate_number` varchar(30) DEFAULT NULL,
  `vehicle_type` varchar(80) DEFAULT NULL,
  `vehicle_description` varchar(120) DEFAULT NULL,
  `title_deed_no` varchar(50) DEFAULT NULL,
  `zone_no` varchar(20) DEFAULT NULL,
  `street_no` varchar(20) DEFAULT NULL,
  `building_no` varchar(20) DEFAULT NULL,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','active','expired','terminated','renewed') NOT NULL DEFAULT 'draft',
  `signed_date` date DEFAULT NULL,
  `billing_start_date` date DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rent_amount` decimal(14,2) NOT NULL,
  `security_deposit` decimal(14,2) DEFAULT NULL,
  `payment_frequency` varchar(30) NOT NULL DEFAULT 'monthly',
  `payment_type` varchar(30) NOT NULL DEFAULT 'cheque',
  `payment_day` tinyint(3) UNSIGNED DEFAULT NULL,
  `late_penalty_pct` decimal(5,2) DEFAULT NULL,
  `grace_period_days` int(11) DEFAULT NULL,
  `discount_pct` decimal(5,2) DEFAULT NULL,
  `vat_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `vat_rate` decimal(5,2) DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `auto_generate_invoices` tinyint(1) NOT NULL DEFAULT 1,
  `custom_content_en` mediumtext DEFAULT NULL,
  `custom_content_ar` mediumtext DEFAULT NULL,
  `contract_terms` text DEFAULT NULL,
  `termination_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `has_free_period` tinyint(1) NOT NULL DEFAULT 0,
  `free_period_months` tinyint(3) UNSIGNED DEFAULT NULL,
  `free_period_desc` varchar(250) DEFAULT NULL,
  `free_period_position` enum('beginning','ending') DEFAULT 'beginning',
  `includes_utilities` tinyint(1) NOT NULL DEFAULT 0,
  `utilities_desc` varchar(250) DEFAULT NULL,
  `includes_furnished` tinyint(1) NOT NULL DEFAULT 0,
  `furnished_desc` varchar(250) DEFAULT NULL,
  `deposit_payment_method` varchar(30) DEFAULT NULL,
  `deposit_cheque_no` varchar(50) DEFAULT NULL,
  `prorata_basis` varchar(30) DEFAULT NULL,
  `parent_contract_id` int(10) UNSIGNED DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL,
  `edited_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lease_contracts`
--

INSERT INTO `lease_contracts` (`id`, `company_id`, `contract_number`, `tenant_id`, `tenant_qid`, `facility_id`, `unit_id`, `contract_kind`, `plate_number`, `vehicle_type`, `vehicle_description`, `title_deed_no`, `zone_no`, `street_no`, `building_no`, `template_id`, `status`, `signed_date`, `billing_start_date`, `start_date`, `end_date`, `rent_amount`, `security_deposit`, `payment_frequency`, `payment_type`, `payment_day`, `late_penalty_pct`, `grace_period_days`, `discount_pct`, `vat_applicable`, `vat_rate`, `auto_renew`, `auto_generate_invoices`, `custom_content_en`, `custom_content_ar`, `contract_terms`, `termination_reason`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `has_free_period`, `free_period_months`, `free_period_desc`, `free_period_position`, `includes_utilities`, `utilities_desc`, `includes_furnished`, `furnished_desc`, `deposit_payment_method`, `deposit_cheque_no`, `prorata_basis`, `parent_contract_id`, `edited_at`, `edited_by`) VALUES
(9001, 1, 'LC-DEMO-9001', 9001, NULL, 9001, 9001, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active', NULL, NULL, '2026-01-01', '2026-12-31', 8500.00, 2000.00, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9101, 1, 'LC-3101', 9101, NULL, 9101, 9101, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-06-01', '2027-05-31', 8000.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9102, 1, 'LC-2', 9101, NULL, 9101, 9102, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9103, 1, 'LC-3', 9101, NULL, 9101, 9103, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9104, 1, 'LC-4', 9101, NULL, 9101, 9104, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9105, 1, 'LC-5', 9101, NULL, 9101, 9105, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9106, 1, 'LC-6', 9101, NULL, 9101, 9106, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9107, 1, 'LC-7', 9101, NULL, 9101, 9107, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9108, 1, 'LC-8', 9101, NULL, 9101, 9108, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9109, 1, 'LC-9', 9101, NULL, 9101, 9109, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9110, 1, 'LC-10', 9101, NULL, 9101, 9110, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9111, 1, 'LC-11', 9101, NULL, 9101, 9111, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9112, 1, 'LC-12', 9198, NULL, 9112, 9215, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2028-07-31', 12500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9113, 1, 'LC-13', 9196, NULL, 9112, 9213, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 11200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9114, 1, 'LC-14', 9197, NULL, 9112, 9214, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-03-01', '2027-02-28', 9750.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9115, 1, 'LC-3201', 9102, '25935609211', 9102, 9117, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-12-01', '2026-12-31', 4250.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9116, 1, 'LC-3202', 9103, '26573600658', 9102, 9118, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 5750.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9117, 1, 'LC-3203', 9104, '26335604253', 9102, 9119, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-01', '2026-08-31', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9118, 1, 'LC-3204', 9105, '27135633074', 9102, 9120, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 2750.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9119, 1, 'LC-3205', 9106, '27135633074', 9102, 9121, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-07-01', '2026-06-30', 4200.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9120, 1, 'LC-3206', 9107, '26858600285', 9102, 9122, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-12-01', '2026-11-30', 3300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9121, 1, 'LC-3207', 9108, '26235601500', 9102, 9123, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2025-09-30', 4750.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9122, 1, 'LC-3208', 9109, '29535636844', 9102, 9124, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-01', '2027-04-30', 2200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9123, 1, 'LC-3209', 9110, '28881810031', 9102, 9125, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-02-01', '2027-02-28', 1750.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9124, 1, 'LC-3210', 9111, '25905000456', 9102, 9126, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2015-06-01', '2016-05-31', 6000.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Agreement With Ouqaf', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9125, 1, 'LC-3211', 9112, '28535667890', 9102, 9127, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 1800.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9126, 1, 'LC-3220', 9113, NULL, 9103, 9128, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 13000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9127, 1, 'LC-3301', 9114, NULL, 9104, 9129, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-08-01', '2026-12-31', 4400.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9128, 1, 'LC-3302', 9115, NULL, 9104, 9130, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-11-30', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9129, 1, 'LC-3303', 9116, NULL, 9104, 9131, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2026-07-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9130, 1, 'LC-3304', 9117, NULL, 9104, 9132, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 4000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9131, 1, 'LC-3305', 9118, NULL, 9104, 9133, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2026-07-31', 4500.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9132, 1, 'LC-3306', 9119, NULL, 9104, 9134, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 4100.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9133, 1, 'LC-3307', 9120, NULL, 9104, 9135, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-05-01', '2027-04-30', 4600.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9134, 1, 'LC-3308', 9121, NULL, 9104, 9136, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 4300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9135, 1, 'LC-3309', 9122, NULL, 9104, 9137, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 1100.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9136, 1, 'LC-3310', 9123, NULL, 9104, 9138, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 1100.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9137, 1, 'LC-3401', 9124, NULL, 9105, 9140, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2025-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Forwarded Waiting For Signature', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9138, 1, 'LC-3402', 9125, NULL, 9105, 9141, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-10-01', '2027-09-30', 24000.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9139, 1, 'LC-3501', 9126, '29163403552', 9106, 9142, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-10-15', '2026-10-14', 12000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9140, 1, 'LC-3601', 9127, NULL, 9107, 9143, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-03-01', '2027-02-28', 4000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9141, 1, 'LC-3602', 9128, NULL, 9107, 9144, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 4500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9142, 1, 'LC-3603', 9129, NULL, 9107, 9145, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-02-28', 4000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9143, 1, 'LC-3604', 9130, NULL, 9107, 9146, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2026-07-31', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9144, 1, 'LC-3605', 9131, NULL, 9107, 9147, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-01', '2026-08-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9145, 1, 'LC-3606', 9132, NULL, 9107, 9148, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9146, 1, 'LC-3607', 9133, NULL, 9107, 9149, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-10-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9147, 1, 'LC-3608', 9134, NULL, 9107, 9150, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-01', '2027-08-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9148, 1, 'LC-3609', 9135, NULL, 9107, 9151, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 4000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9149, 1, 'LC-3610', 9136, '27035601960', 9107, 9152, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-01', '2028-05-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9150, 1, 'LC-3611', 9137, NULL, 9107, 9153, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 4000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9151, 1, 'LC-3612', 9138, NULL, 9107, 9154, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-01', '2027-08-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9152, 1, 'LC-3613', 9139, NULL, 9107, 9155, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 4250.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9153, 1, 'LC-3614', 9140, NULL, 9107, 9156, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-10-01', '2026-09-30', 4200.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9154, 1, 'LC-3615', 9141, NULL, 9107, 9157, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-06-01', '2027-05-31', 1000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9155, 1, 'LC-1101', 9142, '26005002391', 9108, 9159, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 6400.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9156, 1, 'LC-1102', 9143, '27735600374', 9108, 9160, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 6500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9157, 1, 'LC-1103', 9144, NULL, 9108, 9161, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 5900.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9158, 1, 'LC-1104', 9145, NULL, 9108, 9162, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 5600.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9159, 1, 'LC-1105', 9146, NULL, 9108, 9163, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-12-01', '2026-11-30', 6300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9160, 1, 'LC-1106', 9147, '26405000664', 9108, 9164, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-02-01', '2027-01-31', 6500.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9161, 1, 'LC-1107', 9148, NULL, 9108, 9165, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 5650.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9162, 1, 'LC-1108', 9149, '26035610365', 9108, 9166, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 5850.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9163, 1, 'LC-1109', 9150, NULL, 9108, 9167, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-11-30', 6200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9164, 1, 'LC-1110', 9151, NULL, 9108, 9168, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 6500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9165, 1, 'LC-1111', 9152, '173052', 9108, 9169, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-01-08', '2027-07-31', 6000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'First Fifteen days Grace', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9166, 1, 'LC-1112', 9153, '19340', 9108, 9170, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-12-01', '2026-11-30', 5300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9167, 1, 'LC-1113', 9154, NULL, 9108, 9171, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-15', '2026-10-14', 6000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9168, 1, 'LC-1114', 9155, NULL, 9108, 9172, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 6500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9169, 1, 'LC-1115', 9156, NULL, 9108, 9173, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-12-01', '2026-11-30', 6300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9170, 1, 'LC-1116', 9157, NULL, 9108, 9174, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 6100.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9171, 1, 'LC-1117', 9158, '29005004534', 9108, 9175, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-01', '2027-05-31', 6500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9172, 1, 'LC-1118', 9159, NULL, 9108, 9176, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-12-01', '2027-01-31', 6800.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Expired', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9173, 1, 'LC-1119', 9160, '108830', 9108, 9177, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 6300.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9174, 1, 'LC-1120', 9161, '24981800349', 9108, 9178, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2026-01-31', 6000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9175, 1, 'LC-1121', 9162, NULL, 9108, 9179, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-03-01', '2028-02-28', 7000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9176, 1, 'LC-1122', 9163, NULL, 9108, 9180, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 6200.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9177, 1, 'LC-1123', 9164, NULL, 9108, 9181, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-05-01', '2026-04-30', 6000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9178, 1, 'LC-1124', 9165, NULL, 9108, 9182, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-01', '2026-08-31', 6350.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9179, 1, 'LC-1125', 9166, NULL, 9108, 9183, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-06', '2027-05-05', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9180, 1, 'LC-1201', 9167, NULL, 9109, 9184, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-09-01', '2026-08-31', 9300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9181, 1, 'LC-1202', 9168, NULL, 9109, 9185, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2026-12-31', 7500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9182, 1, 'LC-1203', 9169, NULL, 9109, 9186, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2026-12-31', 13000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9183, 1, 'LC-1204', 9170, NULL, 9109, 9187, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-07-01', '2027-06-30', 7500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9184, 1, 'LC-1205', 9171, NULL, 9109, 9188, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-03-15', '2027-03-14', 7500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9185, 1, 'LC-1206', 9172, NULL, 9109, 9189, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-06-01', '2027-05-31', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9186, 1, 'LC-1207', 9173, NULL, 9110, 9190, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-08-01', '2026-07-31', 3000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9187, 1, 'LC-1208', 9174, NULL, 9110, 9191, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2027-06-30', 3000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9188, 1, 'LC-1209', 9175, NULL, 9110, 9192, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 3200.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Ready,Waiting For Tenant Signature', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9189, 1, 'LC-1210', 9176, '29335641247', 9110, 9193, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-03-01', '2027-02-28', 3300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9190, 1, 'LC-1211', 9177, NULL, 9110, 9194, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2027-07-31', 3000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9191, 1, 'LC-1212', 9178, NULL, 9110, 9195, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-06-01', '2027-05-31', 3000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9192, 1, 'LC-1213', 9179, NULL, 9110, 9196, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 2500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9193, 1, 'LC-1214', 9180, NULL, 9110, 9197, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2021-12-01', '2027-04-30', 3000.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9194, 1, 'LC-1215', 9181, NULL, 9110, 9198, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-10-01', '2026-09-30', 2500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9195, 1, 'LC-1216', 9182, '28835641261', 9110, 9199, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-01', '2028-06-30', 1600.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '45 Days Grace(01.07.26 to 15.08.26)', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9196, 1, 'LC-1217', 9183, NULL, 9110, 9200, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-10-31', 2500.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9197, 1, 'LC-1218', 9184, NULL, 9110, 9201, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-04-01', '2027-03-31', 3200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9198, 1, 'LC-1219', 9185, NULL, 9110, 9202, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-01', '2027-04-30', 2500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9199, 1, 'LC-1220', 9186, NULL, 9110, 9203, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 3300.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Ismail Informed to Hold the Renewal', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9200, 1, 'LC-1221', 9187, '27881800771', 9110, 9204, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-03-31', 3100.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9201, 1, 'LC-1222', 9188, NULL, 9110, 9205, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2027-09-30', 3400.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9202, 1, 'LC-1225', 9189, '29463403920', 9111, 9206, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-01', '2026-08-31', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9203, 1, 'LC-1226', 9190, '28276000055', 9111, 9207, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9204, 1, 'LC-1227', 9191, '29363403912', 9111, 9208, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-04-01', '2027-03-31', 10000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Vila No:3, St No:824,Zone No:42', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9205, 1, 'LC-1228', 9192, '26863401351', 9111, 9209, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-04-01', '2027-03-31', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Vila No:4, St No:824,Zone No:42', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9206, 1, 'LC-1229', 9193, '27140000154', 9111, 9210, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-04-01', '2027-03-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9207, 1, 'LC-1230', 9194, '28799900303', 9111, 9211, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 10000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9208, 1, 'LC-15', 9195, '25705000391', 9112, 9212, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 12000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9212, 1, 'LC-1401', 9199, NULL, 9113, 9216, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-01', '2027-07-31', 42000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9213, 1, 'LC-1501', 9200, NULL, 9114, 9217, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-10-31', 3500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9214, 1, 'LC-1502', 9201, NULL, 9114, 9218, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-11-01', '2026-10-31', 3500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9215, 1, 'LC-1503', 9202, NULL, 9114, 9219, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-10-31', 3200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9216, 1, 'LC-1504', 9203, NULL, 9114, 9220, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-08-31', 6200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9217, 1, 'LC-1505', 9204, NULL, 9114, 9221, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-07-01', '2026-06-30', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9218, 1, 'LC-1506', 9205, NULL, 9114, 9222, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-10', '2027-03-31', 7500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9219, 1, 'LC-1507', 9206, NULL, 9114, 9223, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-12-01', '2026-11-30', 3300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9220, 1, 'LC-1508', 9207, NULL, 9114, 9224, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-10-31', 3600.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9221, 1, 'LC-1701', 9208, NULL, 9115, 9225, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 21000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9222, 1, 'LC-1', 9209, NULL, 9116, 9226, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-07-01', '2026-06-30', 12000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9223, 1, 'LC-5101', 9210, NULL, 9117, 9227, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9224, 1, 'LC-5102', 9210, NULL, 9117, 9228, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `lease_contracts` (`id`, `company_id`, `contract_number`, `tenant_id`, `tenant_qid`, `facility_id`, `unit_id`, `contract_kind`, `plate_number`, `vehicle_type`, `vehicle_description`, `title_deed_no`, `zone_no`, `street_no`, `building_no`, `template_id`, `status`, `signed_date`, `billing_start_date`, `start_date`, `end_date`, `rent_amount`, `security_deposit`, `payment_frequency`, `payment_type`, `payment_day`, `late_penalty_pct`, `grace_period_days`, `discount_pct`, `vat_applicable`, `vat_rate`, `auto_renew`, `auto_generate_invoices`, `custom_content_en`, `custom_content_ar`, `contract_terms`, `termination_reason`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `has_free_period`, `free_period_months`, `free_period_desc`, `free_period_position`, `includes_utilities`, `utilities_desc`, `includes_furnished`, `furnished_desc`, `deposit_payment_method`, `deposit_cheque_no`, `prorata_basis`, `parent_contract_id`, `edited_at`, `edited_by`) VALUES
(9225, 1, 'LC-5103', 9211, NULL, 9117, 9229, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9226, 1, 'LC-5104', 9210, NULL, 9117, 9230, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-05-01', '2026-04-30', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9227, 1, 'LC-5201', 9212, NULL, 9118, 9231, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 17000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Building No 03', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9228, 1, 'LC-5301', 9213, NULL, 9119, 9232, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-03-31', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9229, 1, 'LC-5302', 9214, NULL, 9119, 9233, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-03-31', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9230, 1, 'LC-5303', 9215, NULL, 9119, 9234, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-02-28', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9231, 1, 'LC-5304', 9216, NULL, 9119, 9235, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2027-01-31', 4800.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9232, 1, 'LC-5305', 9217, NULL, 9119, 9236, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9233, 1, 'LC-5306', 9218, '2825609531', 9119, 9237, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-04-01', '2027-03-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9234, 1, 'LC-5307', 9219, NULL, 9119, 9238, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2026-12-31', 4800.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9235, 1, 'LC-5308', 9220, NULL, 9119, 9239, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-01', '2026-08-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9236, 1, 'LC-5309', 9221, NULL, 9119, 9240, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-03-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9237, 1, 'LC-5310', 9222, NULL, 9119, 9241, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-10', '2026-12-31', 4800.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9238, 1, 'LC-5401', 9223, NULL, 9120, 9243, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2028-02-29', 12250.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9239, 1, 'LC-6101', 9224, '27742200997', 9121, 9244, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-11-30', 12500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9240, 1, 'LC-6102', 9225, '28345800432', 9121, 9245, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-03', '2027-02-28', 12000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9241, 1, 'LC-6103', 9226, NULL, 9122, 9246, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-20', '2027-01-19', 12000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9242, 1, 'LC-6104', 9227, NULL, 9122, 9247, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2027-04-30', 13000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9243, 1, 'LC-6201', 9228, NULL, 9123, 9248, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-06', '2027-09-05', 12500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9244, 1, 'LC-6202', 9229, NULL, 9123, 9249, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-01', '2027-12-31', 8500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9245, 1, 'LC-6203', 9230, '22081', 9123, 9250, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-01', '2027-11-30', 10000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9246, 1, 'LC-6204', 9231, '227992', 9123, 9251, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-12-01', '2027-11-30', 9500.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'Grace of first and last month', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9247, 1, 'LC-6205', 9232, NULL, 9123, 9252, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9248, 1, 'LC-6206', 9233, NULL, 9123, 9253, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9249, 1, 'LC-6207', 9234, '226019', 9123, 9254, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-06', '2027-09-05', 11500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9250, 1, 'LC-6208', 9235, NULL, 9123, 9255, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9251, 1, 'LC-6209', 9236, '29135600883', 9123, 9256, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-10-15', '2027-10-14', 4200.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9252, 1, 'LC-6210', 9237, '29335626978', 9123, 9257, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-10-01', '2027-09-30', 4250.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9253, 1, 'LC-6211', 9238, '29035626876', 9123, 9258, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-15', '2027-09-14', 4250.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9254, 1, 'LC-6212', 9239, NULL, 9123, 9259, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-11-01', '2027-10-31', 4500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9255, 1, 'LC-6213', 9240, '28336400623', 9123, 9260, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-09-15', '2027-09-14', 4500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9256, 1, 'LC-6214', 9241, '28614409074', 9123, 9261, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-11-01', '2027-10-31', 4250.00, NULL, 'monthly', 'cash', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9257, 1, 'LC-6215', 9242, '28335664309', 9123, 9262, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-01', '2028-05-31', 4200.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, 'First 15 Days Free Grace', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9258, 1, 'LC-6216', 9243, NULL, 9123, 9263, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-01', '2028-05-31', 4000.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9259, 1, 'LC-6217', 9244, '27435627051', 9123, 9264, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-10-15', '2027-10-14', 4100.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9260, 1, 'LC-6218', 9245, '27876001074', 9123, 9265, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-11-01', '2027-10-31', 4500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9261, 1, 'LC-6219', 9246, NULL, 9123, 9266, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-11-01', '2027-10-31', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9262, 1, 'LC-7101', 9247, NULL, 9124, 9267, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-08-01', '2025-06-30', 10500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9263, 1, 'LC-7102', 9248, '184533', 9124, 9268, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-02-01', '2027-01-31', 10000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9264, 1, 'LC-7104', 9249, NULL, 9124, 9269, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-05-01', '2025-04-30', 10500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9265, 1, 'LC-7103', 9250, NULL, 9124, 9270, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-03-01', '2026-02-28', 10250.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9266, 1, 'LC-8101', 9254, NULL, 9126, 9274, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-07-01', '2027-01-31', 14000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9267, 1, 'LC-8103', 9252, NULL, 9125, 9272, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2017-05-15', '2022-05-14', 15000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9268, 1, 'LC-8102', 9255, NULL, 9126, 9275, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2022-11-01', '2027-01-31', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9271, 1, 'LC-4001', 9256, NULL, 9127, 9276, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-03-15', '2026-03-14', 19000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9272, 1, 'LC-AUTO-9272', 9257, NULL, 9128, 9277, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-11-01', '2026-12-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9273, 1, 'LC-1223', 9258, '55531097', 9129, 9278, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2023-04-01', '2027-03-31', 10000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9274, 1, 'LC-AUTO-9274', 9259, '28563401175', 9130, 9279, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-06-01', '2025-05-31', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9275, 1, 'LC-AUTO-9275', 9260, '26635617081', 9131, 9280, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-08-15', '2026-09-14', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9276, 1, 'LC-AUTO-9276', 9261, NULL, 9131, 9281, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2026-01-31', 11000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9277, 1, 'LC-3120', 9262, NULL, 9132, 9282, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-20', '2026-11-30', 3300.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9278, 1, 'LC-3121', 9263, NULL, 9132, 9283, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-20', '2026-10-19', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9279, 1, 'LC-3122', 9264, '30081800597', 9132, 9284, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-01', '2027-04-30', 4000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9280, 1, 'LC-3123', 9264, '30081800597', 9132, 9285, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-01', '2027-04-30', 3800.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9281, 1, 'LC-3124', 9265, '28979201334', 9132, 9286, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-05-01', '2027-04-30', 3000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9282, 1, 'LC-3125', 9266, '29358601398', 9132, 9287, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-06-15', '2027-06-14', 3400.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9283, 1, 'LC-3126', 9267, '27963403124', 9132, 9288, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-08-01', '2027-07-31', 3400.00, NULL, 'monthly', 'bank_transfer', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9284, 1, 'LC-3127', 9268, '28151200033', 9132, 9289, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-06-01', '2027-05-30', 3000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9285, 1, 'LC-3128', 9269, '28988600671', 9132, 9290, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-06-01', '2027-05-31', 3850.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9286, 1, 'LC-3129', 9270, NULL, 9132, 9291, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2026-08-09', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9287, 1, 'LC-3130', 9271, NULL, 9132, 9292, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2026-08-30', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9288, 1, 'LC-3131', 9272, NULL, 9132, 9293, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-10-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9289, 1, 'LC-3132', 9273, NULL, 9132, 9294, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-11-01', '2026-10-31', 9500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9290, 1, 'LC-3133', 9274, NULL, 9132, 9295, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2026-10-31', 8500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9291, 1, 'LC-3134', 9275, NULL, 9132, 9296, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-12-01', '2026-11-30', 8500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9292, 1, 'LC-3135', 9276, NULL, 9132, 9297, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-25', '2026-08-30', 8500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9293, 1, 'LC-3136', 9277, NULL, 9132, 9298, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-03-01', '2027-02-28', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9294, 1, 'LC-3137', 9278, NULL, 9132, 9299, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 75000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9295, 1, 'LC-3138', 9279, NULL, 9132, 9300, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9296, 1, 'LC-3139', 9279, NULL, 9132, 9301, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9297, 1, 'LC-3140', 9279, NULL, 9132, 9302, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9298, 1, 'LC-3141', 9279, NULL, 9132, 9303, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9299, 1, 'LC-3142', 9279, NULL, 9132, 9304, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9300, 1, 'LC-3143', 9279, NULL, 9132, 9305, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9301, 1, 'LC-3144', 9279, NULL, 9132, 9306, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9302, 1, 'LC-3145', 9279, NULL, 9132, 9307, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9303, 1, 'LC-3147', 9280, NULL, 9132, 9309, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 1000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9304, 1, 'LC-3150', 9281, NULL, 9133, 9310, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2026-08-09', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9305, 1, 'LC-3151', 9282, NULL, 9133, 9311, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2026-08-09', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9306, 1, 'LC-3152', 9283, NULL, 9133, 9312, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2026-08-09', 3000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9307, 1, 'LC-3153', 9284, NULL, 9133, 9313, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9308, 1, 'LC-3154', 9284, NULL, 9133, 9314, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9309, 1, 'LC-3155', 9285, NULL, 9133, 9315, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 4500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9310, 1, 'LC-3156', 9274, NULL, 9133, 9316, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-02-01', '2026-10-31', 2500.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9311, 1, 'LC-3157', 9284, NULL, 9133, 9317, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9312, 1, 'LC-3158', 9284, NULL, 9133, 9318, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9313, 1, 'LC-3159', 9285, NULL, 9133, 9319, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9314, 1, 'LC-3160', 9286, NULL, 9133, 9320, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9315, 1, 'LC-3161', 9287, NULL, 9133, 9321, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9316, 1, 'LC-3162', 9285, NULL, 9133, 9322, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-01', '2026-09-30', 9000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '4', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9317, 1, 'LC-3163', 9288, NULL, 9133, 9323, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-25', '2026-09-08', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9318, 1, 'LC-3164', 9289, NULL, 9133, 9324, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-25', '2026-09-08', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9319, 1, 'LC-3165', 9289, NULL, 9133, 9325, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-25', '2026-09-08', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9320, 1, 'LC-3166', 9289, NULL, 9133, 9326, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-09-25', '2026-09-08', 8000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '5', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9321, 1, 'LC-3167', 9284, NULL, 9133, 9327, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 75000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9322, 1, 'LC-3168', 9284, NULL, 9133, 9328, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9323, 1, 'LC-3169', 9284, NULL, 9133, 9329, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9324, 1, 'LC-3170', 9284, NULL, 9133, 9330, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9325, 1, 'LC-3171', 9284, NULL, 9133, 9331, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9326, 1, 'LC-3172', 9284, NULL, 9133, 9332, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9327, 1, 'LC-3173', 9284, NULL, 9133, 9333, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9328, 1, 'LC-3174', 9284, NULL, 9133, 9334, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9329, 1, 'LC-3175', 9284, NULL, 9133, 9335, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9330, 1, 'LC-3176', 9284, NULL, 9133, 9336, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-01-01', '2030-12-31', 0.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, '10', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9331, 1, 'LC-3177', 9290, NULL, 9133, 9337, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-10-10', '2024-08-09', 5000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9332, 1, 'LC-3178', 9291, NULL, 9133, 9338, 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2024-01-01', '2027-12-31', 1000.00, NULL, 'monthly', 'cheque', NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, 0, NULL, NULL, 'beginning', 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lease_payments`
--

CREATE TABLE `lease_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_number` varchar(30) NOT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_type` varchar(50) NOT NULL DEFAULT 'rent',
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `amount` decimal(14,2) NOT NULL,
  `status` enum('pending','paid','partial','overdue','cancelled','postponed') NOT NULL DEFAULT 'pending',
  `bank_name` varchar(120) DEFAULT NULL,
  `transfer_reference` varchar(80) DEFAULT NULL,
  `cheque_no` varchar(50) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `reference_no` varchar(80) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(10) UNSIGNED DEFAULT NULL,
  `collection_session_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `acknowledged` tinyint(1) NOT NULL DEFAULT 0,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledged_by` int(10) UNSIGNED DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `deposit_ref` varchar(80) DEFAULT NULL,
  `collected_at` datetime DEFAULT NULL,
  `cheque_bank` varchar(120) DEFAULT NULL,
  `cheque_maturity` date DEFAULT NULL,
  `transfer_bank` varchar(120) DEFAULT NULL,
  `transfer_account` varchar(80) DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_ref` varchar(80) DEFAULT NULL,
  `postponed_to` date DEFAULT NULL,
  `postpone_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(120) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `created_at`) VALUES
(1, 'admin@fmerp.com', '37.186.32.153', 1, '2026-07-31 14:32:03'),
(2, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:14:01'),
(3, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:14:20'),
(4, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:15:12'),
(5, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:22:41'),
(6, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:33:36'),
(7, 'admin@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:34:06'),
(8, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-17 22:41:24'),
(9, 'admin@alyazwa.com', '176.202.62.86', 0, '2026-08-18 15:08:20'),
(10, 'aziz@alyazwa.com', '176.202.62.86', 1, '2026-08-18 15:09:07'),
(11, 'admin@alyazwa.com', '37.211.64.30', 1, '2026-08-22 10:44:12'),
(12, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-22 11:27:55'),
(13, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-22 12:23:55'),
(14, 'aziz@alyazwa.com', '37.211.64.30', 0, '2026-08-22 21:30:19'),
(15, 'aziz@alyazwa.com', '37.211.64.30', 0, '2026-08-22 21:30:28'),
(16, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-22 21:30:58'),
(17, 'aziz@alyazwa.com', '37.211.64.30', 1, '2026-08-22 22:04:22'),
(18, 'aziz@alyazwa.com', '176.202.62.86', 1, '2026-08-23 16:08:41'),
(19, 'aziz@alyazwa.com', '104.28.38.92', 1, '2026-08-23 16:15:15'),
(20, 'aziz@alyazwa.com', '176.202.62.86', 1, '2026-08-23 16:32:25'),
(21, 'aziz@alyazwa.com', '212.70.114.112', 1, '2026-08-24 07:53:48'),
(22, 'admin@alyazwa.com', '37.210.201.165', 1, '2026-08-26 15:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_costing`
--

CREATE TABLE `maintenance_costing` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `labor_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `parts_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vendor_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `emergency_surcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_estimate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `job_profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `asset_id` int(10) UNSIGNED DEFAULT NULL,
  `scan_source` varchar(30) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `work_type` enum('facility','non_facility') NOT NULL DEFAULT 'facility',
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `salesman_id` int(10) UNSIGNED DEFAULT NULL,
  `sales_rep_name` varchar(150) DEFAULT NULL,
  `requester_name` varchar(150) NOT NULL,
  `requester_email` varchar(150) DEFAULT NULL,
  `requester_phone` varchar(30) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `status` enum('pending','reviewed','converted','rejected') NOT NULL DEFAULT 'pending',
  `image_path` varchar(255) DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `forwarded_to_fm` tinyint(1) NOT NULL DEFAULT 0,
  `forwarded_by` int(10) UNSIGNED DEFAULT NULL,
  `forwarded_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `converted_to_wo` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` varchar(150) DEFAULT NULL,
  `service_customer_id` int(10) UNSIGNED DEFAULT NULL,
  `requester_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`id`, `ticket_number`, `facility_id`, `unit_id`, `asset_id`, `scan_source`, `company_id`, `work_type`, `customer_id`, `salesman_id`, `sales_rep_name`, `requester_name`, `requester_email`, `requester_phone`, `category`, `description`, `priority`, `status`, `image_path`, `reviewed_by`, `verified_by`, `verified_at`, `verification_notes`, `approval_status`, `approved_by`, `approved_at`, `forwarded_to_fm`, `forwarded_by`, `forwarded_at`, `rejection_reason`, `reviewed_at`, `converted_to_wo`, `created_at`, `updated_at`, `email`, `service_customer_id`, `requester_location`) VALUES
(9001, 'MR-DEMO-9001', 9001, 9001, NULL, NULL, 1, 'facility', NULL, NULL, NULL, 'Demo Tenant', 'tenant@demo.local', '+97450002001', 'plumbing', 'Kitchen sink leak reported by tenant in unit 101.', 'medium', 'converted', NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 1, 1, '2026-08-03 15:57:39', NULL, NULL, 9001, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media_albums`
--

CREATE TABLE `media_albums` (
  `id` int(10) UNSIGNED NOT NULL,
  `module` varchar(50) NOT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `album_type` enum('handover','return','condition','before_after','general') NOT NULL DEFAULT 'general',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `signature_path` varchar(300) DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `locked_by` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_items`
--

CREATE TABLE `media_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `album_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `caption` varchar(300) DEFAULT NULL,
  `condition_tag` varchar(50) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-05-21-100000', 'App\\Database\\Migrations\\WorkOrderWorkflow', 'default', 'App', 1785503018, 1),
(2, '2026-05-22-120000', 'App\\Database\\Migrations\\EnterpriseProduction', 'default', 'App', 1785503018, 1),
(3, '2026-05-23-100000', 'App\\Database\\Migrations\\FullModuleParity', 'default', 'App', 1785503018, 1),
(4, '2026-05-24-140000', 'App\\Database\\Migrations\\FinanceErpFoundation', 'default', 'App', 1785503018, 1),
(5, '2026-05-24-160000', 'App\\Database\\Migrations\\FinancialWorkflowRedesign', 'default', 'App', 1785503018, 1),
(6, '2026-05-24-170000', 'App\\Database\\Migrations\\ServiceCustomersAndCleanup', 'default', 'App', 1785503018, 1),
(7, '2026-05-25-120000', 'App\\Database\\Migrations\\PortalRequirements', 'default', 'App', 1785503018, 1),
(8, '2026-05-26-100000', 'App\\Database\\Migrations\\PortalPhase23', 'default', 'App', 1785503018, 1),
(9, '2026-05-27-100000', 'App\\Database\\Migrations\\InvoiceBillToNonFacility', 'default', 'App', 1785503018, 1),
(10, '2026-05-27-100000', 'App\\Database\\Migrations\\PortalCompletion', 'default', 'App', 1785503018, 1),
(11, '2026-05-28-100000', 'App\\Database\\Migrations\\ContractReManager', 'default', 'App', 1785503018, 1),
(12, '2026-05-28-100000', 'App\\Database\\Migrations\\WorkOrderContractAndDocs', 'default', 'App', 1785503018, 1),
(13, '2026-05-29-100000', 'App\\Database\\Migrations\\SalesNonFacilityWorkflow', 'default', 'App', 1785503018, 1),
(14, '2026-05-30-100000', 'App\\Database\\Migrations\\HelpdeskForwardToFm', 'default', 'App', 1785503018, 1),
(15, '2026-05-31-100000', 'App\\Database\\Migrations\\InvoiceEditLogs', 'default', 'App', 1785503018, 1),
(16, '2026-06-01-100000', 'App\\Database\\Migrations\\AssetQrLifecycle', 'default', 'App', 1785503018, 1),
(17, '2026-07-23-100000', 'App\\Database\\Migrations\\WorkspaceArchitecture', 'default', 'App', 1785503018, 2),
(18, '2026-07-23-120000', 'App\\Database\\Migrations\\PmErpModules', 'default', 'App', 1785503018, 2),
(19, '2026-07-23-140000', 'App\\Database\\Migrations\\PmWorkflowExtras', 'default', 'App', 1785503018, 2),
(20, '2026-07-23-150000', 'App\\Database\\Migrations\\PmSecondaryModules', 'default', 'App', 1785503018, 2),
(21, '2026-07-23-160000', 'App\\Database\\Migrations\\PmOpsSecurityMedia', 'default', 'App', 1785503018, 2),
(22, '2026-07-23-170000', 'App\\Database\\Migrations\\PortalAndCollector', 'default', 'App', 1785503018, 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('work_order','invoice','sla_breach','maintenance','general','expense','petty_cash','reimbursement','approval') NOT NULL DEFAULT 'general',
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outgoing_cheques`
--

CREATE TABLE `outgoing_cheques` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `cheque_no` varchar(50) NOT NULL,
  `bank_name` varchar(120) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `cheque_date` date NOT NULL,
  `payee_name` varchar(200) NOT NULL,
  `payee_type` varchar(50) DEFAULT NULL,
  `purpose` varchar(80) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','issued','cleared','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_partials`
--

CREATE TABLE `payment_partials` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash`
--

CREATE TABLE `petty_cash` (
  `id` int(10) UNSIGNED NOT NULL,
  `pc_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `requested_by` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `purpose` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `status` enum('pending','approved','issued','reconciliation','closed','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `issued_by` int(10) UNSIGNED DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `reconciled_by` int(10) UNSIGNED DEFAULT NULL,
  `reconciled_at` datetime DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pm_cost_types`
--

CREATE TABLE `pm_cost_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category` enum('income','expense') NOT NULL DEFAULT 'expense',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_cost_types`
--

INSERT INTO `pm_cost_types` (`id`, `company_id`, `parent_id`, `code`, `name`, `category`, `is_active`, `created_at`) VALUES
(1, NULL, NULL, 'RENT', 'Rent Income', 'income', 1, NULL),
(2, NULL, NULL, 'MAINT', 'Maintenance', 'expense', 1, NULL),
(3, NULL, NULL, 'UTIL', 'Utilities', 'expense', 1, NULL),
(4, NULL, NULL, 'MGMT', 'Management Fee', 'expense', 1, NULL),
(5, NULL, NULL, 'LLPAY', 'Landlord Payout', 'expense', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pm_salary_runs`
--

CREATE TABLE `pm_salary_runs` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `profile_id` int(10) UNSIGNED DEFAULT NULL,
  `month` char(7) NOT NULL,
  `hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross` decimal(14,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(14,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','approved','paid') NOT NULL DEFAULT 'draft',
  `accrual_journal_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_journal_id` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_payments`
--

CREATE TABLE `po_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `po_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(60) NOT NULL DEFAULT 'bank_transfer',
  `reference` varchar(120) DEFAULT NULL,
  `paid_by` int(10) UNSIGNED DEFAULT NULL,
  `paid_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procurement_orders`
--

CREATE TABLE `procurement_orders` (
  `id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `po_number` varchar(30) DEFAULT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','sent','received','cancelled') DEFAULT NULL,
  `payment_status` enum('unpaid','partial','paid') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procurement_three_way_matches`
--

CREATE TABLE `procurement_three_way_matches` (
  `id` int(10) UNSIGNED NOT NULL,
  `po_id` int(10) UNSIGNED NOT NULL,
  `grn_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_bill_id` int(10) UNSIGNED DEFAULT NULL,
  `po_amount` decimal(14,2) DEFAULT 0.00,
  `grn_amount` decimal(14,2) DEFAULT 0.00,
  `bill_amount` decimal(14,2) DEFAULT 0.00,
  `variance` decimal(14,2) DEFAULT 0.00,
  `match_status` enum('pending','matched','exception') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `matched_by` int(10) UNSIGNED DEFAULT NULL,
  `matched_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_budgets`
--

CREATE TABLE `property_budgets` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `income` decimal(14,2) NOT NULL DEFAULT 0.00,
  `expense` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(250) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_costs`
--

CREATE TABLE `property_costs` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `cost_type_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `frequency` varchar(30) NOT NULL DEFAULT 'one-off',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `finance_entry_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `po_number` varchar(30) NOT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','sent','received','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('pending','approved','rejected','ordered') NOT NULL DEFAULT 'pending',
  `requested_by` int(10) UNSIGNED NOT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `po_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `refund_type` varchar(50) NOT NULL,
  `refund_amount` decimal(14,2) DEFAULT NULL,
  `refund_date` date DEFAULT NULL,
  `reference_no` varchar(80) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reimbursements`
--

CREATE TABLE `reimbursements` (
  `id` int(10) UNSIGNED NOT NULL,
  `rmb_number` varchar(30) NOT NULL,
  `requested_by` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `reminder_datetime` datetime NOT NULL,
  `message` varchar(500) NOT NULL,
  `status` enum('pending','done','dismissed') NOT NULL DEFAULT 'pending',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_saved_queries`
--

CREATE TABLE `report_saved_queries` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `report_type` varchar(60) NOT NULL,
  `columns_json` text DEFAULT NULL,
  `filters_json` text DEFAULT NULL,
  `show_cost` tinyint(1) DEFAULT 0,
  `group_by` varchar(60) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq`
--

CREATE TABLE `rfq` (
  `id` int(10) UNSIGNED NOT NULL,
  `rfq_number` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('open','closed','awarded','cancelled') DEFAULT 'open',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq_quotations`
--

CREATE TABLE `rfq_quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `rfq_id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `lead_time` varchar(100) DEFAULT NULL,
  `validity` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_selected` tinyint(1) DEFAULT 0,
  `added_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq_vendors`
--

CREATE TABLE `rfq_vendors` (
  `id` int(10) UNSIGNED NOT NULL,
  `rfq_id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `status` enum('sent','responded','declined') DEFAULT 'sent',
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `workspace` enum('pm','fm','both','portal','collector') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `created_at`, `workspace`) VALUES
(1, 'super_admin', 'Super Admin / CEO', '2026-07-31 13:03:37', 'both'),
(2, 'facility_manager', 'Facility Manager', '2026-07-31 13:03:37', 'fm'),
(3, 'technician', 'Technician', '2026-07-31 13:03:37', 'fm'),
(4, 'client', 'Client', '2026-07-31 13:03:37', 'portal'),
(5, 'finance_manager', 'Finance Manager', '2026-07-31 13:03:37', 'pm'),
(6, 'finance_user', 'Finance User', '2026-07-31 13:03:37', 'pm'),
(7, 'procurement_officer', 'Procurement Officer', '2026-07-31 13:03:37', 'fm'),
(8, 'supervisor', 'Supervisor', '2026-07-31 13:03:37', 'pm'),
(10, 'real_estate_manager', 'Real Estate Manager', '2026-07-31 13:03:37', 'pm'),
(11, 'salesman', 'Salesman', '2026-07-31 13:03:37', 'pm'),
(12, 'property_manager', 'Property Manager', '2026-07-31 13:03:37', 'pm'),
(13, 'cash_collector', 'Cash Collector', '2026-07-31 13:03:37', 'collector'),
(14, 'tenant', 'Tenant (Portal)', '2026-07-31 13:03:37', 'portal'),
(15, 'manager', 'Property Manager (Scoped)', '2026-08-03 16:01:41', 'pm'),
(16, 'accountant', 'Accountant', '2026-08-03 16:01:41', 'pm'),
(17, 'hr', 'HR', '2026-08-03 16:01:41', 'pm'),
(18, 'caretaker', 'Caretaker', '2026-08-03 16:01:41', 'pm'),
(19, 'maintenance', 'Maintenance Staff', '2026-08-03 16:01:41', 'fm'),
(20, 'maintenance_staff', 'Maintenance Staff', '2026-08-03 16:01:41', 'fm'),
(21, 'maintenance_supervisor', 'Maintenance Supervisor', '2026-08-03 16:01:41', 'fm'),
(22, 'landlord', 'Landlord Portal', '2026-08-03 16:01:41', 'portal'),
(23, 'crm_agent', 'CRM Agent', '2026-08-03 16:01:41', 'pm'),
(24, 'sales_agent', 'Sales Agent', '2026-08-03 16:01:41', 'pm'),
(25, 'leasing_agent', 'Leasing Agent', '2026-08-03 16:01:41', 'pm');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `module` varchar(80) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `module`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 16, 'units', 1, 0, 0, 0),
(2, 23, 'units', 1, 0, 0, 0),
(3, 2, 'units', 1, 0, 0, 0),
(4, 5, 'units', 1, 0, 0, 0),
(5, 6, 'units', 1, 0, 0, 0),
(6, 22, 'units', 1, 0, 0, 0),
(7, 25, 'units', 1, 1, 1, 0),
(8, 12, 'units', 1, 1, 1, 0),
(9, 10, 'units', 1, 1, 1, 0),
(10, 11, 'units', 1, 0, 0, 0),
(11, 8, 'units', 1, 0, 0, 0),
(16, 16, 'leases', 1, 0, 0, 0),
(17, 23, 'leases', 1, 0, 0, 0),
(18, 2, 'leases', 1, 0, 0, 0),
(19, 5, 'leases', 1, 0, 0, 0),
(20, 6, 'leases', 1, 0, 0, 0),
(21, 22, 'leases', 1, 0, 0, 0),
(22, 25, 'leases', 1, 1, 1, 0),
(23, 12, 'leases', 1, 1, 1, 0),
(24, 10, 'leases', 1, 1, 1, 0),
(25, 11, 'leases', 1, 0, 0, 0),
(26, 8, 'leases', 1, 0, 0, 0),
(31, 16, 'facilities', 1, 0, 0, 0),
(32, 23, 'facilities', 1, 0, 0, 0),
(33, 5, 'facilities', 1, 0, 0, 0),
(34, 6, 'facilities', 1, 0, 0, 0),
(35, 22, 'facilities', 1, 0, 0, 0),
(36, 25, 'facilities', 1, 1, 1, 0),
(37, 12, 'facilities', 1, 1, 1, 0),
(38, 10, 'facilities', 1, 1, 1, 0),
(39, 11, 'facilities', 1, 0, 0, 0),
(40, 8, 'facilities', 1, 0, 0, 0),
(46, 16, 'tenants', 1, 0, 0, 0),
(47, 23, 'tenants', 1, 0, 0, 0),
(48, 5, 'tenants', 1, 0, 0, 0),
(49, 6, 'tenants', 1, 0, 0, 0),
(50, 22, 'tenants', 1, 0, 0, 0),
(51, 25, 'tenants', 1, 1, 0, 0),
(52, 12, 'tenants', 1, 1, 0, 0),
(53, 10, 'tenants', 1, 1, 0, 0),
(54, 11, 'tenants', 1, 0, 0, 0),
(55, 8, 'tenants', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sales_deals`
--

CREATE TABLE `sales_deals` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `deal_number` varchar(30) NOT NULL,
  `deal_type` enum('Sale','Lease') NOT NULL DEFAULT 'Lease',
  `lead_id` int(10) UNSIGNED DEFAULT NULL,
  `buyer_name` varchar(200) NOT NULL,
  `buyer_phone` varchar(30) DEFAULT NULL,
  `buyer_email` varchar(150) DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `deal_value` decimal(14,2) DEFAULT NULL,
  `agreed_price` decimal(14,2) DEFAULT NULL,
  `stage` varchar(50) NOT NULL DEFAULT 'prospect',
  `agent_id` int(10) UNSIGNED DEFAULT NULL,
  `commission_rule_id` int(10) UNSIGNED DEFAULT NULL,
  `expected_close_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_customers`
--

CREATE TABLE `service_customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_visits`
--

CREATE TABLE `site_visits` (
  `id` int(10) UNSIGNED NOT NULL,
  `visit_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `work_order_id` int(10) UNSIGNED DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `visited_at` datetime DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `purpose` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `technician_id` int(10) UNSIGNED DEFAULT NULL,
  `supervisor_id` int(10) UNSIGNED DEFAULT NULL,
  `technician_remarks` text DEFAULT NULL,
  `supervisor_remarks` text DEFAULT NULL,
  `client_signature` varchar(255) DEFAULT NULL,
  `technician_signature` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sla_rules`
--

CREATE TABLE `sla_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `priority` enum('critical','high','medium','low') NOT NULL,
  `response_hours` int(10) UNSIGNED NOT NULL,
  `resolution_hours` int(10) UNSIGNED NOT NULL,
  `escalation_hours` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sla_rules`
--

INSERT INTO `sla_rules` (`id`, `priority`, `response_hours`, `resolution_hours`, `escalation_hours`, `created_at`) VALUES
(1, 'critical', 1, 4, 2, '2026-07-31 13:03:37'),
(2, 'high', 2, 12, 6, '2026-07-31 13:03:37'),
(3, 'medium', 4, 24, 12, '2026-07-31 13:03:37'),
(4, 'low', 8, 72, 48, '2026-07-31 13:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `updated_at`) VALUES
(0, 'petty_prefix_expense', 'PCE', 'petty_cash', '2026-08-17 22:55:36'),
(1, 'company_name', 'Al Yazwa Facility Management', 'branding', '2026-08-01 21:25:40'),
(2, 'currency', 'QAR', 'finance', '2026-07-31 13:03:37'),
(3, 'vat_enabled', '0', 'finance', '2026-07-31 13:03:37'),
(4, 'vat_rate', '0', 'finance', '2026-07-31 13:03:37'),
(5, 'timezone', 'Asia/Qatar', 'general', '2026-07-31 13:03:37'),
(6, 'sla_breach_notify', '1', 'notifications', '2026-07-31 13:03:37'),
(7, 'smtp_host', '', 'email', '2026-07-31 13:03:37'),
(8, 'smtp_user', '', 'email', '2026-07-31 13:03:37'),
(9, 'smtp_port', '587', 'email', '2026-07-31 13:03:37'),
(10, 'system_version', '2.4.1', 'general', '2026-07-31 13:03:37'),
(12, 'company_tagline', 'Smart Property, Facility Management', 'branding', '2026-08-17 23:16:04'),
(13, 'company_logo', 'logos/logo_1785608740.png', 'branding', '2026-08-01 21:25:41'),
(14, 'primary_color', '#332882', 'branding', '2026-08-01 21:26:33'),
(15, 'secondary_color', '#768c2e', 'branding', '2026-08-01 21:26:33'),
(16, 'wo_approval_required', '1', 'workflow', '2026-07-31 13:03:37'),
(17, 'jc_qa_required', '1', 'workflow', '2026-07-31 13:03:37'),
(18, 'complaint_auto_verify', '0', 'workflow', '2026-07-31 13:03:37'),
(22, 'company_address', '', 'company', '2026-07-31 13:03:37'),
(23, 'company_phone', '', 'company', '2026-07-31 13:03:37'),
(24, 'company_email', 'admin@fmerp.com', 'company', '2026-07-31 13:03:37'),
(27, 'default_labor_rate', '0.00', 'finance', '2026-07-31 13:03:37'),
(28, 'wf_auto_invoice_on_client_approve', '0', 'general', '2026-07-31 13:03:37'),
(29, 'alert_email_enabled', '1', 'general', '2026-07-31 13:03:37'),
(30, 'alert_whatsapp_enabled', '0', 'general', '2026-07-31 13:03:37'),
(31, 'alert_whatsapp_webhook', '', 'general', '2026-07-31 13:03:37'),
(32, 'site_visits_enabled', '1', 'general', '2026-07-31 13:03:37'),
(33, 'procurement_match_required', '1', 'general', '2026-07-31 13:03:37'),
(34, 'wf_require_supervisor_approval', '1', 'general', '2026-07-31 13:03:37'),
(35, 'wf_require_qa_on_complete', '0', 'general', '2026-07-31 13:03:37'),
(36, 'wf_require_client_approval', '1', 'general', '2026-07-31 13:03:37'),
(37, 'wf_require_invoice_before_close', '1', 'general', '2026-07-31 13:03:37'),
(38, 'wf_require_labor_or_material', '1', 'general', '2026-07-31 13:03:37'),
(40, 'rbac_overrides', '{\"client\": [\"dashboard\", \"helpdesk\", \"finance.invoices\", \"notifications\", \"profile\"], \"facility_manager\": [\"dashboard\", \"dashboard.kpi\", \"helpdesk\", \"workorders\", \"job-cards\", \"facilities\", \"assets\", \"employees\", \"inventory\", \"procurement\", \"vendors\", \"finance\", \"finance.invoices\", \"finance.expenses\", \"reports\", \"reports.finance\", \"reports.kpi\", \"compliance\", \"utility\", \"estimations\", \"costing\", \"settings.users\", \"notifications\"], \"finance_manager\": [\"dashboard\", \"dashboard.kpi\", \"finance\", \"finance.invoices\", \"finance.expenses\", \"finance.petty_cash\", \"finance.reimbursements\", \"finance.contracts\", \"finance.ledger\", \"finance.payments\", \"finance.coa\", \"finance.gl\", \"finance.ap\", \"finance.amc\", \"finance.budgets\", \"finance.reports\", \"reports\", \"reports.finance\", \"reports.kpi\", \"notifications\", \"profile\"], \"finance_user\": [\"dashboard\", \"finance\", \"finance.invoices\", \"finance.expenses\", \"finance.petty_cash\", \"finance.reimbursements\", \"finance.ap\", \"finance.amc\", \"notifications\", \"profile\"], \"procurement_officer\": [\"dashboard\", \"inventory\", \"procurement\", \"vendors\", \"reports\", \"reports.procurement\", \"notifications\", \"profile\"], \"supervisor\": [\"dashboard\", \"helpdesk\", \"workorders\", \"job-cards\", \"employees\", \"reports\", \"notifications\", \"profile\"], \"super_admin\": [\"*\"], \"technician\": [\"dashboard\", \"workorders\", \"job-cards\", \"inventory\", \"finance.petty_cash\", \"finance.reimbursements\", \"notifications\", \"profile\"], \"property_manager\": \"[\\\"dashboard\\\", \\\"dashboard.kpi\\\", \\\"helpdesk\\\", \\\"workorders\\\", \\\"job-cards\\\", \\\"facilities\\\", \\\"tenants\\\", \\\"landlords\\\", \\\"leases\\\", \\\"cheques\\\", \\\"crm\\\", \\\"sales\\\", \\\"utilities\\\", \\\"budgets\\\", \\\"cost-management\\\", \\\"offers\\\", \\\"media\\\", \\\"ai\\\", \\\"compliance\\\", \\\"utility\\\", \\\"reports\\\", \\\"reports.kpi\\\", \\\"finance\\\", \\\"finance.invoices\\\", \\\"finance.contracts\\\", \\\"finance.payments\\\", \\\"finance.expenses\\\", \\\"finance.ledger\\\", \\\"finance.petty_cash\\\", \\\"finance.reimbursements\\\", \\\"finance.dashboard\\\", \\\"finance.bank.view\\\", \\\"finance.transaction.view\\\", \\\"finance.deposit.create\\\", \\\"finance.withdrawal.create\\\", \\\"petty_cash.view\\\", \\\"petty_cash.create\\\", \\\"petty_cash.submit\\\", \\\"petty_cash.advance\\\", \\\"petty_cash.settle\\\", \\\"petty_cash.count\\\", \\\"petty_cash.reconcile\\\", \\\"collector\\\", \\\"estimations\\\", \\\"notifications\\\", \\\"profile\\\"]\"}', 'security', '2026-08-17 23:41:51');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_type` enum('Personal','Corporate') NOT NULL DEFAULT 'Personal',
  `full_name` varchar(200) NOT NULL,
  `nationality` varchar(80) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `company_cr` varchar(50) DEFAULT NULL,
  `qid_no` varchar(30) DEFAULT NULL,
  `qid_expiry` date DEFAULT NULL,
  `passport_no` varchar(30) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `emergency_name` varchar(120) DEFAULT NULL,
  `emergency_phone` varchar(30) DEFAULT NULL,
  `emergency_relation` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `current_unit_id` int(10) UNSIGNED DEFAULT NULL,
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `company_id`, `user_id`, `tenant_type`, `full_name`, `nationality`, `gender`, `dob`, `phone`, `whatsapp`, `email`, `company_name`, `company_cr`, `qid_no`, `qid_expiry`, `passport_no`, `passport_expiry`, `emergency_name`, `emergency_phone`, `emergency_relation`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`, `current_unit_id`, `is_blacklisted`) VALUES
(9001, 1, NULL, 'Personal', 'Demo Tenant', 'Qatar', NULL, NULL, '+97450002001', NULL, 'tenant@demo.local', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 9001, 0),
(9101, 1, NULL, 'Personal', 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9102, 1, NULL, 'Personal', 'YESUDAS KAITHAYALAPPIL', NULL, NULL, NULL, '55298197', NULL, NULL, NULL, NULL, '25935609211', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9103, 1, NULL, 'Personal', 'SAIF HASSAN AWADALLA', NULL, NULL, NULL, '55978737', '44086118', NULL, NULL, NULL, '26573600658', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9104, 1, NULL, 'Corporate', 'COOL & REST TRDG (STAFFS)', NULL, NULL, NULL, '33766555', NULL, NULL, NULL, NULL, '26335604253', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9105, 1, NULL, 'Personal', 'COOL AND REST', NULL, NULL, NULL, '66169064', NULL, NULL, NULL, NULL, '27135633074', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9106, 1, NULL, 'Personal', 'COOL AND REST (SHABIR)', NULL, NULL, NULL, '66169064', NULL, NULL, NULL, NULL, '27135633074', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9107, 1, NULL, 'Personal', 'ABDULLA BASHIR KHAN', NULL, NULL, NULL, '30080637', NULL, NULL, NULL, NULL, '26858600285', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9108, 1, NULL, 'Personal', 'MATHEW SIMON', NULL, NULL, NULL, '55867916', NULL, NULL, NULL, NULL, '26235601500', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9109, 1, NULL, 'Personal', 'MECH & TECH (RESHMI JOHN)', NULL, NULL, NULL, '74789579', NULL, NULL, NULL, NULL, '29535636844', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9110, 1, NULL, 'Personal', 'MARY GRACE CABARDO', NULL, NULL, NULL, '77750250', NULL, NULL, NULL, NULL, '28881810031', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9111, 1, NULL, 'Personal', 'MOHAMMED HOSSAN ABDURRAHIM', NULL, NULL, NULL, '55318268', NULL, NULL, NULL, NULL, '25905000456', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9112, 1, NULL, 'Personal', 'MANAF POTTAMMAL', NULL, NULL, NULL, '30091155', NULL, NULL, NULL, NULL, '28535667890', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9113, 1, NULL, 'Personal', 'Othman Mohd Al Emadi', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9114, 1, NULL, 'Personal', 'Hassam Pervez', NULL, NULL, NULL, '77998002', '55033094', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9115, 1, NULL, 'Corporate', 'Shanavas M (AL YAZWA TRDG & CONT', NULL, NULL, NULL, '66555916', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9116, 1, NULL, 'Personal', 'M/s. Cool N Rest - Vacant', NULL, NULL, NULL, '33766555', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9117, 1, NULL, 'Personal', 'Ouseph Shaji Kunduparambil', NULL, NULL, NULL, '55797010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9118, 1, NULL, 'Personal', 'Khaled Abdulkarim', NULL, NULL, NULL, '55210077', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9119, 1, NULL, 'Personal', 'ASHRAF DYAB MOHAMED ABDELR', NULL, NULL, NULL, '55606857', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9120, 1, NULL, 'Personal', 'ABDELBAST ELNAGI MOHAMED', NULL, NULL, NULL, '55240366', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9121, 1, NULL, 'Personal', 'ABDI HASSAN JAMA', NULL, NULL, NULL, '55545160', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9122, 1, NULL, 'Personal', 'Sadikh Kalanad', NULL, NULL, NULL, '66555953', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9123, 1, NULL, 'Personal', 'M/S. Cool and Rest', NULL, NULL, NULL, '33766555', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9124, 1, NULL, 'Personal', 'Global dry Land', NULL, NULL, NULL, '44261878', '66688093', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9125, 1, NULL, 'Personal', 'ABDUL AZIZ AL EMADI', NULL, NULL, NULL, '55501000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9126, 1, NULL, 'Personal', 'Mohd Hamad AS Hbabi', NULL, NULL, NULL, '66661024', '77777964', NULL, NULL, NULL, '29163403552', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9127, 1, NULL, 'Personal', 'HUSSEINI ABDELKARIM ELSAYED MOHAMED', NULL, NULL, NULL, '66121077', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9128, 1, NULL, 'Personal', 'HENRY GACUSAN JABAT', NULL, NULL, NULL, '70188768', '44343000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9129, 1, NULL, 'Personal', 'MOHD IBRAHIM ABDELA', NULL, NULL, NULL, '33030379', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9130, 1, NULL, 'Personal', 'M/S. ROCK TRANSPORTING & RENT', NULL, NULL, NULL, '66100564', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9131, 1, NULL, 'Personal', 'Nasser Ahmed Kolliyil', NULL, NULL, NULL, '55810190', '44310190', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9132, 1, NULL, 'Personal', 'Chief Tailor', NULL, NULL, NULL, '55804172', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9133, 1, NULL, 'Personal', 'M/S. MECH & TECH', NULL, NULL, NULL, '74789579', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9134, 1, NULL, 'Personal', 'Lokendra Bahadur Karki', NULL, NULL, NULL, '77153392', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9135, 1, NULL, 'Personal', 'Ahmed Ali Gasmalla Bagari', NULL, NULL, NULL, '33784229', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9136, 1, NULL, 'Personal', 'Nalakath Sulaiman Manafe', NULL, NULL, NULL, '33394202', NULL, NULL, NULL, NULL, '27035601960', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9137, 1, NULL, 'Personal', 'Mohammed Shawki', NULL, NULL, NULL, '55527429', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9138, 1, NULL, 'Personal', 'Faisal Ishaq Qadir Ishaq', NULL, NULL, NULL, '77151945', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9139, 1, NULL, 'Personal', 'Fahad Abdul Hameed', NULL, NULL, NULL, '70927817', '55823541', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9140, 1, NULL, 'Personal', 'Manakkat Thekkepeedikayil Kunhi', NULL, NULL, NULL, '70715588', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9141, 1, NULL, 'Personal', 'AL A.M.A AL ABDULLA', NULL, NULL, NULL, '55093010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9142, 1, NULL, 'Personal', 'Islam toufal', NULL, NULL, NULL, '55229672', '55757928', NULL, NULL, NULL, '26005002391', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9143, 1, NULL, 'Personal', 'Faisal Kakkuziulla Parambath', NULL, NULL, NULL, '55819842', NULL, NULL, NULL, NULL, '27735600374', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9144, 1, NULL, 'Personal', 'Ibrahim Abdullah harischandra', NULL, NULL, NULL, '33552936', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9145, 1, NULL, 'Personal', 'Ali Abdulkarim Ahmed', NULL, NULL, NULL, '66781011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9146, 1, NULL, 'Personal', 'Muhommad Usman Azam', NULL, NULL, NULL, '33811218', '66136458', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9147, 1, NULL, 'Personal', 'Majibul Haque Hafezzahmed', NULL, NULL, NULL, '55451046', NULL, NULL, NULL, NULL, '26405000664', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9148, 1, NULL, 'Personal', 'Muhammad Rafi', NULL, NULL, NULL, '55277831', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9149, 1, NULL, 'Personal', 'Gangulli Mohammed', NULL, NULL, NULL, '55832497', NULL, NULL, NULL, NULL, '26035610365', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9150, 1, NULL, 'Corporate', 'New Yasrib Trading', NULL, NULL, NULL, '33834694', '77134228', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9151, 1, NULL, 'Corporate', 'M/s. Top Ten Restaurant', NULL, NULL, NULL, '30376452', '55853557', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9152, 1, NULL, 'Corporate', 'Hayya Baladna Trading', NULL, NULL, NULL, '30012351', '30939363', NULL, NULL, NULL, '173052', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9153, 1, NULL, 'Corporate', 'M/S. Naqi Store', NULL, NULL, NULL, '55521920', NULL, NULL, NULL, NULL, '19340', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9154, 1, NULL, 'Personal', 'MOHAMMED ASGAR ALI', NULL, NULL, NULL, '30376452', '55853557', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9155, 1, NULL, 'Corporate', 'AL Yasrib Electric Materials', NULL, NULL, NULL, '55553210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9156, 1, NULL, 'Personal', 'M/s.Nassem Al Ward Sewing', NULL, NULL, NULL, '55521920', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9157, 1, NULL, 'Personal', 'Amin Andullah Atef Mugammal', NULL, NULL, NULL, '33395978', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9158, 1, NULL, 'Personal', 'Muhammed Nasir Hussain Miazi', NULL, NULL, NULL, '33207531', NULL, NULL, NULL, NULL, '29005004534', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9159, 1, NULL, 'Personal', 'M.D SAIFUDDIN MOHD ELIAS', NULL, NULL, NULL, '55221726', '70694214', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9160, 1, NULL, 'Corporate', 'Abeer Perfume', NULL, NULL, NULL, '55042124', NULL, NULL, NULL, NULL, '108830', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9161, 1, NULL, 'Personal', 'Ashfaq Alam', NULL, NULL, NULL, '55805150', NULL, NULL, NULL, NULL, '24981800349', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9162, 1, NULL, 'Corporate', 'NEW AL HANAA SALOON', NULL, NULL, NULL, '55994580', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9163, 1, NULL, 'Corporate', 'Dafe Payment Services', NULL, NULL, NULL, '30279441', '66306885', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9164, 1, NULL, 'Personal', 'MAHAMOOD MOOSSA K', NULL, NULL, NULL, '33422600', '55930776', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9165, 1, NULL, 'Corporate', 'Key World Trading', NULL, NULL, NULL, '30844701', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9166, 1, NULL, 'Corporate', 'Jassim Al Naama Butchery', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9167, 1, NULL, 'Personal', 'MOHAMED ABDEL MAGAID', NULL, NULL, NULL, '55507086', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9168, 1, NULL, 'Personal', 'Yousef Mohd I Mandani', NULL, NULL, NULL, '55115500', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9169, 1, NULL, 'Personal', 'Satish Gopalakrishna Pillai', NULL, NULL, NULL, '70200012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9170, 1, NULL, 'Personal', 'AbdulAziz Mahmoud I M Emadi', NULL, NULL, NULL, '55000634', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9171, 1, NULL, 'Personal', 'Saoud Abdulaziz A l Emadi', NULL, NULL, NULL, '55770014', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9172, 1, NULL, 'Personal', 'Ahmed Yousef Qaddourah', NULL, NULL, NULL, '55550927', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9173, 1, NULL, 'Personal', 'Mustafe Abdurahiman', NULL, NULL, NULL, '30009442', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9174, 1, NULL, 'Personal', 'MS.ISHRAGA MOHAMED AHMED', NULL, NULL, NULL, '66033001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9175, 1, NULL, 'Personal', 'Tariq Ibrahim Ali Suliman', NULL, NULL, NULL, '55309821', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9176, 1, NULL, 'Personal', 'NABEEH RASHEED', NULL, NULL, NULL, '30421544', '55878088', NULL, NULL, NULL, '29335641247', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9177, 1, NULL, 'Personal', 'SANKAR DEBNATH', NULL, NULL, NULL, '66956814', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9178, 1, NULL, 'Personal', 'QUSAY MOHAMMED MUSTAFA', NULL, NULL, NULL, '55646080', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9179, 1, NULL, 'Personal', 'M/S. COOL & REST (IMRAN)', NULL, NULL, NULL, '50338554', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9180, 1, NULL, 'Personal', 'FAISAL MUHAMMAD NAZIR', NULL, NULL, NULL, '30106262', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9181, 1, NULL, 'Personal', 'KUNDU PARAMNBIL OUSEPH JOJI', NULL, NULL, NULL, '66213411', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9182, 1, NULL, 'Personal', 'Mahamood Achi Moosantakath Purayil', NULL, NULL, NULL, '70657865', NULL, NULL, NULL, NULL, '28835641261', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9183, 1, NULL, 'Personal', 'RUSHD BILAVINAKATH', NULL, NULL, NULL, '31348899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9184, 1, NULL, 'Personal', 'Yousef Mohammed Jaafarian', NULL, NULL, NULL, '66899870', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9185, 1, NULL, 'Corporate', 'Al Yazwa Trading & Contracting', NULL, NULL, NULL, '66104011', '55520147', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9186, 1, NULL, 'Personal', 'mirshad mohammed', NULL, NULL, NULL, '30200399', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9187, 1, NULL, 'Personal', 'Ahmed Mohd Hassan', NULL, NULL, NULL, '55378544', NULL, NULL, NULL, NULL, '27881800771', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9188, 1, NULL, 'Personal', 'Osman Kamal', NULL, NULL, NULL, '30575600', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9189, 1, NULL, 'Personal', 'Ahmed Ibrahim Al Naama', NULL, NULL, NULL, '66622822', NULL, NULL, NULL, NULL, '29463403920', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9190, 1, NULL, 'Personal', 'Jihad Marwan Khair', NULL, NULL, NULL, '55553478', NULL, NULL, NULL, NULL, '28276000055', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9191, 1, NULL, 'Personal', 'Ali Ibrahim A AL Emadi', NULL, NULL, NULL, '33043947', NULL, NULL, NULL, NULL, '29363403912', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9192, 1, NULL, 'Personal', 'Amina Al Emadi', NULL, NULL, NULL, '55528520', NULL, NULL, NULL, NULL, '26863401351', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9193, 1, NULL, 'Personal', 'Hazem Suleiman Mosleh', NULL, NULL, NULL, '55526441', NULL, NULL, NULL, NULL, '27140000154', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9194, 1, NULL, 'Personal', 'Mrs. Mona Yousef', NULL, NULL, NULL, '50945396', NULL, NULL, NULL, NULL, '28799900303', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9195, 1, NULL, 'Personal', 'ABDUL MATIN MD CHAN', NULL, NULL, NULL, '55389108', NULL, NULL, NULL, NULL, '25705000391', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9196, 1, NULL, 'Personal', 'KS MOHAMED KUNHI', NULL, NULL, NULL, '55529410', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9197, 1, NULL, 'Personal', 'MADINAT AL AKHTHAM', NULL, NULL, NULL, '55442337', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9198, 1, NULL, 'Personal', 'BEIGHT CAR  ABDURAHIMAN', NULL, NULL, NULL, '55547299', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9199, 1, NULL, 'Corporate', 'Investoreal Trdg & Intl', NULL, NULL, NULL, '44447271', '55511903', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9200, 1, NULL, 'Personal', 'JOHN MESSIH', NULL, NULL, NULL, '55942697', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9201, 1, NULL, 'Personal', 'SHAFFEQ MARAKKAN K PARAMBA', NULL, NULL, NULL, '55573618', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9202, 1, NULL, 'Personal', 'M.K. ABDUL LATHEEF', NULL, NULL, NULL, '55573613', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9203, 1, NULL, 'Personal', 'CHHAGALNAIYA TRD & CONT', NULL, NULL, NULL, '31460800', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9204, 1, NULL, 'Personal', 'M/S. AL YAZWA TRA & CONT(Facility Staff)', NULL, NULL, NULL, '66555915', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9205, 1, NULL, 'Personal', 'SADEKUL ISLAM', NULL, NULL, NULL, '55324775', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9206, 1, NULL, 'Personal', 'AMANULLA MUHARRAM', NULL, NULL, NULL, '50409460', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9207, 1, NULL, 'Personal', 'MOHAMED TAHA HOSSEIN', NULL, NULL, NULL, '55844025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9208, 1, NULL, 'Corporate', 'PRESTIGE CARS COMPANY', NULL, NULL, NULL, '55808610', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9209, 1, NULL, 'Corporate', 'Head Office (TRAD & CONT)', NULL, NULL, NULL, '50507799', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9210, 1, NULL, 'Personal', 'FAWZI MOHD KHALIF', NULL, NULL, NULL, '55243758', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9211, 1, NULL, 'Corporate', 'White trd & Contracting', NULL, NULL, NULL, '77675887', '55092864', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9212, 1, NULL, 'Personal', 'ABDUL MATIN MD CHAN', NULL, NULL, NULL, '55389108', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9213, 1, NULL, 'Personal', 'MUHAMMAD RIZWAN', NULL, NULL, NULL, '39987372', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9214, 1, NULL, 'Personal', 'SHEILA MARIE BAIS', NULL, NULL, NULL, '33899870', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9215, 1, NULL, 'Personal', 'NEILA H BOUKRAIEM', NULL, NULL, NULL, '66922335', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9216, 1, NULL, 'Personal', 'ABDULLA AHMED ABDEL RAHMAN', NULL, NULL, NULL, '55624482', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9217, 1, NULL, 'Personal', 'MONA TOLBA', NULL, NULL, NULL, '66117297', '55074454', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9218, 1, NULL, 'Personal', 'USAMA AZIZ', NULL, NULL, NULL, '66767005', NULL, NULL, NULL, NULL, '2825609531', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9219, 1, NULL, 'Personal', 'MUTASIM SALEH OSMAN', NULL, NULL, NULL, '55375468', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9220, 1, NULL, 'Personal', 'ABDUL MATEEN AURANG ZEB', NULL, NULL, NULL, '33498119', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9221, 1, NULL, 'Personal', 'ISLAM ABDELHAMID MOHAMMD ABDELSAL', NULL, NULL, NULL, '31112594', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9222, 1, NULL, 'Personal', 'FAIZ YOUSIF MOHAMED YOUSIF', NULL, NULL, NULL, '66268737', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9223, 1, NULL, 'Personal', 'Abdul Latheef', NULL, NULL, NULL, '55096552', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9224, 1, NULL, 'Personal', 'VENISE TANIOS NASSAR', NULL, NULL, NULL, '66037143', NULL, NULL, NULL, NULL, '27742200997', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9225, 1, NULL, 'Personal', 'MOHD FAKHRURRAZI', NULL, NULL, NULL, '33027514', NULL, NULL, NULL, NULL, '28345800432', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9226, 1, NULL, 'Personal', 'CITY STAR FOR REAL ESTATE', NULL, NULL, NULL, '55817018', '44431472', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9227, 1, NULL, 'Corporate', 'M.G.Group (Kunhali Kutty)', NULL, NULL, NULL, '30304485', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9228, 1, NULL, 'Personal', 'COOL & REST', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9229, 1, NULL, 'Corporate', 'DRAVA GENTS SALOON', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9230, 1, NULL, 'Corporate', 'LOVERA FOR SWEETS AND FLOWERS', NULL, NULL, NULL, '33153780', NULL, NULL, NULL, NULL, '22081', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9231, 1, NULL, 'Corporate', 'FIT FUEL TRADING', NULL, NULL, NULL, '30087008', NULL, NULL, NULL, NULL, '227992', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9232, 1, NULL, 'Personal', 'Hassan Yaquobi(BAKER)', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9233, 1, NULL, 'Personal', 'Hassan Yaqoubi(RESTRAUNT)', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9234, 1, NULL, 'Corporate', 'I CLEAN LAUNDRY', NULL, NULL, NULL, '55784666', NULL, NULL, NULL, NULL, '226019', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9235, 1, NULL, 'Personal', 'ABDURRASHEED ABDUL HAKKEEM', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9236, 1, NULL, 'Personal', 'FADIL AMEER', NULL, NULL, NULL, '50013008', NULL, NULL, NULL, NULL, '29135600883', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9237, 1, NULL, 'Personal', 'MUHAMMED HASHIR PATINCHARE PUNATHIL', NULL, NULL, NULL, '50791578', NULL, NULL, NULL, NULL, '29335626978', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9238, 1, NULL, 'Personal', 'RASHID THALAYILLATHU', NULL, NULL, NULL, '33578844', NULL, NULL, NULL, NULL, '29035626876', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9239, 1, NULL, 'Personal', 'ANSAR AHAMMAD', NULL, NULL, NULL, '55460535', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9240, 1, NULL, 'Personal', 'ABDULHAGH ABDOLRAHIM HANIFI', NULL, NULL, NULL, '30088785', NULL, NULL, NULL, NULL, '28336400623', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9241, 1, NULL, 'Personal', 'MOHAMED SHIYAM SAMHOON', NULL, NULL, NULL, '50773630', NULL, NULL, NULL, NULL, '28614409074', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9242, 1, NULL, 'Personal', 'PRADEEP AZHEEL VEETTIL', NULL, NULL, NULL, '66519406', NULL, NULL, NULL, NULL, '28335664309', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9243, 1, NULL, 'Personal', 'ANMARY JOY', NULL, NULL, NULL, '70405375', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9244, 1, NULL, 'Personal', 'MOHAMMED ABDUL RAUF', NULL, NULL, NULL, '33855828', NULL, NULL, NULL, NULL, '27435627051', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9245, 1, NULL, 'Personal', 'HASSAN AHMAD HAMRA', NULL, NULL, NULL, '33746124', NULL, NULL, NULL, NULL, '27876001074', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9246, 1, NULL, 'Personal', 'SHANID KANNANTHODI VAYOLIPARAMBATH', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9247, 1, NULL, 'Personal', 'NOORULISLAM SAID WALI', NULL, NULL, NULL, '55991345', '33116812', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9248, 1, NULL, 'Corporate', 'EDEX FREIGHYT SERVICES LLC', NULL, NULL, NULL, '55917216', '55957555', NULL, NULL, NULL, '184533', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9249, 1, NULL, 'Personal', 'SHAMMA NOSHIR ZAINABA', NULL, NULL, NULL, '70073786', '77912119', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9250, 1, NULL, 'Personal', 'FAROOK AHMED FAZEL AHMED', NULL, NULL, NULL, '55516862', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9251, 1, NULL, 'Personal', 'FATMA SALEM AL HAMAD', NULL, NULL, NULL, '55901666', '55652755', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9252, 1, NULL, 'Personal', 'KHALID SAEED', NULL, NULL, NULL, '55558950', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9253, 1, NULL, 'Personal', 'HAMMAM AHMAD', NULL, NULL, NULL, '55577858', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9254, 1, NULL, 'Personal', 'KAMALUDHEEN P KHADAR', NULL, NULL, NULL, '66087646', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9255, 1, NULL, 'Personal', 'JAFAR MATTUMMAL', NULL, NULL, NULL, '66967077', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9256, 1, NULL, 'Personal', 'SULTAN IBRAHIM SL AL HASHMI', NULL, NULL, NULL, '55515584', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9257, 1, NULL, 'Personal', 'VALIYAKATH V T UMMER', NULL, NULL, NULL, '55454004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9258, 1, NULL, 'Personal', 'YOUSEF ALI M AL EMADI', NULL, NULL, NULL, '26163400427', NULL, NULL, NULL, NULL, '55531097', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9259, 1, NULL, 'Personal', 'ALI AHAMED A M AL SULAITI', NULL, NULL, NULL, '55566590', NULL, NULL, NULL, NULL, '28563401175', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9260, 1, NULL, 'Personal', 'MUHAMMED NOUSHAD ABOO KANDATHIL', NULL, NULL, NULL, '33188200', '33112400', NULL, NULL, NULL, '26635617081', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9261, 1, NULL, 'Personal', 'HAMAD IBRAHIM H I AL BADR', NULL, NULL, NULL, '55669556', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9262, 1, NULL, 'Personal', 'RAIHANA PALOT T ABOOBACKER', NULL, NULL, NULL, '31231696', '33458865', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9263, 1, NULL, 'Personal', 'NAVAS VANNATHAN K', NULL, NULL, NULL, '66706786', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9264, 1, NULL, 'Personal', 'ABDALLA SALEM', NULL, NULL, NULL, '31444041', NULL, NULL, NULL, NULL, '30081800597', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9265, 1, NULL, 'Personal', 'MUHAMMED ALI KURDI', NULL, NULL, NULL, '50424259', NULL, NULL, NULL, NULL, '28979201334', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9266, 1, NULL, 'Personal', 'MOHAMMED HUZAIFA', NULL, NULL, NULL, '77266440', NULL, NULL, NULL, NULL, '29358601398', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9267, 1, NULL, 'Personal', 'JASSIM DARWISH A A MASHHADI', NULL, NULL, NULL, '55199915', NULL, NULL, NULL, NULL, '27963403124', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9268, 1, NULL, 'Personal', 'SULAIMAN KHALID A ALAZWANI', NULL, NULL, NULL, '55889120', NULL, NULL, NULL, NULL, '28151200033', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9269, 1, NULL, 'Personal', 'MURAD ALSOUFI', NULL, NULL, NULL, '30394477', NULL, NULL, NULL, NULL, '28988600671', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9270, 1, NULL, 'Personal', 'HOT & COOL', NULL, NULL, NULL, '66083535', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9271, 1, NULL, 'Personal', 'HOT & COOL', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9272, 1, NULL, 'Personal', 'TOP END AUTO DTS', NULL, NULL, NULL, '50290779', '71205871', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9273, 1, NULL, 'Personal', 'TOP END AUTO DTS', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9274, 1, NULL, 'Corporate', 'AL FAJER SALOON', NULL, NULL, NULL, '55966888', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9275, 1, NULL, 'Personal', 'SELVADO ABHAYA', NULL, NULL, NULL, '66880866', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9276, 1, NULL, 'Personal', 'PICK FRESH', NULL, NULL, NULL, '66706786', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9277, 1, NULL, 'Personal', 'JAMEEL FOOD STUFF', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9278, 1, NULL, 'Personal', 'TOP LINE', NULL, NULL, NULL, '33533250', '66444284', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9279, 1, NULL, 'Personal', 'TOP LINE', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9280, 1, NULL, 'Corporate', 'Jameel Food(Mezanine Store)', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9281, 1, NULL, 'Personal', 'CHILLYS HOT & COOL', NULL, NULL, NULL, '66083535', '33822168', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9282, 1, NULL, 'Personal', 'SOUK AL JUMLA', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9283, 1, NULL, 'Personal', 'CHILLYS HOT & COOL', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9284, 1, NULL, 'Corporate', 'TOP LINE CARS', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9285, 1, NULL, 'Personal', 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9286, 1, NULL, 'Personal', 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, NULL, '55606141', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9287, 1, NULL, 'Personal', 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, NULL, '66608926', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9288, 1, NULL, 'Corporate', 'WHOLE SALE MARKET', NULL, NULL, NULL, '55274498', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9289, 1, NULL, 'Corporate', 'WHOLE SALE MARKET', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9290, 1, NULL, 'Corporate', 'WHOLE SALE MARKET STORE', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9291, 1, NULL, 'Corporate', 'Souq Al Jamla(Mezanine Store)', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, 0),
(9400, 1, NULL, 'Personal', 'Abdulaziz Khalid A H Al Marzooqi', NULL, NULL, NULL, '70084444', NULL, NULL, NULL, NULL, '29463402168', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9401, 1, NULL, 'Personal', 'Muqaddam Mohammed MM Al Boainain', NULL, NULL, NULL, '33293333', NULL, NULL, NULL, NULL, '30263403365', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9402, 1, NULL, 'Personal', 'Ahmad Mohammed M M Al Boainain', NULL, NULL, NULL, '50377222', NULL, NULL, NULL, NULL, '30663401148', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9403, 1, NULL, 'Personal', 'Mohammed Zaid S A al Kuwari', NULL, NULL, NULL, '50065066', NULL, NULL, NULL, NULL, '29863405109', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9404, 1, NULL, 'Personal', 'Khalid Hassen M M Al Ansari', NULL, NULL, NULL, '66668762', NULL, NULL, NULL, NULL, '29663404374', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9405, 1, NULL, 'Personal', 'Farhood Abdulla R R Al Hajri', NULL, NULL, NULL, '77666702', NULL, NULL, NULL, NULL, '28663401177', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9406, 1, NULL, 'Personal', 'Mohamed Abdull R R Al Hajri', NULL, NULL, NULL, '33988883', NULL, NULL, NULL, NULL, '29463401611', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9407, 1, NULL, 'Personal', 'Abdulaziz Ali H M Al-Rashid', NULL, NULL, NULL, '55550598', NULL, NULL, NULL, NULL, '28463403223', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9408, 1, NULL, 'Personal', 'Ahmed Ali H M Al-Rashid', NULL, NULL, NULL, '55552334', NULL, NULL, NULL, NULL, '27963402398', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9409, 1, NULL, 'Personal', 'Fahad Ali HM Al Rashid', NULL, NULL, NULL, '55555378', NULL, NULL, NULL, NULL, '27863400652', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9410, 1, NULL, 'Personal', 'Khalid Salem KH A AL-Hajri', NULL, NULL, NULL, '66668762', NULL, NULL, NULL, NULL, '29863400035', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9411, 1, NULL, 'Personal', 'Fahad Mohammed S B Al Mansoori', NULL, NULL, NULL, '50555116', NULL, NULL, NULL, NULL, '29963404208', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9412, 1, NULL, 'Personal', 'Rashid Juma R M Al-m Muhannadi', NULL, NULL, NULL, '55324039', NULL, NULL, NULL, NULL, '28963400914', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9413, 1, NULL, 'Personal', 'Rashid Abdulla R R Al Hajri', NULL, NULL, NULL, '70070130', NULL, NULL, NULL, NULL, '30263405291', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9414, 1, NULL, 'Personal', 'Mohammed Yousef J K Al Sulaiti', NULL, NULL, NULL, '66116633', NULL, NULL, NULL, NULL, '28763401819', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9415, 1, NULL, 'Personal', 'Hamad Jassim A j AL Jalabi', NULL, NULL, NULL, '66990010', NULL, NULL, NULL, NULL, '29363400146', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9416, 1, NULL, 'Personal', 'Ali Hassen M A AL Hajri', NULL, NULL, NULL, '66664763', NULL, NULL, NULL, NULL, '29163400660', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9417, 1, NULL, 'Personal', 'Nasser Abdulla A A AL- Amer', NULL, NULL, NULL, '66633309', NULL, NULL, NULL, NULL, '28963401619', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9418, 1, NULL, 'Personal', 'Khaleefa Hussain A I AL Wali', NULL, NULL, NULL, '5090380', NULL, NULL, NULL, NULL, '30036400095', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9419, 1, NULL, 'Personal', 'Jassim Hussain M A Alyafei', NULL, NULL, NULL, '33346496', NULL, NULL, NULL, NULL, '30563406218', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9420, 1, NULL, 'Personal', 'Ali Mahmoud H A Makki', NULL, NULL, NULL, '55519985', NULL, NULL, NULL, NULL, '29163403819', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9421, 1, NULL, 'Personal', 'Khalid M Ameen M J Al-Shafai', NULL, NULL, NULL, '66555498', NULL, NULL, NULL, NULL, '28263400779', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9422, 1, NULL, 'Personal', 'Yousef Mohd Mandani Al-Emadi', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9423, 1, NULL, 'Personal', 'Abdul Aziz Saad A A Al-Ali', NULL, NULL, NULL, '51119891', NULL, NULL, NULL, NULL, '29963404623', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9424, 1, NULL, 'Personal', 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, NULL, '555177881', NULL, NULL, NULL, NULL, '28763401230', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0);
INSERT INTO `tenants` (`id`, `company_id`, `user_id`, `tenant_type`, `full_name`, `nationality`, `gender`, `dob`, `phone`, `whatsapp`, `email`, `company_name`, `company_cr`, `qid_no`, `qid_expiry`, `passport_no`, `passport_expiry`, `emergency_name`, `emergency_phone`, `emergency_relation`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`, `current_unit_id`, `is_blacklisted`) VALUES
(9425, 1, NULL, 'Personal', 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9426, 1, NULL, 'Personal', 'Ahmed Abdul Aziz A A Al Maliki', NULL, NULL, NULL, '50500519', NULL, NULL, NULL, NULL, '29763402612', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9427, 1, NULL, 'Personal', 'Ali Husain M A Shehabi', NULL, NULL, NULL, '30124444', NULL, NULL, NULL, NULL, '30204800013', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9428, 1, NULL, 'Personal', 'Yousef Al Hammadi', NULL, NULL, NULL, '55886689', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9429, 1, NULL, 'Personal', 'Abdul Aziz', NULL, NULL, NULL, '55512412', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9430, 1, NULL, 'Personal', 'Ali Ahmed M H Al Sada', NULL, NULL, NULL, '55890909', NULL, NULL, NULL, NULL, '28163402639', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9431, 1, NULL, 'Personal', 'Ahmed Hassan AH Fakhroo', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '28663401076', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9432, 1, NULL, 'Personal', 'MOHAMMED HASSAN M A AL MUFTAH', NULL, NULL, NULL, '77000727', NULL, NULL, NULL, NULL, '29763401173', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9433, 1, NULL, 'Personal', 'Khalifa Omar S O Al Hemaidi', NULL, NULL, NULL, '50304747', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0),
(9434, 1, NULL, 'Personal', 'Abdulla Ahmed M A Al-Buainain', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '29563401717', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `unit_number` varchar(50) NOT NULL,
  `floor` varchar(20) DEFAULT NULL,
  `unit_type` varchar(50) DEFAULT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `owner_mobile` varchar(30) DEFAULT NULL,
  `owner_email` varchar(100) DEFAULT NULL,
  `tenant_name` varchar(100) DEFAULT NULL,
  `tenant_mobile` varchar(30) DEFAULT NULL,
  `tenant_email` varchar(100) DEFAULT NULL,
  `contract_number` varchar(50) DEFAULT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `rent_amount` decimal(12,2) DEFAULT NULL,
  `security_deposit` decimal(12,2) DEFAULT NULL,
  `status` enum('vacant','occupied','maintenance','reserved') NOT NULL DEFAULT 'vacant',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `contract_attachment` varchar(255) DEFAULT NULL,
  `plate_number` varchar(30) DEFAULT NULL,
  `bedrooms` tinyint(3) UNSIGNED DEFAULT NULL,
  `bathrooms` tinyint(3) UNSIGNED DEFAULT NULL,
  `furnished` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `facility_id`, `unit_number`, `floor`, `unit_type`, `area_sqft`, `owner_name`, `owner_mobile`, `owner_email`, `tenant_name`, `tenant_mobile`, `tenant_email`, `contract_number`, `contract_start`, `contract_end`, `rent_amount`, `security_deposit`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `contract_attachment`, `plate_number`, `bedrooms`, `bathrooms`, `furnished`, `description`) VALUES
(0, 0, 'A03', '1', 'apartment', 1800.00, '', '', '', 'Muhammed Hashir', '30976558', '', 'E0101', '2026-05-01', '2027-05-08', 4000.00, 3999.98, 'occupied', '', 1, '2026-08-08 17:49:14', '2026-08-08 17:49:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9001, 9001, '101', '1', '2BR', 1200.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'occupied', NULL, 1, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9101, 9101, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-3101', '2022-06-01', '2027-05-31', 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9102, 9101, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-2', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9103, 9101, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-3', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9104, 9101, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-4', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9105, 9101, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-5', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9106, 9101, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-6', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9107, 9101, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-7', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9108, 9101, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-8', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9109, 9101, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-9', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9110, 9101, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-10', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9111, 9101, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-11', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9112, 9101, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-12', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9113, 9101, '13', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-13', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9114, 9101, '14', NULL, NULL, NULL, NULL, NULL, NULL, 'SUPREME COMMITTEE FOR DELIVERY & LEGACY', NULL, NULL, 'REF-14', NULL, NULL, 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9115, 9101, '15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REF-15', NULL, NULL, NULL, NULL, 'vacant', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9116, 9101, '16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REF-16', NULL, NULL, NULL, NULL, 'vacant', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9117, 9102, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'YESUDAS KAITHAYALAPPIL', '55298197', NULL, 'REF-3201', '2024-12-01', '2026-12-31', 4250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9118, 9102, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'SAIF HASSAN AWADALLA', '55978737', NULL, 'REF-3202', '2024-10-01', '2026-09-30', 5750.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9119, 9102, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'COOL & REST TRDG (STAFFS)', '33766555', NULL, 'REF-3203', '2024-09-01', '2026-08-31', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9120, 9102, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'COOL AND REST', '66169064', NULL, 'REF-3204', '2024-10-01', '2026-09-30', 2750.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9121, 9102, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'COOL AND REST (SHABIR)', '66169064', NULL, 'REF-3205', '2025-07-01', '2026-06-30', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9122, 9102, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDULLA BASHIR KHAN', '30080637', NULL, 'REF-3206', '2024-12-01', '2026-11-30', 3300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9123, 9102, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'MATHEW SIMON', '55867916', NULL, 'REF-3207', '2024-10-01', '2025-09-30', 4750.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9124, 9102, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'MECH & TECH (RESHMI JOHN)', '74789579', NULL, 'REF-3208', '2025-05-01', '2027-04-30', 2200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9125, 9102, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'MARY GRACE CABARDO', '77750250', NULL, 'REF-3209', '2025-02-01', '2027-02-28', 1750.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9126, 9102, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMMED HOSSAN ABDURRAHIM', '55318268', NULL, 'REF-3210', '2015-06-01', '2016-05-31', 6000.00, NULL, 'occupied', 'Agreement With Ouqaf', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9127, 9102, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'MANAF POTTAMMAL', '30091155', NULL, 'REF-3211', '2024-01-01', '2026-12-31', 1800.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9128, 9103, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Othman Mohd Al Emadi', NULL, NULL, 'REF-3220', NULL, NULL, 13000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9129, 9104, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Hassam Pervez', '77998002', NULL, 'REF-3301', '2023-08-01', '2026-12-31', 4400.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9130, 9104, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'Shanavas M (AL YAZWA TRDG & CONT', '66555916', NULL, 'REF-3302', '2024-11-01', '2026-11-30', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9131, 9104, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'M/s. Cool N Rest - Vacant', '33766555', NULL, 'REF-3303', '2024-08-01', '2026-07-31', 0.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9132, 9104, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'Ouseph Shaji Kunduparambil', '55797010', NULL, 'REF-3304', '2024-10-01', '2026-09-30', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9133, 9104, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'Khaled Abdulkarim', '55210077', NULL, 'REF-3305', '2024-08-01', '2026-07-31', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9134, 9104, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'ASHRAF DYAB MOHAMED ABDELR', '55606857', NULL, 'REF-3306', '2024-01-01', '2026-12-31', 4100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9135, 9104, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDELBAST ELNAGI MOHAMED', '55240366', NULL, 'REF-3307', '2024-05-01', '2027-04-30', 4600.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9136, 9104, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDI HASSAN JAMA', '55545160', NULL, 'REF-3308', '2024-01-01', '2026-12-31', 4300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9137, 9104, 'OH-1', NULL, NULL, NULL, NULL, NULL, NULL, 'Sadikh Kalanad', '66555953', NULL, 'REF-3309', '2024-01-01', '2026-12-31', 1100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9138, 9104, 'OH-2', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. Cool and Rest', '33766555', NULL, 'REF-3310', '2024-01-01', '2026-12-31', 1100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9139, 9104, 'U38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vacant', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9140, 9105, 'WB-50', NULL, NULL, NULL, NULL, NULL, NULL, 'Global dry Land', '44261878', NULL, 'REF-3401', '2024-01-01', '2025-12-31', NULL, NULL, 'occupied', 'Contract Forwarded Waiting For Signature', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9141, 9105, 'WB-31', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDUL AZIZ AL EMADI', '55501000', NULL, 'REF-3402', '2025-10-01', '2027-09-30', 24000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9142, 9106, '14', NULL, NULL, NULL, NULL, NULL, NULL, 'Mohd Hamad AS Hbabi', '66661024', NULL, 'REF-3501', '2023-10-15', '2026-10-14', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9143, 9107, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'HUSSEINI ABDELKARIM ELSAYED MOHAMED', '66121077', NULL, 'REF-3601', '2026-03-01', '2027-02-28', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9144, 9107, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'HENRY GACUSAN JABAT', '70188768', NULL, 'REF-3602', '2024-07-01', '2027-06-30', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9145, 9107, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHD IBRAHIM ABDELA', '33030379', NULL, 'REF-3603', '2024-02-01', '2027-02-28', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9146, 9107, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. ROCK TRANSPORTING & RENT', '66100564', NULL, 'REF-3604', '2024-08-01', '2026-07-31', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9147, 9107, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'Nasser Ahmed Kolliyil', '55810190', NULL, 'REF-3605', '2024-09-01', '2026-08-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9148, 9107, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'Chief Tailor', '55804172', NULL, 'REF-3606', '2024-03-01', '2027-02-28', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9149, 9107, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. MECH & TECH', '74789579', NULL, 'REF-3607', '2024-11-01', '2026-10-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9150, 9107, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'Lokendra Bahadur Karki', '77153392', NULL, 'REF-3608', '2025-09-01', '2027-08-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9151, 9107, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmed Ali Gasmalla Bagari', '33784229', NULL, 'REF-3609', '2024-07-01', '2027-06-30', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9152, 9107, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'Nalakath Sulaiman Manafe', '33394202', NULL, 'REF-3610', '2026-07-01', '2028-05-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9153, 9107, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'Mohammed Shawki', '55527429', NULL, 'REF-3611', '2024-02-01', '2027-01-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9154, 9107, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'Faisal Ishaq Qadir Ishaq', '77151945', NULL, 'REF-3612', '2025-09-01', '2027-08-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9155, 9107, '13', NULL, NULL, NULL, NULL, NULL, NULL, 'Fahad Abdul Hameed', '70927817', NULL, 'REF-3613', '2024-03-01', '2027-02-28', 4250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9156, 9107, '14', NULL, NULL, NULL, NULL, NULL, NULL, 'Manakkat Thekkepeedikayil Kunhi', '70715588', NULL, 'REF-3614', '2022-10-01', '2026-09-30', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9157, 9107, '15', NULL, NULL, NULL, NULL, NULL, NULL, 'AL A.M.A AL ABDULLA', '55093010', NULL, 'REF-3615', '2024-06-01', '2027-05-31', 1000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9158, 9107, 'U57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vacant', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9159, 9108, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Islam toufal', '55229672', NULL, 'REF-1101', '2024-02-01', '2027-01-31', 6400.00, NULL, 'occupied', 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9160, 9108, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'Faisal Kakkuziulla Parambath', '55819842', NULL, 'REF-1102', '2024-02-01', '2027-01-31', 6500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9161, 9108, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'Ibrahim Abdullah harischandra', '33552936', NULL, 'REF-1103', '2024-07-01', '2027-06-30', 5900.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9162, 9108, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'Ali Abdulkarim Ahmed', '66781011', NULL, 'REF-1104', '2024-07-01', '2027-06-30', 5600.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9163, 9108, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'Muhommad Usman Azam', '33811218', NULL, 'REF-1105', '2024-12-01', '2026-11-30', 6300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9164, 9108, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'Majibul Haque Hafezzahmed', '55451046', NULL, 'REF-1106', '2026-02-01', '2027-01-31', 6500.00, NULL, 'occupied', 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9165, 9108, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'Muhammad Rafi', '55277831', NULL, 'REF-1107', '2024-03-01', '2027-02-28', 5650.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9166, 9108, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'Gangulli Mohammed', '55832497', NULL, 'REF-1108', '2024-02-01', '2027-01-31', 5850.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9167, 9108, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'New Yasrib Trading', '33834694', NULL, 'REF-1109', '2024-11-01', '2026-11-30', 6200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9168, 9108, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'M/s. Top Ten Restaurant', '30376452', NULL, 'REF-1110', '2024-10-01', '2026-09-30', 6500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9169, 9108, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'Hayya Baladna Trading', '30012351', NULL, 'REF-1111', '2026-01-08', '2027-07-31', 6000.00, NULL, 'occupied', 'First Fifteen days Grace', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9170, 9108, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. Naqi Store', '55521920', NULL, 'REF-1112', '2022-12-01', '2026-11-30', 5300.00, NULL, 'occupied', 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9171, 9108, '13', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMMED ASGAR ALI', '30376452', NULL, 'REF-1113', '2024-10-15', '2026-10-14', 6000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9172, 9108, '14', NULL, NULL, NULL, NULL, NULL, NULL, 'AL Yasrib Electric Materials', '55553210', NULL, 'REF-1114', '2024-10-01', '2026-09-30', 6500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9173, 9108, '15', NULL, NULL, NULL, NULL, NULL, NULL, 'M/s.Nassem Al Ward Sewing', '55521920', NULL, 'REF-1115', '2024-12-01', '2026-11-30', 6300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9174, 9108, '16', NULL, NULL, NULL, NULL, NULL, NULL, 'Amin Andullah Atef Mugammal', '33395978', NULL, 'REF-1116', '2024-03-01', '2027-02-28', 6100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9175, 9108, '17', NULL, NULL, NULL, NULL, NULL, NULL, 'Muhammed Nasir Hussain Miazi', '33207531', NULL, 'REF-1117', '2026-06-01', '2027-05-31', 6500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9176, 9108, '18', NULL, NULL, NULL, NULL, NULL, NULL, 'M.D SAIFUDDIN MOHD ELIAS', '55221726', NULL, 'REF-1118', '2022-12-01', '2027-01-31', 6800.00, NULL, 'occupied', 'Expired', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9177, 9108, '19', NULL, NULL, NULL, NULL, NULL, NULL, 'Abeer Perfume', '55042124', NULL, 'REF-1119', '2024-01-01', '2026-12-31', 6300.00, NULL, 'occupied', 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9178, 9108, '20', NULL, NULL, NULL, NULL, NULL, NULL, 'Ashfaq Alam', '55805150', NULL, 'REF-1120', '2024-02-01', '2026-01-31', 6000.00, NULL, 'occupied', 'Contract Ready ,to be Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9179, 9108, 'Shp#4', NULL, NULL, NULL, NULL, NULL, NULL, 'NEW AL HANAA SALOON', '55994580', NULL, 'REF-1121', '2023-03-01', '2028-02-28', 7000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9180, 9108, 'Shp#1', NULL, NULL, NULL, NULL, NULL, NULL, 'Dafe Payment Services', '30279441', NULL, 'REF-1122', '2024-03-01', '2027-02-28', 6200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9181, 9108, 'Shp#2', NULL, NULL, NULL, NULL, NULL, NULL, 'MAHAMOOD MOOSSA K', '33422600', NULL, 'REF-1123', '2024-05-01', '2026-04-30', 6000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9182, 9108, 'Shp#3', NULL, NULL, NULL, NULL, NULL, NULL, 'Key World Trading', '30844701', NULL, 'REF-1124', '2024-09-01', '2026-08-31', 6350.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9183, 9108, 'Shp#5', NULL, NULL, NULL, NULL, NULL, NULL, 'Jassim Al Naama Butchery', NULL, NULL, 'REF-1125', '2025-05-06', '2027-05-05', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9184, 9109, 'NO-301', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMED ABDEL MAGAID', '55507086', NULL, 'REF-1201', '2023-09-01', '2026-08-31', 9300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9185, 9109, 'NO-302', NULL, NULL, NULL, NULL, NULL, NULL, 'Yousef Mohd I Mandani', '55115500', NULL, 'REF-1202', '2025-01-01', '2026-12-31', 7500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9186, 9109, 'NO-303', NULL, NULL, NULL, NULL, NULL, NULL, 'Satish Gopalakrishna Pillai', '70200012', NULL, 'REF-1203', '2025-01-01', '2026-12-31', 13000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9187, 9109, 'NO-304', NULL, NULL, NULL, NULL, NULL, NULL, 'AbdulAziz Mahmoud I M Emadi', '55000634', NULL, 'REF-1204', '2023-07-01', '2027-06-30', 7500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9188, 9109, 'No-305', NULL, NULL, NULL, NULL, NULL, NULL, 'Saoud Abdulaziz A l Emadi', '55770014', NULL, 'REF-1205', '2022-03-15', '2027-03-14', 7500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9189, 9109, 'NO-306', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmed Yousef Qaddourah', '55550927', NULL, 'REF-1206', '2024-06-01', '2027-05-31', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9190, 9110, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Mustafe Abdurahiman', '30009442', NULL, 'REF-1207', '2025-08-01', '2026-07-31', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9191, 9110, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'MS.ISHRAGA MOHAMED AHMED', '66033001', NULL, 'REF-1208', '2024-07-01', '2027-06-30', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9192, 9110, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'Tariq Ibrahim Ali Suliman', '55309821', NULL, 'REF-1209', '2024-01-01', '2026-12-31', 3200.00, NULL, 'occupied', 'Ready,Waiting For Tenant Signature', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9193, 9110, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'NABEEH RASHEED', '30421544', NULL, 'REF-1210', '2023-03-01', '2027-02-28', 3300.00, NULL, 'occupied', 'Signed', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9194, 9110, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'SANKAR DEBNATH', '66956814', NULL, 'REF-1211', '2024-08-01', '2027-07-31', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9195, 9110, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'QUSAY MOHAMMED MUSTAFA', '55646080', NULL, 'REF-1212', '2024-06-01', '2027-05-31', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9196, 9110, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. COOL & REST (IMRAN)', '50338554', NULL, 'REF-1213', '2024-10-01', '2026-09-30', 2500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9197, 9110, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'FAISAL MUHAMMAD NAZIR', '30106262', NULL, 'REF-1214', '2021-12-01', '2027-04-30', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9198, 9110, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'KUNDU PARAMNBIL OUSEPH JOJI', '66213411', NULL, 'REF-1215', '2023-10-01', '2026-09-30', 2500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9199, 9110, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'Mahamood Achi Moosantakath Purayil', '70657865', NULL, 'REF-1216', '2026-07-01', '2028-06-30', 1600.00, NULL, 'occupied', '45 Days Grace(01.07.26 to 15.08.26)', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9200, 9110, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'RUSHD BILAVINAKATH', '31348899', NULL, 'REF-1217', '2024-11-01', '2026-10-31', 2500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9201, 9110, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'Yousef Mohammed Jaafarian', '66899870', NULL, 'REF-1218', '2024-04-01', '2027-03-31', 3200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9202, 9110, '13', NULL, NULL, NULL, NULL, NULL, NULL, 'Al Yazwa Trading & Contracting', '66104011', NULL, 'REF-1219', '2025-05-01', '2027-04-30', 2500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9203, 9110, '14', NULL, NULL, NULL, NULL, NULL, NULL, 'mirshad mohammed', '30200399', NULL, 'REF-1220', '2024-03-01', '2027-02-28', 3300.00, NULL, 'occupied', 'Ismail Informed to Hold the Renewal', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9204, 9110, '15', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmed Mohd Hassan', '55378544', NULL, 'REF-1221', '2024-01-01', '2027-03-31', 3100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9205, 9110, '16', NULL, NULL, NULL, NULL, NULL, NULL, 'Osman Kamal', '30575600', NULL, 'REF-1222', '2024-08-01', '2027-09-30', 3400.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9206, 9111, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmed Ibrahim Al Naama', '66622822', NULL, 'REF-1225', '2024-09-01', '2026-08-31', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9207, 9111, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'Jihad Marwan Khair', '55553478', NULL, 'REF-1226', '2024-03-01', '2027-02-28', 9500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9208, 9111, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'Ali Ibrahim A AL Emadi', '33043947', NULL, 'REF-1227', '2023-04-01', '2027-03-31', 10000.00, NULL, 'occupied', 'Vila No:3, St No:824,Zone No:42', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9209, 9111, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'Amina Al Emadi', '55528520', NULL, 'REF-1228', '2023-04-01', '2027-03-31', 5000.00, NULL, 'occupied', 'Vila No:4, St No:824,Zone No:42', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9210, 9111, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'Hazem Suleiman Mosleh', '55526441', NULL, 'REF-1229', '2024-04-01', '2027-03-31', 9500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9211, 9111, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'Mrs. Mona Yousef', '50945396', NULL, 'REF-1230', '2024-03-01', '2027-02-28', 10000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9212, 9112, '1301', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDUL MATIN MD CHAN', '55389108', NULL, 'REF-15', '2024-03-01', '2027-02-28', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9213, 9112, '1302', NULL, NULL, NULL, NULL, NULL, NULL, 'KS MOHAMED KUNHI', '55529410', NULL, 'REF-13', '2024-07-01', '2027-06-30', 11200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9214, 9112, '1303', NULL, NULL, NULL, NULL, NULL, NULL, 'MADINAT AL AKHTHAM', '55442337', NULL, 'REF-14', '2025-03-01', '2027-02-28', 9750.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9215, 9112, '1304', NULL, NULL, NULL, NULL, NULL, NULL, 'BEIGHT CAR  ABDURAHIMAN', '55547299', NULL, 'REF-12', '2024-10-01', '2028-07-31', 12500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9216, 9113, 'Labour Acco', NULL, NULL, NULL, NULL, NULL, NULL, 'Investoreal Trdg & Intl', '44447271', NULL, 'REF-1401', '2024-08-01', '2027-07-31', 42000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9217, 9114, 'V 3', NULL, NULL, NULL, NULL, NULL, NULL, 'JOHN MESSIH', '55942697', NULL, 'REF-1501', NULL, '2026-10-31', 3500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9218, 9114, 'V 36 D', NULL, NULL, NULL, NULL, NULL, NULL, 'SHAFFEQ MARAKKAN K PARAMBA', '55573618', NULL, 'REF-1502', '2022-11-01', '2026-10-31', 3500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9219, 9114, 'V 42 U', NULL, NULL, NULL, NULL, NULL, NULL, 'M.K. ABDUL LATHEEF', '55573613', NULL, 'REF-1503', NULL, '2026-10-31', 3200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9220, 9114, 'V 25', NULL, NULL, NULL, NULL, NULL, NULL, 'CHHAGALNAIYA TRD & CONT', '31460800', NULL, 'REF-1504', NULL, '2026-08-31', 6200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9221, 9114, 'v 23', NULL, NULL, NULL, NULL, NULL, NULL, 'M/S. AL YAZWA TRA & CONT(Facility Staff)', '66555915', NULL, 'REF-1505', '2023-07-01', '2026-06-30', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9222, 9114, 'V 32', NULL, NULL, NULL, NULL, NULL, NULL, 'SADEKUL ISLAM', '55324775', NULL, 'REF-1506', '2024-03-10', '2027-03-31', 7500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9223, 9114, 'V 36 U', NULL, NULL, NULL, NULL, NULL, NULL, 'AMANULLA MUHARRAM', '50409460', NULL, 'REF-1507', '2022-12-01', '2026-11-30', 3300.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9224, 9114, 'V 42 G', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMED TAHA HOSSEIN', '55844025', NULL, 'REF-1508', '2024-11-01', '2026-10-31', 3600.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9225, 9115, '23', NULL, NULL, NULL, NULL, NULL, NULL, 'PRESTIGE CARS COMPANY', '55808610', NULL, 'REF-1701', '2024-02-01', '2027-01-31', 21000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9226, 9116, '216', NULL, NULL, NULL, NULL, NULL, NULL, 'Head Office (TRAD & CONT)', '50507799', NULL, 'REF-1', '2024-07-01', '2026-06-30', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9227, 9117, 'WAK- 117', NULL, NULL, NULL, NULL, NULL, NULL, 'FAWZI MOHD KHALIF', '55243758', NULL, 'REF-5101', '2024-03-01', '2027-02-28', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9228, 9117, 'WAK - 115', NULL, NULL, NULL, NULL, NULL, NULL, 'FAWZI MOHD KHALIF', '55243758', NULL, 'REF-5102', '2024-03-01', '2027-02-28', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9229, 9117, 'WAK- 20', NULL, NULL, NULL, NULL, NULL, NULL, 'White trd & Contracting', '77675887', NULL, 'REF-5103', '2024-03-01', '2027-02-28', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9230, 9117, 'WAK-18', NULL, NULL, NULL, NULL, NULL, NULL, 'FAWZI MOHD KHALIF', '55243758', NULL, 'REF-5104', '2024-05-01', '2026-04-30', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9231, 9118, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDUL MATIN MD CHAN', '55389108', NULL, 'REF-5201', '2024-10-01', '2026-09-30', 17000.00, NULL, 'occupied', 'Building No 03', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9232, 9119, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'MUHAMMAD RIZWAN', '39987372', NULL, 'REF-5301', '2024-03-01', '2027-03-31', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9233, 9119, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'SHEILA MARIE BAIS', '33899870', NULL, 'REF-5302', '2024-03-01', '2027-03-31', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9234, 9119, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'NEILA H BOUKRAIEM', '66922335', NULL, 'REF-5303', '2024-03-01', '2027-02-28', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9235, 9119, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDULLA AHMED ABDEL RAHMAN', '55624482', NULL, 'REF-5304', '2024-02-01', '2027-01-31', 4800.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9236, 9119, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'MONA TOLBA', '66117297', NULL, 'REF-5305', '2024-10-01', '2026-09-30', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9237, 9119, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'USAMA AZIZ', '66767005', NULL, 'REF-5306', '2024-04-01', '2027-03-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9238, 9119, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'MUTASIM SALEH OSMAN', '55375468', NULL, 'REF-5307', '2024-01-01', '2026-12-31', 4800.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9239, 9119, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDUL MATEEN AURANG ZEB', '33498119', NULL, 'REF-5308', '2024-09-01', '2026-08-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9240, 9119, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'ISLAM ABDELHAMID MOHAMMD ABDELSAL', '31112594', NULL, 'REF-5309', '2024-03-01', '2027-03-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9241, 9119, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'FAIZ YOUSIF MOHAMED YOUSIF', '66268737', NULL, 'REF-5310', '2024-01-10', '2026-12-31', 4800.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9242, 9119, '11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REF-5311', NULL, NULL, NULL, NULL, 'vacant', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9243, 9120, 'No.10', NULL, NULL, NULL, NULL, NULL, NULL, 'Abdul Latheef', '55096552', NULL, 'REF-5401', '2025-01-01', '2028-02-29', 12250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9244, 9121, 'VILLA NO 14', NULL, NULL, NULL, NULL, NULL, NULL, 'VENISE TANIOS NASSAR', '66037143', NULL, 'REF-6101', '2024-03-01', '2027-11-30', 12500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9245, 9121, 'VILLA NO 12', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHD FAKHRURRAZI', '33027514', NULL, 'REF-6102', '2025-01-03', '2027-02-28', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9246, 9122, 'No.12', NULL, NULL, NULL, NULL, NULL, NULL, 'CITY STAR FOR REAL ESTATE', '55817018', NULL, 'REF-6103', '2024-01-20', '2027-01-19', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9247, 9122, 'NO.14', NULL, NULL, NULL, NULL, NULL, NULL, 'M.G.Group (Kunhali Kutty)', '30304485', NULL, 'REF-6104', '2024-03-01', '2027-04-30', 13000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9248, 9123, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'COOL & REST', NULL, NULL, 'REF-6201', '2025-09-06', '2027-09-05', 12500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9249, 9123, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'DRAVA GENTS SALOON', NULL, NULL, 'REF-6202', '2026-07-01', NULL, 8500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9250, 9123, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'LOVERA FOR SWEETS AND FLOWERS', '33153780', NULL, 'REF-6203', '2025-12-01', '2027-11-30', 10000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9251, 9123, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'FIT FUEL TRADING', '30087008', NULL, 'REF-6204', '2025-12-01', '2027-11-30', 9500.00, NULL, 'occupied', 'Grace of first and last month', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9252, 9123, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'Hassan Yaquobi(BAKER)', NULL, NULL, 'REF-6205', NULL, NULL, 9500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9253, 9123, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'Hassan Yaqoubi(RESTRAUNT)', NULL, NULL, 'REF-6206', NULL, NULL, 9500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9254, 9123, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'I CLEAN LAUNDRY', '55784666', NULL, 'REF-6207', '2025-09-06', '2027-09-05', 11500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9255, 9123, '1', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDURRASHEED ABDUL HAKKEEM', NULL, NULL, 'REF-6208', NULL, NULL, 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9256, 9123, '2', NULL, NULL, NULL, NULL, NULL, NULL, 'FADIL AMEER', '50013008', NULL, 'REF-6209', '2025-10-15', '2027-10-14', 4200.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9257, 9123, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'MUHAMMED HASHIR PATINCHARE PUNATHIL', '50791578', NULL, 'REF-6210', '2025-10-01', '2027-09-30', 4250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9258, 9123, '4', NULL, NULL, NULL, NULL, NULL, NULL, 'RASHID THALAYILLATHU', '33578844', NULL, 'REF-6211', '2025-09-15', '2027-09-14', 4250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9259, 9123, '5', NULL, NULL, NULL, NULL, NULL, NULL, 'ANSAR AHAMMAD', '55460535', NULL, 'REF-6212', '2025-11-01', '2027-10-31', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9260, 9123, '6', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDULHAGH ABDOLRAHIM HANIFI', '30088785', NULL, 'REF-6213', '2025-09-15', '2027-09-14', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9261, 9123, '7', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMED SHIYAM SAMHOON', '50773630', NULL, 'REF-6214', '2025-11-01', '2027-10-31', 4250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9262, 9123, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'PRADEEP AZHEEL VEETTIL', '66519406', NULL, 'REF-6215', '2026-06-01', '2028-05-31', 4200.00, NULL, 'occupied', 'First 15 Days Free Grace', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9263, 9123, '9', NULL, NULL, NULL, NULL, NULL, NULL, 'ANMARY JOY', '70405375', NULL, 'REF-6216', '2026-06-01', '2028-05-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9264, 9123, '10', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMMED ABDUL RAUF', '33855828', NULL, 'REF-6217', '2025-10-15', '2027-10-14', 4100.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9265, 9123, '11', NULL, NULL, NULL, NULL, NULL, NULL, 'HASSAN AHMAD HAMRA', '33746124', NULL, 'REF-6218', '2025-11-01', '2027-10-31', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9266, 9123, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'SHANID KANNANTHODI VAYOLIPARAMBATH', NULL, NULL, 'REF-6219', '2025-11-01', '2027-10-31', 4000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9267, 9124, '62', NULL, NULL, NULL, NULL, NULL, NULL, 'NOORULISLAM SAID WALI', '55991345', NULL, 'REF-7101', '2023-08-01', '2025-06-30', 10500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9268, 9124, '58', NULL, NULL, NULL, NULL, NULL, NULL, 'EDEX FREIGHYT SERVICES LLC', '55917216', NULL, 'REF-7102', '2025-02-01', '2027-01-31', 10000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9269, 9124, '54', NULL, NULL, NULL, NULL, NULL, NULL, 'SHAMMA NOSHIR ZAINABA', '70073786', NULL, 'REF-7104', '2024-05-01', '2025-04-30', 10500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9270, 9124, '56', NULL, NULL, NULL, NULL, NULL, NULL, 'FAROOK AHMED FAZEL AHMED', '55516862', NULL, 'REF-7103', '2024-03-01', '2026-02-28', 10250.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9271, 9125, '57', NULL, NULL, NULL, NULL, NULL, NULL, 'FATMA SALEM AL HAMAD', '55901666', NULL, 'REF-8101', '2024-09-01', '2026-08-31', 12000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9272, 9125, '59', NULL, NULL, NULL, NULL, NULL, NULL, 'KHALID SAEED', '55558950', NULL, 'REF-8103', '2017-05-15', '2022-05-14', 15000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9273, 9125, '61', NULL, NULL, NULL, NULL, NULL, NULL, 'HAMMAM AHMAD', '55577858', NULL, 'REF-8102', '2017-05-01', '2026-05-30', 15000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9274, 9126, '8101', NULL, NULL, NULL, NULL, NULL, NULL, 'KAMALUDHEEN P KHADAR', '66087646', NULL, 'REF-8101', '2023-07-01', '2027-01-31', 14000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9275, 9126, 'NO.16', NULL, NULL, NULL, NULL, NULL, NULL, 'JAFAR MATTUMMAL', '66967077', NULL, 'REF-8102', '2022-11-01', '2027-01-31', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9276, 9127, '12', NULL, NULL, NULL, NULL, NULL, NULL, 'SULTAN IBRAHIM SL AL HASHMI', '55515584', NULL, 'REF-4001', '2023-03-15', '2026-03-14', 19000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9277, 9128, '8', NULL, NULL, NULL, NULL, NULL, NULL, 'VALIYAKATH V T UMMER', '55454004', NULL, NULL, '2023-11-01', '2026-12-31', 9500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9278, 9129, '60', NULL, NULL, NULL, NULL, NULL, NULL, 'YOUSEF ALI M AL EMADI', '26163400427', NULL, 'REF-1223', '2023-04-01', '2027-03-31', 10000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9279, 9130, 'U178', NULL, NULL, NULL, NULL, NULL, NULL, 'ALI AHAMED A M AL SULAITI', '55566590', NULL, NULL, '2024-06-01', '2025-05-31', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9280, 9131, 'U179', NULL, NULL, NULL, NULL, NULL, NULL, 'MUHAMMED NOUSHAD ABOO KANDATHIL', '33188200', NULL, NULL, '2024-08-15', '2026-09-14', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9281, 9131, 'U180', NULL, NULL, NULL, NULL, NULL, NULL, 'HAMAD IBRAHIM H I AL BADR', '55669556', NULL, NULL, '2024-02-01', '2026-01-31', 11000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9282, 9132, 'ACDN. I', NULL, NULL, NULL, NULL, NULL, NULL, 'RAIHANA PALOT T ABOOBACKER', '31231696', NULL, 'REF-3120', '2024-10-20', '2026-11-30', 3300.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9283, 9132, 'ACDN.2', NULL, NULL, NULL, NULL, NULL, NULL, 'NAVAS VANNATHAN K', '66706786', NULL, 'REF-3121', '2024-10-20', '2026-10-19', 5000.00, NULL, 'occupied', '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9284, 9132, 'ACDN.3', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDALLA SALEM', '31444041', NULL, 'REF-3122', '2025-05-01', '2027-04-30', 4000.00, NULL, 'occupied', '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9285, 9132, 'ACDN.4', NULL, NULL, NULL, NULL, NULL, NULL, 'ABDALLA SALEM', '31444041', NULL, 'REF-3123', '2025-05-01', '2027-04-30', 3800.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9286, 9132, 'ACDN.5', NULL, NULL, NULL, NULL, NULL, NULL, 'MUHAMMED ALI KURDI', '50424259', NULL, 'REF-3124', '2025-05-01', '2027-04-30', 3000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9287, 9132, 'ACDN.6', NULL, NULL, NULL, NULL, NULL, NULL, 'MOHAMMED HUZAIFA', '77266440', NULL, 'REF-3125', '2025-06-15', '2027-06-14', 3400.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9288, 9132, 'ACDN.7', NULL, NULL, NULL, NULL, NULL, NULL, 'JASSIM DARWISH A A MASHHADI', '55199915', NULL, 'REF-3126', '2025-08-01', '2027-07-31', 3400.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `units` (`id`, `facility_id`, `unit_number`, `floor`, `unit_type`, `area_sqft`, `owner_name`, `owner_mobile`, `owner_email`, `tenant_name`, `tenant_mobile`, `tenant_email`, `contract_number`, `contract_start`, `contract_end`, `rent_amount`, `security_deposit`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `contract_attachment`, `plate_number`, `bedrooms`, `bathrooms`, `furnished`, `description`) VALUES
(9289, 9132, 'ACDN.8', NULL, NULL, NULL, NULL, NULL, NULL, 'SULAIMAN KHALID A ALAZWANI', '55889120', NULL, 'REF-3127', '2025-06-01', '2027-05-30', 3000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9290, 9132, 'ACDN.9', NULL, NULL, NULL, NULL, NULL, NULL, 'MURAD ALSOUFI', '30394477', NULL, 'REF-3128', '2025-06-01', '2027-05-31', 3850.00, NULL, 'occupied', '2', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9291, 9132, 'SHP.A1', NULL, NULL, NULL, NULL, NULL, NULL, 'HOT & COOL', '66083535', NULL, 'REF-3129', '2024-10-10', '2026-08-09', 8000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9292, 9132, 'SHP.A2', NULL, NULL, NULL, NULL, NULL, NULL, 'HOT & COOL', NULL, NULL, 'REF-3130', '2024-10-10', '2026-08-30', 8000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9293, 9132, 'SHP.A3', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP END AUTO DTS', '50290779', NULL, 'REF-3131', '2024-11-01', '2026-10-31', 9500.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9294, 9132, 'SHP.A4', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP END AUTO DTS', NULL, NULL, 'REF-3132', '2024-11-01', '2026-10-31', 9500.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9295, 9132, 'SHP.A5', NULL, NULL, NULL, NULL, NULL, NULL, 'AL FAJER SALOON', '55966888', NULL, 'REF-3133', '2024-02-01', '2026-10-31', 8500.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9296, 9132, 'SHP.6 A', NULL, NULL, NULL, NULL, NULL, NULL, 'SELVADO ABHAYA', '66880866', NULL, 'REF-3134', '2024-12-01', '2026-11-30', 8500.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9297, 9132, 'SHP.7 A', NULL, NULL, NULL, NULL, NULL, NULL, 'PICK FRESH', '66706786', NULL, 'REF-3135', '2024-09-25', '2026-08-30', 8500.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9298, 9132, 'SHP.8 A', NULL, NULL, NULL, NULL, NULL, NULL, 'JAMEEL FOOD STUFF', NULL, NULL, 'REF-3136', '2025-03-01', '2027-02-28', 8000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9299, 9132, 'SHP.9 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', '33533250', NULL, 'REF-3137', '2025-01-01', '2030-12-31', 75000.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9300, 9132, 'SHP.10 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3138', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9301, 9132, 'SHP.11A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3139', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9302, 9132, 'SHP.12 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3140', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9303, 9132, 'SHP.13 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3141', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9304, 9132, 'SHP.14 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3142', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9305, 9132, 'SHP.15 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3143', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9306, 9132, 'SHP.16 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3144', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9307, 9132, 'SHP.17 A', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE', NULL, NULL, 'REF-3145', NULL, NULL, 0.00, NULL, 'occupied', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9308, 9132, 'SHP.18 A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'REF-3146', NULL, NULL, NULL, NULL, 'vacant', '1', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9309, 9132, '3147', NULL, NULL, NULL, NULL, NULL, NULL, 'Jameel Food(Mezanine Store)', NULL, NULL, 'REF-3147', '2025-01-01', '2030-12-31', 1000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9310, 9133, 'FLAT NO.1', NULL, NULL, NULL, NULL, NULL, NULL, 'CHILLYS HOT & COOL', '66083535', NULL, 'REF-3150', '2024-10-10', '2026-08-09', 5000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9311, 9133, 'FLAT NO.2', NULL, NULL, NULL, NULL, NULL, NULL, 'SOUK AL JUMLA', NULL, NULL, 'REF-3151', '2024-10-10', '2026-08-09', 5000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9312, 9133, 'FLAT NO.3', NULL, NULL, NULL, NULL, NULL, NULL, 'CHILLYS HOT & COOL', NULL, NULL, 'REF-3152', '2024-10-10', '2026-08-09', 3000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9313, 9133, 'FLAT NO.4', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3153', '2025-01-01', '2030-12-31', 5000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9314, 9133, 'FLAT NO.5', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3154', '2025-01-01', '2030-12-31', 0.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9315, 9133, 'FLAT NO.6', NULL, NULL, NULL, NULL, NULL, NULL, 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, 'REF-3155', '2024-10-01', '2026-09-30', 4500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9316, 9133, 'FLAT NO.7', NULL, NULL, NULL, NULL, NULL, NULL, 'AL FAJER SALOON', '55966888', NULL, 'REF-3156', '2024-02-01', '2026-10-31', 2500.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9317, 9133, 'FLAT NO.8', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3157', '2025-01-01', '2030-12-31', 0.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9318, 9133, 'FLAT NO.9', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3158', '2025-01-01', '2030-12-31', 0.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9319, 9133, 'SHP.1 B', NULL, NULL, NULL, NULL, NULL, NULL, 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, 'REF-3159', '2024-10-01', '2026-09-30', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9320, 9133, 'SHP.2 B', NULL, NULL, NULL, NULL, NULL, NULL, 'ASAYL LLAWANY ALMNZL YHA', '55606141', NULL, 'REF-3160', '2024-10-01', '2026-09-30', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9321, 9133, 'SHP.3 B', NULL, NULL, NULL, NULL, NULL, NULL, 'ASAYL LLAWANY ALMNZL YHA', '66608926', NULL, 'REF-3161', '2024-10-01', '2026-09-30', 9000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9322, 9133, 'SHP.4 B', NULL, NULL, NULL, NULL, NULL, NULL, 'ASAYL LLAWANY ALMNZL YHA', NULL, NULL, 'REF-3162', '2024-10-01', '2026-09-30', 9000.00, NULL, 'occupied', '4', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9323, 9133, 'SHP.5 B', NULL, NULL, NULL, NULL, NULL, NULL, 'WHOLE SALE MARKET', '55274498', NULL, 'REF-3163', '2024-09-25', '2026-09-08', 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9324, 9133, 'SHP.6 B', NULL, NULL, NULL, NULL, NULL, NULL, 'WHOLE SALE MARKET', NULL, NULL, 'REF-3164', '2024-09-25', '2026-09-08', 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9325, 9133, 'SHP.7 B', NULL, NULL, NULL, NULL, NULL, NULL, 'WHOLE SALE MARKET', NULL, NULL, 'REF-3165', '2024-09-25', '2026-09-08', 8000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9326, 9133, 'SHP.8 B', NULL, NULL, NULL, NULL, NULL, NULL, 'WHOLE SALE MARKET', NULL, NULL, 'REF-3166', '2024-09-25', '2026-09-08', 8000.00, NULL, 'occupied', '5', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9327, 9133, 'SHP.9 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3167', '2025-01-01', '2030-12-31', 75000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9328, 9133, 'SHP.10 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3168', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9329, 9133, 'SHP.11B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3169', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9330, 9133, 'SHP.12 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3170', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9331, 9133, 'SHP.13 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3171', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9332, 9133, 'SHP.14 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3172', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9333, 9133, 'SHP.15 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3173', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9334, 9133, 'SHP.16 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3174', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9335, 9133, 'SHP.17 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3175', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9336, 9133, 'SHP.18 B', NULL, NULL, NULL, NULL, NULL, NULL, 'TOP LINE CARS', NULL, NULL, 'REF-3176', '2025-01-01', '2030-12-31', NULL, NULL, 'occupied', '10', 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9337, 9133, 'SHP.19 B', NULL, NULL, NULL, NULL, NULL, NULL, 'WHOLE SALE MARKET STORE', NULL, NULL, 'REF-3177', '2024-10-10', '2024-08-09', 5000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9338, 9133, '3178', NULL, NULL, NULL, NULL, NULL, NULL, 'Souq Al Jamla(Mezanine Store)', NULL, NULL, 'REF-3178', NULL, NULL, 1000.00, NULL, 'occupied', NULL, 1, '2026-08-17 22:08:45', '2026-08-17 22:08:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9500, 9134, '24', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Muqaddam Mohammed MM Al Boainain', '33293333', NULL, 'PKG-43-24', '2026-03-22', '2026-07-21', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '7467', NULL, NULL, NULL, NULL),
(9501, 9134, '23', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ahmad Mohammed M M Al Boainain', '50377222', NULL, 'PKG-43-23', '2026-03-22', '2026-07-21', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '44417', NULL, NULL, NULL, NULL),
(9502, 9134, '28', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Zaid S A al Kuwari', '50065066', NULL, 'PKG-43-28', '2026-03-25', '2026-07-24', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '24049', NULL, NULL, NULL, NULL),
(9503, 9134, '22', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khalid Hassen M M Al Ansari', '66668762', NULL, 'PKG-43-22', '2026-03-25', '2026-07-24', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '4140', NULL, NULL, NULL, NULL),
(9504, 9134, '39', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Farhood Abdulla R R Al Hajri', '77666702', NULL, 'PKG-43-39', '2026-03-28', '2026-07-27', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '11185', NULL, NULL, NULL, NULL),
(9505, 9134, '41', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohamed Abdull R R Al Hajri', '33988883', NULL, 'PKG-43-41', '2026-03-28', '2026-07-27', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '44612', NULL, NULL, NULL, NULL),
(9506, 9134, '19', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Abdulaziz Ali H M Al-Rashid', '55550598', NULL, 'PKG-43-19', '2026-03-28', '2026-07-28', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '49947', NULL, NULL, NULL, NULL),
(9507, 9134, '21', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ahmed Ali H M Al-Rashid', '55552334', NULL, 'PKG-43-21', '2026-03-29', '2026-07-28', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45418', NULL, NULL, NULL, NULL),
(9508, 9134, '20', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Fahad Ali HM Al Rashid', '55555378', NULL, 'PKG-43-20', '2026-03-29', '2026-07-28', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45416', NULL, NULL, NULL, NULL),
(9509, 9134, '18', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khalid Salem KH A AL-Hajri', '66668762', NULL, 'PKG-43-18', '2026-03-30', '2026-07-29', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45386', NULL, NULL, NULL, NULL),
(9510, 9134, '37', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Fahad Mohammed S B Al Mansoori', '50555116', NULL, 'PKG-43-37', '2026-03-31', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '7081', NULL, NULL, NULL, NULL),
(9511, 9134, '16', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Rashid Juma R M Al-m Muhannadi', '55324039', NULL, 'PKG-43-16', '2026-03-31', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '8207', NULL, NULL, NULL, NULL),
(9512, 9134, '40', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Rashid Abdulla R R Al Hajri', '70070130', NULL, 'PKG-43-40', '2026-03-31', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '48486', NULL, NULL, NULL, NULL),
(9513, 9134, '17', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Yousef J K Al Sulaiti', '66116633', NULL, 'PKG-43-17', '2026-03-31', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '4240', NULL, NULL, NULL, NULL),
(9514, 9134, '27', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Hamad Jassim A j AL Jalabi', '66990010', NULL, 'PKG-43-27', '2026-03-31', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '2520', NULL, NULL, NULL, NULL),
(9515, 9134, '38', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ali Hassen M A AL Hajri', '66664763', NULL, 'PKG-43-38', '2026-04-02', '2026-08-01', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '167', NULL, NULL, NULL, NULL),
(9516, 9134, '15', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Nasser Abdulla A A AL- Amer', '66633309', NULL, 'PKG-43-15', '2026-04-05', '2026-04-08', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '28266', NULL, NULL, NULL, NULL),
(9517, 9134, '36', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khaleefa Hussain A I AL Wali', '5090380', NULL, 'PKG-43-36', '2026-04-06', '2026-07-30', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45640', NULL, NULL, NULL, NULL),
(9518, 9134, '46', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Jassim Hussain M A Alyafei', '33346496', NULL, 'PKG-43-46', '2026-04-06', '2026-06-08', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '35556', NULL, NULL, NULL, NULL),
(9519, 9134, '42', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ali Mahmoud H A Makki', '55519985', NULL, 'PKG-43-42', '2026-04-06', '2026-05-08', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '1450', NULL, NULL, NULL, NULL),
(9520, 9134, '47', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khalid M Ameen M J Al-Shafai', '66555498', NULL, 'PKG-43-47', '2026-04-11', '2026-10-08', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '31424', NULL, NULL, NULL, NULL),
(9521, 9134, '25', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Yousef Mohd Mandani Al-Emadi', NULL, NULL, 'PKG-43-25', NULL, NULL, NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '12516', NULL, NULL, NULL, NULL),
(9522, 9134, '26', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Abdul Aziz Saad A A Al-Ali', '51119891', NULL, 'PKG-43-26', '2026-04-02', '2026-08-01', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '49952', NULL, NULL, NULL, NULL),
(9523, 9134, '34', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', '555177881', NULL, 'PKG-43-34', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '44394', NULL, NULL, NULL, NULL),
(9524, 9134, '35', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-35', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '42375', NULL, NULL, NULL, NULL),
(9525, 9134, '32', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-32', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '25444', NULL, NULL, NULL, NULL),
(9526, 9134, '31', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-31', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '20602', NULL, NULL, NULL, NULL),
(9527, 9134, '30', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-30', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '44395', NULL, NULL, NULL, NULL),
(9528, 9134, '33', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-33', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45140', NULL, NULL, NULL, NULL),
(9529, 9134, '49', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-49', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45138', NULL, NULL, NULL, NULL),
(9530, 9134, '50', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-50', '2026-04-04', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45053', NULL, NULL, NULL, NULL),
(9531, 9134, '45', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-45', '2026-04-07', '2026-12-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '45142', NULL, NULL, NULL, NULL),
(9532, 9134, '53', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-53', '2026-04-20', '2026-08-19', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '33317', NULL, NULL, NULL, NULL),
(9533, 9134, '52', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Mohammed Fahad Y M Al Mahamadi', NULL, NULL, 'PKG-43-52', '2026-04-20', '2026-08-19', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '33373', NULL, NULL, NULL, NULL),
(9534, 9134, '48', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ahmed Abdul Aziz A A Al Maliki', '50500519', NULL, 'PKG-43-48', '2026-04-15', '2026-08-14', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '48784', NULL, NULL, NULL, NULL),
(9535, 9134, '12', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ali Husain M A Shehabi', '30124444', NULL, 'PKG-43-12', '2026-04-19', '2026-08-18', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '50604', NULL, NULL, NULL, NULL),
(9536, 9134, '10', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ali Ahmed M H Al Sada', '55890909', NULL, 'PKG-43-10', '2026-05-03', '2026-09-03', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '6273', NULL, NULL, NULL, NULL),
(9537, 9134, '9', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Ali Husain M A Shehabi', '30124444', NULL, 'PKG-43-9', '2026-05-24', '2026-09-23', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '48777', NULL, NULL, NULL, NULL),
(9538, 9134, '6', NULL, 'Parking', NULL, NULL, NULL, NULL, 'MOHAMMED HASSAN M A AL MUFTAH', '77000727', NULL, 'PKG-43-6', '2026-07-05', '2026-11-04', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '4492', NULL, NULL, NULL, NULL),
(9539, 9134, '5', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khalid Hassen M M Al Ansari', '66668762', NULL, 'PKG-43-5', '2026-07-05', '2026-11-04', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '43462', NULL, NULL, NULL, NULL),
(9540, 9134, '4', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Khalifa Omar S O Al Hemaidi', '50304747', NULL, 'PKG-43-4', '2026-07-16', '2026-11-15', NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, '23525', NULL, NULL, NULL, NULL),
(9541, 9134, '7', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Fahad Mohammed S B Al Mansoori', NULL, NULL, 'PKG-43-7', NULL, NULL, NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9542, 9134, '3', NULL, 'Parking', NULL, NULL, NULL, NULL, 'Abdulla Ahmed M A Al-Buainain', NULL, NULL, 'PKG-43-3', NULL, NULL, NULL, NULL, 'occupied', NULL, 1, '2026-08-22 14:06:27', '2026-08-22 14:06:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unit_checklists`
--

CREATE TABLE `unit_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `type` enum('move_in','move_out','routine','handover','weekly','monthly') NOT NULL DEFAULT 'routine',
  `frequency` varchar(20) NOT NULL DEFAULT 'regular' COMMENT 'weekly|monthly|regular',
  `items_json` mediumtext DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','completed') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(120) DEFAULT NULL,
  `overall_condition` varchar(30) DEFAULT NULL,
  `link_to` varchar(50) DEFAULT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `areas_json` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_checklists`
--

INSERT INTO `unit_checklists` (`id`, `unit_id`, `type`, `frequency`, `items_json`, `notes`, `status`, `created_by`, `created_at`, `updated_at`, `inspection_date`, `inspector_name`, `overall_condition`, `link_to`, `ref_id`, `areas_json`) VALUES
(0, 0, 'routine', 'regular', '{\"items\":{\"item_0\":\"1\"},\"notes\":{\"item_0\":\"ihdasjd\",\"item_1\":\"\",\"item_2\":\"\",\"item_3\":\"\",\"item_4\":\"\",\"item_5\":\"\",\"item_6\":\"\",\"item_7\":\"\"},\"labels\":{\"item_0\":\"Cleanliness and housekeeping\",\"item_1\":\"Walls, floors, ceilings\",\"item_2\":\"Electrical fixtures\",\"item_3\":\"Plumbing and water\",\"item_4\":\"Air conditioning\",\"item_5\":\"Doors, windows, locks\",\"item_6\":\"Fire safety (extinguisher visible)\",\"item_7\":\"Common area access\"},\"photos\":[]}', '', 'completed', 1, '2026-08-08 17:50:15', '2026-08-08 17:50:15', '2026-08-08', 'Super Admin', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unit_costs`
--

CREATE TABLE `unit_costs` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `cost_type_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `frequency` varchar(30) NOT NULL DEFAULT 'one-off',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `finance_entry_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_occupants`
--

CREATE TABLE `unit_occupants` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `relation` varchar(80) DEFAULT NULL,
  `id_type` varchar(30) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `move_out_date` date DEFAULT NULL,
  `status` enum('active','checked_out') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `mfa_secret` varchar(64) DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `phone`, `password`, `status`, `last_login`, `created_at`, `updated_at`, `deleted_at`, `company_id`, `tenant_id`, `mfa_secret`, `mfa_enabled`, `password_changed_at`) VALUES
(1, 1, 'Super Admin', 'admin@alyazwa.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2026-08-26 15:52:51', '2026-07-31 13:03:37', '2026-08-26 18:52:51', NULL, 1, NULL, NULL, 0, '2026-08-03 12:22:22'),
(2, 12, 'Property Manager', 'pm@alyazwa.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2026-08-08 14:56:36', '2026-07-31 17:43:38', '2026-08-08 17:56:36', NULL, 1, NULL, NULL, 0, '2026-08-08 14:40:05'),
(3, 8, 'Supervisor', 'supervisor@fmerp.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NULL, '2026-07-31 17:56:40', '2026-07-31 17:56:40', NULL, 1, NULL, NULL, 0, NULL),
(4, 5, 'Finance Manager', 'finance@fmerp.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NULL, '2026-07-31 17:56:40', '2026-07-31 17:56:40', NULL, 1, NULL, NULL, 0, NULL),
(5, 6, 'Finance User', 'fuser@fmerp.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NULL, '2026-07-31 17:56:40', '2026-07-31 17:56:40', NULL, 1, NULL, NULL, 0, NULL),
(6, 10, 'Real Estate Manager', 'rem@fmerp.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NULL, '2026-07-31 17:56:40', '2026-07-31 17:56:40', NULL, 1, NULL, NULL, 0, NULL),
(7, 11, 'Salesman', 'sales@fmerp.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NULL, '2026-07-31 17:56:40', '2026-07-31 17:56:40', NULL, 1, NULL, NULL, 0, NULL),
(8, 12, 'Aziz', 'aziz@alyazwa.com', '66555953', '$2y$10$QVLSLMXmDfZ3vJrLbH29R.mSt6lfukI6kLMIkKylBi4k/avh2LtUe', 'active', '2026-08-24 07:53:48', '2026-08-17 22:37:37', '2026-08-24 10:53:48', NULL, 1, NULL, NULL, 0, '2026-08-17 19:37:52'),
(9002, 8, 'Demo Supervisor', 'supervisor@demo.local', '+97450009002', '$2b$10$RghykByPnBGGh/Qxis7QHehY2C7Pc6pZ4oon4t22rd7u85VAGM9W6', 'active', NULL, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 1, NULL, NULL, 0, NULL),
(9003, 3, 'Demo Technician', 'technician@demo.local', '+97450009003', '$2b$10$RghykByPnBGGh/Qxis7QHehY2C7Pc6pZ4oon4t22rd7u85VAGM9W6', 'active', NULL, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 1, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_facilities`
--

CREATE TABLE `user_facilities` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_facilities`
--

INSERT INTO `user_facilities` (`id`, `user_id`, `facility_id`, `created_at`) VALUES
(0, 9002, 9001, '2026-08-03 16:01:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_property_assignments`
--

CREATE TABLE `user_property_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `role_type` enum('manager','caretaker','other') NOT NULL DEFAULT 'manager',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_property_assignments`
--

INSERT INTO `user_property_assignments` (`id`, `user_id`, `facility_id`, `role_type`, `is_primary`, `assigned_by`, `assigned_at`) VALUES
(1, 9002, 9001, 'manager', 1, 1, '2026-08-03 16:01:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` varchar(128) DEFAULT NULL,
  `logged_out_at` datetime DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `logged_in_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utility_accounts`
--

CREATE TABLE `utility_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `utility_name` varchar(80) NOT NULL,
  `provider_name` varchar(120) DEFAULT NULL,
  `account_number` varchar(80) DEFAULT NULL,
  `meter_number` varchar(80) DEFAULT NULL,
  `managed_by` varchar(80) DEFAULT NULL,
  `billing_mode` enum('included','billed_separately','tenant_pays_direct','complimentary') NOT NULL DEFAULT 'included',
  `monthly_charge` decimal(14,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `paid_by` varchar(30) DEFAULT 'company',
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utility_bills`
--

CREATE TABLE `utility_bills` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `bill_no` varchar(50) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `reading_prev` decimal(10,3) DEFAULT NULL,
  `reading_curr` decimal(10,3) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `charge_to_tenant` tinyint(1) NOT NULL DEFAULT 0,
  `due_date` date DEFAULT NULL,
  `paid_by` varchar(50) DEFAULT NULL,
  `status` enum('pending','paid','transferred','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utility_readings`
--

CREATE TABLE `utility_readings` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `type` enum('electricity','water','gas','diesel','other') NOT NULL,
  `reading_date` date NOT NULL,
  `units` decimal(10,2) NOT NULL,
  `cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `meter_reading` decimal(12,2) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `contact` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive','blacklisted') NOT NULL DEFAULT 'active',
  `is_manpower_supplier` tinyint(1) NOT NULL DEFAULT 0,
  `supplier_classification` varchar(40) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `vat_number` varchar(50) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_orders`
--

CREATE TABLE `work_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `wo_number` varchar(30) NOT NULL,
  `facility_id` int(10) UNSIGNED DEFAULT NULL,
  `asset_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('corrective','preventive','predictive','breakdown','inspection','emergency','project') NOT NULL DEFAULT 'corrective',
  `category` enum('electrical','hvac','plumbing','cleaning','civil','it','fire_safety','security','other') DEFAULT NULL,
  `priority` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `status` enum('new','assigned','in_progress','on_hold','completed','closed','cancelled') NOT NULL DEFAULT 'new',
  `workflow_stage` enum('complaint_received','complaint_verification','approval_process','converted_to_wo','assigned_to_supervisor','job_card_created','technician_assigned','planning_scheduling','work_execution','inspection_qc','job_completed','wo_closed') NOT NULL DEFAULT 'complaint_received',
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `supervisor_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `sla_due` datetime DEFAULT NULL,
  `planned_start` datetime DEFAULT NULL,
  `planned_end` datetime DEFAULT NULL,
  `sla_breached` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `estimated_cost` decimal(12,2) DEFAULT NULL,
  `selling_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_cost` decimal(12,2) DEFAULT NULL,
  `execution_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `actual_labor_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_material_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_transport_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_equipment_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_misc_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `completion_notes` text DEFAULT NULL,
  `requester_name` varchar(150) DEFAULT NULL,
  `requester_phone` varchar(30) DEFAULT NULL,
  `requester_email` varchar(100) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `qa_status` enum('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  `qa_approved_by` int(10) UNSIGNED DEFAULT NULL,
  `qa_approved_at` datetime DEFAULT NULL,
  `client_approval_status` enum('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  `client_approved_by` int(10) UNSIGNED DEFAULT NULL,
  `client_approved_at` datetime DEFAULT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `estimation_id` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `maintenance_request_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_type` enum('facility','non_facility','walk_in','direct') NOT NULL DEFAULT 'facility',
  `service_customer_id` int(10) UNSIGNED DEFAULT NULL,
  `requester_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_orders`
--

INSERT INTO `work_orders` (`id`, `company_id`, `wo_number`, `facility_id`, `asset_id`, `title`, `description`, `type`, `category`, `priority`, `status`, `workflow_stage`, `verified_by`, `verified_at`, `assigned_to`, `supervisor_id`, `vendor_id`, `created_by`, `sla_due`, `planned_start`, `planned_end`, `sla_breached`, `started_at`, `completed_at`, `estimated_cost`, `selling_total`, `actual_cost`, `execution_percent`, `actual_labor_cost`, `actual_material_cost`, `actual_transport_cost`, `actual_equipment_cost`, `actual_misc_cost`, `actual_total_cost`, `completion_notes`, `requester_name`, `requester_phone`, `requester_email`, `approval_status`, `qa_status`, `qa_approved_by`, `qa_approved_at`, `client_approval_status`, `client_approved_by`, `client_approved_at`, `invoice_id`, `estimation_id`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `deleted_at`, `unit_id`, `contract_id`, `maintenance_request_id`, `customer_type`, `service_customer_id`, `requester_location`) VALUES
(9001, 1, 'WO-DEMO-9001', 9001, NULL, 'Fix kitchen sink leak', 'Investigate and repair leaking kitchen sink in unit 101.', 'corrective', 'plumbing', 'medium', 'assigned', 'job_card_created', NULL, NULL, NULL, 9002, NULL, 1, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'Demo Tenant', '+97450002001', 'tenant@demo.local', 'approved', 'none', NULL, NULL, 'none', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:57:39', '2026-08-03 15:57:39', NULL, 9001, 9001, 9001, 'facility', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wo_approvals`
--

CREATE TABLE `wo_approvals` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `approval_type` enum('supervisor','budget','completion','reopen') NOT NULL DEFAULT 'supervisor',
  `action` enum('approved','rejected') NOT NULL,
  `notes` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `actioned_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wo_attachments`
--

CREATE TABLE `wo_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wo_chat_messages`
--

CREATE TABLE `wo_chat_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wo_comments`
--

CREATE TABLE `wo_comments` (
  `id` int(11) NOT NULL,
  `wo_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wo_labor`
--

CREATE TABLE `wo_labor` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wo_materials`
--

CREATE TABLE `wo_materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `wo_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deducted_from_stock` tinyint(1) NOT NULL DEFAULT 0,
  `notes` varchar(500) DEFAULT NULL,
  `added_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_actlog_user` (`user_id`),
  ADD KEY `idx_actlog_module` (`module`),
  ADD KEY `idx_actlog_created` (`created_at`);

--
-- Indexes for table `ai_flags`
--
ALTER TABLE `ai_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_ref` (`module`,`ref_id`);

--
-- Indexes for table `ai_property_scores`
--
ALTER TABLE `ai_property_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `ai_tenant_scores`
--
ALTER TABLE `ai_tenant_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `app_mobile_logs`
--
ALTER TABLE `app_mobile_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appml_user` (`user_id`),
  ADD KEY `idx_appml_action` (`action`),
  ADD KEY `idx_appml_status` (`status`),
  ADD KEY `idx_appml_created` (`created_at`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assets_code` (`asset_code`),
  ADD KEY `fk_assets_facility` (`facility_id`),
  ADD KEY `idx_asset_facility` (`facility_id`),
  ADD KEY `idx_asset_status` (`status`);

--
-- Indexes for table `asset_documents`
--
ALTER TABLE `asset_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_docs_asset` (`asset_id`);

--
-- Indexes for table `asset_meter_readings`
--
ALTER TABLE `asset_meter_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_amr_asset` (`asset_id`),
  ADD KEY `fk_amr_wo` (`wo_id`);

--
-- Indexes for table `asset_scan_logs`
--
ALTER TABLE `asset_scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_scan_asset` (`asset_id`),
  ADD KEY `idx_asset_scan_created` (`created_at`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_emp_date` (`employee_id`,`date`),
  ADD KEY `idx_attendance_employee_date` (`employee_id`,`date`),
  ADD KEY `idx_attendance_facility_date` (`facility_id`,`date`);

--
-- Indexes for table `cheques`
--
ALTER TABLE `cheques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `collection_assignments`
--
ALTER TABLE `collection_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collector_id` (`collector_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `collector_handoffs`
--
ALTER TABLE `collector_handoffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collector_id` (`collector_id`);

--
-- Indexes for table `collector_sessions`
--
ALTER TABLE `collector_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collector_id` (`collector_id`),
  ADD KEY `session_code` (`session_code`);

--
-- Indexes for table `commission_records`
--
ALTER TABLE `commission_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_id` (`deal_id`);

--
-- Indexes for table `commission_rules`
--
ALTER TABLE `commission_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_company_status` (`status`);

--
-- Indexes for table `company_roles`
--
ALTER TABLE `company_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cr_company` (`company_id`);

--
-- Indexes for table `company_users`
--
ALTER TABLE `company_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_user` (`company_id`,`user_id`);

--
-- Indexes for table `compliance_audits`
--
ALTER TABLE `compliance_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_facility` (`facility_id`),
  ADD KEY `fk_audit_created` (`created_by`);

--
-- Indexes for table `compliance_documents`
--
ALTER TABLE `compliance_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc_expiry` (`expiry_date`),
  ADD KEY `fk_doc_facility` (`facility_id`),
  ADD KEY `fk_doc_created` (`created_by`);

--
-- Indexes for table `complimentary_offers`
--
ALTER TABLE `complimentary_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contracts_number` (`contract_number`),
  ADD KEY `fk_contracts_facility` (`facility_id`),
  ADD KEY `fk_contracts_created_by` (`created_by`),
  ADD KEY `idx_con_status` (`status`),
  ADD KEY `idx_con_end_date` (`end_date`),
  ADD KEY `idx_con_unit` (`unit_id`);

--
-- Indexes for table `contract_rent_schedule`
--
ALTER TABLE `contract_rent_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_year` (`contract_id`,`year_number`);

--
-- Indexes for table `contract_templates`
--
ALTER TABLE `contract_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cost_reminders`
--
ALTER TABLE `cost_reminders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crm_activities`
--
ALTER TABLE `crm_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `crm_leads`
--
ALTER TABLE `crm_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_number` (`lead_number`);

--
-- Indexes for table `crm_visits`
--
ALTER TABLE `crm_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customers_mobile` (`mobile`),
  ADD KEY `idx_customers_company` (`company_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documents_facility` (`facility_id`),
  ADD KEY `idx_documents_status` (`status`),
  ADD KEY `idx_documents_expiry` (`expiry_date`),
  ADD KEY `idx_documents_type` (`doc_type`),
  ADD KEY `fk_documents_user` (`uploaded_by`),
  ADD KEY `idx_documents_module_expiry` (`module`,`expiry_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_emp_code` (`emp_code`),
  ADD KEY `fk_employees_user` (`user_id`),
  ADD KEY `fk_employees_facility` (`facility_id`),
  ADD KEY `idx_employees_company` (`company_id`),
  ADD KEY `idx_employees_status_id` (`status_id`),
  ADD KEY `idx_employees_dept` (`department_id`),
  ADD KEY `idx_employees_type` (`employee_type_id`),
  ADD KEY `idx_employees_source` (`employment_source_id`),
  ADD KEY `idx_employees_qid` (`qid_number`);

--
-- Indexes for table `employee_breaks`
--
ALTER TABLE `employee_breaks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_breaks_employee` (`employee_id`);

--
-- Indexes for table `employee_profiles`
--
ALTER TABLE `employee_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_employee_user` (`user_id`),
  ADD UNIQUE KEY `uk_employee_code` (`employee_code`),
  ADD KEY `idx_employee_status` (`status`),
  ADD KEY `idx_employee_dept` (`department_id`);

--
-- Indexes for table `estimations`
--
ALTER TABLE `estimations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `est_number` (`est_number`),
  ADD KEY `idx_est_facility` (`facility_id`),
  ADD KEY `idx_est_status` (`status`);

--
-- Indexes for table `estimation_items`
--
ALTER TABLE `estimation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estitem_est` (`est_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expenses_date` (`expense_date`),
  ADD KEY `fk_expenses_facility` (`facility_id`),
  ADD KEY `fk_expenses_work_order` (`work_order_id`),
  ADD KEY `fk_expenses_created_by` (`created_by`),
  ADD KEY `idx_exp_facility` (`facility_id`),
  ADD KEY `idx_exp_status` (`status`),
  ADD KEY `idx_exp_date` (`expense_date`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_facilities_code` (`code`),
  ADD KEY `fk_facilities_manager` (`manager_id`),
  ADD KEY `idx_fac_company` (`company_id`),
  ADD KEY `idx_fac_status` (`status`);

--
-- Indexes for table `finance_accounts`
--
ALTER TABLE `finance_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fa_code` (`code`),
  ADD KEY `idx_fa_group` (`group_id`);

--
-- Indexes for table `finance_account_groups`
--
ALTER TABLE `finance_account_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fag_code` (`code`);

--
-- Indexes for table `finance_amc_schedules`
--
ALTER TABLE `finance_amc_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fas_contract` (`contract_id`),
  ADD KEY `idx_fas_next` (`next_bill_date`);

--
-- Indexes for table `finance_audit_logs`
--
ALTER TABLE `finance_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fal_module` (`module`,`record_id`),
  ADD KEY `idx_fal_tx` (`transaction_id`),
  ADD KEY `idx_fal_created` (`created_at`);

--
-- Indexes for table `finance_bank_accounts`
--
ALTER TABLE `finance_bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fba_company` (`company_id`),
  ADD KEY `idx_fba_branch` (`branch_id`),
  ADD KEY `idx_fba_facility` (`facility_id`),
  ADD KEY `idx_fba_status` (`status`);

--
-- Indexes for table `finance_branches`
--
ALTER TABLE `finance_branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fin_branch_code` (`code`);

--
-- Indexes for table `finance_budgets`
--
ALTER TABLE `finance_budgets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finance_budget_lines`
--
ALTER TABLE `finance_budget_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fbl_budget` (`budget_id`);

--
-- Indexes for table `finance_cash_accounts`
--
ALTER TABLE `finance_cash_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fca_company` (`company_id`),
  ADD KEY `idx_fca_branch` (`branch_id`),
  ADD KEY `idx_fca_facility` (`facility_id`),
  ADD KEY `idx_fca_status` (`status`);

--
-- Indexes for table `finance_categories`
--
ALTER TABLE `finance_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fc_type_code` (`category_type`,`code`);

--
-- Indexes for table `finance_cost_centers`
--
ALTER TABLE `finance_cost_centers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fcc_branch` (`branch_id`);

--
-- Indexes for table `finance_deposits`
--
ALTER TABLE `finance_deposits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fd_number` (`deposit_number`),
  ADD KEY `idx_fd_account` (`bank_account_id`),
  ADD KEY `idx_fd_status` (`status`),
  ADD KEY `idx_fd_date` (`deposit_date`),
  ADD KEY `idx_fd_facility` (`facility_id`),
  ADD KEY `idx_fd_branch` (`branch_id`);

--
-- Indexes for table `finance_entries`
--
ALTER TABLE `finance_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `ref_module` (`ref_module`,`ref_id`),
  ADD KEY `entry_date` (`entry_date`);

--
-- Indexes for table `finance_expense_records`
--
ALTER TABLE `finance_expense_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fer_number` (`expense_number`),
  ADD KEY `idx_fer_status` (`status`),
  ADD KEY `idx_fer_date` (`expense_date`),
  ADD KEY `idx_fer_facility` (`facility_id`),
  ADD KEY `idx_fer_wo` (`work_order_id`);

--
-- Indexes for table `finance_income_records`
--
ALTER TABLE `finance_income_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fir_number` (`income_number`),
  ADD KEY `idx_fir_status` (`status`),
  ADD KEY `idx_fir_date` (`income_date`),
  ADD KEY `idx_fir_facility` (`facility_id`),
  ADD KEY `idx_fir_invoice` (`invoice_id`);

--
-- Indexes for table `finance_integration_log`
--
ALTER TABLE `finance_integration_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fil_module` (`module`,`created_at`);

--
-- Indexes for table `finance_journal_entries`
--
ALTER TABLE `finance_journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fje_number` (`entry_number`),
  ADD KEY `idx_fje_source` (`source_module`,`source_type`,`source_id`),
  ADD KEY `idx_fje_status` (`status`);

--
-- Indexes for table `finance_journal_lines`
--
ALTER TABLE `finance_journal_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fjl_journal` (`journal_id`),
  ADD KEY `idx_fjl_account` (`account_id`);

--
-- Indexes for table `finance_petty_advances`
--
ALTER TABLE `finance_petty_advances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpa_number` (`advance_number`),
  ADD KEY `idx_fpa_account` (`petty_account_id`),
  ADD KEY `idx_fpa_employee` (`employee_id`),
  ADD KEY `idx_fpa_status` (`status`);

--
-- Indexes for table `finance_petty_advance_settlements`
--
ALTER TABLE `finance_petty_advance_settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fpas_advance` (`advance_id`);

--
-- Indexes for table `finance_petty_audit_logs`
--
ALTER TABLE `finance_petty_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fpal_module` (`module`,`record_id`);

--
-- Indexes for table `finance_petty_cash_accounts`
--
ALTER TABLE `finance_petty_cash_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpca_code` (`account_code`),
  ADD KEY `idx_fpca_branch` (`branch_id`),
  ADD KEY `idx_fpca_facility` (`facility_id`),
  ADD KEY `idx_fpca_custodian` (`custodian_user_id`),
  ADD KEY `idx_fpca_status` (`status`);

--
-- Indexes for table `finance_petty_counts`
--
ALTER TABLE `finance_petty_counts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpc_number` (`count_number`),
  ADD KEY `idx_fpc_account` (`petty_account_id`);

--
-- Indexes for table `finance_petty_count_lines`
--
ALTER TABLE `finance_petty_count_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fpcl_count` (`count_id`);

--
-- Indexes for table `finance_petty_custodian_history`
--
ALTER TABLE `finance_petty_custodian_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fpch_account` (`petty_account_id`);

--
-- Indexes for table `finance_petty_expenses`
--
ALTER TABLE `finance_petty_expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpe_number` (`expense_number`),
  ADD KEY `idx_fpe_account` (`petty_account_id`),
  ADD KEY `idx_fpe_status` (`status`),
  ADD KEY `idx_fpe_wo` (`work_order_id`);

--
-- Indexes for table `finance_petty_reconciliations`
--
ALTER TABLE `finance_petty_reconciliations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fprec_account` (`petty_account_id`);

--
-- Indexes for table `finance_petty_replenishments`
--
ALTER TABLE `finance_petty_replenishments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpr_number` (`replenishment_number`),
  ADD KEY `idx_fpr_account` (`petty_account_id`);

--
-- Indexes for table `finance_petty_transfers`
--
ALTER TABLE `finance_petty_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fpt_number` (`transfer_number`);

--
-- Indexes for table `finance_reconciliations`
--
ALTER TABLE `finance_reconciliations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_frec_account` (`bank_account_id`),
  ADD KEY `idx_frec_status` (`status`);

--
-- Indexes for table `finance_reconciliation_items`
--
ALTER TABLE `finance_reconciliation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fri_rec` (`reconciliation_id`),
  ADD KEY `idx_fri_tx` (`transaction_id`);

--
-- Indexes for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ftx_number` (`transaction_number`),
  ADD KEY `idx_ftx_account` (`account_type`,`account_id`),
  ADD KEY `idx_ftx_date` (`transaction_date`),
  ADD KEY `idx_ftx_type` (`transaction_type`),
  ADD KEY `idx_ftx_status` (`status`),
  ADD KEY `idx_ftx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_ftx_facility` (`facility_id`),
  ADD KEY `idx_ftx_branch` (`branch_id`),
  ADD KEY `idx_ftx_reversal` (`reversal_of`);

--
-- Indexes for table `finance_transaction_approvals`
--
ALTER TABLE `finance_transaction_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fta_ref` (`transaction_ref_type`,`transaction_ref_id`),
  ADD KEY `idx_fta_status` (`status`);

--
-- Indexes for table `finance_transfers`
--
ALTER TABLE `finance_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ft_number` (`transfer_number`),
  ADD KEY `idx_ft_status` (`status`),
  ADD KEY `idx_ft_date` (`transfer_date`);

--
-- Indexes for table `finance_vendor_bills`
--
ALTER TABLE `finance_vendor_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fvb_number` (`bill_number`),
  ADD KEY `idx_fvb_po` (`purchase_order_id`),
  ADD KEY `idx_fvb_vendor` (`vendor_id`);

--
-- Indexes for table `finance_withdrawals`
--
ALTER TABLE `finance_withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fw_number` (`withdrawal_number`),
  ADD KEY `idx_fw_account` (`bank_account_id`),
  ADD KEY `idx_fw_cash` (`cash_account_id`),
  ADD KEY `idx_fw_status` (`status`),
  ADD KEY `idx_fw_date` (`withdrawal_date`),
  ADD KEY `idx_fw_facility` (`facility_id`);

--
-- Indexes for table `grn`
--
ALTER TABLE `grn`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grn_number` (`grn_number`),
  ADD KEY `idx_grn_po` (`po_id`);

--
-- Indexes for table `grn_items`
--
ALTER TABLE `grn_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_grnitem_grn` (`grn_id`);

--
-- Indexes for table `helpdesk_feedback`
--
ALTER TABLE `helpdesk_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_request` (`request_id`);

--
-- Indexes for table `hr_approval_actions`
--
ALTER TABLE `hr_approval_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_approval_action_req` (`approval_request_id`);

--
-- Indexes for table `hr_approval_requests`
--
ALTER TABLE `hr_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_approval_req_status` (`status`),
  ADD KEY `idx_hr_approval_req_module` (`module`);

--
-- Indexes for table `hr_approval_steps`
--
ALTER TABLE `hr_approval_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_approval_step` (`workflow_id`,`step_no`);

--
-- Indexes for table `hr_approval_workflows`
--
ALTER TABLE `hr_approval_workflows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_approval_wf_code` (`company_id`,`code`);

--
-- Indexes for table `hr_attendance_raw_logs`
--
ALTER TABLE `hr_attendance_raw_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_raw_log_employee` (`employee_id`,`logged_at`),
  ADD KEY `idx_hr_raw_log_type` (`log_type`);

--
-- Indexes for table `hr_attendance_regularizations`
--
ALTER TABLE `hr_attendance_regularizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_reg_employee` (`employee_id`),
  ADD KEY `idx_hr_reg_status` (`status`),
  ADD KEY `idx_hr_reg_date` (`attendance_date`);

--
-- Indexes for table `hr_audit_logs`
--
ALTER TABLE `hr_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_audit_employee` (`employee_id`,`created_at`),
  ADD KEY `idx_hr_audit_module` (`module`,`action`);

--
-- Indexes for table `hr_branches`
--
ALTER TABLE `hr_branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_branch_code` (`company_id`,`code`);

--
-- Indexes for table `hr_clearance_checklists`
--
ALTER TABLE `hr_clearance_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_clearance_checklist_code` (`company_id`,`code`);

--
-- Indexes for table `hr_clearance_instances`
--
ALTER TABLE `hr_clearance_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_clearance_inst_employee` (`employee_id`);

--
-- Indexes for table `hr_clearance_items`
--
ALTER TABLE `hr_clearance_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_clearance_item_checklist` (`checklist_id`);

--
-- Indexes for table `hr_clearance_item_status`
--
ALTER TABLE `hr_clearance_item_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_clearance_item_inst` (`instance_id`,`item_id`);

--
-- Indexes for table `hr_contract_expiry_alerts`
--
ALTER TABLE `hr_contract_expiry_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_contract_alert_contract` (`contract_id`),
  ADD KEY `idx_hr_contract_alert_date` (`alert_type`,`notified_at`);

--
-- Indexes for table `hr_cost_centers`
--
ALTER TABLE `hr_cost_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_departments`
--
ALTER TABLE `hr_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_designations`
--
ALTER TABLE `hr_designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_document_categories`
--
ALTER TABLE `hr_document_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_doc_cat_code` (`company_id`,`code`),
  ADD KEY `idx_hr_doc_cat_active` (`is_active`);

--
-- Indexes for table `hr_document_expiry_alerts`
--
ALTER TABLE `hr_document_expiry_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_doc_alert_doc` (`document_id`),
  ADD KEY `idx_hr_doc_alert_emp` (`employee_id`),
  ADD KEY `idx_hr_doc_alert_type_date` (`alert_type`,`notified_at`);

--
-- Indexes for table `hr_employee_assets`
--
ALTER TABLE `hr_employee_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_asset_user` (`user_id`);

--
-- Indexes for table `hr_employee_assignments`
--
ALTER TABLE `hr_employee_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_assign_employee` (`employee_id`),
  ADD KEY `idx_hr_assign_facility` (`facility_id`),
  ADD KEY `idx_hr_assign_unit` (`unit_id`),
  ADD KEY `idx_hr_assign_contract` (`contract_id`),
  ADD KEY `idx_hr_assign_status` (`assignment_status`),
  ADD KEY `idx_hr_assign_current` (`employee_id`,`is_current`),
  ADD KEY `idx_hr_assign_dates` (`start_date`,`end_date`);

--
-- Indexes for table `hr_employee_assignment_history`
--
ALTER TABLE `hr_employee_assignment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_assign_hist_assignment` (`assignment_id`),
  ADD KEY `idx_hr_assign_hist_employee` (`employee_id`);

--
-- Indexes for table `hr_employee_loans`
--
ALTER TABLE `hr_employee_loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_loan_employee` (`employee_id`),
  ADD KEY `idx_hr_loan_status` (`status`);

--
-- Indexes for table `hr_employee_requests`
--
ALTER TABLE `hr_employee_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_emp_req_employee` (`employee_id`),
  ADD KEY `idx_hr_emp_req_status` (`status`),
  ADD KEY `idx_hr_emp_req_module` (`module`);

--
-- Indexes for table `hr_employee_shifts`
--
ALTER TABLE `hr_employee_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emp_shift_user` (`user_id`);

--
-- Indexes for table `hr_employee_statuses`
--
ALTER TABLE `hr_employee_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_emp_status_code` (`company_id`,`code`),
  ADD KEY `idx_hr_emp_status_active` (`is_active`);

--
-- Indexes for table `hr_employee_timeline`
--
ALTER TABLE `hr_employee_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_timeline_employee` (`employee_id`,`event_at`);

--
-- Indexes for table `hr_employee_transfers`
--
ALTER TABLE `hr_employee_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_transfer_employee` (`employee_id`),
  ADD KEY `idx_hr_transfer_status` (`status`);

--
-- Indexes for table `hr_employee_types`
--
ALTER TABLE `hr_employee_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_emp_type_code` (`company_id`,`code`),
  ADD KEY `idx_hr_emp_type_active` (`is_active`);

--
-- Indexes for table `hr_employment_contracts`
--
ALTER TABLE `hr_employment_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_contract_employee` (`employee_id`),
  ADD KEY `idx_hr_contract_supplier` (`supplier_id`),
  ADD KEY `idx_hr_contract_status` (`contract_status`),
  ADD KEY `idx_hr_contract_end` (`contract_end_date`),
  ADD KEY `idx_hr_contract_current` (`employee_id`,`is_current`);

--
-- Indexes for table `hr_employment_contract_history`
--
ALTER TABLE `hr_employment_contract_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_contract_hist_contract` (`contract_id`),
  ADD KEY `idx_hr_contract_hist_employee` (`employee_id`);

--
-- Indexes for table `hr_employment_periods`
--
ALTER TABLE `hr_employment_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_emp_period_employee` (`employee_id`),
  ADD KEY `idx_hr_emp_period_current` (`employee_id`,`is_current`);

--
-- Indexes for table `hr_employment_sources`
--
ALTER TABLE `hr_employment_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_emp_source_code` (`company_id`,`code`),
  ADD KEY `idx_hr_emp_source_active` (`is_active`);

--
-- Indexes for table `hr_expense_claims`
--
ALTER TABLE `hr_expense_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_exp_user` (`user_id`),
  ADD KEY `idx_hr_exp_status` (`status`);

--
-- Indexes for table `hr_final_settlements`
--
ALTER TABLE `hr_final_settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_settlement_employee` (`employee_id`);

--
-- Indexes for table `hr_final_settlement_lines`
--
ALTER TABLE `hr_final_settlement_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_settlement_line_settlement` (`settlement_id`);

--
-- Indexes for table `hr_grades`
--
ALTER TABLE `hr_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_grade_code` (`company_id`,`code`),
  ADD KEY `idx_hr_grade_company` (`company_id`);

--
-- Indexes for table `hr_leave_balances`
--
ALTER TABLE `hr_leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_leave_balance` (`user_id`,`leave_type_id`,`year`);

--
-- Indexes for table `hr_leave_policies`
--
ALTER TABLE `hr_leave_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_leave_policy_type` (`leave_type_id`),
  ADD KEY `idx_hr_leave_policy_company` (`company_id`);

--
-- Indexes for table `hr_leave_requests`
--
ALTER TABLE `hr_leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_user` (`user_id`);

--
-- Indexes for table `hr_leave_request_history`
--
ALTER TABLE `hr_leave_request_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_leave_hist_request` (`request_id`);

--
-- Indexes for table `hr_leave_types`
--
ALTER TABLE `hr_leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_loan_installments`
--
ALTER TABLE `hr_loan_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_loan_inst_loan` (`loan_id`),
  ADD KEY `idx_hr_loan_inst_due` (`due_date`,`status`);

--
-- Indexes for table `hr_manpower_requirements`
--
ALTER TABLE `hr_manpower_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_mp_facility` (`facility_id`),
  ADD KEY `idx_hr_mp_status` (`status`),
  ADD KEY `idx_hr_mp_designation` (`designation_id`);

--
-- Indexes for table `hr_onboarding_checklists`
--
ALTER TABLE `hr_onboarding_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_onboard_checklist_code` (`company_id`,`code`);

--
-- Indexes for table `hr_onboarding_instances`
--
ALTER TABLE `hr_onboarding_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_onboard_inst_employee` (`employee_id`);

--
-- Indexes for table `hr_onboarding_tasks`
--
ALTER TABLE `hr_onboarding_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_onboard_task_checklist` (`checklist_id`);

--
-- Indexes for table `hr_onboarding_task_status`
--
ALTER TABLE `hr_onboarding_task_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_onboard_task_inst` (`instance_id`,`task_id`);

--
-- Indexes for table `hr_payroll_allocations`
--
ALTER TABLE `hr_payroll_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_payroll_alloc_line` (`payroll_line_id`);

--
-- Indexes for table `hr_payroll_groups`
--
ALTER TABLE `hr_payroll_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_payroll_group_code` (`company_id`,`code`),
  ADD KEY `idx_hr_payroll_group_branch` (`branch_id`);

--
-- Indexes for table `hr_payroll_lines`
--
ALTER TABLE `hr_payroll_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_payroll_line_run_emp` (`payroll_run_id`,`employee_id`),
  ADD KEY `idx_hr_payroll_line_employee` (`employee_id`);

--
-- Indexes for table `hr_payroll_line_components`
--
ALTER TABLE `hr_payroll_line_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_payroll_line_comp_line` (`payroll_line_id`);

--
-- Indexes for table `hr_payroll_locks`
--
ALTER TABLE `hr_payroll_locks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_payroll_lock_run` (`payroll_run_id`);

--
-- Indexes for table `hr_payroll_runs`
--
ALTER TABLE `hr_payroll_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_payroll_run_period` (`company_id`,`period_start`,`period_end`),
  ADD KEY `idx_hr_payroll_run_status` (`status`),
  ADD KEY `idx_hr_payroll_run_branch` (`branch_id`);

--
-- Indexes for table `hr_performance_goals`
--
ALTER TABLE `hr_performance_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_goal_user` (`user_id`);

--
-- Indexes for table `hr_performance_reviews`
--
ALTER TABLE `hr_performance_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_review_user` (`user_id`);

--
-- Indexes for table `hr_salary_advances`
--
ALTER TABLE `hr_salary_advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_advance_employee` (`employee_id`),
  ADD KEY `idx_hr_advance_status` (`status`);

--
-- Indexes for table `hr_salary_components`
--
ALTER TABLE `hr_salary_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_salary_comp_code` (`company_id`,`code`),
  ADD KEY `idx_hr_salary_comp_type` (`component_type`);

--
-- Indexes for table `hr_salary_revisions`
--
ALTER TABLE `hr_salary_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_salary_rev_employee` (`employee_id`);

--
-- Indexes for table `hr_salary_structures`
--
ALTER TABLE `hr_salary_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_salary_struct_employee` (`employee_id`),
  ADD KEY `idx_hr_salary_struct_current` (`employee_id`,`is_current`);

--
-- Indexes for table `hr_salary_structure_lines`
--
ALTER TABLE `hr_salary_structure_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_salary_line_struct` (`structure_id`),
  ADD KEY `idx_hr_salary_line_comp` (`component_id`);

--
-- Indexes for table `hr_settings`
--
ALTER TABLE `hr_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_settings_company_key` (`company_id`,`setting_key`),
  ADD KEY `idx_hr_settings_key` (`setting_key`);

--
-- Indexes for table `hr_shifts`
--
ALTER TABLE `hr_shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_shift_code` (`company_id`,`code`),
  ADD KEY `idx_hr_shift_active` (`is_active`);

--
-- Indexes for table `hr_shift_assignments`
--
ALTER TABLE `hr_shift_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_shift_assign_employee` (`employee_id`),
  ADD KEY `idx_hr_shift_assign_shift` (`shift_id`),
  ADD KEY `idx_hr_shift_assign_current` (`employee_id`,`is_current`);

--
-- Indexes for table `hr_shift_templates`
--
ALTER TABLE `hr_shift_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_teams`
--
ALTER TABLE `hr_teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_wps_batches`
--
ALTER TABLE `hr_wps_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_wps_batch_run` (`payroll_run_id`),
  ADD KEY `idx_hr_wps_batch_branch` (`branch_id`);

--
-- Indexes for table `hr_wps_records`
--
ALTER TABLE `hr_wps_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hr_wps_record_batch` (`batch_id`),
  ADD KEY `idx_hr_wps_record_employee` (`employee_id`);

--
-- Indexes for table `hr_wps_settings`
--
ALTER TABLE `hr_wps_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hr_wps_settings_branch` (`branch_id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_incident_facility` (`facility_id`),
  ADD KEY `fk_incident_reported` (`reported_by`);

--
-- Indexes for table `inspection_checklists`
--
ALTER TABLE `inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ic_facility` (`facility_id`),
  ADD KEY `idx_ic_status` (`status`),
  ADD KEY `idx_ic_date` (`inspection_date`);

--
-- Indexes for table `inspection_items`
--
ALTER TABLE `inspection_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ii_checklist` (`checklist_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_code` (`item_code`),
  ADD KEY `idx_inventory_low_stock` (`quantity`,`min_quantity`),
  ADD KEY `idx_inv_item_code` (`item_code`),
  ADD KEY `idx_inv_qty` (`quantity`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoices_number` (`invoice_number`),
  ADD KEY `idx_invoices_status` (`status`),
  ADD KEY `idx_invoices_issue_date` (`issue_date`),
  ADD KEY `fk_invoices_facility` (`facility_id`),
  ADD KEY `fk_invoices_contract` (`contract_id`),
  ADD KEY `fk_invoices_created_by` (`created_by`),
  ADD KEY `idx_inv_status` (`status`),
  ADD KEY `idx_inv_facility` (`facility_id`),
  ADD KEY `idx_inv_due_date` (`due_date`),
  ADD KEY `idx_inv_issue_date` (`issue_date`);

--
-- Indexes for table `invoice_edit_logs`
--
ALTER TABLE `invoice_edit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_edit_logs_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ii_invoice` (`invoice_id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inv_pay_invoice` (`invoice_id`);

--
-- Indexes for table `jc_attachments`
--
ALTER TABLE `jc_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jcatt_jc` (`jc_id`);

--
-- Indexes for table `jc_materials`
--
ALTER TABLE `jc_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jcmat_jc` (`jc_id`);

--
-- Indexes for table `job_cards`
--
ALTER TABLE `job_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jc_number` (`jc_number`),
  ADD KEY `idx_jc_wo` (`wo_id`),
  ADD KEY `idx_jc_assigned` (`assigned_to`),
  ADD KEY `idx_jc_status` (`status`),
  ADD KEY `idx_jc_supervisor` (`supervisor_id`);

--
-- Indexes for table `landlords`
--
ALTER TABLE `landlords`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landlord_payouts`
--
ALTER TABLE `landlord_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `landlord_id` (`landlord_id`);

--
-- Indexes for table `lease_amendments`
--
ALTER TABLE `lease_amendments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `lease_contracts`
--
ALTER TABLE `lease_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_number` (`contract_number`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `lease_payments`
--
ALTER TABLE `lease_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `collection_session_id` (`collection_session_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_email_time` (`email`,`created_at`);

--
-- Indexes for table `maintenance_costing`
--
ALTER TABLE `maintenance_costing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_costing_wo` (`wo_id`),
  ADD KEY `fk_costing_created_by` (`created_by`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mr_ticket` (`ticket_number`),
  ADD KEY `fk_mr_facility` (`facility_id`),
  ADD KEY `fk_mr_reviewed_by` (`reviewed_by`);

--
-- Indexes for table `media_albums`
--
ALTER TABLE `media_albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_ref` (`module`,`ref_id`);

--
-- Indexes for table `media_items`
--
ALTER TABLE `media_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notif_created` (`created_at`);

--
-- Indexes for table `outgoing_cheques`
--
ALTER TABLE `outgoing_cheques`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_partials`
--
ALTER TABLE `payment_partials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `petty_cash`
--
ALTER TABLE `petty_cash`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pc_number` (`pc_number`),
  ADD KEY `idx_pc_status` (`status`),
  ADD KEY `idx_pc_req_by` (`requested_by`);

--
-- Indexes for table `pm_cost_types`
--
ALTER TABLE `pm_cost_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `pm_salary_runs`
--
ALTER TABLE `pm_salary_runs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_month` (`employee_id`,`month`),
  ADD KEY `month` (`month`);

--
-- Indexes for table `po_payments`
--
ALTER TABLE `po_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_po_payments_po_id` (`po_id`);

--
-- Indexes for table `procurement_three_way_matches`
--
ALTER TABLE `procurement_three_way_matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_budgets`
--
ALTER TABLE `property_budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facility_year_month` (`facility_id`,`year`,`month`);

--
-- Indexes for table `property_costs`
--
ALTER TABLE `property_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_po_number` (`po_number`),
  ADD KEY `fk_po_vendor` (`vendor_id`),
  ADD KEY `fk_po_created_by` (`created_by`),
  ADD KEY `idx_po_status` (`status`),
  ADD KEY `idx_po_vendor` (`vendor_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pr_item` (`item_id`),
  ADD KEY `fk_pr_requested_by` (`requested_by`),
  ADD KEY `idx_pr_status` (`status`),
  ADD KEY `idx_pr_po_id` (`po_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reimbursements`
--
ALTER TABLE `reimbursements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rmb_number` (`rmb_number`),
  ADD KEY `idx_rmb_status` (`status`),
  ADD KEY `idx_rmb_req_by` (`requested_by`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_ref` (`module`,`ref_id`),
  ADD KEY `user_status` (`user_id`,`status`);

--
-- Indexes for table `report_saved_queries`
--
ALTER TABLE `report_saved_queries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rfq`
--
ALTER TABLE `rfq`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfq_number` (`rfq_number`),
  ADD KEY `idx_rfq_status` (`status`);

--
-- Indexes for table `rfq_quotations`
--
ALTER TABLE `rfq_quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rfqq_rfq` (`rfq_id`),
  ADD KEY `idx_rfqq_vendor` (`vendor_id`);

--
-- Indexes for table `rfq_vendors`
--
ALTER TABLE `rfq_vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_rfq_vendor` (`rfq_id`,`vendor_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_module` (`role_id`,`module`);

--
-- Indexes for table `sales_deals`
--
ALTER TABLE `sales_deals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deal_number` (`deal_number`);

--
-- Indexes for table `service_customers`
--
ALTER TABLE `service_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sc_name` (`name`),
  ADD KEY `idx_sc_phone` (`phone`);

--
-- Indexes for table `site_visits`
--
ALTER TABLE `site_visits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sla_rules`
--
ALTER TABLE `sla_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sla_priority` (`priority`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_item` (`item_id`),
  ADD KEY `fk_stock_created_by` (`created_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_facility` (`facility_id`),
  ADD KEY `idx_unit_status` (`status`);

--
-- Indexes for table `unit_checklists`
--
ALTER TABLE `unit_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `unit_costs`
--
ALTER TABLE `unit_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `unit_occupants`
--
ALTER TABLE `unit_occupants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `fk_users_role` (`role_id`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `user_facilities`
--
ALTER TABLE `user_facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_facility` (`user_id`,`facility_id`),
  ADD KEY `idx_user_facilities_facility` (`facility_id`);

--
-- Indexes for table `user_property_assignments`
--
ALTER TABLE `user_property_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_property_role` (`user_id`,`facility_id`,`role_type`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_sessions_token` (`token`),
  ADD KEY `fk_user_sessions_user` (`user_id`),
  ADD KEY `idx_sess_user` (`user_id`),
  ADD KEY `idx_sess_session` (`session_id`);

--
-- Indexes for table `utility_accounts`
--
ALTER TABLE `utility_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utility_bills`
--
ALTER TABLE `utility_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `utility_readings`
--
ALTER TABLE `utility_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utility_date` (`reading_date`),
  ADD KEY `fk_utility_facility` (`facility_id`),
  ADD KEY `fk_utility_created` (`created_by`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vendor_created` (`created_by`);

--
-- Indexes for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wo_number` (`wo_number`),
  ADD KEY `idx_wo_status` (`status`),
  ADD KEY `idx_wo_priority` (`priority`),
  ADD KEY `idx_wo_sla_due` (`sla_due`),
  ADD KEY `fk_wo_facility` (`facility_id`),
  ADD KEY `fk_wo_asset` (`asset_id`),
  ADD KEY `fk_wo_assigned` (`assigned_to`),
  ADD KEY `fk_wo_created` (`created_by`),
  ADD KEY `idx_wo_facility` (`facility_id`),
  ADD KEY `idx_wo_assigned` (`assigned_to`),
  ADD KEY `idx_wo_sla_breach` (`sla_breached`),
  ADD KEY `idx_wo_approval` (`approval_status`),
  ADD KEY `idx_wo_created` (`created_at`),
  ADD KEY `idx_wo_unit` (`unit_id`),
  ADD KEY `idx_wo_supervisor` (`supervisor_id`),
  ADD KEY `idx_wo_workflow_stage` (`workflow_stage`),
  ADD KEY `idx_wo_facility_status` (`facility_id`,`status`);

--
-- Indexes for table `wo_approvals`
--
ALTER TABLE `wo_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_woapproval_wo` (`wo_id`);

--
-- Indexes for table `wo_attachments`
--
ALTER TABLE `wo_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attach_wo` (`wo_id`),
  ADD KEY `fk_attach_user` (`uploaded_by`);

--
-- Indexes for table `wo_chat_messages`
--
ALTER TABLE `wo_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wochat_wo` (`wo_id`),
  ADD KEY `idx_wochat_user` (`user_id`);

--
-- Indexes for table `wo_comments`
--
ALTER TABLE `wo_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_woc_wo` (`wo_id`);

--
-- Indexes for table `wo_labor`
--
ALTER TABLE `wo_labor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wolabor_wo` (`wo_id`),
  ADD KEY `fk_wolabor_user` (`user_id`);

--
-- Indexes for table `wo_materials`
--
ALTER TABLE `wo_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_womat_wo` (`wo_id`),
  ADD KEY `fk_womat_item` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_flags`
--
ALTER TABLE `ai_flags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `ai_property_scores`
--
ALTER TABLE `ai_property_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `ai_tenant_scores`
--
ALTER TABLE `ai_tenant_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_mobile_logs`
--
ALTER TABLE `app_mobile_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cheques`
--
ALTER TABLE `cheques`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collection_assignments`
--
ALTER TABLE `collection_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collector_handoffs`
--
ALTER TABLE `collector_handoffs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collector_sessions`
--
ALTER TABLE `collector_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_records`
--
ALTER TABLE `commission_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_rules`
--
ALTER TABLE `commission_rules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complimentary_offers`
--
ALTER TABLE `complimentary_offers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_rent_schedule`
--
ALTER TABLE `contract_rent_schedule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contract_templates`
--
ALTER TABLE `contract_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cost_reminders`
--
ALTER TABLE `cost_reminders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_activities`
--
ALTER TABLE `crm_activities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_leads`
--
ALTER TABLE `crm_leads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_visits`
--
ALTER TABLE `crm_visits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_profiles`
--
ALTER TABLE `employee_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_audit_logs`
--
ALTER TABLE `finance_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_cash_accounts`
--
ALTER TABLE `finance_cash_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_categories`
--
ALTER TABLE `finance_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `finance_deposits`
--
ALTER TABLE `finance_deposits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_entries`
--
ALTER TABLE `finance_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_expense_records`
--
ALTER TABLE `finance_expense_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_income_records`
--
ALTER TABLE `finance_income_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_advances`
--
ALTER TABLE `finance_petty_advances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_advance_settlements`
--
ALTER TABLE `finance_petty_advance_settlements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_audit_logs`
--
ALTER TABLE `finance_petty_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_cash_accounts`
--
ALTER TABLE `finance_petty_cash_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_counts`
--
ALTER TABLE `finance_petty_counts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_count_lines`
--
ALTER TABLE `finance_petty_count_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_custodian_history`
--
ALTER TABLE `finance_petty_custodian_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_expenses`
--
ALTER TABLE `finance_petty_expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_reconciliations`
--
ALTER TABLE `finance_petty_reconciliations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_replenishments`
--
ALTER TABLE `finance_petty_replenishments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_petty_transfers`
--
ALTER TABLE `finance_petty_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_reconciliations`
--
ALTER TABLE `finance_reconciliations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_reconciliation_items`
--
ALTER TABLE `finance_reconciliation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transaction_approvals`
--
ALTER TABLE `finance_transaction_approvals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transfers`
--
ALTER TABLE `finance_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_withdrawals`
--
ALTER TABLE `finance_withdrawals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_approval_actions`
--
ALTER TABLE `hr_approval_actions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_approval_requests`
--
ALTER TABLE `hr_approval_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_approval_steps`
--
ALTER TABLE `hr_approval_steps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hr_approval_workflows`
--
ALTER TABLE `hr_approval_workflows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hr_attendance_raw_logs`
--
ALTER TABLE `hr_attendance_raw_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_attendance_regularizations`
--
ALTER TABLE `hr_attendance_regularizations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_audit_logs`
--
ALTER TABLE `hr_audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_branches`
--
ALTER TABLE `hr_branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_clearance_checklists`
--
ALTER TABLE `hr_clearance_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hr_clearance_instances`
--
ALTER TABLE `hr_clearance_instances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_clearance_items`
--
ALTER TABLE `hr_clearance_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `hr_clearance_item_status`
--
ALTER TABLE `hr_clearance_item_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_contract_expiry_alerts`
--
ALTER TABLE `hr_contract_expiry_alerts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_cost_centers`
--
ALTER TABLE `hr_cost_centers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_departments`
--
ALTER TABLE `hr_departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_designations`
--
ALTER TABLE `hr_designations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_document_categories`
--
ALTER TABLE `hr_document_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `hr_document_expiry_alerts`
--
ALTER TABLE `hr_document_expiry_alerts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_assets`
--
ALTER TABLE `hr_employee_assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_assignments`
--
ALTER TABLE `hr_employee_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_assignment_history`
--
ALTER TABLE `hr_employee_assignment_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_loans`
--
ALTER TABLE `hr_employee_loans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_requests`
--
ALTER TABLE `hr_employee_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_shifts`
--
ALTER TABLE `hr_employee_shifts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_statuses`
--
ALTER TABLE `hr_employee_statuses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `hr_employee_timeline`
--
ALTER TABLE `hr_employee_timeline`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_transfers`
--
ALTER TABLE `hr_employee_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employee_types`
--
ALTER TABLE `hr_employee_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `hr_employment_contracts`
--
ALTER TABLE `hr_employment_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employment_contract_history`
--
ALTER TABLE `hr_employment_contract_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employment_periods`
--
ALTER TABLE `hr_employment_periods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_employment_sources`
--
ALTER TABLE `hr_employment_sources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `hr_expense_claims`
--
ALTER TABLE `hr_expense_claims`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_final_settlements`
--
ALTER TABLE `hr_final_settlements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_final_settlement_lines`
--
ALTER TABLE `hr_final_settlement_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_grades`
--
ALTER TABLE `hr_grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_leave_balances`
--
ALTER TABLE `hr_leave_balances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_leave_policies`
--
ALTER TABLE `hr_leave_policies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hr_leave_requests`
--
ALTER TABLE `hr_leave_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_leave_request_history`
--
ALTER TABLE `hr_leave_request_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_leave_types`
--
ALTER TABLE `hr_leave_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `hr_loan_installments`
--
ALTER TABLE `hr_loan_installments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_manpower_requirements`
--
ALTER TABLE `hr_manpower_requirements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_onboarding_checklists`
--
ALTER TABLE `hr_onboarding_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hr_onboarding_instances`
--
ALTER TABLE `hr_onboarding_instances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_onboarding_tasks`
--
ALTER TABLE `hr_onboarding_tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `hr_onboarding_task_status`
--
ALTER TABLE `hr_onboarding_task_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_allocations`
--
ALTER TABLE `hr_payroll_allocations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_groups`
--
ALTER TABLE `hr_payroll_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_lines`
--
ALTER TABLE `hr_payroll_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_line_components`
--
ALTER TABLE `hr_payroll_line_components`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_locks`
--
ALTER TABLE `hr_payroll_locks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_payroll_runs`
--
ALTER TABLE `hr_payroll_runs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_performance_goals`
--
ALTER TABLE `hr_performance_goals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_performance_reviews`
--
ALTER TABLE `hr_performance_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salary_advances`
--
ALTER TABLE `hr_salary_advances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salary_components`
--
ALTER TABLE `hr_salary_components`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hr_salary_revisions`
--
ALTER TABLE `hr_salary_revisions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salary_structures`
--
ALTER TABLE `hr_salary_structures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salary_structure_lines`
--
ALTER TABLE `hr_salary_structure_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_settings`
--
ALTER TABLE `hr_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hr_shifts`
--
ALTER TABLE `hr_shifts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `hr_shift_assignments`
--
ALTER TABLE `hr_shift_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_shift_templates`
--
ALTER TABLE `hr_shift_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_teams`
--
ALTER TABLE `hr_teams`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_wps_batches`
--
ALTER TABLE `hr_wps_batches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_wps_records`
--
ALTER TABLE `hr_wps_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_wps_settings`
--
ALTER TABLE `hr_wps_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_checklists`
--
ALTER TABLE `inspection_checklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inspection_items`
--
ALTER TABLE `inspection_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `landlords`
--
ALTER TABLE `landlords`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9017;

--
-- AUTO_INCREMENT for table `landlord_payouts`
--
ALTER TABLE `landlord_payouts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lease_amendments`
--
ALTER TABLE `lease_amendments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lease_contracts`
--
ALTER TABLE `lease_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9333;

--
-- AUTO_INCREMENT for table `lease_payments`
--
ALTER TABLE `lease_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `media_albums`
--
ALTER TABLE `media_albums`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_items`
--
ALTER TABLE `media_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outgoing_cheques`
--
ALTER TABLE `outgoing_cheques`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_partials`
--
ALTER TABLE `payment_partials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pm_cost_types`
--
ALTER TABLE `pm_cost_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pm_salary_runs`
--
ALTER TABLE `pm_salary_runs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_budgets`
--
ALTER TABLE `property_budgets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_costs`
--
ALTER TABLE `property_costs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `sales_deals`
--
ALTER TABLE `sales_deals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9435;

--
-- AUTO_INCREMENT for table `unit_costs`
--
ALTER TABLE `unit_costs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_occupants`
--
ALTER TABLE `unit_occupants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9004;

--
-- AUTO_INCREMENT for table `user_property_assignments`
--
ALTER TABLE `user_property_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `utility_accounts`
--
ALTER TABLE `utility_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utility_bills`
--
ALTER TABLE `utility_bills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
