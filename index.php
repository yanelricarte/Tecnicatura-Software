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

// ===== Config DB (para endpoints que la usen) =====
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
function json_out($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
// Acepta código opcional (arreglo clave)
function bad_request(string $msg='Solicitud inválida', int $code=400){ json_out(['error'=>$msg], $code); }
function not_found(string $msg='No encontrado'){ json_out(['error'=>$msg], 404); }

// Param de query obligatorio
function req_q(string $k): string {
    if (!isset($_GET[$k]) || trim((string)$_GET[$k]) === '') {
        bad_request("Falta parámetro: $k", 400);
    }
    return trim((string)$_GET[$k]);
}
// Validador de DNI (7–10 dígitos)
function is_dni(string $v): bool { return (bool)preg_match('/^\d{7,10}$/', $v); }

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
    json_out(['pong' => true, 'time' => date('c')]); // ISO 8601
}

// GET /      -> raíz informativa
if ($method === 'GET' && $path === '/') {
    json_out(['ok'=>true, 'msg'=>'API del gimnasio OK. Usa GET /ping para chequeo.']);
}

// ----------------- Endpoint: GET /clientes/estado -------------------
// http://localhost/api/clientes/estado?dni=12345678
//Me responde: 
//{"dni":"12345678","nombre":"Ana Pérez","vence":"2025-12-05","dias_restantes":59,"activa":true}

//Ejemplo: /clientes/estado?dni=12345678
if ($method === 'GET' && $path === '/clientes/estado') {
    $dni = req_q('dni');
    if (!is_dni($dni)) bad_request('DNI inválido', 422);

    $cx = db();
    $st = $cx->prepare("SELECT nombre, membresia_vence FROM clientes WHERE dni=?");
    $st->bind_param('s', $dni);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if (!$r) bad_request('Cliente no encontrado', 404);

    $vence = new DateTime($r['membresia_vence']);
    $hoy   = new DateTime('today');
    $dias  = (int)$hoy->diff($vence)->format('%r%a'); // negativo si vencida

    json_out([
        'dni'             => $dni,
        'nombre'          => $r['nombre'],
        'vence'           => $vence->format('Y-m-d'),
        'dias_restantes'  => $dias,
        'activa'          => ($dias >= 0)
    ]);
}


// ----------------- Endpoint: POST /echo -------------------
// Recibe JSON y lo devuelve junto con la hora del servidor.
// Útil para probar POST en Postman sin depender de BD.
if ($method === 'POST' && $path === '/echo') {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        bad_request('JSON inválido', 400);
    }
    json_out([
        'ok' => true,
        'received' => $data,
        'server_time' => date('c')
    ], 200);
}

// Si nada coincide
not_found('Ruta no encontrada. Proba GET /ping');
