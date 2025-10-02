<?php
// ===============================================
// API mínima (PHP nativo) con FallbackResource
// Archivo: C:\xampp\htdocs\api\api.php
// ===============================================

// JSON UTF-8 + CORS básico (ajusta Origin en producción)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ===== Config DB (por si agregás endpoints que la usen) =====
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gimnasio');
define('DB_CHARSET', 'utf8mb4');

function db(): mysqli {
    static $cx = null;
    if ($cx instanceof mysqli) return $cx;
    $cx = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($cx->connect_error) {
        http_response_code(500);
        echo json_encode(['error'=>'No se pudo conectar a la BD','detalle'=>$cx->connect_error], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        exit;
    }
    $cx->set_charset(DB_CHARSET);
    return $cx;
}

// ===== Helpers JSON =====
function json($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function bad_request($msg='Solicitud inválida'){ json(['error'=>$msg],400); }
function not_found($msg='No encontrado'){ json(['error'=>$msg],404); }

// ===== Router robusto =====
// Prioridad: PATH_INFO -> ?r= -> REQUEST_URI relativa al dir de api.php (para FallbackResource)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_SERVER['PATH_INFO'] ?? ($_GET['r'] ?? null);

if ($path === null) {
    // p.ej. REQUEST_URI = /api/ping?x=1
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    // base = /api (directorio real donde vive api.php)
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($base && str_starts_with($reqPath, $base)) {
        $reqPath = substr($reqPath, strlen($base));
    }
    $path = $reqPath === '' ? '/' : $reqPath;
}
if ($path === '' || $path[0] !== '/') $path = '/' . $path;

// ===== Endpoints =====

// GET /ping  -> chequeo de vida
if ($method === 'GET' && $path === '/ping') {
    json(['pong' => true, 'time' => date('c')]); // ISO 8601
}

// GET /      -> raíz informativa
if ($method === 'GET' && $path === '/') {
    json(['ok'=>true, 'msg'=>'API del gimnasio OK. Usa GET /ping para chequeo.']);
}

// Si nada coincide
not_found('Ruta no encontrada. Proba GET /ping');
