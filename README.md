# Jurnal Dosen Indonesia

Mesin pencari akademik modern, cepat, dan aman yang terintegrasi dengan indeks Google Scholar untuk menyediakan hasil pencarian yang komprehensif bagi komunitas akademik di Indonesia.

## ✨ Fitur Utama

-   **Pencarian Terintegrasi**: Menggunakan Google Scholar sebagai backend melalui SerpApi untuk hasil pencarian yang luas dan relevan.
-   **Performa Super Cepat**:
    -   **Server-Side Rendering (SSR)**: Hasil pencarian ditampilkan hampir seketika untuk pencarian yang dilakukan via URL, meningkatkan FCP dan LCP secara drastis.
    -   **Caching Agresif**: Memanfaatkan cache di sisi server, browser, dan CDN (Edge) untuk waktu respons secepat kilat pada pencarian populer.
    -   **Kompresi Gzip**: Mengurangi ukuran transfer data HTML dan JSON secara signifikan.
    -   **Desain API Efisien**: Backend mengirimkan payload JSON yang ringkas dan sudah diproses, meminimalkan pekerjaan di sisi klien.
-   **Pengalaman Pengguna Modern**:
    -   **Autocomplete Instan**: Memberikan saran pencarian secara *real-time* dengan teknik *debouncing* untuk mencegah beban server berlebih.
    -   **Navigasi Keyboard**: Dukungan penuh untuk tombol panah (atas/bawah), `Enter`, dan `Escape` pada saran pencarian.
    -   **"Muat Lebih Banyak"**: Menggantikan paginasi tradisional untuk pengalaman *infinite scroll* yang lebih mulus tanpa memuat ulang halaman.
    -   **Desain Bersih & Responsif**: Antarmuka minimalis yang berfokus pada fungsi pencarian dan bekerja sempurna di semua perangkat (mobile-first).
-   **Aksesibilitas (A11y)**:
    -   Struktur HTML semantik (misalnya, `<ul>` untuk hasil) yang ramah untuk pembaca layar (*screen readers*).
    -   Manajemen fokus yang cerdas ("focus trap") untuk modal dan konten dinamis.
    -   Dukungan penuh untuk navigasi menggunakan tombol Tab.
-   **Keamanan Terdepan**:
    -   **HTTP Security Headers**: Menerapkan CSP, HSTS, X-Frame-Options, dan lainnya untuk melindungi dari serangan web umum.
    -   **Validasi & Sanitasi Input**: Perlindungan ketat terhadap semua input pengguna di sisi server.
    -   **Rate Limiting**: Mencegah penyalahgunaan API oleh bot atau pengguna dengan membatasi jumlah permintaan.
    -   **Manajemen Kunci API Aman**: Kunci API dikelola di sisi server dan tidak pernah diekspos ke klien.
-   **Kompatibilitas Luas**: Menggunakan JavaScript modern (ES6+) dengan *polyfills* dari `polyfill.io` untuk memastikan fungsionalitas tetap berjalan di browser versi lama.

## 🚀 Teknologi

-   **Backend**: PHP 8+
-   **Frontend**: Vanilla JavaScript (ES6+), Tailwind CSS
-   **Dependensi**: `serpapi/google-search-results-php` (dikelola via Composer)

## 🔧 Instalasi & Konfigurasi

1.  **Clone Repositori**
    ```bash
    git clone https://github.com/your-username/jurnal-dosen-indonesia.git
    cd jurnal-dosen-indonesia
    ```

2.  **Instal Dependensi PHP**
    Pastikan Anda memiliki Composer terinstal.
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment Variables**
    Proyek ini menggunakan environment variables untuk menyimpan kunci API dan konfigurasi sensitif lainnya. Salin `config.example.php` menjadi `config.php` dan sesuaikan, atau lebih baik, atur environment variables di server Anda.

    **Variabel yang Wajib Diisi:**
    -   `SERPAPI_API_KEY`: Kunci API Anda dari SerpApi.

    **Variabel Opsional:**
    -   `LOG_ADMIN_USER`: Username untuk melihat panel log (default: `admin`).
    -   `LOG_ADMIN_PASS`: Password untuk panel log (sangat disarankan untuk diatur).

    Untuk detail lebih lanjut, lihat file `ENV_VARS.md`.

4.  **Jalankan Pemeriksa Konfigurasi (Opsional)**
    Skrip ini akan membantu memverifikasi apakah environment variables Anda sudah terbaca dengan benar oleh PHP.
    ```bash
    php scripts/check_env.php
    ```

5.  **Arahkan Web Server Anda**
    Konfigurasikan web server Anda (Apache, Nginx, dll.) agar menunjuk ke direktori root proyek ini. Pastikan `mod_rewrite` (atau yang setara) aktif jika Anda ingin menggunakan URL yang lebih bersih di masa depan.

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi **Proprietary**. Anda tidak diizinkan untuk mendistribusikan ulang atau memodifikasi kode tanpa izin eksplisit dari pemilik.

---
*Dibuat dan dioptimalkan dengan bantuan Gemini Code Assist.*