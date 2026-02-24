import sqlite3
import requests
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
    
    # Listas para clasificar
    urgentes_hoy = []
    academicas_pendientes = []
    personales_pendientes = []
    
    # Clasificador automático
    for tarea in tareas:
        betreff, fach, daten = tarea
        
        if daten == hoy:
            urgentes_hoy.append(tarea)
        else:
            if fach.lower() == 'personal':
                personales_pendientes.append(tarea)
            else:
                academicas_pendientes.append(tarea)
                
    # --- ENVÍO DE MENSAJES SEPARADOS ---
    
    # 1. Mensaje de Emergencia (Si hay tareas con fecha de HOY)
    if urgentes_hoy:
        txt_urgente = f"🚨 *¡EMERGENCIA! TAREAS PARA HOY* ({hoy}) 🚨\n\n"
        for t in urgentes_hoy:
            txt_urgente += f"⚠️ *{t[1]}*: {t[0]}\n"
        enviar_telegram(txt_urgente)
        
    # 2. Mensaje: Otras Académicas Pendientes
    if academicas_pendientes:
        txt_acad = "🎓 *OTRAS ACADÉMICAS PENDIENTES*\n\n"
        for t in academicas_pendientes:
            # Formateamos la fecha para verla en DD-MM-YYYY
            fecha_formato = f"{t[2][8:10]}-{t[2][5:7]}-{t[2][0:4]}"
            txt_acad += f"📚 *{t[1]}* ({fecha_formato}): {t[0]}\n"
        enviar_telegram(txt_acad)
        
    # 3. Mensaje: Otras Personales Pendientes
    if personales_pendientes:
        txt_pers = "🏠 *OTRAS PERSONALES PENDIENTES*\n\n"
        for t in personales_pendientes:
            fecha_formato = f"{t[2][8:10]}-{t[2][5:7]}-{t[2][0:4]}"
            txt_pers += f"🔹 ({fecha_formato}): {t[0]}\n"
        enviar_telegram(txt_pers)

except sqlite3.Error as e:
    print(f"❌ Error de base de datos: {e}")
finally:
    if 'conexion' in locals():
        conexion.close()