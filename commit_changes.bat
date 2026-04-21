@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"
echo Adding all changes...
git add -A
echo.
echo Committing changes...
git commit -m "feat: add colorful sidebar theme with beautiful menu design for admin and customer portals"
echo.
echo Pushing to GitHub...
git push origin main
echo.
echo Done!
pause
