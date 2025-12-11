"""
Servicio principal del chatbot con integración Google Gemini
"""
import os
import asyncio
import google.generativeai as genai
from typing import Dict, Optional, Tuple
import logging
import json

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class ChatbotService:
    """Lógica principal del chatbot con IA de Google Gemini"""
    
    def __init__(self, db_service):
        self.db_service = db_service
        self.api_key = os.getenv("GEMINI_API_KEY")
        
        if not self.api_key:
            logger.warning("⚠️ GEMINI_API_KEY no configurada")
            self.client = None
        else:
            logger.info("✅ Google Gemini API key configurada")
            genai.configure(api_key=self.api_key)
            model_name = os.getenv("GEMINI_MODEL", "gemini-1.5-flash")
            self.client = genai.GenerativeModel(model_name)
        
        self.model = os.getenv("GEMINI_MODEL", "gemini-1.5-flash")
    
    async def get_context_from_db(self, message: str) -> Dict:
        """
        Analizar la pregunta y obtener contexto relevante de la base de datos
        """
        context = {}
        message_lower = message.lower()
        
        try:
            # Si pregunta sobre productos o inventario
            if any(word in message_lower for word in ['producto', 'inventario', 'stock', 'precio', 'disponible']):
                products = await self.db_service.get_products_info(limit=30)
                low_stock = await self.db_service.get_low_stock_products()
                context['products'] = products[:10]  # Limitar para no saturar el prompt
                context['low_stock_products'] = low_stock
                context['total_products'] = len(products)
            
            # Si pregunta sobre ventas
            if any(word in message_lower for word in ['venta', 'vender', 'vendido', 'ingreso', 'ganancia']):
                sales_stats = await self.db_service.get_sales_statistics()
                recent_sales = await self.db_service.get_recent_sales(limit=10)
                context['sales_statistics'] = sales_stats
                context['recent_sales'] = recent_sales[:5]
                
                # Si pregunta específicamente por ventas de hoy/día
                if any(word in message_lower for word in ['hoy', 'día', 'dia', 'actual', 'diaria', 'diario']):
                    today_sales = await self.db_service.get_today_sales()
                    context['today_sales'] = today_sales
                    logger.info(f"📅 Ventas de hoy: {today_sales['total_sales']} ventas, ${today_sales['total_revenue']:.2f}")
            
            # Si pregunta sobre empleados
            if any(word in message_lower for word in ['empleado', 'vendedor', 'personal', 'trabajador']):
                employees = await self.db_service.get_employees_info()
                context['employees'] = employees
                
                # Si pregunta por ventas de empleados hoy
                if any(word in message_lower for word in ['hoy', 'día', 'dia', 'actual']):
                    employees_today = await self.db_service.get_employees_today_sales()
                    context['employees_today'] = employees_today
                    logger.info(f"👥 Empleados con ventas hoy: {len([e for e in employees_today if e['sales_today'] > 0])}")
            
            # Si no hay contexto específico, dar overview general
            if not context:
                stats = await self.db_service.get_business_stats()
                context['business_overview'] = stats
            
            logger.info(f"📊 Contexto obtenido: {list(context.keys())}")
            return context
        
        except Exception as e:
            logger.error(f"Error obteniendo contexto: {e}")
            return {"error": str(e)}
    
    def build_system_prompt(self, context: Dict) -> str:
        """
        Construir el prompt del sistema con el contexto de la base de datos
        """
        base_prompt = """Eres un asistente IA para Pecosol, una tienda de productos. 
Tu rol es ayudar al administrador con información sobre:
- Inventario y productos
- Ventas y estadísticas (incluyendo ventas del día actual)
- Empleados y su rendimiento
- Análisis de negocio en tiempo real

IMPORTANTE: Tienes acceso COMPLETO a la base de datos en tiempo real. 
Los datos que se te proporcionan son ACTUALES y puedes responder con confianza sobre:
- Ventas realizadas HOY
- Rendimiento de empleados HOY
- Estado actual del inventario
- Cualquier estadística del negocio

ACLARACIÓN CRÍTICA sobre los datos:
- Cuando veas "employee_name" o "Vendedor:", se refiere al EMPLEADO/VENDEDOR que realizó la venta (el trabajador de la tienda)
- El sistema NO registra datos del cliente final que compró
- Ejemplo: "Venta #10: $399.98 - Vendedor: Ale Peres" significa que el EMPLEADO Ale Peres fue quien atendió y registró esa venta

Responde de manera concisa, profesional y útil. Usa los datos proporcionados para dar respuestas precisas y directas.
Cuando menciones ventas, deja claro que mencionas al VENDEDOR/EMPLEADO que la realizó, no al cliente.
"""
        
        # Agregar contexto de productos
        if 'products' in context:
            products_info = "\n".join([
                f"- {p['name']}: ${p['price']}, Stock: {p['stock']} ({p.get('stock_status', 'N/A')})"
                for p in context['products'][:10]
            ])
            base_prompt += f"\n\n📦 PRODUCTOS DISPONIBLES:\n{products_info}"
        
        if 'low_stock_products' in context and context['low_stock_products']:
            low_stock_info = ", ".join([p['name'] for p in context['low_stock_products'][:5]])
            base_prompt += f"\n\n⚠️ PRODUCTOS CON STOCK BAJO: {low_stock_info}"
        
        # Agregar estadísticas de ventas
        if 'sales_statistics' in context:
            stats = context['sales_statistics']
            base_prompt += f"""

📊 ESTADÍSTICAS DE VENTAS:
- Total de ventas: {stats.get('total_sales', 0)}
- Ingresos totales: ${stats.get('total_revenue', 0):.2f}
- Promedio por venta: ${stats.get('average_sale', 0):.2f}
- Ventas últimos 30 días: {stats.get('recent_sales_30d', 0)}
- Ingresos últimos 30 días: ${stats.get('recent_revenue_30d', 0):.2f}
"""
            
            if 'top_products' in stats and stats['top_products']:
                top_info = "\n".join([
                    f"  {i+1}. {p['name']}: {p['total_quantity']} unidades (${p['total_sales']:.2f})"
                    for i, p in enumerate(stats['top_products'][:5])
                ])
                base_prompt += f"\n🏆 TOP PRODUCTOS MÁS VENDIDOS:\n{top_info}"
        
        # Agregar información de ventas del día
        if 'today_sales' in context:
            today = context['today_sales']
            base_prompt += f"""\n\n📅 VENTAS DE HOY ({context.get('current_date', 'hoy')}):
- Total de ventas: {today['total_sales']}
- Ingresos: ${today['total_revenue']:.2f}
- Promedio por venta: ${today['average_sale']:.2f}
"""
            if today['sales_details']:
                sales_info = "\n".join([
                    f"  • Venta #{s['id']}: ${s['total']:.2f} - Vendedor: {s['employee_name']} - Producto: {s['product_name']} ({s['quantity']} unidades)"
                    for s in today['sales_details'][:10]
                ])
                base_prompt += f"\nDetalles de ventas (cada línea muestra: ID de venta, monto, VENDEDOR que realizó la venta, producto y cantidad):\n{sales_info}"
        
        # Agregar información de empleados
        if 'employees' in context:
            employees_info = "\n".join([
                f"- {e['full_name']}: {e['total_sales']} ventas totales, ${e['total_revenue']:.2f} en ingresos"
                for e in context['employees'][:10]
            ])
            base_prompt += f"\n\n👥 EMPLEADOS (rendimiento total):\n{employees_info}"
        
        # Agregar ventas de empleados del día
        if 'employees_today' in context:
            emp_today_info = "\n".join([
                f"- {e['full_name']}: {e['sales_today']} ventas hoy, ${e['revenue_today']:.2f}"
                for e in context['employees_today'][:10]
            ])
            base_prompt += f"\n\n👥 VENTAS DE EMPLEADOS HOY:\n{emp_today_info}"
        
        # Overview general
        if 'business_overview' in context:
            overview = context['business_overview']
            base_prompt += f"""

📈 RESUMEN DEL NEGOCIO:
- Productos activos: {overview.get('products_count', 0)}
- Empleados: {overview.get('employees_count', 0)}
- Productos con stock bajo: {overview.get('low_stock_count', 0)}
"""
        
        return base_prompt
    
    async def call_gemini(self, messages: list) -> str:
        """
        Hacer llamada a la API de Google Gemini
        """
        if not self.client:
            raise Exception("GEMINI_API_KEY no está configurada. Obtén una gratis en https://aistudio.google.com/app/apikey")
        
        try:
            logger.info(f"🤖 Llamando a Google Gemini ({self.model})...")
            
            # Convertir formato de mensajes de OpenAI a Gemini
            prompt_parts = []
            for msg in messages:
                role = msg.get("role", "user")
                content = msg.get("content", "")
                
                if role == "system":
                    prompt_parts.append(f"INSTRUCCIONES DEL SISTEMA:\n{content}\n")
                elif role == "user":
                    prompt_parts.append(f"USUARIO: {content}")
                elif role == "assistant":
                    prompt_parts.append(f"ASISTENTE: {content}")
            
            full_prompt = "\n\n".join(prompt_parts)
            
            # Ejecutar la llamada en un thread para no bloquear
            response = await asyncio.to_thread(
                self.client.generate_content,
                full_prompt,
                generation_config={
                    "temperature": 0.7,
                    "max_output_tokens": 800,
                    "top_p": 0.9,
                }
            )
            
            answer = response.text.strip()
            logger.info(f"✅ Respuesta recibida de Gemini ({len(answer)} caracteres)")
            
            return answer
        
        except Exception as e:
            error_msg = str(e)
            logger.error(f"❌ Error llamando a Gemini: {error_msg}")
            
            if "API_KEY_INVALID" in error_msg or "invalid" in error_msg.lower():
                raise Exception("API Key inválida. Obtén una gratis en https://aistudio.google.com/app/apikey")
            elif "quota" in error_msg.lower() or "limit" in error_msg.lower():
                raise Exception("Límite de uso alcanzado. Espera un momento e intenta de nuevo.")
            else:
                raise Exception(f"Error de Gemini: {error_msg}")
    
    async def process_message(
        self, 
        message: str, 
        user_id: Optional[int] = None,
        session_id: Optional[str] = None
    ) -> Tuple[str, Dict]:
        """
        Procesar mensaje del usuario y generar respuesta
        
        Returns:
            Tuple[str, Dict]: (respuesta, contexto_usado)
        """
        try:
            # 1. Obtener contexto de la base de datos
            context = await self.get_context_from_db(message)
            
            # 2. Construir el prompt del sistema con el contexto
            system_prompt = self.build_system_prompt(context)
            
            # 3. Preparar mensajes para OpenAI
            messages = [
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": message}
            ]
            
            # 4. Obtener respuesta de Gemini
            response = await self.call_gemini(messages)
            
            logger.info(f"✅ Respuesta generada exitosamente")
            
            return response, context
        
        except Exception as e:
            logger.error(f"❌ Error procesando mensaje: {e}")
            raise  # Re-lanzar la excepción original sin modificarla
