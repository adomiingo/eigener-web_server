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
    
    # Filtra solo categoría 'Ideas' (Ajusta el nombre exacto de la categoría si es diferente en tu BD)
    cursor.execute("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Ideas' ORDER BY daten ASC")
    tareas = cursor.fetchall()
    
    if tareas:
        for tarea in tareas:
            betreff = tarea[0]
            mensaje = f"💡 *IDEA PARA EL SERVER*\n⚙️ {betreff}"
            enviar_telegram(mensaje)
            time.sleep(0.3)
        print(f"✅ Enviadas {len(tareas)} ideas.")

except sqlite3.Error as e:
    print(f"❌ Error de BD: {e}")
finally:
    if 'conexion' in locals(): conexion.close()