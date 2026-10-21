# ATEA Parfums — Website + Backend PHP (XAMPP)

Frontend toko parfum yang sekarang tersambung ke database MySQL (`atea_parfums`) lewat PHP (PDO).

## Cara menjalankan (XAMPP, Windows)
1. Buka **XAMPP Control Panel**, klik **Start** pada **Apache** dan **MySQL**.
2. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/atea_parfums.sql` → **Go**.
3. Salin seluruh folder `atea_parfums_store` ke `C:\xampp\htdocs\`.
4. Buka `http://localhost/atea_parfums_store/` di browser.
5. Buat akun admin: buka `http://localhost/atea_parfums_store/setup_admin.php`, isi nama/email/password.
   **Setelah itu hapus file `setup_admin.php`.** (Halaman ini otomatis menolak kalau admin sudah ada.)
6. Login admin lalu buka **Panel Admin** (menu ikon akun) untuk kelola pesanan, harga, dan stok.

Kebutuhan: PHP 8.1 atau lebih baru (XAMPP versi terbaru sudah memenuhi).

## Pengaturan (database, rekening, WhatsApp)
Salin `config.example.php` menjadi `config.local.php`, lalu isi data aslimu di sana (`DB_USER`, `DB_PASS`, info pembayaran, `WHATSAPP_NUMBER`).
`config.local.php` sudah ada di `.gitignore`, jadi tidak ikut ke GitHub. Kalau file itu belum ada, website memakai default XAMPP (user `root`, password kosong).

## Struktur
- `index.php`, `lineup.php`, `about.php`: halaman publik (produk, harga, stok, notes diambil dari database)
- `login.php`, `register.php`, `logout.php`: akun pelanggan (password di-hash dengan `password_hash`)
- `checkout.php`, `order_success.php`, `orders.php`: checkout, instruksi bayar, riwayat & pembatalan pesanan
- `api/cart.php`: keranjang (tabel `carts`, `cart_items`); `api/review.php`: review (tabel `reviews`)
- `admin/index.php`: kelola pesanan & konfirmasi pembayaran; `admin/products.php`: edit deskripsi, harga, stok, status
- `includes/`: koneksi database, helper, katalog, fungsi keranjang & pesanan
- `config.example.php`: contoh pengaturan (ikut GitHub); `config.local.php`: pengaturan asli (tidak ikut GitHub); `config.php`: pemuat
- `database/atea_parfums.sql`: skema + data awal

## Alur pesanan
Keranjang disimpan di database per akun, jadi pelanggan harus login untuk menambah ke keranjang.
Checkout membuat baris di `orders`, `order_items` (harga_satuan = snapshot harga saat itu), dan `payments` (status `pending`),
mengurangi stok varian, lalu mengosongkan keranjang, semuanya dalam satu transaksi.
Admin mengonfirmasi pembayaran → status pesanan jadi `diproses`. Pesanan yang dibatalkan mengembalikan stok.
