<?php
// Browser-accessible widget test
_admin();
$ui->assign('_title', 'Widget Debug');

echo "<h2>Widget Debug - Browser Test</h2>";

// Test 1: Check if widget file exists
$widgetFile = 'system/widgets/fiber_stats.php';
echo "<p>1. Widget file exists: " . (file_exists($widgetFile) ? '<span style="color: green;">YES</span>' : '<span style="color: red;">NO</span>') . "</p>";

// Test 2: Check template
echo "<p>2. Template file exists: " . (file_exists('ui/ui/widget/fiber_stats.tpl') ? '<span style="color: green;">YES</span>' : '<span style="color: red;">NO</span>') . "</p>";

// Test 3: Try to load and render
echo "<p>3. Testing render...<br>";
try {
    require_once 'system/widgets/fiber_stats.php';
    
    if (class_exists('fiber_stats')) {
        echo "Class exists: <span style='color: green;'>YES</span><br>";
        
        $widget = new fiber_stats();
        echo "Object created: <span style='color: green;'>YES</span><br>";
        
        $content = $widget->getWidget();
        $len = strlen($content);
        echo "Content length: {$len} chars<br>";
        
        if ($len > 0) {
            echo "<span style='color: green;'>✓ SUCCESS!</span></p>";
            echo "<h3>Widget Output Preview:</h3>";
            echo "<div style='border: 2px solid #337ab7; padding: 10px; margin: 10px 0;'>";
            echo substr($content, 0, 2000); // Show first 2000 chars
            echo "</div>";
        } else {
            echo "<span style='color: red;'>✗ EMPTY OUTPUT</span></p>";
            
            // Debug: Try direct fetch
            echo "<h3>Direct Template Test:</h3>";
            try {
                $ui->assign('total_olts', 5);
                $ui->assign('active_olts', 3);
                $ui->assign('offline_olts', 2);
                $ui->assign('total_onus', 10);
                $ui->assign('active_onus', 7);
                $ui->assign('suspended_onus', 1);
                $ui->assign('inactive_onus', 1);
                $ui->assign('offline_onus', 1);
                $ui->assign('total_cpes', 3);
                $ui->assign('active_cpes', 2);
                $ui->assign('offline_cpes', 1);
                $ui->assign('last_sync_time', date('Y-m-d H:i:s'));
                
                $direct = $ui->fetch('widget/fiber_stats.tpl');
                echo "<p>Direct fetch length: " . strlen($direct) . " chars</p>";
                
                if (strlen($direct) > 0) {
                    echo "<div style='border: 1px solid green; padding: 10px;'>";
                    echo $direct;
                    echo "</div>";
                }
            } catch (Throwable $e2) {
                echo "<p style='color: red;'>Direct fetch error: " . $e2->getMessage() . "</p>";
            }
        }
    } else {
        echo "Class exists: <span style='color: red;'>NO</span></p>";
    }
} catch (Throwable $e) {
    echo "<span style='color: red;'>ERROR: " . $e->getMessage() . "</span></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none;'>Go to Dashboard</a></p>";
