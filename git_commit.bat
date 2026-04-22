@echo off
cd /d C:\xampp\htdocs\modified-nuxbill
git add database/complete_install.sql
git add push_to_production.bat
git add git_commit.bat
git commit -m "feat: add complete SQL installation file for production deployment"
git push origin main
echo.
echo Done! Press any key to exit.
pause > nul
