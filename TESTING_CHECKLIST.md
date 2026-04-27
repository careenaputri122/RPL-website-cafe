# Testing Checklist - Sistem Manajemen Cafe Berbasis Web

Gunakan checklist ini saat menguji project sebelum presentasi atau pengumpulan.

## A. Setup

- [ ] Project berhasil diekstrak ke `htdocs` atau `www`.
- [ ] `database_cafe.sql` berhasil di-import ke database `cafe_management`.
- [ ] `config/database.php` sudah sesuai dengan konfigurasi MySQL lokal.
- [ ] Website bisa dibuka di `http://localhost/RPL-website-cafe/public`.
- [ ] Jika database belum aktif, fallback session demo tetap menampilkan website.

## B. Autentikasi

- [ ] Pelanggan bisa register akun baru.
- [ ] Pelanggan bisa login.
- [ ] Admin bisa login.
- [ ] Logout berjalan.
- [ ] Halaman admin tidak bisa diakses pelanggan.
- [ ] Password database tersimpan dalam bentuk hash.

## C. Pelanggan - Menu dan Reservasi

- [ ] Halaman Home tampil sesuai tema UI cafe.
- [ ] Halaman Menu menampilkan daftar menu.
- [ ] Search menu berjalan.
- [ ] Filter kategori menu berjalan.
- [ ] Menu habis tidak bisa dipesan.
- [ ] Pelanggan bisa membuat reservasi.
- [ ] Reservasi baru memiliki status `pending`.
- [ ] Halaman Cek Status Reservasi menampilkan reservasi pelanggan.

## D. Pelanggan - Pesanan dan Payment

- [ ] Pesanan dine-in wajib memilih meja.
- [ ] Pesanan take away tidak wajib memilih meja.
- [ ] Pre-order reservasi wajib memilih reservasi aktif.
- [ ] Tombol Lanjut Payment tidak aktif jika keranjang kosong.
- [ ] Stok berkurang saat pesanan dibuat.
- [ ] Payment otomatis dibuat dengan status `pending`.
- [ ] Upload bukti transfer berhasil untuk JPG/PNG/WEBP/PDF maksimal 2 MB.
- [ ] Upload file selain format valid ditolak.
- [ ] Riwayat menampilkan pesanan dan status payment.

## E. Admin - Menu, Meja, Reservasi

- [ ] Admin bisa menambah menu.
- [ ] Admin bisa edit menu.
- [ ] Admin bisa upload foto menu.
- [ ] Admin bisa hapus menu yang belum dipakai transaksi.
- [ ] Admin bisa update stok menu.
- [ ] Admin bisa tambah/edit meja.
- [ ] Admin bisa melihat semua reservasi.
- [ ] Filter reservasi berdasarkan tanggal berjalan.
- [ ] Filter reservasi berdasarkan status berjalan.
- [ ] Admin bisa membuka detail reservasi.
- [ ] Admin bisa konfirmasi reservasi: `pending -> confirmed`.
- [ ] Admin bisa membatalkan reservasi: `pending/confirmed -> cancelled`.

## F. Admin - Pesanan, Payment, dan Stok

- [ ] Admin bisa melihat semua pesanan.
- [ ] Payment tanpa bukti transfer tidak bisa diverifikasi verified.
- [ ] Admin bisa verify payment: `pending -> verified`.
- [ ] Payment verified membuat pesanan menjadi `diproses`.
- [ ] Admin bisa membuat pesanan `diproses -> selesai`.
- [ ] Pesanan dine-in selesai membuat meja kembali `tersedia`.
- [ ] Admin bisa membatalkan pesanan.
- [ ] Pesanan dibatalkan membuat stok menu kembali.
- [ ] Pesanan dibatalkan membuat meja dine-in kembali `tersedia`.
- [ ] Admin bisa reject payment: `pending -> rejected`.
- [ ] Payment rejected membuat pesanan `dibatalkan`.
- [ ] Payment rejected mengembalikan stok.

## G. Laporan

- [ ] Laporan bisa difilter berdasarkan tanggal.
- [ ] Laporan bisa difilter berdasarkan jenis pesanan.
- [ ] Total pendapatan hanya menghitung payment `verified`.
- [ ] Ringkasan dine-in, take away, dan reservasi tampil.
- [ ] Menu terlaris tampil.
- [ ] Tombol cetak laporan berjalan.

## H. Keamanan Form

- [ ] Semua form POST memiliki CSRF token.
- [ ] Submit form tanpa token ditolak.
- [ ] Form upload hanya menerima file sesuai validasi MIME dan ekstensi.
- [ ] File upload disimpan dengan nama aman di folder `uploads`.

## I. Alur Status Final

```txt
Reservasi: pending -> confirmed / cancelled
Payment  : pending -> verified / rejected
Pesanan  : pending -> diproses -> selesai / dibatalkan
Meja     : tersedia -> terisi -> tersedia
Stok     : berkurang saat pesanan dibuat, kembali jika pesanan dibatalkan/payment rejected
```
