<?php
/**
 * Rename ONU table to match controller
 */

require_once 'init.php';

echo "=== Renaming ONU table ===\n\n";

try {
    // Rename tbl_olt_onus to tbl_onus
    ORM::raw_execute("RENAME TABLE `tbl_olt_onus` TO `tbl_onus`");
    echo "✓ Table renamed from tbl_olt_onus to tbl_onus\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        // Table might already be renamed or doesn't exist, try creating it
        echo "⚠ Rename failed, attempting to create tbl_onus...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_onus` (
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
        
        ORM::raw_execute($sql);
        echo "✓ tbl_onus created\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Done! ===\n";
