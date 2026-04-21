@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ==========================================
echo Syncing Local with GitHub
echo ==========================================
echo.

echo [1/6] Setting git config...
git config user.email "kennethndugi@gmail.com"
git config user.name "wilndotcom"

echo.
echo [2/6] Checking local changes...
git status --short

echo.
echo [3/6] Stashing local changes (if any)...
git stash

echo.
echo [4/6] Pulling latest from GitHub...
git pull https://github.com/wilndotcom/modified-nuxbill.git main

echo.
echo [5/6] Restoring local changes...
git stash pop

echo.
echo [6/6] Checking final status...
git status

echo.
echo ==========================================
echo Sync Complete!
echo Check output above for any issues.
echo ==========================================
pause
