import sqlite3

# Ruta a la base de datos
DB_PATH = "/var/www/html/kalender.db"

try:
    # Conectamos a la base de datos
    conexion = sqlite3.connect(DB_PATH)
    cursor = conexion.cursor()
    
    # --- 🧹 MANTENIMIENTO: Limpieza de la papelera ---
    # Busca y destruye tareas completadas hace más de 15 días
    cursor.execute("DELETE FROM completadas WHERE fecha_completada <= date('now', '-15 days')")
    
    # Contamos cuántas filas se han borrado para el log de la web
    filas_borradas = cursor.rowcount
    conexion.commit()
    
    print("✅ Mantenimiento del servidor completado.")
    if filas_borradas > 0:
        print(f"🧹 Se han purgado {filas_borradas} tareas antiguas de la base de datos.")
    else:
        print("📭 La base de datos está limpia. No había tareas de más de 15 días.")

except sqlite3.Error as e:
    print(f"❌ Error crítico de base de datos: {e}")
finally:
    # Cerramos la conexión de forma segura
    if 'conexion' in locals():
        conexion.close()