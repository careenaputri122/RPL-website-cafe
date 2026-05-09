<?php
$allowedJenis = ['dine-in', 'take-away', 'reservasi'];
$jenisAktif = isset($_GET['jenis']) && in_array($_GET['jenis'], $allowedJenis, true) ? $_GET['jenis'] : 'dine-in';
$selectedReservation = isset($_GET['reservasi_id']) ? (int)$_GET['reservasi_id'] : 0;
?>
<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">ORDER</span><h1>Pesan Menu</h1><p>Pilih layanan dine-in, take away, atau pre-order saat reservasi.</p></div></section>
<section class="py-5"><div class="container">
  <form method="POST" action="<?= url('pesan/store') ?>" id="orderForm"><?= csrf_field() ?>
    <?php $jenisAktif = old('jenis', $jenisAktif); ?>
    <input type="hidden" name="jenis" id="orderJenis" value="<?= e($jenisAktif) ?>">
    <input type="hidden" name="no_meja" id="orderTable" value="<?= e(old('no_meja', '')) ?>">
    <input type="hidden" name="cart_data" id="cartData" value="<?= e(old('cart_data', '[]')) ?>">
    <div class="dc-order-tabs mb-4">
      <button type="button" class="<?= $jenisAktif === 'dine-in' ? 'active' : '' ?>" data-service="dine-in"><i class="fa-solid fa-utensils"></i> Dine-In</button>
      <button type="button" class="<?= $jenisAktif === 'take-away' ? 'active' : '' ?>" data-service="take-away"><i class="fa-solid fa-bag-shopping"></i> Take Away</button>
      <button type="button" class="<?= $jenisAktif === 'reservasi' ? 'active' : '' ?>" data-service="reservasi"><i class="fa-solid fa-calendar-check"></i> Reservasi</button>
    </div>

    <div class="dc-panel mb-4" id="tablePanel" style="<?= $jenisAktif === 'dine-in' ? '' : 'display:none;' ?>"><h5>Pilih Meja</h5><div class="dc-table-grid">
      <?php $oldTable = old('no_meja', ''); foreach ($tables as $t): ?>
        <button type="button" class="dc-table-seat <?= e($t['status']) ?> <?= $t['no_meja'] === $oldTable ? 'selected' : '' ?>" data-table="<?= e($t['no_meja']) ?>" <?= $t['status'] !== 'tersedia' && $t['no_meja'] !== $oldTable ? 'disabled' : '' ?>><strong><?= e($t['no_meja']) ?></strong><span><?= e($t['kapasitas']) ?> org</span><small><?= e(ucfirst($t['status'])) ?></small></button>
      <?php endforeach; ?>
    </div><div class="dc-legend"><span><i class="ok"></i> Tersedia</span><span><i class="bad"></i> Terisi</span></div></div>

    <div class="dc-panel mb-4" id="reservationPanel" style="<?= $jenisAktif === 'reservasi' ? '' : 'display:none;' ?>">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div><h5 class="mb-1">Pilih Reservasi untuk Pre-Order</h5><p class="text-muted mb-0">Menu yang dipilih akan ditautkan ke reservasi pelanggan.</p></div>
        <a href="<?= url('reservasi') ?>" class="btn dc-btn-ghost"><i class="fa-solid fa-calendar-plus"></i> Buat Reservasi</a>
      </div>
      <?php 
        $validReservations = array_values(array_filter($reservations, function ($r) { return isset($r['status']) && $r['status'] !== 'cancelled'; })); 
        $selectedReservation = old('reservation_id', $selectedReservation);
      ?>
      <?php if (count($validReservations)): ?>
        <select name="reservation_id" class="form-select dc-input mt-3">
          <option value="">Pilih reservasi...</option>
          <?php foreach ($validReservations as $r): ?>
            <option value="<?= e($r['id']) ?>" <?= (int)$r['id'] === (int)$selectedReservation ? 'selected' : '' ?>><?= e($r['kode']) ?> - <?= e($r['tanggal']) ?> <?= e($r['jam']) ?> - Meja <?= e($r['no_meja']) ?> - <?= e($r['status']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <div class="alert alert-warning mt-3 mb-0">Belum ada reservasi aktif. Buat reservasi dulu, lalu kembali ke halaman Pesan.</div>
      <?php endif; ?>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="dc-toolbar mb-3"><div class="dc-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="orderSearch" placeholder="Cari menu..."></div><div class="dc-filter-group" id="orderFilter"><button type="button" class="active" data-category="Semua">Semua</button><button type="button" data-category="Makanan">Makanan</button><button type="button" data-category="Minuman">Minuman</button><button type="button" data-category="Dessert">Dessert</button></div></div>
        <div class="row g-3" id="orderMenuList">
          <?php foreach ($menus as $m): $available = (int)$m['stok'] > 0; ?>
          <div class="col-md-6 order-item" data-name="<?= e(strtolower($m['nama'])) ?>" data-category="<?= e($m['kategori']) ?>"><div class="dc-order-menu-card <?= !$available ? 'is-empty' : '' ?>"><img src="<?= e($m['foto']) ?>" alt="<?= e($m['nama']) ?>"><div><h6><?= e($m['nama']) ?></h6><p><?= e($m['deskripsi']) ?></p><strong><?= rupiah($m['harga']) ?></strong><small class="d-block text-muted">Stok: <?= e($m['stok']) ?></small></div><button type="button" class="dc-add-mini cart-add" data-id="<?= e($m['id']) ?>" data-name="<?= e($m['nama']) ?>" data-price="<?= e($m['harga']) ?>" data-stock="<?= e($m['stok']) ?>" <?= !$available ? 'disabled' : '' ?>><i class="fa-solid fa-plus"></i></button></div></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-4"><div class="dc-cart-card sticky-lg-top"><h5><i class="fa-solid fa-cart-shopping me-2"></i>Keranjang</h5><div id="cartItems" class="dc-cart-empty"><i class="fa-solid fa-cart-shopping"></i><p>Keranjang masih kosong<br><small>Tambahkan menu dari daftar</small></p></div><div class="dc-cart-footer"><div class="d-flex justify-content-between"><span>Total</span><strong id="cartTotal">Rp 0</strong></div><button class="btn dc-btn-submit w-100 mt-3" type="submit" id="checkoutBtn" disabled>Lanjut Payment</button><small class="text-muted d-block mt-2" id="checkoutHint">Pilih layanan dan minimal satu menu terlebih dahulu.</small></div></div></div>
    </div>
  </form>
</div></section>
