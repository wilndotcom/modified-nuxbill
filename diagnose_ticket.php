<?php
/**
 * Diagnostic tool for customer ticket issues
 */

include 'init.php';

echo "<h2>Customer Ticket System Diagnostics</h2>";
echo "<hr>";

// 1. Check if user is logged in
echo "<h3>1. User Authentication</h3>";
$uid = User::getID();
echo "User::getID() = " . ($uid ?: "<span style='color:red'>NOT LOGGED IN</span>") . "<br>";

if ($uid) {
    $user = User::_info();
    echo "User::_info() type: " . gettype($user) . "<br>";
    if (is_object($user)) {
        echo "User ID: " . $user->id . "<br>";
        echo "Username: " . $user->username . "<br>";
    } else {
        echo "<span style='color:red'>User object is not valid!</span><br>";
    }
}
echo "<hr>";

// 2. Check database tables
echo "<h3>2. Database Tables</h3>";
try {
    $tables = ['tbl_tickets', 'tbl_ticket_replies', 'tbl_ticket_categories'];
    foreach ($tables as $table) {
        $exists = ORM::for_table($table)->raw_query("SHOW TABLES LIKE '$table'")->find_one();
        echo "$table: " . ($exists ? "<span style='color:green'>EXISTS</span>" : "<span style='color:red'>MISSING</span>") . "<br>";
    }
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 3. Check files
echo "<h3>3. Controller & Template Files</h3>";
$files = [
    'system/controllers/customer_ticket.php',
    'ui/ui/customer/ticket.tpl',
    'ui/ui/customer/ticket_create.tpl',
    'ui/ui/customer/ticket_view.tpl',
    'ui/ui/customer/header.tpl'
];
foreach ($files as $file) {
    $exists = file_exists($file);
    echo "$file: " . ($exists ? "<span style='color:green'>EXISTS</span>" : "<span style='color:red'>MISSING</span>") . "<br>";
}
echo "<hr>";

// 4. Session info
echo "<h3>4. Session Data</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "<hr>";

// 5. Direct link test
echo "<h3>5. Test Links</h3>";
echo "<a href='?_route=customer_ticket/list'>Test: customer_ticket/list</a><br>";
echo "<a href='?_route=customer_ticket/create'>Test: customer_ticket/create</a><br>";
echo "<hr>";

// 6. Emoji check in footer
echo "<h3>6. Checking for Emojis (JavaScript killers)</h3>";
$footer_content = file_get_contents('ui/ui/customer/footer.tpl');
if (strpos($footer_content, '🌞') !== false || strpos($footer_content, '🌜') !== false) {
    echo "<span style='color:red'>FOUND EMOJIS IN FOOTER - This breaks JavaScript!</span><br>";
} else {
    echo "<span style='color:green'>No emojis found in footer</span><br>";
}

$header_content = file_get_contents('ui/ui/customer/header.tpl');
if (strpos($header_content, '🌞') !== false || strpos($header_content, '🌜') !== false) {
    echo "<span style='color:red'>FOUND EMOJIS IN HEADER - This breaks JavaScript!</span><br>";
} else {
    echo "<span style='color:green'>No emojis found in header</span><br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Click the test links above - do they work?</li>";
echo "<li>Open browser console (F12) - any red errors?</li>";
echo "<li>Check if all items above show GREEN</li>";
echo "</ol>";
