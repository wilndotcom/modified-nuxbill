<?php
/**
 * Capture exact error
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture all output
ob_start();

try {
    include 'init.php';
    
    echo "Step 1: init.php loaded<br>";
    
    // Get UI from boot
    if (isset($GLOBALS['ui'])) {
        $ui = $GLOBALS['ui'];
        echo "Step 2: UI obtained from \$GLOBALS['ui']<br>";
    } else {
        echo "Step 2 ERROR: \$GLOBALS['ui'] not set!<br>";
        var_dump(array_keys($GLOBALS));
        exit;
    }
    
    echo "Step 3: UI type: " . gettype($ui) . "<br>";
    
    if (!is_object($ui)) {
        echo "ERROR: UI is not an object!<br>";
        exit;
    }
    
    // Check auth
    _auth();
    echo "Step 4: Auth passed<br>";
    
    $user = User::_info();
    if (!$user || !$user->id) {
        $uid = User::getID();
        if ($uid) {
            $user = ORM::for_table('tbl_customers')->find_one($uid);
        }
    }
    
    if (!$user || !$user->id) {
        echo "ERROR: No user found<br>";
        exit;
    }
    
    echo "Step 5: User loaded - {$user->username}<br>";
    
    // Set title
    $ui->assign('_title', 'My Support Tickets');
    echo "Step 6: Title assigned<br>";
    
    $ui->assign('_system_menu', 'tickets');
    echo "Step 7: System menu assigned<br>";
    
    // Get tickets
    $tickets = ORM::for_table('tbl_tickets')
        ->where('customer_id', $user->id)
        ->order_by_desc('created_at')
        ->find_many();
    echo "Step 8: Tickets queried - " . count($tickets) . " found<br>";
    
    // Get counts
    $counts = [
        'all' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->count(),
        'open' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->where('status', 'open')->count(),
        'closed' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->where('status', 'closed')->count()
    ];
    echo "Step 9: Counts calculated<br>";
    
    // Get categories
    $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
    echo "Step 10: Categories loaded - " . count($categories) . " found<br>";
    
    // Assign all
    $ui->assign('tickets', $tickets);
    $ui->assign('counts', $counts);
    $ui->assign('categories', $categories);
    $ui->assign('current_status', 'all');
    $ui->assign('csrf_token', 'test123');
    echo "Step 11: All variables assigned<br>";
    
    // Display
    echo "Step 12: Attempting to display template...<br>";
    $ui->display('customer/ticket.tpl');
    echo "Step 13: Template displayed successfully!<br>";
    
} catch (Throwable $e) {
    echo "<hr><h3 style='color:red'>ERROR CAUGHT:</h3>";
    echo "<pre style='color:red'>";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nFull Trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}

$output = ob_get_clean();
echo $output;
