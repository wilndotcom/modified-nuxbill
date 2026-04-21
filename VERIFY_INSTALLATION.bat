@echo off
cd /d "C:\xampp\htdocs\modified-nuxbill"

echo ============================================
echo VERIFYING INSTALLATION
echo ============================================
echo.

echo [1/5] Checking Colorful Theme CSS...
if exist "ui\ui\styles\colorful-theme.css" (
    echo   [OK] colorful-theme.css exists
) else (
    echo   [MISSING] colorful-theme.css NOT FOUND
)

echo.
echo [2/5] Checking Admin Header includes...
findstr /C:"colorful-theme.css" "ui\ui\admin\header.tpl" >nul && (
    echo   [OK] Admin header includes colorful-theme.css
) || (
    echo   [MISSING] Admin header missing CSS link
)

echo.
echo [3/5] Checking Customer Header includes...
findstr /C:"colorful-theme.css" "ui\ui\customer\header.tpl" >nul && (
    echo   [OK] Customer header includes colorful-theme.css
) || (
    echo   [MISSING] Customer header missing CSS link
)

echo.
echo [4/5] Checking Smarty Cache...
for %%f in (ui\compiled\*.php) do (
    set /a count+=1
)
if %count% GTR 0 (
    echo   [WARNING] %count% compiled templates found
    echo   Run: del /Q ui\compiled\*.php
) else (
    echo   [OK] No compiled templates (cache is clear)
)

echo.
echo [5/5] Git Status...
git status --short

echo.
echo ============================================
echo VERIFICATION COMPLETE
echo ============================================
echo.
echo If any items show [MISSING], run AUTO_PUSH.bat
echo ============================================
pause
