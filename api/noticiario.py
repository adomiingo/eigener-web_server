import os
import sqlite3
import requests
import feedparser
import random
import re
from datetime import datetime
from dotenv import load_dotenv
from google import genai

# --- CONFIGURACIÓN ---
load_dotenv("/var/www/html/api/.env")
client_ai = genai.Client(api_key=os.getenv("GEMINI_KEY"))

DB_PATH = "/var/www/ubungen/kalender.db"
TXT_OUTPUT = "/var/www/html/api/noticiario_hoy.txt"
DESPEDIDAS_PATH = "/var/www/html/api/despedidas.txt"

# --- 1. METEOROLOGÍA (Estilo iOS con Open-Meteo) ---
def obtener_clima():
    try:
        # Coordenadas de Barcelona
        url = "https://api.open-meteo.com/v1/forecast?latitude=41.3888&longitude=2.159&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max&current_weather=true&timezone=Europe%2FMadrid"
        r = requests.get(url, timeout=5).json()
        
        temp_actual = r['current_weather']['temperature']
        temp_max = r['daily']['temperature_2m_max'][0]
        temp_min = r['daily']['temperature_2m_min'][0]
        lluvia = r['daily']['precipitation_probability_max'][0]
        
        return f"Actual: {temp_actual}°C. Máxima hoy: {temp_max}°C. Mínima: {temp_min}°C. Probabilidad de lluvia: {lluvia}%."
    except:
        return "No se ha podido contactar con el satélite meteorológico."

# --- 2. NOTICIAS (El Buffet de RSS) ---
def recolectar_noticias():
    fuentes = [
        # Tendencias / Más habladas
        "https://news.google.com/rss?hl=es&gl=ES",
        # Tecnología
        "https://news.google.com/rss/search?q=tecnologia+innovacion+when:24h&hl=es&gl=ES",
        # Barcelona (Positivas / Agenda)
        "https://news.google.com/rss/search?q=Barcelona+(buenas+noticias+OR+cultura+OR+fin+de+semana)+when:24h&hl=es&gl=ES",
        # Bélicas importantes
        "https://news.google.com/rss/search?q=(guerra+OR+conflicto+internacional+OR+geopolitica)+when:24h&hl=es&gl=ES",
        # La Liga (Para el análisis deportivo)
        "https://news.google.com/rss/search?q=La+Liga+partidos+hoy+clasificacion+when:24h&hl=es&gl=ES"
    ]
    
    titulares = []
    for url in fuentes:
        try:
            feed = feedparser.parse(url)
            # Cogemos 3 de cada categoría para que Gemini tenga de dónde elegir
            titulares.extend([entry.title for entry in feed.entries[:3]])
        except:
            continue
            
    return " | ".join(titulares)

# --- 3. CITAS (Agenda) ---
def obtener_citas():
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()
        # Filtramos por citas pendientes
        cursor.execute("SELECT betreff, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Citas'")
        citas = cursor.fetchall()
        conn.close()
        
        if not citas:
            return "Día libre. No hay citas programadas en la agenda."
            
        lista = []
        for c in citas:
            desc = f" ({c[1]})" if c[1] else ""
            lista.append(f"{c[0]}{desc}")
        return "\n".join(lista)
    except Exception:
        return "Error al leer la base de datos de la agenda."

# --- 4. DESPEDIDA ALEATORIA ---
def obtener_despedida():
    try:
        with open(DESPEDIDAS_PATH, "r", encoding="utf-8") as f:
            lineas = [l.strip() for l in f.readlines() if l.strip()]
            return random.choice(lineas) if lineas else "Que tenga un excelente día, señor."
    except:
        return "Que tenga un excelente día, señor."

# --- 5. EL CEREBRO: REDACCIÓN CON GEMINI ---
def main():
    clima = obtener_clima()
    noticias_crudo = recolectar_noticias()
    citas = obtener_citas()
    despedida = obtener_despedida()
    
    prompt = f"""
    Eres un asistente personal de élite. Tu objetivo es redactar un noticiario matutino de unos 3 minutos de lectura en voz alta.
    Escribe ESTRICTAMENTE EN TEXTO PLANO. Prohibido usar asteriscos, hashtags, negritas o listas con viñetas. Usa puntuación natural (comas y puntos) para que el motor de voz respire.

    Sigue EXACTAMENTE esta estructura y orden:
    1. Empieza diciendo "Buenos días".
    2. Da el parte meteorológico detallado pero útil (como la app del tiempo de iOS), basándote en estos datos: {clima}
    3. Haz un resumen de noticias de entre 7 y 10 artículos hilados de forma natural. Debes incluir obligatoriamente: lo más comentado del día, novedades tecnológicas, noticias positivas o de ocio en Barcelona, y una breve mención a la geopolítica o conflictos mundiales relevantes. Base de datos de titulares: {noticias_crudo}
    4. Deportes: Revisa los titulares e informa si hoy hay algún partido importante de La Liga española. Si lo hay, haz un micro análisis de cómo afectaría a la clasificación.
    5. Citas: Informa de la agenda personal. Usa un formato cercano y directo, por ejemplo: "Hoy has quedado con...", "Hoy tienes hora para...". Datos de agenda: {citas}
    6. Cierra el discurso EXACTAMENTE con esta frase: "{despedida}"
    """
    
    try:
        response = client_ai.models.generate_content(
            model="gemini-2.5-flash", 
            contents=prompt
        )
        
        # Limpieza de seguridad por si Gemini intenta colar markdown
        texto_limpio = re.sub(r'[*#_`>\-]', '', response.text)
        texto_final = " ".join(texto_limpio.split()).strip()
        
        # Guardar en texto plano en el servidor
        with open(TXT_OUTPUT, "w", encoding="utf-8") as f:
            f.write(texto_final)
            
        print(f"✅ Noticiario guardado correctamente en: {TXT_OUTPUT}")
        
    except Exception as e:
        # Archivo de emergencia por si falla la API
        with open(TXT_OUTPUT, "w", encoding="utf-8") as f:
            f.write("Buenos días. Ha ocurrido un error al generar el noticiario de hoy. Por favor, revisa mi conexión neuronal.")

if __name__ == "__main__":
    main()