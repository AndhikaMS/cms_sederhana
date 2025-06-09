# Panduan Penggunaan Aplikasi CMS Sederhana

Berikut adalah langkah-langkah untuk menjalankan aplikasi CMS Sederhana:

1.  **Unduh Aplikasi:**
    *   Clone repositori GitHub ini atau unduh file ZIP repositori.
    *   Ekstrak (jika Anda mengunduh ZIP) dan tempatkan folder `cms_sederhana` ke dalam direktori `xampp/htdocs` Anda (misalnya, `D:\Campus\xampp\htdocs\cms_sederhana`).

2.  **Aktifkan Apache dan MySQL:**
    *   Buka XAMPP Control Panel.
    *   Mulai (Start) modul `Apache` dan `MySQL`. Pastikan keduanya berjalan.

3.  **Siapkan Database:**
    *   Buka browser Anda dan pergi ke `http://localhost/phpmyadmin`.
    *   Buat database baru dengan nama `cms_sederhana`.
    *   Pilih database `cms_sederhana` yang baru saja Anda buat.
    *   Pergi ke tab `Import`.
    *   Klik tombol `Choose File` (atau `Browse`) dan pilih file `cms_sederhana.sql` yang terletak di dalam folder "Database to Import" di proyek Anda (misalnya, `D:\Campus\xampp\htdocs\cms_sederhana\Database to Import\cms_sederhana.sql`).
    *   Gulir ke bawah dan klik tombol `Go` untuk memulai proses impor.

4.  **Jalankan Aplikasi Web:**
    *   Buka browser Anda.
    *   Akses aplikasi dengan membuka URL: `http://localhost/cms_sederhana`

Selamat! Aplikasi CMS Sederhana Anda sekarang sudah berjalan.