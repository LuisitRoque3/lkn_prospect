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
    """Busca negocios locales en Google Maps optimizando el uso de la API y leyendo hasta 3 páginas de resultados"""
    query = f"empresas de {giro} en {ubicacion}"
    print(f"\n[GOOGLE MAPS] Buscando: '{query}'...")
    url = "https://maps.googleapis.com/maps/api/place/textsearch/json"
    
    empresas_google = []
    pagetoken = None
    paginas_leidas = 0
    max_paginas = 3
    
    try:
        while paginas_leidas < max_paginas:
            if pagetoken:
                # Esperar 2 segundos para que el token de Google se active
                time.sleep(2)
                params = {"pagetoken": pagetoken, "key": GOOGLE_PLACES_API_KEY}
                print(f"[GOOGLE MAPS] Solicitando página {paginas_leidas + 1}...")
            else:
                params = {"query": query, "key": GOOGLE_PLACES_API_KEY}
            
            response = requests.get(url, params=params, timeout=15)
            if response.status_code != 200:
                print(f"[-] Error HTTP {response.status_code} en Google Places")
                break
                
            res_json = response.json()
            results = res_json.get('results', [])
            pagetoken = res_json.get('next_page_token')
            
            # Procesar los resultados de la página actual
            for r in results:
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
                time.sleep(0.35) # Rate limit friendly
            
            paginas_leidas += 1
            if not pagetoken:
                break
                
    except Exception as e:
        print(f"[-] Error en Motor Google Places: {e}")
        
    print(f"[GOOGLE MAPS] Encontró {len(empresas_google)} empresas con número telefónico en total ({paginas_leidas} páginas).")
    return empresas_google

# ==========================================
# BASE DE DATOS
# ==========================================
def persistir_leads(leads):
    if not leads: return
    try:
        conexion = mysql.connector.connect(**DB_CONFIG)
        cursor = conexion.cursor()
        query = """INSERT INTO prospectos_scrapping 
                   (empresa, giro_negocio, director_nombre, correo_corporativo, telefono_whatsapp, 
                    tamano_estimado, ubicacion_local, url_origen, fuente_descubrimiento, 
                    vacantes_activas, puestos_buscados, tamano_empresa, origen_detalles, user_id, organizacion_id)
                   VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                   ON DUPLICATE KEY UPDATE
                     empresa = IF(VALUES(empresa) != 'N/A' AND VALUES(empresa) != '', VALUES(empresa), empresa),
                     director_nombre = IF(VALUES(director_nombre) != 'Dueño / Encargado' AND VALUES(director_nombre) != '', VALUES(director_nombre), director_nombre),
                     correo_corporativo = IF(VALUES(correo_corporativo) != 'N/A' AND VALUES(correo_corporativo) != '', VALUES(correo_corporativo), correo_corporativo),
                     url_origen = IF(VALUES(url_origen) != 'N/A' AND VALUES(url_origen) != '', VALUES(url_origen), url_origen),
                     vacantes_activas = GREATEST(vacantes_activas, VALUES(vacantes_activas)),
                     puestos_buscados = IF(VALUES(puestos_buscados) != 'N/A' AND VALUES(puestos_buscados) != '', VALUES(puestos_buscados), puestos_buscados),
                     updated_at = NOW()"""
        
        valores = [
            (
                l['empresa'], l['giro_negocio'], l['director_nombre'], l['correo_corporativo'], 
                l['telefono_whatsapp'], l['tamano_estimado'], l['ubicacion_local'], l['url_origen'],
                l['fuente_descubrimiento'], l['vacantes_activas'], l['puestos_buscados'], 
                l['tamano_empresa'], l['origen_detalles'], l.get('user_id'), l.get('organizacion_id')
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
