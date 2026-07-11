import time
import random
import requests
from urllib.parse import urlparse
import mysql.connector
from mysql.connector import Error
from config import DB_CONFIG, GOOGLE_PLACES_API_KEY, DENUE_API_TOKEN, CIUDADES, GIROS

# Mapeo de ciudades a códigos de entidad federativa de México (INEGI)
ENTIDADES_INEGI = {
    "CDMX": "09",
    "Monterrey": "19", # Nuevo León
    "Guadalajara": "14", # Jalisco
    "Puebla": "21",
    "Tijuana": "02", # Baja California
    "Leon": "11", # Guanajuato
    "Querétaro": "22",
    "Toluca": "15", # Estado de México
    "San Luis Potosí": "24",
    "Mérida": "31", # Yucatán
    "Aguascalientes": "01",
    "Saltillo": "05", # Coahuila
    "Hermosillo": "26", # Sonora
    "Mexicali": "02",
    "Culiacán": "25", # Sinaloa
    "Chihuahua": "08"
}

def consultar_denue_inegi(giro, ciudad):
    """
    Busca empresas registradas formalmente en el DENUE del INEGI.
    """
    entidad_code = ENTIDADES_INEGI.get(ciudad, "00") # "00" busca a nivel nacional
    print(f"\n[DENUE] Buscando negocios de '{giro}' en '{ciudad}' (Entidad: {entidad_code})...")
    
    # Adaptar giro a términos de búsqueda simplificados para el DENUE
    termino_busqueda = giro
    if "logística" in giro: termino_busqueda = "logistica"
    elif "eléctricas" in giro: termino_busqueda = "electricas"
    elif "mecánicos" in giro: termino_busqueda = "mecanico"
    elif "seguridad" in giro: termino_busqueda = "seguridad"
    
    # URL de la API del DENUE para búsqueda por actividad y entidad federativa
    # /BuscarEntidad/{termino}/{entidad}/{inicio}/{fin}/{token}
    url = f"https://www.inegi.org.mx/app/api/denue/v1/consulta/BuscarEntidad/{termino_busqueda}/{entidad_code}/1/20/{DENUE_API_TOKEN}"
    
    empresas_denue = []
    try:
        response = requests.get(url, timeout=15)
        if response.status_code == 200:
            results = response.json()
            # A veces la API retorna mensajes de error en formato JSON si el token no es válido o no hay registros
            if isinstance(results, list):
                for r in results:
                    nombre = r.get('Nombre') or r.get('Razon_social')
                    tamano = r.get('Estrato') or 'No especificado'
                    direccion = f"{r.get('Calle', '')} {r.get('Num_Exterior', '')}, {r.get('Colonia', '')}, {r.get('Municipio', '')}, {ciudad}"
                    
                    if nombre and len(nombre) > 2:
                        empresas_denue.append({
                            'empresa': nombre,
                            'tamano_empresa': tamano,
                            'direccion': direccion,
                            'ciudad': ciudad
                        })
            else:
                print(f"[DENUE] Advertencia o error de la API: {results}")
    except Exception as e:
        print(f"[-] Error al consultar la API del DENUE: {e}")
        
    print(f"[DENUE] Encontró {len(empresas_denue)} empresas formales en el registro.")
    return empresas_denue

def motor_denue_y_enriquecimiento(giro, ciudad):
    """
    Ejecuta el flujo: consulta el DENUE y busca en Google Places para rellenar los datos de contacto.
    """
    empresas_denue = consultar_denue_inegi(giro, ciudad)
    leads_enriquecidos = []
    
    for emp in empresas_denue:
        nombre_empresa = emp['empresa']
        tamano = emp['tamano_empresa']
        print(f"\n[ENRIQUECER] Buscando contacto de DENUE lead: '{nombre_empresa}' en '{ciudad}'...")
        
        # Buscamos en Google Places
        query = f"{nombre_empresa} {ciudad}"
        url = "https://maps.googleapis.com/maps/api/place/textsearch/json"
        params = {"query": query, "key": GOOGLE_PLACES_API_KEY}
        
        try:
            response = requests.get(url, params=params, timeout=10)
            if response.status_code == 200:
                results = response.json().get('results', [])
                if results:
                    best_match = results[0]
                    place_id = best_match.get('place_id')
                    
                    # Detalles
                    detail_url = f"https://maps.googleapis.com/maps/api/place/details/json?place_id={place_id}&fields=name,website,formatted_phone_number,formatted_address&key={GOOGLE_PLACES_API_KEY}"
                    det_res = requests.get(detail_url, timeout=10)
                    if det_res.status_code == 200:
                        detalles = det_res.json().get('result', {})
                        telefono = detalles.get('formatted_phone_number', '')
                        web = detalles.get('website', '')
                        
                        if telefono: # Filtro crítico
                            dominio = urlparse(web).netloc.replace('www.', '') if web else 'N/A'
                            
                            leads_enriquecidos.append({
                                'empresa': nombre_empresa,
                                'giro_negocio': giro,
                                'director_nombre': 'Dueño / Encargado',
                                'correo_corporativo': 'N/A',
                                'telefono_whatsapp': telefono,
                                'tamano_estimado': tamano, # Del DENUE
                                'ubicacion_local': detalles.get('formatted_address', emp['direccion']),
                                'url_origen': dominio,
                                'fuente_descubrimiento': 'denue',
                                'vacantes_activas': 0,
                                'puestos_buscados': 'N/A',
                                'tamano_empresa': tamano,
                                'origen_detalles': f"Registro oficial DENUE (INEGI)"
                            })
                            print(f"[ENRIQUECER] [+] ¡Encontrado teléfono!: {telefono}")
            time.sleep(0.5)
        except Exception as e:
            print(f"[-] Error enriqueciendo lead de DENUE {nombre_empresa}: {e}")
            
    return leads_enriquecidos

if __name__ == "__main__":
    import sys
    import os
    sys.path.append(os.path.dirname(os.path.abspath(__file__)))
    from extractor_inteligente import persistir_leads
    
    print("[*] Iniciando Extractor DENUE-INEGI de forma independiente...")
    giro_aleatorio = random.choice(GIROS)
    ciudad_aleatoria = random.choice(CIUDADES)
    
    leads = motor_denue_y_enriquecimiento(giro_aleatorio, ciudad_aleatoria)
    leads_unicos = {l['telefono_whatsapp']: l for l in leads}.values()
    persistir_leads(list(leads_unicos))
