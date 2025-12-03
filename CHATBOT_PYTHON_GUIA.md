# 🚀 Chatbot IA - Guía de Configuración y Uso

## 📋 Descripción General

El sistema de chatbot está completamente implementado en **Python con FastAPI**, conectado directamente a la base de datos MySQL, y se muestra como un **widget flotante** en todas las interfaces principales.

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────┐
│              Frontend PHP (Bodeshop)                │
│  Dashboard, Productos, Ventas, Empleados           │
│                                                     │
│  ┌────────────────────────────────────────┐        │
│  │   Widget Flotante de Chatbot (JS)     │        │
│  │   - assets/js/chatbot-widget.js       │        │
│  │   - assets/css/chatbot-widget.css     │        │
│  └──────────────┬─────────────────────────┘        │
└─────────────────┼──────────────────────────────────┘
                  │ HTTP POST /api/chat
                  ▼
┌─────────────────────────────────────────────────────┐
│         FastAPI Python Backend                      │
│         (python_api/)                               │
│                                                     │
│  ┌────────────────────────────────────────┐        │
│  │   main.py (Servidor FastAPI)          │        │
│  │   - Endpoint: /api/chat                │        │
│  │   - Health check                       │        │
│  └────────────┬───────────────────────────┘        │
│               │                                     │
│  ┌────────────▼───────────────────────────┐        │
│  │   services/chatbot_service.py         │        │
│  │   - Procesamiento IA con OpenAI       │        │
│  │   - Construcción de contexto          │        │
│  └────────────┬───────────────────────────┘        │
│               │                                     │
│  ┌────────────▼───────────────────────────┐        │
│  │   services/database_service.py        │        │
│  │   - Consultas MySQL                    │        │
│  │   - Productos, ventas, empleados       │        │
│  └───────────────────────────────────────┘        │
└─────────────────┼──────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────┐
│         Base de Datos MySQL (bodeshop_db)           │
│  - products, sales, users, sale_details            │
└─────────────────────────────────────────────────────┘
```

---

## ⚙️ Configuración Inicial

### 1. Verificar Python

```powershell
python --version
# Debe ser Python 3.8 o superior
```

### 2. Instalar Dependencias Python

```powershell
cd C:\xampp\htdocs\bodeshop\python_api
pip install -r requirements.txt
```

**Dependencias instaladas:**
- fastapi
- uvicorn
- mysql-connector-python
- openai
- python-dotenv
- pydantic

### 3. Configurar Variables de Entorno

Editar el archivo `python_api/.env`:

```env
# OpenAI Configuration
OPENAI_API_KEY=tu_clave_de_openai_aqui
OPENAI_MODEL=gpt-4o-mini

# Database Configuration
DB_HOST=localhost
DB_NAME=bodeshop_db
DB_USER=root
DB_PASSWORD=
DB_PORT=3306

# Server Configuration
API_HOST=127.0.0.1
API_PORT=8000
```

**⚠️ IMPORTANTE:** Obtén tu API Key en https://platform.openai.com/api-keys

### 4. Iniciar el Servidor Python

**Opción A - Usando el script:**
```powershell
cd C:\xampp\htdocs\bodeshop\python_api
.\start.bat
```

**Opción B - Manualmente:**
```powershell
cd C:\xampp\htdocs\bodeshop\python_api
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

El servidor estará disponible en:
- 🌐 http://127.0.0.1:8000 (Inicio)
- 📊 http://127.0.0.1:8000/docs (Documentación interactiva)
- ❤️ http://127.0.0.1:8000/health (Health check)
- 💬 http://127.0.0.1:8000/api/chat (Endpoint del chatbot)

---

## 🎨 Widget Flotante

### Características

- **Botón flotante** en la esquina inferior derecha
- **Abre/cierra** con un clic
- **Diseño moderno** con gradientes y animaciones
- **Responsive** para móvil y escritorio
- **Historial de conversación** en la misma sesión
- **Indicador de escritura** mientras la IA procesa

### Ubicaciones

El widget está disponible en:
✅ Dashboard Admin
✅ Lista de Productos
✅ Lista de Empleados
✅ Lista de Ventas
✅ Dashboard de Empleados

### Archivos del Widget

```
assets/
  css/
    chatbot-widget.css    ← Estilos del widget flotante
  js/
    chatbot-widget.js     ← Lógica del cliente
```

---

## 🧠 Capacidades del Chatbot

El asistente IA puede ayudarte con:

### 📦 Inventario y Productos
- "¿Cuántos productos tengo en stock?"
- "¿Qué productos tienen stock bajo?"
- "Muéstrame los productos más caros"
- "¿Tengo producto X disponible?"

### 💰 Ventas y Estadísticas
- "¿Cuántas ventas hice hoy?"
- "¿Cuál es el total de ingresos del mes?"
- "¿Cuáles son los productos más vendidos?"
- "¿Cuánto es el promedio de venta?"

### 👥 Empleados
- "¿Cuántos empleados tengo?"
- "¿Quién es el mejor vendedor?"
- "Muéstrame el rendimiento de los empleados"

