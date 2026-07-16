# Directrices de Optimización de Rendimiento y Escala - Desarrollo LKN

Este documento define las reglas de diseño de código y consultas de base de datos obligatorias para todos los proyectos dentro de la carpeta "Desarrollo LKN", garantizando estabilidad y latencia mínima a gran escala.

---

## 1. Reglas de Base de Datos y Consultas (MySQL)

* **Prohibido realizar `distinct()` directos sobre tablas transaccionales masivas**:
  * Cualquier consulta para obtener listados únicos de giros, ciudades o estados en tablas de leads (como `prospectos_scrapping`) debe implementarse usando la caché de Laravel (`Cache::remember`) por un mínimo de 30 minutos, o delegarse a tablas de catálogos independientes.
* **Uso obligatorio de Índices**:
  * Todo campo utilizado en cláusulas `where()` frecuentes de filtrado (como `organizacion_id`, `user_id`, `estado_contacto`) debe contar con un índice en la migración de base de datos.
* **Búsquedas de Texto Optimadas**:
  * Evitar el uso de `LIKE '%termino%'` a gran escala. Utilizar índices `FULLTEXT` y consultas `MATCH() AGAINST()` en campos de búsqueda como nombre de empresa, director o correos.
* **Paginación por Cursor**:
  * Para tablas con más de 100,000 registros, evitar la paginación tradicional basada en `offset` (`paginate()`). Utilizar `cursorPaginate()` en su lugar para mantener tiempos de respuesta constantes.

---

## 2. Gestión de Sesiones y Estado en Livewire

* **Evitar base de datos para caché volátil**:
  * Si `SESSION_DRIVER` y `CACHE_STORE` están configurados en `database`, asegurar que el host de base de datos (`DB_HOST`) esté configurado como `127.0.0.1` en entornos locales/monolíticos para evitar la latencia de inspección de red y firewalls.
* **Mount vs Render**:
  * Las consultas SQL de catálogos o conteos no reactivos deben ejecutarse una sola vez en el método `mount()` del componente y guardarse en propiedades del mismo. Nunca deben realizarse consultas pesadas dentro del método `render()`.
