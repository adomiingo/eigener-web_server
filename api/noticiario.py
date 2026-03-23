import os
import sqlite3
import requests
import google.generativeai as genai
from elevenlabs.client import ElevenLabs
from newspaper import Article
import feedparser

# --- 1. CONFIGURACIÓN ---
GEMINI_KEY = "AIzaSyBcvQ93Vb6x8XptBXAm_Bf_-Nrb9PKFaas"
ELEVEN_KEY = "sk_3752dc681f5ade5c8999421d27b02378dc0377bc53473c01"
# Si aún no tienes un Voice ID, usa este (es una voz masculina profesional):
VOICE_ID = "pNInz6obpg8nEByWQX2t" 

DB_PATH = "/var/www/ubungen/kalender.db"
AUDIO_OUTPUT = "/var/www/html/api/audio/noticias.mp3"

# --- 2. INICIALIZAR IAs ---
genai.configure(api_key=GEMINI_KEY)
ai_model = genai.GenerativeModel('gemini-1.5-pro')
client_11 = ElevenLabs(api_key=ELEVEN_KEY)

# --- 3. RECOLECTAR NOTICIAS (BCN, BARÇA, EUROPA) ---
def leer_noticia(rss_url):
    feed = feedparser.parse(rss_url)
    if not feed.entries: return "No hay noticias nuevas."
    try:
        entry = feed.entries[0]
        art = Article(entry.link)
        art.download()
        art.parse()
        return f"TITULAR: {entry.title}. CONTENIDO: {art.text[:1200]}"
    except:
        return f"TITULAR: {feed.entries[0].title}"

print("🛰️ Extrayendo información...")
news_bcn = leer_noticia("https://news.google.com/rss/search?q=Barcelona+cultura+agenda+when:24h&hl=es&gl=ES")
news_fcb = leer_noticia("https://news.google.com/rss/search?q=FC+Barcelona+when:12h&hl=es&gl=ES")
news_eur = leer_noticia("https://news.google.com/rss/search?q=politica+Europa+when:24h&hl=es&gl=ES")

# --- 4. LEER TAREAS SQLITE ---
conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()
cursor.execute("SELECT betreff, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1")
tareas = cursor.fetchall()
tareas_txt = "\n".join([f"- {t[0]}: {t[1]}" for t in tareas]) if tareas else "Día libre de tareas."
conn.close()

# --- 5. GENERAR GUIÓN CON GEMINI ---
prompt = f"""
Escribe un guión de radio matutino. Sé directo, épico y motivador. 
No uses asteriscos, ni listas, solo texto fluido para ser leído por un locutor.

ORDEN:
1. Saludo breve.
2. Barcelona: {news_bcn}
3. Barça: {news_fcb}
4. Europa: {news_eur}
5. Tus tareas: {tareas_txt}
6. Cierre motivador: "Recuerda que eres una puta máquina de matar y nadie puede contigo. Buenos días, señor."
"""

print("🧠 Redactando guión...")
guion = ai_model.generate_content(prompt).text

# --- 6. CONVERTIR A AUDIO (ElevenLabs) ---
print("🎙️ Generando audio...")
audio_gen = client_11.generate(
    text=guion,
    voice=VOICE_ID,
    model="eleven_multilingual_v2"
)

# --- 7. GUARDAR ARCHIVO ---
os.makedirs(os.path.dirname(AUDIO_OUTPUT), exist_ok=True)
if os.path.exists(AUDIO_OUTPUT): os.remove(AUDIO_OUTPUT)

with open(AUDIO_OUTPUT, "wb") as f:
    for chunk in audio_gen:
        if chunk: f.write(chunk)

print(f"✅ Noticiario listo en: {AUDIO_OUTPUT}")