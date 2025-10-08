<?php

declare(strict_types=1);

/**
 * API REST — Proyecto Gimnasio (PHP nativo, XAMPP)
 * Compatible con PHP 7.4+.
 */

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }
}

/* ---------- CORS + JSON ---------- */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Max-Age: 86400');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* ---------- Config ---------- */
error_reporting(E_ALL);
ini_set('display_errors', '0');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'gimnasio');
define('DB_CHARSET', 'utf8mb4');

define('API_KEY', getenv('API_KEY') ?: 'abc123'); // auth simple (opcional)

/* ---------- Helpers JSON/HTTP ---------- */
const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_FLAGS);
    exit;
}
function json_error(string $msg, int $code = 400, array $fields = [], ?string $detail = null): void
{
    $out = ['error' => $msg];
    if ($detail !== null) $out['detail'] = $detail;
    if ($fields) $out['fields'] = $fields;
    json_out($out, $code);
}
function not_found(string $msg = 'No encontrado'): void
{
    json_error($msg, 404);
}
function method_not_allowed(array $allow): void
{
    header('Allow: ' . implode(', ', $allow));
    json_error('Método no permitido', 405);
}
function read_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) json_error('JSON inválido', 400, [], json_last_error_msg());
    return is_array($data) ? $data : [];
}
function require_q(string $name): string
{
    $v = isset($_GET[$name]) ? trim((string)$_GET[$name]) : '';
    if ($v === '') json_error("Falta parámetro: $name", 400);
    return $v;
}
function require_field(array $a, string $name): string
{
    $v = isset($a[$name]) ? trim((string)$a[$name]) : '';
    if ($v === '') json_error("Falta campo: $name", 400);
    return $v;
}
function is_dni(string $v): bool
{
    return (bool)preg_match('/^\d{7,10}$/', $v);
}
function assert_dni(string $dni): void
{
    if (!is_dni($dni)) json_error('DNI inválido', 422);
}
function iso_now(): string
{
    return (new DateTime('now', new DateTimeZone('UTC')))->format('c');
}

/* ---------- Auth simple (opcional) ---------- */
function check_api_key(): void
{
    $hdr = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $candidate = $hdr ?: (preg_match('/X-API-Key\s+(.+)/', $auth, $m) ? $m[1] : '');
    if ($candidate !== API_KEY) json_error('Unauthorized (API key inválida)', 401);
}

/* ---------- DB ---------- */
function db(): mysqli
{
    static $cx = null;
    if ($cx instanceof mysqli) return $cx;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $cx = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $cx->set_charset(DB_CHARSET);
        return $cx;
    } catch (Throwable $e) {
        json_error('No se pudo conectar a la BD', 500, [], $e->getMessage());
    }
}

/* ---------- Paginación ---------- */
function page_params(): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $size = min(100, max(1, (int)($_GET['size'] ?? 20)));
    $off  = ($page - 1) * $size;
    return [$page, $size, $off];
}

/* ---------- Router ---------- */
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_SERVER['PATH_INFO'] ?? ($_GET['r'] ?? null);
if ($path === null) {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($base && str_starts_with($reqPath, $base)) $reqPath = substr($reqPath, strlen($base));
    $path = $reqPath === '' ? '/' : $reqPath;
}
if ($path === '' || $path[0] !== '/') $path = '/' . $path;
$path = preg_replace('#/{2,}#', '/', $path);

// Normalizar cuando entran a /api/api.php o /api/api.php/...
$script = '/' . trim(basename($_SERVER['SCRIPT_NAME'] ?? ''), '/'); // /api.php
if ($path === $script || $path === $script . '/') {
    $path = '/';
} elseif (strpos($path, $script . '/') === 0) {
    $path = substr($path, strlen($script)); // /api.php/ping -> /ping
}


/* ---------- Paths públicos / auth ---------- */
$public = ['/', '/ping'];
if (!in_array($path, $public, true)) {
    // check_api_key(); // activar si querés auth simple
}

/* ========================================================================== */
/*                                   ENDPOINTS                                */
/* ========================================================================== */

if ($method === 'GET' && $path === '/ping') {
    json_out(['pong' => true, 'time' => iso_now()]);
}

