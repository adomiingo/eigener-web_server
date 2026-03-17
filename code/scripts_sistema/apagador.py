from http.server import BaseHTTPRequestHandler, HTTPServer
import urllib.parse
import os

# === CONFIGURACIÓN ===
PUERTO = 8000
TOKEN_SECRETO = "Motxito@2024" # <-- Asegúrate de que es IDÉNTICO al de control.php
# =====================

class Manejador(BaseHTTPRequestHandler):
    def do_GET(self):
        url_analizada = urllib.parse.urlparse(self.path)
        parametros = urllib.parse.parse_qs(url_analizada.query)
        
        # Extraemos el token que llega desde Google Cloud
        token_recibido = parametros.get('token', [''])[0]
        
        # Chivato para la consola:
        print(f"-> Petición recibida en: {url_analizada.path}")
        print(f"-> Token esperado por el PC: '{TOKEN_SECRETO}'")
        print(f"-> Token recibido de la Web: '{token_recibido}'")

        if url_analizada.path == '/apagar' and token_recibido == TOKEN_SECRETO:
            self.send_response(200)
            self.send_header('Content-type', 'text/plain')
            self.end_headers()
            self.wfile.write(b"Orden recibida. Apagando WERKSTATT en 5 segundos...")
            print("¡Contraseña correcta! Apagando el sistema...")
            
            # os.system("shutdown /s /t 5")
            os.system("echo EL SERVIDOR WEB HABLA CON PYTHON PERFECTAMENTE > C:\\Users\\socal\\Desktop\\PRUEBA_OK.txt")
        else:
            self.send_response(403)
            self.end_headers()
            self.wfile.write(b"Acceso denegado.")
            print("¡Contraseña INCORRECTA o ruta equivocada! Acceso denegado.\n")

servidor = HTTPServer(('0.0.0.0', PUERTO), Manejador)
print(f"WERKSTATT escuchando órdenes de apagado en el puerto {PUERTO}...")
servidor.serve_forever()