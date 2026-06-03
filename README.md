# API Gimnasio (PHP nativo)

API REST en **PHP nativo** (sin framework) con router simple, CORS controlado y respuestas JSON. Pensada para correr en XAMPP/Apache sobre MySQL/MariaDB. Proyecto de la Tecnicatura en Software.

El archivo principal es **`api.php`**.

---

## 1) Requisitos

* PHP 7.4+ (8.x ok) con extensión `mysqli`
* MySQL / MariaDB
* Apache con `mod_rewrite` y `.htaccess` habilitado (o llamar a `api.php?r=/ruta` directamente)

---

## 2) Instalación

```bash
# 1. Copiar el proyecto al htdocs (ej. C:\xampp\htdocs\api\)
git clone https://github.com/yanelricarte/Tecnicatura-Software.git api

# 2. Importar la base de datos
mysql -u root -p < gimnasio.sql
```

---

## 3) Configuración por variables de entorno

La API lee su configuración del entorno (con valores por defecto para desarrollo local):

| Variable | Default | Descripción |
|----------|---------|-------------|
| `DB_HOST` | `127.0.0.1` | Host de la base |
| `DB_USER` | `root` | Usuario MySQL |
| `DB_PASS` | *(vacío)* | Contraseña MySQL |
| `DB_NAME` | `gimnasio` | Nombre de la base |
| `ALLOWED_ORIGINS` | `http://localhost` | Orígenes permitidos para CORS (separados por coma) |
| `API_KEY` | *(vacío)* | Clave para operaciones de escritura. **Sin esta variable, POST/PUT/DELETE quedan bloqueados.** |

> **Seguridad:** las lecturas (`GET`) son públicas; **crear, editar y borrar exigen el header `X-API-Key: <tu_clave>`**. El CORS usa lista blanca, no `*`.

---

## 4) Endpoints

**Lectura (GET, sin auth):**

* `GET /` → mensaje informativo
* `GET /ping` → health check (`{"pong": true, "time": "<ISO8601>"}`)
* `GET /clientes/estado?dni=...`
* `GET /asistencias`
* `GET /ejercicios`
* `GET /rutinas` · `GET /rutinas/{id}`

**Escritura (requiere `X-API-Key`):**

* `POST /asistencia` (JSON: `{ "dni": "..." }`)
* `POST /ejercicios` · `PUT /ejercicios/{id}` · `DELETE /ejercicios/{id}`
* `POST /rutinas` · `PUT /rutinas/{id}` · `DELETE /rutinas/{id}`
* `POST /progresos`

> Listas con paginación vía `?page=` y `?size=` (máx. 100).

---

## 5) Pruebas rápidas

```bash
# Lectura (abierta)
curl "http://localhost/api/ping"
curl "http://localhost/api/clientes"

# Fallback sin rewrite
curl "http://localhost/api/api.php?r=/ping"

# Escritura (con API key)
curl -X POST "http://localhost/api/asistencia" \
     -H "X-API-Key: tu_clave" \
     -H "Content-Type: application/json" \
     -d '{"dni":"12345678"}'
```

La colección de Postman está en `postman/`.

---

## 6) Notas

* Todas las consultas usan **sentencias preparadas** (anti SQL injection).
* Los errores internos se registran en el log del servidor; al cliente solo se le devuelve un mensaje genérico.
* `gimnasio.sql` incluye datos de ejemplo ficticios para probar.
