<?php
// Simple ticket debug
define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Basic ORM setup
require_once APP_PATH . 'system' . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'ORM.php';

// Database configuration
$config = [];
if (file_exists(APP_PATH . 'system' . DIRECTORY_SEPARATOR . 'config.php')) {
    require_once APP_PATH . 'system' . DIRECTORY_SEPARATOR . 'config.php';
}

echo "=== Ticket Debug Information ===\n";

try {
    // Initialize ORM
    ORM::configure(array(
        'connection_string' => 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'],
        'username' => $config['db_user'],
        'password' => $config['db_password'],
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
    ));
    
    // Check if table exists
    $tickets = ORM::for_table('tbl_tickets')->limit(1)->find_many();
    echo "✓ tbl_tickets table exists\n";
    
    // Count all tickets
    $total_count = ORM::for_table('tbl_tickets')->count();
    echo "Total tickets: $total_count\n";
    
    // Count open tickets
    $open_count = ORM::for_table('tbl_tickets')->where('status', 'open')->count();
    echo "Open tickets: $open_count\n";
    
    if ($open_count > 0) {
        // Get recent open tickets
        $open_tickets = ORM::for_table('tbl_tickets')
            ->where('status', 'open')
            ->order_by_desc('created_at')
            ->limit(5)
            ->find_many();
        
        echo "\nRecent Open Tickets:\n";
        foreach ($open_tickets as $ticket) {
            echo "- ID: {$ticket['id']}, Subject: {$ticket['subject']}, Status: {$ticket['status']}\n";
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
    } else {
        echo "\nNo open tickets found - this is why the siren notification is not visible!\n";
        echo "The siren only appears when there are open tickets.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";
?>
