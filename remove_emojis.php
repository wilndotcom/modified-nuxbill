<?php
/**
 * Remove all emoji characters from template files
 */

$emoji_pattern = '/[\x{1F300}-\x{1F9FF}]/u';

$template_dir = __DIR__ . '/ui/ui';

function process_directory($dir, $pattern) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $count = 0;
    
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['tpl', 'html', 'php'])) {
            $content = file_get_contents($file->getPathname());
            $original = $content;
            
            // Remove emojis
            $content = preg_replace($pattern, '', $content);
            
            if ($content !== $original) {
                file_put_contents($file->getPathname(), $content);
                echo "Fixed: " . $file->getPathname() . "\n";
                $count++;
            }
        }
    }
    
    return $count;
}

$fixed = process_directory($template_dir, $emoji_pattern);
echo "\nFixed $fixed files\n";
echo "<a href='?_route=dashboard'>Go to Dashboard</a>";
