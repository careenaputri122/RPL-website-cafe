<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME ?></title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg dc-navbar fixed-top" id="mainNavbar">
  <div class="container">
    <!-- Brand -->
    <a class="navbar-brand dc-brand" href="<?= base_url('index.php?page=home') ?>">
      <div class="dc-logo-circle">☕</div>
      <span class="dc-brand-name">Damian Cafe</span>
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link dc-nav-link <?= ($current_page === 'home') ? 'active' : '' ?>"
             href="<?= base_url('index.php?page=home') ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link dc-nav-link <?= ($current_page === 'menu') ? 'active' : '' ?>"
             href="<?= base_url('index.php?page=menu') ?>">Menu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link dc-nav-link <?= ($current_page === 'reservasi') ? 'active' : '' ?>"
             href="<?= base_url('index.php?page=reservasi') ?>">Reservasi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link dc-nav-link <?= ($current_page === 'pesan') ? 'active' : '' ?>"
             href="<?= base_url('index.php?page=pesan') ?>">Pesan</a>
        </li>
      </ul>

      <!-- Auth Buttons -->
      <div class="d-flex align-items-center gap-2">
        <button class="btn dc-btn-masuk" data-bs-toggle="modal" data-bs-target="#loginModal">Masuk</button>
        <button class="btn dc-btn-daftar" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar</button>
      </div>
    </div>
  </div>
</nav>
<!-- END NAVBAR -->
