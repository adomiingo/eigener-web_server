from google import genai
import getpass

# Te pedirá la clave en la terminal al ejecutar el script
API_KEY = getpass.getpass("Pega tu nueva API Key (no se verá en pantalla mientras escribes por seguridad): ")

try:
    print("🛰️ Conectando a los servidores...")
    client_ai = genai.Client(api_key=API_KEY.strip())
    response = client_ai.models.generate_content(
        model="gemini-2.5-flash", 
        contents="Dime hola en una sola palabra."
    )
    print("✅ ¡ÉXITO! La IA responde:", response.text.strip())
except Exception as e:
    print("\n❌ ERROR EXACTO:")
    print(e)