<?php

/**
 * Vercel Entry Point (Router Script)
 *
 * File ini digunakan oleh PHP Built-In Server di Vercel sebagai router script.
 * Semua request diteruskan ke sini (lihat vercel.json), lalu kita bootstrap
 * CodeIgniter 4 dari sini.
 *
 * Struktur di Vercel: /var/task/user/ adalah root project.
 * api/index.php  → file ini (router)
 * app/           → folder CI4
 * vendor/        → composer dependencies
 * public/        → FCPATH CI4 (tidak dipakai langsung di Vercel)
 */

// ─── 1. Tentukan root project (satu level di atas folder api/) ───────────────
$projectRoot = dirname(__DIR__);

// ─── 2. Minimal PHP version check ────────────────────────────────────────────
$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode([
        'status'  => 503,
        'message' => sprintf(
            'PHP %s+ required. Current: %s',
            $minPhpVersion,
            PHP_VERSION
        ),
    ]);
    exit(1);
}

// ─── 3. Set FCPATH agar CI4 tahu lokasi "public" front-controller ─────────────
// Di Vercel tidak ada folder public/ yang di-serve; kita pura-purakan bahwa
// folder api/ adalah FCPATH-nya.
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Pastikan working directory mengarah ke sini
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(__DIR__);
}

// ─── 4. Handle static files (CSS/JS/images) yang di-route ke sini ────────────
// PHP Built-In Server: jika file fisik ada di disk, kembalikan langsung.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$requestedFile = $projectRoot . '/public' . $uri;

if ($uri !== '/' && is_file($requestedFile)) {
    return false; // biarkan built-in server menangani file statis
}

// ─── 5. Load Paths config & bootstrap CodeIgniter 4 ─────────────────────────
require $projectRoot . '/app/Config/Paths.php';

$paths = new Config\Paths();

require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));