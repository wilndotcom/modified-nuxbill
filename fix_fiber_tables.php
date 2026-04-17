<?php
/**
 * Fix Fiber Tables - Direct SQL Migration
 * Run: php fix_fiber_tables.php
 */

require_once 'init.php';

echo "=== Creating Fiber Management Tables ===\n\n";

// tbl_olt_devices
$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_olt_devices` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `brand` VARCHAR(32) NOT NULL,
    `ip_address` VARCHAR(40) NOT NULL,
    `port` INT NOT NULL DEFAULT 22,
    `username` VARCHAR(64) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    ORM::raw_execute($sql1);
    echo "✓ tbl_olt_devices created\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⚠ tbl_olt_devices already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

// tbl_olt_profiles
$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_olt_profiles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `olt_id` INT NOT NULL,
    `download_speed` INT NOT NULL COMMENT 'Mbps',
    `upload_speed` INT NOT NULL COMMENT 'Mbps',
    `line_profile` VARCHAR(64) NULL,
    `service_profile` VARCHAR(64) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    ORM::raw_execute($sql2);
    echo "✓ tbl_olt_profiles created\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⚠ tbl_olt_profiles already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

// tbl_olt_onus
$sql3 = "CREATE TABLE IF NOT EXISTS `tbl_olt_onus` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `olt_id` INT NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `onu_id` VARCHAR(32) NOT NULL,
    `pon_port` VARCHAR(32) NULL,
    `customer_id` INT NULL,
    `profile_id` INT NULL,
    `status` ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Inactive',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    ORM::raw_execute($sql3);
    echo "✓ tbl_olt_onus created\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⚠ tbl_olt_onus already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

// tbl_cpe_routers
$sql4 = "CREATE TABLE IF NOT EXISTS `tbl_cpe_routers` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `onu_id` INT NULL,
    `mac_address` VARCHAR(20) NOT NULL,
    `ip_address` VARCHAR(40) NOT NULL,
    `brand` VARCHAR(32) NULL,
    `model` VARCHAR(64) NULL,
    `protocol` VARCHAR(10) NOT NULL DEFAULT 'HTTP',
    `username` VARCHAR(64) NULL,
    `password` VARCHAR(255) NULL,
    `status` ENUM('Active','Inactive','Offline') NOT NULL DEFAULT 'Active',
    `last_seen` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    ORM::raw_execute($sql4);
    echo "✓ tbl_cpe_routers created\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⚠ tbl_cpe_routers already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Done! ===\n";
echo "Access Fiber Management at: http://localhost/modified-nuxbill/fiber/olt-devices\n";
