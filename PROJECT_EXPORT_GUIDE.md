# PHPNuxBill Project Export Guide

## **Export Checklist**

### **1. Pre-Export Preparation**
- [ ] **Backup Current Database**: Create full database backup
- [ ] **Check File Permissions**: Ensure all files are readable
- [ ] **Clear Cache**: Remove temporary files and cache
- [ ] **Document Current Setup**: Note PHP version, extensions, and configuration
- [ ] **Test Current System**: Ensure everything works before export

### **2. Files to Export**
**Core Application Files:**
```
C:\xampp\htdocs\phpnuxbill\
  - system\ (All PHP controllers, models, helpers)
  - ui\ (All templates, assets, themes)
  - system\vendor\ (Composer dependencies)
  - config.php (Database and system configuration)
  - .htaccess (URL rewriting rules)
```

**User Uploads & Data:**
```
- system\uploads\ (Customer photos, attachments, etc.)
- ui\uploads\ (UI assets and uploads)
```

**Configuration Files:**
```
- config.php (Main configuration)
- system\config.php (System settings)
- .env (If environment variables used)
```

### **3. Database Export**
**Required Tables:**
```sql
-- Core tables to export
- tbl_customers
- tbl_plans
- tbl_routers
- tbl_tickets
- tbl_user_recharges
- tbl_payment_gateway
- tbl_transactions
- tbl_admins
- tbl_settings
- tbl_widgets
- tbl_customers_fields
- tbl_ticket_attachments
```

## **Export Methods**

### **Method 1: Manual Export (Recommended)**

#### **Step 1: Database Export**
```bash
# Using MySQL Command Line
mysqldump -u username -p database_name > phpnuxbill_backup.sql

# Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database
3. Click "Export" tab
4. Choose "Custom" export method
5. Select all tables
6. Choose "SQL" format
7. Click "Go" to download
```

#### **Step 2: Files Export**
```bash
# Create compressed archive
# Option 1: Using 7-Zip (Windows)
1. Right-click on "phpnuxbill" folder
2. Select "7-Zip" > "Add to archive..."
3. Choose format: 7z or zip
4. Set compression level: Normal
5. Click "OK"

# Option 2: Using Command Line
cd C:\xampp\htdocs
7z a phpnuxbill_export.7z phpnuxbill\
```

### **Method 2: Automated Export Script**

#### **Windows Batch Script**
```batch
@echo off
echo Starting PHPNuxBill Export...

SET BACKUP_DIR=C:\xampp\htdocs\phpnuxbill_export
SET PROJECT_DIR=C:\xampp\htdocs\phpnuxbill
SET TIMESTAMP=%date:~-4%_%date:~4,2%_%date:~7,2%_%time:~0,2%_%time:~3,2%

echo Creating backup directory...
mkdir "%BACKUP_DIR%" 2>nul

echo Exporting database...
mysqldump -u root -p phpnuxbill > "%BACKUP_DIR%\database_%TIMESTAMP%.sql"

echo Compressing files...
cd C:\xampp\htdocs
7z a "%BACKUP_DIR%\phpnuxbill_files_%TIMESTAMP%.7z" phpnuxbill\

echo Creating deployment package...
mkdir "%BACKUP_DIR%\deployment_package"
copy "%BACKUP_DIR%\database_%TIMESTAMP%.sql" "%BACKUP_DIR%\deployment_package\database.sql"
copy "C:\xampp\htdocs\phpnuxbill\config.php" "%BACKUP_DIR%\deployment_package\config_example.php"

echo Export completed!
echo Backup location: %BACKUP_DIR%
pause
```

#### **PHP Export Script**
```php
<?php
// export_project.php
$project_dir = 'C:\xampp\htdocs\phpnuxbill';
$backup_dir = 'C:\xampp\htdocs\phpnuxbill_export';
$timestamp = date('Y_m_d_H_i_s');

// Create backup directory
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Database export
$db_config = include $project_dir . '/config.php';
$command = "mysqldump -u {$db_config['db_user']} -p{$db_config['db_password']} {$db_config['db_name']} > {$backup_dir}/database_{$timestamp}.sql";
exec($command);

// Files export
$zip = new ZipArchive();
$zip_file = "{$backup_dir}/phpnuxbill_files_{$timestamp}.zip";

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
}

echo "Export completed! Files saved to: $backup_dir\n";
?>
```

## **Import/Deployment Guide**

### **1. New Device Setup**
**Required Software:**
- XAMPP/WAMP/MAMP (Web server with PHP & MySQL)
- PHP 7.4+ (Recommended PHP 8.0+)
- MySQL 5.7+ or MariaDB 10.2+
- Required PHP extensions:
  - PDO, PDO_MySQL
  - mbstring
  - curl
  - gd
  - json
  - xml
  - zip

### **2. Deployment Steps**

#### **Step 1: Environment Setup**
```bash
# Install XAMPP
# Download and install XAMPP from https://www.apachefriends.org/

# Start Apache and MySQL services
# Open XAMPP Control Panel
# Start Apache and MySQL modules
```

#### **Step 2: Database Setup**
```bash
# Create database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" in left sidebar
3. Enter database name: phpnuxbill
4. Click "Create"

# Import database
1. Select the phpnuxbill database
2. Click "Import" tab
3. Choose your database backup file (.sql)
4. Click "Go" to import
```

