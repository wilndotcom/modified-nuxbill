<?php
/**
 * PHPNuxBill Backup to WILNWORLD(E)
 * Direct backup script for WILNWORLD(E) deployment
 */

// Configuration
$project_dir = 'C:\xampp\htdocs\phpnuxbill';
$backup_dir = 'E:\WILNWORLD_BACKUPS';
$timestamp = date('Y_m_d_H_i_s');
$deployment_name = 'PHPNuxBill_WILNWORLD_' . $timestamp;
$db_name = 'phpnuxbill';

echo "PHPNuxBill Backup to WILNWORLD(E)\n";
echo "=================================\n\n";

// Check if WILNWORLD(E) drive is accessible
if (!is_dir('E:\\')) {
    echo "ERROR: WILNWORLD(E) drive not accessible!\n";
    echo "Please ensure the E: drive is available.\n";
    exit(1);
}

// Create backup directory
$full_backup_path = $backup_dir . '\\' . $deployment_name;
if (!mkdir($full_backup_path, 0777, true)) {
    echo "ERROR: Failed to create backup directory!\n";
    echo "Path: $full_backup_path\n";
    exit(1);
}

echo "Backup directory created: $full_backup_path\n\n";

// Database export
echo "[1/4] Exporting database...\n";
try {
    $config = include $project_dir . '\\config.php';
    $db_host = $config['db_host'] ?? 'localhost';
    $db_user = $config['db_user'] ?? 'root';
    $db_pass = $config['db_password'] ?? '';
    
    $db_file = $full_backup_path . '\\database.sql';
    
    // Use PDO to connect and export
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES FROM `$db_name`");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sql_content = "-- PHPNuxBill Database Backup\n";
    $sql_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- Database: $db_name\n\n";
    
    foreach ($tables as $table) {
        $sql_content .= "-- Table: $table\n";
        
        // Get table structure
        $stmt = $pdo->query("SHOW CREATE TABLE `$db_name`.`$table`");
        $create_table = $stmt->fetch(PDO::FETCH_NUM);
        $sql_content .= $create_table[1] . ";\n\n";
        
        // Get table data
        $stmt = $pdo->query("SELECT * FROM `$db_name`.`$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $sql_content .= "-- Data for table: $table\n";
            foreach ($rows as $row) {
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return 'NULL';
                    if ($value === '') return "''";
                    return $pdo->quote($value);
                }, $row);
                
                $sql_content .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql_content .= "\n";
        }
    }
    
    if (file_put_contents($db_file, $sql_content)) {
        echo "Database exported successfully!\n";
        echo "File: database.sql (" . number_format(filesize($db_file)) . " bytes)\n";
    } else {
        echo "ERROR: Failed to save database backup!\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "ERROR: Database export failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Files export
echo "\n[2/4] Compressing project files...\n";
try {
    $zip_file = $full_backup_path . '\\project_files.zip';
    
    if (class_exists('ZipArchive')) {
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
            echo "Project files compressed successfully!\n";
            echo "File: project_files.zip (" . number_format(filesize($zip_file)) . " bytes)\n";
        } else {
            echo "ERROR: Failed to create ZIP archive!\n";
            exit(1);
        }
    } else {
        echo "ERROR: ZipArchive class not available!\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "ERROR: Files export failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Copy configuration files
echo "\n[3/4] Copying configuration files...\n";
try {
    // Copy config.php
    if (copy($project_dir . '\\config.php', $full_backup_path . '\\config_example.php')) {
        echo "Configuration file copied: config_example.php\n";
    } else {
        echo "WARNING: Failed to copy config.php\n";
    }
    
    // Copy .htaccess if exists
    if (file_exists($project_dir . '\\.htaccess')) {
        if (copy($project_dir . '\\.htaccess', $full_backup_path . '\\.htaccess')) {
            echo "HTAccess file copied: .htaccess\n";
        }
    }
    
} catch (Exception $e) {
    echo "WARNING: Configuration copy failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Create installation guide
echo "\n[4/4] Creating WILNWORLD(E) installation guide...\n";
$readme_content = "PHPNuxBill Backup for WILNWORLD(E)\n";
$readme_content .= "=====================================\n\n";
$readme_content .= "BACKUP INFORMATION:\n";
$readme_content .= "Source: C:\\xampp\\htdocs\\phpnuxbill\n";
$readme_content .= "Destination: E:\\WILNWORLD_BACKUPS\\$deployment_name\n";
$readme_content .= "Backup Date: " . date('Y-m-d H:i:s') . "\n";
$readme_content .= "Timestamp: $timestamp\n\n";

$readme_content .= "WILNWORLD(E) DEPLOYMENT INSTRUCTIONS:\n";
$readme_content .= "-----------------------------------\n\n";

$readme_content .= "1. PREPARE WILNWORLD(E) ENVIRONMENT:\n";
$readme_content .= "   - Install XAMPP on WILNWORLD(E) system\n";
$readme_content .= "   - Ensure PHP 7.4+ and MySQL 5.7+ are installed\n";
$readme_content .= "   - Start Apache and MySQL services\n\n";

$readme_content .= "2. DATABASE SETUP ON WILNWORLD(E):\n";
$readme_content .= "   - Open phpMyAdmin (http://localhost/phpmyadmin)\n";
$readme_content .= "   - Create new database named 'phpnuxbill'\n";
$readme_content .= "   - Import database.sql from this backup package\n\n";

$readme_content .= "3. PROJECT FILES DEPLOYMENT:\n";
$readme_content .= "   - Extract project_files.zip to C:\\xampp\\htdocs\\phpnuxbill\n";
$readme_content .= "   - Ensure folder structure is preserved\n";
$readme_content .= "   - Copy .htaccess to project root if needed\n\n";

$readme_content .= "4. CONFIGURATION FOR WILNWORLD(E):\n";
$readme_content .= "   - Copy config_example.php to config.php\n";
$readme_content .= "   - Update database credentials in config.php:\n";
$readme_content .= "     * db_host: localhost\n";
$readme_content .= "     * db_name: phpnuxbill\n";
$readme_content .= "     * db_user: root (or appropriate MySQL user)\n";
$readme_content .= "     * db_password: (MySQL password on WILNWORLD(E))\n";
$readme_content .= "   - Update base_url if needed: http://localhost/phpnuxbill\n\n";

$readme_content .= "5. PERMISSIONS SETUP:\n";
$readme_content .= "   - Set write permissions for system/uploads folder\n";
$readme_content .= "   - On Windows: Ensure IIS/Apache has write access\n\n";

$readme_content .= "6. FINAL STEPS:\n";
$readme_content .= "   - Clear browser cache\n";
$readme_content .= "   - Access: http://localhost/phpnuxbill\n";
$readme_content .= "   - Test admin login and all functionality\n";
$readme_content .= "   - Verify customer data and settings\n\n";

$readme_content .= "7. WILNWORLD(E) SPECIFIC NOTES:\n";
$readme_content .= "   - This backup includes all customer management enhancements\n";
$readme_content .= "   - Router assignment system is fully functional\n";
$readme_content .= "   - Ticket siren notification system is included\n";
$readme_content .= "   - All custom modifications are preserved\n\n";

$readme_content .= "BACKUP VERIFICATION:\n";
$readme_content .= "-------------------\n";
$readme_content .= "Verify the following files are present:\n";
$readme_content .= "- database.sql (Complete database backup)\n";
$readme_content .= "- project_files.zip (All project files)\n";
$readme_content .= "- config_example.php (Configuration template)\n";
$readme_content .= "- .htaccess (URL rewriting rules)\n";
$readme_content .= "- README_WILNWORLD.txt (This installation guide)\n\n";

$readme_content .= "SUPPORT:\n";
$readme_content .= "-------\n";
$readme_content .= "For additional support on WILNWORLD(E) deployment:\n";
$readme_content .= "- Check PHPNuxBill documentation\n";
$readme_content .= "- Verify XAMPP installation on WILNWORLD(E)\n";
$readme_content .= "- Test database connectivity\n";

if (file_put_contents($full_backup_path . '\\README_WILNWORLD.txt', $readme_content)) {
    echo "Installation guide created: README_WILNWORLD.txt\n";
} else {
    echo "WARNING: Failed to create installation guide\n";
}

// Verify backup
echo "\nBACKUP VERIFICATION:\n";
echo "==================\n";

$files_to_check = [
    'database.sql' => 'Database backup',
    'project_files.zip' => 'Project archive',
    'config_example.php' => 'Configuration template',
    'README_WILNWORLD.txt' => 'Installation guide'
];

$backup_success = true;
foreach ($files_to_check as $file => $description) {
    $file_path = $full_backup_path . '\\' . $file;
    if (file_exists($file_path)) {
        $size = number_format(filesize($file_path));
        echo "  - $description: OK ($size bytes)\n";
    } else {
        echo "  - $description: FAILED\n";
        $backup_success = false;
    }
}

// Summary
echo "\n=================================\n";
if ($backup_success) {
    echo "BACKUP TO WILNWORLD(E) COMPLETED!\n";
    echo "=================================\n\n";
    
    echo "Backup Location: $full_backup_path\n";
    echo "Drive: E:\\WILNWORLD_BACKUPS\n";
    echo "Deployment Name: $deployment_name\n";
    echo "Timestamp: $timestamp\n\n";
    
    echo "Files Created:\n";
    foreach ($files_to_check as $file => $description) {
        $file_path = $full_backup_path . '\\' . $file;
        if (file_exists($file_path)) {
            $size = number_format(filesize($file_path));
            echo "  - $file ($size bytes)\n";
        }
    }
    
    echo "\nNext Steps for WILNWORLD(E):\n";
    echo "1. Copy the entire $deployment_name folder to WILNWORLD(E) system\n";
    echo "2. Follow README_WILNWORLD.txt instructions\n";
    echo "3. Test all functionality after deployment\n";
    
    // Calculate total backup size
    $total_size = 0;
    foreach (new DirectoryIterator($full_backup_path) as $file) {
        if ($file->isFile()) {
            $total_size += $file->getSize();
        }
    }
    
    echo "\nTotal Backup Size: " . number_format($total_size) . " bytes";
    echo " (" . round($total_size / 1024 / 1024, 2) . " MB)\n";
    
} else {
    echo "BACKUP TO WILNWORLD(E) FAILED!\n";
    echo "=================================\n\n";
    echo "Please check the error messages above.\n";
}

echo "\nBackup process completed.\n";
?>
