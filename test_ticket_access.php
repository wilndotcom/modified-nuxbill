<?php
/**
 * Comprehensive Support Ticket Access Test
 */

include 'init.php';

echo "<!DOCTYPE html><html><head><title>Ticket Access Test</title></head><body>";
echo "<h1>Support Ticket Access Diagnostic</h1><hr>";

$allPassed = true;

// TEST 1: Session Status
echo "<h2>1. Session Status</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: " . session_id() . "<br>";
echo "Session UID: " . (isset($_SESSION['uid']) ? $_SESSION['uid'] : 'NOT SET') . "<br>";

// TEST 2: User Authentication
echo "<h2>2. User Authentication</h2>";
$uid = User::getID();
if ($uid) {
    echo "✓ User::getID() returned: $uid<br>";
    $user = User::_info();
    if ($user && $user->id) {
        echo "✓ User::_info() loaded successfully<br>";
        echo "  - Username: {$user->username}<br>";
        echo "  - Email: {$user->email}<br>";
    } else {
        echo "✗ User::_info() returned false - User ID $uid not in database!<br>";
        $allPassed = false;
    }
} else {
    echo "✗ User::getID() returned 0 - Not logged in!<br>";
    $allPassed = false;
}

// TEST 3: Database Tables
echo "<h2>3. Database Tables</h2>";
$tables = ['tbl_tickets', 'tbl_ticket_replies', 'tbl_ticket_categories', 'tbl_customers'];
foreach ($tables as $table) {
    try {
        $exists = ORM::for_table($table)->count();
        echo "✓ Table '$table' exists<br>";
    } catch (Exception $e) {
        echo "✗ Table '$table' error: " . $e->getMessage() . "<br>";
        $allPassed = false;
    }
}

// TEST 4: Controller File
echo "<h2>4. Controller File</h2>";
$controller = 'system/controllers/customer_ticket.php';
if (file_exists($controller)) {
    echo "✓ Controller file exists<br>";
    // Check for syntax errors
    $output = shell_exec("php -l $controller 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✓ Controller syntax OK<br>";
    } else {
        echo "✗ Controller syntax error: $output<br>";
        $allPassed = false;
    }
} else {
    echo "✗ Controller file missing!<br>";
    $allPassed = false;
}

// TEST 5: Template Files
echo "<h2>5. Template Files</h2>";
$templates = [
    'ui/ui/customer/ticket.tpl',
    'ui/ui/customer/ticket_create.tpl',
    'ui/ui/customer/ticket_view.tpl',
    'ui/ui/customer/header.tpl',
    'ui/ui/customer/footer.tpl'
];
foreach ($templates as $tpl) {
    if (file_exists($tpl)) {
        echo "✓ $tpl exists<br>";
    } else {
        echo "✗ $tpl missing!<br>";
        $allPassed = false;
    }
}

// TEST 6: Check for emojis (JavaScript killers)
echo "<h2>6. Emoji Check (JavaScript Issues)</h2>";
$emojiFiles = [
    'ui/ui/customer/footer.tpl',
    'ui/ui/customer/header.tpl',
    'ui/ui/admin/footer.tpl',
    'ui/ui/admin/header.tpl'
];
$emojis = ['🌞', '🌜', '🌙', '☀'];
$emojiFound = false;
foreach ($emojiFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($emojis as $emoji) {
            if (strpos($content, $emoji) !== false) {
                echo "✗ Found emoji $emoji in $file<br>";
                $emojiFound = true;
                $allPassed = false;
            }
        }
    }
}
if (!$emojiFound) {
    echo "✓ No emojis found in templates<br>";
}

// TEST 7: Direct URL Test
echo "<h2>7. URL Routing Test</h2>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Route parameter: " . ($_GET['_route'] ?? 'not set') . "<br>";

// TEST 8: Test Links
echo "<h2>8. Test Links</h2>";
echo "<a href='?_route=customer_ticket/list' style='padding:10px;background:green;color:white;text-decoration:none;'>Test: customer_ticket/list</a><br><br>";
echo "<a href='?_route=customer_ticket/create' style='padding:10px;background:blue;color:white;text-decoration:none;'>Test: customer_ticket/create</a><br><br>";
echo "<a href='?_route=home' style='padding:10px;background:gray;color:white;text-decoration:none;'>Back to Home</a><br>";

// Summary
echo "<hr><h2>Summary</h2>";
if ($allPassed) {
    echo "<div style='background:green;color:white;padding:20px;font-size:20px;'>✓ ALL TESTS PASSED - Support Tickets should work!</div>";
} else {
    echo "<div style='background:red;color:white;padding:20px;font-size:20px;'>✗ SOME TESTS FAILED - See errors above</div>";
}

echo "</body></html>";
