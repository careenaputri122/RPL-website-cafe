<section class="dc-auth-section">
  <div class="dc-auth-card">
    <a href="<?= url('home') ?>" class="dc-auth-logo"><span class="dc-logo-circle"><i class="fa-solid fa-mug-saucer"></i></span><strong><?= e(app_config('name')) ?></strong></a>
    <h1>Daftar Akun</h1><p>Buat akun pelanggan untuk reservasi, pesan, payment, dan melihat riwayat.</p>
    <form method="POST" action="<?= url('register') ?>"><?= csrf_field() ?>
      <label class="dc-form-label">Nama Lengkap</label><input class="form-control dc-input mb-3" type="text" name="name" required>
      <label class="dc-form-label">Email</label><input class="form-control dc-input mb-3" type="email" name="email" required>
      <label class="dc-form-label">No. Telepon</label><input class="form-control dc-input mb-3" type="tel" name="phone" required>
      <label class="dc-form-label">Password</label><input class="form-control dc-input mb-3" type="password" name="password" required>
      <button class="btn dc-btn-submit w-100" type="submit">Daftar</button>
    </form>
    <p class="mt-3 mb-0 text-center">Sudah punya akun? <a href="<?= url('login') ?>">Login</a></p>
  </div>
</section>
