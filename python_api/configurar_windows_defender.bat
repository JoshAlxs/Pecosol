@echo off
REM Script para agregar exclusión de Windows Defender y desactivar PUA Protection
REM EJECUTAR COMO ADMINISTRADOR (clic derecho -> Ejecutar como administrador)

echo ========================================
echo   Configurando Windows Defender
echo   para permitir Python y pydantic
echo ========================================
echo.

REM Verificar si se está ejecutando como administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Este script debe ejecutarse como Administrador
    echo.
    echo Haz clic derecho en este archivo y selecciona "Ejecutar como administrador"
    pause
    exit /b 1
)

echo Ejecutando con permisos de Administrador... OK
echo.

REM Agregar exclusión para site-packages de Python
echo [1/3] Agregando exclusión para site-packages de Python...
powershell -Command "Add-MpPreference -ExclusionPath 'C:\Users\ALEXIS\AppData\Local\Programs\Python\Python311\Lib\site-packages'"
if %errorLevel% equ 0 (
    echo OK - Exclusión agregada
) else (
    echo ADVERTENCIA - No se pudo agregar la exclusión
)
echo.

REM Agregar exclusión para el proyecto
echo [2/3] Agregando exclusión para el proyecto bodeshop...
powershell -Command "Add-MpPreference -ExclusionPath 'C:\xampp\htdocs\bodeshop\python_api'"
if %errorLevel% equ 0 (
    echo OK - Exclusión agregada
) else (
    echo ADVERTENCIA - No se pudo agregar la exclusión
)
echo.

REM Desactivar PUA Protection temporalmente (opcional)
echo [3/3] Desactivando PUA Protection temporalmente...
powershell -Command "Set-MpPreference -PUAProtection Disabled"
if %errorLevel% equ 0 (
    echo OK - PUA Protection desactivada
) else (
    echo ADVERTENCIA - No se pudo desactivar PUA Protection
)
echo.

echo ========================================
echo   Configuración completada
echo ========================================
echo.
echo Las DLLs de Python ahora deberían funcionar correctamente.
echo.
echo IMPORTANTE: Después de terminar tu trabajo, puedes reactivar
echo la protección ejecutando: reactivar_defender.bat
echo.

pause
