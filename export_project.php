<?php
/**
 * PHPNuxBill Project Export Script
 * Run this script via web browser or command line
 */

// Configuration
$project_dir = 'C:\xampp\htdocs\phpnuxbill';
$export_dir = 'C:\xampp\htdocs\phpnuxbill_exports';
$timestamp = date('Y_m_d_H_i_s');
$db_name = 'phpnuxbill'; // Update if different

// Web interface
if (php_sapi_name() !== 'cli') {
    echo '<!DOCTYPE html><html><head><title>PHPNuxBill Export Tool</title>';
    echo '<style>body{font-family:Arial,sans-serif;margin:20px;}';
    echo '.container{max-width:800px;margin:0 auto;}';
    echo '.progress{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:5px;}';
    echo '.success{background:#d4edda;color:#155724;}';
    echo '.error{background:#f8d7da;color:#721c24;}';
    echo '.info{background:#d1ecf1;color:#0c5460;}';
    echo 'button{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;}';
    echo 'button:hover{background:#0056b3;}';
    echo '</style></head><body>';
    echo '<div class="container">';
    echo '<h1>PHPNuxBill Project Export Tool</h1>';
    
    if (isset($_POST['export'])) {
        exportProject($project_dir, $export_dir, $timestamp, $db_name);
    } else {
        showExportForm();
    }
    
    echo '</div></body></html>';
} else {
    // Command line interface
    echo "PHPNuxBill Project Export Tool\n";
    echo "============================\n";
    exportProject($project_dir, $export_dir, $timestamp, $db_name);
}

function showExportForm() {
    echo '<div class="info">';
    echo '<p>This tool will export your PHPNuxBill project including:</p>';
    echo '<ul>';
    echo '<li>Complete database backup</li>';
    echo '<li>All project files</li>';
    echo '<li>Configuration files</li>';
    echo '<li>Deployment package with installation guide</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<form method="post">';
    echo '<button type="submit" name="export" value="1">Start Export</button>';
    echo '</form>';
}

function exportProject($project_dir, $export_dir, $timestamp, $db_name) {
    $success = true;
    $messages = [];
    
    // Create export directory
    if (!is_dir($export_dir)) {
        if (!mkdir($export_dir, 0777, true)) {
            $messages[] = ['error', 'Failed to create export directory'];
            $success = false;
        } else {
            $messages[] = ['success', 'Export directory created'];
        }
    }
    
    if ($success) {
        // Database export
        try {
            $config = include $project_dir . '/config.php';
            $db_host = $config['db_host'] ?? 'localhost';
            $db_user = $config['db_user'] ?? 'root';
            $db_pass = $config['db_password'] ?? '';
            
            $db_file = $export_dir . '/database_' . $timestamp . '.sql';
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                $db_host,
                $db_user,
                $db_pass,
                $db_name,
                $db_file
            );
            
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($db_file)) {
                $messages[] = ['success', 'Database exported successfully'];
            } else {
                $messages[] = ['error', 'Database export failed: ' . implode(', ', $output)];
                $success = false;
            }
        } catch (Exception $e) {
            $messages[] = ['error', 'Database export error: ' . $e->getMessage()];
            $success = false;
        }
        
        // Files export
        if ($success && class_exists('ZipArchive')) {
            try {
                $zip_file = $export_dir . '/project_files_' . $timestamp . '.zip';
                $zip = new ZipArchive();
                
                if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($project_dir),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($project_dir) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                    
                    $zip->close();
                    $messages[] = ['success', 'Project files compressed successfully'];
                } else {
                    $messages[] = ['error', 'Failed to create ZIP archive'];
                    $success = false;
                }
            } catch (Exception $e) {
                $messages[] = ['error', 'Files export error: ' . $e->getMessage()];
                $success = false;
            }
        } else {
            $messages[] = ['error', 'ZipArchive class not available'];
            $success = false;
        }
        
        // Create deployment package
        if ($success) {
            $deploy_dir = $export_dir . '/deployment_' . $timestamp;
            if (!mkdir($deploy_dir, 0777, true)) {
                $messages[] = ['error', 'Failed to create deployment directory'];
                $success = false;
            } else {
                // Copy database file
                if (copy($db_file, $deploy_dir . '/database.sql')) {
                    $messages[] = ['success', 'Database copied to deployment package'];
                } else {
                    $messages[] = ['error', 'Failed to copy database file'];
                }
                
                // Copy config file
                $config_file = $project_dir . '/config.php';
                if (copy($config_file, $deploy_dir . '/config_example.php')) {
                    $messages[] = ['success', 'Configuration file copied'];
                } else {
                    $messages[] = ['error', 'Failed to copy configuration file'];
                }
                
                // Create README
                $readme_content = createReadmeContent($timestamp);
                if (file_put_contents($deploy_dir . '/README.txt', $readme_content)) {
                    $messages[] = ['success', 'Installation guide created'];
                } else {
                    $messages[] = ['error', 'Failed to create installation guide'];
                }
            }
        }
    }
    
    // Display results
    if (php_sapi_name() !== 'cli') {
        foreach ($messages as $message) {
            $type = $message[0];
            $text = $message[1];
            echo "<div class='progress $type'>$text</div>";
        }
        
        if ($success) {
            echo '<div class="progress success">';
            echo '<h2>Export Completed Successfully!</h2>';
            echo '<p><strong>Export Location:</strong> ' . htmlspecialchars($export_dir) . '</p>';
            echo '<p><strong>Timestamp:</strong> ' . $timestamp . '</p>';
            echo '<p><strong>Files Created:</strong></p>';
            echo '<ul>';
            echo '<li>database_' . $timestamp . '.sql (Database backup)</li>';
            echo '<li>project_files_' . $timestamp . '.zip (Project files)</li>';
            echo '<li>deployment_' . $timestamp . '/ (Deployment package)</li>';
            echo '</ul>';
            echo '<p><strong>Next Steps:</strong></p>';
            echo '<ol>';
            echo '<li>Copy the deployment_' . $timestamp . ' folder to the new device</li>';
            echo '<li>Follow the README.txt instructions</li>';
            echo '</ol>';
            echo '</div>';
        } else {
            echo '<div class="progress error">';
            echo '<h2>Export Failed!</h2>';
            echo '<p>Please check the error messages above and try again.</p>';
            echo '</div>';
        }
    } else {
        // CLI output
        echo "\nExport Results:\n";
        echo "===============\n";
        foreach ($messages as $message) {
            echo "[{$message[0]}] {$message[1]}\n";
        }
        
        if ($success) {
            echo "\nExport Completed Successfully!\n";
            echo "Export Location: $export_dir\n";
            echo "Timestamp: $timestamp\n";
        } else {
            echo "\nExport Failed! Please check the errors above.\n";
        }
    }
}

