# 🚀 GUÍA DE INICIO RÁPIDO - Chatbot Python

## ✅ Pasos para poner en marcha el chatbot

### 1️⃣ Configurar las variables de entorno

```bash
cd python_api
copy .env.example .env
```

Edita el archivo `.env` y configura:
```ini
OPENAI_API_KEY=sk-tu-clave-aqui
OPENAI_MODEL=gpt-4o-mini

DB_HOST=localhost
DB_NAME=bodeshop
DB_USER=root
DB_PASSWORD=
DB_PORT=3306
```

### 2️⃣ Instalar dependencias Python

Abre PowerShell en la carpeta `python_api`:

```powershell
# Crear entorno virtual (primera vez)
python -m venv venv

# Activar entorno virtual
.\venv\Scripts\Activate.ps1

# Instalar dependencias
pip install -r requirements.txt
```

### 3️⃣ Iniciar el servidor FastAPI

Opción A - Script automatizado (recomendado):
```bash
start.bat
```

Opción B - Manual:
```powershell
.\venv\Scripts\Activate.ps1
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

### 4️⃣ Verificar que funciona

Abre tu navegador y ve a:
- **API**: http://127.0.0.1:8000
- **Documentación interactiva**: http://127.0.0.1:8000/docs
- **Health check**: http://127.0.0.1:8000/health

Deberías ver:
```json
{
  "status": "healthy",
  "database": "connected",
  "openai_configured": true
}
```

### 5️⃣ Integrar con el frontend PHP

El archivo JavaScript ya está creado en `python_api/chatbot_frontend.js`.

**Opción A - Reemplazar el JS actual:**
Copia `chatbot_frontend.js` a `assets/js/chatbot.js` (sobrescribir).

**Opción B - Actualizar la vista chatbot.php:**
Cambia la referencia del script en `views/admin/chatbot.php`:
```html
<script src="<?php echo BASE_URL; ?>python_api/chatbot_frontend.js"></script>
```

### 6️⃣ Probar el chatbot

1. Asegúrate de que:
   - ✅ XAMPP/Apache está corriendo (puerto 80)
   - ✅ MySQL está corriendo (puerto 3306)
   - ✅ FastAPI está corriendo (puerto 8000)

2. Abre el chatbot en el navegador:
   http://localhost/bodeshop/?controller=chatbot&action=show

3. Haz una pregunta de prueba:
   - "¿Cuántos productos tengo?"
   - "¿Cuáles son las ventas del mes?"
   - "¿Qué productos tienen poco stock?"

---

## 🧪 Pruebas desde PowerShell

```powershell
# Test de conexión
Invoke-RestMethod -Uri "http://127.0.0.1:8000/health"

# Test de chat
$body = @{ message = "¿Cuántos productos hay?" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/chat" -Method Post -Body $body -ContentType "application/json"
```

---

## ❌ Solución de problemas

### "No module named 'fastapi'"
```bash
pip install -r requirements.txt
```

### "Can't connect to MySQL"
- Verifica que XAMPP/MySQL esté corriendo
- Confirma las credenciales en `.env`

### "OPENAI_API_KEY no configurada"
- Edita `.env` y agrega tu API key válida
- Reinicia el servidor FastAPI

### "Port 8000 is already in use"
```bash
# Matar proceso en puerto 8000 (Windows)
netstat -ano | findstr :8000
taskkill /PID <numero_pid> /F
```

---

## 📁 Estructura final

```
bodeshop/
├── python_api/               # ← Nuevo microservicio Python
│   ├── main.py
│   ├── services/
│   │   ├── chatbot_service.py
│   │   └── database_service.py
│   ├── requirements.txt
│   ├── .env
│   ├── start.bat
│   ├── chatbot_frontend.js
│   └── README.md
├── views/admin/chatbot.php
├── assets/js/chatbot.js      # ← Actualizar con chatbot_frontend.js
└── ...
```

---

## 🎯 Siguiente paso

Una vez que el servidor FastAPI esté corriendo y probado:

1. Actualiza el frontend para usar el nuevo JavaScript
2. Prueba el chatbot desde la interfaz web
3. Verifica que las respuestas usen datos reales de la base de datos

¡Listo! 🎉
