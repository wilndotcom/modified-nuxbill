<?php
/**
 * Test customer ticket with full error display
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'init.php';

global $ui;

echo "<h2>Testing Customer Ticket</h2>";

try {
    // Simulate what the controller does
    $user = User::_info();
    if (!$user || !$user->id) {
        echo "User not logged in<br>";
        exit;
    }
    
    echo "User ID: {$user->id}<br>";
    echo "Username: {$user->username}<br>";
    
    // Try to get tickets
    echo "<h3>Querying tickets...</h3>";
    $tickets = ORM::for_table('tbl_tickets')
        ->where('customer_id', $user->id)
        ->order_by_desc('created_at')
        ->find_many();
    
    echo "Found " . count($tickets) . " tickets<br>";
    
    // Try to get categories
    echo "<h3>Querying categories...</h3>";
    $categories = ORM::for_table('tbl_ticket_categories')
        ->where('enabled', 1)
        ->find_many();
    
    echo "Found " . count($categories) . " categories<br>";
    
    // Try to assign to UI
    echo "<h3>Assigning to UI...</h3>";
    $ui->assign('tickets', $tickets);
    $ui->assign('counts', ['all' => 0, 'open' => 0, 'closed' => 0]);
    $ui->assign('categories', $categories);
    $ui->assign('current_status', 'all');
    $ui->assign('csrf_token', 'test_token');
    
    echo "Assignment successful<br>";
    
    // Try to display
    echo "<h3>Displaying template...</h3>";
    $ui->display('customer/ticket.tpl');
    
} catch (Exception $e) {
    echo "<h3>ERROR:</h3>";
    echo "<pre style='color:red'>";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h3>FATAL ERROR:</h3>";
    echo "<pre style='color:red'>";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
