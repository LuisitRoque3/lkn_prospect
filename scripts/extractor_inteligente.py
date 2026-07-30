import os
import time
import random
import requests
import re
import mysql.connector
from mysql.connector import Error
from urllib.parse import urlparse
from bs4 import BeautifulSoup
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

def enriquecer_con_mexicopymes(nombre, ubicacion):
    """
    Busca una empresa en mexicopymes.com y obtiene teléfono, email y sitio web adicionales de forma directa.
    """
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    # 1. Buscar en el buscador
    search_url = f"https://www.mexicopymes.com/buscar/?q={requests.utils.quote(nombre)}"
    try:
        res = requests.get(search_url, headers=headers, timeout=10)
        if res.status_code != 200:
            return {}
            
        soup = BeautifulSoup(res.text, 'html.parser')
        
        # Encontrar el primer enlace que tenga '/info/'
        profile_url = None
        for link in soup.find_all('a', href=True):
            href = link['href']
            if "/info/" in href:
                profile_url = href
                break
                
        if not profile_url:
            return {}
            
        # Asegurar URL absoluta
        if not profile_url.startswith("http"):
            profile_url = "https://mexicopymes.com" + profile_url if profile_url.startswith("/") else "https://mexicopymes.com/" + profile_url
            
        # Extraer el n_id del final de la URL del perfil
        n_id = profile_url.split("-")[-1]
        if not n_id or len(n_id) < 5:
            return {}
            
        # 2. Consultar la página del perfil para el Sitio Web
        res_profile = requests.get(profile_url, headers=headers, timeout=10)
        web = "N/A"
        if res_profile.status_code == 200:
            soup_profile = BeautifulSoup(res_profile.text, 'html.parser')
            for link in soup_profile.find_all('a', href=True):
                href = link['href']
                if "http" in href and "mexicopymes.com" not in href and "google.com" not in href and "waze.com" not in href and "facebook.com" not in href and "instagram.com" not in href and "linkedin.com" not in href:
                    web = href
                    break
                    
        # 3. Consultar endpoints AJAX para obtener datos reales
        ajax_headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
            "Accept": "application/json",
            "Referer": profile_url
        }
        
        telefono = "N/A"
        correo = "N/A"
        
        # AJAX Teléfono
        try:
            res_tel = requests.post("https://mexicopymes.com/ajax/mostrar_numero.php", data={"n_id": n_id, "n_type": "P1"}, headers=ajax_headers, timeout=8)
            if res_tel.status_code == 200:
                tel_json = res_tel.json()
                if tel_json.get("ok") and tel_json.get("value"):
                    raw_tel = tel_json.get("value")
                    telefono = "".join(filter(str.isdigit, raw_tel))
                    if not telefono.startswith("52") and len(telefono) == 10:
                        telefono = "52" + telefono
        except Exception as e:
            pass
            
        # AJAX Correo
        try:
            res_cor = requests.post("https://mexicopymes.com/ajax/mostrar_email.php", data={"n_id": n_id, "n_type": "E"}, headers=ajax_headers, timeout=8)
            if res_cor.status_code == 200:
                cor_json = res_cor.json()
                if cor_json.get("ok") and cor_json.get("value"):
                    correo = cor_json.get("value").strip()
        except Exception as e:
            pass
            
        return {
            "telefono": telefono if telefono != "N/A" else None,
            "correo": correo if correo != "N/A" else None,
            "web": web if web != "N/A" else None
        }
        
    except Exception as e:
        print(f"[-] Error al enriquecer con MexicoPymes para '{nombre}': {e}")
        
    return {}

# ==========================================
# BASE DE DATOS
# ==========================================
def persistir_leads(leads):
    if not leads: return
    
    # --- ENRIQUECIMIENTO CON MEXICOPYMES ---
    print(f"\n[*] Nutriendo {len(leads)} leads con MexicoPymes...")
    for idx, l in enumerate(leads):
        # Enriquecer si le falta el correo corporativo o la página web
        if not l.get('correo_corporativo') or l.get('correo_corporativo') == 'N/A' or not l.get('url_origen') or l.get('url_origen') == 'N/A':
            print(f" - [{idx+1}/{len(leads)}] Buscando datos para '{l['empresa']}'...")
            extra_info = enriquecer_con_mexicopymes(l['empresa'], l['ubicacion_local'])
            if extra_info:
                if extra_info.get('correo') and (not l.get('correo_corporativo') or l.get('correo_corporativo') == 'N/A'):
                    l['correo_corporativo'] = extra_info['correo']
                    print(f"   [+] Correo corporativo encontrado: {extra_info['correo']}")
                if extra_info.get('web') and (not l.get('url_origen') or l.get('url_origen') == 'N/A'):
                    l['url_origen'] = urlparse(extra_info['web']).netloc.replace('www.', '') if extra_info['web'] else 'N/A'
                    print(f"   [+] Sitio web oficial encontrado: {extra_info['web']}")
            time.sleep(0.5) # Rate limit friendly
            
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
