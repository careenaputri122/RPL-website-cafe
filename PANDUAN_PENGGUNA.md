# Panduan Pengguna - Sistem Manajemen Cafe Berbasis Web

Dokumen ini menjelaskan cara memakai fitur utama pelanggan dan admin.

## 1. Akses Website

Buka website melalui browser:

```txt
http://localhost/RPL-website-cafe/public
```

Akun demo:

```txt
Pelanggan: budi@email.com / password123
Admin    : admin@cafe.com / admin123
```

## 2. Panduan Pelanggan

### Register dan Login

1. Klik tombol **Daftar** untuk membuat akun pelanggan baru.
2. Isi nama, email, nomor telepon, dan password.
3. Setelah berhasil, pelanggan otomatis login.
4. Pelanggan lama bisa klik **Masuk** dan menggunakan email serta password.

### Melihat Menu

1. Buka halaman **Menu**.
2. Gunakan kolom pencarian untuk mencari nama menu.
3. Gunakan filter kategori: Semua, Makanan, Minuman, Dessert.
4. Perhatikan status stok. Menu yang habis tidak dapat dipesan.

### Membuat Reservasi

1. Login sebagai pelanggan.
2. Buka halaman **Reservasi**.
3. Pilih tanggal, jam, jumlah orang, meja, dan catatan jika diperlukan.
4. Klik **Buat Reservasi**.
5. Setelah berhasil, pelanggan diarahkan ke halaman **Cek Status Reservasi**.

Alur status reservasi:

```txt
Reservasi: pending -> confirmed / cancelled
```

### Membuat Pesanan

1. Login sebagai pelanggan.
2. Buka halaman **Pesan**.
3. Pilih jenis layanan:
   - **Dine-In**: wajib memilih meja.
   - **Take Away**: tidak perlu memilih meja.
   - **Reservasi**: wajib memilih reservasi aktif.
4. Tambahkan menu ke keranjang.
5. Tombol **Lanjut Payment** aktif jika syarat pesanan sudah lengkap.
6. Klik **Lanjut Payment** untuk membuat pesanan.

Alur status pesanan:

```txt
Pesanan: pending -> diproses -> selesai / dibatalkan
```

### Payment dan Upload Bukti Transfer

1. Setelah pesanan dibuat, pelanggan masuk ke halaman **Payment**.
2. Transfer sesuai nominal yang ditampilkan.
3. Upload bukti transfer dalam format JPG, PNG, WEBP, atau PDF maksimal 2 MB.
4. Tunggu admin memverifikasi payment.

Alur status payment:

```txt
Payment: pending -> verified / rejected
```

Jika payment rejected, pesanan dibatalkan dan stok menu dikembalikan.

### Cek Status Reservasi dan Riwayat

1. Buka halaman **Cek Status** untuk melihat status reservasi, pesanan, dan payment.
2. Buka halaman **Riwayat** untuk melihat daftar reservasi dan pesanan yang pernah dibuat.

## 3. Panduan Admin

### Login Admin

1. Klik **Masuk**.
2. Login menggunakan akun admin.
3. Sistem mengarahkan admin ke dashboard.

### Dashboard

Dashboard menampilkan:

- total pendapatan hari ini;
- jumlah reservasi hari ini;
- payment yang masih pending;
- menu yang stoknya hampir habis.

### Kelola Menu

Admin dapat:

- menambah menu baru;
- mengedit nama, kategori, harga, deskripsi, foto, status unggulan, stok, dan minimum stok;
- menghapus menu;
- mengupload foto menu ke folder `public/uploads/menu`.

### Kelola Meja

Admin dapat menambah dan mengedit meja.

Alur status meja operasional:

```txt
Meja: tersedia -> terisi -> tersedia
```

Meja berubah menjadi **terisi** saat ada pesanan dine-in, lalu kembali **tersedia** saat pesanan selesai atau dibatalkan.

### Kelola Reservasi

Admin dapat:

- melihat semua reservasi;
- filter berdasarkan tanggal dan status;
- melihat detail reservasi beserta pre-order dan payment;
- mengonfirmasi reservasi;
- membatalkan reservasi;
- menghapus reservasi.

Jika reservasi dibatalkan dan memiliki pre-order, pesanan terkait akan ikut dibatalkan dan stok dikembalikan.

### Kelola Pesanan

Admin dapat mengubah status pesanan:

```txt
pending -> diproses -> selesai
pending/diproses -> dibatalkan
```

Catatan:

- Pesanan hanya bisa masuk **diproses** setelah payment verified.
- Pesanan **selesai** membuat meja dine-in kembali tersedia.
- Pesanan **dibatalkan** membuat stok menu dikembalikan dan meja dine-in kembali tersedia.

### Verifikasi Payment

Admin dapat:

- melihat bukti transfer;
- memberi status **verified** jika bukti transfer valid;
- memberi status **rejected** jika bukti tidak valid;
- mengisi catatan admin.

Jika payment rejected:

```txt
Payment rejected -> pesanan dibatalkan -> stok dikembalikan -> meja kembali tersedia jika dine-in
```

### Laporan Penjualan

Admin dapat membuka **Laporan** untuk:

- filter tanggal;
- filter jenis pesanan;
- melihat total pendapatan;
- melihat ringkasan dine-in, take away, dan reservasi;
- melihat menu terlaris;
- mencetak laporan dari browser.
