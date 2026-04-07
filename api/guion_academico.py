import os
import sqlite3
import re
from datetime import date, timedelta
from dotenv import load_dotenv
from google import genai

load_dotenv("/var/www/html/api/.env")
client_ai = genai.Client(api_key=os.getenv("GEMINI_KEY"))
DB_PATH = "/var/www/ubungen/kalender.db"
TXT_OUTPUT = "/var/www/html/api/guion_academico.txt"

def main():
    hoy = date.today()
    limite = hoy + timedelta(days=2)
    str_hoy = hoy.strftime('%Y-%m-%d')
    str_limite = limite.strftime('%Y-%m-%d')

    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("SELECT betreff, daten FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Académico' AND daten >= ? AND daten <= ? ORDER BY daten ASC", (str_hoy, str_limite))
    tareas = cursor.fetchall()
    conn.close()

    if not tareas:
        texto_final = "Libre de clases, libre de estrés. Disfruta del día"
    else:
        lista_tareas = "\n".join([f"- {t[0]} (Fecha: {t[1]})" for t in tareas])
        
        prompt = f"""
        Eres un asistente. Redacta un texto breve en texto plano para que lo lea Siri en voz alta.
        Habla sobre estas tareas académicas de los próximos 2 días.
        REGLA DE ORO: Haz un énfasis especial y advierte si alguna tarea tiene la fecha de hoy ({str_hoy}).
        No uses formato markdown (ni asteriscos, ni negritas).
        
        TAREAS:
        {lista_tareas}
        """
        response = client_ai.models.generate_content(model="gemini-2.5-flash", contents=prompt)
        texto_final = re.sub(r'[*#_`>\-]', '', response.text).strip()

    # Sobrescribir (purgar) el guion antiguo
    with open(TXT_OUTPUT, "w", encoding="utf-8") as f:
        f.write(texto_final)

if __name__ == "__main__":
    main()