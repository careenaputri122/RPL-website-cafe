<!-- ============ FOOTER ============ -->
<footer class="dc-footer">
  <div class="container">
    <div class="row g-5 py-5">
      <!-- Brand -->
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="dc-logo-circle">☕</div>
          <span class="dc-brand-name text-white">Damian Cafe</span>
        </div>
        <p class="dc-footer-desc">Damian Cafe menghadirkan cita rasa autentik Indonesia dalam suasana modern yang nyaman. Reservasi mudah, pesanan cepat.</p>
        <div class="d-flex gap-3 mt-3">
          <a href="#" class="dc-sosmed"><i class="fab fa-instagram"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-x-twitter"></i></a>
          <a href="#" class="dc-sosmed"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>

      <!-- Navigasi -->
      <div class="col-lg-2 col-6">
        <h6 class="dc-footer-heading">NAVIGASI</h6>
        <ul class="dc-footer-links">
          <li><a href="<?= base_url('index.php?page=home') ?>">Home</a></li>
          <li><a href="<?= base_url('index.php?page=menu') ?>">Menu Kami</a></li>
          <li><a href="<?= base_url('index.php?page=reservasi') ?>">Reservasi</a></li>
          <li><a href="<?= base_url('index.php?page=pesan') ?>">Pesan Sekarang</a></li>
          <li><a href="<?= base_url('index.php?page=riwayat') ?>">Riwayat Pesanan</a></li>
        </ul>
      </div>

      <!-- Kontak -->
      <div class="col-lg-4">
        <h6 class="dc-footer-heading">KONTAK</h6>
        <ul class="dc-footer-kontak">
          <li><i class="fas fa-map-marker-alt"></i> Jl. Sudirman No. 88, Jakarta Pusat, DKI Jakarta 10220</li>
          <li><i class="fas fa-phone"></i> +62 21 1234 5678</li>
          <li><i class="fas fa-envelope"></i> hello@damiancafe.id</li>
          <li><i class="fas fa-clock"></i> Setiap hari: 09.00 – 22.00 WIB</li>
        </ul>
      </div>
    </div>

    <div class="dc-footer-bottom">
      <span>© <?= date('Y') ?> Damian Cafe. Semua hak dilindungi.</span>
      <div class="d-flex gap-3">
        <a href="#">Kebijakan Privasi</a>
        <a href="#">Syarat &amp; Ketentuan</a>
      </div>
    </div>
  </div>
</footer>

<!-- ============ MODAL LOGIN ============ -->
<div class="modal fade dc-modal" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content dc-modal-content">
      <button type="button" class="dc-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>

      <div class="dc-modal-logo">☕</div>
      <h4 class="dc-modal-title">Selamat Datang</h4>
      <p class="dc-modal-subtitle">Masuk ke akun Anda</p>

      <form method="POST" action="<?= base_url('index.php?page=auth&action=login') ?>">
        <div class="mb-3">
          <label class="dc-form-label">Email</label>
          <input type="email" name="email" class="form-control dc-input" placeholder="email@contoh.com" required>
        </div>
        <div class="mb-3">
          <label class="dc-form-label">Password</label>
          <input type="password" name="password" class="form-control dc-input" placeholder="Minimal 6 karakter" required>
        </div>
        <p class="dc-demo-hint">Demo: budi@email.com / password123 &nbsp;|&nbsp; Admin: admin@cafe.com / admin123</p>
        <button type="submit" class="btn dc-btn-submit w-100">Masuk</button>
      </form>

      <p class="dc-modal-switch">
        Belum punya akun?
        <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar sekarang</a>
      </p>
    </div>
  </div>
</div>

<!-- ============ MODAL REGISTER ============ -->
<div class="modal fade dc-modal" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content dc-modal-content">
      <button type="button" class="dc-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>

      <div class="dc-modal-logo">☕</div>
      <h4 class="dc-modal-title">Buat Akun Baru</h4>
      <p class="dc-modal-subtitle">Daftar untuk mulai memesan</p>

      <form method="POST" action="<?= base_url('index.php?page=auth&action=register') ?>">
        <div class="mb-3">
          <label class="dc-form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control dc-input" placeholder="Masukkan nama lengkap" required>
        </div>
        <div class="mb-3">
          <label class="dc-form-label">Email</label>
          <input type="email" name="email" class="form-control dc-input" placeholder="email@contoh.com" required>
        </div>
        <div class="mb-3">
          <label class="dc-form-label">No. Telepon</label>
          <input type="tel" name="telepon" class="form-control dc-input" placeholder="08xxxxxxxxxx" required>
        </div>
        <div class="mb-3">
          <label class="dc-form-label">Password</label>
          <input type="password" name="password" class="form-control dc-input" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="mb-3">
          <label class="dc-form-label">Konfirmasi Password</label>
          <input type="password" name="password_confirm" class="form-control dc-input" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn dc-btn-submit w-100">Daftar Sekarang</button>
      </form>

      <p class="dc-modal-switch">
        Sudah punya akun?
        <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Masuk di sini</a>
      </p>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
