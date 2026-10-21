<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

date_default_timezone_set(APP_TIMEZONE);

if (session_status() === PHP_SESSION_NONE) {
    session_name('ATEASESS');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- URL dasar (otomatis, jadi folder boleh dinamai apa saja di htdocs) ----------
(function () {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(dirname($script), '/');
    $dir = preg_replace('#/(admin|api)$#', '', $dir);
    define('BASE', $dir . '/');
})();

function url(string $path = ''): string { return BASE . ltrim($path, '/'); }
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function now(): string { return date('Y-m-d H:i:s'); }

function redirect(string $to): never { header('Location: ' . $to); exit; }

// ---------- Database ----------
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    if ($f = getenv('ATEA_SQLITE')) {            // hanya untuk pengujian developer
        $pdo = new PDO('sqlite:' . $f, null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, $opts + [PDO::ATTR_EMULATE_PREPARES => false]);
    }
    return $pdo;
}
function is_sqlite(): bool { return db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'; }
function for_update(): string { return is_sqlite() ? '' : ' FOR UPDATE'; }

set_exception_handler(function (Throwable $e) {
    error_log('[ATEA] ' . $e);
    http_response_code(500);
    $isApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
    $msg = ($e instanceof PDOException)
        ? 'Tidak bisa terhubung ke database. Pastikan MySQL di XAMPP menyala dan database "atea_parfums" sudah di-import (lihat folder database/).'
        : 'Terjadi kesalahan pada server.';
    if ($isApi) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['error' => $msg]); exit; }
    echo '<!doctype html><meta charset="utf-8"><title>Error</title><body style="font-family:sans-serif;padding:40px;max-width:560px;margin:auto"><h1>Oops</h1><p>' . h($msg) . '</p></body>';
    exit;
});

// ---------- CSRF ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; }
function csrf_ok(?string $t): bool { return is_string($t) && $t !== '' && hash_equals($_SESSION['csrf'] ?? '', $t); }
function csrf_require(): void {
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!csrf_ok($t)) { http_response_code(419); exit('Sesi tidak valid. Muat ulang halaman lalu coba lagi.'); }
}

// ---------- Flash message ----------
function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['t' => $type, 'm' => $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

// ---------- Auth ----------
function current_user(): ?array {
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (!empty($_SESSION['uid'])) {
        $st = db()->prepare('SELECT id_user, nama, email, no_telepon, role FROM `user` WHERE id_user = ?');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch() ?: null;
        if (!$u) unset($_SESSION['uid']);
    }
    return $u;
}
function login_user(int $id): void { session_regenerate_id(true); $_SESSION['uid'] = $id; }
function safe_next(?string $n): string {
    // hanya izinkan path relatif di situs ini
    if (!$n || !preg_match('#^[A-Za-z0-9_\-./?=&%]+$#', $n) || str_contains($n, '..') || str_starts_with($n, '/') || str_contains($n, '//')) return url('index.php');
    return url($n);
}
function require_login(): array {
    $u = current_user();
    if (!$u) {
        $next = basename($_SERVER['SCRIPT_NAME']) . (($_SERVER['QUERY_STRING'] ?? '') ? '?' . $_SERVER['QUERY_STRING'] : '');
        flash('info', 'Silakan login dulu.');
        redirect(url('login.php?next=' . rawurlencode($next)));
    }
    return $u;
}
function require_admin(): array {
    $u = current_user();
    if (!$u || $u['role'] !== 'admin') { http_response_code(403); exit('Akses ditolak. Halaman ini khusus admin.'); }
    return $u;
}
function json_out($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
