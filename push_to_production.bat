@echo off
echo ==========================================
echo Preparing for Production Deployment
echo ==========================================

cd /d C:\xampp\htdocs\modified-nuxbill

echo.
echo [1/5] Checking Git status...
git status --short

echo.
echo [2/5] Adding all files to git...
git add .

echo.
echo [3/5] Committing changes...
git commit -m "pre-deployment: complete SQL file, production ready"

echo.
echo [4/5] Pushing to remote...
git push origin main

echo.
echo [5/5] Done!
echo.
echo ==========================================
echo Check output above for any errors
echo ==========================================
pause
