# API Gimnasio (PHP nativo)

API mínima en **PHP nativo** con **router simple**, **CORS** y respuestas **JSON**.

Endpoints actuales:

* `GET /` → mensaje informativo
* `GET /ping` → health check (`{"pong": true, "time": "<ISO8601>"}`)

Ruta del proyecto en XAMPP (Windows):

```
C:\xampp\htdocs\api\
```

Estructura esperada:

```
api/
├─ index.php
└─ .htaccess
```

---

## 1) Requisitos

* XAMPP con PHP 7.4+ (8.x ok)
* MySQL/MariaDB (opcional por ahora)
* Apache con `.htaccess` habilitado (o usar FallbackResource)

---

## 2) Archivo principal (`index.php`)

Tu `index.php` debe:

* Enviar headers JSON + CORS
* Implementar router que acepte: `PATH_INFO`, `?r=` y `REQUEST_URI` (para rewrite/fallback)
* Exponer `GET /` y `GET /ping`

> El router recomendado prioriza: `PATH_INFO` → `?r=` → `REQUEST_URI` relativa al directorio del script, y normaliza el path para que empiece con `/`.

---

## 3) Configurar Apache — ** (Rewrite con mod_rewrite)**

> **Esta es la configuración que implementamos en el proyecto.** 

**`C:\xampp\htdocs\api\.htaccess`**

```apache
Options -MultiViews
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /api/

  # Si existe archivo o carpeta, no reescribas
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # /api/ -> index.php
  RewriteRule ^$ index.php [L,QSA]

  # /api/lo-que-sea -> index.php?r=/lo-que-sea
  RewriteRule ^(.+)$ index.php?r=/$1 [L,QSA]
</IfModule>
```



## 4) Pruebas rápidas

### Navegador

* `http://localhost/api/`
* `http://localhost/api/ping`

### Fallback que siempre funciona (aunque no esté bien el rewrite)

* `http://localhost/api/index.php?r=/`
* `http://localhost/api/index.php?r=/ping`

### cURL (CMD/PowerShell)

```bash
curl "http://localhost/api/ping"
curl "http://localhost/api/"

# Fallback:
curl "http://localhost/api/index.php?r=/ping"
```

### Postman / Insomnia

* Método: **GET**
* URL: `http://localhost/api/ping`
* Enviar → debe devolver `{ "pong": true, "time": "..." }`

---

## 5) CORS y JSON

El `index.php` debe incluir:

* `Content-Type: application/json; charset=utf-8`
* `Access-Control-Allow-Origin: *`
* `Access-Control-Allow-Methods: GET, POST, OPTIONS`
* `Access-Control-Allow-Headers: Content-Type, Authorization`
* Respuesta `204` a `OPTIONS` (preflight)
* `json_encode(..., JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)` para evitar `\u` y `\/`

---

## 6) Base de datos (opcional, preparado)

Incluye helpers para MySQLi (conexión **singleton** y `utf8mb4`), aunque **no se usan** aún en estos endpoints. Cuando agregues `/clientes/estado` y `/asistencia`, ya está lista la función `db()`.

---


## 7) Próximos pasos 

* Agregar `GET /clientes/estado?dni=...`
* Agregar `POST /asistencia` (JSON: `{ "dni": "..." }`)
* Paginación en `GET /productos` (si vas a sumar catálogo)
