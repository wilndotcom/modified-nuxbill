<?php
echo "Scanning ALL files for emojis...\n\n";
$emojis = ['🌞', '🌜', '🌙', '☀', '⭐', '🌛'];
$found = false;

function scanDir($dir, $emojis, &$found) {
    foreach(glob($dir . '/*') as $path) {
        if(is_dir($path)) {
            scanDir($path, $emojis, $found);
        } elseif(is_file($path) && preg_match('/\.(tpl|php|js|html|htm|css)$/', $path)) {
            $content = @file_get_contents($path);
            if($content === false) continue;
            foreach($emojis as $emoji) {
                if(strpos($content, $emoji) !== false) {
                    echo "FOUND $emoji in: $path\n";
                    $found = true;
                    break;
                }
            }
        }
    }
}

scanDir('ui/ui', $emojis, $found);
scanDir('system', $emojis, $found);

if(!$found) echo "No emojis found in any files!\n";
echo "\nDone.\n";
