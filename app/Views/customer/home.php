<section class="dc-hero">
  <div class="dc-hero-overlay"></div>
  <div class="container h-100 d-flex flex-column justify-content-center">
    <div class="dc-hero-content text-center">
      <span class="dc-open-badge"><i class="fa-solid fa-clock"></i> Buka Setiap Hari 09.00 - 22.00 WIB</span>
      <h1 class="dc-hero-title">Cita Rasa Nusantara<br><em>dalam Setiap Sajian</em></h1>
      <p class="dc-hero-desc">Nikmati pengalaman kuliner autentik Indonesia dengan suasana modern yang hangat. Reservasi meja, pesan menu favorit, dan bayar dengan mudah.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="<?= url('reservasi') ?>" class="btn dc-btn-hero-primary">Reservasi Sekarang <i class="fa-solid fa-arrow-right ms-1"></i></a>
        <a href="<?= url('menu') ?>" class="btn dc-btn-hero-secondary">Lihat Menu</a>
      </div>
    </div>
  </div>
  <div class="dc-hero-stats">
    <div class="container"><div class="row justify-content-center text-center g-4">
      <div class="col-6 col-md-3"><div class="dc-stat"><i class="fas fa-utensils dc-stat-icon"></i><div class="dc-stat-number">50+</div><div class="dc-stat-label">Menu Pilihan</div></div></div>
      <div class="col-6 col-md-3"><div class="dc-stat"><i class="fas fa-table dc-stat-icon"></i><div class="dc-stat-number">12</div><div class="dc-stat-label">Meja Tersedia</div></div></div>
      <div class="col-6 col-md-3"><div class="dc-stat"><i class="fas fa-star dc-stat-icon"></i><div class="dc-stat-number">4.9</div><div class="dc-stat-label">Rating</div></div></div>
      <div class="col-6 col-md-3"><div class="dc-stat"><i class="fas fa-users dc-stat-icon"></i><div class="dc-stat-number">2K+</div><div class="dc-stat-label">Pelanggan Puas</div></div></div>
    </div></div>
  </div>
</section>

<section class="dc-layanan py-5">
  <div class="container py-4">
    <div class="text-center mb-5"><span class="dc-section-badge">LAYANAN KAMI</span><h2 class="dc-section-title">Cara Menikmati Cafe Nusantara</h2><p class="dc-section-desc">Tiga cara mudah untuk menikmati hidangan terbaik kami sesuai kebutuhan Anda</p></div>
    <div class="row g-4">
      <div class="col-md-4"><div class="dc-layanan-card dc-card-yellow"><div class="dc-layanan-icon"><i class="fas fa-calendar-check"></i></div><h5>Reservasi Meja</h5><p>Pesan meja favorit Anda jauh hari sebelumnya. Pilih tanggal, jam, jumlah tamu, dan catatan kebutuhan.</p><a href="<?= url('reservasi') ?>">Reservasi Sekarang <i class="fa-solid fa-arrow-right"></i></a></div></div>
      <div class="col-md-4"><div class="dc-layanan-card dc-card-peach"><div class="dc-layanan-icon"><i class="fas fa-utensils"></i></div><h5>Dine-In</h5><p>Datang langsung dan nikmati suasana cafe kami. Pilih meja tersedia, pesan menu favorit, dan bayar mudah.</p><a href="<?= url('pesan') ?>">Pesan Dine-In <i class="fa-solid fa-arrow-right"></i></a></div></div>
      <div class="col-md-4"><div class="dc-layanan-card dc-card-pink"><div class="dc-layanan-icon"><i class="fas fa-bag-shopping"></i></div><h5>Take Away</h5><p>Pre-order menu sebelum datang agar pesanan siap tepat waktu untuk dibawa pulang.</p><a href="<?= url('pesan') ?>">Pesan Take Away <i class="fa-solid fa-arrow-right"></i></a></div></div>
    </div>
  </div>
</section>

<section class="dc-menu-populer py-5">
  <div class="container py-3">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
      <div><span class="dc-badge-populer"><i class="fas fa-star me-1"></i> Menu Favorit</span><h2 class="dc-section-title mt-2">Pilihan <span class="dc-text-gold">Terpopuler</span></h2><p class="dc-section-desc mb-0">Hidangan yang paling banyak dipesan pelanggan kami</p></div>
      <a href="<?= url('menu') ?>" class="dc-lihat-semua align-self-end">Lihat Semua Menu <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="row g-3">
      <?php foreach ($menus as $item): ?>
      <div class="col-6 col-md-4 col-lg-2"><div class="dc-menu-card compact"><img src="<?= e($item['foto']) ?>" alt="<?= e($item['nama']) ?>"><div class="dc-menu-info"><p class="dc-menu-name"><?= e($item['nama']) ?></p><p class="dc-menu-price"><?= rupiah($item['harga']) ?></p></div></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="dc-testimoni py-5">
  <div class="container py-4">
    <div class="text-center mb-5"><span class="dc-section-badge">TESTIMONI</span><h2 class="dc-section-title">Kata Pelanggan Kami</h2></div>
    <div id="testimoniCarousel" class="carousel slide" data-bs-ride="carousel"><div class="carousel-inner dc-testimoni-inner">
      <?php foreach ($testimonials as $i => $t): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>"><div class="dc-testimoni-box"><div class="dc-rating-badge"><i class="fas fa-star"></i> <?= e(number_format($t['rating'], 1)) ?></div><p class="dc-testimoni-text">&quot;<?= e($t['komentar']) ?>&quot;</p><div class="d-flex align-items-center gap-3 mt-4"><img src="<?= e($t['avatar']) ?>" class="dc-testimoni-avatar" alt="<?= e($t['nama']) ?>"><div><div class="dc-testimoni-name"><?= e($t['nama']) ?></div><div class="dc-testimoni-peran"><?= e($t['peran']) ?></div></div><div class="ms-auto d-flex gap-2"><button class="dc-carousel-btn" data-bs-target="#testimoniCarousel" data-bs-slide="prev"><i class="fas fa-arrow-left"></i></button><button class="dc-carousel-btn dc-carousel-btn-dark" data-bs-target="#testimoniCarousel" data-bs-slide="next"><i class="fas fa-arrow-right"></i></button></div></div></div></div>
      <?php endforeach; ?>
    </div></div>
  </div>
</section>
