<!DOCTYPE html>
<html lang="id"><head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - <?= e(app_config('name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head><body class="dc-admin-body">
<aside class="dc-admin-sidebar">
  <div class="dc-admin-brand">
    <span class="dc-logo-circle"><i class="fa-solid fa-mug-saucer"></i></span>
    <div class="dc-brand-name">
      <div style="font-size: 0.7rem; letter-spacing: 0.1em; color: var(--gold); text-transform: uppercase; line-height: 1;">Admin Panel</div>
      <div style="font-size: 1.1rem;"><?= e(app_config('name')) ?></div>
    </div>
  </div>
  <?php $items = [
    'admin/dashboard' => ['Dashboard','fa-chart-line'], 'admin/menu' => ['Kelola Menu','fa-bowl-food'], 'admin/meja' => ['Kelola Meja','fa-chair'], 'admin/reservasi' => ['Reservasi','fa-calendar-check'], 'admin/pesanan' => ['Pesanan','fa-receipt'], 'admin/payment' => ['Payment','fa-credit-card'], 'admin/laporan' => ['Laporan','fa-file-invoice-dollar'], 'admin/profile' => ['Profil Admin','fa-user-gear']
  ]; ?>
  <nav><?php foreach ($items as $routeKey => $item): ?><a class="<?= $current_admin_page === $routeKey ? 'active' : '' ?>" href="<?= url($routeKey) ?>"><i class="fa-solid <?= e($item[1]) ?>"></i><?= e($item[0]) ?></a><?php endforeach; ?></nav>
  <form method="POST" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="dc-admin-logout" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button></form>
</aside>
<div class="dc-admin-content">
  <?php $adminTitles = ['admin/dashboard' => 'Dashboard', 'admin/menu' => 'Kelola Menu', 'admin/meja' => 'Kelola Meja', 'admin/reservasi' => 'Kelola Reservasi', 'admin/pesanan' => 'Daftar Pesanan', 'admin/payment' => 'Verifikasi Payment', 'admin/laporan' => 'Laporan Penjualan', 'admin/profile' => 'Profil Admin']; $topTitle = isset($admin_title) ? $admin_title : (isset($adminTitles[$current_admin_page]) ? $adminTitles[$current_admin_page] : 'Dashboard'); ?>
  <header class="dc-admin-topbar"><div><span>Panel Admin</span><h1><?= e($topTitle) ?></h1></div></header>
