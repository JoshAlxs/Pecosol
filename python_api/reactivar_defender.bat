@echo off
REM Script para reactivar Windows Defender PUA Protection
REM EJECUTAR COMO ADMINISTRADOR después de terminar tu trabajo

echo ========================================
echo   Reactivando Windows Defender
echo ========================================
echo.

REM Verificar si se está ejecutando como administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Este script debe ejecutarse como Administrador
    pause
    exit /b 1
)

REM Reactivar PUA Protection
echo Reactivando PUA Protection...
powershell -Command "Set-MpPreference -PUAProtection Enabled"

if %errorLevel% equ 0 (
    echo OK - PUA Protection reactivada
) else (
    echo ERROR - No se pudo reactivar
)

echo.
echo Windows Defender restaurado a su configuración de seguridad.
echo.

pause
