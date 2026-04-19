<?php
/**
 * Capture the 500 error
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/modified-nuxbill/my_error.log');

try {
    // Simulate the route
    $_GET['_route'] = 'customer_ticket/list';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Include the app
    include 'index.php';
    
} catch (Throwable $e) {
    echo "<h2>Fatal Error Caught:</h2>";
    echo "<pre style='color:red;font-size:14px'>";
    echo "Type: " . get_class($e) . "\n\n";
    echo "Message:\n" . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
    
    // Also log to file
    error_log("ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}
