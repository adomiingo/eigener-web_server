import os
from dotenv import load_dotenv # Nueva librería
from google import genai
from elevenlabs.client import ElevenLabs
import sqlite3
import requests
from google import genai # Cambiado al nuevo cliente
from elevenlabs.client import ElevenLabs
from newspaper import Article
import feedparser

load_dotenv()

# --- 1. CONFIGURACIÓN ---
GEMINI_KEY = os.getenv("GEMINI_KEY")
ELEVEN_KEY = os.getenv("ELEVEN_KEY")
VOICE_ID = os.getenv("VOICE_ID")

DB_PATH = "/var/www/ubungen/kalender.db"
AUDIO_OUTPUT = "/var/www/html/api/audio/noticias.mp3"

# --- 2. INICIALIZAR CLIENTES ---
client_ai = genai.Client(api_key=GEMINI_KEY) # Nuevo formato de cliente
client_11 = ElevenLabs(api_key=ELEVEN_KEY)

# --- 3. RECOLECTAR NOTICIAS ---
def leer_noticia(rss_url):
    feed = feedparser.parse(rss_url)
    if not feed.entries: return "No hay noticias."
    try:
        entry = feed.entries[0]
        art = Article(entry.link)
        art.download()
        art.parse()
        return f"TITULAR: {entry.title}. CONTENIDO: {art.text[:1000]}"
    except:
        return f"TITULAR: {feed.entries[0].title}"

print("🛰️ Extrayendo información...")
news_bcn = leer_noticia("https://news.google.com/rss/search?q=Barcelona+cultura+agenda+when:24h&hl=es&gl=ES")
news_fcb = leer_noticia("https://news.google.com/rss/search?q=FC+Barcelona+when:12h&hl=es&gl=ES")
news_eur = leer_noticia("https://news.google.com/rss/search?q=politica+Europa+when:24h&hl=es&gl=ES")

# --- 4. TAREAS ---
conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()
cursor.execute("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1")
tareas = [t[0] for t in cursor.fetchall()]
tareas_txt = ", ".join(tareas) if tareas else "Día libre."
conn.close()

# --- 5. GENERAR GUIÓN ---
print("🧠 Redactando guión...")
prompt = f"""
Escribe un guión de radio. Sé épico y motivador. 
No uses asteriscos ni listas. Solo texto fluido.
Noticias BCN: {news_bcn}
Barça: {news_fcb}
Europa: {news_eur}
Tareas: {tareas_txt}
Cierra con: "Eres una puta máquina de matar y nadie puede contigo. Buenos días, señor."
"""

# Nueva forma de generar contenido
response = client_ai.models.generate_content(
    model="gemini-1.5-flash", 
    contents=prompt
)
guion = response.text

# --- 6. GENERAR AUDIO ---
print("🎙️ Generando audio...")
audio_gen = client_11.generate(
    text=guion,
    voice=VOICE_ID,
    model="eleven_multilingual_v2"
)

# --- 7. GUARDAR ---
os.makedirs(os.path.dirname(AUDIO_OUTPUT), exist_ok=True)
with open(AUDIO_OUTPUT, "wb") as f:
    for chunk in audio_gen:
        if chunk: f.write(chunk)

print(f"✅ Noticiario listo en: {AUDIO_OUTPUT}")