-- ============================================================
-- Modified NuxBill - Complete Database Installation SQL
-- For Production Deployment
-- ============================================================

-- ============================================================
-- 1. CORE TABLES (Original PHPNuxBill)
-- ============================================================

-- Admins table (if not exists)
CREATE TABLE IF NOT EXISTS `tbl_admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `user_type` enum('SuperAdmin','Admin','Agent','Report') DEFAULT 'Admin',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE IF NOT EXISTS `tbl_customers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL,
  `address` text,
  `city` varchar(255) DEFAULT NULL,
  `service_type` varchar(50) DEFAULT 'Hotspot',
  `pppoe_username` varchar(255) DEFAULT NULL,
  `pppoe_password` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `router_id` int(11) unsigned DEFAULT NULL,
  `plan_id` int(11) unsigned DEFAULT NULL,
  `balance` decimal(20,2) DEFAULT '0.00',
  `service_expired` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `router_id` (`router_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Routers table
CREATE TABLE IF NOT EXISTS `tbl_routers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `description` text,
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plans table
CREATE TABLE IF NOT EXISTS `tbl_plans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('Hotspot','PPPOE','Traffic','Balance') DEFAULT 'Hotspot',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `bw_id` int(11) unsigned DEFAULT NULL,
  `shared_users` int(11) DEFAULT '1',
  `validity` int(11) DEFAULT '30',
  `validity_unit` enum('Mins','Hrs','Days','Months') DEFAULT 'Days',
  `data_limit` bigint(20) DEFAULT NULL,
  `data_unit` enum('MB','GB') DEFAULT 'MB',
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- App Config table
CREATE TABLE IF NOT EXISTS `tbl_appconfig` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. MODIFIED NUXBILL - CUSTOM TABLES
-- ============================================================

-- CPE Devices Table (Device Access Module)
CREATE TABLE IF NOT EXISTS `tbl_cpe_devices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('PPPoE','Static') NOT NULL DEFAULT 'PPPoE',
  `device_type` enum('Tenda','Ubiquiti','Huawei','TP-Link','Other') NOT NULL DEFAULT 'Other',
  `ip_address` varchar(45) NOT NULL,
  `pppoe_username` varchar(255) DEFAULT NULL,
  `router_id` int(11) unsigned DEFAULT NULL,
  `port` int(11) DEFAULT 80,
  `access_url` varchar(500) DEFAULT NULL,
  `customer_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_router_id` (`router_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OLT Devices Table (Fiber Management)
CREATE TABLE IF NOT EXISTS `tbl_olt_devices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `vendor` enum('ZTE','Huawei','FiberHome','Other') DEFAULT 'Other',
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `snmp_community` varchar(255) DEFAULT 'public',
  `description` text,
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ONU Devices Table (Fiber Management)
CREATE TABLE IF NOT EXISTS `tbl_onu_devices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `olt_id` int(11) unsigned DEFAULT NULL,
  `pon_port` int(11) DEFAULT NULL,
  `onu_id` int(11) DEFAULT NULL,
  `customer_id` int(11) unsigned DEFAULT NULL,
  `signal_strength` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive','Offline') DEFAULT 'Offline',
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `idx_olt_id` (`olt_id`),
  KEY `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OLT Profiles Table
CREATE TABLE IF NOT EXISTS `tbl_olt_profiles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `olt_id` int(11) unsigned DEFAULT NULL,
  `gemport` int(11) DEFAULT NULL,
  `vlan` int(11) DEFAULT NULL,
  `upload_speed` varchar(50) DEFAULT NULL,
  `download_speed` varchar(50) DEFAULT NULL,
  `tcont_type` varchar(50) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_olt_id` (`olt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tickets Table (Support System)
CREATE TABLE IF NOT EXISTS `tbl_tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text,
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `priority` enum('Low','Medium','High','Urgent') DEFAULT 'Low',
  `category` varchar(100) DEFAULT 'General',
  `admin_read_at` datetime DEFAULT NULL,
  `customer_read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ticket Replies Table
CREATE TABLE IF NOT EXISTS `tbl_ticket_replies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) unsigned NOT NULL,
  `sender` enum('customer','admin') NOT NULL,
  `admin_id` int(11) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `attachment` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Widgets Table (with position column)
CREATE TABLE IF NOT EXISTS `tbl_widgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `user_type` enum('admin','customer') DEFAULT 'admin',
  `enabled` tinyint(1) DEFAULT '1',
  `position` varchar(20) DEFAULT 'bottom',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payment Gateway Table
CREATE TABLE IF NOT EXISTS `tbl_payment_gateway` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `gateway` varchar(255) DEFAULT NULL,
  `gateway_trx_id` varchar(255) DEFAULT NULL,
  `plan_id` int(11) unsigned DEFAULT NULL,
  `plan_name` varchar(255) DEFAULT NULL,
  `routers_id` int(11) unsigned DEFAULT NULL,
  `routers_name` varchar(255) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT '0.00',
  `pg_url_payment` varchar(500) DEFAULT NULL,
  `pg_request` text,
  `pg_paid_response` text,
  `status` tinyint(4) DEFAULT '1',
  `expired_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `paid_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Banks/Paybills/Tills Table (M-Pesa)
CREATE TABLE IF NOT EXISTS `tbl_banks_paybills_tills` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `paybill` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `type` enum('Bank','Paybill','Till') DEFAULT 'Paybill',
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. INSERT DEFAULT DATA
-- ============================================================

-- Insert default admin (password: admin)
INSERT IGNORE INTO `tbl_admins` (`username`, `password`, `fullname`, `user_type`, `status`) VALUES
('admin', '$2y$10$yourhashhere', 'Administrator', 'SuperAdmin', 'Active');

-- Insert sample CPE devices
INSERT IGNORE INTO `tbl_cpe_devices` (`name`, `type`, `device_type`, `ip_address`, `pppoe_username`, `router_id`, `port`, `access_url`, `created_at`) VALUES
('CPE-001', 'PPPoE', 'Tenda', '192.168.1.100', 'pppoe_user1', 1, 80, 'http://192.168.1.100:80', NOW()),
('CPE-002', 'Static', 'Ubiquiti', '192.168.1.101', NULL, 1, 80, 'http://192.168.1.101:80', NOW()),
('CPE-003', 'PPPoE', 'Huawei', '192.168.1.102', 'pppoe_user2', 1, 80, 'http://192.168.1.102:80', NOW());

-- Insert default bank/paybill options for M-Pesa
INSERT IGNORE INTO `tbl_banks_paybills_tills` (`name`, `paybill`, `type`) VALUES
('Equity Bank', '247247', 'Bank'),
('KCB Bank', '522522', 'Bank'),
('Co-operative Bank', '400200', 'Bank'),
('M-Pesa Paybill', '174379', 'Paybill'),
('Buy Goods Till', '123456', 'Till');

-- Insert wallet widget
INSERT IGNORE INTO `tbl_widgets` (`widget`, `title`, `user_type`, `enabled`, `position`) VALUES
('customer/wallet', 'My Wallet', 'customer', 1, 'sidebar');

-- ============================================================
-- 4. ALTER EXISTING TABLES (if needed)
-- ============================================================

-- Add position column to widgets if not exists
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
  WHERE table_name = 'tbl_widgets' AND column_name = 'position' AND table_schema = DATABASE());
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE tbl_widgets ADD COLUMN position VARCHAR(20) DEFAULT "bottom" AFTER enabled', 'SELECT "Column already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. CREATE INDEXES FOR PERFORMANCE
-- ============================================================

CREATE INDEX IF NOT EXISTS `idx_cpe_created` ON `tbl_cpe_devices`(`created_at`);
CREATE INDEX IF NOT EXISTS `idx_tickets_created` ON `tbl_tickets`(`created_at`);
CREATE INDEX IF NOT EXISTS `idx_payment_gateway` ON `tbl_payment_gateway`(`gateway`);

-- ============================================================
-- DONE!
-- ============================================================