function createReadmeContent($timestamp) {
    $content = "PHPNuxBill Export Package\n";
    $content .= "========================\n";
    $content .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
    $content .= "Timestamp: $timestamp\n\n";
    
    $content .= "INSTALLATION INSTRUCTIONS:\n";
    $content .= "--------------------------\n\n";
    
    $content .= "1. Environment Setup:\n";
    $content .= "   - Install XAMPP (or similar stack) on new device\n";
    $content .= "   - Ensure PHP 7.4+ and MySQL 5.7+ are installed\n";
    $content .= "   - Start Apache and MySQL services\n\n";
    
    $content .= "2. Database Setup:\n";
    $content .= "   - Open phpMyAdmin (http://localhost/phpmyadmin)\n";
    $content .= "   - Create new database named 'phpnuxbill'\n";
    $content .= "   - Import the database.sql file\n\n";
    
    $content .= "3. Project Files:\n";
    $content .= "   - Extract project_files_$timestamp.zip to C:\\xampp\\htdocs\\phpnuxbill\n";
    $content .= "   - Ensure the folder structure is preserved\n\n";
    
    $content .= "4. Configuration:\n";
    $content .= "   - Copy config_example.php to config.php\n";
    $content .= "   - Update database credentials in config.php:\n";
    $content .= "     - db_host: localhost\n";
    $content .= "     - db_name: phpnuxbill\n";
    $content .= "     - db_user: root (or your MySQL user)\n";
    $content .= "     - db_password: (your MySQL password)\n";
    $content .= "   - Update base_url if needed: http://localhost/phpnuxbill\n\n";
    
    $content .= "5. Permissions:\n";
    $content .= "   - Set write permissions for system/uploads folder\n";
    $content .= "   - On Linux: chmod -R 777 system/uploads\n";
    $content .= "   - On Windows: Ensure IIS/Apache has write access\n\n";
    
    $content .= "6. Final Steps:\n";
    $content .= "   - Clear browser cache\n";
    $content .= "   - Access: http://localhost/phpnuxbill\n";
    $content .= "   - Test admin login and functionality\n\n";
    
    $content .= "TROUBLESHOOTING:\n";
    $content .= "----------------\n";
    $content .= "- Database connection error: Check config.php credentials\n";
    $content .= "- 404 errors: Ensure .htaccess is present and mod_rewrite enabled\n";
    $content .= "- Permission errors: Check folder write permissions\n";
    $content .= "- Blank pages: Check PHP error logs and requirements\n\n";
    
    $content .= "SUPPORT:\n";
    $content .= "--------\n";
    $content .= "For additional support, check the PHPNuxBill documentation\n";
    $content .= "or visit the project repository.\n";
    
    return $content;
}
?>
