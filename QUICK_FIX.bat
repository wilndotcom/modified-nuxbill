@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ==========================================
echo Quick Fix - Pull then Push
echo ==========================================
echo.

echo [1/4] Adding your changes...
git add -A

echo.
echo [2/4] Committing your changes...
git commit -m "feat: colorful sidebar theme" --author="wilndotcom <kennethndugi@gmail.com>"

echo.
echo [3/4] Pulling remote changes and merging...
git pull https://github.com/wilndotcom/modified-nuxbill.git main --no-rebase

echo.
echo [4/4] Pushing to GitHub...
git push https://github.com/wilndotcom/modified-nuxbill.git main

echo.
echo ==========================================
echo Done!
echo ==========================================
pause
