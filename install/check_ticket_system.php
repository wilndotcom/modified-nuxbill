<?php
/**
 * Check Support Ticket System Status
 */

include '../init.php';

echo "<h2>🎫 Support Ticket System Check</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

// 1. Check if ticket tables exist
echo "<strong>1. Database Tables</strong>\n";
$ticketTables = [
    'tbl_tickets',
    'tbl_ticket_replies',
    'tbl_ticket_categories',
    'tbl_ticket_attachments'
];

foreach ($ticketTables as $table) {
    try {
        $exists = ORM::for_table($table)->raw_query("SHOW TABLES LIKE '{$table}'")->find_one();
        echo "  " . ($exists ? '✅' : '❌') . " {$table}\n";
    } catch (Exception $e) {
        echo "  ❌ {$table} - " . $e->getMessage() . "\n";
    }
}

// 2. Check controller files
echo "\n<strong>2. Controller Files</strong>\n";
$controllers = [
    'system/controllers/ticket.php' => 'Ticket Controller (Admin)',
    'system/controllers/tickets.php' => 'Tickets Controller (Alt)',
    'system/controllers/support.php' => 'Support Controller',
];

foreach ($controllers as $file => $desc) {
    $exists = file_exists(__DIR__ . '/../' . $file);
    echo "  " . ($exists ? '✅' : '❌') . " {$desc}\n";
}

// 3. Check UI templates
echo "\n<strong>3. UI Templates</strong>\n";
$templates = [
    'ui/ui/admin/ticket/list.tpl' => 'Admin Ticket List',
    'ui/ui/admin/ticket/add.tpl' => 'Admin Add Ticket',
    'ui/ui/admin/ticket/view.tpl' => 'Admin View Ticket',
    'ui/ui/customer/ticket.tpl' => 'Customer Ticket Page',
    'ui/ui/customer/support.tpl' => 'Customer Support',
];

foreach ($templates as $file => $desc) {
    $exists = file_exists(__DIR__ . '/../' . $file);
    echo "  " . ($exists ? '✅' : '❌') . " {$desc}\n";
}

// 4. Check widget files
echo "\n<strong>4. Ticket Siren Widget</strong>\n";
$widgetFiles = [
    'system/widgets/ticket_siren.php' => 'Ticket Siren Widget Controller',
    'ui/ui/widget/ticket_siren.tpl' => 'Ticket Siren Template',
];

foreach ($widgetFiles as $file => $desc) {
    $exists = file_exists(__DIR__ . '/../' . $file);
    echo "  " . ($exists ? '✅' : '❌') . " {$desc}\n";
}

// 5. Check database for ticket data
echo "\n<strong>5. Ticket Data (if table exists)</strong>\n";
try {
    $ticketCount = ORM::for_table('tbl_tickets')->count();
    echo "  Total tickets: {$ticketCount}\n";
    
    $openCount = ORM::for_table('tbl_tickets')->where('status', 'open')->count();
    echo "  Open tickets: {$openCount}\n";
    
    $highPriority = ORM::for_table('tbl_tickets')
        ->where('status', 'open')
        ->where('priority', 'high')
        ->count();
    echo "  High priority open: {$highPriority}\n";
} catch (Exception $e) {
    echo "  Cannot query tickets: " . $e->getMessage() . "\n";
}

// Summary
echo "\n<strong>6. Summary</strong>\n";
echo "If you see many ❌ above, the ticket system needs to be implemented.\n";
echo "If you see ✅ for tables and controllers, it's working but may need the siren widget.\n";

echo "\n<a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a>\n";

echo "</pre>";
