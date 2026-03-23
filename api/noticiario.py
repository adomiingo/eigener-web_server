import os
import sqlite3
import requests
import random  # <--- NUEVA LIBRERÍA AÑADIDA
from dotenv import load_dotenv
from google import genai
from elevenlabs.client import ElevenLabs
from newspaper import Article
import feedparser

# --- 1. CARGAR LLAVES SECRETAS ---
load_dotenv()
GEMINI_KEY = os.getenv("GEMINI_KEY")
ELEVEN_KEY = os.getenv("ELEVEN_KEY")
VOICE_ID = os.getenv("VOICE_ID", "pNInz6obpg8nEByWQX2t")

DB_PATH = "/var/www/ubungen/kalender.db"
AUDIO_OUTPUT = "/var/www/html/api/audio/noticias.mp3"
DESPEDIDAS_PATH = "/var/www/html/api/despedidas.txt" # <--- RUTA DEL ARCHIVO

# --- 2. INICIALIZAR CLIENTES ---
client_ai = genai.Client(api_key=GEMINI_KEY)
client_11 = ElevenLabs(api_key=ELEVEN_KEY)

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

# --- 4. TAREAS ---
print("📅 Leyendo tu base de datos...")
try:
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1")
    tareas = [t[0] for t in cursor.fetchall()]
    tareas_txt = ", ".join(tareas) if tareas else "Día libre, sin tareas matutinas."
    conn.close()
except Exception as e:
    tareas_txt = "Error al leer las tareas."

# --- 4.5 ELEGIR DESPEDIDA ALEATORIA ---
print("🎲 Buscando una frase de cierre...")
try:
    with open(DESPEDIDAS_PATH, "r", encoding="utf-8") as f:
        # Lee todas las líneas, quitando espacios en blanco y líneas vacías
        despedidas = [linea.strip() for linea in f.readlines() if linea.strip()]
    
    # Elige una al azar, o usa una por defecto si el archivo está vacío
    frase_final = random.choice(despedidas) if despedidas else "Buenos días, señor."
except FileNotFoundError:
    frase_final = "Buenos días, señor." # Salvavidas si el archivo no existe

# --- 5. REDACTAR EL GUIÓN ---
print("🧠 Redactando el guión con Gemini...")
prompt = f"""
Eres un asistente informativo, tú unico proposito es narrar de manera clara y entendedora la información proporcionada. Usa un vocabulario tecnico y rapido, no hagas introducciones, lee los titulares de los siguientes articulos y resume de manera muy breve pero concisa la información (sin asteriscos ni listas).

CONTENIDO A RESUMIR:
1. Ciudad de Barcelona: {news_bcn}
2. FC Barcelona: {news_fcb}
3. Política Europea: {news_eur}
4. Agenda personal del señor: {tareas_txt}

Cierra el programa con esta frase exacta: "{frase_final}"
"""

# Usamos el modelo que descubrimos que funciona perfectamente
response = client_ai.models.generate_content(
    model="gemini-2.5-flash", 
    contents=prompt
)
guion = response.text

# --- 6. GENERAR AUDIO CON ELEVENLABS ---
print("🎙️ Grabando audio en el estudio...")
audio_gen = client_11.text_to_speech.convert(
    text=guion,
    voice_id=VOICE_ID,
    model_id="eleven_multilingual_v2",
    output_format="mp3_44100_128"
)

# --- 7. GUARDAR ARCHIVO ---
os.makedirs(os.path.dirname(AUDIO_OUTPUT), exist_ok=True)
with open(AUDIO_OUTPUT, "wb") as f:
    for chunk in audio_gen:
        if chunk: f.write(chunk)

print(f"✅ ¡Misión cumplida! MP3 generado en: {AUDIO_OUTPUT}")