@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ==========================================
echo Fixing Git Divergence and Pushing
echo ==========================================
echo.

echo [1/6] Setting git config...
git config user.email "kennethndugi@gmail.com"
git config user.name "wilndotcom"

echo.
echo [2/6] Stashing local changes...
git stash

echo.
echo [3/6] Pulling remote changes...
git pull https://github.com/wilndotcom/modified-nuxbill.git main --rebase

echo.
echo [4/6] Restoring local changes...
git stash pop

echo.
echo [5/6] Adding and committing changes...
git add -A
git commit -m "feat: add colorful sidebar theme with beautiful menu design for admin and customer portals" --author="wilndotcom <kennethndugi@gmail.com>"

echo.
echo [6/6] Pushing to GitHub...
git push https://github.com/wilndotcom/modified-nuxbill.git main

echo.
echo ==========================================
if %ERRORLEVEL% EQU 0 (
    echo SUCCESS! Changes pushed to GitHub
) else (
    echo ERROR: Push failed. Check output above.
)
echo ==========================================
pause