if ($method === 'GET' && $path === '/') {
    json_out([
        'name' => 'API Gimnasio',
        'version' => '1.0.0',
        'time' => iso_now(),
        'endpoints' => [
            ['GET', '/'],
            ['GET', '/ping'],
            ['GET', '/clientes/estado?dni='],
            ['POST', '/asistencia'],
            ['GET', '/asistencias?dni=&desde=&hasta=&page=&size='],
            ['GET', '/ejercicios'],
            ['POST', '/ejercicios'],
            ['PUT', '/ejercicios/{id}'],
            ['DELETE', '/ejercicios/{id}'],
            ['GET', '/rutinas?alumno_dni='],
            ['GET', '/rutinas/{id}'],
            ['POST', '/rutinas'],
            ['PUT', '/rutinas/{id}'],
            ['DELETE', '/rutinas/{id}'],
            ['POST', '/progresos']
        ]
    ]);
}

if ($method === 'GET' && $path === '/clientes/estado') {
    $dni = require_q('dni');
    assert_dni($dni);
    $cx = db();
    $st = $cx->prepare("SELECT nombre, membresia_vence FROM clientes WHERE dni=?");
    $st->bind_param('s', $dni);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if (!$r) json_error('Cliente no encontrado', 404);

    $vence = new DateTime($r['membresia_vence']);
    $hoy   = new DateTime('today');
    $dias  = (int)$hoy->diff($vence)->format('%r%a');

    json_out([
        'dni' => $dni,
        'nombre' => $r['nombre'],
        'vence' => $vence->format('Y-m-d'),
        'dias_restantes' => $dias,
        'activa' => ($dias >= 0)
    ]);
}

if ($method === 'POST' && $path === '/asistencia') {
    $b   = read_json();
    $dni = require_field($b, 'dni');
    assert_dni($dni);

    $cx = db();
    $st = $cx->prepare("SELECT membresia_vence FROM clientes WHERE dni=?");
    $st->bind_param('s', $dni);
    $st->execute();
    $rr = $st->get_result()->fetch_assoc();
    if (!$rr) json_error('Cliente no encontrado', 404);

    $vence = new DateTime($rr['membresia_vence']);
    $hoy   = new DateTime('today');
    if ($vence < $hoy) json_error('Membresía vencida', 402);

    $ins = $cx->prepare("INSERT INTO asistencias (dni) VALUES (?)");
    $ins->bind_param('s', $dni);
    $ins->execute();

    json_out(['ok' => true, 'dni' => $dni, 'momento' => iso_now()], 201);
}

if ($method === 'GET' && $path === '/asistencias') {
    $dni   = require_q('dni');
    assert_dni($dni);
    $desde = $_GET['desde'] ?? null;
    $hasta = $_GET['hasta'] ?? null;
    [$page, $size, $off] = page_params();

    $cx = db();
    $where = ['dni=?'];
    $types = 's';
    $bind = [$dni];
    if ($desde) {
        $where[] = 'DATE(momento) >= ?';
        $types .= 's';
        $bind[] = $desde;
    }
    if ($hasta) {
        $where[] = 'DATE(momento) <= ?';
        $types .= 's';
        $bind[] = $hasta;
    }
    $wsql = 'WHERE ' . implode(' AND ', $where);

    $stc = $cx->prepare("SELECT COUNT(*) c FROM asistencias $wsql");
    $stc->bind_param($types, ...$bind);
    $stc->execute();
    $total = (int)$stc->get_result()->fetch_assoc()['c'];

    $sql = "SELECT id, dni, momento FROM asistencias $wsql
            ORDER BY momento DESC LIMIT ? OFFSET ?";
    $types2 = $types . 'ii';
    $bind2 = $bind;
    $bind2[] = $size;
    $bind2[] = $off;
    $st = $cx->prepare($sql);
    $st->bind_param($types2, ...$bind2);
    $st->execute();
    $items = $st->get_result()->fetch_all(MYSQLI_ASSOC);

    json_out(['page' => $page, 'size' => $size, 'total' => $total, 'items' => $items]);
}

