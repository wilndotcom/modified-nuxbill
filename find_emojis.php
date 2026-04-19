<?php
/**
 * Find all emojis in templates
 */

function scanFiles($dir, $pattern) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $found = [];
    foreach ($files as $file) {
        if ($file->isFile() && preg_match('/\.(tpl|php|js|css|html)$/', $file->getFilename())) {
            $content = file_get_contents($file->getPathname());
            if (preg_match_all('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\u{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F600}-\x{1F64F}]/u', $content, $matches)) {
                $found[] = [
                    'file' => $file->getPathname(),
                    'emojis' => array_unique($matches[0])
                ];
            }
        }
    }
    return $found;
}

echo "<h2>Scanning for Emojis...</h2>";

$dirs = [
    'ui/ui/customer',
    'ui/ui/admin',
    'system/controllers'
];

foreach ($dirs as $dir) {
    echo "<h3>Scanning: $dir</h3>";
    $found = scanFiles($dir, '');
    if (empty($found)) {
        echo "<span style='color:green'>No emojis found</span><br>";
    } else {
        foreach ($found as $item) {
            echo "<span style='color:red'>" . $item['file'] . ": " . implode(', ', $item['emojis']) . "</span><br>";
        }
    }
}

echo "<hr><h3>Direct Search for Sun/Moon symbols:</h3>";

// Direct byte search for common emoji bytes
$emojiBytes = ['🌞', '🌜', '🌙', '☀', '⭐'];
foreach ($dirs as $dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && preg_match('/\.(tpl|php|js|css|html)$/', $file->getFilename())) {
            $content = file_get_contents($file->getPathname());
            foreach ($emojiBytes as $emoji) {
                if (strpos($content, $emoji) !== false) {
                    echo "<span style='color:red'>FOUND $emoji in: " . $file->getPathname() . "</span><br>";
                }
            }
        }
    }
}

echo "<br><b>Done!</b>";
