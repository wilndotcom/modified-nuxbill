<?php
/**
 * Trace Test - Follow the redirect path
 */

// Test 1: Direct access to controller
echo "<h2>Test 1: Direct Controller Access</h2>";
$controller_file = 'system/controllers/customer_ticket.php';
echo "Controller file: $controller_file<br>";
echo "Exists: " . (file_exists($controller_file) ? 'YES' : 'NO') . "<br>";
echo "Readable: " . (is_readable($controller_file) ? 'YES' : 'NO') . "<br>";
if (file_exists($controller_file)) {
    echo "Size: " . filesize($controller_file) . " bytes<br>";
}
echo "<hr>";

// Test 2: URL Generation
echo "<h2>Test 2: URL Generation</h2>";
include 'init.php';

echo "U constant: " . U . "<br>";
echo "Text::url('home'): " . Text::url('home') . "<br>";
echo "Text::url('customer_ticket/list'): " . Text::url('customer_ticket/list') . "<br>";
echo "Text::url('customer_ticket/create'): " . Text::url('customer_ticket/create') . "<br>";
echo "<hr>";

// Test 3: Check if we can simulate the route
echo "<h2>Test 3: Route Simulation</h2>";
$test_routes = [
    'customer_ticket',
    'customer_ticket/list',
    'customer_ticket/create',
    'customer_ticket/view/1'
];

foreach ($test_routes as $route) {
    $parts = explode('/', $route);
    $handler = $parts[0];
    $action = $parts[1] ?? 'list';
    
    $controller_path = 'system/controllers/' . $handler . '.php';
    $exists = file_exists($controller_path);
    
    echo "Route: $route<br>";
    echo "  Handler: $handler<br>";
    echo "  Action: $action<br>";
    echo "  Controller exists: " . ($exists ? 'YES' : 'NO') . "<br>";
    echo "  Generated URL: " . Text::url($route) . "<br><br>";
}
echo "<hr>";

// Test 4: Direct links
echo "<h2>Test 4: Direct Links (No JavaScript)</h2>";
echo "<p>These are plain HTML links. If they still redirect, it's a server issue:</p>";
echo '<a href="' . Text::url('home') . '">Direct to Home</a><br><br>';
echo '<a href="' . Text::url('customer_ticket/list') . '">Direct to Ticket List</a><br><br>';
echo '<a href="' . U . '?_route=customer_ticket/list">Direct to Ticket (explicit)</a><br>';
echo "<hr>";

echo "<h3>Diagnosis:</h3>";
echo "<ol>";
echo "<li>If 'Controller exists' shows NO above, the file is missing or in wrong location</li>";
echo "<li>If URLs are different but still redirect to home, there's a server-side redirect</li>";
echo "<li>If all links work except the menu, there's a JavaScript issue</li>";
echo "</ol>";
