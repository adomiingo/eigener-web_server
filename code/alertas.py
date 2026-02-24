import sqlite3
import requests
from datetime import date

# 1. Configuración de Telegram (¡Pon tus datos!)
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
    
    # Buscamos TODAS las tareas de hoy
    cursor.execute("SELECT betreff, fach, zustand FROM aufgaben WHERE daten = ?", (hoy,))
    tareas = cursor.fetchall()
    
    # Listas para clasificar
    academicas_pendientes = []
    personales_pendientes = []
    realizadas = []
    
    # Clasificador automático
    for tarea in tareas:
        betreff, fach, zustand = tarea
        
        if zustand == 'Erledigt':
            realizadas.append(tarea)
        else: # Si están en 'Ausstehen'
            if fach.lower() == 'personal':
                personales_pendientes.append(tarea)
            else:
                academicas_pendientes.append(tarea)
                
    # --- ENVÍO DE MENSAJES SEPARADOS ---
    
    # Mensaje 1: Académicas
    if academicas_pendientes:
        txt_acad = f"🎓 *ACADÉMICAS PENDIENTES* ({hoy})\n\n"
        for t in academicas_pendientes:
            txt_acad += f"📚 *{t[1]}*: {t[0]}\n"
        enviar_telegram(txt_acad)
        
    # Mensaje 2: Personales
    if personales_pendientes:
        txt_pers = f"🏠 *PERSONALES PENDIENTES* ({hoy})\n\n"
        for t in personales_pendientes:
            txt_pers += f"🔹 {t[0]}\n"
        enviar_telegram(txt_pers)
        
    # Mensaje 3: Realizadas (El resumen de lo que ya has hecho hoy)
    if realizadas:
        txt_hechas = f"✅ *TAREAS REALIZADAS* ({hoy})\n\n"
        for t in realizadas:
            txt_hechas += f"✔️ *{t[1]}*: {t[0]}\n"
        enviar_telegram(txt_hechas)

except sqlite3.Error as e:
    print(f"❌ Error de base de datos: {e}")
finally:
    if 'conexion' in locals():
        conexion.close()