<?php
// Debug script to check ticket data
require_once 'system/boot.php';

echo "=== Ticket Debug Information ===\n";

try {
    // Check if table exists
    $tickets = ORM::for_table('tbl_tickets')->limit(1)->find_many();
    echo "✓ tbl_tickets table exists\n";
    
    // Count all tickets
    $total_count = ORM::for_table('tbl_tickets')->count();
    echo "Total tickets: $total_count\n";
    
    // Count open tickets
    $open_count = ORM::for_table('tbl_tickets')->where('status', 'open')->count();
    echo "Open tickets: $open_count\n";
    
    // Get recent open tickets
    $open_tickets = ORM::for_table('tbl_tickets')
        ->where('status', 'open')
        ->order_by_desc('created_at')
        ->limit(5)
        ->find_many();
    
    echo "\nRecent Open Tickets:\n";
    foreach ($open_tickets as $ticket) {
        echo "- ID: {$ticket['id']}, Subject: {$ticket['subject']}, Status: {$ticket['status']}, Created: {$ticket['created_at']}\n";
    }
    
    // Check high priority tickets
    $high_priority_count = ORM::for_table('tbl_tickets')
        ->where('status', 'open')
        ->where('priority', 'high')
        ->count();
    echo "\nHigh priority open tickets: $high_priority_count\n";
    
    // Get high priority tickets
    $high_priority_tickets = ORM::for_table('tbl_tickets')
        ->where('status', 'open')
        ->where('priority', 'high')
        ->order_by_desc('created_at')
        ->limit(3)
        ->find_many();
    
    echo "\nHigh Priority Tickets:\n";
    foreach ($high_priority_tickets as $ticket) {
        echo "- ID: {$ticket['id']}, Subject: {$ticket['subject']}, Priority: {$ticket['priority']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";
?>
