/**
 * Chatbot JavaScript - Integrado con FastAPI Python Backend
 * Actualizado para usar el microservicio Python en puerto 8000
 */

// URL del API Python (FastAPI)
const PYTHON_API_URL = 'http://127.0.0.1:8000/api/chat';

// Referencias DOM
let chatMessages, userInput, sendButton, statusIndicator;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    chatMessages = document.getElementById('chat-messages');
    userInput = document.getElementById('user-input');
    sendButton = document.getElementById('send-button');
    statusIndicator = document.getElementById('status-indicator');
    
    // Event listeners
    sendButton?.addEventListener('click', sendMessage);
    userInput?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Verificar estado del servidor Python al cargar
    checkPythonServerStatus();
    
    // Mensaje de bienvenida
    addBotMessage("¡Hola! 👋 Soy tu asistente IA de Pecosol. Puedo ayudarte con información sobre tu inventario, ventas y empleados en tiempo real.");
});

/**
 * Verificar si el servidor Python está corriendo
 */
async function checkPythonServerStatus() {
    try {
        const response = await fetch('http://127.0.0.1:8000/health', {
            method: 'GET',
            mode: 'cors'
        });
        
        if (response.ok) {
            const data = await response.json();
            updateStatusIndicator('online', 'Conectado a IA');
            
            if (!data.openai_configured) {
                addSystemMessage('⚠️ OpenAI API Key no configurada. Configura OPENAI_API_KEY en el archivo .env del servidor Python.');
            }
            
            if (data.database !== 'connected') {
                addSystemMessage('⚠️ Base de datos desconectada. Verifica la configuración en .env');
            }
        } else {
            updateStatusIndicator('offline', 'Servidor Python no disponible');
            addSystemMessage('❌ El servidor FastAPI no está respondiendo. Ejecuta start.bat en la carpeta python_api/');
        }
    } catch (error) {
        updateStatusIndicator('offline', 'Error de conexión');
        addSystemMessage('❌ No se puede conectar al servidor Python. Asegúrate de que FastAPI esté corriendo en el puerto 8000. Ejecuta: python_api/start.bat');
        console.error('Error verificando servidor:', error);
    }
}

/**
 * Actualizar indicador de estado
 */
function updateStatusIndicator(status, message) {
    if (!statusIndicator) return;
    
    statusIndicator.className = `status-indicator status-${status}`;
    statusIndicator.textContent = message;
    statusIndicator.title = message;
}

/**
 * Enviar mensaje del usuario
 */
async function sendMessage() {
    const message = userInput?.value?.trim();
    
    if (!message) return;
    
    // Agregar mensaje del usuario al chat
    addUserMessage(message);
    
    // Limpiar input
    userInput.value = '';
    
    // Deshabilitar botón mientras procesa
    if (sendButton) sendButton.disabled = true;
    
    // Mostrar indicador de escritura
    const typingId = showTypingIndicator();
    
    try {
        // Llamar al API Python
        const response = await fetch(PYTHON_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            mode: 'cors',
            body: JSON.stringify({
                message: message,
                user_id: null, // Opcional: puedes pasar el ID del usuario desde PHP
                session_id: generateSessionId()
            })
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Remover indicador de escritura
        removeTypingIndicator(typingId);
        
        // Procesar respuesta
        if (data.success) {
            addBotMessage(data.response);
            
            // Log opcional del contexto usado (para debug)
            if (data.context_used) {
                console.log('📊 Contexto usado:', data.context_used);
            }
        } else {
            addErrorMessage(data.error || 'Error desconocido al procesar tu pregunta');
        }
        
    } catch (error) {
        removeTypingIndicator(typingId);
        console.error('Error:', error);
        
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            addErrorMessage('Error de conexión. Asegúrate de que el servidor FastAPI esté corriendo (python_api/start.bat)');
            updateStatusIndicator('offline', 'Sin conexión');
        } else {
            addErrorMessage('Error al comunicarse con el servidor: ' + error.message);
        }
    } finally {
        // Rehabilitar botón
        if (sendButton) sendButton.disabled = false;
        userInput?.focus();
    }
}

