@echo off
REM Quick Security Setup Script for Band Cafe (Windows)

echo ========================================
echo    Band Cafe Security Setup
echo ========================================
echo.

REM Create .env file from example
if not exist .env (
    echo Creating .env file...
    copy .env.example .env >nul
    echo [OK] .env file created
    echo.
    echo [IMPORTANT] Edit .env and change the default passwords!
    echo    - DB_PASSWORD
    echo    - DB_ROOT_PASSWORD
    echo.
) else (
    echo [INFO] .env file already exists
)

REM Create logs directory
if not exist logs (
    echo Creating logs directory...
    mkdir logs
    echo [OK] Logs directory created
) else (
    echo [INFO] Logs directory already exists
)

echo.
echo ========================================
echo    Security setup complete!
echo ========================================
echo.
echo Next steps:
echo 1. Edit .env and set strong passwords
echo 2. Run: docker-compose up -d
echo 3. Test the security features
echo.
echo For more details, see SECURITY_IMPLEMENTATION.md
echo.
pause
