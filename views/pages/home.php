<!-- ============ HERO ============ -->
<section class="dc-hero">
  <div class="dc-hero-overlay"></div>
  <div class="container h-100 d-flex flex-column justify-content-center">
    <div class="dc-hero-content text-center">
      <h1 class="dc-hero-title">
        Cita Rasa Nusantara<br>
        <em>dalam Setiap Sajian</em>
      </h1>
      <p class="dc-hero-desc">
        Nikmati pengalaman kuliner autentik Indonesia dengan suasana modern yang<br class="d-none d-md-block">
        hangat. Reservasi meja, pesan menu favorit, dan bayar dengan mudah.
      </p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="<?= base_url('index.php?page=reservasi') ?>" class="btn dc-btn-hero-primary">
          Reservasi Sekarang &rarr;
        </a>
        <a href="<?= base_url('index.php?page=menu') ?>" class="btn dc-btn-hero-secondary">
          Lihat Menu
        </a>
      </div>
    </div>
  </div>

  <!-- Stats Bar -->
  <div class="dc-hero-stats">
    <div class="container">
      <div class="row justify-content-center text-center g-4">
        <div class="col-6 col-md-3">
          <div class="dc-stat">
            <i class="fas fa-utensils dc-stat-icon"></i>
            <div class="dc-stat-number">50+</div>
            <div class="dc-stat-label">Menu Pilihan</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dc-stat">
            <i class="fas fa-table dc-stat-icon"></i>
            <div class="dc-stat-number">12</div>
            <div class="dc-stat-label">Meja Tersedia</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dc-stat">
            <i class="fas fa-star dc-stat-icon"></i>
            <div class="dc-stat-number">4.9</div>
            <div class="dc-stat-label">Rating</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dc-stat">
            <i class="fas fa-users dc-stat-icon"></i>
            <div class="dc-stat-number">2K+</div>
            <div class="dc-stat-label">Pelanggan Puas</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ LAYANAN ============ -->
<section class="dc-layanan py-5">
  <div class="container py-4">
    <div class="text-center mb-5">
      <span class="dc-section-badge">LAYANAN KAMI</span>
      <h2 class="dc-section-title">Cara Menikmati Damian Cafe</h2>
      <p class="dc-section-desc">Tiga cara mudah untuk menikmati hidangan terbaik kami sesuai kebutuhan Anda</p>
    </div>

    <div class="row g-4">
      <!-- Reservasi -->
      <div class="col-md-4">
        <div class="dc-layanan-card" style="--card-bg: #fffbea;">
          <div class="dc-layanan-icon" style="color: #f59e0b;">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h5 class="dc-layanan-title">Reservasi Meja</h5>
          <p class="dc-layanan-desc">Pesan meja favorit Anda jauh hari sebelumnya. Pilih tanggal, jam, dan jumlah tamu. Sistem kami akan otomatis mencarikan meja terbaik untuk Anda.</p>
          <a href="<?= base_url('index.php?page=reservasi') ?>" class="dc-layanan-link">Reservasi Sekarang &rarr;</a>
        </div>
      </div>
      <!-- Dine In -->
      <div class="col-md-4">
        <div class="dc-layanan-card" style="--card-bg: #fff5f0;">
          <div class="dc-layanan-icon" style="color: #ef4444;">
            <i class="fas fa-utensils"></i>
          </div>
          <h5 class="dc-layanan-title">Dine-In</h5>
          <p class="dc-layanan-desc">Datang langsung dan nikmati suasana cafe kami. Pilih meja yang tersedia, pesan menu favorit, dan bayar dengan mudah melalui sistem kami.</p>
          <a href="<?= base_url('index.php?page=pesan') ?>" class="dc-layanan-link">Pesan Dine-In &rarr;</a>
        </div>
      </div>
      <!-- Take Away -->
      <div class="col-md-4">
        <div class="dc-layanan-card" style="--card-bg: #fdf4f4;">
          <div class="dc-layanan-icon" style="color: #e11d48;">
            <i class="fas fa-bag-shopping"></i>
          </div>
          <h5 class="dc-layanan-title">Take Away</h5>
          <p class="dc-layanan-desc">Tidak sempat duduk? Pesan menu favorit Anda untuk dibawa pulang. Pre-order sebelum datang agar pesanan siap tepat waktu.</p>
          <a href="<?= base_url('index.php?page=pesan') ?>" class="dc-layanan-link">Pesan Take Away &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ MENU POPULER ============ -->
<section class="dc-menu-populer py-5">
  <div class="container py-3">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
      <div>
        <span class="dc-badge-populer"><i class="fas fa-star me-1"></i> Menu Favorit</span>
        <h2 class="dc-section-title mt-2">Pilihan <span class="dc-text-gold">Terpopuler</span></h2>
        <p class="dc-section-desc mb-0">Hidangan yang paling banyak dipesan pelanggan kami</p>
      </div>
      <a href="<?= base_url('index.php?page=menu') ?>" class="dc-lihat-semua align-self-end">Lihat Semua Menu &rarr;</a>
    </div>

    <div class="row g-3" id="menuRow">
      <?php if (!empty($menu_populer)): ?>
        <?php foreach ($menu_populer as $item): ?>
        <div class="col-6 col-md-4 col-lg-2">
          <div class="dc-menu-card">
            <div class="dc-menu-img-wrap">
              <img src="<?= htmlspecialchars($item['gambar']) ?>"
                   alt="<?= htmlspecialchars($item['nama']) ?>"
                   class="dc-menu-img">
            </div>
            <div class="dc-menu-info">
              <p class="dc-menu-name"><?= htmlspecialchars($item['nama']) ?></p>
              <p class="dc-menu-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-muted">Menu tidak tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============ TESTIMONI ============ -->
<section class="dc-testimoni py-5">
  <div class="container py-4">
    <div class="text-center mb-5">
      <span class="dc-section-badge">TESTIMONI</span>
      <h2 class="dc-section-title">Kata Pelanggan Kami</h2>
    </div>

    <div id="testimoniCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner dc-testimoni-inner">
        <?php foreach ($testimoni as $i => $t): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <div class="dc-testimoni-box">
            <div class="dc-rating-badge">
              <i class="fas fa-star"></i> <?= number_format($t['rating'], 1) ?>
            </div>
            <p class="dc-testimoni-text">"<?= htmlspecialchars($t['komentar']) ?>"</p>
            <div class="d-flex align-items-center gap-3 mt-4">
              <img src="<?= htmlspecialchars($t['avatar']) ?>" class="dc-testimoni-avatar" alt="<?= htmlspecialchars($t['nama']) ?>">
              <div>
                <div class="dc-testimoni-name"><?= htmlspecialchars($t['nama']) ?></div>
                <div class="dc-testimoni-peran"><?= htmlspecialchars($t['peran']) ?></div>
              </div>
              <div class="ms-auto d-flex gap-2">
                <button class="dc-carousel-btn" data-bs-target="#testimoniCarousel" data-bs-slide="prev">
                  <i class="fas fa-arrow-left"></i>
                </button>
                <button class="dc-carousel-btn dc-carousel-btn-dark" data-bs-target="#testimoniCarousel" data-bs-slide="next">
                  <i class="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
            <!-- Dots -->
            <div class="dc-dots mt-3">
              <?php foreach ($testimoni as $j => $d): ?>
              <span class="dc-dot <?= $j === $i ? 'active' : '' ?>"></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
