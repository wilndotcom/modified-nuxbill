<?php
// Test dashboard variables
$host = 'localhost';
$dbname = 'phpnuxbill';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Dashboard Variables Test ===\n";
    
    // Replicate dashboard controller logic
    $open_tickets_count = 0;
    $recent_tickets = [];
    $high_priority_tickets = [];
    $urgency_level = 'low';
    
    // Count open tickets
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_tickets WHERE status = 'open'");
    $open_tickets_count = $stmt->fetchColumn();
    echo "open_tickets_count: $open_tickets_count\n";
    
    if ($open_tickets_count > 0) {
        // Get recent tickets
        $stmt = $pdo->prepare("SELECT * FROM tbl_tickets WHERE status = 'open' ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "recent_tickets count: " . count($recent_tickets) . "\n";
        
        // Get high priority tickets
        $stmt = $pdo->prepare("SELECT * FROM tbl_tickets WHERE status = 'open' AND priority = 'high' ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $high_priority_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "high_priority_tickets count: " . count($high_priority_tickets) . "\n";
        
        // Calculate urgency level
        $urgency_level = 'low';
        if (count($high_priority_tickets) > 0) {
            $urgency_level = 'high';
        } elseif ($open_tickets_count > 5) {
            $urgency_level = 'medium';
        } elseif ($open_tickets_count > 0) {
            $urgency_level = 'normal';
        }
        echo "urgency_level: $urgency_level\n";
        
        // Check if alert should show
        $should_show = ($open_tickets_count > 0);
        echo "should_show_alert: " . ($should_show ? 'YES' : 'NO') . "\n";
        
        if ($should_show) {
            echo "\n✅ ALERT SHOULD BE VISIBLE!\n";
            echo "Template condition: {if isset(\$open_tickets_count) && \$open_tickets_count > 0}\n";
            echo "Variables that should be available:\n";
            echo "- \$open_tickets_count = $open_tickets_count\n";
            echo "- \$ticket_urgency_level = $urgency_level\n";
            echo "- \$recent_tickets = " . json_encode($recent_tickets) . "\n";
            echo "- \$high_priority_tickets = " . json_encode($high_priority_tickets) . "\n";
        } else {
            echo "\n❌ Alert should NOT be visible\n";
        }
    } else {
        echo "\n❌ No open tickets - alert should not be visible\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";
?>
