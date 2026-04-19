<?php
echo "<h2>Scanning for emojis...</h2>";
$emojis = ['🌞', '🌜', '🌙', '☀', '⭐'];
$files = [
    'ui/ui/customer/header.tpl',
    'ui/ui/customer/footer.tpl',
    'ui/ui/admin/header.tpl',
    'ui/ui/admin/footer.tpl',
    'ui/ui/scripts/custom.js'
];

foreach($files as $file) {
    if(!file_exists($file)) {
        echo "<span style='color:orange'>$file - NOT FOUND</span><br>";
        continue;
    }
    $content = file_get_contents($file);
    $found = false;
    foreach($emojis as $emoji) {
        if(strpos($content, $emoji) !== false) {
            echo "<span style='color:red'>FOUND $emoji in $file</span><br>";
            $found = true;
            break;
        }
    }
    if(!$found) {
        echo "<span style='color:green'>$file - OK (no emojis)</span><br>";
    }
}

echo "<hr><h3>Cache Busting</h3>";
echo "Try this URL to force reload:<br>";
echo "<a href='?_route=customer_ticket/list&v=
