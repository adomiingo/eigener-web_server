import os
from dotenv import load_dotenv
from google import genai

# 1. Cargar el archivo .env
load_dotenv("/var/www/html/api/.env")
API_KEY = os.getenv("GEMINI_KEY")

# Chivato de seguridad (sin mostrar la clave entera)
if API_KEY:
    print(f"🔍 Llave detectada en .env. Empieza por: {API_KEY[:5]}... (Longitud: {len(API_KEY)} caracteres)")
else:
    print("❌ ERROR: Python no encuentra la variable GEMINI_KEY en el archivo .env")

# 2. Prueba de conexión
try:
    print("🛰️ Conectando con los servidores de Google...")
    client_ai = genai.Client(api_key=API_KEY)
    response = client_ai.models.generate_content(
        model="gemini-2.5-flash", 
        contents="Dime hola en una sola palabra."
    )
    print("✅ ¡ÉXITO! La IA responde:", response.text.strip())
except Exception as e:
    print("\n❌ ERROR EXACTO DE LA API:")
    print(e)