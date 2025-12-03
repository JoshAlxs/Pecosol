@echo off
cd /d "%~dp0"
echo ====================================
echo   Bodeshop - FastAPI Chatbot Server
echo ====================================
echo.
echo Directorio actual: %CD%
echo.

REM Verificar archivo .env
if not exist ".env" (
    echo [ADVERTENCIA] No existe archivo .env
    echo Copia .env.example a .env y configura tus variables
    echo.
    copy .env.example .env
    echo Archivo .env creado. EDITA el archivo y agrega tu OPENAI_API_KEY
    echo.
    pause
)

REM Iniciar el servidor
echo [*] Iniciando servidor FastAPI en http://127.0.0.1:8000
echo [*] Documentacion disponible en http://127.0.0.1:8000/docs
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload

pause
