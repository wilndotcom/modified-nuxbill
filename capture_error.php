<?php
/**
 * Capture the exact error from customer_ticket
 */

// Set up error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<h3 style='color:red'>PHP ERROR:</h3>";
    echo "<pre style='color:red'>";
    echo "Error: $errstr\n";
    echo "File: $errfile\n";
    echo "Line: $errline\n";
    echo "</pre>";
    return true;
});

set_exception_handler(function($e) {
    echo "<h3 style='color:red'>EXCEPTION:</h3>";
    echo "<pre style='color:red'>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString();
    echo "</pre>";
});

// Now include and run
$_GET['_route'] = 'customer_ticket/list';
include 'index.php';
