import sys
import os
import random
import traceback

# Asegurar que se puedan importar módulos locales
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from config import GIROS, CIUDADES
from extractor_inteligente import motor_google_places, persistir_leads
from extractor_empleos import motor_empleos_y_enriquecimiento
from extractor_denue import motor_denue_y_enriquecimiento

def ejecutar_orquestador(giro=None, ciudad=None):
    # Seleccionar giro y ciudad de forma dinámica si no se pasan por parámetros
    if not giro:
        giro = random.choice(GIROS)
    if not ciudad:
        ciudad = random.choice(CIUDADES)
        
    print("=" * 60)
    print(f"[*] INICIANDO MOTOR DE EXTRACCIÓN MODULAR MULTI-FUENTE")
    print(f"[*] Giro objetivo: {giro.upper()}")
    print(f"[*] Ubicación objetivo: {ciudad.upper()}")
    print("=" * 60)
    
    todos_los_leads = []
    
    # --- SANDBOX 1: GOOGLE MAPS (Baseline prioritario) ---
    print("\n[FASE 1/3] Lanzando módulo: Google Maps...")
    try:
        leads_maps = motor_google_places(giro, ciudad)
        todos_los_leads.extend(leads_maps)
        print("[FASE 1/3] [OK] Módulo Google Maps completado exitosamente.")
    except Exception as e:
        print(f"[FASE 1/3] [ERROR CRÍTICO] Módulo Google Maps falló: {e}")
        traceback.print_exc()

    # --- SANDBOX 2: PORTALES DE EMPLEO (Oportunidad) ---
    print("\n[FASE 2/3] Lanzando módulo: Vacantes de Empleo...")
    try:
        leads_empleos = motor_empleos_y_enriquecimiento(giro, ciudad)
        todos_los_leads.extend(leads_empleos)
        print("[FASE 2/3] [OK] Módulo Empleos completado exitosamente.")
    except Exception as e:
        # Si este módulo experimental falla, el script NO se detiene
        print(f"[FASE 2/3] [FALLO DETECTADO (Aislado)] Módulo Empleos falló: {e}")
        print("[*] Continuando con la ejecución de los otros módulos...")

    # --- SANDBOX 3: DENUE INEGI (Registro Oficial) ---
    print("\n[FASE 3/3] Lanzando módulo: DENUE (INEGI)...")
    try:
        leads_denue = motor_denue_y_enriquecimiento(giro, ciudad)
        todos_los_leads.extend(leads_denue)
        print("[FASE 3/3] [OK] Módulo DENUE completado exitosamente.")
    except Exception as e:
        # Falla aislada capturada
        print(f"[FASE 3/3] [FALLO DETECTADO (Aislado)] Módulo DENUE falló: {e}")
        print("[*] Continuando con la persistencia de datos...")

    # --- PROCESAMIENTO GENERAL ---
    if todos_los_leads:
        print("\n" + "=" * 60)
        print(f"[*] PROCESANDO Y DEPURANDO LEADS EXTRAÍDOS")
        # Filtro de duplicados por teléfono
        leads_unicos = {l['telefono_whatsapp']: l for l in todos_los_leads}.values()
        leads_finales = list(leads_unicos)
        print(f"[*] Leads totales consolidados: {len(todos_los_leads)}")
        print(f"[*] Leads únicos finales para inyectar al CRM: {len(leads_finales)}")
        print("=" * 60)
        
        # Inyectar en BD
        persistir_leads(leads_finales)
    else:
        print("\n[-] No se obtuvieron leads viables en ninguna de las fuentes en esta ejecución.")
        
    print("\n[*] Proceso de extracción terminado de forma exitosa.")

if __name__ == "__main__":
    # Soporte para argumentos de consola: python orquestador.py "giro" "ciudad"
    giro_arg = sys.argv[1] if len(sys.argv) > 1 else None
    ciudad_arg = sys.argv[2] if len(sys.argv) > 2 else None
    
    ejecutar_orquestador(giro_arg, ciudad_arg)
