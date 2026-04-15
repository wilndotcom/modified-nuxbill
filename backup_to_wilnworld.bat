@echo off
echo ==================================
echo  PHPNuxBill Backup to WILNWORLD(E)
echo ==================================

SET PROJECT_DIR=C:\xampp\htdocs\phpnuxbill
SET BACKUP_DIR=E:\WILNWORLD_BACKUPS
SET TIMESTAMP=%date:~-4%_%date:~4,2%_%date:~7,2%_%time:~0,2%_%time:~3,2%
SET DEPLOYMENT_NAME=PHPNuxBill_WILNWORLD_%TIMESTAMP%

echo Creating backup directory on WILNWORLD(E)...
if not exist "E:\" (
    echo ERROR: WILNWORLD(E) drive not found!
    echo Please ensure the E: drive is accessible.
    pause
    exit /b 1
)

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
if not exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%" mkdir "%BACKUP_DIR%\%DEPLOYMENT_NAME%"

echo [1/6] Verifying project directory...
if not exist "%PROJECT_DIR%" (
    echo ERROR: Project directory not found: %PROJECT_DIR%
    pause
    exit /b 1
)
echo Project directory verified: %PROJECT_DIR%

echo [2/6] Exporting database...
echo Please enter MySQL password when prompted...
mysqldump -u root -p phpnuxbill > "%BACKUP_DIR%\%DEPLOYMENT_NAME%\database.sql"
if %errorlevel% neq 0 (
    echo ERROR: Database export failed!
    echo Please check MySQL credentials and try again.
    pause
    exit /b 1
)
echo Database exported successfully!

echo [3/6] Compressing project files...
cd C:\xampp\htdocs
if exist 7z.exe (
    echo Using 7-Zip compression...
    7z a -t7z -mx=5 "%BACKUP_DIR%\%DEPLOYMENT_NAME%\project_files.7z" phpnuxbill\
    if %errorlevel% neq 0 (
        echo ERROR: File compression failed!
        pause
        exit /b 1
    )
) else (
    echo 7-Zip not found, using built-in compression...
    powershell -command "Compress-Archive -Path 'phpnuxbill' -DestinationPath '%BACKUP_DIR%\%DEPLOYMENT_NAME%\project_files.zip' -Force"
    if %errorlevel% neq 0 (
        echo ERROR: File compression failed!
        pause
        exit /b 1
    )
)
echo Project files compressed successfully!

echo [4/6] Copying configuration files...
copy "%PROJECT_DIR%\config.php" "%BACKUP_DIR%\%DEPLOYMENT_NAME%\config_example.php"
if exist "%PROJECT_DIR%\.htaccess" copy "%PROJECT_DIR%\.htaccess" "%BACKUP_DIR%\%DEPLOYMENT_NAME%\.htaccess"

echo [5/6] Creating WILNWORLD(E) deployment package...
echo Creating installation guide...

