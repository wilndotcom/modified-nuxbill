<?php
/**
 * Check what the menu link actually generates
 */

include 'init.php';

echo "<h2>Menu Link Check</h2>";
echo "<hr>";

// Generate what the menu should show
echo "<h3>Support Tickets Menu Item HTML:</h3>";
echo "<pre style='background:#f5f5f5; padding:10px;'>";
echo htmlspecialchars("<li {if \$_system_menu eq 'tickets'}class='active' {/if}>\n");
echo htmlspecialchars("    <a href='" . Text::url('customer_ticket/list') . "'>\n");
echo htmlspecialchars("        <i class='fa fa-ticket'></i>\n");
echo htmlspecialchars("        <span>Support Tickets</span>\n");
echo htmlspecialchars("    </a>\n");
echo htmlspecialchars("</li>");
echo "</pre>";

echo "<hr>";

echo "<h3>Expected URL:</h3>";
echo Text::url('customer_ticket/list') . "<br>";

echo "<hr>";

echo "<h3>Test Links:</h3>";
echo "<a href='" . Text::url('customer_ticket/list') . "'>Direct Customer Ticket Link</a><br><br>";
echo "<a href='" . Text::url('ticket/list') . "'>Admin Ticket Link (for comparison)</a><br>";

echo "<hr>";

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>Click the 'Direct Customer Ticket Link' above</li>";
echo "<li>Check if it goes to customer or admin portal</li>";
echo "<li>If it goes to admin, there's a routing/URL generation issue</li>";
echo "</ol>";
