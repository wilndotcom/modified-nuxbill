<?php
/**
 * Create CPE Devices Table for Device Access Module
 */

require_once 'config.php';

try {
    $db = ORM::get_db();
    
    echo "<h2>🔧 Creating CPE Devices Table</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";
    
    // Check if table exists
    $check = $db->query("SHOW TABLES LIKE 'tbl_cpe_devices'")->fetch();
    
    if ($check === false) {
        // Create table
        $createTableSQL = "
            CREATE TABLE `tbl_cpe_devices` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `type` ENUM('PPPoE', 'Static') NOT NULL DEFAULT 'PPPoE',
                `device_type` ENUM('Tenda', 'Ubiquiti', 'Huawei', 'TP-Link', 'Other') NOT NULL DEFAULT 'Other',
                `ip_address` VARCHAR(45) NOT NULL,
                `pppoe_username` VARCHAR(255) DEFAULT NULL,
                `router_id` INT(11) UNSIGNED DEFAULT NULL,
                `port` INT(11) DEFAULT 80,
                `access_url` VARCHAR(500) DEFAULT NULL,
                `customer_id` INT(11) UNSIGNED DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                INDEX `idx_ip_address` (`ip_address`),
                INDEX `idx_router_id` (`router_id`),
                INDEX `idx_customer_id` (`customer_id`),
                INDEX `idx_device_type` (`device_type`),
                FOREIGN KEY (`router_id`) REFERENCES `tbl_routers`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $db->exec($createTableSQL);
        echo "✅ Created 'tbl_cpe_devices' table\n";
        
        // Insert sample data
        $sampleData = [
            ['CPE-001', 'PPPoE', 'Tenda', '192.168.1.100', 'pppoe_user1', 1, 80, 'http://192.168.1.100:80', null],
            ['CPE-002', 'Static', 'Ubiquiti', '192.168.1.101', null, 1, 80, 'http://192.168.1.101:80', null],
            ['CPE-003', 'PPPoE', 'Huawei', '192.168.1.102', 'pppoe_user2', 1, 80, 'http://192.168.1.102:80', null],
        ];
        
        $stmt = $db->prepare("INSERT INTO tbl_cpe_devices (name, type, device_type, ip_address, pppoe_username, router_id, port, access_url, customer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        echo "✅ Added 3 sample devices\n";
        
    } else {
        echo "⚠️ Table 'tbl_cpe_devices' already exists\n";
        
        // Check columns
        $columns = $db->query("SHOW COLUMNS FROM tbl_cpe_devices")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['id', 'name', 'type', 'device_type', 'ip_address', 'pppoe_username', 'router_id', 'port', 'access_url', 'customer_id', 'created_at', 'updated_at'];
        
        foreach ($required as $col) {
            if (!in_array($col, $columns)) {
                echo "  ⚠️ Missing column: $col\n";
            }
        }
    }
    
    // Show current count
    $count = $db->query("SELECT COUNT(*) FROM tbl_cpe_devices")->fetchColumn();
    echo "\n📊 Current device count: $count\n";
    
    echo "\n✅ Done!\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<pre style='background: #ffebee; padding: 15px;'>";
    echo "Database error: " . $e->getMessage() . "\n";
    echo "</pre>";
}
?>