#### **Step 3: Project Files**
```bash
# Extract project files
1. Extract the compressed archive to C:\xampp\htdocs\
2. Ensure folder name is "phpnuxbill"
3. Check file permissions (should be readable by web server)

# Update configuration
1. Open C:\xampp\htdocs\phpnuxbill\config.php
2. Update database credentials:
   - $config['db_host'] = 'localhost';
   - $config['db_name'] = 'phpnuxbill';
   - $config['db_user'] = 'root';
   - $config['db_password'] = ''; // Update with correct password
3. Update site URL if needed:
   - $config['base_url'] = 'http://localhost/phpnuxbill';
```

#### **Step 4: Final Setup**
```bash
# Set permissions (Linux/Mac)
chmod -R 755 C:\xampp\htdocs\phpnuxbill
chmod -R 777 C:\xampp\htdocs\phpnuxbill\system\uploads

# Clear cache
Delete contents of:
- C:\xampp\htdocs\phpnuxbill\system\cache\
- C:\xampp\htdocs\phpnuxbill\system\temp\

# Test installation
Open browser: http://localhost/phpnuxbill
```

## **Troubleshooting**

### **Common Issues**

#### **1. Database Connection Error**
```php
// Error: "Connection failed"
// Solution: Check config.php database settings
$config['db_host'] = 'localhost';
$config['db_name'] = 'phpnuxbill';
$config['db_user'] = 'root';
$config['db_password'] = ''; // Update with correct password
```

#### **2. File Permission Issues**
```bash
# Linux/Mac: Set correct permissions
sudo chown -R www-data:www-data /path/to/phpnuxbill
sudo chmod -R 755 /path/to/phpnuxbill
sudo chmod -R 777 /path/to/phpnuxbill/system/uploads

# Windows: Check folder permissions
1. Right-click phpnuxbill folder
2. Properties > Security
3. Add "IIS_IUSRS" or "Apache" user with full control
```

#### **3. PHP Extensions Missing**
```bash
# Check required extensions
Create phpinfo.php with: <?php phpinfo(); ?>
# Access: http://localhost/phpinfo.php
# Look for: PDO, mbstring, curl, gd, json, xml, zip

# Enable extensions in php.ini
extension=pdo_mysql
extension=mbstring
extension=curl
extension=gd
extension=json
extension=xml
extension=zip
```

#### **4. URL Rewriting Issues**
```apache
# Check .htaccess file exists in project root
# Ensure Apache mod_rewrite is enabled
# In httpd.conf or .htaccess:
LoadModule rewrite_module modules/mod_rewrite.so

<Directory "C:/xampp/htdocs/phpnuxbill">
    AllowOverride All
    Require all granted
</Directory>
```

### **Post-Import Checklist**
- [ ] **Test admin login**: Access admin panel
- [ ] **Check customer data**: Verify customer records
- [ ] **Test ticket system**: Create and view tickets
- [ ] **Check uploads**: Verify file uploads work
- [ ] **Test notifications**: Check email/SMS functionality
- [ ] **Verify URLs**: Ensure all links work correctly
- [ ] **Check performance**: Monitor loading times

## **Quick Export Script**

### **One-Click Export (Windows)**
```batch
@echo off
echo ==================================
echo   PHPNuxBill Quick Export Tool
echo ==================================

SET PROJECT_DIR=C:\xampp\htdocs\phpnuxbill
SET EXPORT_DIR=C:\xampp\htdocs\phpnuxbill_exports
SET TIMESTAMP=%date:~-4%_%date:~4,2%_%date:~7,2%_%time:~0,2%_%time:~3,2%

echo Creating export directory...
mkdir "%EXPORT_DIR%" 2>nul

echo [1/4] Exporting database...
mysqldump -u root -p phpnuxbill > "%EXPORT_DIR%\database_%TIMESTAMP%.sql"
if %errorlevel% neq 0 (
    echo ERROR: Database export failed!
    pause
    exit /b 1
)

echo [2/4] Compressing project files...
cd C:\xampp\htdocs
7z a -t7z -mx=5 "%EXPORT_DIR%\project_files_%TIMESTAMP%.7z" phpnuxbill\

echo [3/4] Creating deployment package...
mkdir "%EXPORT_DIR%\deployment_%TIMESTAMP%"
copy "%EXPORT_DIR%\database_%TIMESTAMP%.sql" "%EXPORT_DIR%\deployment_%TIMESTAMP%\database.sql"
copy "C:\xampp\htdocs\phpnuxbill\config.php" "%EXPORT_DIR%\deployment_%TIMESTAMP%\config_example.php"

echo [4/4] Creating installation guide...
echo PHPNuxBill Export Completed > "%EXPORT_DIR%\deployment_%TIMESTAMP%\README.txt"
echo Timestamp: %TIMESTAMP% >> "%EXPORT_DIR%\deployment_%TIMESTAMP%\README.txt"
echo Files: project_files_%TIMESTAMP%.7z >> "%EXPORT_DIR%\deployment_%TIMESTAMP%\README.txt"
echo Database: database.sql >> "%EXPORT_DIR%\deployment_%TIMESTAMP%\README.txt"

echo ==================================
echo Export completed successfully!
echo Location: %EXPORT_DIR%
echo Timestamp: %TIMESTAMP%
echo ==================================
pause
```

## **Next Steps**

1. **Choose Export Method**: Manual or automated script
2. **Run Export**: Follow chosen method
3. **Verify Export**: Check all files are included
4. **Transfer to New Device**: Copy export files
5. **Deploy on New Device**: Follow deployment guide
6. **Test Installation**: Verify everything works

This comprehensive guide will help you successfully export and deploy your PHPNuxBill project to another device!
<tool_call>EmptyFile</arg_key>
<arg_value>false
