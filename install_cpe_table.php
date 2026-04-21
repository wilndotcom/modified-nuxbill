<?php
// Simple table installer
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phpnuxbill;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<h2>Installing CPE Devices Table</h2>';
    
    // Create table
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
    
    $pdo->exec($sql);
    echo '<p style="color: green;">✅ Table created successfully!</p>';
    
    // Insert sample data
    $stmt = $pdo->prepare("INSERT INTO tbl_cpe_devices (name, type, device_type, ip_address, pppoe_username, router_id, port, access_url, customer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $samples = [
        ['CPE-001', 'PPPoE', 'Tenda', '192.168.1.100', 'pppoe_user1', 1, 80, 'http://192.168.1.100:80', null],
        ['CPE-002', 'Static', 'Ubiquiti', '192.168.1.101', null, 1, 80, 'http://192.168.1.101:80', null],
        ['CPE-003', 'PPPoE', 'Huawei', '192.168.1.102', 'pppoe_user2', 1, 80, 'http://192.168.1.102:80', null],
    ];
    
    foreach ($samples as $data) {
        $stmt->execute($data);
    }
    echo '<p style="color: green;">✅ Sample data added (3 devices)</p>';
    
    $count = $pdo->query("SELECT COUNT(*) FROM tbl_cpe_devices")->fetchColumn();
    echo "<p>Total devices in table: <strong>$count</strong></p>";
    
    echo '<hr><a href="index.php?_route=device_access/dashboard" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Go to Device Access Dashboard</a>';
    
} catch (PDOException $e) {
    echo '<h2 style="color: red;">Error</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
}
?>
