import time
import random
import requests
from bs4 import BeautifulSoup
from urllib.parse import urlparse
import mysql.connector
from mysql.connector import Error
from config import DB_CONFIG, GOOGLE_PLACES_API_KEY, CIUDADES, GIROS
# Importaciones locales se manejan dentro de las funciones para evitar importaciones circulares

def buscar_vacantes_web(giro, ciudad):
    """
    Busca vacantes activas en portales de empleo de forma pública (Scraping de bajo costo/free).
    Retorna una lista de diccionarios con {'empresa': ..., 'puesto': ..., 'ciudad': ...}
    """
    print(f"\n[EMPLEOS] Buscando vacantes de '{giro}' en '{ciudad}'...")
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36"
    }
    
    # Mapeo de giros a palabras clave de vacantes
    puesto_busqueda = giro
    if "logística" in giro:
        puesto_busqueda = "chofer OR logistica OR almacenista"
    elif "eléctricas" in giro:
        puesto_busqueda = "electricista OR instalaciones"
    elif "mecánicos" in giro:
        puesto_busqueda = "mecanico OR ayudante general"
    elif "seguridad" in giro:
        puesto_busqueda = "guardia de seguridad OR oficial de seguridad"
        
    url = f"https://mx.jooble.org/SearchResult?ukw={puesto_busqueda}&rgns={ciudad}"
    
    companias_hiring = []
    try:
        response = requests.get(url, headers=headers, timeout=15)
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, 'html.parser')
            # Las tarjetas de empleo en Jooble suelen tener clases de artículo
            job_cards = soup.find_all('article')
            
            for card in job_cards[:10]: # Limitar a las primeras 10 para cuidar la carga
                try:
                    # Buscar nombre de la empresa
                    company_elem = card.find('span', {'class': lambda x: x and 'company' in x.lower()}) or card.find('div', {'class': lambda x: x and 'company' in x.lower()})
                    if not company_elem:
                        # Fallback a clases comunes
                        company_elem = card.select_one('[data-company]')
                        
                    company_name = company_elem.text.strip() if company_elem else ""
                    
                    # Si no encuentra por clase, buscar en texto
                    if not company_name:
                        for span in card.find_all(['span', 'p', 'div']):
                            if span.get('class') and any('company' in c for c in span.get('class')):
                                company_name = span.text.strip()
                                break
                                
                    # Buscar el título del puesto
                    title_elem = card.find('h2') or card.find('a', {'href': True})
                    job_title = title_elem.text.strip() if title_elem else "Vacante"
                    
                    # Filtros críticos: Evitar "Confidencial", "Anonima" o vacíos
                    if company_name and len(company_name) > 2 and not any(x in company_name.lower() for x in ["confidencial", "anonimo", "empresa líder", "importante empresa"]):
                        companias_hiring.append({
                            'empresa': company_name,
                            'puesto': job_title,
                            'ciudad': ciudad
                        })
                except Exception as card_err:
                    continue
    except Exception as e:
        print(f"[-] Error al consultar portal de empleo: {e}")
        
    print(f"[EMPLEOS] Se encontraron {len(companias_hiring)} ofertas de empleo viables con nombres de empresas.")
    return companias_hiring

def motor_empleos_y_enriquecimiento(giro, ciudad):
    """
    Ejecuta el flujo completo: busca empresas contratando y resuelve sus datos de contacto en Google Maps.
    """
    empresas_contratando = buscar_vacantes_web(giro, ciudad)
    leads_enriquecidos = []
    
    for emp in empresas_contratando:
        nombre_empresa = emp['empresa']
        puesto = emp['puesto']
        print(f"\n[ENRIQUECER] Buscando datos de contacto para: '{nombre_empresa}' en '{ciudad}'...")
        
        # Usamos Google Places para buscar esta empresa específica
        query = f"{nombre_empresa} {ciudad}"
        url = "https://maps.googleapis.com/maps/api/place/textsearch/json"
        params = {"query": query, "key": GOOGLE_PLACES_API_KEY}
        
        try:
            response = requests.get(url, params=params, timeout=10)
            if response.status_code == 200:
                results = response.json().get('results', [])
                if results:
                    best_match = results[0] # El primer resultado suele ser la coincidencia exacta
                    place_id = best_match.get('place_id')
                    
                    # Obtener detalles
                    detail_url = f"https://maps.googleapis.com/maps/api/place/details/json?place_id={place_id}&fields=name,website,formatted_phone_number,formatted_address&key={GOOGLE_PLACES_API_KEY}"
                    det_res = requests.get(detail_url, timeout=10)
                    if det_res.status_code == 200:
                        detalles = det_res.json().get('result', {})
                        telefono = detalles.get('formatted_phone_number', '')
                        web = detalles.get('website', '')
                        
                        if telefono: # Filtro crítico: Debe tener teléfono
                            dominio = urlparse(web).netloc.replace('www.', '') if web else 'N/A'
                            
                            leads_enriquecidos.append({
                                'empresa': nombre_empresa,
                                'giro_negocio': giro,
                                'director_nombre': 'Dueño / Encargado',
                                'correo_corporativo': 'N/A',
                                'telefono_whatsapp': telefono,
                                'tamano_estimado': 'Mediana (Hiring)',
                                'ubicacion_local': detalles.get('formatted_address', emp['ciudad']),
                                'url_origen': dominio,
                                'fuente_descubrimiento': 'empleo',
                                'vacantes_activas': 1,
                                'puestos_buscados': puesto,
                                'tamano_empresa': 'Contratando',
                                'origen_detalles': f"Vacante: {puesto}"
                            })
                            print(f"[ENRIQUECER] [+] ¡Encontrado teléfono!: {telefono}")
            time.sleep(0.5) # Cuidar cuotas
        except Exception as e:
            print(f"[-] Error enriqueciendo {nombre_empresa}: {e}")
            
    return leads_enriquecidos

if __name__ == "__main__":
    import sys
    import os
    # Permitir importación limpia de persistir_leads
    sys.path.append(os.path.dirname(os.path.abspath(__file__)))
    from extractor_inteligente import persistir_leads
    
    print("[*] Iniciando Extractor de Empleos de forma independiente...")
    giro_aleatorio = random.choice(GIROS)
    ciudad_aleatoria = random.choice(CIUDADES)
    
    leads = motor_empleos_y_enriquecimiento(giro_aleatorio, ciudad_aleatoria)
    leads_unicos = {l['telefono_whatsapp']: l for l in leads}.values()
    persistir_leads(list(leads_unicos))
