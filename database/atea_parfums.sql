-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Nov 2026 pada 12.25
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atea_parfums`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `brand`
--

CREATE TABLE `brand` (
  `id_brand` int(10) UNSIGNED NOT NULL,
  `nama_brand` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `negara_asal` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `brand`
--

INSERT INTO `brand` (`id_brand`, `nama_brand`, `deskripsi`, `logo`, `negara_asal`) VALUES
(1, 'ATEA Parfums', 'A scent that feels like you. Brand parfum dengan sepuluh karakter aroma.', 'assets/atea-logo.png', 'Indonesia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `carts`
--

CREATE TABLE `carts` (
  `id_cart` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_items`
--

CREATE TABLE `cart_items` (
  `id_cart_item` int(10) UNSIGNED NOT NULL,
  `id_cart` int(10) UNSIGNED NOT NULL,
  `id_variant` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fragrance_notes`
--

CREATE TABLE `fragrance_notes` (
  `id_note` int(10) UNSIGNED NOT NULL,
  `nama_note` varchar(100) NOT NULL,
  `jenis_aroma` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fragrance_notes`
--

INSERT INTO `fragrance_notes` (`id_note`, `nama_note`, `jenis_aroma`) VALUES
(1, 'Bergamot', 'Citrus'),
(2, 'Pear', 'Fruity'),
(3, 'Pink Pepper', 'Spicy'),
(4, 'Jasmine', 'Floral'),
(5, 'Rose', 'Floral'),
(6, 'Heliotrope', 'Floral'),
(7, 'Vanilla', 'Sweet'),
(8, 'Sandalwood', 'Woody'),
(9, 'Musk', 'Musk'),
(10, 'Mandarin', 'Citrus'),
(11, 'Neroli', 'Floral'),
(12, 'White Flowers', 'Floral'),
(13, 'Cedarwood', 'Woody'),
(14, 'Iris', 'Floral'),
(15, 'Violet', 'Floral'),
(16, 'Cashmere Wood', 'Woody'),
(17, 'Cardamom', 'Spicy'),
(18, 'Saffron', 'Spicy'),
(19, 'Nutmeg', 'Spicy'),
(20, 'Amber', 'Amber'),
(21, 'Patchouli', 'Woody'),
(22, 'Lemon', 'Citrus'),
(23, 'Green Apple', 'Fruity'),
(24, 'Lavender', 'Floral'),
(25, 'Geranium', 'Floral'),
(26, 'Vetiver', 'Woody'),
(27, 'White Musk', 'Musk'),
(28, 'Black Pepper', 'Spicy'),
(29, 'Plum', 'Fruity'),
(30, 'Incense', 'Spicy'),
(31, 'Oud Accord', 'Woody'),
(32, 'Tonka', 'Sweet'),
(33, 'Lychee', 'Fruity'),
(34, 'Raspberry', 'Fruity'),
(35, 'Peony', 'Floral'),
(36, 'Watery Notes', 'Aquatic'),
(37, 'Freesia', 'Floral'),
(38, 'Peach', 'Fruity'),
(39, 'Magnolia', 'Floral'),
(40, 'Juniper', 'Spicy'),
(41, 'Clary Sage', 'Spicy'),
(42, 'Cedar', 'Woody');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Floral', 'Aroma bunga yang lembut, romantis, dan elegan.'),
(2, 'Fresh', 'Aroma segar, bersih, dan ringan untuk aktivitas harian.'),
(3, 'Woody', 'Aroma kayu yang hangat, dalam, dan berkarakter.'),
(4, 'Oriental', 'Aroma amber, rempah, dan manis yang hangat serta sensual.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `tanggal_pesanan` datetime NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `alamat_pengiriman` text NOT NULL,
  `status_pesanan` enum('menunggu_pembayaran','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id_order_item` int(10) UNSIGNED NOT NULL,
  `id_order` int(10) UNSIGNED NOT NULL,
  `id_variant` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id_payment` int(10) UNSIGNED NOT NULL,
  `id_order` int(10) UNSIGNED NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL COMMENT 'mis. Transfer Bank, QRIS, E-Wallet',
  `tanggal_pembayaran` datetime DEFAULT NULL,
  `jumlah_pembayaran` decimal(12,2) NOT NULL,
  `status_pembayaran` enum('pending','berhasil','gagal','refund') NOT NULL DEFAULT 'pending',
  `nomor_referensi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_brand` int(10) UNSIGNED NOT NULL,
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `konsentrasi` varchar(50) DEFAULT NULL COMMENT 'mis. Eau de Parfum, Extrait de Parfum',
  `status_produk` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_brand`, `id_kategori`, `nama_produk`, `deskripsi`, `gambar`, `konsentrasi`, `status_produk`, `created_at`) VALUES
(1, 1, 4, 'Aureon', 'Merah burgundy yang memancarkan keberanian dan kemewahan. Aureon merepresentasikan karakter elegan, hangat, dan penuh percaya diri untuk menemani setiap momen berharga.', 'assets/aureon-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(2, 1, 3, 'Zafran', 'Terinspirasi dari warna amber yang hangat dan bercahaya. Zafran menggambarkan semangat, energi, dan keceriaan bagi mereka yang ingin tampil lebih ekspresif.', 'assets/zafran-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(3, 1, 4, 'Noctis', 'Hijau zamrud menghadirkan kesan tenang, misterius, dan berkelas. Noctis merepresentasikan ketenangan yang berpadu dengan karakter kuat dan penuh pesona.', 'assets/noctis-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(4, 1, 2, 'Velora', 'Biru royal mencerminkan ketegasan dan kepercayaan diri. Velora hadir dengan karakter modern dan elegan, cocok untuk melengkapi penampilan dalam berbagai kesempatan.', 'assets/velora-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(5, 1, 2, 'Elvara', 'Ungu menghadirkan kesan anggun dengan sentuhan misterius. Elvara merepresentasikan keindahan, kreativitas, dan karakter yang tidak mudah dilupakan.', 'assets/elvara-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(6, 1, 3, 'Nerion', 'Nuansa pink peach memberikan kesan lembut, hangat, dan bersahaja. Nerion menggambarkan keindahan sederhana yang tetap mampu memberikan kesan istimewa.', 'assets/nerion-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(7, 1, 1, 'Sorell', 'Perpaduan warna rose dan biru muda menciptakan kesan lembut sekaligus menenangkan. Sorell merepresentasikan keseimbangan, keanggunan, dan keindahan dalam kesederhanaan.', 'assets/sorell-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(8, 1, 1, 'Orlune', 'Sentuhan champagne dan lavender menghadirkan nuansa mewah yang tidak berlebihan. Orlune mencerminkan kelembutan, ketenangan, dan keanggunan yang timeless.', 'assets/orlune-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(9, 1, 1, 'Veyron', 'Perpaduan pink dan ungu memberikan karakter yang berani, ekspresif, dan penuh daya tarik. Veyron merepresentasikan kebebasan berekspresi dengan sentuhan elegan.', 'assets/veyron-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15'),
(10, 1, 2, 'Lumera', 'Perpaduan biru dan keemasan menciptakan kesan eksklusif dan bercahaya. Lumera menggambarkan kemewahan, optimisme, dan pesona yang hadir dalam setiap kesempatan.', 'assets/lumera-bottle.svg', 'Extrait de Parfum', 'aktif', '2026-10-20 20:25:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_notes`
--

CREATE TABLE `produk_notes` (
  `id_product_note` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_note` int(10) UNSIGNED NOT NULL,
  `note_stage` enum('Top','Middle','Base') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `produk_notes`
--

INSERT INTO `produk_notes` (`id_product_note`, `id_produk`, `id_note`, `note_stage`) VALUES
(324, 1, 1, 'Top'),
(320, 1, 9, 'Base'),
(323, 1, 10, 'Top'),
(322, 1, 11, 'Middle'),
(321, 1, 12, 'Middle'),
(319, 1, 13, 'Base'),
(331, 2, 1, 'Top'),
(325, 2, 9, 'Base'),
(328, 2, 13, 'Middle'),
(327, 2, 20, 'Base'),
(326, 2, 26, 'Base'),
(330, 2, 40, 'Top'),
(329, 2, 41, 'Middle'),
(334, 3, 5, 'Middle'),
(337, 3, 28, 'Top'),
(336, 3, 29, 'Top'),
(335, 3, 30, 'Middle'),
(333, 3, 31, 'Base'),
(332, 3, 32, 'Base'),
(343, 4, 2, 'Top'),
(342, 4, 3, 'Top'),
(338, 4, 9, 'Base'),
(341, 4, 14, 'Middle'),
(340, 4, 15, 'Middle'),
(339, 4, 16, 'Base'),
(349, 5, 22, 'Top'),
(348, 5, 23, 'Top'),
(347, 5, 24, 'Middle'),
(346, 5, 25, 'Middle'),
(345, 5, 26, 'Base'),
(344, 5, 27, 'Base'),
(353, 6, 5, 'Middle'),
(355, 6, 17, 'Top'),
(354, 6, 18, 'Top'),
(352, 6, 19, 'Middle'),
(351, 6, 20, 'Base'),
(350, 6, 21, 'Base'),
(359, 7, 5, 'Middle'),
(357, 7, 7, 'Base'),
(356, 7, 9, 'Base'),
(361, 7, 33, 'Top'),
(360, 7, 34, 'Top'),
(358, 7, 35, 'Middle'),
(370, 8, 1, 'Top'),
(369, 8, 2, 'Top'),
(368, 8, 3, 'Top'),
(367, 8, 4, 'Middle'),
(366, 8, 5, 'Middle'),
(365, 8, 6, 'Middle'),
(364, 8, 7, 'Base'),
(363, 8, 8, 'Base'),
(362, 8, 9, 'Base'),
(375, 9, 1, 'Top'),
(374, 9, 5, 'Middle'),
(371, 9, 8, 'Base'),
(372, 9, 9, 'Base'),
(376, 9, 38, 'Top'),
(373, 9, 39, 'Middle'),
(381, 10, 1, 'Top'),
(380, 10, 4, 'Middle'),
(377, 10, 20, 'Base'),
(378, 10, 27, 'Base'),
(382, 10, 36, 'Top'),
(379, 10, 37, 'Middle');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_variants`
--

CREATE TABLE `produk_variants` (
  `id_variant` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `sku` varchar(50) NOT NULL,
  `ukuran_ml` int(10) UNSIGNED NOT NULL,
  `harga_normal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status_variant` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_variants`
--

INSERT INTO `produk_variants` (`id_variant`, `id_produk`, `sku`, `ukuran_ml`, `harga_normal`, `harga`, `stok`, `status_variant`, `created_at`) VALUES
(1, 1, 'ATEA-AUR-50', 50, 249000.00, 199000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(2, 1, 'ATEA-AUR-100', 100, 299000.00, 249000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(3, 2, 'ATEA-ZAF-50', 50, 229000.00, 189000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(4, 2, 'ATEA-ZAF-100', 100, 279000.00, 229000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(5, 3, 'ATEA-NOC-50', 50, 259000.00, 209000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(6, 3, 'ATEA-NOC-100', 100, 299000.00, 249000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(7, 4, 'ATEA-VEL-50', 50, 239000.00, 199000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(8, 4, 'ATEA-VEL-100', 100, 289000.00, 239000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(9, 5, 'ATEA-ELA-50', 50, 249000.00, 209000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(10, 5, 'ATEA-ELA-100', 100, 299000.00, 249000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(11, 6, 'ATEA-NER-50', 50, 219000.00, 179000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(12, 6, 'ATEA-NER-100', 100, 269000.00, 219000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(13, 7, 'ATEA-SOR-50', 50, 229000.00, 189000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(14, 7, 'ATEA-SOR-100', 100, 279000.00, 229000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(15, 8, 'ATEA-ORL-50', 50, 269000.00, 219000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(16, 8, 'ATEA-ORL-100', 100, 299000.00, 259000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(17, 9, 'ATEA-VEY-50', 50, 239000.00, 199000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(18, 9, 'ATEA-VEY-100', 100, 289000.00, 239000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(19, 10, 'ATEA-LUM-50', 50, 259000.00, 209000.00, 50, 'aktif', '2026-10-20 20:25:15'),
(20, 10, 'ATEA-LUM-100', 100, 299000.00, 249000.00, 50, 'aktif', '2026-10-20 20:25:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id_review` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal_review` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'simpan HASH (bcrypt), bukan teks asli',
  `no_telepon` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`id_brand`);

--
-- Indeks untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id_cart`),
  ADD UNIQUE KEY `uq_cart_user` (`id_user`);

--
-- Indeks untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id_cart_item`),
  ADD UNIQUE KEY `uq_cart_variant` (`id_cart`,`id_variant`),
  ADD KEY `idx_ci_variant` (`id_variant`);

--
-- Indeks untuk tabel `fragrance_notes`
--
ALTER TABLE `fragrance_notes`
  ADD PRIMARY KEY (`id_note`),
  ADD UNIQUE KEY `uq_note_nama` (`nama_note`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `uq_kategori_nama` (`nama_kategori`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `idx_order_user` (`id_user`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id_order_item`),
  ADD KEY `idx_oi_order` (`id_order`),
  ADD KEY `idx_oi_variant` (`id_variant`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `idx_pay_order` (`id_order`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `idx_produk_brand` (`id_brand`),
  ADD KEY `idx_produk_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `produk_notes`
--
ALTER TABLE `produk_notes`
  ADD PRIMARY KEY (`id_product_note`),
  ADD UNIQUE KEY `uq_produk_note_stage` (`id_produk`,`id_note`,`note_stage`),
  ADD KEY `idx_pn_note` (`id_note`);

--
-- Indeks untuk tabel `produk_variants`
--
ALTER TABLE `produk_variants`
  ADD PRIMARY KEY (`id_variant`),
  ADD UNIQUE KEY `uq_variant_sku` (`sku`),
  ADD UNIQUE KEY `uq_variant_produk_ukuran` (`id_produk`,`ukuran_ml`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id_review`),
  ADD UNIQUE KEY `uq_review_user_produk` (`id_user`,`id_produk`),
  ADD KEY `idx_rev_produk` (`id_produk`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_user_email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `brand`
--
ALTER TABLE `brand`
  MODIFY `id_brand` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `carts`
--
ALTER TABLE `carts`
  MODIFY `id_cart` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id_cart_item` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fragrance_notes`
--
ALTER TABLE `fragrance_notes`
  MODIFY `id_note` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id_order_item` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `produk_notes`
--
ALTER TABLE `produk_notes`
  MODIFY `id_product_note` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=383;

--
-- AUTO_INCREMENT untuk tabel `produk_variants`
--
ALTER TABLE `produk_variants`
  MODIFY `id_variant` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id_review` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_ci_cart` FOREIGN KEY (`id_cart`) REFERENCES `carts` (`id_cart`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ci_variant` FOREIGN KEY (`id_variant`) REFERENCES `produk_variants` (`id_variant`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oi_variant` FOREIGN KEY (`id_variant`) REFERENCES `produk_variants` (`id_variant`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_brand` FOREIGN KEY (`id_brand`) REFERENCES `brand` (`id_brand`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk_notes`
--
ALTER TABLE `produk_notes`
  ADD CONSTRAINT `fk_pn_note` FOREIGN KEY (`id_note`) REFERENCES `fragrance_notes` (`id_note`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pn_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk_variants`
--
ALTER TABLE `produk_variants`
  ADD CONSTRAINT `fk_variant_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
