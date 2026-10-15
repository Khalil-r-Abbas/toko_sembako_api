<?php

/**
 * Vercel Entry Point – PHP Built-In Server Router Script
 *
 * Semua request dari vercel.json diteruskan ke sini.
 * File ini mem-bootstrap CodeIgniter 4 dan menangani setiap error
 * sebagai JSON agar mudah di-debug via Postman / Vercel logs.
 */

// ─── 0. Error visibility: tangkap semua error sebagai JSON ──────────────────
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * Kirim error sebagai JSON lalu hentikan eksekusi.
 */
function sendJsonError(int $code, string $type, string $message, string $file = '', int $line = 0): never
{
    if (! headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'status'  => $code,
        'error'   => $type,
        'message' => $message,
        'file'    => $file,
        'line'    => $line,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit(1);
}

// Handler untuk exception tidak tertangkap
set_exception_handler(function (Throwable $e): void {
    sendJsonError(500, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
});

// Handler untuk PHP warning/notice (non-fatal)
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Lewatkan error yang di-suppress dengan @
    if (! (error_reporting() & $errno)) {
        return false;
    }
    sendJsonError(500, "PHP Error ($errno)", $errstr, $errfile, $errline);
});

// Shutdown function: tangkap fatal error (E_ERROR, E_PARSE, dll.)
register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        sendJsonError(500, 'PHP Fatal Error', $err['message'], $err['file'], $err['line']);
    }
});

// ─── 1. Root project (satu level di atas folder api/) ───────────────────────
$projectRoot = dirname(__DIR__);

// ─── 2. PHP version check ────────────────────────────────────────────────────
$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    sendJsonError(503, 'PHP Version Error', sprintf(
        'PHP %s+ required. Current: %s',
        $minPhpVersion,
        PHP_VERSION
    ));
}

// ─── 3. Pastikan vendor/autoload.php tersedia ─────────────────────────────────
$autoload = $projectRoot . '/vendor/autoload.php';
if (! is_file($autoload)) {
    sendJsonError(503, 'Autoloader Missing', 'vendor/autoload.php not found. Run composer install.');
}

// ─── 4. Set FCPATH seperti yang diharapkan CI4 ───────────────────────────────
// CI4 menggunakan FCPATH sebagai base path front-controller.
// Kita arahkan ke folder public/ (CI4 default), meski di Vercel tidak di-serve langsung.
define('FCPATH', $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Sesuaikan working directory
if (is_dir($projectRoot . '/public')) {
    chdir($projectRoot . '/public');
}

// ─── 5. Handle static files (lewati ke built-in server) ──────────────────────
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$staticFile = $projectRoot . '/public' . $uri;
if ($uri !== '/' && is_file($staticFile)) {
    return false; // biarkan PHP built-in server melayani file statis
}

// ─── 6. Bootstrap CodeIgniter 4 ──────────────────────────────────────────────
require $projectRoot . '/app/Config/Paths.php';

$paths = new Config\Paths();

require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));