/**
 * Agregar mensaje del usuario
 */
function addUserMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message user-message';
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">${escapeHtml(text)}</div>
            <div class="message-time">${getCurrentTime()}</div>
        </div>
    `;
    chatMessages?.appendChild(messageDiv);
    scrollToBottom();
}

/**
 * Agregar mensaje del bot
 */
function addBotMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message bot-message';
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">${formatBotMessage(text)}</div>
            <div class="message-time">${getCurrentTime()}</div>
        </div>
    `;
    chatMessages?.appendChild(messageDiv);
    scrollToBottom();
}

/**
 * Agregar mensaje de error
 */
function addErrorMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message error-message';
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">❌ ${escapeHtml(text)}</div>
            <div class="message-time">${getCurrentTime()}</div>
        </div>
    `;
    chatMessages?.appendChild(messageDiv);
    scrollToBottom();
}

/**
 * Agregar mensaje del sistema
 */
function addSystemMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message system-message';
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">${text}</div>
        </div>
    `;
    chatMessages?.appendChild(messageDiv);
    scrollToBottom();
}

/**
 * Mostrar indicador de escritura
 */
function showTypingIndicator() {
    const typingId = 'typing-' + Date.now();
    const typingDiv = document.createElement('div');
    typingDiv.id = typingId;
    typingDiv.className = 'message bot-message typing-indicator';
    typingDiv.innerHTML = `
        <div class="message-content">
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    chatMessages?.appendChild(typingDiv);
    scrollToBottom();
    return typingId;
}

/**
 * Remover indicador de escritura
 */
function removeTypingIndicator(typingId) {
    const typingDiv = document.getElementById(typingId);
    typingDiv?.remove();
}

/**
 * Formatear mensaje del bot (mantener saltos de línea, listas, etc.)
 */
function formatBotMessage(text) {
    // Convertir saltos de línea a <br>
    text = escapeHtml(text);
    text = text.replace(/\n/g, '<br>');
    
    // Convertir listas con guiones
    text = text.replace(/^- (.+)$/gm, '• $1');
    
    // Convertir números con punto
    text = text.replace(/^(\d+)\. (.+)$/gm, '<strong>$1.</strong> $2');
    
    // Resaltar cantidades monetarias
    text = text.replace(/\$(\d+(?:,\d{3})*(?:\.\d{2})?)/g, '<strong style="color: #00fff0;">$$$1</strong>');
    
    return text;
}

/**
 * Escapar HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Scroll al final del chat
 */
function scrollToBottom() {
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

/**
 * Obtener hora actual formateada
 */
function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

/**
 * Generar ID de sesión (para tracking opcional)
 */
function generateSessionId() {
    if (!sessionStorage.getItem('chatSessionId')) {
        sessionStorage.setItem('chatSessionId', 'session-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9));
    }
    return sessionStorage.getItem('chatSessionId');
}

// Estilos adicionales para el indicador de estado y mensajes
const styles = `
.status-indicator {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.status-online {
    background: rgba(0, 255, 0, 0.1);
    color: #00ff00;
    border: 1px solid rgba(0, 255, 0, 0.3);
}

.status-offline {
    background: rgba(255, 100, 100, 0.1);
    color: #ff6464;
    border: 1px solid rgba(255, 100, 100, 0.3);
}

.error-message .message-content {
    background: rgba(255, 100, 100, 0.1);
    border-left: 3px solid #ff6464;
}

.system-message {
    text-align: center;
    margin: 10px 0;
}

.system-message .message-content {
    background: rgba(100, 100, 100, 0.1);
    padding: 8px 16px;
    border-radius: 8px;
    display: inline-block;
    font-size: 0.9rem;
    color: #aaa;
}

.typing-indicator .typing-dots {
    display: flex;
    gap: 4px;
    padding: 10px;
}

.typing-indicator .typing-dots span {
    width: 8px;
    height: 8px;
    background: #00fff0;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator .typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator .typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { opacity: 0.3; }
    30% { opacity: 1; }
}
`;

// Inyectar estilos
const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);
