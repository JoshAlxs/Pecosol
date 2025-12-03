# Guía de Instalación - Chatbot IA con OpenAI

## Pasos de Instalación

### 1. Obtener tu API Key de OpenAI

1. Ve a https://platform.openai.com/api-keys
2. Inicia sesión con tu cuenta de OpenAI (o crea una si no tienes)
3. Haz clic en "Create new secret key"
4. Copia la clave generada
5. **IMPORTANTE:** Guarda esta clave en un lugar seguro. No la compartas públicamente.

### 2. Instalar la librería de OpenAI vía Composer

Abre PowerShell/CMD en el directorio `c:\xampp\htdocs\bodeshop` y ejecuta:

```powershell
composer install
```

Esto instalará la librería `openai-php/client` que ya agregué al `composer.json`.

### 3. Configurar tu API Key

Tienes dos opciones:

#### Opción A: Variable de entorno (RECOMENDADO para producción)

1. Abre `config/openai.php`
2. Busca la línea: `define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: 'tu-api-key-aqui');`
3. Configura una variable de entorno en Windows:
   - Panel de control → Variables de entorno
   - Nueva variable: `OPENAI_API_KEY` = tu-api-key
   - Reinicia el servidor

#### Opción B: Directa en el archivo (SOLO para desarrollo local)

1. Abre `config/openai.php`
2. Reemplaza `'tu-api-key-aqui'` con tu API Key real:
   ```php
   define('OPENAI_API_KEY', 'sk-xxxxxxxxxxxxx');
   ```

### 4. Acceder al Chatbot

1. Abre tu navegador y ve a: `http://localhost/bodeshop/`
2. Inicia sesión como administrador
3. En el menú superior, verás el botón **🤖 Chatbot IA**
4. ¡Haz clic y comienza a chatear!

## Características

- ✅ Acceso en tiempo real a productos, inventario y ventas
- ✅ Análisis inteligente de estadísticas
- ✅ Respuestas contextualizadas basadas en tu base de datos
- ✅ Interfaz moderna y responsiva
- ✅ Historial de chat guardado en el navegador
- ✅ Modelo: GPT-4o Mini (rápido y económico)

## Estructura de Archivos Agregados

```
bodeshop/
├── config/
│   └── openai.php                    # Configuración de OpenAI
├── models/
│   └── ChatbotService.php            # Servicio de consultas a BD
├── controllers/
│   └── ChatbotController.php         # Controlador del chatbot
├── api/
│   └── chatbot.php                   # Endpoint API
├── views/admin/
│   └── chatbot.php                   # Interfaz del chatbot
├── assets/
│   ├── css/
│   │   └── chatbot.css               # Estilos del chatbot
│   └── js/
│       └── chatbot.js                # Lógica del frontend
└── composer.json                     # (Actualizado con openai-php/client)
```

## Cómo Funciona

1. **Usuario escribe un mensaje** en la interfaz del chatbot
2. **Frontend envía** el mensaje al endpoint `api/chatbot.php`
3. **ChatbotController** consulta la base de datos con `ChatbotService`
4. **Se construye un prompt** con contexto de productos, ventas, empleados
5. **OpenAI API** procesa el prompt y devuelve una respuesta
6. **La respuesta se muestra** en el chat en tiempo real

## Preguntas de Ejemplo

- "¿Cuántos productos tengo en stock?"
- "¿Cuál fue el total de ventas de hoy?"
- "¿Qué productos tienen bajo stock?"
- "Analiza las ventas del mes"
- "¿Cuáles son los productos más caros?"
- "Dame un resumen de empleados"

## Solución de Problemas

### Error: "OPENAI_API_KEY no está configurada"
- Verifica que hayas reemplazado `'tu-api-key-aqui'` en `config/openai.php`
- O configura la variable de entorno correctamente

### Error: "Método no permitido" (405)
- Verifica que estés haciendo una solicitud POST al endpoint
- Comprueba que el archivo `api/chatbot.php` exista

### Error: "Error de conexión"
- Verifica tu conexión a internet
- Comprueba que tu API Key sea válida
- Verifica los límites de uso de tu cuenta OpenAI

### El chatbot no responde
- Abre la consola del navegador (F12)
- Verifica los errores en la pestaña "Network"
- Revisa los logs del servidor

## Costos Estimados

Con GPT-4o Mini (modelo económico):
- Aproximadamente $0.00015 por 1K tokens de entrada
- Aproximadamente $0.0006 por 1K tokens de salida

Para una pequeña empresa, esto suele ser muy económico (menos de $1/mes).

## Seguridad

⚠️ **IMPORTANTE:**
- Nunca compartas tu API Key públicamente
- No la commits en un repositorio Git
- Usa variables de entorno en producción
- Implementa autenticación para el endpoint del chatbot
- Valida y sanitiza todas las entradas del usuario

## Próximas Mejoras (Opcional)

- Agregar autenticación de usuario al endpoint
- Guardar historial de conversaciones en BD
- Implementar rate limiting
- Agregar tipos de modelos selectables (GPT-3.5, GPT-4, etc.)
- Crear reportes generados por IA
- Integrar con sistemas de tickets/soporte

## Soporte

Si tienes problemas:
1. Verifica la documentación de OpenAI: https://platform.openai.com/docs
2. Revisa los logs del servidor
3. Prueba desde el navegador: F12 → Network tab

¡Listo! Tu chatbot está funcionando. 🎉
