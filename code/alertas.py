import sqlite3
import requests
import time
from datetime import date

# 1. Configuración de Telegram
TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg"
CHAT_ID = "5181963608"
DB_PATH = "/var/www/ubungen/kalender.db"

hoy = date.today().strftime("%Y-%m-%d")

def enviar_telegram(mensaje):
    url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"
    datos = {"chat_id": CHAT_ID, "text": mensaje, "parse_mode": "Markdown"}
    requests.post(url, data=datos)

try:
    conexion = sqlite3.connect(DB_PATH)
    cursor = conexion.cursor()
    
    # Buscamos SOLAMENTE las tareas PENDIENTES ('Ausstehen')
    cursor.execute("SELECT betreff, fach, daten FROM aufgaben WHERE zustand = 'Ausstehen' ORDER BY daten ASC")
    tareas = cursor.fetchall()
    
    if not tareas:
        print("📭 No hay tareas pendientes.")
    else:
        # Analizamos y enviamos cada tarea UNA a UNA
        for tarea in tareas:
            betreff, fach, daten = tarea
            
            # Formatear la fecha a DD-MM-YYYY para que sea más legible
            fecha_formato = f"{daten[8:10]}-{daten[5:7]}-{daten[0:4]}"
            
            # 1. ¿Es para hoy? (EMERGENCIA)
            if daten == hoy:
                mensaje = f"🚨 *¡URGENTE PARA HOY!*\n⚠️ *{fach}*: {betreff}"
                
            # 2. ¿Es personal?
            elif fach.lower() == 'personal':
                mensaje = f"🏠 *PERSONAL PENDIENTE*\n🔹 {betreff}\n📅 Fecha: {fecha_formato}"
                
            # 3. ¿Es académica?
            else:
                mensaje = f"🎓 *ACADÉMICA PENDIENTE*\n📚 *{fach}*: {betreff}\n📅 Fecha: {fecha_formato}"
                
            # Enviamos el mensaje individual
            enviar_telegram(mensaje)
            
            # Pausa de seguridad de 0.3 segundos para no saturar la API de Telegram
            time.sleep(0.3)
            
        print(f"✅ Se han enviado {len(tareas)} notificaciones individuales a Telegram.")

except sqlite3.Error as e:
    print(f"❌ Error de base de datos: {e}")
finally:
    if 'conexion' in locals():
        conexion.close()