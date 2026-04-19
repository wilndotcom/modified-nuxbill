<?php
/**
 * Fix Ticket Tables - Direct SQL Execution
 */

include '../init.php';

echo "<h2>🔧 Creating Ticket Tables</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

try {
    $pdo = ORM::get_db();
    
    // Create tbl_tickets
    echo "Creating tbl_tickets...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_tickets (
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
        INDEX idx_customer_id (customer_id),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ tbl_tickets created\n";
    
    // Create tbl_ticket_replies
    echo "\nCreating tbl_ticket_replies...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_ticket_replies (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT(11) UNSIGNED NOT NULL,
        customer_id INT(11) UNSIGNED DEFAULT NULL,
        admin_id INT(11) UNSIGNED DEFAULT NULL,
        message TEXT NOT NULL,
        is_staff BOOLEAN DEFAULT FALSE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ticket_id (ticket_id),
        FOREIGN KEY (ticket_id) REFERENCES tbl_tickets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ tbl_ticket_replies created\n";
    
    // Create tbl_ticket_categories
    echo "\nCreating tbl_ticket_categories...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_ticket_categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        color VARCHAR(20) DEFAULT '#337ab7',
        enabled BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ tbl_ticket_categories created\n";
    
    // Insert default categories
    echo "\nAdding default categories...\n";
    $categories = [
        ['General Support', 'General technical support inquiries', '#337ab7'],
        ['Billing', 'Billing and payment related issues', '#5cb85c'],
        ['Technical', 'Technical issues and troubleshooting', '#f0ad4e'],
        ['Sales', 'Sales and plan inquiries', '#5bc0de'],
        ['Complaint', 'Customer complaints', '#d9534f'],
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_ticket_categories (name, description, color) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
        echo "  ✓ Added: {$cat[0]}\n";
    }
    
    echo "\n<strong>✅ All tables and categories created successfully!</strong>\n";
    echo "\n<a href='../?_route=customer_ticket/list' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Customer Tickets</a>\n";
    
} catch (PDOException $e) {
    echo "\n<strong>❌ Error:</strong> " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "\n<strong>❌ Error:</strong> " . $e->getMessage() . "\n";
}

echo "</pre>";
