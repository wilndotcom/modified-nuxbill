<?php
/**
 * Auto Fix Database - Run this to fix everything
 */

include 'init.php';

echo "<h2>🔧 Auto Database Fix</h2>";
echo "<hr>";

$errors = [];
$success = [];

// 1. Create customers table
echo "<h3>1. Creating Customers Table...</h3>";
try {
    ORM::get_db()->exec("CREATE TABLE IF NOT EXISTS tbl_customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        fullname VARCHAR(100),
        email VARCHAR(100),
        phonenumber VARCHAR(20),
        address TEXT,
        status VARCHAR(20) DEFAULT 'Active',
        balance DECIMAL(10,2) DEFAULT 0,
        service_type VARCHAR(50) DEFAULT 'Personal',
        account_type VARCHAR(50) DEFAULT 'Member',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        photo VARCHAR(50) DEFAULT 'default'
    )");
    $success[] = "Customers table created";
    echo "✅ Customers table created<br>";
} catch (Exception $e) {
    $errors[] = "Customers table: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 2. Create tickets table
echo "<h3>2. Creating Tickets Table...</h3>";
try {
    ORM::get_db()->exec("CREATE TABLE IF NOT EXISTS tbl_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT,
        category VARCHAR(50),
        priority VARCHAR(20) DEFAULT 'medium',
        status VARCHAR(20) DEFAULT 'open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        closed_at DATETIME,
        assigned_to INT
    )");
    $success[] = "Tickets table created";
    echo "✅ Tickets table created<br>";
} catch (Exception $e) {
    $errors[] = "Tickets table: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Create ticket replies table
echo "<h3>3. Creating Ticket Replies Table...</h3>";
try {
    ORM::get_db()->exec("CREATE TABLE IF NOT EXISTS tbl_ticket_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        customer_id INT,
        admin_id INT,
        message TEXT NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $success[] = "Ticket replies table created";
    echo "✅ Ticket replies table created<br>";
} catch (Exception $e) {
    $errors[] = "Ticket replies table: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 4. Create ticket categories table
echo "<h3>4. Creating Ticket Categories Table...</h3>";
try {
    ORM::get_db()->exec("CREATE TABLE IF NOT EXISTS tbl_ticket_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        color VARCHAR(10) DEFAULT '#007bff',
        enabled TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $success[] = "Ticket categories table created";
    echo "✅ Ticket categories table created<br>";
} catch (Exception $e) {
    $errors[] = "Ticket categories table: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 5. Insert default categories
echo "<h3>5. Inserting Default Categories...</h3>";
try {
    $categories = [
        ['General Inquiry', 'General questions and inquiries', '#007bff'],
        ['Technical Support', 'Technical issues and troubleshooting', '#dc3545'],
        ['Billing', 'Billing and payment related questions', '#28a745'],
        ['Feature Request', 'Requests for new features', '#ffc107']
    ];
    
    foreach ($categories as $cat) {
        $exists = ORM::for_table('tbl_ticket_categories')->where('name', $cat[0])->find_one();
        if (!$exists) {
            ORM::for_table('tbl_ticket_categories')->create([
                'name' => $cat[0],
                'description' => $cat[1],
                'color' => $cat[2],
                'enabled' => 1
            ])->save();
        }
    }
    $success[] = "Default categories inserted";
    echo "✅ Default categories inserted<br>";
} catch (Exception $e) {
    $errors[] = "Categories: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 6. Insert test customer
echo "<h3>6. Inserting Test Customer...</h3>";
try {
    $exists = ORM::for_table('tbl_customers')->where('username', 'testuser')->find_one();
    if (!$exists) {
        ORM::for_table('tbl_customers')->create([
            'username' => 'testuser',
            'password' => '5f4dcc3b5aa765d61d8327deb882cf99', // MD5 of 'password'
            'fullname' => 'Test User',
            'email' => 'test@test.com',
            'phonenumber' => '1234567890',
            'address' => 'Test Address',
            'status' => 'Active',
            'balance' => 0,
            'service_type' => 'Personal',
            'account_type' => 'Member'
        ])->save();
        $success[] = "Test user created (username: testuser, password: password)";
        echo "✅ Test user created<br>";
        echo "Username: <b>testuser</b><br>";
        echo "Password: <b>password</b><br>";
    } else {
        echo "ℹ️ Test user already exists<br>";
    }
} catch (Exception $e) {
    $errors[] = "Test user: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Results:</h3>";

if (count($success) > 0) {
    echo "<p style='color:green'><b>✅ Successful:</b></p>";
    echo "<ul>";
    foreach ($success as $s) {
        echo "<li>$s</li>";
    }
    echo "</ul>";
}

if (count($errors) > 0) {
    echo "<p style='color:red'><b>❌ Errors:</b></p>";
    echo "<ul>";
    foreach ($errors as $e) {
        echo "<li>$e</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Clear browser cache (Ctrl + Shift + Delete)</li>";
echo "<li><a href='?_route=login'>Login here</a> with:</li>";
echo "<ul>";
echo "<li>Username: <b>testuser</b></li>";
echo "<li>Password: <b>password</b></li>";
echo "</ul>";
echo "<li><a href='?_route=customer_ticket/list'>Test Support Tickets</a></li>";
echo "</ol>";
