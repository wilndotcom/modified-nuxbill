<?php
// Create test ticket to verify siren notification
$host = 'localhost';
$dbname = 'phpnuxbill'; // Update with your actual database name
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database\n";
    
    // Check if tbl_tickets exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_tickets'");
    if ($stmt->rowCount() == 0) {
        echo "tbl_tickets table does not exist!\n";
        exit;
    }
    
    // Count existing tickets
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_tickets WHERE status = 'open'");
    $open_count = $stmt->fetchColumn();
    echo "Current open tickets: $open_count\n";
    
    if ($open_count == 0) {
        echo "Creating a test ticket to trigger siren notification...\n";
        
        // Insert test ticket
        $stmt = $pdo->prepare("INSERT INTO tbl_tickets (customer_id, subject, message, status, priority, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            1, // customer_id (assuming user ID 1 exists)
            'Test Ticket for Siren Notification',
            'This is a test ticket to verify the siren notification system is working properly.',
            'open',
            'high',
            date('Y-m-d H:i:s')
        ]);
        
        echo "Test ticket created successfully!\n";
        
        // Verify it was created
        $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_tickets WHERE status = 'open'");
        $new_open_count = $stmt->fetchColumn();
        echo "New open tickets count: $new_open_count\n";
        
        if ($new_open_count > 0) {
            echo "✅ SUCCESS: The siren notification should now be visible on dashboard!\n";
            echo "Visit your dashboard to see the alert.\n";
        }
    } else {
        echo "You already have $open_count open ticket(s).\n";
        echo "The siren notification should be visible on your dashboard.\n";
        
        // Show recent open tickets
        $stmt = $pdo->query("SELECT id, subject, priority, created_at FROM tbl_tickets WHERE status = 'open' ORDER BY created_at DESC LIMIT 5");
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nRecent Open Tickets:\n";
        foreach ($tickets as $ticket) {
            echo "- ID: {$ticket['id']}, Subject: {$ticket['subject']}, Priority: {$ticket['priority']}, Created: {$ticket['created_at']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}
?>
