from google import genai

# Inyectamos la clave directamente saltándonos el .env
API_KEY = "TU_CLAVE_AQUI" 

try:
    print("Conectando con los servidores de Google...")
    client_ai = genai.Client(api_key=API_KEY)
    response = client_ai.models.generate_content(
        model="gemini-2.5-flash", 
        contents="Dime hola en una sola palabra."
    )
    print("✅ ¡ÉXITO! La IA responde:", response.text)
except Exception as e:
    print("❌ ERROR EXACTO DE LA API:")
    print(e)