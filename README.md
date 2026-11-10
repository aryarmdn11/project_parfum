# ATEA Parfums — PHP, MySQL, AdminLTE

## Menjalankan di XAMPP (Windows)
1. Ekstrak folder `project_parfum-main` ke `C:\xampp\htdocs\`.
2. Buka XAMPP Control Panel, lalu Start **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin`.
4. Buat database bernama **atea_parfums** (collation `utf8mb4_unicode_ci`), lalu pilih database tersebut.
5. Import file `database/atea_parfums.sql`.
6. Buka `http://localhost/project_parfum-main/`.
7. Buat akun administrator satu kali di `http://localhost/project_parfum-main/setup_admin.php`.
8. Login melalui `http://localhost/project_parfum-main/login.php`.
9. Setelah admin berhasil dibuat, hapus `setup_admin.php` dari folder project.

## Konfigurasi database
Default XAMPP:
- Host: `127.0.0.1`
- Port: `3306`
- Database: `atea_parfums`
- Username: `root`
- Password: kosong

Jika konfigurasi MySQL berbeda, salin `config.example.php` menjadi `config.local.php`, lalu sesuaikan `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASS`. `config.local.php` tidak diunggah ke Git.

## Catatan
- Pastikan ekstensi PHP `pdo_mysql` aktif di XAMPP.
- AdminLTE 3.2 dan Font Awesome dimuat dari CDN, sehingga styling membutuhkan koneksi internet.
- Jangan import SQL berulang kali ke database yang sudah berisi data kecuali memang ingin membangun ulang database.
