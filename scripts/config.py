# --- CONFIGURACIÓN COMPARTIDA DEL MOTOR DE EXTRACCIÓN ---

# Configuración de Base de Datos
DB_CONFIG = {
    'host': 'localhost', # O '217.77.3.82' según el entorno
    'database': 'dbScrap',
    'user': 'adminDbScrap',
    'password': 'RNN7R7Re0pzuZ5napGZR'
}

# Llaves de API
GOOGLE_PLACES_API_KEY = 'AIzaSyBfm6IRK4y3sSEIzDN5Me-S67W2JCvyw0Y'
JOOBLE_API_KEY = '' # El usuario la puede rellenar o usar fallback sin API de pago
DENUE_API_TOKEN = '644cb744-9d96-47bb-a8ab-1dceddd73850' # Token privado del usuario

# Catálogo de Giros a Mapear
GIROS = [
    "transporte y logística",
    "instalaciones eléctricas",
    "talleres mecánicos",
    "agencias de seguridad privada",
    "climatización y aire acondicionado",
    "servicios de limpieza industrial",
    "construcción y contratistas",
    "distribuidores mayoristas"
]

# Catálogo de Ciudades
CIUDADES = [
    "CDMX", "Monterrey", "Guadalajara", "Puebla", "Tijuana", 
    "Leon", "Querétaro", "Toluca", "San Luis Potosí", "Mérida", 
    "Aguascalientes", "Saltillo", "Hermosillo", "Mexicali", "Culiacán", "Chihuahua"
]
