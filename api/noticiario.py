import os
import sqlite3
import requests
import random
import asyncio
import edge_tts
from dotenv import load_dotenv
from google import genai
from newspaper import Article
import feedparser

# --- 1. CARGAR LLAVES SECRETAS ---
# --- 1. CARGAR LLAVES SECRETAS ---
load_dotenv("/var/www/html/api/.env")

GEMINI_KEY = os.getenv("GEMINI_KEY")
# ¡Adiós ElevenLabs! Ya no pagamos peajes.

DB_PATH = "/var/www/ubungen/kalender.db"
AUDIO_OUTPUT = "/var/www/html/api/audio/noticias.mp3"
DESPEDIDAS_PATH = "/var/www/html/api/despedidas.txt"

# --- 2. INICIALIZAR CLIENTE IA ---
client_ai = genai.Client(api_key=GEMINI_KEY)

# --- 3. RECOLECTAR NOTICIAS ---
def leer_noticia(rss_url, limite=1):
    feed = feedparser.parse(rss_url)
    textos = []
    for entry in feed.entries[:limite]:
        try:
            art = Article(entry.link)
            art.download()
            art.parse()
            textos.append(f"TITULAR: {entry.title}. CONTENIDO: {art.text[:1000]}")
        except:
            textos.append(f"TITULAR: {entry.title}")
    return "\n\n".join(textos) if textos else "Sin noticias destacadas."

print("🛰️ Extrayendo información del mundo...")
news_bcn = leer_noticia("https://news.google.com/rss/search?q=Barcelona+cultura+agenda+when:24h&hl=es&gl=ES", 1)
news_fcb = leer_noticia("https://news.google.com/rss/search?q=FC+Barcelona+when:12h&hl=es&gl=ES", 2)
news_eur = leer_noticia("https://news.google.com/rss/search?q=politica+Europa+when:24h&hl=es&gl=ES", 2)

# --- 3.5 RECOLECTAR METEOROLOGÍA ---
def obtener_clima(ciudad="Barcelona"):
    print("🌤️ Consultando satélites meteorológicos...")
    try:
        url = f"https://wttr.in/{ciudad}?format=%C,+Temperatura:+%t,+Sensación+térmica:+%f,+Humedad:+%h&lang=es"
        respuesta = requests.get(url, timeout=5)
        if respuesta.status_code == 200:
            return f"Ciudad de {ciudad}: {respuesta.text}"
        return "Datos meteorológicos inaccesibles temporalmente."
    except:
        return "Conexión con el satélite meteorológico perdida."

clima_actual = obtener_clima()

# --- 4. TAREAS ---
print("📅 Leyendo tu base de datos...")
try:
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1")
    tareas = [t[0] for t in cursor.fetchall()]
    tareas_txt = ", ".join(tareas) if tareas else "Día libre, sin tareas matutinas programadas."
    conn.close()
except Exception as e:
    tareas_txt = "Error al leer los registros de la agenda."

# --- 4.5 ELEGIR DESPEDIDA ALEATORIA ---
print("🎲 Buscando una frase de cierre...")
try:
    with open(DESPEDIDAS_PATH, "r", encoding="utf-8") as f:
        despedidas = [linea.strip() for linea in f.readlines() if linea.strip()]
    frase_final = random.choice(despedidas) if despedidas else "Que tenga un excelente día, señor."
except FileNotFoundError:
    frase_final = "Que tenga un excelente día, señor."

# --- 5. REDACTAR EL GUIÓN ---
print("🧠 Redactando el guión con Gemini...")
prompt = f"""
Haz un guión con el supuesto fin de que lo leyera J.A.R.V.I.S de Iron Man, da los buenos días de manera formal, no uses acrónimos y si los encuentras expándelos (ej. FC Barcelona = El fútbol club Barcelona), da el nombre completo de las cosas, no te limites a decir únicamente la cabecera, indaga ligeramente en el artículo pero sin entretenerte, mantén un tono formal pero no plano, quiero que el guión tenga un poco de ritmo (sin asteriscos ni listas).

CONTENIDO A INFORMAR:
1. Reporte Meteorológico: {clima_actual}
2. Noticias en relación a la Ciudad de Barcelona: {news_bcn}
3. Novedades del Fútbol Club Barcelona: {news_fcb}
4. Actualización de la Política Europea: {news_eur}
5. Agenda personal del señor para hoy: {tareas_txt}

Cierra el programa con esta frase exacta (Pon una pausa o un punto para que se entienda que tienes que hacer una ligera pausa antes de decirla): "{frase_final}"
"""

response = client_ai.models.generate_content(
    model="gemini-2.5-flash", 
    contents=prompt
)
guion = response.text

# --- 6. GENERAR AUDIO CON EDGE TTS (Versión Ultra-Estable) ---
print("🎙️ Generando audio con Elías (Velocidad optimizada)...")

async def generar_audio():
    # Eliminamos el Pitch por completo para evitar errores de Microsoft
    # El +15% de velocidad quita la sensación de "robot leyendo"
    comunicador = edge_tts.Communicate(
        guion, 
        "es-ES-EliasNeural", 
        rate="+15%"
    )
    await comunicador.save(AUDIO_OUTPUT)

asyncio.run(generar_audio())

print(f"✅ ¡Misión cumplida! MP3 generado en: {AUDIO_OUTPUT}")