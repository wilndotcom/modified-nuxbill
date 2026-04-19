<?php
/**
 * Menu HTML Test - Shows actual rendered HTML
 */

include 'init.php';

echo "<h2>Menu HTML Test</h2>";
echo "<hr>";

// Assign the system menu variable like the real app does
$ui->assign('_system_menu', 'home');

// Generate what the menu would look like
echo "<h3>Actual Menu HTML that should be generated:</h3>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ccc;'>";
echo htmlspecialchars("<li class=\"active\">\n");
echo htmlspecialchars("    <a href=\"" . Text::url('home') . "\">\n");
echo htmlspecialchars("        <i class=\"ion ion-monitor\"></i>\n");
echo htmlspecialchars("        <span>Dashboard</span>\n");
echo htmlspecialchars("    </a>\n");
echo htmlspecialchars("</li>");
echo "</pre>";

echo "<h3>Support Tickets Menu HTML:</h3>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ccc;'>";
echo htmlspecialchars("<li>\n");
echo htmlspecialchars("    <a href=\"" . Text::url('customer_ticket/list') . "\">\n");
echo htmlspecialchars("        <i class=\"fa fa-ticket\"></i>\n");
echo htmlspecialchars("        <span>Support Tickets</span>\n");
echo htmlspecialchars("    </a>\n");
echo htmlspecialchars("</li>");
echo "</pre>";

echo "<hr>";

// Show the actual URLs
echo "<h3>URL Comparison:</h3>";
$home_url = Text::url('home');
$ticket_url = Text::url('customer_ticket/list');

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Menu Item</th><th>Route</th><th>Generated URL</th></tr>";
echo "<tr>";
echo "<td>Dashboard</td>";
echo "<td>home</td>";
echo "<td><a href='$home_url'>$home_url</a></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Support Tickets</td>";
echo "<td>customer_ticket/list</td>";
echo "<td><a href='$ticket_url'>$ticket_url</a></td>";
echo "</tr>";
echo "</table>";

echo "<hr>";

// Check if URLs are the same
if ($home_url === $ticket_url) {
    echo "<h2 style='color:red;'>PROBLEM DETECTED!</h2>";
    echo "<p>The URLs for Dashboard and Support Tickets are IDENTICAL.</p>";
    echo "<p>This means either:</p>";
    echo "<ol>";
    echo "<li>The Text::url() function is broken</li>";
    echo "<li>The route 'customer_ticket/list' is not defined</li>";
    echo "<li>The routing system is returning default URL for unknown routes</li>";
    echo "</ol>";
} else {
    echo "<h2 style='color:green;'>URLs are different - OK</h2>";
    echo "<p>The menu links should work correctly.</p>";
}

echo "<hr>";
echo "<h3>Test Instructions:</h3>";
echo "<ol>";
echo "<li>Right-click on both links in the table above</li>";
echo "<li>Copy the link addresses</li>";
echo "<li>If they are different, click the Support Tickets link</li>";
echo "<li>If they are the same, the routing is broken</li>";
echo "</ol>";
