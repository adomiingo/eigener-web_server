import os
from dotenv import load_dotenv
from google import genai

load_dotenv()
clave = os.getenv("GEMINI_KEY")

print("📡 Conectando con los satélites de Google...")
try:
    client = genai.Client(api_key=clave)
    response = client.models.generate_content(
        model="gemini-2.5-flash", 
        contents="Responde únicamente con estas dos palabras: 'En línea'."
    )
    print("✅ Respuesta de J.A.R.V.I.S:", response.text.strip())
except Exception as e:
    print("❌ Google dice:", e)
