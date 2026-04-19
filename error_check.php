<?php
/**
 * Check for errors
 */
include 'init.php';

echo "<h2>Error Check</h2>";

// Check error log
$error_log = 'system/logs/error.log';
if (file_exists($error_log)) {
    echo "<h3>Recent Errors:</h3>";
    $lines = file($error_log);
    $recent = array_slice($lines, -20);
    echo "<pre>";
    foreach ($recent as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "No error log found<br>";
}

// Try to access customer ticket
echo "<h3>Test Access:</h3>";
echo "<a href='?_route=customer_ticket/list'>Try customer ticket</a>";
