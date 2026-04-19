<?php
/**
 * Clear all caches
 */
echo "<h2>Clearing All Caches...</h2>";

// Clear Smarty compiled templates
$dirs = [
    'system/cache',
    'ui/compiled',
    'ui/cache'
];

foreach($dirs as $dir) {
    if(is_dir($dir)) {
        $files = glob($dir . '/*');
        $count = 0;
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        echo "Cleared $count files from $dir<br>";
    } else {
        echo "Directory $dir does not exist<br>";
    }
}

// Clear session if requested
if(isset($_GET['session'])) {
    session_start();
    session_destroy();
    echo "<br>Session cleared!<br>";
}

echo "<hr><h3>Done!</h3>";
echo "<a href='?_route=customer_ticket/list&nocache=1'>Go to Support Tickets (with cache-buster)</a><br>";
echo "<a href='?_route=home'>Go to Home</a>";
