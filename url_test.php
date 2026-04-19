<?php
/**
 * URL Test - Check if URLs are different
 */

include 'init.php';

echo "<h2>URL Comparison Test</h2>";
echo "<hr>";

// Generate URLs
$dashboard_url = Text::url('home');
$ticket_list_url = Text::url('customer_ticket/list');
$ticket_create_url = Text::url('customer_ticket/create');

// Show URLs
echo "<h3>Generated URLs:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Route</th><th>Generated URL</th></tr>";
echo "<tr><td>home</td><td>$dashboard_url</td></tr>";
echo "<tr><td>customer_ticket/list</td><td>$ticket_list_url</td></tr>";
echo "<tr><td>customer_ticket/create</td><td>$ticket_create_url</td></tr>";
echo "</table>";

echo "<hr>";

// Compare
echo "<h3>URL Comparison:</h3>";
if ($dashboard_url === $ticket_list_url) {
    echo "<p style='color:red; font-size:18px;'><b>PROBLEM: Dashboard and Ticket URLs ARE THE SAME!</b></p>";
    echo "<p>This means the routing is broken or Text::url() is not working correctly.</p>";
} else {
    echo "<p style='color:green;'><b>OK: URLs are different</b></p>";
}

echo "<hr>";

// Test links
echo "<h3>Test Links:</h3>";
echo "<p>Right-click each link and check the actual URL:</p>";
echo "<a href='$dashboard_url' target='_blank'>Dashboard Link</a> (should go to home)<br><br>";
echo "<a href='$ticket_list_url' target='_blank'>Support Tickets Link</a> (should go to ticket list)<br><br>";
echo "<a href='$ticket_create_url' target='_blank'>Create Ticket Link</a><br><br>";

echo "<hr>";

// Check if controller file exists
echo "<h3>Controller Check:</h3>";
$controller = 'system/controllers/customer_ticket.php';
if (file_exists($controller)) {
    echo "<p style='color:green;'>Controller file exists: $controller</p>";
    echo "<p>File size: " . filesize($controller) . " bytes</p>";
} else {
    echo "<p style='color:red;'>Controller file MISSING: $controller</p>";
}

echo "<hr>";

// Check current user
echo "<h3>Current User:</h3>";
$uid = User::getID();
if ($uid) {
    echo "Logged in as user ID: $uid<br>";
    $user = User::_info();
    if ($user && $user->id) {
        echo "Username: {$user->username}<br>";
    } else {
        echo "<span style='color:red'>User data not loaded correctly!</span><br>";
    }
} else {
    echo "<span style='color:red'>NOT LOGGED IN</span><br>";
}

echo "<hr>";
echo "<h3>Action:</h3>";
echo "<ol>";
echo "<li>Right-click on 'Support Tickets Link' above</li>";
echo "<li>Select 'Copy link address'</li>";
echo "<li>Paste it here to verify the actual URL</li>";
echo "<li>If URLs are the same, the Text::url() function is broken</li>";
echo "</ol>";
