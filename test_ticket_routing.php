<?php
/**
 * Test Ticket Routing
 */

require 'init.php';

echo "<h2>Ticket Routing Test</h2>";
echo "<pre>";

// Check if admin is logged in
echo "Admin logged in: " . (isset($_SESSION['aid']) ? 'YES (ID: ' . $_SESSION['aid'] . ')' : 'NO') . "\n";
echo "Customer logged in: " . (isset($_SESSION['uid']) ? 'YES (ID: ' . $_SESSION['uid'] . ')' : 'NO') . "\n\n";

// Check URL generation
echo "URL Generation:\n";
echo "  ticket/list => " . Text::url('ticket/list') . "\n";
echo "  customer_ticket/list => " . Text::url('customer_ticket/list') . "\n\n";

// Check if files exist
echo "Controller Files:\n";
echo "  ticket.php => " . (file_exists('system/controllers/ticket.php') ? 'EXISTS' : 'MISSING') . "\n";
echo "  customer_ticket.php => " . (file_exists('system/controllers/customer_ticket.php') ? 'EXISTS' : 'MISSING') . "\n\n";

// Check tables
echo "Database Tables:\n";
try {
    $tickets = ORM::for_table('tbl_tickets')->count();
    echo "  tbl_tickets => EXISTS (count: $tickets)\n";
} catch (Exception $e) {
    echo "  tbl_tickets => ERROR: " . $e->getMessage() . "\n";
}

echo "\n<a href='?_route=ticket/list'>Test Admin Ticket List</a> | ";
echo "<a href='?_route=customer_ticket/list'>Test Customer Ticket List</a>";

echo "</pre>";
