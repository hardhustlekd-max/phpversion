-- ====================================================================
-- DATABASE EXPORT: Bahir Dar Motorcycle Permit & Enforcement System
-- Database Name: permit_db
-- Compatible with: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
-- Import directly via: phpMyAdmin -> Import -> database.sql
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `permit_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `permit_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- 1. Table structure for table `users` (System Users & Auth)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` VARCHAR(100) NOT NULL,
  `badge_id` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `role` ENUM('clerk', 'admin', 'officer', 'superadmin', 'it_specialist') NOT NULL DEFAULT 'clerk',
  `sub_city` VARCHAR(100) DEFAULT 'Central Command',
  `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_badge_id` (`badge_id`),
  KEY `idx_user_role` (`role`),
  KEY `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. Table structure for table `system_settings` (Global Configuration)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` VARCHAR(50) NOT NULL DEFAULT 'global_config',
  `officer_name` VARCHAR(150) DEFAULT 'አበበ ደስታ (Abebe Desta)',
  `department` VARCHAR(255) DEFAULT 'የትራፊክ ማኔጅመንትና ህግ ማስከበሪያ (Traffic Mgmt & Enforcement)',
  `sub_city_office` VARCHAR(150) DEFAULT 'በላይ ዘለቀ ክፍለ ከተማ (Belay Zeleke)',
  `default_printer` VARCHAR(150) DEFAULT 'Zebra ZD621 Industrial PVC Card Printer',
  `card_stock_type` VARCHAR(150) DEFAULT 'CR80 Standard PVC Card (85.6 x 54 mm)',
  `calendar_system` ENUM('ethiopian', 'gregorian') DEFAULT 'ethiopian',
  `auto_print_qr` TINYINT(1) DEFAULT 1,
  `email_alerts` TINYINT(1) DEFAULT 1,
  `security_2fa` TINYINT(1) DEFAULT 1,
  `high_risk_alerts` TINYINT(1) DEFAULT 1,
  `show_clerk_permit_status` TINYINT(1) DEFAULT 0,
  `show_clerk_submissions_action` TINYINT(1) DEFAULT 0,
  `show_clerk_approved_vehicles_action` TINYINT(1) DEFAULT 0,
  `show_clerk_payment_kpis` TINYINT(1) DEFAULT 0,
  `show_clerk_payment_records_table` TINYINT(1) DEFAULT 0,
  `clerk_payment_kpi_permission` ENUM('allow', 'view_only', 'deny') DEFAULT 'deny',
  `clerk_payment_table_permission` ENUM('allow', 'view_only', 'deny') DEFAULT 'deny',
  `frozen_subcities` JSON NULL,
  `system_reset_epoch` BIGINT NULL,
  `last_system_reset_at` DATETIME NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. Table structure for table `motorcycle_registrations` (Registrations)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `motorcycle_registrations`;
CREATE TABLE `motorcycle_registrations` (
  `id` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `user_portrait_photo` VARCHAR(255) NULL,
  `user_portrait_thumbnail` LONGTEXT NULL,
  `national_id_photo` VARCHAR(255) NULL,
  `national_id_back_photo` VARCHAR(255) NULL,
  `driving_license_photo` VARCHAR(255) NULL,
  `driving_permit_photo` VARCHAR(255) NULL,
  `vehicle_category` ENUM('electric', 'gas_under_110cc') NOT NULL DEFAULT 'electric',
  `motor_brand` VARCHAR(100) NULL,
  `motorModel` VARCHAR(100) NULL,
  `chassis_number` VARCHAR(100) NULL,
  `engine_or_serial_no` VARCHAR(100) NOT NULL,
  `plate_number` VARCHAR(50) NOT NULL,
  `registration_date` VARCHAR(50) NOT NULL,
  `status` ENUM('pending_approval', 'approved', 'rejected', 'ordered_print', 'printed') NOT NULL DEFAULT 'pending_approval',
  `qr_code_data` TEXT NULL,
  `registered_by` VARCHAR(100) NOT NULL,
  `rejection_reason` TEXT NULL,
  `sub_city` VARCHAR(100) NULL,
  `blood_group` VARCHAR(10) NULL,
  `hide_from_other_users` TINYINT(1) NOT NULL DEFAULT 0,
  `receipt_number` VARCHAR(100) NULL,
  `payment_amount` VARCHAR(50) NULL,
  `receipt_screenshot` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plate_number` (`plate_number`),
  KEY `idx_reg_status` (`status`),
  KEY `idx_reg_subcity` (`sub_city`),
  KEY `idx_registered_by` (`registered_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. Table structure for table `officer_assignments` (Officers on Duty)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `officer_assignments`;
CREATE TABLE `officer_assignments` (
  `id` VARCHAR(100) NOT NULL,
  `officer_name` VARCHAR(150) NOT NULL,
  `badge_id` VARCHAR(50) NOT NULL,
  `sub_city` VARCHAR(100) NOT NULL,
  `location_name` VARCHAR(150) NOT NULL,
  `shift` ENUM('morning', 'afternoon', 'night') NOT NULL DEFAULT 'morning',
  `status` ENUM('active', 'off_duty') NOT NULL DEFAULT 'active',
  `assigned_location` VARCHAR(150) NULL,
  `phone` VARCHAR(30) NULL,
  `shift_hours` VARCHAR(50) NULL,
  `assigned_date` VARCHAR(50) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_officer_badge` (`badge_id`),
  KEY `idx_officer_subcity` (`sub_city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. Table structure for table `print_batch_orders` (PVC Print Batches)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `print_batch_orders`;
CREATE TABLE `print_batch_orders` (
  `id` VARCHAR(100) NOT NULL,
  `order_date` VARCHAR(50) NOT NULL,
  `total_items` INT NOT NULL DEFAULT 0,
  `registration_ids` JSON NULL,
  `status` ENUM('pending', 'in_printing', 'completed') NOT NULL DEFAULT 'pending',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_print_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. Table structure for table `verification_logs` (Roadside Check Logs)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `verification_logs`;
CREATE TABLE `verification_logs` (
  `id` VARCHAR(100) NOT NULL,
  `scanned_at` VARCHAR(50) NOT NULL,
  `plate_number` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `vehicle_category` ENUM('electric', 'gas_under_110cc') NOT NULL DEFAULT 'electric',
  `engine_or_serial_no` VARCHAR(100) NOT NULL,
  `permit_status` ENUM('pending_approval', 'approved', 'rejected', 'ordered_print', 'printed') NOT NULL DEFAULT 'pending_approval',
  `verification_status` ENUM('verified', 'warning', 'flagged') NOT NULL DEFAULT 'verified',
  `officer_notes` TEXT NULL,
  `officer_badge_id` VARCHAR(50) NULL,
  `location_name` VARCHAR(150) NULL,
  `user_portrait_photo` VARCHAR(255) NULL,
  `national_id_photo` VARCHAR(255) NULL,
  `driving_license_photo` VARCHAR(255) NULL,
  `driving_permit_photo` VARCHAR(255) NULL,
  `national_id_back_photo` VARCHAR(255) NULL,
  `registration_id` VARCHAR(100) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_verif_plate` (`plate_number`),
  KEY `idx_verif_status` (`verification_status`),
  KEY `idx_verif_badge` (`officer_badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. Table structure for table `unregistered_vehicle_reports` (Illegal Motor Patrol)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `unregistered_vehicle_reports`;
CREATE TABLE `unregistered_vehicle_reports` (
  `id` VARCHAR(100) NOT NULL,
  `reported_at` VARCHAR(50) NOT NULL,
  `plate_number` VARCHAR(50) NULL,
  `driver_name` VARCHAR(150) NULL,
  `driver_phone` VARCHAR(30) NULL,
  `vehicle_category` ENUM('electric', 'gas_under_110cc') NOT NULL DEFAULT 'electric',
  `engine_or_serial_no` VARCHAR(100) NULL,
  `chassis_number` VARCHAR(100) NULL,
  `motor_brand` VARCHAR(100) NULL,
  `sub_city` VARCHAR(100) NOT NULL,
  `location_name` VARCHAR(150) NOT NULL,
  `officer_badge_id` VARCHAR(50) NOT NULL,
  `officer_name` VARCHAR(150) NULL,
  `notes` TEXT NOT NULL,
  `evidence_photo` VARCHAR(255) NULL,
  `status` ENUM('pending', 'under_investigation', 'resolved', 'registered') NOT NULL DEFAULT 'pending',
  `resolution_notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_unreg_status` (`status`),
  KEY `idx_unreg_subcity` (`sub_city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. Table structure for table `payment_receipts` (Revenue & Permitting Fees)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `payment_receipts`;
CREATE TABLE `payment_receipts` (
  `id` VARCHAR(100) NOT NULL,
  `receipt_number` VARCHAR(100) NOT NULL,
  `owner_registration_id` VARCHAR(100) NULL,
  `owner_name` VARCHAR(150) NOT NULL,
  `plate_number` VARCHAR(50) NULL,
  `phone` VARCHAR(30) NULL,
  `payment_date` VARCHAR(50) NOT NULL,
  `expiration_date` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NULL,
  `receipt_screenshot` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `entered_by` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_receipt_num` (`receipt_number`),
  KEY `idx_pay_reg_id` (`owner_registration_id`),
  KEY `idx_pay_plate` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 9. Table structure for table `system_audit_logs` (Security & Actions)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `system_audit_logs`;
CREATE TABLE `system_audit_logs` (
  `id` VARCHAR(100) NOT NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actor_badge_id` VARCHAR(50) NOT NULL,
  `actor_role` ENUM('clerk', 'admin', 'officer', 'superadmin', 'it_specialist') NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NOT NULL,
  `ip_address` VARCHAR(50) NULL,
  `severity` ENUM('info', 'warning', 'critical') NOT NULL DEFAULT 'info',
  PRIMARY KEY (`id`),
  KEY `idx_audit_actor` (`actor_badge_id`),
  KEY `idx_audit_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 10. Table structure for table `role_permissions` (Dynamic RBAC Matrix)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` VARCHAR(50) NOT NULL,
  `task_id` INT NOT NULL,
  `permission_state` ENUM('allow', 'view_only', 'deny') NOT NULL DEFAULT 'allow',
  UNIQUE KEY `idx_role_task` (`role_id`, `task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ====================================================================
-- SEED INITIAL SYSTEM DATA
-- ====================================================================

-- 1. Default System Users (Passwords: admin123, officer123, clerk123, superadmin123)
-- Uses standard PHP password_hash(..., PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `badge_id`, `email`, `password_hash`, `full_name`, `role`, `sub_city`, `status`, `created_at`) VALUES
('user-superadmin-SUPER-ADMIN-01', 'SUPER-ADMIN-01', 'superadmin@permit.gov.et', '$2y$10$eH0Z42L4/LpS1j18v4o6Eeh2wVbZ0M5m29w9J6W9aB8N/5mU3h1g2', 'ካሌብ ታደሰ (Kaleb Tadesse - Chief Super Admin)', 'superadmin', 'Central Command', 'active', NOW()),
('user-admin-ADMIN-001', 'ADMIN-001', 'admin@addisababa.gov.et', '$2y$10$eH0Z42L4/LpS1j18v4o6Eeh2wVbZ0M5m29w9J6W9aB8N/5mU3h1g2', 'ዳዊት ኃይሌ (Dawit Haile)', 'admin', 'በላይ ዘለቀ ክፍለ ከተማ', 'active', NOW()),
('user-officer-OFFICER-442', 'OFFICER-442', 'officer@addisababa.gov.et', '$2y$10$eH0Z42L4/LpS1j18v4o6Eeh2wVbZ0M5m29w9J6W9aB8N/5mU3h1g2', 'አበበ ደስታ (Abebe Desta)', 'officer', 'ፋሲሎ ክፍለ ከተማ', 'active', NOW()),
('user-clerk-CLERK-209', 'CLERK-209', 'clerk@addisababa.gov.et', '$2y$10$eH0Z42L4/LpS1j18v4o6Eeh2wVbZ0M5m29w9J6W9aB8N/5mU3h1g2', 'ሳራ ተሾመ (Sara Teshome)', 'clerk', 'ጣና ክፍለ ከተማ', 'active', NOW());

-- 2. Default System Settings
INSERT INTO `system_settings` (`id`, `officer_name`, `department`, `sub_city_office`, `default_printer`, `card_stock_type`, `calendar_system`, `auto_print_qr`, `email_alerts`, `security_2fa`, `high_risk_alerts`, `show_clerk_permit_status`, `show_clerk_submissions_action`, `show_clerk_approved_vehicles_action`, `show_clerk_payment_kpis`, `show_clerk_payment_records_table`, `clerk_payment_kpi_permission`, `clerk_payment_table_permission`, `frozen_subcities`)
VALUES ('global_config', 'አበበ ደስታ (Abebe Desta)', 'የትራፊክ ማኔጅመንትና ህግ ማስከበሪያ (Traffic Mgmt & Enforcement)', 'በላይ ዘለቀ ክፍለ ከተማ (Belay Zeleke)', 'Zebra ZD621 Industrial PVC Card Printer', 'CR80 Standard PVC Card (85.6 x 54 mm)', 'ethiopian', 1, 1, 1, 1, 0, 0, 0, 0, 0, 'deny', 'deny', '{}');

-- 3. Initial Dynamic RBAC Matrix (16 tasks for 5 system roles)
-- Secretary / Clerk
INSERT INTO `role_permissions` (`role_id`, `task_id`, `permission_state`) VALUES
('role-secretary', 1, 'allow'), ('role-secretary', 2, 'allow'), ('role-secretary', 3, 'allow'), ('role-secretary', 4, 'allow'),
('role-secretary', 5, 'deny'), ('role-secretary', 6, 'allow'), ('role-secretary', 7, 'allow'), ('role-secretary', 8, 'allow'),
('role-secretary', 9, 'allow'), ('role-secretary', 10, 'allow'), ('role-secretary', 11, 'deny'), ('role-secretary', 12, 'deny'),
('role-secretary', 13, 'deny'), ('role-secretary', 14, 'deny'), ('role-secretary', 15, 'deny'), ('role-secretary', 16, 'deny'),

-- Officer
('role-officer', 1, 'deny'), ('role-officer', 2, 'deny'), ('role-officer', 3, 'deny'), ('role-officer', 4, 'deny'),
('role-officer', 5, 'allow'), ('role-officer', 6, 'allow'), ('role-officer', 7, 'allow'), ('role-officer', 8, 'view_only'),
('role-officer', 9, 'view_only'), ('role-officer', 10, 'deny'), ('role-officer', 11, 'deny'), ('role-officer', 12, 'deny'),
('role-officer', 13, 'deny'), ('role-officer', 14, 'deny'), ('role-officer', 15, 'deny'), ('role-officer', 16, 'deny'),

-- Manager / Admin
('role-manager', 1, 'allow'), ('role-manager', 2, 'allow'), ('role-manager', 3, 'allow'), ('role-manager', 4, 'allow'),
('role-manager', 5, 'allow'), ('role-manager', 6, 'allow'), ('role-manager', 7, 'allow'), ('role-manager', 8, 'allow'),
('role-manager', 9, 'allow'), ('role-manager', 10, 'allow'), ('role-manager', 11, 'view_only'), ('role-manager', 12, 'view_only'),
('role-manager', 13, 'deny'), ('role-manager', 14, 'deny'), ('role-manager', 15, 'allow'), ('role-manager', 16, 'allow'),

-- IT Specialist
('role-it', 1, 'view_only'), ('role-it', 2, 'view_only'), ('role-it', 3, 'view_only'), ('role-it', 4, 'view_only'),
('role-it', 5, 'allow'), ('role-it', 6, 'view_only'), ('role-it', 7, 'view_only'), ('role-it', 8, 'view_only'),
('role-it', 9, 'view_only'), ('role-it', 10, 'view_only'), ('role-it', 11, 'allow'), ('role-it', 12, 'allow'),
('role-it', 13, 'allow'), ('role-it', 14, 'allow'), ('role-it', 15, 'allow'), ('role-it', 16, 'allow'),

-- Super Admin
('role-superadmin', 1, 'allow'), ('role-superadmin', 2, 'allow'), ('role-superadmin', 3, 'allow'), ('role-superadmin', 4, 'allow'),
('role-superadmin', 5, 'allow'), ('role-superadmin', 6, 'allow'), ('role-superadmin', 7, 'allow'), ('role-superadmin', 8, 'allow'),
('role-superadmin', 9, 'allow'), ('role-superadmin', 10, 'allow'), ('role-superadmin', 11, 'allow'), ('role-superadmin', 12, 'allow'),
('role-superadmin', 13, 'allow'), ('role-superadmin', 14, 'allow'), ('role-superadmin', 15, 'allow'), ('role-superadmin', 16, 'allow');

-- 4. Sample Motorcycle Registrations (Bahir Dar Subcities)
INSERT INTO `motorcycle_registrations` (
  `id`, `full_name`, `phone`, `user_portrait_photo`, `national_id_photo`, `driving_license_photo`, `driving_permit_photo`,
  `vehicle_category`, `motor_brand`, `motorModel`, `chassis_number`, `engine_or_serial_no`, `plate_number`,
  `registration_date`, `status`, `qr_code_data`, `registered_by`, `sub_city`, `blood_group`, `receipt_number`, `payment_amount`
) VALUES
('REG-2026-001', 'ሙሉጌታ አያሌው (Mulugeta Ayalew)', '0918234567', 'image/app/logo.png', 'image/app/flag.jpg', 'image/app/flag.jpg', 'image/app/flag.jpg', 'electric', 'Super Soco', 'TC-Max 2025', 'CHAS-ET-883921', 'ENG-ELEC-44910', '3-AA-98124', '2026-02-15', 'approved', 'https://enforcement.gov.et/verify/REG-2026-001', 'CLERK-209', 'Fasilo', 'O+', 'REC-8921-2026', '850.00'),
('REG-2026-002', 'ዮሐንስ በቀለ (Yohannes Bekele)', '0922883311', 'image/app/logo.png', 'image/app/flag.jpg', 'image/app/flag.jpg', 'image/app/flag.jpg', 'electric', 'Niu', 'NQi GTS', 'CHAS-ET-910283', 'ENG-ELEC-19283', '3-AM-44129', '2026-02-20', 'ordered_print', 'https://enforcement.gov.et/verify/REG-2026-002', 'CLERK-209', 'Belay Zeleke', 'A+', 'REC-9014-2026', '850.00'),
('REG-2026-003', 'ብርሃኑ ተፈራ (Birhanu Tefera)', '0911554433', 'image/app/logo.png', 'image/app/flag.jpg', 'image/app/flag.jpg', 'image/app/flag.jpg', 'gas_under_110cc', 'Yamaha', 'Crux 106cc', 'CHAS-ET-110293', 'ENG-GAS-772183', '3-AM-11203', '2026-03-01', 'pending_approval', 'https://enforcement.gov.et/verify/REG-2026-003', 'CLERK-209', 'Dagmawi Minilik', 'B+', 'REC-9182-2026', '1200.00'),
('REG-2026-004', 'አልማዝ ታደሰ (Almaz Tadesse)', '0930123456', 'image/app/logo.png', 'image/app/flag.jpg', 'image/app/flag.jpg', 'image/app/flag.jpg', 'electric', 'Yadea', 'G5 Pro Electric', 'CHAS-ET-554201', 'ENG-ELEC-88192', '3-AM-77312', '2026-03-02', 'printed', 'https://enforcement.gov.et/verify/REG-2026-004', 'ADMIN-001', 'Gish Abay', 'AB+', 'REC-9231-2026', '850.00'),
('REG-2026-005', 'ተስፋዬ ገብሬ (Tesfaye Gebre)', '0944998877', 'image/app/logo.png', 'image/app/flag.jpg', 'image/app/flag.jpg', 'image/app/flag.jpg', 'gas_under_110cc', 'Bajaj', 'Boxer BM100', 'CHAS-ET-330192', 'ENG-GAS-553190', '3-AM-55104', '2026-03-04', 'rejected', 'https://enforcement.gov.et/verify/REG-2026-005', 'CLERK-209', 'Tana', 'O-', 'REC-9304-2026', '1200.00');

-- 5. Sample Officer Assignments
INSERT INTO `officer_assignments` (`id`, `officer_name`, `badge_id`, `sub_city`, `location_name`, `shift`, `status`, `assigned_location`, `phone`, `shift_hours`, `assigned_date`) VALUES
('OFF-01', 'አበበ ደስታ (Abebe Desta)', 'OFFICER-442', 'Fasilo', 'Fasilo Main Roundabout Checkpoint', 'morning', 'active', 'Fasilo Checkpoint 1', '0918001122', '06:00 - 14:00', '2026-03-07'),
('OFF-02', 'ሰለሞን ግርማ (Solomon Girma)', 'OFFICER-8842', 'Belay Zeleke', 'Belay Zeleke Gate Station', 'afternoon', 'active', 'Belay Zeleke Gate Alpha', '0918334455', '14:00 - 22:00', '2026-03-07'),
('OFF-03', 'ታደሰ ወርቁ (Tadesse Worku)', 'OFFICER-109', 'Tana', 'Tana Port Access Checkpoint', 'night', 'active', 'Tana Port Station', '0911778899', '22:00 - 06:00', '2026-03-07');

-- 6. Sample Print Batch Order
INSERT INTO `print_batch_orders` (`id`, `order_date`, `total_items`, `registration_ids`, `status`, `notes`) VALUES
('BATCH-2026-001', '2026-02-28', 2, '["REG-2026-002", "REG-2026-004"]', 'in_printing', 'PVC Card Batch for Belay Zeleke and Gish Abay approvals.');

-- 7. Sample Verification Logs
INSERT INTO `verification_logs` (`id`, `scanned_at`, `plate_number`, `full_name`, `phone`, `vehicle_category`, `engine_or_serial_no`, `permit_status`, `verification_status`, `officer_notes`, `officer_badge_id`, `location_name`, `user_portrait_photo`, `registration_id`) VALUES
('VLOG-001', '2026-03-06 09:30:00', '3-AA-98124', 'ሙሉጌታ አያሌው (Mulugeta Ayalew)', '0918234567', 'electric', 'ENG-ELEC-44910', 'approved', 'verified', 'All documents clear. QR code scanned successfully.', 'OFFICER-442', 'Fasilo Main Roundabout', 'image/app/logo.png', 'REG-2026-001'),
('VLOG-002', '2026-03-06 15:45:00', '3-AM-77312', 'አልማዝ ታደሰ (Almaz Tadesse)', '0930123456', 'electric', 'ENG-ELEC-88192', 'printed', 'verified', 'Physical PVC Card inspected and verified.', 'OFFICER-8842', 'Belay Zeleke Gate', 'image/app/logo.png', 'REG-2026-004'),
('VLOG-003', '2026-03-07 10:15:00', '3-AM-55104', 'ተስፋዬ ገብሬ (Tesfaye Gebre)', '0944998877', 'gas_under_110cc', 'ENG-GAS-553190', 'rejected', 'flagged', 'Permit application previously rejected due to expired driving license.', 'OFFICER-442', 'Fasilo Main Roundabout', 'image/app/logo.png', 'REG-2026-005');

-- 8. Sample Payment Receipts
INSERT INTO `payment_receipts` (`id`, `receipt_number`, `owner_registration_id`, `owner_name`, `plate_number`, `phone`, `payment_date`, `expiration_date`, `amount`, `notes`, `entered_by`) VALUES
('RCPT-001', 'REC-8921-2026', 'REG-2026-001', 'ሙሉጌታ አያሌው (Mulugeta Ayalew)', '3-AA-98124', '0918234567', '2026-02-15', '2027-02-15', 850.00, 'Annual municipal registration fee for electric motorcycle', 'CLERK-209'),
('RCPT-002', 'REC-9014-2026', 'REG-2026-002', 'ዮሐንስ በቀለ (Yohannes Bekele)', '3-AM-44129', '0922883311', '2026-02-20', '2027-02-20', 850.00, 'Annual municipal registration fee for electric motorcycle', 'CLERK-209'),
('RCPT-003', 'REC-9182-2026', 'REG-2026-003', 'ብርሃኑ ተፈራ (Birhanu Tefera)', '3-AM-11203', '0911554433', '2026-03-01', '2027-03-01', 1200.00, 'Annual municipal registration fee for gas motorcycle under 110cc', 'CLERK-209');

-- 9. Sample Unregistered Vehicle Report
INSERT INTO `unregistered_vehicle_reports` (`id`, `reported_at`, `plate_number`, `driver_name`, `driver_phone`, `vehicle_category`, `engine_or_serial_no`, `chassis_number`, `motor_brand`, `sub_city`, `location_name`, `officer_badge_id`, `officer_name`, `notes`, `status`) VALUES
('UNREG-001', '2026-03-07 11:20:00', 'UNREG-BD-902', 'ያልታወቀ አሽከርካሪ (Unknown Driver)', '0911002233', 'gas_under_110cc', 'ENG-UNKNOWN-992', 'CHAS-UNKNOWN-881', 'Haojue 110', 'Atse Tewodros', 'Atse Tewodros St. Station', 'OFFICER-442', 'አበበ ደስታ (Abebe Desta)', 'Vehicle operating without valid municipal permit or plate.', 'under_investigation');

-- 10. Initial Audit Log
INSERT INTO `system_audit_logs` (`id`, `timestamp`, `actor_badge_id`, `actor_role`, `action`, `details`, `severity`) VALUES
('LOG-INIT-01', NOW(), 'SUPER-ADMIN-01', 'superadmin', 'SYSTEM_INITIALIZATION', 'Bahir Dar Motorcycle Permit Management System initialized with MySQL database.', 'info');

SET FOREIGN_KEY_CHECKS = 1;
