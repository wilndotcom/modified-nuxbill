@echo off
:: Create a new database migration file

cd /d "C:\xampp\htdocs\modified-nuxbill\database\migrations"

echo ============================================
echo CREATE NEW DATABASE MIGRATION
echo ============================================
echo.

set /p description="Enter migration description (use_underscores): "

if "%description%"=="" (
    echo Error: Description is required!
    pause
    exit /b 1
)

:: Generate timestamp
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c%%a%%b)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (set mytime=%%a%%b)
set timestamp=%mydate%_%mytime%

:: Create filename
set filename=%timestamp%_%description%.sql

echo.
echo Creating: %filename%
echo.

:: Create file with template
echo -- Migration: %description% > %filename%
echo -- Created: %date% %time% >> %filename%
echo -- Author: wilndotcom >> %filename%
echo. >> %filename%
echo -- Add your SQL below: >> %filename%
echo. >> %filename%

echo ============================================
echo Migration file created: %filename%
echo.
echo Next steps:
echo 1. Open the file and add your SQL
echo 2. Run: php apply_migrations.php
echo 3. Run: AUTO_PUSH.bat to push to GitHub
echo ============================================
pause
