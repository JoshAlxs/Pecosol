/**
 * Chatbot Widget - Cliente JavaScript
 * Conecta con la API Python de FastAPI
 */

class ChatbotWidget {
    constructor() {
        this.apiUrl = 'http://127.0.0.1:8000/api/chat';
        this.isOpen = false;
        this.sessionId = this.generateSessionId();
        this.init();
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    init() {
        this.createWidget();
        this.attachEventListeners();
        console.log('✅ Chatbot Widget inicializado');
    }

    createWidget() {
        // Crear estructura HTML del widget
        const widgetHTML = `
            <!-- Botón flotante -->
            <button class="chatbot-fab" id="chatbotFab" title="Abrir Asistente IA">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9h10v2H7v-2z"/>
                    <circle cx="8" cy="10" r="1.5"/>
                    <circle cx="16" cy="10" r="1.5"/>
                    <path d="M12 17.5c2.33 0 4.32-1.45 5.12-3.5H6.88c.8 2.05 2.79 3.5 5.12 3.5z"/>
                </svg>
            </button>

            <!-- Widget de chat -->
            <div class="chatbot-widget" id="chatbotWidget">
                <div class="chatbot-widget-header">
                    <h3>Asistente IA</h3>
                    <button class="chatbot-close-btn" id="chatbotClose">×</button>
                </div>
                
                <div class="chatbot-widget-messages" id="chatbotMessages">
                    <div class="welcome-message">
                        <h4>¡Hola! 👋 Soy tu asistente IA de Bodeshop</h4>
                        <ul>
                            <li>Consultar inventario y stock</li>
                            <li>Analizar ventas y estadísticas</li>
                            <li>Generar reportes</li>
                            <li>Responder preguntas sobre tu negocio</li>
                        </ul>
                    </div>
                    <div class="typing-indicator" id="typingIndicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                
                <div class="chatbot-widget-input">
                    <textarea 
                        id="chatbotInput" 
                        placeholder="Escribe tu pregunta aquí..."
                        rows="1"
                        maxlength="500"
                    ></textarea>
                    <button class="chatbot-send-btn" id="chatbotSend" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Insertar en el body
        const container = document.createElement('div');
        container.innerHTML = widgetHTML;
        document.body.appendChild(container);

        // Referencias a elementos
        this.fab = document.getElementById('chatbotFab');
        this.widget = document.getElementById('chatbotWidget');
        this.closeBtn = document.getElementById('chatbotClose');
        this.messagesContainer = document.getElementById('chatbotMessages');
        this.input = document.getElementById('chatbotInput');
        this.sendBtn = document.getElementById('chatbotSend');
        this.typingIndicator = document.getElementById('typingIndicator');
    }

    attachEventListeners() {
        // Abrir/cerrar widget
        this.fab.addEventListener('click', () => this.toggleWidget());
        this.closeBtn.addEventListener('click', () => this.closeWidget());

        // Input
        this.input.addEventListener('input', () => this.handleInput());
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Botón enviar
        this.sendBtn.addEventListener('click', () => this.sendMessage());

        // Auto-resize textarea
        this.input.addEventListener('input', () => {
            this.input.style.height = 'auto';
            this.input.style.height = this.input.scrollHeight + 'px';
        });
    }

    toggleWidget() {
        if (this.isOpen) {
            this.closeWidget();
        } else {
            this.openWidget();
        }
    }

    openWidget() {
        this.widget.classList.add('open');
        this.fab.classList.add('active');
        this.isOpen = true;
        this.input.focus();
    }

    closeWidget() {
        this.widget.classList.remove('open');
        this.fab.classList.remove('active');
        this.isOpen = false;
    }

    handleInput() {
        const hasText = this.input.value.trim().length > 0;
        this.sendBtn.disabled = !hasText;
    }

    async sendMessage() {
        const message = this.input.value.trim();
        if (!message) return;

        // Limpiar input
        this.input.value = '';
        this.input.style.height = 'auto';
        this.sendBtn.disabled = true;

        // Agregar mensaje del usuario
        this.addMessage(message, 'user');

        // Mostrar indicador de escritura
        this.showTyping();

        try {
            // Llamar a la API Python
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    session_id: this.sessionId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Ocultar indicador de escritura
            this.hideTyping();

            if (data.success) {
                this.addMessage(data.response, 'bot');
            } else {
                this.addMessage(data.error || 'Error al procesar tu pregunta', 'error');
            }

        } catch (error) {
            console.error('Error:', error);
            this.hideTyping();
            
            let errorMessage = 'Error de conexión. ';
            if (error.message.includes('Failed to fetch')) {
                errorMessage += 'Verifica que el servidor Python esté ejecutándose en http://127.0.0.1:8000';
            } else {
                errorMessage += error.message;
            }
            
            this.addMessage(errorMessage, 'error');
        }
    }

    addMessage(text, type = 'bot') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;

        const now = new Date();
        const time = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

        const avatar = type === 'bot' ? '🤖' : '👤';
        
        messageDiv.innerHTML = `
            <div class="chat-message-avatar">${avatar}</div>
            <div class="chat-message-content">
                ${this.formatMessage(text)}
                <div class="chat-message-time">${time}</div>
            </div>
        `;

        // Insertar antes del indicador de escritura
        this.messagesContainer.insertBefore(messageDiv, this.typingIndicator);
        
        // Scroll al final
        this.scrollToBottom();
    }

    formatMessage(text) {
        // Convertir saltos de línea a <br>
        text = text.replace(/\n/g, '<br>');
        
        // Convertir URLs a enlaces
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        text = text.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
        
        return text;
    }

    showTyping() {
        this.typingIndicator.classList.add('active');
        this.scrollToBottom();
    }

    hideTyping() {
        this.typingIndicator.classList.remove('active');
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }
}

// Inicializar el widget cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.chatbotWidget = new ChatbotWidget();
    });
} else {
    window.chatbotWidget = new ChatbotWidget();
}
