import os
import sqlite3
import re
from datetime import date, timedelta
from dotenv import load_dotenv
from google import genai

load_dotenv("/var/www/html/api/.env")
client_ai = genai.Client(api_key=os.getenv("GEMINI_KEY"))
DB_PATH = "/var/www/ubungen/kalender.db"
TXT_OUTPUT = "/var/www/html/api/guion_citas.txt"

def main():
    hoy = date.today()
    limite = hoy + timedelta(days=3)
    str_hoy = hoy.strftime('%Y-%m-%d')
    str_limite = limite.strftime('%Y-%m-%d')

    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("SELECT betreff, daten FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Citas' AND daten >= ? AND daten <= ? ORDER BY daten ASC", (str_hoy, str_limite))
    citas = cursor.fetchall()
    conn.close()

    if not citas:
        texto_final = "Agenda libre, puedes estar tranquilo"
    else:
        lista_citas = "\n".join([f"- {c[0]} (Fecha: {c[1]})" for c in citas])
        
        prompt = f"""
        Eres un asistente. Redacta un reporte en texto plano para ser leído en voz alta por Siri.
        Enumera las siguientes citas de los próximos 3 días de forma natural (ej: "tienes hora para...").
        REGLA DE ORO: Destaca con urgencia si hay alguna cita programada para hoy ({str_hoy}).
        No uses formato markdown.
        
        CITAS:
        {lista_citas}
        """
        response = client_ai.models.generate_content(model="gemini-2.5-flash", contents=prompt)
        texto_final = re.sub(r'[*#_`>\-]', '', response.text).strip()

    with open(TXT_OUTPUT, "w", encoding="utf-8") as f:
        f.write(texto_final)

if __name__ == "__main__":
    main()