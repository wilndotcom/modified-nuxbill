<?php
echo "Scanning for emojis...\n\n";
$emojis = ['🌞', '🌜', '🌙', '☀', '⭐', '🌛'];
$found = false;

foreach(glob('ui/ui/customer/*.tpl') as $file) {
    $content = file_get_contents($file);
    foreach($emojis as $emoji) {
        if(strpos($content, $emoji) !== false) {
            echo "FOUND $emoji in: $file\n";
            $found = true;
        }
    }
}

foreach(glob('ui/ui/admin/*.tpl') as $file) {
    $content = file_get_contents($file);
    foreach($emojis as $emoji) {
        if(strpos($content, $emoji) !== false) {
            echo "FOUND $emoji in: $file\n";
            $found = true;
        }
    }
}

foreach(glob('ui/ui/scripts/*.js') as $file) {
    $content = file_get_contents($file);
    foreach($emojis as $emoji) {
        if(strpos($content, $emoji) !== false) {
            echo "FOUND $emoji in: $file\n";
            $found = true;
        }
    }
}

if(!$found) echo "No emojis found in templates!\n";
echo "\nDone.\n";
