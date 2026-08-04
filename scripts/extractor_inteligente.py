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

def obtener_datos_places(query, max_results=20, page_token=None):
    """
    Realiza una búsqueda de lugares en Google Places utilizando únicamente la Nueva API.
    Retorna (resultados_formateados, next_page_token)
    """
    url_new = "https://places.googleapis.com/v1/places:searchText"
    headers_new = {
        "Content-Type": "application/json",
        "X-Goog-Api-Key": GOOGLE_PLACES_API_KEY,
        "X-Goog-FieldMask": "places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,nextPageToken"
    }
    body_new = {
        "textQuery": query,
        "languageCode": "es-MX",
        "maxResultCount": max_results
    }
    if page_token:
        body_new["pageToken"] = page_token

    try:
        response = requests.post(url_new, json=body_new, headers=headers_new, timeout=15)
        if response.status_code == 200:
            res_json = response.json()
            places = res_json.get('places', [])
            next_token = res_json.get('nextPageToken')
            
            resultados = []
            for p in places:
                resultados.append({
                    'place_id': p.get('id', ''),
                    'name': p.get('displayName', {}).get('text', ''),
                    'formatted_address': p.get('formattedAddress', ''),
                    'formatted_phone_number': p.get('nationalPhoneNumber', ''),
                    'website': p.get('websiteUri', '')
                })
            return resultados, next_token
        else:
            print(f"[-] Error en API Nueva Google Places: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"[-] Excepción de red en API Nueva Google Places: {e}")
        
    return [], None

# ==========================================
# MOTOR DE BÚSQUEDA NACIONAL (Google Maps)
# ==========================================
def motor_google_places(giro, ubicacion):
    """Busca negocios locales en Google Maps optimizando el uso de la API y leyendo hasta 3 páginas de resultados"""
    query = f"empresas de {giro} en {ubicacion}"
    print(f"\n[GOOGLE MAPS] Buscando: '{query}'...")
    
    empresas_google = []
    pagetoken = None
    paginas_leidas = 0
    max_paginas = 3
    
    try:
        while paginas_leidas < max_paginas:
            if pagetoken:
                # Esperar 2 segundos para que el token de Google se active
                time.sleep(2)
                print(f"[GOOGLE MAPS] Solicitando página {paginas_leidas + 1}...")
            
            results, pagetoken = obtener_datos_places(query, page_token=pagetoken)
            if not results:
                break
                
            # Procesar los resultados de la página actual
            for r in results:
                nombre = r.get('name')
                telefono = r.get('formatted_phone_number')
                web = r.get('website')
                direccion = r.get('formatted_address', ubicacion)
                
                if telefono: # Filtro crítico: Solo con número
                    dominio = urlparse(web).netloc.replace('www.', '') if web else 'N/A'
                    
                    empresas_google.append({
                        'empresa': nombre,
                        'giro_negocio': giro,
                        'director_nombre': 'Dueño / Encargado',
                        'correo_corporativo': 'N/A',
                        'telefono_whatsapp': telefono,
                        'tamano_estimado': 'Google Maps',
                        'ubicacion_local': direccion,
                        'url_origen': dominio,
                        'fuente_descubrimiento': 'maps',
                        'vacantes_activas': 0,
                        'puestos_buscados': 'N/A',
                        'tamano_empresa': 'N/A',
                        'origen_detalles': 'Google Maps Search'
                    })
            
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

def escanear_sitio_web_oficial(url):
    """
    Rastrea el sitio web oficial del negocio para extraer WhatsApp, correos y redes sociales.
    """
    if not url or url == 'N/A':
        return {}
        
    if not url.startswith("http"):
        url = "https://" + url
        
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    try:
        response = requests.get(url, headers=headers, timeout=8)
        if response.status_code != 200:
            return {}
            
        soup = BeautifulSoup(response.text, 'html.parser')
        html_content = response.text
        
        # 1. Buscar enlaces de WhatsApp (wa.me, api.whatsapp.com, etc.)
        whatsapp_tel = None
        for link in soup.find_all('a', href=True):
            href = link['href']
            if 'wa.me' in href or 'api.whatsapp.com/send' in href or 'whatsapp.com/send' in href:
                # Evitar basura de parámetros de consulta (ej: %20 -> 20) limpiando el query string
                base_url = href.split('?')[0]
                
                # Si contiene parámetro explícito de teléfono
                if 'phone=' in href:
                    from urllib.parse import urlparse, parse_qs
                    try:
                        query_params = parse_qs(urlparse(href).query)
                        phone_val = query_params.get('phone')
                        if phone_val:
                            base_url = phone_val[0]
                    except Exception:
                        pass
                
                phone_digits = "".join(filter(str.isdigit, base_url))
                if len(phone_digits) >= 10:
                    if len(phone_digits) == 10:
                        whatsapp_tel = "52" + phone_digits
                    else:
                        whatsapp_tel = phone_digits
                    break
                    
        # 2. Buscar correos electrónicos en el texto
        email_pattern = r'[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'
        emails = re.findall(email_pattern, html_content)
        valid_emails = [e for e in emails if not any(x in e.lower() for x in ['.png', '.jpg', '.jpeg', '.gif', 'bootstrap', 'jquery', 'wix', 'wordpress'])]
        correo = valid_emails[0] if valid_emails else None
        
        return {
            "whatsapp": whatsapp_tel,
            "correo": correo
        }
        
    except Exception:
        pass
        
    return {}

# ==========================================
# BASE DE DATOS
# ==========================================
def persistir_leads(leads):
    if not leads: return
    
    # --- ENRIQUECIMIENTO CON MEXICOPYMES Y SITIOS WEB OFICIALES ---
    print(f"\n[*] Nutriendo {len(leads)} leads...")
    for idx, l in enumerate(leads):
        # A. Enriquecer desde MexicoPymes si falta correo o sitio web
        if not l.get('correo_corporativo') or l.get('correo_corporativo') == 'N/A' or not l.get('url_origen') or l.get('url_origen') == 'N/A':
            print(f" - [{idx+1}/{len(leads)}] Buscando en MexicoPymes para '{l['empresa']}'...")
            extra_info = enriquecer_con_mexicopymes(l['empresa'], l['ubicacion_local'])
            if extra_info:
                if extra_info.get('correo') and (not l.get('correo_corporativo') or l.get('correo_corporativo') == 'N/A'):
                    l['correo_corporativo'] = extra_info['correo']
                    print(f"   [+] Correo corporativo encontrado (MexicoPymes): {extra_info['correo']}")
                if extra_info.get('web') and (not l.get('url_origen') or l.get('url_origen') == 'N/A'):
                    l['url_origen'] = urlparse(extra_info['web']).netloc.replace('www.', '') if extra_info['web'] else 'N/A'
                    print(f"   [+] Sitio web oficial encontrado (MexicoPymes): {extra_info['web']}")
            time.sleep(0.4)
            
        # B. Scraping directo del sitio web oficial si está disponible
        web_url = l.get('url_origen')
        if web_url and web_url != 'N/A':
            print(f"   [*] Escaneando sitio web oficial: {web_url}...")
            site_info = escanear_sitio_web_oficial(web_url)
            if site_info:
                if site_info.get('whatsapp'):
                    l['telefono_whatsapp'] = site_info['whatsapp']
                    print(f"   [+] WhatsApp directo detectado en su web: {site_info['whatsapp']}")
                if site_info.get('correo') and (not l.get('correo_corporativo') or l.get('correo_corporativo') == 'N/A'):
                    l['correo_corporativo'] = site_info['correo']
                    print(f"   [+] Correo corporativo detectado en su web: {site_info['correo']}")
            
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
                     actualizado_at = NOW()"""
        
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
