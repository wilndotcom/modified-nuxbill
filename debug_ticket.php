<?php
/**
 * Debug customer ticket - capture exact error
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture all output
ob_start();

try {
    include 'init.php';
    
    global $ui;
    
    echo "<h2>Debug Customer Ticket</h2>";
    
    // Check auth
    _auth();
    
    $user = User::_info();
    if (!$user || !$user->id) {
        echo "User not loaded, trying getID...<br>";
        $uid = User::getID();
        if ($uid) {
            $user = ORM::for_table('tbl_customers')->find_one($uid);
        }
    }
    
    if (!$user || !$user->id) {
        echo "No user, redirecting...<br>";
        exit;
    }
    
    echo "User: {$user->username}<br>";
    echo "UI object type: " . gettype($ui) . "<br>";
    
    if (!is_object($ui)) {
        echo "ERROR: UI is not an object!<br>";
        echo "UI value: ";
        var_dump($ui);
        exit;
    }
    
    // Test assign
    $ui->assign('_title', 'Test');
    echo "Assign successful<br>";
    
    // Get data
    $tickets = ORM::for_table('tbl_tickets')
        ->where('customer_id', $user->id)
        ->find_many();
    echo "Tickets found: " . count($tickets) . "<br>";
    
    // Assign and display
    $ui->assign('tickets', $tickets);
    $ui->assign('counts', ['all' => 0]);
    $ui->assign('categories', []);
    $ui->assign('current_status', 'all');
    $ui->assign('csrf_token', 'test');
    
    echo "All assignments done<br>";
    
    $ui->display('customer/ticket.tpl');
    
} catch (Throwable $e) {
    echo "<h3>ERROR:</h3>";
    echo "<pre style='color:red'>";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

$output = ob_get_clean();
echo $output;
