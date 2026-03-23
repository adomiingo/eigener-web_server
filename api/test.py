import os
from dotenv import load_dotenv
from google import genai

# Cargar las llaves secretas
load_dotenv()
api_key = os.getenv("GEMINI_KEY")

if not api_key:
    print("❌ Error: No se ha encontrado GEMINI_KEY en el archivo .env")
else:
    try:
        client = genai.Client(api_key=api_key)
        print("✅ Llave válida. Modelos disponibles:")
        for m in client.models.list():
            if "flash" in m.name or "pro" in m.name:
                print("-", m.name)
    except Exception as e:
        print("❌ Error de la API:", e)
