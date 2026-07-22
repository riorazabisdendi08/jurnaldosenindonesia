<?php
// File: index.php
// Entry point / Router utama dengan security headers global

// 1. Matikan display errors di production untuk mencegah informasi server bocor
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. Definisikan konstanta untuk memvalidasi akses internal
define('APP_ENTRY', true);

// Cek keberadaan konfigurasi
$configFile = __DIR__ . '/config/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Kesalahan Sistem: File konfigurasi tidak ditemukan. Harap salin config.example.php ke config.php.');
}

// 3. Inisialisasi Session Global
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 4. Security Headers Global
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 5. Routing Sederhana
$route = $_GET['route'] ?? '';

switch ($route) {
    case 'api/search':
        require __DIR__ . '/api/search.php';
        break;
    case 'api/logs':
        require __DIR__ . '/api/logs.php';
        break;
    default:
        require __DIR__ . '/view/index.php';
        break;
}