-- Debt Notification System - Database Tables
-- Run this SQL to create debt tracking tables

-- Table for tracking customer debt
CREATE TABLE IF NOT EXISTS `tbl_customer_debt` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `detected_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deadline_date` DATETIME NOT NULL,
    `status` ENUM('Active','Notified','Warning','Final','Disconnected','Settled') NOT NULL DEFAULT 'Active',
    `notification_count` INT NOT NULL DEFAULT 0,
    `last_notification_date` DATETIME NULL,
    `settled_date` DATETIME NULL,
    `disconnected_date` DATETIME NULL,
    `notes` TEXT NULL,
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `status` (`status`),
    KEY `deadline_date` (`deadline_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for debt notification history
CREATE TABLE IF NOT EXISTS `tbl_debt_notifications` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `debt_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `notification_type` ENUM('Initial','Warning','Final','Disconnection') NOT NULL,
    `channel` VARCHAR(50) NOT NULL COMMENT 'SMS,WhatsApp,Email,Inbox',
    `message_content` TEXT NULL,
    `sent_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Sent',
    PRIMARY KEY (`id`),
    KEY `debt_id` (`debt_id`),
    KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
