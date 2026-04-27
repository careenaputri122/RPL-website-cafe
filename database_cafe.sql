-- ============================================================
-- DATABASE RESTORAN
-- Generated from ERD Diagram
-- ============================================================

CREATE DATABASE IF NOT EXISTS restoran;
USE restoran;

-- ============================================================
-- TABLE: admin
-- ============================================================
CREATE TABLE admin (
    id_admin    INT PRIMARY KEY AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL
);

-- ============================================================
-- TABLE: pelanggan
-- ============================================================
CREATE TABLE pelanggan (
    id_pelanggan    INT PRIMARY KEY AUTO_INCREMENT,
    nama            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    no_telp         VARCHAR(20),
    password        VARCHAR(255) NOT NULL
);

-- ============================================================
-- TABLE: jenis_pesanan
-- ============================================================
CREATE TABLE jenis_pesanan (
    id_jenis_pesanan    INT PRIMARY KEY AUTO_INCREMENT,
    nama_pesanan        VARCHAR(100) NOT NULL
);

-- ============================================================
-- TABLE: menu
-- ============================================================
CREATE TABLE menu (
    id_menu     INT PRIMARY KEY AUTO_INCREMENT,
    nama_menu   VARCHAR(100) NOT NULL,
    harga       DECIMAL(10, 2) NOT NULL,
    deskripsi   TEXT,
    foto        VARCHAR(255)
);

-- ============================================================
-- TABLE: Meja
-- ============================================================
CREATE TABLE Meja (
    id_meja     INT PRIMARY KEY AUTO_INCREMENT,
    no_meja     VARCHAR(10) NOT NULL,
    kapasitas   INT NOT NULL,
    status      ENUM('tersedia', 'terisi', 'reserved') DEFAULT 'tersedia'
);

-- ============================================================
-- TABLE: stok_menu
-- ============================================================
CREATE TABLE stok_menu (
    id_stok     INT PRIMARY KEY AUTO_INCREMENT,
    id_menu     INT NOT NULL,
    jumlah_stok INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_stok_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- ============================================================
-- TABLE: reservasi
-- ============================================================
CREATE TABLE reservasi (
    id_reservasi        INT PRIMARY KEY AUTO_INCREMENT,
    id_pelanggan        INT NOT NULL,
    id_meja             INT NOT NULL,
    nama_tamu           VARCHAR(100) NOT NULL,
    tanggal             DATE NOT NULL,
    jam                 TIME NOT NULL,
    jumlah_orang        INT NOT NULL,
    status_reservasi    ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    biaya_booking       DECIMAL(10, 2) DEFAULT 0,
    catatan             TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservasi_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservasi_meja FOREIGN KEY (id_meja) REFERENCES Meja(id_meja)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ============================================================
-- TABLE: pesanan
-- ============================================================
-- Logika jenis_pesanan:
--   1 = dine-in   -> id_reservasi NULL, id_meja WAJIB diisi
--   2 = reservasi -> id_reservasi WAJIB diisi, id_meja WAJIB diisi
--   3 = take away -> id_reservasi NULL, id_meja NULL
-- ============================================================
CREATE TABLE pesanan (
    id_pesanan          INT PRIMARY KEY AUTO_INCREMENT,
    id_pelanggan        INT NOT NULL,
    id_reservasi        INT DEFAULT NULL,
    id_meja             INT DEFAULT NULL,
    id_jenis_pesanan    INT NOT NULL,
    total_harga         DECIMAL(10, 2) NOT NULL DEFAULT 0,
    deposit             DECIMAL(10, 2) DEFAULT 0,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pesanan_reservasi FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pesanan_meja FOREIGN KEY (id_meja) REFERENCES Meja(id_meja)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pesanan_jenis FOREIGN KEY (id_jenis_pesanan) REFERENCES jenis_pesanan(id_jenis_pesanan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    -- take away tidak boleh punya meja atau reservasi
    CONSTRAINT chk_takeaway CHECK (
        id_jenis_pesanan != 3 OR (id_meja IS NULL AND id_reservasi IS NULL)
    ),
    -- reservasi wajib punya id_reservasi dan id_meja
    CONSTRAINT chk_reservasi CHECK (
        id_jenis_pesanan != 2 OR (id_reservasi IS NOT NULL AND id_meja IS NOT NULL)
    ),
    -- dine-in wajib punya id_meja tapi tidak perlu reservasi
    CONSTRAINT chk_dinein CHECK (
        id_jenis_pesanan != 1 OR (id_meja IS NOT NULL AND id_reservasi IS NULL)
    )
);

-- ============================================================
-- TABLE: detail_pesanan
-- ============================================================
CREATE TABLE detail_pesanan (
    id_detail   INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan  INT NOT NULL,
    id_menu     INT NOT NULL,
    jumlah      INT NOT NULL DEFAULT 1,
    harga_satuan DECIMAL(10, 2) NOT NULL,
    subtotal    DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ============================================================
-- TABLE: payment
-- ============================================================
CREATE TABLE payment (
    id_payment          INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan          INT NOT NULL,
    id_admin            INT,
    bukti_tf            VARCHAR(255),
    tanggal_upload      DATETIME,
    status_payment      ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    catatan_admin       TEXT,
    tanggal_verifikasi  DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payment_admin FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- ============================================================
-- END OF SCRIPT
-- ============================================================
