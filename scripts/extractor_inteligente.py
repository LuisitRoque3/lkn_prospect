import os
import time
import random
import requests
import mysql.connector
from mysql.connector import Error
from urllib.parse import urlparse
from config import DB_CONFIG, GOOGLE_PLACES_API_KEY, CIUDADES, GIROS

# ==========================================
# MOTOR DE BÚSQUEDA NACIONAL (Google Maps)
# ==========================================
def motor_google_places(giro, ubicacion):
    """Busca negocios locales en Google Maps optimizando el uso de la API"""
    query = f"empresas de {giro} en {ubicacion}"
    print(f"\n[GOOGLE MAPS] Buscando: '{query}'...")
    url = "https://maps.googleapis.com/maps/api/place/textsearch/json"
    params = {"query": query, "key": GOOGLE_PLACES_API_KEY}
    
    empresas_google = []
    try:
        response = requests.get(url, params=params, timeout=15)
        if response.status_code == 200:
            results = response.json().get('results', [])
            
            # Procesar los primeros 15 resultados para cuidar consumo de API
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
                    
                    if telefono: # Filtro crítico: Solo con número
                        dominio = urlparse(web).netloc.replace('www.', '') if web else 'N/A'
                        dir_exacta = detalles.get('formatted_address', direccion)
                        
                        empresas_google.append({
                            'empresa': nombre,
                            'giro_negocio': giro,
                            'director_nombre': 'Dueño / Encargado',
                            'correo_corporativo': 'N/A',
                            'telefono_whatsapp': telefono,
                            'tamano_estimado': 'Google Maps',
                            'ubicacion_local': dir_exacta,
                            'url_origen': dominio,
                            'fuente_descubrimiento': 'maps',
                            'vacantes_activas': 0,
                            'puestos_buscados': 'N/A',
                            'tamano_empresa': 'N/A',
                            'origen_detalles': 'Google Maps Search'
                        })
                time.sleep(0.3) # Rate limit friendly
    except Exception as e:
        print(f"[-] Error en Motor Google Places: {e}")
        
    print(f"[GOOGLE MAPS] Encontró {len(empresas_google)} empresas con número telefónico.")
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
                   (empresa, giro_negocio, director_nombre, correo_corporativo, telefono_whatsapp, 
                    tamano_estimado, ubicacion_local, url_origen, fuente_descubrimiento, 
                    vacantes_activas, puestos_buscados, tamano_empresa, origen_detalles, user_id)
                   VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        
        valores = [
            (
                l['empresa'], l['giro_negocio'], l['director_nombre'], l['correo_corporativo'], 
                l['telefono_whatsapp'], l['tamano_estimado'], l['ubicacion_local'], l['url_origen'],
                l['fuente_descubrimiento'], l['vacantes_activas'], l['puestos_buscados'], 
                l['tamano_empresa'], l['origen_detalles'], l.get('user_id')
            ) 
            for l in leads
        ]
        cursor.executemany(query, valores)
        conexion.commit()
        print(f"[BD] [+] ¡Registros inyectados al CRM de manera exitosa!: {cursor.rowcount}")
    except Error as e:
        print(f"[BD] [-] Error MySQL: {e}")
    finally:
        if 'conexion' in locals() and conexion.is_connected():
            cursor.close()
            conexion.close()

if __name__ == "__main__":
    print("[*] Iniciando Extractor Google Maps de forma independiente...")
    giro_aleatorio = random.choice(GIROS)
    ciudad_aleatoria = random.choice(CIUDADES)
    
    leads = motor_google_places(giro_aleatorio, ciudad_aleatoria)
    leads_unicos = {l['telefono_whatsapp']: l for l in leads}.values()
    persistir_leads(list(leads_unicos))
