<?php
/**
 * URL Routing Diagnostic Tool
 */

include 'init.php';

echo "<h2>URL Routing Test</h2>";
echo "<hr>";

// Test 1: Check what URLs are being generated
echo "<h3>1. URL Generation Test</h3>";
echo "Text::url('home') = " . Text::url('home') . "<br>";
echo "Text::url('customer_ticket/list') = " . Text::url('customer_ticket/list') . "<br>";
echo "Text::url('customer_ticket/create') = " . Text::url('customer_ticket/create') . "<br>";
echo "<hr>";

// Test 2: Check current route
echo "<h3>2. Current Route</h3>";
$current_route = $_GET['_route'] ?? 'home';
echo "Current _route param: " . htmlspecialchars($current_route) . "<br>";
echo "<hr>";

// Test 3: Check if controller exists
echo "<h3>3. Controller File Check</h3>";
$controller_file = 'system/controllers/customer_ticket.php';
echo "Controller file exists: " . (file_exists($controller_file) ? 'YES' : 'NO') . "<br>";
if (file_exists($controller_file)) {
    echo "Controller file size: " . filesize($controller_file) . " bytes<br>";
}
echo "<hr>";

// Test 4: Check user authentication
echo "<h3>4. User Authentication</h3>";
$uid = User::getID();
echo "User::getID() = " . ($uid ?: "NOT LOGGED IN") . "<br>";
if ($uid) {
    $user = User::_info();
    if ($user && $user->id) {
        echo "User loaded: YES (ID: {$user->id}, Username: {$user->username})<br>";
    } else {
        echo "User loaded: NO - User::_info() returned false<br>";
    }
}
echo "<hr>";

// Test 5: Direct link test
echo "<h3>5. Direct Link Test</h3>";
echo "<p>Click these links to test routing:</p>";
echo "<a href='" . Text::url('home') . "'>Test: Dashboard (home)</a><br>";
echo "<a href='" . Text::url('customer_ticket/list') . "'>Test: Support Ticket List</a><br>";
echo "<a href='" . Text::url('customer_ticket/create') . "'>Test: Create Ticket</a><br>";
echo "<hr>";

// Test 6: Check menu comparison
echo "<h3>6. Menu URL Comparison</h3>";
$home_url = Text::url('home');
$ticket_url = Text::url('customer_ticket/list');
echo "Home URL: $home_url<br>";
echo "Ticket URL: $ticket_url<br>";
if ($home_url === $ticket_url) {
    echo "<span style='color:red'><b>WARNING: URLs ARE THE SAME!</b></span><br>";
} else {
    echo "<span style='color:green'>URLs are different - OK</span><br>";
}
echo "<hr>";

echo "<h3>Test Instructions:</h3>";
echo "<ol>";
echo "<li>Click 'Test: Support Ticket List' link above</li>";
echo "<li>Check if URL in browser address bar changes</li>";
echo "<li>If it stays on 'home', the routing is broken</li>";
echo "</ol>";
