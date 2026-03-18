from http.server import BaseHTTPRequestHandler, HTTPServer
import urllib.parse
import os
import sys

# Escudo para evitar que pythonw explote al no tener consola
sys.stdout = open(os.devnull, 'w')
sys.stderr = open(os.devnull, 'w')

# === CONFIGURACIÓN ===
PUERTO = 8000
TOKEN_SECRETO = "Motxito@2024" 
# =====================

class Manejador(BaseHTTPRequestHandler):
    def do_GET(self):
        url_analizada = urllib.parse.urlparse(self.path)
        parametros = urllib.parse.parse_qs(url_analizada.query)
        token_recibido = parametros.get('token', [''])[0]

        if url_analizada.path == '/apagar' and token_recibido == TOKEN_SECRETO:
            self.send_response(200)
            self.send_header('Content-type', 'text/plain')
            self.end_headers()
            self.wfile.write(b"Orden recibida. Apagando WERKSTATT en 5 segundos...")
            
            # --- MODO APAGADO REAL ---
            os.system("shutdown /s /t 5")

        else:
            self.send_response(403)
            self.end_headers()
            self.wfile.write(b"Acceso denegado.")

servidor = HTTPServer(('0.0.0.0', PUERTO), Manejador)
servidor.serve_forever()