### 📊 Análisis General
- "Dame un resumen del negocio"
- "¿Cómo va el negocio este mes?"
- "¿Qué productos debo reponer?"

---

## 🔧 Solución de Problemas

### Error: "Error de conexión. Verifica que el servidor Python esté ejecutándose"

**Causa:** El servidor FastAPI no está corriendo.

**Solución:**
```powershell
cd C:\xampp\htdocs\bodeshop\python_api
.\start.bat
```

Verifica que veas el mensaje: `Uvicorn running on http://127.0.0.1:8000`

---

### Error: "You didn't provide an API key"

**Causa:** La variable `OPENAI_API_KEY` no está configurada en `.env`

**Solución:**
1. Abre `python_api/.env`
2. Reemplaza `tu_clave_de_openai_aqui` con tu API key real
3. Reinicia el servidor Python

---

### Error: "Error de conexión a base de datos"

**Causa:** La configuración de MySQL es incorrecta o MySQL no está corriendo.

**Solución:**
1. Verifica que XAMPP/MySQL esté corriendo
2. Comprueba las credenciales en `python_api/.env`:
   ```env
   DB_HOST=localhost
   DB_NAME=bodeshop_db
   DB_USER=root
   DB_PASSWORD=
   ```
3. Verifica que la base de datos exista:
   ```sql
   SHOW DATABASES LIKE 'bodeshop_db';
   ```

---

### El widget no aparece en la interfaz

**Causa:** Los archivos CSS/JS del widget no están cargados.

**Solución:**
1. Verifica que existan:
   - `assets/css/chatbot-widget.css`
   - `assets/js/chatbot-widget.js`
2. Abre la consola del navegador (F12) y busca errores 404
3. Limpia la caché del navegador (Ctrl+F5)

---

## 📡 API Endpoints

### POST `/api/chat`

Envía un mensaje al chatbot y recibe una respuesta.

**Request:**
```json
{
  "message": "¿Cuántos productos tengo en stock?",
  "session_id": "opcional_session_123"
}
```

**Response (Éxito):**
```json
{
  "success": true,
  "response": "Tienes 45 productos en stock actualmente...",
  "context_used": {
    "products": [...],
    "business_overview": {...}
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "Descripción del error"
}
```

### GET `/health`

Verifica el estado del servicio.

**Response:**
```json
{
  "status": "healthy",
  "database": "connected",
  "openai_configured": true
}
```

---

## 🗑️ Archivos Obsoletos Eliminados

Los siguientes archivos PHP del chatbot anterior ya NO se usan:

❌ `api/chatbot.php` (reemplazado por Python API)
❌ `api/chatbot_debug.php`
❌ `controllers/ChatbotController.php`
❌ `models/ChatbotService.php`
❌ `views/admin/chatbot.php` (vista de página completa)
❌ `assets/css/chatbot.css` (antiguo)
❌ `assets/js/chatbot.js` (antiguo)
❌ `test_chatbot.php`

**Nueva implementación:**
✅ `python_api/main.py` (Servidor FastAPI)
✅ `python_api/services/chatbot_service.py`
✅ `python_api/services/database_service.py`
✅ `assets/css/chatbot-widget.css` (Widget flotante)
✅ `assets/js/chatbot-widget.js` (Widget flotante)

---

## 📝 Comandos Rápidos

### Iniciar todo el sistema

```powershell
# Terminal 1: Iniciar XAMPP (Apache + MySQL)
# Usa el panel de control de XAMPP

# Terminal 2: Iniciar servidor Python
cd C:\xampp\htdocs\bodeshop\python_api
.\start.bat

# Navegador: Abrir la aplicación
# http://localhost/bodeshop
```

### Ver logs del servidor Python

El servidor muestra logs en tiempo real en la terminal:
```
INFO:     127.0.0.1:xxxx - "POST /api/chat HTTP/1.1" 200 OK
✅ Contexto obtenido: ['products', 'business_overview']
✅ Respuesta generada exitosamente
```

### Verificar conexión a la API

```powershell
# Desde PowerShell
$body = @{ message = 'Hola' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/chat' -Method Post -Body $body -ContentType 'application/json'
```

---

## 🎯 Próximos Pasos

1. ✅ Servidor Python corriendo en http://127.0.0.1:8000
2. ✅ Widget flotante visible en todas las vistas
3. ✅ Conexión a base de datos MySQL funcionando
4. ✅ OpenAI API Key configurada
5. 🔜 Personalizar respuestas del asistente
6. 🔜 Agregar historial de conversaciones (opcional)
7. 🔜 Implementar autenticación por usuario (opcional)

---

## 📞 Soporte

Si encuentras problemas:
1. Verifica los logs del servidor Python
2. Revisa la consola del navegador (F12)
3. Comprueba el health check: http://127.0.0.1:8000/health
4. Consulta la documentación interactiva: http://127.0.0.1:8000/docs

---

## 🎉 ¡Listo!

Tu chatbot IA con Python + FastAPI + OpenAI está configurado y funcionando. El widget flotante está disponible en todas las interfaces principales del sistema.

**Enjoy! 🚀**
