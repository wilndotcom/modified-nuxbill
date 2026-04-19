<?php
/**
 * Create tbl_admins table
 */
include 'init.php';

echo "Creating tbl_admins table...<br>";

try {
    ORM::get_db()->exec("CREATE TABLE IF NOT EXISTS tbl_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        fullname VARCHAR(100),
        user_type ENUM('Admin', 'SuperAdmin', 'Support') DEFAULT 'Admin',
        email VARCHAR(100),
        phone VARCHAR(20),
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        last_login DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table created successfully!<br>";
    
    // Insert default admin
    $exists = ORM::for_table('tbl_admins')->where('username', 'admin')->find_one();
    if (!$exists) {
        ORM::for_table('tbl_admins')->create([
            'username' => 'admin',
            'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
            'fullname' => 'Administrator',
            'user_type' => 'SuperAdmin',
            'email' => 'admin@localhost',
            'status' => 'Active'
        ])->save();
        echo "Default admin created.<br>";
    } else {
        echo "Admin already exists.<br>";
    }
    
    echo "<hr><a href='?_route=customer_ticket/list'>Test Support Tickets</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
