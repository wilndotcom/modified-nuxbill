<?php
/**
 * Create Support Ticket System Database Tables
 */

include '../init.php';

echo "<h2>🎫 Creating Ticket System Tables</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

$errors = [];
$success = [];

// SQL statements
$tables = [
    'tbl_tickets' => "CREATE TABLE IF NOT EXISTS tbl_tickets (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id INT(11) UNSIGNED NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT,
        status ENUM('open', 'pending', 'closed') DEFAULT 'open',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        category VARCHAR(50) DEFAULT 'general',
        assigned_to INT(11) UNSIGNED DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        closed_at DATETIME DEFAULT NULL,
        admin_read_at DATETIME DEFAULT NULL,
        customer_read_at DATETIME DEFAULT NULL,
        INDEX idx_customer_id (customer_id),
        INDEX idx_admin_read_at (admin_read_at),
        INDEX idx_customer_read_at (customer_read_at),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'tbl_ticket_replies' => "CREATE TABLE IF NOT EXISTS tbl_ticket_replies (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT(11) UNSIGNED NOT NULL,
        customer_id INT(11) UNSIGNED DEFAULT NULL,
        admin_id INT(11) UNSIGNED DEFAULT NULL,
        message TEXT NOT NULL,
        is_staff BOOLEAN DEFAULT FALSE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ticket_id (ticket_id),
        FOREIGN KEY (ticket_id) REFERENCES tbl_tickets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'tbl_ticket_categories' => "CREATE TABLE IF NOT EXISTS tbl_ticket_categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        color VARCHAR(20) DEFAULT '#337ab7',
        enabled BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tables as $table => $sql) {
    try {
        ORM::for_table($table)->raw_query($sql);
        echo "✅ Created table: {$table}\n";
        $success[] = $table;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️ Table {$table} already exists\n";
            $success[] = $table;
        } else {
            echo "❌ Error creating {$table}: " . $e->getMessage() . "\n";
            $errors[] = $table . ': ' . $e->getMessage();
        }
    }
}

// Insert default categories
if (in_array('tbl_ticket_categories', $success)) {
    echo "\n<strong>Adding default categories...</strong>\n";
    
    $categories = [
        ['name' => 'General Support', 'description' => 'General technical support inquiries', 'color' => '#337ab7'],
        ['name' => 'Billing', 'description' => 'Billing and payment related issues', 'color' => '#5cb85c'],
        ['name' => 'Technical', 'description' => 'Technical issues and troubleshooting', 'color' => '#f0ad4e'],
        ['name' => 'Sales', 'description' => 'Sales and plan inquiries', 'color' => '#5bc0de'],
        ['name' => 'Complaint', 'description' => 'Customer complaints', 'color' => '#d9534f'],
    ];
    
    foreach ($categories as $cat) {
        try {
            $exists = ORM::for_table('tbl_ticket_categories')->where('name', $cat['name'])->find_one();
            if (!$exists) {
                $c = ORM::for_table('tbl_ticket_categories')->create();
                $c->name = $cat['name'];
                $c->description = $cat['description'];
                $c->color = $cat['color'];
                $c->save();
                echo "  ✅ Added category: {$cat['name']}\n";
            } else {
                echo "  ⚠️ Category exists: {$cat['name']}\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Error adding category: " . $e->getMessage() . "\n";
        }
    }
}

// Summary
echo "\n<strong>Summary:</strong>\n";
if (empty($errors)) {
    echo "✅ All tables created successfully!\n";
} else {
    echo "❌ Errors encountered:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
}

echo "\n<a href='../?_route=ticket/list' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Tickets</a>\n";

echo "</pre>";
