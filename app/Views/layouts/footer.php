</main>
<?php if (empty($hide_cta)): ?>
<section class="dc-cta-section">
  <div class="container">
    <div class="dc-cta-card">
      <div>
        <span class="dc-section-badge text-start">SIAP MENIKMATI?</span>
        <h2>Siap Menikmati Pengalaman Kuliner Terbaik?</h2>
        <p>Reservasi meja sekarang dan dapatkan pengalaman makan malam yang tak terlupakan bersama orang-orang tersayang.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('reservasi') ?>" class="btn dc-btn-hero-primary">Reservasi Meja</a>
        <a href="<?= url('pesan') ?>" class="btn dc-btn-hero-secondary">Pesan Sekarang</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
<footer class="dc-footer">
  <div class="container">
    <div class="row g-5 py-5">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="dc-logo-circle"><i class="fa-solid fa-mug-saucer"></i></span>
          <span class="dc-brand-name text-white"><?= e(app_config('name')) ?></span>
        </div>
        <p class="dc-footer-desc"><?= e(app_config('name')) ?> menghadirkan cita rasa autentik Indonesia dalam suasana modern yang nyaman. Reservasi mudah, pesanan cepat, dan payment terpantau.</p>
        <div class="d-flex gap-3 mt-3">
          <a href="#" class="dc-sosmed"><i class="fab fa-instagram"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-x-twitter"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <h6 class="dc-footer-heading">NAVIGASI</h6>
        <ul class="dc-footer-links">
          <li><a href="<?= url('home') ?>">Home</a></li>
          <li><a href="<?= url('menu') ?>">Menu Kami</a></li>
          <li><a href="<?= url('reservasi') ?>">Reservasi</a></li>
          <li><a href="<?= url('pesan') ?>">Pesan Sekarang</a></li>
          <li><a href="<?= url('cek-status') ?>">Cek Status Reservasi</a></li>
          <li><a href="<?= url('riwayat') ?>">Riwayat Pesanan</a></li>
        </ul>
      </div>
      <div class="col-lg-5">
        <h6 class="dc-footer-heading">KONTAK</h6>
        <?php $kontak = app_config('kontak'); ?>
        <ul class="dc-footer-kontak">
          <li><i class="fas fa-map-marker-alt"></i> <?= e($kontak['alamat']) ?></li>
          <li><i class="fas fa-phone"></i> <?= e($kontak['telepon']) ?></li>
          <li><i class="fas fa-envelope"></i> <?= e($kontak['email']) ?></li>
          <li><i class="fas fa-clock"></i> <?= e($kontak['jam']) ?></li>
        </ul>
      </div>
    </div>
    <div class="dc-footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e(app_config('name')) ?>. Semua hak dilindungi.</span>
      <div class="d-flex gap-3"><a href="#">Kebijakan Privasi</a><a href="#">Syarat &amp; Ketentuan</a></div>
    </div>
  </div>
</footer>

<div class="modal fade dc-modal" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content dc-modal-content">
    <button type="button" class="dc-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
    <div class="dc-modal-logo"><i class="fa-solid fa-mug-saucer"></i></div>
    <h4 class="dc-modal-title">Selamat Datang</h4><p class="dc-modal-subtitle">Masuk ke akun Anda</p>
    <form method="POST" action="<?= url('login') ?>"><?= csrf_field() ?>
      <div class="mb-3"><label class="dc-form-label">Email</label><input type="email" name="email" class="form-control dc-input" placeholder="email@contoh.com" required></div>
      <div class="mb-3"><label class="dc-form-label">Password</label><input type="password" name="password" class="form-control dc-input" placeholder="Minimal 6 karakter" required></div>
      <p class="dc-demo-hint">Demo pelanggan: budi@email.com / password123<br>Demo admin: admin@cafe.com / admin123</p>
      <button type="submit" class="btn dc-btn-submit w-100">Masuk</button>
    </form>
    <p class="dc-modal-switch">Belum punya akun? <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar sekarang</a></p>
  </div></div>
</div>

<div class="modal fade dc-modal" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content dc-modal-content">
    <button type="button" class="dc-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
    <div class="dc-modal-logo"><i class="fa-solid fa-user-plus"></i></div>
    <h4 class="dc-modal-title">Buat Akun Baru</h4><p class="dc-modal-subtitle">Daftar untuk mulai reservasi dan memesan</p>
    <form method="POST" action="<?= url('register') ?>"><?= csrf_field() ?>
      <div class="mb-3"><label class="dc-form-label">Nama Lengkap</label><input type="text" name="name" class="form-control dc-input" required></div>
      <div class="mb-3"><label class="dc-form-label">Email</label><input type="email" name="email" class="form-control dc-input" required></div>
      <div class="mb-3"><label class="dc-form-label">No. Telepon</label><input type="tel" name="phone" class="form-control dc-input" required></div>
      <div class="mb-3"><label class="dc-form-label">Password</label><input type="password" name="password" class="form-control dc-input" required></div>
      <button type="submit" class="btn dc-btn-submit w-100">Daftar Sekarang</button>
    </form>
    <p class="dc-modal-switch">Sudah punya akun? <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Masuk di sini</a></p>
  </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
