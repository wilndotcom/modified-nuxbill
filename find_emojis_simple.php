<?php
/**
 * Simple emoji finder
 */

echo "<h2>Scanning for Emojis...</h2>";

$emojiBytes = ['🌞', '🌜', '🌙', '☀', '⭐', '🌛'];
$dirs = ['ui/ui/customer', 'ui/ui/admin'];

foreach ($dirs as $dir) {
    echo "<h3>Scanning: $dir</h3>";
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $foundAny = false;
    foreach ($files as $file) {
        if ($file->isFile() && preg_match('/\.(tpl|php|js)$/', $file->getFilename())) {
            $content = file_get_contents($file->getPathname());
            foreach ($emojiBytes as $emoji) {
                if (strpos($content, $emoji) !== false) {
                    echo "<span style='color:red'>FOUND $emoji in: " . $file->getPathname() . "</span><br>";
                    $foundAny = true;
                }
            }
        }
    }
    if (!$foundAny) {
        echo "<span style='color:green'>No emojis found</span><br>";
    }
}

echo "<br><b>Done!</b>";
