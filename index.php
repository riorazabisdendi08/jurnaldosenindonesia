<?php
// File: index.php
// Entry point / Router utama dengan security headers global

// Aktifkan kompresi Gzip untuk output jika didukung oleh browser dan server.
// Ini akan mengompres HTML dan JSON secara otomatis.
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

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

// 5. Routing Sederhana
$route = $_GET['route'] ?? '';

switch ($route) {
    case 'api/search':
        // Caching headers akan diatur di dalam api/search.php untuk kontrol yang lebih baik
        require __DIR__ . '/api/search.php';
        break;
    case 'api/logs':
        // Halaman log yang dilindungi tidak boleh di-cache
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        require __DIR__ . '/api/logs.php';
        break;
    default:
        // --- Server-Side Rendering (SSR) Logic ---
        // Jika parameter 'q' ada di URL, lakukan pencarian di sisi server
        // untuk mempercepat First Contentful Paint (FCP) dan Largest Contentful Paint (LCP).
        $ssr_data = null;
        $cache_ttl = 0; // TTL untuk browser/CDN cache, 0 berarti tidak cacheable
        if (!empty($_GET['q'])) {
            // CATATAN: Logika di bawah ini adalah duplikasi yang disederhanakan dari api/search.php.
            // Untuk pemeliharaan jangka panjang, sangat disarankan untuk merefaktor logika ini
            // ke dalam sebuah fungsi atau kelas yang dapat digunakan bersama oleh kedua skrip.

            $config = require __DIR__ . '/config/config.php';
            $query = trim($_GET['q']);
            $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;

            // Gunakan konfigurasi default dari config.php
            $apiKey = $config['serpapi']['api_key'] ?? '';
            $engine = $config['serpapi']['engine'] ?? 'google_scholar';

            if (!empty($apiKey) && !empty($query)) {
                $params = [
                    'engine'  => $engine,
                    'hl'      => $config['serpapi']['hl'] ?? 'id',
                    'gl'      => $config['serpapi']['gl'] ?? 'id',
                    'api_key' => $apiKey,
                    'start'   => $start,
                    'num'     => 10, // Standar hasil per halaman
                    'q'       => $query,
                ];
                if ($engine === 'google_scholar') {
                    $params['as_rr'] = '1'; // Meniru parameter dari API
                }

                // Logika Caching (disalin dari api/search.php untuk konsistensi)
                $cacheDir = dirname(__DIR__) . '/cache';
                if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
                
                $cacheParams = $params;
                unset($cacheParams['api_key']);
                $cacheKey = md5(serialize($cacheParams));
                $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
                $cacheLifetime = 3600; // 1 jam

                if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
                    $ssr_data = json_decode(file_get_contents($cacheFile), true);
                    if ($ssr_data) {
                        // Hitung sisa waktu cache untuk dikirim ke browser/CDN
                        $cache_ttl = max(0, $cacheLifetime - (time() - filemtime($cacheFile)));
                    }
                } else {
                    // Panggil API jika tidak ada di cache (hanya jika library ada)
                    $autoloadPath = __DIR__ . '/vendor/autoload.php';
                    if (file_exists($autoloadPath)) require_once $autoloadPath;

                    if (class_exists('GoogleSearch')) {
                        try {
                            $search = new GoogleSearch($apiKey);
                            $decoded = json_decode(json_encode($search->get_json($params)), true);

                            if (isset($decoded['error'])) throw new Exception($decoded['error']);

                            // Normalisasi hasil (disalin dari api/search.php)
                            $results = $decoded['organic_results'] ?? $decoded['articles'] ?? $decoded['case_law_results'] ?? [];                            
                            
                            // Normalisasi data di backend untuk menghasilkan payload JSON yang ringkas.
                            $normalizedResults = [];
                            foreach ($results as $raw) {
                                $item = [];
                                $item['title'] = $raw['title'] ?? $raw['display_name'] ?? ($raw['publication_info']['title'] ?? 'Judul tidak tersedia');
                                $item['link'] = $raw['url'] ?? $raw['link'] ?? ($raw['primary_location']['pdf_url'] ?? '#');
                                
                                $type = $raw['type'] ?? ($raw['primary_location']['source']['display_name'] ?? ($raw['file_format'] ?? ''));
                                $item['type'] = is_string($type) ? trim($type) : '';

                                $authors = [];
                                if (!empty($raw['authors']) && is_array($raw['authors'])) {
                                    $authors = array_map(fn($a) => $a['name'] ?? $a['display_name'] ?? ($a['author']['display_name'] ?? null), $raw['authors']);
                                } elseif (!empty($raw['authorships']) && is_array($raw['authorships'])) {
                                    $authors = array_map(fn($a) => $a['author']['display_name'] ?? $a['author']['name'] ?? null, $raw['authorships']);
                                } elseif (!empty($raw['publication_info']['authors']) && is_array($raw['publication_info']['authors'])) {
                                    $authors = array_map(fn($a) => $a['name'] ?? null, $raw['publication_info']['authors']);
                                }
                                $item['authors'] = array_values(array_filter($authors));

                                $item['snippet'] = $raw['abstract'] ?? $raw['snippet'] ?? $raw['description'] ?? 'Tidak ada abstrak tersedia.';
                                $item['publication_summary'] = $raw['publication_info']['summary'] ?? '';

                                $item['cited_by_count'] = $raw['cited_by_count'] ?? ($raw['inline_links']['cited_by']['total'] ?? 0);
                                $item['cited_by_link'] = $raw['cited_by_link'] ?? ($raw['inline_links']['cited_by']['link'] ?? '#');

                                $item['versions_count'] = $raw['versions_count'] ?? ($raw['inline_links']['versions']['total'] ?? 0);
                                $item['versions_link'] = $raw['versions_link'] ?? ($raw['inline_links']['versions']['link'] ?? '#');

                                $resourceLinks = [];
                                if (!empty($raw['primary_location']['pdf_url'])) {
                                    $resourceLinks[] = ['label' => 'PDF', 'link' => $raw['primary_location']['pdf_url'], 'is_pdf' => true];
                                } elseif (!empty($raw['resources']) && is_array($raw['resources'])) {
                                    foreach ($raw['resources'] as $res) {
                                        $isPdf = isset($res['file_format']) && strcasecmp($res['file_format'], 'PDF') === 0;
                                        $resourceLinks[] = ['label' => $res['title'] ?? $res['file_format'] ?? 'Link', 'link' => $res['link'] ?? '#', 'is_pdf' => $isPdf];
                                    }
                                }
                                $item['resource_links'] = $resourceLinks;
                                $normalizedResults[] = $item;
                            }
                            $totalResults = $decoded['search_information']['total_results'] ?? 0;
                            $ssr_data = [
                                'results' => $normalizedResults,
                                'metadata' => ['total_results' => $totalResults, 'time_taken' => $decoded['search_metadata']['total_time_taken'] ?? 0],
                                'pagination' => ['current_start' => $start, 'has_next' => isset($decoded['serpapi_pagination']['next']), 'has_prev' => $start > 0]
                            ];
                            @file_put_contents($cacheFile, json_encode($ssr_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
                            // Set TTL penuh untuk cache browser/CDN karena ini data baru
                            $cache_ttl = $cacheLifetime;
                        } catch (Exception $e) {
                            $ssr_data = ['error' => 'Gagal mengambil data di sisi server: ' . $e->getMessage()];
                        }
                    }
                }
            }
        }

        // Atur header caching berdasarkan apakah halaman ini cacheable atau tidak
        if ($cache_ttl > 0 && isset($ssr_data) && !isset($ssr_data['error'])) {
            // Terapkan browser & edge caching untuk hasil pencarian yang sukses
            header('Cache-Control: public, max-age=' . $cache_ttl);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_ttl) . ' GMT');
        } else {
            // Halaman utama (tanpa query) atau halaman error tidak boleh di-cache
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Pragma: no-cache");
        }
        require __DIR__ . '/view/index.php';
        break;
}