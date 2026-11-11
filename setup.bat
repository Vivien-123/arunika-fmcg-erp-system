@echo off
echo ============================================
echo  PT Arunika ERP - Setup Script
echo ============================================
echo.

REM Create SQLite file if missing
if not exist "database\database.sqlite" (
    echo [1/4] Creating database.sqlite...
    type nul > "database\database.sqlite"
) else (
    echo [1/4] database.sqlite already exists.
)

echo [2/4] Running composer dump-autoload...
composer dump-autoload -q

echo [3/4] Running migrations...
php artisan migrate:fresh --force

echo [4/4] Running seeder...
php artisan db:seed --class=FmcgSeeder --force 2>nul || php seed_data.php

echo.
echo ============================================
echo  Setup complete! Run: php artisan serve
echo  Login: sales_user / Demo2026!
echo ============================================
pause
