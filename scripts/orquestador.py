import sys
import os
import random
import traceback
import mysql.connector
from mysql.connector import Error

# Asegurar que se puedan importar módulos locales
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from config import GIROS, CIUDADES, DB_CONFIG
from extractor_inteligente import motor_google_places, persistir_leads
from extractor_empleos import motor_empleos_y_enriquecimiento
from extractor_denue import motor_denue_y_enriquecimiento

def obtener_configuraciones_activas():
    """
    Obtiene las tareas de extracción activas configuradas por los usuarios desde la BD.
    """
    configs = []
    try:
        conexion = mysql.connector.connect(**DB_CONFIG)
        cursor = conexion.cursor(dictionary=True)
        cursor.execute("SELECT id, user_id, giro, ciudad FROM configuraciones_extraccion WHERE estado = 1")
        configs = cursor.fetchall()
    except Exception as e:
        print(f"[-] Error al consultar configuraciones_extraccion: {e}")
    finally:
        if 'conexion' in locals() and conexion.is_connected():
            cursor.close()
            conexion.close()
    return configs

def ejecutar_extraccion_para_combinacion(giro, ciudad, user_id=None):
    print("=" * 60)
    print(f"[*] PROCESANDO TAREA DE EXTRACCIÓN")
    print(f"[*] Giro: {giro.upper()}")
    print(f"[*] Ciudad: {ciudad.upper()}")
    print(f"[*] Usuario Asignado (ID): {user_id}")
    print("=" * 60)
    
    todos_los_leads = []
    
    # --- FASE 1: GOOGLE MAPS ---
    try:
        leads_maps = motor_google_places(giro, ciudad)
        todos_los_leads.extend(leads_maps)
    except Exception as e:
        print(f"[-] Fallo en Módulo Google Maps: {e}")
        traceback.print_exc()

    # --- FASE 2: VACANTES DE EMPLEO (Computrabajo) ---
    try:
        leads_empleos = motor_empleos_y_enriquecimiento(giro, ciudad)
        todos_los_leads.extend(leads_empleos)
    except Exception as e:
        print(f"[-] Fallo en Módulo Empleos: {e}")

    # --- FASE 3: DENUE INEGI ---
    try:
        leads_denue = motor_denue_y_enriquecimiento(giro, ciudad)
        todos_los_leads.extend(leads_denue)
    except Exception as e:
        print(f"[-] Fallo en Módulo DENUE: {e}")

    # --- PROCESAMIENTO E INYECCIÓN ---
    if todos_los_leads:
        # Asignar el user_id a cada lead extraído
        for lead in todos_los_leads:
            lead['user_id'] = user_id
            
        # Eliminar duplicados de teléfonos en esta corrida
        leads_unicos = {l['telefono_whatsapp']: l for l in todos_los_leads}.values()
        leads_finales = list(leads_unicos)
        
        print(f"\n[*] Guardando {len(leads_finales)} leads únicos...")
        persistir_leads(leads_finales)
    else:
        print("\n[-] No se obtuvieron leads para esta combinación.")

def main():
    # Soporte para argumentos manuales: python orquestador.py "giro" "ciudad"
    if len(sys.argv) > 2:
        giro_arg = sys.argv[1]
        ciudad_arg = sys.argv[2]
        ejecutar_extraccion_para_combinacion(giro_arg, ciudad_arg, user_id=None)
        return

    # Consultar tareas programadas por usuarios en la base de datos
    tareas_activas = obtener_configuraciones_activas()
    
    if tareas_activas:
        print(f"[*] Se encontraron {len(tareas_activas)} tareas programadas activas por usuarios.")
        for tarea in tareas_activas:
            ejecutar_extraccion_para_combinacion(
                giro=tarea['giro'], 
                ciudad=tarea['ciudad'], 
                user_id=tarea['user_id']
            )
    else:
        # Fallback histórico: Si no hay tareas activas, hacer una corrida aleatoria de catálogo
        print("[*] No hay tareas activas programadas en BD. Usando fallback de catálogo aleatorio...")
        giro_aleatorio = random.choice(GIROS)
        ciudad_aleatoria = random.choice(CIUDADES)
        ejecutar_extraccion_para_combinacion(giro_aleatorio, ciudad_aleatoria, user_id=None)

if __name__ == "__main__":
    main()
