# Cafe Nusantara - Sistem Manajemen Cafe Berbasis Web

Project ini sudah disesuaikan dengan SRS dan referensi UI/UX yang diberikan. Versi ini adalah versi lanjutan setelah prototype: database MySQL sudah diintegrasikan, tetapi aplikasi tetap memiliki fallback data session supaya masih bisa dibuka walaupun database belum di-import.

## Cara menjalankan

1. Extract folder `RPL-website-cafe` ke `htdocs` atau `www`.
2. Buka phpMyAdmin, buat/import database dari file:

```txt
database_cafe.sql
```

3. Cek konfigurasi database di:

```txt
config/database.php
```

Default XAMPP/Laragon:

```txt
host: 127.0.0.1
port: 3306
dbname: cafe_management
username: root
password: kosong
```

4. Buka website:

```txt
http://localhost/RPL-website-cafe/public
```

Jika folder project diganti, sistem akan mendeteksi base URL otomatis. Jika perlu, ubah `config/app.php` bagian `base_url`.

## Akun demo database

Pelanggan:

```txt
Email: budi@email.com
Password: password123
```

Admin:

```txt
Email: admin@cafe.com
Password: admin123
```

Password pada database disimpan memakai hash dan dicek dengan `password_verify()`.

## Fitur pelanggan

- Home sesuai referensi UI.
- Menu dengan pencarian, filter kategori, harga, foto, status stok tersedia/habis.
- Register, login, logout, edit profil, dan ganti password.
- Reservasi meja berdasarkan tanggal, jam, jumlah orang, catatan, dan kapasitas meja.
- Pesan menu untuk dine-in, take away, atau pre-order berdasarkan reservasi.
- Payment dengan info rekening dan upload bukti transfer.
- Riwayat reservasi, pesanan, dan status payment.

## Fitur admin

- Dashboard: pendapatan hari ini, reservasi hari ini, payment pending, stok hampir habis.
- Kelola menu: tambah, edit, hapus, upload foto, update stok, minimum stok, menu unggulan.
- Kelola meja: tambah/edit meja, kapasitas, dan status.
- Kelola reservasi: filter tanggal/status, konfirmasi, batalkan, hapus.
- Lihat daftar pesanan dan detail item.
- Verifikasi payment: lihat bukti transfer, verified/rejected, catatan admin.
- Laporan penjualan: filter tanggal dan cetak laporan dari browser.
- Edit profil admin dan ganti password.

## Urutan perbaikan yang sudah dilakukan

1. Integrasi database MySQL.
2. Login/register asli.
3. CRUD menu dan meja ke database.
4. Reservasi ke database.
5. Pesanan dan detail pesanan ke database.
6. Payment upload dan verifikasi ke database.
7. Stok menu otomatis berkurang saat pesanan dibuat.
8. Laporan penjualan.
9. Dokumentasi pembagian tugas 5 orang.

## Catatan fallback

Kalau database belum di-import atau koneksi MySQL gagal, aplikasi tetap memakai data session demo agar UI masih bisa dicek. Supaya data tersimpan permanen, wajib import `database_cafe.sql`.

## Update lanjutan alur aplikasi

Versi ini juga sudah ditambah penyempurnaan alur operasional:

1. Status pesanan dapat diubah admin: `pending -> diproses -> selesai / dibatalkan`.
2. Meja dine-in otomatis menjadi `terisi` saat pesanan dibuat dan kembali `tersedia` saat pesanan selesai/dibatalkan.
3. Stok berkurang saat pesanan dibuat dan kembali jika pesanan dibatalkan atau payment rejected.
4. Admin dapat membuka detail reservasi beserta pesanan dan payment customer.
5. Semua form POST memakai CSRF token.
6. Upload foto menu dan bukti transfer diperkuat dengan validasi ekstensi, MIME type, ukuran file, dan nama file aman.
7. Halaman **Cek Status Reservasi** ditambahkan untuk pelanggan.
8. Laporan penjualan dirapikan dengan filter jenis pesanan, ringkasan per jenis, dan menu terlaris.
9. Dokumentasi pengguna dan checklist testing ditambahkan.

## Alur status final

```txt
Reservasi: pending -> confirmed / cancelled
Payment  : pending -> verified / rejected
Pesanan  : pending -> diproses -> selesai / dibatalkan
Meja     : tersedia -> terisi -> tersedia
Stok     : berkurang saat pesanan dibuat, kembali jika pesanan dibatalkan/payment rejected
```

## Dokumen tambahan

- `PANDUAN_PENGGUNA.md`
- `TESTING_CHECKLIST.md`
- `PEMBAGIAN_TUGAS.md`
