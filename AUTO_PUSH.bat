@echo off
:: Auto Push Script - Commits and pushes changes to GitHub
:: Place this in your project root and run after making changes

cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ============================================
echo AUTO PUSH TO GITHUB
echo ============================================
echo.

:: Step 1: Apply database migrations
echo [0/3] Checking database migrations...
if exist "database\migrations\apply_migrations.php" (
    php "database\migrations\apply_migrations.php"
    echo.
)

:: Check if there are changes
git status --short > temp_status.txt
set /p GIT_STATUS=<temp_status.txt
del temp_status.txt

if "%GIT_STATUS%"=="" (
    echo No code changes to commit.
    goto :end
)

echo Changes detected:
git status --short
echo.

:: Add all changes
echo [1/3] Adding changes...
git add -A

:: Commit with timestamp
echo.
echo [2/3] Committing...
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c-%%a-%%b)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (set mytime=%%a:%%b)
git commit -m "auto: update on %mydate% %mytime%" --author="wilndotcom <kennethndugi@gmail.com>"

:: Push to GitHub
echo.
echo [3/3] Pushing to GitHub...
git push origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo SUCCESS! Changes pushed to GitHub
    echo ============================================
) else (
    echo.
    echo ============================================
    echo ERROR: Push failed. Trying to pull first...
    echo ============================================
    git pull origin main --no-rebase
    git push origin main
)

:end
echo.
pause
