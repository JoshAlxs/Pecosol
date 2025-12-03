"""
Script de prueba rápida para verificar:
1. Conexión a MySQL
2. Lectura de datos de la base de datos
3. Variables de entorno
"""
import os
import sys
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

print("=" * 60)
print("🔍 VERIFICACIÓN DEL SISTEMA")
print("=" * 60)

# 1. Verificar Python
print(f"\n✅ Python version: {sys.version}")

# 2. Verificar variables de entorno
print("\n📝 Variables de entorno:")
print(f"   OPENAI_API_KEY: {'✅ Configurada' if os.getenv('OPENAI_API_KEY') and os.getenv('OPENAI_API_KEY') != 'tu_api_key_aqui' else '❌ NO configurada'}")
print(f"   OPENAI_MODEL: {os.getenv('OPENAI_MODEL', 'No definido')}")
print(f"   DB_HOST: {os.getenv('DB_HOST', 'No definido')}")
print(f"   DB_NAME: {os.getenv('DB_NAME', 'No definido')}")
print(f"   DB_USER: {os.getenv('DB_USER', 'No definido')}")

# 3. Verificar conexión a MySQL
print("\n🔌 Verificando conexión a MySQL...")
try:
    import mysql.connector
    from mysql.connector import Error
    
    config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'database': os.getenv('DB_NAME', 'bodeshop'),
        'user': os.getenv('DB_USER', 'root'),
        'password': os.getenv('DB_PASSWORD', ''),
        'port': int(os.getenv('DB_PORT', '3306'))
    }
    
    conn = mysql.connector.connect(**config)
    
    if conn.is_connected():
        print("   ✅ Conexión a MySQL exitosa!")
        
        cursor = conn.cursor(dictionary=True)
        
        # Probar queries
        print("\n📊 Datos de prueba:")
        
        # Contar productos
        cursor.execute("SELECT COUNT(*) as total FROM products")
        result = cursor.fetchone()
        print(f"   📦 Productos totales: {result['total']}")
        
        # Contar ventas
        cursor.execute("SELECT COUNT(*) as total FROM sales")
        result = cursor.fetchone()
        print(f"   💰 Ventas totales: {result['total']}")
        
        # Contar empleados
        cursor.execute("SELECT COUNT(*) as total FROM users WHERE role = 'employee'")
        result = cursor.fetchone()
        print(f"   👥 Empleados: {result['total']}")
        
        cursor.close()
        conn.close()
        
        print("\n✅ Base de datos funcionando correctamente!")
    
except Error as e:
    print(f"   ❌ Error de conexión: {e}")
    print("\n💡 Soluciones:")
    print("   - Verifica que XAMPP/MySQL esté corriendo")
    print("   - Confirma las credenciales en el archivo .env")
    print("   - Asegúrate de que el puerto 3306 no esté bloqueado")

except ImportError:
    print("   ❌ mysql-connector-python no está instalado")
    print("   Ejecuta: pip install mysql-connector-python")

# 4. Verificar OpenAI
print("\n🤖 Verificando OpenAI...")
try:
    import openai
    api_key = os.getenv('OPENAI_API_KEY')
    
    if not api_key or api_key == 'tu_api_key_aqui':
        print("   ⚠️ API Key no configurada")
        print("\n💡 Para configurar:")
        print("   1. Ve a https://platform.openai.com/api-keys")
        print("   2. Crea una nueva API key")
        print("   3. Edita el archivo .env y reemplaza 'tu_api_key_aqui' con tu clave")
        print("   4. Reinicia este script")
    else:
        print(f"   ✅ API Key configurada (longitud: {len(api_key)} caracteres)")
        print(f"   📝 Modelo: {os.getenv('OPENAI_MODEL', 'gpt-4o-mini')}")

except ImportError:
    print("   ❌ openai no está instalado")
    print("   Ejecuta: pip install openai")

print("\n" + "=" * 60)
print("🎯 SIGUIENTE PASO:")
print("=" * 60)

if not os.getenv('OPENAI_API_KEY') or os.getenv('OPENAI_API_KEY') == 'tu_api_key_aqui':
    print("\n1. Edita el archivo .env")
    print("2. Reemplaza 'tu_api_key_aqui' con tu API key real de OpenAI")
    print("3. Ejecuta nuevamente: python test_setup.py")
    print("4. Luego inicia el servidor: python -m uvicorn main:app --reload")
else:
    print("\n✅ Todo está listo!")
    print("Inicia el servidor con: python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload")
    print("O usa: start.bat")

print("\n")
