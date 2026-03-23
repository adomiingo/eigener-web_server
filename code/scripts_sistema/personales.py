import sqlite3
import requests
import time
from datetime import date

TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg"
CHAT_ID = "5181963608"
DB_PATH = "/var/www/ubungen/kalender.db"
hoy = date.today().strftime("%Y-%m-%d")

def enviar_telegram(mensaje):
    url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"
    requests.post(url, data={"chat_id": CHAT_ID, "text": mensaje, "parse_mode": "Markdown"})

try:
    conexion = sqlite3.connect(DB_PATH)
    cursor = cursor = conexion.cursor()
    
    # Filtra solo categoría 'Personal'
    cursor.execute("SELECT betreff, daten FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Personal' ORDER BY daten ASC")
    tareas = cursor.fetchall()
    
    if tareas:
        for tarea in tareas:
            betreff, daten = tarea
            fecha_formato = f"{daten[8:10]}-{daten[5:7]}-{daten[0:4]}"
            
            mensaje = f"🏠 *PERSONAL*\n🔹 {betreff}\n📅 Fecha: {fecha_formato}"
            enviar_telegram(mensaje)
            time.sleep(0.3)
        print(f"✅ Enviadas {len(tareas)} notificaciones personales.")

except sqlite3.Error as e:
    print(f"❌ Error de BD: {e}")
finally:
    if 'conexion' in locals(): conexion.close()