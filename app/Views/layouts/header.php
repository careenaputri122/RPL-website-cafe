<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? e($page_title) . ' - ' . e(app_config('name')) : e(app_config('name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../../public/css/style.css') ?>" rel="stylesheet">
</head>
<body class="dc-body">
<nav class="navbar navbar-expand-lg dc-navbar fixed-top" id="mainNavbar">
  <div class="container">
    <a class="navbar-brand dc-brand" href="<?= url('home') ?>">
      <span class="dc-logo-circle"><i class="fa-solid fa-mug-saucer"></i></span>
      <span class="dc-brand-name d-none d-sm-inline"><?= e(app_config('name')) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-lg-2">
        <?php $navs = ['home' => 'Home', 'menu' => 'Menu', 'reservasi' => 'Reservasi', 'pesan' => 'Pesan']; ?>
        <?php $login_required = ['reservasi', 'pesan']; ?>
<?php foreach ($navs as $key => $label): ?>
  <li class="nav-item">
    <?php if (!is_logged_in() && in_array($key, $login_required)): ?>
      <a class="nav-link dc-nav-link <?= $current_page === $key ? 'active' : '' ?>"
         href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
        <?= e($label) ?>
      </a>
    <?php else: ?>
      <a class="nav-link dc-nav-link <?= $current_page === $key ? 'active' : '' ?>"
         href="<?= url($key) ?>">
        <?= e($label) ?>
      </a>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
      </ul>
      <div class="d-flex align-items-center gap-2 dc-nav-actions">
        <?php if (is_logged_in()): ?>
        <a class="dc-riwayat-link <?= $current_page === 'riwayat' ? 'active' : '' ?>" href="<?= url('riwayat') ?>">Riwayat</a>
        <?php endif; ?>
        <?php if (is_logged_in()): $user = current_user(); ?>
          <div class="dropdown">
            <button class="btn dc-user-pill dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="dc-user-avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
              <span class="d-none d-md-inline"><?= e(explode(' ', $user['name'])[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dc-dropdown">
              <?php if (is_admin()): ?><li><a class="dropdown-item" href="<?= url('admin/dashboard') ?>">Dashboard Admin</a></li><?php endif; ?>
              <li><a class="dropdown-item" href="<?= url('profile') ?>">Edit Profil</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="dropdown-item text-danger" type="submit">Logout</button></form>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <button class="btn dc-btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">Masuk</button>
          <button class="btn dc-btn-register d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="dc-main">
<?php require __DIR__ . '/../partials/flash.php'; ?>
<?php if (is_logged_in() && !is_admin()): ?>
  <?php 
    $unpaid = 0;
    foreach (get_payments(false) as $p) {
        if ($p['status'] === 'pending' && !payment_has_receipt($p)) {
            $unpaid++;
        }
    }
    if ($unpaid > 0): 
  ?>
  <div class="container mt-4">
    <div class="alert alert-warning border-warning shadow-sm d-flex align-items-center justify-content-between">
      <div>
        <i class="fa-solid fa-triangle-exclamation me-2"></i> Anda memiliki <strong><?= $unpaid ?> tagihan</strong> yang belum dibayar.
      </div>
      <a href="<?= url('payment/bulk') ?>" class="btn btn-sm btn-warning fw-bold">Bayar Semua Sekaligus</a>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php if (!empty($_SESSION['_open_login_modal'])): ?>
  <?php unset($_SESSION['_open_login_modal']); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      new bootstrap.Modal(document.getElementById('loginModal')).show();
    });
  </script>
<?php endif; ?>
