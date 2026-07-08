import os
import time
import random
import requests
import mysql.connector
from mysql.connector import Error
from urllib.parse import urlparse

# --- CONFIGURACIÓN DE BASE DE DATOS (PRODUCCIÓN) ---
DB_CONFIG = {
    'host': 'localhost',
    'database': 'dbScrap',
    'user': 'adminDbScrap',
    'password': 'RNN7R7Re0pzuZ5napGZR'
}

# --- CONFIGURACIÓN DE APIs ---
GOOGLE_PLACES_API_KEY = 'AIzaSyBfm6IRK4y3sSEIzDN5Me-S67W2JCvyw0Y'

# --- LISTA DE CIUDADES PARA BÚSQUEDA NACIONAL DINÁMICA ---
# Al rotar cada día, gastamos 1 sola petición a Google pero abarcamos todo el país
CIUDADES = [
    "CDMX", "Monterrey", "Guadalajara", "Puebla", "Tijuana", 
    "Leon", "Querétaro", "Toluca", "San Luis Potosí", "Mérida", 
    "Aguascalientes", "Saltillo", "Hermosillo", "Mexicali", "Culiacán", "Chihuahua"
]

# ==========================================
# MOTOR DE BÚSQUEDA NACIONAL (Google Maps)
# ==========================================
def motor_google_places(giro, ubicacion):
    """Busca negocios locales en Google Maps optimizando el uso de la API"""
    query = f"empresas de {giro} en {ubicacion}"
    print(f"\n[GOOGLE] Buscando: '{query}'...")
    url = "https://maps.googleapis.com/maps/api/place/textsearch/json"
    params = {"query": query, "key": GOOGLE_PLACES_API_KEY}
    
    empresas_google = []
    try:
        response = requests.get(url, params=params, timeout=15)
        if response.status_code == 200:
            results = response.json().get('results', [])
            
            # Procesar solo los primeros 15 resultados para evitar gastar toda la capa gratuita de Places Details rápido
            for r in results[:15]:
                place_id = r.get('place_id')
                nombre = r.get('name')
                direccion = r.get('formatted_address', ubicacion)
                
                # Obtener detalles específicos (teléfono y web)
                detail_url = f"https://maps.googleapis.com/maps/api/place/details/json?place_id={place_id}&fields=name,website,formatted_phone_number,formatted_address&key={GOOGLE_PLACES_API_KEY}"
                det_res = requests.get(detail_url, timeout=10)
                if det_res.status_code == 200:
                    detalles = det_res.json().get('result', {})
                    telefono = detalles.get('formatted_phone_number', '')
                    web = detalles.get('website', '')
                    
                    if telefono: # Filtro crítico: Solo guardamos si tienen teléfono
                        dominio = urlparse(web).netloc.replace('www.', '') if web else 'N/A'
                        dir_exacta = detalles.get('formatted_address', direccion)
                        
                        empresas_google.append({
                            'empresa': nombre,
                            'giro_negocio': giro,
                            'director_nombre': 'Dueño / Encargado',
                            'correo_corporativo': 'N/A', # Sin correo forzado
                            'telefono_whatsapp': telefono,
                            'tamano_estimado': 'Google Maps',
                            'ubicacion_local': dir_exacta, # Usamos la dirección exacta
                            'url_origen': dominio
                        })
                time.sleep(0.5) # Balanceo de carga para Google API
    except Exception as e: print(f"[-] Error en Motor Google Places: {e}")
        
    print(f"[GOOGLE] Encontró {len(empresas_google)} empresas con número telefónico.")
    return empresas_google

# ==========================================
# BASE DE DATOS
# ==========================================
def persistir_leads(leads):
    if not leads: return
    try:
        conexion = mysql.connector.connect(**DB_CONFIG)
        cursor = conexion.cursor()
        query = """INSERT IGNORE INTO prospectos_scrapping 
                   (empresa, giro_negocio, director_nombre, correo_corporativo, telefono_whatsapp, tamano_estimado, ubicacion_local, url_origen)
                   VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"""
        valores = [(l['empresa'], l['giro_negocio'], l['director_nombre'], l['correo_corporativo'], l['telefono_whatsapp'], l['tamano_estimado'], l['ubicacion_local'], l['url_origen']) for l in leads]
        cursor.executemany(query, valores)
        conexion.commit()
        print(f"\n[BD] [+] ¡Registros inyectados al CRM de manera exitosa!: {cursor.rowcount}")
    except Error as e: print(f"\n[BD] [-] Error MySQL: {e}")

if __name__ == "__main__":
    print("[*] Iniciando Motor de Extracción Autónomo (Optimizado para Google Maps)...")
    
    industria_objetivo = "transporte y logística" 
    
    # Selecciona una ciudad al azar hoy para ahorrar llamadas y abarcar todo el país
    ciudad_aleatoria = random.choice(CIUDADES)
    
    leads_extraidos = motor_google_places(industria_objetivo, ciudad_aleatoria)
    
    # Filtro anti-duplicados por teléfono antes de enviar a BD
    leads_unicos = {l['telefono_whatsapp']: l for l in leads_extraidos}.values()
    
    print(f"\n[*] Proceso finalizado. Se enviarán {len(leads_unicos)} Leads para prospección telefónica.")
    persistir_leads(list(leads_unicos))
