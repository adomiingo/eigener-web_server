import sqlite3
import requests
from datetime import date

# 1. Configuración de Telegram (¡Pon tus datos!)
TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg"
CHAT_ID = "5181963608"
DB_PATH = "/var/www/ubungen/kalender.db"

# 2. Obtenemos la fecha de hoy en formato YYYY-MM-DD
hoy = date.today().strftime("%Y-%m-%d")

def enviar_telegram(mensaje):
    url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"
    datos = {
        "chat_id": CHAT_ID,
        "text": mensaje,
        "parse_mode": "Markdown" # Para poder usar negritas
    }
    requests.post(url, data=datos)

# 3. Conexión a la base de datos y búsqueda
try:
    conexion = sqlite3.connect(DB_PATH)
    cursor = conexion.cursor()
    
    # Buscamos tareas de HOY que sigan "Ausstehen" (Pendientes)
    cursor.execute("SELECT betreff, fach FROM aufgaben WHERE daten = ? AND zustand = 'Ausstehen'", (hoy,))
    tareas = cursor.fetchall()
    
    # 4. Lógica de envío
    if tareas:
        texto = f"🔔 *RECORDATORIO SMR*\nTienes {len(tareas)} tarea(s) para hoy ({hoy}):\n\n"
        for tarea in tareas:
            texto += f"📚 *{tarea[1]}*: {tarea[0]}\n"
        
        enviar_telegram(texto)
        print("✅ Se han encontrado tareas y se ha enviado el aviso.")
    else:
        print("📭 No hay tareas pendientes para hoy.")

except sqlite3.Error as e:
    print(f"❌ Error al leer la base de datos: {e}")
finally:
    if 'conexion' in locals():
        conexion.close()