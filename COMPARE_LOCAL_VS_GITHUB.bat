@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ============================================
echo COMPARING LOCAL vs GITHUB
echo ============================================
echo.

echo [1/5] Setting git config...
git config user.email "kennethndugi@gmail.com"
git config user.name "wilndotcom"

echo.
echo [2/5] Fetching latest from GitHub...
git fetch https://github.com/wilndotcom/modified-nuxbill.git main

echo.
echo [3/5] Comparing commits...
echo --- LOCAL (HEAD) vs GITHUB (origin/main) ---
git log HEAD..FETCH_HEAD --oneline 2>nul || echo No new commits on GitHub
git log FETCH_HEAD..HEAD --oneline 2>nul || echo No new commits locally

echo.
echo [4/5] Checking file differences...
git diff --name-only HEAD FETCH_HEAD 2>nul || echo Files are identical or no diff available

echo.
echo [5/5] Checking local uncommitted changes...
git status --short

echo.
echo ============================================
echo SUMMARY:
echo ============================================
echo If you see files listed above, they are different.
echo If empty, local and GitHub are in sync.
echo.
echo To sync: Run SYNC_WITH_GITHUB.bat
echo ============================================
pause
