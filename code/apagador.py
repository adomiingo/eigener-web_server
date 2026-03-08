from http.server import BaseHTTPRequestHandler, HTTPServer
import urllib.parse
import os

# === CONFIGURACIÓN ===
PUERTO = 8000
TOKEN_SECRETO = "Motxito@2024" # Pon la contraseña que quieras aquí
# =====================

class Manejador(BaseHTTPRequestHandler):
    def do_GET(self):
        # Analizamos la URL que nos llega
        url_analizada = urllib.parse.urlparse(self.path)
        parametros = urllib.parse.parse_qs(url_analizada.query)
        
        # Comprobamos si la ruta es /apagar y el token coincide
        if url_analizada.path == '/apagar' and parametros.get('token', [''])[0] == TOKEN_SECRETO:
            self.send_response(200)
            self.send_header('Content-type', 'text/plain')
            self.end_headers()
            self.wfile.write(b"Orden recibida. Apagando WERKSTATT en 5 segundos...")
            print("¡Recibida orden legítima! Apagando...")
            
            # Lanzamos el comando de Windows para apagar en 5 segundos
            os.system("shutdown /s /t 5 /c \"Apagado remoto desde tu web\"")
        else:
            self.send_response(403) # Acceso denegado
            self.end_headers()
            self.wfile.write(b"Acceso denegado.")

# Arrancamos el servidor
servidor = HTTPServer(('0.0.0.0', PUERTO), Manejador)
print(f"WERKSTATT escuchando órdenes de apagado en el puerto {PUERTO}...")
servidor.serve_forever()