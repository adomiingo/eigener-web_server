import os
import sqlite3
import re
from dotenv import load_dotenv
from google import genai

load_dotenv("/var/www/html/api/.env")
client_ai = genai.Client(api_key=os.getenv("GEMINI_KEY"))
DB_PATH = "/var/www/ubungen/kalender.db"
TXT_OUTPUT = "/var/www/html/api/guion_personal.txt"

def main():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Personal'")
    tareas = cursor.fetchall()
    conn.close()

    if not tareas:
        texto_final = "No tienes asuntos personales pendientes en este momento."
    else:
        lista_tareas = "\n".join([f"- {t[0]}" for t in tareas])
        
        prompt = f"""
        Eres un asistente. Redacta un reporte en texto plano fluido para ser leído en voz alta por Siri.
        Debes enumerar todas estas tareas personales pendientes.
        No uses formato markdown.
        
        TAREAS PERSONALES:
        {lista_tareas}
        """
        response = client_ai.models.generate_content(model="gemini-2.5-flash", contents=prompt)
        texto_final = re.sub(r'[*#_`>\-]', '', response.text).strip()

    with open(TXT_OUTPUT, "w", encoding="utf-8") as f:
        f.write(texto_final)

if __name__ == "__main__":
    main()