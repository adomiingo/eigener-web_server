import sqlite3
import requests
import time

TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg"
CHAT_ID = "5181963608"
DB_PATH = "/var/www/ubungen/kalender.db"

def enviar_telegram(mensaje):
    url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"
    requests.post(url, data={"chat_id": CHAT_ID, "text": mensaje, "parse_mode": "Markdown"})

try:
    conexion = sqlite3.connect(DB_PATH)
    cursor = conexion.cursor()
    
    # Busca tareas pendientes que tengan el botón de "Matutino" activado (1)
    cursor.execute("SELECT betreff, fach FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1")
    tareas = cursor.fetchall()
    
    if tareas:
        for tarea in tareas:
            betreff, fach = tarea
            mensaje = f"☕ *BUEN DÍA MÁQUINA DE MATAR, HOY TOCA ESTO*\n🌞 *{fach}*: {betreff}"
            enviar_telegram(mensaje)
            time.sleep(0.3)
        print(f"✅ Enviadas {len(tareas)} alertas matutinas.")

except sqlite3.Error as e:
    print(f"❌ Error de BD: {e}")
finally:
    if 'conexion' in locals(): conexion.close()