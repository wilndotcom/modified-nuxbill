<?php
/**
 * Trace where the redirect is coming from
 */

include 'init.php';

echo "<h2>🔍 Redirect Trace</h2>";
echo "<hr>";

// 1. Check user type
echo "<h3>1. User Type Check</h3>";
$uid = User::getID();
if ($uid) {
    echo "User ID: $uid<br>";
    $user = User::_info();
    if ($user && $user->id) {
        echo "User loaded: YES<br>";
        echo "Username: {$user->username}<br>";
        echo "Account type: " . ($user->account_type ?? 'N/A') . "<br>";
        echo "Service type: " . ($user->service_type ?? 'N/A') . "<br>";
    } else {
        echo "User loaded: NO<br>";
    }
} else {
    echo "Not logged in<br>";
}
echo "<hr>";

// 2. Check what URL is being generated
echo "<h3>2. URL Generation Check</h3>";
echo "customer_ticket/list URL: " . Text::url('customer_ticket/list') . "<br>";
echo "ticket/list URL: " . Text::url('ticket/list') . "<br>";
echo "home URL: " . Text::url('home') . "<br>";
echo "<hr>";

// 3. Check if customer menu is showing correct link
echo "<h3>3. Customer Menu Link</h3>";
echo "Expected: " . Text::url('customer_ticket/list') . "<br>";
echo "<hr>";

// 4. Test direct access with trace
echo "<h3>4. Test Direct Access</h3>";
echo "<a href='?_route=customer_ticket/list&trace=1'>Click here with trace</a><br>";
echo "<hr>";

// 5. Check if there's a default case redirect
echo "<h3>5. Controller Analysis</h3>";
$controller_file = 'system/controllers/customer_ticket.php';
$content = file_get_contents($controller_file);
if (strpos($content, "r2(getUrl('ticket/list')") !== false) {
    echo "<span style='color:red'>⚠️ Found redirect to 'ticket/list' (admin) in controller!</span><br>";
} else {
    echo "✅ No redirect to admin ticket found<br>";
}

if (strpos($content, "r2(getUrl('customer_ticket/list')") !== false) {
    echo "✅ Found redirect to 'customer_ticket/list' (customer)<br>";
}
echo "<hr>";

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>Click the 'Test Direct Access' link above</li>";
echo "<li>Check what URL you end up on</li>";
echo "<li>If it goes to admin, there's a routing issue</li>";
echo "</ol>";
