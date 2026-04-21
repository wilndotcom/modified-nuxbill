<?php
require 'config.php';
$db = ORM::get_db();

echo "<h3>Creating tbl_cpe_devices table...</h3>";

$sql = "CREATE TABLE IF NOT EXISTS tbl_cpe_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('PPPoE', 'Static') NOT NULL DEFAULT 'PPPoE',
    device_type ENUM('Tenda', 'Ubiquiti', 'Huawei', 'TP-Link', 'Other') NOT NULL DEFAULT 'Other',
    ip_address VARCHAR(45) NOT NULL,
    pppoe_username VARCHAR(255) DEFAULT NULL,
    router_id INT(11) UNSIGNED DEFAULT NULL,
    port INT(11) DEFAULT 80,
    access_url VARCHAR(500) DEFAULT NULL,
    customer_id INT(11) UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    INDEX idx_ip_address (ip_address),
    INDEX idx_router_id (router_id),
    INDEX idx_customer_id (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$db->exec($sql);
echo "✅ Table created!<br>";

// Add sample data
$stmt = $db->prepare("INSERT INTO tbl_cpe_devices (name, type, device_type, ip_address, pppoe_username, router_id, port, access_url, customer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute(['CPE-001', 'PPPoE', 'Tenda', '192.168.1.100', 'pppoe_user1', 1, 80, 'http://192.168.1.100:80', null]);
$stmt->execute(['CPE-002', 'Static', 'Ubiquiti', '192.168.1.101', null, 1, 80, 'http://192.168.1.101:80', null]);
$stmt->execute(['CPE-003', 'PPPoE', 'Huawei', '192.168.1.102', 'pppoe_user2', 1, 80, 'http://192.168.1.102:80', null]);
echo "✅ Sample data added!<br>";
echo "<a href='index.php?_route=device_access/dashboard'>Go to Device Access</a>";