if ($path === '/ejercicios' && $method === 'GET') {
    [$page, $size, $off] = page_params();
    $mus = trim((string)($_GET['musculo'] ?? ''));
    $q   = trim((string)($_GET['q'] ?? ''));

    $cx = db();
    $where = [];
    $types = '';
    $bind = [];
    if ($mus !== '') {
        $where[] = 'musculo=?';
        $types .= 's';
        $bind[] = $mus;
    }
    if ($q !== '') {
        $where[] = '(nombre LIKE CONCAT("%",?,"%") OR descripcion LIKE CONCAT("%",?,"%"))';
        $types .= 'ss';
        $bind[] = $q;
        $bind[] = $q;
    }
    $wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stc = $cx->prepare("SELECT COUNT(*) c FROM ejercicios $wsql");
    if ($types) $stc->bind_param($types, ...$bind);
    $stc->execute();
    $total = (int)$stc->get_result()->fetch_assoc()['c'];

    $sql = "SELECT id,nombre,musculo,media_url,media_tipo,descripcion
            FROM ejercicios $wsql ORDER BY id DESC LIMIT ? OFFSET ?";
    $types2 = $types . 'ii';
    $bind2 = $bind;
    $bind2[] = $size;
    $bind2[] = $off;
    $st = $cx->prepare($sql);
    $st->bind_param($types2, ...$bind2);
    $st->execute();
    $items = $st->get_result()->fetch_all(MYSQLI_ASSOC);

    //Paginación
    json_out(['page' => $page, 'size' => $size, 'total' => $total, 'items' => $items]);
}
if ($path === '/ejercicios' && $method === 'POST') {
    $b = read_json();
    $nombre = require_field($b, 'nombre');
    $mus    = $b['musculo'] ?? null;
    $media  = $b['media_url'] ?? null;
    $tipo   = ($b['media_tipo'] ?? 'img') === 'video' ? 'video' : 'img';
    $desc   = $b['descripcion'] ?? null;

    $cx = db();
    $st = $cx->prepare("INSERT INTO ejercicios (nombre,musculo,media_url,media_tipo,descripcion) VALUES (?,?,?,?,?)");
    $st->bind_param('sssss', $nombre, $mus, $media, $tipo, $desc);
    $st->execute();

    json_out(['ok' => true, 'id' => $cx->insert_id], 201);
}
if ($method === 'PUT' && preg_match('#^/ejercicios/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $b = read_json();
    $nombre = require_field($b, 'nombre');
    $mus    = $b['musculo'] ?? null;
    $media  = $b['media_url'] ?? null;
    $tipo   = ($b['media_tipo'] ?? 'img') === 'video' ? 'video' : 'img';
    $desc   = $b['descripcion'] ?? null;

    $cx = db();
    $st = $cx->prepare("UPDATE ejercicios SET nombre=?, musculo=?, media_url=?, media_tipo=?, descripcion=? WHERE id=?");
    $st->bind_param('sssssi', $nombre, $mus, $media, $tipo, $desc, $id);
    $st->execute();
    json_out(['ok' => true]);
}
if ($method === 'DELETE' && preg_match('#^/ejercicios/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $cx = db();
    $st = $cx->prepare("DELETE FROM ejercicios WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    json_out(['ok' => true]);
}

if ($path === '/rutinas' && $method === 'GET') {
    $dni = require_q('alumno_dni');
    assert_dni($dni);
    $cx = db();
    $st = $cx->prepare("SELECT id, alumno_dni, profesor_id, nombre, notas, creado_en FROM rutinas WHERE alumno_dni=? ORDER BY id DESC");
    $st->bind_param('s', $dni);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    json_out(['items' => $rows]);
}
if ($method === 'GET' && preg_match('#^/rutinas/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $cx = db();
    $st = $cx->prepare("SELECT id, alumno_dni, profesor_id, nombre, notas, creado_en FROM rutinas WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    $head = $st->get_result()->fetch_assoc();
    if (!$head) json_error('Rutina no encontrada', 404);

    $sd = $cx->prepare("SELECT id, ejercicio_id, series, repeticiones, peso_objetivo, orden
                        FROM rutina_detalle WHERE rutina_id=? ORDER BY orden ASC, id ASC");
    $sd->bind_param('i', $id);
    $sd->execute();
    $items = $sd->get_result()->fetch_all(MYSQLI_ASSOC);

    json_out(['rutina' => $head, 'items' => $items]);
}
if ($path === '/rutinas' && $method === 'POST') {
    $b = read_json();
    $dni    = require_field($b, 'alumno_dni');
    assert_dni($dni);
    $nombre = require_field($b, 'nombre');
    $prof   = isset($b['profesor_id']) && $b['profesor_id'] !== '' ? (int)$b['profesor_id'] : null;
    $notas  = $b['notas'] ?? null;
    $items  = is_array($b['items'] ?? null) ? $b['items'] : [];

    $cx = db();
    $cx->begin_transaction();
    try {
        $st = $cx->prepare("INSERT INTO rutinas (alumno_dni, profesor_id, nombre, notas) VALUES (?,?,?,?)");
        $st->bind_param('siss', $dni, $prof, $nombre, $notas);
        $st->execute();
        $rid = (int)$cx->insert_id;

        if ($items) {
            $det = $cx->prepare("INSERT INTO rutina_detalle (rutina_id, ejercicio_id, series, repeticiones, peso_objetivo, orden)
                                 VALUES (?,?,?,?,?,?)");
            foreach ($items as $it) {
                $eid = (int)($it['ejercicio_id'] ?? 0);
                if ($eid <= 0) continue;
                $ser = (int)($it['series'] ?? 3);
                $rep = (int)($it['repeticiones'] ?? 10);
                $pes = isset($it['peso_objetivo']) && $it['peso_objetivo'] !== '' ? (float)$it['peso_objetivo'] : null;
                $ord = (int)($it['orden'] ?? 1);
                $det->bind_param('iiiidi', $rid, $eid, $ser, $rep, $pes, $ord);
                $det->execute();
            }
        }
        $cx->commit();
        json_out(['ok' => true, 'rutina_id' => $rid], 201);
    } catch (Throwable $e) {
        $cx->rollback();
        json_error('No se pudo crear la rutina', 500, [], $e->getMessage());
    }
}
if ($method === 'PUT' && preg_match('#^/rutinas/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $b = read_json();
    $nombre = require_field($b, 'nombre');
    $notas  = $b['notas'] ?? null;
    $items  = is_array($b['items'] ?? null) ? $b['items'] : [];

    $cx = db();
    $cx->begin_transaction();
    try {
        $st = $cx->prepare("UPDATE rutinas SET nombre=?, notas=? WHERE id=?");
        $st->bind_param('ssi', $nombre, $notas, $id);
        $st->execute();

        $cx->query("DELETE FROM rutina_detalle WHERE rutina_id=" . (int)$id);
        if ($items) {
            $det = $cx->prepare("INSERT INTO rutina_detalle (rutina_id, ejercicio_id, series, repeticiones, peso_objetivo, orden)
                                 VALUES (?,?,?,?,?,?)");
            foreach ($items as $it) {
                $eid = (int)($it['ejercicio_id'] ?? 0);
                if ($eid <= 0) continue;
                $ser = (int)($it['series'] ?? 3);
                $rep = (int)($it['repeticiones'] ?? 10);
                $pes = isset($it['peso_objetivo']) && $it['peso_objetivo'] !== '' ? (float)$it['peso_objetivo'] : null;
                $ord = (int)($it['orden'] ?? 1);
                $det->bind_param('iiiidi', $id, $eid, $ser, $rep, $pes, $ord);
                $det->execute();
            }
        }
        $cx->commit();
        json_out(['ok' => true]);
    } catch (Throwable $e) {
        $cx->rollback();
        json_error('No se pudo actualizar la rutina', 500, [], $e->getMessage());
    }
}
if ($method === 'DELETE' && preg_match('#^/rutinas/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $cx = db();
    $st = $cx->prepare("DELETE FROM rutinas WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    json_out(['ok' => true]);
}

if ($method === 'POST' && $path === '/progresos') {
    $b  = read_json();
    $dni = require_field($b, 'dni');
    assert_dni($dni);
    $eid = (int)require_field($b, 'ejercicio_id');
    $rid = isset($b['rutina_id']) && $b['rutina_id'] !== '' ? (int)$b['rutina_id'] : null;
    $fec = $b['fecha'] ?? (new DateTime('today'))->format('Y-m-d');
    $ser = (int)require_field($b, 'series');
    $rep = (int)require_field($b, 'repeticiones');
    $pes = isset($b['peso']) && $b['peso'] !== '' ? (float)$b['peso'] : null;
    $not = $b['notas'] ?? null;

    if ($ser <= 0 || $rep <= 0) json_error('series/repeticiones deben ser > 0', 422);

    $cx = db();
    $st = $cx->prepare("INSERT INTO progresos (dni, rutina_id, ejercicio_id, fecha, series, repeticiones, peso, notas)
                        VALUES (?,?,?,?,?,?,?,?)");
    $st->bind_param('siisiiis', $dni, $rid, $eid, $fec, $ser, $rep, $pes, $not);
    $st->execute();

    json_out(['ok' => true, 'id' => $cx->insert_id], 201);
}

not_found('Ruta no encontrada. Probá GET /ping');
