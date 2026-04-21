@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ==========================================
echo Pushing to GitHub - wilndotcom
echo ==========================================
echo.

echo [1/4] Setting git config...
git config user.email "kennethndugi@gmail.com"
git config user.name "wilndotcom"

echo.
echo [2/4] Adding all files...
git add -A

echo.
echo [3/4] Committing changes...
git commit -m "feat: add colorful sidebar theme with beautiful menu design for admin and customer portals" --author="wilndotcom <kennethndugi@gmail.com>"

echo.
echo [4/4] Pushing to GitHub...
git push https://github.com/wilndotcom/modified-nuxbill.git main

echo.
echo ==========================================
echo Done! Check https://github.com/wilndotcom/modified-nuxbill
echo ==========================================
pause
