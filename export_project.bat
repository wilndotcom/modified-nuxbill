@echo off
echo ==================================
echo   PHPNuxBill Project Export Tool
echo ==================================

SET PROJECT_DIR=C:\xampp\htdocs\phpnuxbill
SET EXPORT_DIR=C:\xampp\htdocs\phpnuxbill_exports
SET TIMESTAMP=%date:~-4%_%date:~4,2%_%date:~7,2%_%time:~0,2%_%time:~3,2%

echo Creating export directory...
if not exist "%EXPORT_DIR%" mkdir "%EXPORT_DIR%"

echo [1/5] Checking project directory...
if not exist "%PROJECT_DIR%" (
    echo ERROR: Project directory not found: %PROJECT_DIR%
    pause
    exit /b 1
)
echo Project directory found: %PROJECT_DIR%

echo [2/5] Exporting database...
echo Please enter MySQL password when prompted...
mysqldump -u root -p phpnuxbill > "%EXPORT_DIR%\database_%TIMESTAMP%.sql"
if %errorlevel% neq 0 (
    echo ERROR: Database export failed!
    echo Please check MySQL credentials and try again.
    pause
    exit /b 1
)
echo Database exported successfully!

echo [3/5] Compressing project files...
cd C:\xampp\htdocs
if exist 7z.exe (
    7z a -t7z -mx=5 "%EXPORT_DIR%\project_files_%TIMESTAMP%.7z" phpnuxbill\
    if %errorlevel% neq 0 (
        echo ERROR: File compression failed!
        pause
        exit /b 1
    )
) else (
    echo 7-Zip not found, using built-in compression...
    powershell -command "Compress-Archive -Path 'phpnuxbill' -DestinationPath '%EXPORT_DIR%\project_files_%TIMESTAMP%.zip' -Force"
    if %errorlevel% neq 0 (
        echo ERROR: File compression failed!
        pause
        exit /b 1
    )
)
echo Project files compressed successfully!

echo [4/5] Creating deployment package...
set DEPLOY_DIR=%EXPORT_DIR%\deployment_%TIMESTAMP%
mkdir "%DEPLOY_DIR%"

copy "%EXPORT_DIR%\database_%TIMESTAMP%.sql" "%DEPLOY_DIR%\database.sql"
copy "%PROJECT_DIR%\config.php" "%DEPLOY_DIR%\config_example.php"

echo Creating installation guide...
echo PHPNuxBill Export Package > "%DEPLOY_DIR%\README.txt"
echo Export Date: %date% %time% >> "%DEPLOY_DIR%\README.txt"
echo. >> "%DEPLOY_DIR%\README.txt"
echo INSTALLATION INSTRUCTIONS: >> "%DEPLOY_DIR%\README.txt"
echo 1. Install XAMPP on new device >> "%DEPLOY_DIR%\README.txt"
echo 2. Create database 'phpnuxbill' in phpMyAdmin >> "%DEPLOY_DIR%\README.txt"
echo 3. Import database.sql into the database >> "%DEPLOY_DIR%\README.txt"
echo 4. Extract project files to C:\xampp\htdocs\phpnuxbill >> "%DEPLOY_DIR%\README.txt"
echo 5. Update config.php with new database credentials >> "%DEPLOY_DIR%\README.txt"
echo 6. Set permissions for uploads folder >> "%DEPLOY_DIR%\README.txt"
echo 7. Access http://localhost/phpnuxbill >> "%DEPLOY_DIR%\README.txt"

echo [5/5] Verifying export...
set EXPORT_SUCCESS=1

if exist "%EXPORT_DIR%\database_%TIMESTAMP%.sql" (
    echo - Database backup: OK
) else (
    echo - Database backup: FAILED
    set EXPORT_SUCCESS=0
)

if exist "%EXPORT_DIR%\project_files_%TIMESTAMP%.7z" (
    echo - Project archive: OK
) else if exist "%EXPORT_DIR%\project_files_%TIMESTAMP%.zip" (
    echo - Project archive: OK
) else (
    echo - Project archive: FAILED
    set EXPORT_SUCCESS=0
)

if %EXPORT_SUCCESS%==1 (
    echo.
    echo ==================================
    echo EXPORT COMPLETED SUCCESSFULLY!
    echo ==================================
    echo Export Location: %EXPORT_DIR%
    echo Timestamp: %TIMESTAMP%
    echo.
    echo Files Created:
    echo - database_%TIMESTAMP%.sql (Database backup)
    if exist "%EXPORT_DIR%\project_files_%TIMESTAMP%.7z" (
        echo - project_files_%TIMESTAMP%.7z (Project files)
    ) else (
        echo - project_files_%TIMESTAMP%.zip (Project files)
    )
    echo - deployment_%TIMESTAMP%\ (Deployment package)
    echo.
    echo Next Steps:
    echo 1. Copy the deployment_%TIMESTAMP% folder to new device
    echo 2. Follow the README.txt instructions
    echo.
) else (
    echo.
    echo ==================================
    echo EXPORT COMPLETED WITH ERRORS!
    echo ==================================
    echo Please check the error messages above.
)

pause
