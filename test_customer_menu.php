<?php
// Test if customer ticket URL is accessible
echo "<h2>Testing Customer Ticket URL</h2>";
echo "<a href='?_route=customer_ticket/list'>Click here to test customer_ticket/list directly</a><br><br>";
echo "<a href='http://localhost/modified-nuxbill/?_route=customer_ticket/list'>Direct URL test</a><br><br>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Check session
echo "<h3>Session Data:</h3><pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
include 'init.php';
$uid = User::getID();
echo "<h3>User ID from session: " . ($uid ?: "NOT LOGGED IN") . "</h3>";

if ($uid) {
    $user = User::_info();
    echo "<h3>User Info:</h3><pre>";
    print_r($user);
    echo "</pre>";
}