echo PHPNuxBill Backup for WILNWORLD(E) > "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo ====================================== >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo BACKUP INFORMATION: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo Source: C:\xampp\htdocs\phpnuxbill >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo Destination: E:\WILNWORLD_BACKUPS\%DEPLOYMENT_NAME% >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo Backup Date: %date% %time% >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo Timestamp: %TIMESTAMP% >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo WILNWORLD(E) DEPLOYMENT INSTRUCTIONS: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo ----------------------------------- >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo 1. PREPARE WILNWORLD(E) ENVIRONMENT: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Install XAMPP on WILNWORLD(E) system >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Ensure PHP 7.4+ and MySQL 5.7+ are installed >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Start Apache and MySQL services >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 2. DATABASE SETUP ON WILNWORLD(E): >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Open phpMyAdmin (http://localhost/phpmyadmin) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Create new database named 'phpnuxbill' >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Import database.sql from this backup package >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 3. PROJECT FILES DEPLOYMENT: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Extract project_files.7z (or .zip) to C:\xampp\htdocs\phpnuxbill >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Ensure folder structure is preserved >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Copy .htaccess to project root if needed >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 4. CONFIGURATION FOR WILNWORLD(E): >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Copy config_example.php to config.php >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Update database credentials in config.php: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo      * db_host: localhost >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo      * db_name: phpnuxbill >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo      * db_user: root (or appropriate MySQL user) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo      * db_password: (MySQL password on WILNWORLD(E)) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Update base_url if needed: http://localhost/phpnuxbill >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 5. PERMISSIONS SETUP: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Set write permissions for system/uploads folder >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - On Windows: Ensure IIS/Apache has write access >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 6. FINAL STEPS: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Clear browser cache >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Access: http://localhost/phpnuxbill >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Test admin login and all functionality >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Verify customer data and settings >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo 7. WILNWORLD(E) SPECIFIC NOTES: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - This backup includes all customer management enhancements >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Router assignment system is fully functional >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - Ticket siren notification system is included >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo    - All custom modifications are preserved >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo BACKUP VERIFICATION: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo ------------------- >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo Verify the following files are present: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - database.sql (Complete database backup) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - project_files.7z or project_files.zip (All project files) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - config_example.php (Configuration template) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - .htaccess (URL rewriting rules) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - README_WILNWORLD.txt (This installation guide) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo. >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo SUPPORT: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo ------- >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo For additional support on WILNWORLD(E) deployment: >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - Check PHPNuxBill documentation >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - Verify XAMPP installation on WILNWORLD(E) >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"
echo - Test database connectivity >> "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt"

echo [6/6] Verifying backup integrity...
set BACKUP_SUCCESS=1

if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\database.sql" (
    echo - Database backup: OK
) else (
    echo - Database backup: FAILED
    set BACKUP_SUCCESS=0
)

if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\project_files.7z" (
    echo - Project archive (7z): OK
) else if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\project_files.zip" (
    echo - Project archive (zip): OK
) else (
    echo - Project archive: FAILED
    set BACKUP_SUCCESS=0
)

if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\config_example.php" (
    echo - Configuration file: OK
) else (
    echo - Configuration file: FAILED
    set BACKUP_SUCCESS=0
)

if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\README_WILNWORLD.txt" (
    echo - Installation guide: OK
) else (
    echo - Installation guide: FAILED
    set BACKUP_SUCCESS=0
)

echo.
echo ==================================
if %BACKUP_SUCCESS%==1 (
    echo BACKUP TO WILNWORLD(E) COMPLETED!
    echo ==================================
    echo.
    echo Backup Location: %BACKUP_DIR%\%DEPLOYMENT_NAME%
    echo Drive: E:\WILNWORLD_BACKUPS
    echo Timestamp: %TIMESTAMP%
    echo Deployment Name: %DEPLOYMENT_NAME%
    echo.
    echo Files Created:
    echo - database.sql (Complete database backup)
    if exist "%BACKUP_DIR%\%DEPLOYMENT_NAME%\project_files.7z" (
        echo - project_files.7z (Compressed project files)
    ) else (
        echo - project_files.zip (Compressed project files)
    )
    echo - config_example.php (Configuration template)
    echo - README_WILNWORLD.txt (Installation guide)
    echo.
    echo Next Steps for WILNWORLD(E):
    echo 1. Copy the entire %DEPLOYMENT_NAME% folder to WILNWORLD(E) system
    echo 2. Follow README_WILNWORLD.txt instructions
    echo 3. Test all functionality after deployment
    echo.
    echo Backup Size Check:
    dir "%BACKUP_DIR%\%DEPLOYMENT_NAME%" /s
) else (
    echo BACKUP TO WILNWORLD(E) FAILED!
    echo ==================================
    echo.
    echo Please check the error messages above.
    echo Ensure E: drive is accessible and has sufficient space.
)

echo.
echo Press any key to exit...
pause > nul
