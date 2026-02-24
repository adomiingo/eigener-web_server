import requests

# 1. Configuración (¡Pon tus datos aquí!)
TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg"
CHAT_ID = "5181963608"
MENSAJE = "Bot Online, enhorabuena"

# 2. La llamada a la API de Telegram
url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"
datos = {
    "chat_id": CHAT_ID,
    "text": MENSAJE
}

# 3. Ejecución
try:
    respuesta = requests.post(url, data=datos)
    if respuesta.status_code == 200:
        print("Message sent, check ur phone")
    else:
        print(f"❌ Error del servidor de Telegram: {respuesta.text}")
except Exception as e:
    print(f"❌ Error de conexión: {e}")