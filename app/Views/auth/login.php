<section class="dc-auth-section">
  <div class="dc-auth-card">
    <a href="<?= url('home') ?>" class="dc-auth-logo"><span class="dc-logo-circle"><i class="fa-solid fa-mug-saucer"></i></span><strong><?= e(app_config('name')) ?></strong></a>
    <h1>Login</h1>
    <p>Masuk sebagai pelanggan atau administrator.</p>
    <form method="POST" action="<?= url('login') ?>"><?= csrf_field() ?>
      <label class="dc-form-label">Email</label><input class="form-control dc-input mb-3" type="email" name="email" required>
      <label class="dc-form-label">Password</label><input class="form-control dc-input mb-3" type="password" name="password" required>
      
      <button class="btn dc-btn-submit w-100" type="submit">Login</button>
    </form>
    <p class="mt-3 mb-0 text-center">Belum punya akun? <a href="<?= url('register') ?>">Daftar</a></p>
  </div>
</section>
