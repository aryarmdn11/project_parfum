-- ============================================================
--  DATABASE: atea_parfums  (ATEA Parfums E-commerce)
--  Untuk MySQL / MariaDB (XAMPP + phpMyAdmin)
--  Cara pakai: phpMyAdmin > tab "SQL" (atau Import) > paste/pilih file ini > Go
-- ============================================================

DROP DATABASE IF EXISTS atea_parfums;
CREATE DATABASE atea_parfums CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE atea_parfums;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. BRAND
-- ------------------------------------------------------------
CREATE TABLE brand (
  id_brand     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_brand   VARCHAR(100) NOT NULL,
  deskripsi    TEXT NULL,
  logo         VARCHAR(255) NULL,
  negara_asal  VARCHAR(100) NULL,
  PRIMARY KEY (id_brand)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. KATEGORI
-- ------------------------------------------------------------
CREATE TABLE kategori (
  id_kategori    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori  VARCHAR(100) NOT NULL,
  deskripsi      TEXT NULL,
  PRIMARY KEY (id_kategori),
  UNIQUE KEY uq_kategori_nama (nama_kategori)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. USER  (nama tabel `user`, ditulis dengan backtick)
-- ------------------------------------------------------------
CREATE TABLE `user` (
  id_user     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  password    VARCHAR(255) NOT NULL COMMENT 'simpan HASH (bcrypt), bukan teks asli',
  no_telepon  VARCHAR(20) NULL,
  role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_user_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. PRODUK
-- ------------------------------------------------------------
CREATE TABLE produk (
  id_produk      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_brand       INT UNSIGNED NOT NULL,
  id_kategori    INT UNSIGNED NOT NULL,
  nama_produk    VARCHAR(150) NOT NULL,
  deskripsi      TEXT NULL,
  gambar         VARCHAR(255) NULL,
  konsentrasi    VARCHAR(50) NULL COMMENT 'mis. Eau de Parfum, Extrait de Parfum',
  status_produk  ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_produk),
  KEY idx_produk_brand (id_brand),
  KEY idx_produk_kategori (id_kategori),
  CONSTRAINT fk_produk_brand    FOREIGN KEY (id_brand)    REFERENCES brand (id_brand)       ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_produk_kategori FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. PRODUK_VARIANTS  (ukuran 50 ml / 100 ml; harga & stok ada di sini)
-- ------------------------------------------------------------
CREATE TABLE produk_variants (
  id_variant      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_produk       INT UNSIGNED NOT NULL,
  sku             VARCHAR(50) NOT NULL,
  ukuran_ml       INT UNSIGNED NOT NULL,
  harga           DECIMAL(12,2) NOT NULL,
  stok            INT UNSIGNED NOT NULL DEFAULT 0,
  status_variant  ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_variant),
  UNIQUE KEY uq_variant_sku (sku),
  UNIQUE KEY uq_variant_produk_ukuran (id_produk, ukuran_ml),
  CONSTRAINT fk_variant_produk FOREIGN KEY (id_produk) REFERENCES produk (id_produk) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_variant_harga CHECK (harga >= 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. FRAGRANCE_NOTES
-- ------------------------------------------------------------
CREATE TABLE fragrance_notes (
  id_note      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_note    VARCHAR(100) NOT NULL,
  jenis_aroma  VARCHAR(50) NULL,
  PRIMARY KEY (id_note),
  UNIQUE KEY uq_note_nama (nama_note)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. PRODUK_NOTES  (M:N produk <-> fragrance_notes, per tahap Top/Middle/Base)
-- ------------------------------------------------------------
CREATE TABLE produk_notes (
  id_product_note  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_produk        INT UNSIGNED NOT NULL,
  id_note          INT UNSIGNED NOT NULL,
  note_stage       ENUM('Top','Middle','Base') NOT NULL,
  PRIMARY KEY (id_product_note),
  UNIQUE KEY uq_produk_note_stage (id_produk, id_note, note_stage),
  KEY idx_pn_note (id_note),
  CONSTRAINT fk_pn_produk FOREIGN KEY (id_produk) REFERENCES produk (id_produk) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pn_note   FOREIGN KEY (id_note)   REFERENCES fragrance_notes (id_note) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. CARTS  (satu keranjang aktif per user -> id_user UNIQUE)
-- ------------------------------------------------------------
CREATE TABLE carts (
  id_cart     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user     INT UNSIGNED NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cart),
  UNIQUE KEY uq_cart_user (id_user),
  CONSTRAINT fk_cart_user FOREIGN KEY (id_user) REFERENCES `user` (id_user) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. CART_ITEMS
-- ------------------------------------------------------------
CREATE TABLE cart_items (
  id_cart_item  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cart       INT UNSIGNED NOT NULL,
  id_variant    INT UNSIGNED NOT NULL,
  jumlah        INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id_cart_item),
  UNIQUE KEY uq_cart_variant (id_cart, id_variant),
  KEY idx_ci_variant (id_variant),
  CONSTRAINT fk_ci_cart    FOREIGN KEY (id_cart)    REFERENCES carts (id_cart) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ci_variant FOREIGN KEY (id_variant) REFERENCES produk_variants (id_variant) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_ci_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. ORDERS
-- ------------------------------------------------------------
CREATE TABLE orders (
  id_order           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user            INT UNSIGNED NOT NULL,
  tanggal_pesanan    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_harga        DECIMAL(12,2) NOT NULL DEFAULT 0,
  alamat_pengiriman  TEXT NOT NULL,
  status_pesanan     ENUM('menunggu_pembayaran','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_order),
  KEY idx_order_user (id_user),
  CONSTRAINT fk_order_user FOREIGN KEY (id_user) REFERENCES `user` (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. ORDER_ITEMS  (harga_satuan = snapshot harga saat transaksi)
-- ------------------------------------------------------------
CREATE TABLE order_items (
  id_order_item  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_order       INT UNSIGNED NOT NULL,
  id_variant     INT UNSIGNED NOT NULL,
  jumlah         INT UNSIGNED NOT NULL,
  harga_satuan   DECIMAL(12,2) NOT NULL,
  subtotal       DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id_order_item),
  KEY idx_oi_order (id_order),
  KEY idx_oi_variant (id_variant),
  CONSTRAINT fk_oi_order   FOREIGN KEY (id_order)   REFERENCES orders (id_order) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_oi_variant FOREIGN KEY (id_variant) REFERENCES produk_variants (id_variant) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_oi_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. PAYMENTS
-- ------------------------------------------------------------
CREATE TABLE payments (
  id_payment          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_order            INT UNSIGNED NOT NULL,
  metode_pembayaran   VARCHAR(50) NOT NULL COMMENT 'mis. Transfer Bank, QRIS, E-Wallet',
  tanggal_pembayaran  DATETIME NULL,
  jumlah_pembayaran   DECIMAL(12,2) NOT NULL,
  status_pembayaran   ENUM('pending','berhasil','gagal','refund') NOT NULL DEFAULT 'pending',
  nomor_referensi     VARCHAR(100) NULL,
  PRIMARY KEY (id_payment),
  KEY idx_pay_order (id_order),
  CONSTRAINT fk_pay_order FOREIGN KEY (id_order) REFERENCES orders (id_order) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. REVIEWS
-- ------------------------------------------------------------
CREATE TABLE reviews (
  id_review       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user         INT UNSIGNED NOT NULL,
  id_produk       INT UNSIGNED NOT NULL,
  rating          TINYINT UNSIGNED NOT NULL,
  komentar        TEXT NULL,
  tanggal_review  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_review),
  UNIQUE KEY uq_review_user_produk (id_user, id_produk),
  KEY idx_rev_produk (id_produk),
  CONSTRAINT fk_rev_user   FOREIGN KEY (id_user)   REFERENCES `user` (id_user) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_rev_produk FOREIGN KEY (id_produk) REFERENCES produk (id_produk) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_rev_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  DATA AWAL (sesuai produk di website)
-- ============================================================

INSERT INTO brand (id_brand, nama_brand, deskripsi, logo, negara_asal) VALUES
(1, 'ATEA Parfums', 'A scent that feels like you. Brand parfum dengan sepuluh karakter aroma.', 'assets/atea-logo.png', 'Indonesia');

INSERT INTO kategori (id_kategori, nama_kategori, deskripsi) VALUES
(1, 'Floral', 'Aroma bunga yang lembut, romantis, dan elegan.'),
(2, 'Fresh', 'Aroma segar, bersih, dan ringan untuk aktivitas harian.'),
(3, 'Woody', 'Aroma kayu yang hangat, dalam, dan berkarakter.'),
(4, 'Oriental', 'Aroma amber, rempah, dan manis yang hangat serta sensual.');

INSERT INTO produk (id_produk, id_brand, id_kategori, nama_produk, deskripsi, gambar, konsentrasi, status_produk) VALUES
(1, 1, 1, 'Orlune', 'Perpaduan hangat yang lembut dengan sentuhan floral dan nuansa creamy. Orlune dirancang untuk memberi kesan elegan, tenang, dan mudah diingat, dari momen sehari-hari hingga malam spesial.', 'assets/orlune-bottle.png', 'Extrait De Parfum', 'aktif'),
(2, 1, 2, 'Aurelle', 'Placeholder deskripsi: aroma cerah dengan karakter elegan untuk menemani aktivitas harian.', NULL, 'Eau De Parfum', 'aktif'),
(3, 1, 3, 'Veloura', 'Placeholder deskripsi: aroma lembut dan modern dengan karakter yang terasa dekat di kulit.', NULL, 'Eau De Parfum', 'aktif'),
(4, 1, 4, 'Solenne', 'Placeholder deskripsi: aroma hangat dan memikat untuk suasana santai maupun acara istimewa.', NULL, 'Eau De Parfum', 'aktif'),
(5, 1, 2, 'Élaris', 'Placeholder deskripsi: aroma segar dengan sentuhan bersih yang terasa ringan dan berenergi.', NULL, 'Eau De Parfum', 'aktif'),
(6, 1, 4, 'Noctelle', 'Placeholder deskripsi: aroma malam dengan karakter dalam, sensual, dan berkesan.', NULL, 'Eau De Parfum', 'aktif'),
(7, 1, 1, 'Amoura', 'Placeholder deskripsi: aroma romantis dengan nuansa bunga yang lembut dan manis.', NULL, 'Eau De Parfum', 'aktif'),
(8, 1, 2, 'Célestine', 'Placeholder deskripsi: aroma bersih dan airy dengan sentuhan floral yang modern.', NULL, 'Eau De Parfum', 'aktif'),
(9, 1, 1, 'Rosavelle', 'Placeholder deskripsi: aroma floral yang anggun dengan sentuhan buah yang segar.', NULL, 'Eau De Parfum', 'aktif'),
(10, 1, 3, 'Vespera', 'Placeholder deskripsi: aroma elegan dengan karakter woody-amber yang meninggalkan kesan.', NULL, 'Eau De Parfum', 'aktif');

INSERT INTO produk_variants (sku, id_produk, ukuran_ml, harga, stok) VALUES
('ATEA-ORL-50', 1, 50, 319000, 50),
('ATEA-ORL-100', 1, 100, 489000, 50),
('ATEA-AUR-50', 2, 50, 279000, 50),
('ATEA-AUR-100', 2, 100, 429000, 50),
('ATEA-VEL-50', 3, 50, 289000, 50),
('ATEA-VEL-100', 3, 100, 449000, 50),
('ATEA-SOL-50', 4, 50, 299000, 50),
('ATEA-SOL-100', 4, 100, 459000, 50),
('ATEA-ELA-50', 5, 50, 289000, 50),
('ATEA-ELA-100', 5, 100, 439000, 50),
('ATEA-NOC-50', 6, 50, 299000, 50),
('ATEA-NOC-100', 6, 100, 469000, 50),
('ATEA-AMO-50', 7, 50, 279000, 50),
('ATEA-AMO-100', 7, 100, 429000, 50),
('ATEA-CEL-50', 8, 50, 299000, 50),
('ATEA-CEL-100', 8, 100, 459000, 50),
('ATEA-ROS-50', 9, 50, 289000, 50),
('ATEA-ROS-100', 9, 100, 439000, 50),
('ATEA-VES-50', 10, 50, 309000, 50),
('ATEA-VES-100', 10, 100, 479000, 50);

INSERT INTO fragrance_notes (id_note, nama_note, jenis_aroma) VALUES
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

INSERT INTO produk_notes (id_produk, id_note, note_stage) VALUES
(1, 1, 'Top'),
(1, 2, 'Top'),
(1, 3, 'Top'),
(1, 4, 'Middle'),
(1, 5, 'Middle'),
(1, 6, 'Middle'),
(1, 7, 'Base'),
(1, 8, 'Base'),
(1, 9, 'Base'),
(2, 1, 'Top'),
(2, 10, 'Top'),
(2, 11, 'Middle'),
(2, 12, 'Middle'),
(2, 9, 'Base'),
(2, 13, 'Base'),
(3, 2, 'Top'),
(3, 3, 'Top'),
(3, 14, 'Middle'),
(3, 15, 'Middle'),
(3, 16, 'Base'),
(3, 9, 'Base'),
(4, 17, 'Top'),
(4, 18, 'Top'),
(4, 5, 'Middle'),
(4, 19, 'Middle'),
(4, 20, 'Base'),
(4, 21, 'Base'),
(5, 22, 'Top'),
(5, 23, 'Top'),
(5, 24, 'Middle'),
(5, 25, 'Middle'),
(5, 26, 'Base'),
(5, 27, 'Base'),
(6, 28, 'Top'),
(6, 29, 'Top'),
(6, 30, 'Middle'),
(6, 5, 'Middle'),
(6, 31, 'Base'),
(6, 32, 'Base'),
(7, 33, 'Top'),
(7, 34, 'Top'),
(7, 5, 'Middle'),
(7, 35, 'Middle'),
(7, 7, 'Base'),
(7, 9, 'Base'),
(8, 36, 'Top'),
(8, 1, 'Top'),
(8, 4, 'Middle'),
(8, 37, 'Middle'),
(8, 27, 'Base'),
(8, 20, 'Base'),
(9, 38, 'Top'),
(9, 1, 'Top'),
(9, 5, 'Middle'),
(9, 39, 'Middle'),
(9, 9, 'Base'),
(9, 8, 'Base'),
(10, 1, 'Top'),
(10, 40, 'Top'),
(10, 41, 'Middle'),
(10, 42, 'Middle'),
(10, 20, 'Base'),
(10, 26, 'Base'),
(10, 9, 'Base');


-- ============================================================
--  QUERY CONTOH (opsional, untuk cek hasil)
-- ============================================================
-- Daftar produk + varian + harga:
-- SELECT p.nama_produk, v.ukuran_ml, v.harga, v.stok
-- FROM produk p JOIN produk_variants v ON v.id_produk = p.id_produk
-- ORDER BY p.id_produk, v.ukuran_ml;

-- Fragrance pyramid satu produk:
-- SELECT pn.note_stage, GROUP_CONCAT(fn.nama_note SEPARATOR ', ') AS notes
-- FROM produk_notes pn JOIN fragrance_notes fn ON fn.id_note = pn.id_note
-- WHERE pn.id_produk = 1
-- GROUP BY pn.note_stage ORDER BY FIELD(pn.note_stage,'Top','Middle','Base');