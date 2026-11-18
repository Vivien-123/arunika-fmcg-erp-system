@echo off
echo ============================================
echo  PT Arunika ERP - Setup Script (Laravel)
echo ============================================
echo.

REM Step 1: Create .env from template if missing
if not exist ".env" (
    if exist ".env.example" (
        echo [1/5] Creating .env from .env.example...
        copy .env.example .env >nul
    ) else (
        echo [1/5] ERROR: .env.example not found. Cannot create .env
        pause
        exit /b 1
    )
) else (
    echo [1/5] .env already exists.
)

REM Step 2: Check PHP
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP is not installed or not in PATH.
    echo         Install PHP 8.2+ from https://windows.php.net/download/
    echo         Enable extensions in php.ini: pdo_sqlite, mbstring, zip, fileinfo
    pause
    exit /b 1
)
echo [2/5] PHP found.
php -v

REM Step 3: Install Composer dependencies if vendor/ is missing
if not exist "vendor\autoload.php" (
    echo [3/5] Installing Composer dependencies...
    where composer >nul 2>&1
    if %ERRORLEVEL% neq 0 (
        echo [ERROR] Composer is not installed.
        echo         Install from https://getcomposer.org/download/
        pause
        exit /b 1
    )
    composer install --no-interaction --no-dev --optimize-autoloader
) else (
    echo [3/5] Composer dependencies already installed.
)

REM Step 4: Generate APP_KEY
echo [4/5] Generating APP_KEY...
php artisan key:generate --force

REM Step 5: Seed database to baseline
echo [5/5] Seeding database to baseline...
php artisan db:master-seed

echo.
echo ============================================
echo  Setup complete!
echo.
echo  Run: php artisan serve
echo  Open: http://localhost:8000
echo  Login: master_admin / Demo2026!
echo.
echo  Other users (password: Demo2026!):
echo    sales_user, finance_user, warehouse_mgr,
echo    purchase_user, hr_user, owner_user
echo ============================================
pause
