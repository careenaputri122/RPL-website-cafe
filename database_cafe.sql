-- ============================================================
-- DATABASE: cafe_management
-- Sistem Manajemen Cafe Berbasis Web
-- Fix: Tambah booking payment record untuk data demo reservasi
-- ============================================================

CREATE DATABASE IF NOT EXISTS cafe_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE cafe_management;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS reservasi;
DROP TABLE IF EXISTS stok_menu;
DROP TABLE IF EXISTS meja;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS jenis_pesanan;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TABEL ADMIN
-- ============================================================
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telp VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL PELANGGAN
-- ============================================================
CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_telp VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL JENIS PESANAN
-- ============================================================
CREATE TABLE jenis_pesanan (
    id_jenis_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pesanan ENUM('dine-in', 'take-away', 'reservasi') NOT NULL,
    deskripsi VARCHAR(255)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL MENU
-- ============================================================
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori ENUM('Makanan', 'Minuman', 'Dessert') NOT NULL DEFAULT 'Makanan',
    harga DECIMAL(10,2) NOT NULL,
    deskripsi TEXT,
    foto VARCHAR(255) DEFAULT 'uploads/menu/default.svg',
    is_unggulan TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL MEJA
-- ============================================================
CREATE TABLE meja (
    id_meja INT AUTO_INCREMENT PRIMARY KEY,
    no_meja VARCHAR(10) NOT NULL UNIQUE,
    kapasitas INT NOT NULL,
    status ENUM('tersedia', 'terisi', 'nonaktif') NOT NULL DEFAULT 'tersedia',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL STOK MENU
-- ============================================================
CREATE TABLE stok_menu (
    id_stok INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT NOT NULL,
    jumlah_stok INT NOT NULL DEFAULT 0,
    minimum_stok INT NOT NULL DEFAULT 5,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_stok_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL RESERVASI
-- ============================================================
CREATE TABLE reservasi (
    id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_meja INT NOT NULL,
    nama_tamu VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    jumlah_orang INT NOT NULL,
    status_reservasi ENUM('pending', 'confirmed', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    biaya_booking DECIMAL(10,2) NOT NULL DEFAULT 15000,
    catatan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reservasi_tanggal_status (tanggal, status_reservasi),
    CONSTRAINT fk_reservasi_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservasi_meja FOREIGN KEY (id_meja) REFERENCES meja(id_meja)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- TABEL PESANAN
-- ============================================================
CREATE TABLE pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_reservasi INT DEFAULT NULL,
    id_meja INT DEFAULT NULL,
    id_jenis_pesanan INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL DEFAULT 0,
    deposit DECIMAL(10,2) NOT NULL DEFAULT 0,
    status_pesanan ENUM('pending', 'diproses', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pesanan_created (created_at),
    CONSTRAINT fk_pesanan_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pesanan_reservasi FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pesanan_meja FOREIGN KEY (id_meja) REFERENCES meja(id_meja)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pesanan_jenis FOREIGN KEY (id_jenis_pesanan) REFERENCES jenis_pesanan(id_jenis_pesanan)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- TABEL DETAIL PESANAN
-- ============================================================
CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_menu INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- TABEL PAYMENT
-- Menampung dua tipe:
--   tipe='pesanan'  → id_pesanan diisi, id_reservasi boleh NULL
--   tipe='booking'  → id_pesanan NULL, id_reservasi diisi (booking fee reservasi)
-- ============================================================
CREATE TABLE payment (
    id_payment INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT DEFAULT NULL,
    id_reservasi INT DEFAULT NULL,
    id_admin INT DEFAULT NULL,
    tipe ENUM('pesanan', 'booking', 'pelunasan') NOT NULL DEFAULT 'pesanan',
    jumlah DECIMAL(10,2) NOT NULL DEFAULT 0,
    bukti_tf VARCHAR(255) DEFAULT NULL,
    tanggal_upload DATETIME DEFAULT NULL,
    status_payment ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    catatan_admin TEXT DEFAULT NULL,
    tanggal_verifikasi DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payment_status (status_payment),
    CONSTRAINT fk_payment_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payment_reservasi FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payment_admin FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- DATA DEMO
-- Password admin@cafe.com  = admin123
-- Password budi@email.com  = password123
-- ============================================================

INSERT INTO admin (nama, email, password, no_telp) VALUES
('Administrator', 'admin@cafe.com',
 '$2y$12$wHvJ0jRQXhOx04IV7gyYH./TmkV/geaO/kBflW258BGB3zvBIJdva',
 '081234567890');

INSERT INTO pelanggan (nama, email, no_telp, password) VALUES
('Budi Santoso', 'budi@email.com', '081234567891',
 '$2y$12$fnLwOc68pPxsTFSKzBUtM.WGnbyl1ZqIxS4EmQCyf/swRKtTyd9/C');

INSERT INTO jenis_pesanan (nama_pesanan, deskripsi) VALUES
('dine-in',   'Pesanan makan di tempat'),
('take-away',  'Pesanan dibawa pulang'),
('reservasi',  'Pre-order untuk reservasi meja');

INSERT INTO menu (nama_menu, kategori, harga, deskripsi, foto, is_unggulan) VALUES
('Nasi Goreng Spesial', 'Makanan', 35000,
 'Nasi goreng dengan telur, ayam suwir, dan bumbu rempah pilihan.',
 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80', 1),
('Ayam Bakar Madu', 'Makanan', 42000,
 'Ayam bakar dengan marinasi madu dan kecap, disajikan dengan lalapan.',
 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&q=80', 1),
('Mi Goreng Seafood', 'Makanan', 38000,
 'Mi goreng dengan udang, cumi, dan sayuran segar.',
 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600&q=80', 0),
('Soto Ayam Lamongan', 'Makanan', 28000,
 'Soto ayam khas Lamongan dengan kuah bening gurih dan koya.',
 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=600&q=80', 0),
('Es Kopi Susu', 'Minuman', 22000,
 'Kopi susu gula aren dengan espresso premium dan susu segar.',
 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&q=80', 1),
('Matcha Latte', 'Minuman', 28000,
 'Matcha premium Jepang dengan susu oat, disajikan dingin atau panas.',
 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=600&q=80', 1),
('Jus Alpukat', 'Minuman', 20000,
 'Jus alpukat segar dengan susu kental manis dan topping coklat.',
 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=600&q=80', 0),
('Teh Tarik', 'Minuman', 15000,
 'Teh tarik khas Malaysia dengan aroma rempah lembut.',
 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&q=80', 0),
('Tiramisu Slice', 'Dessert', 32000,
 'Tiramisu klasik Italia dengan mascarpone premium dan espresso.',
 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&q=80', 1),
('Lava Cake Coklat', 'Dessert', 35000,
 'Molten chocolate lava cake dengan isian coklat lumer.',
 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=600&q=80', 1),
('Pancake Stack', 'Dessert', 30000,
 'Tumpukan pancake fluffy dengan maple syrup, butter, dan buah segar.',
 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?w=600&q=80', 0),
('Paket Hemat Duo', 'Makanan', 55000,
 'Nasi goreng dan es kopi susu, hemat untuk dua orang.',
 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80', 0);

INSERT INTO stok_menu (id_menu, jumlah_stok, minimum_stok) VALUES
(1,24,5),(2,18,5),(3,12,5),(4,0,5),(5,30,5),(6,16,5),
(7,10,5),(8,28,5),(9,8,5),(10,6,5),(11,14,5),(12,5,5);

INSERT INTO meja (no_meja, kapasitas, status) VALUES
('A1',2,'tersedia'),('A2',2,'terisi'),('A3',4,'tersedia'),('A4',4,'tersedia'),
('B1',6,'tersedia'),('B2',6,'tersedia'),('B3',8,'tersedia'),('B4',8,'tersedia'),
('C1',10,'tersedia'),('C2',10,'tersedia'),('VIP1',6,'tersedia'),('VIP2',8,'tersedia');

-- Reservasi demo (status confirmed karena booking fee sudah dibayar)
INSERT INTO reservasi (id_pelanggan, id_meja, nama_tamu, tanggal, jam, jumlah_orang, status_reservasi, biaya_booking, catatan)
VALUES (1, 3, 'Budi Santoso', CURDATE(), '19:00:00', 4, 'confirmed', 15000, 'Dekat jendela');

-- Pesanan demo
INSERT INTO pesanan (id_pelanggan, id_reservasi, id_meja, id_jenis_pesanan, total_harga, deposit, status_pesanan)
VALUES
(1, 1, 3, 3, 126000, 63000, 'pending'),   -- pre-order untuk reservasi 1
(1, NULL, 2, 1, 69000, 69000, 'diproses'); -- dine-in biasa

INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES
(1, 1, 2, 35000, 70000),
(1, 6, 2, 28000, 56000),
(2, 5, 1, 22000, 22000),
(2, 9, 1, 32000, 32000),
(2, 8, 1, 15000, 15000);

-- ============================================================
-- DATA PAYMENT
-- PENTING: 3 record payment:
--   1. Booking fee reservasi 1  → tipe='booking', id_pesanan=NULL  ← INI YANG DULU HILANG
--   2. Pre-order pesanan 1      → tipe='pesanan', linked ke reservasi 1
--   3. Dine-in pesanan 2        → tipe='pesanan', sudah verified
-- ============================================================
INSERT INTO payment (id_pesanan, id_reservasi, id_admin, tipe, jumlah, bukti_tf, status_payment, tanggal_upload, catatan_admin, tanggal_verifikasi)
VALUES
-- [1] Booking fee reservasi 1 — pending, belum upload bukti (ini yang hilang sebelumnya!)
(NULL, 1, NULL, 'booking', 15000, NULL, 'pending', NULL, NULL, NULL),

-- [2] Pre-order pesanan 1 (linked juga ke reservasi 1) — pending, belum upload bukti
(1, 1, NULL, 'pesanan', 63000, NULL, 'pending', NULL, NULL, NULL),

-- [3] Dine-in pesanan 2 — sudah verified oleh admin
(2, NULL, 1, 'pesanan', 69000, NULL, 'verified', NOW(), 'Pembayaran demo sudah valid.', NOW());
