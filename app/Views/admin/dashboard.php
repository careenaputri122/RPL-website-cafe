<?php $admin_title = 'Dashboard'; ?>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Total Pendapatan Hari Ini</span><strong><?= rupiah($stats['income_today']) ?></strong><i class="fa-solid fa-money-bill-trend-up"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Reservasi Hari Ini</span><strong><?= e($stats['reservations_today']) ?></strong><i class="fa-solid fa-calendar-check"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Payment Pending</span><strong><?= e($stats['pending_payments']) ?></strong><i class="fa-solid fa-credit-card"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Stok Hampir Habis</span><strong><?= e($stats['low_stock_count']) ?></strong><i class="fa-solid fa-triangle-exclamation"></i></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-7"><div class="dc-admin-card"><h4>Payment Pending</h4><div class="table-responsive"><table class="table dc-table align-middle"><thead><tr><th>Kode</th><th>Nama</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach ($payments as $p): if ($p['status'] === 'pending'): ?><tr><td><?= e($p['kode']) ?></td><td><?= e($p['nama']) ?></td><td><?= rupiah($p['total']) ?></td><td><span class="dc-status pending">pending</span></td></tr><?php endif; endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-5"><div class="dc-admin-card"><h4>Stok Hampir Habis</h4><?php foreach ($stats['low_stock'] as $m): ?><div class="dc-low-stock"><span><?= e($m['nama']) ?></span><strong><?= e($m['stok']) ?> stok</strong></div><?php endforeach; ?></div></div>
</div>
