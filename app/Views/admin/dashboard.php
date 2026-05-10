<?php
$admin_title = 'Dashboard';
$pendingPayments = array_values(array_filter($payments, function ($p) {
    return isset($p['status']) && $p['status'] === 'pending';
}));
$visiblePendingPayments = array_slice($pendingPayments, 0, 5);
?>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Total Pendapatan Hari Ini</span><strong><?= rupiah($stats['income_today']) ?></strong><i class="fa-solid fa-money-bill-trend-up"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Reservasi Hari Ini</span><strong><?= e($stats['reservations_today']) ?></strong><i class="fa-solid fa-calendar-check"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Payment Pending</span><strong><?= e($stats['pending_payments']) ?></strong><i class="fa-solid fa-credit-card"></i></div></div>
  <div class="col-sm-6 col-xl-3"><div class="dc-admin-stat"><span>Stok Hampir Habis</span><strong><?= e($stats['low_stock_count']) ?></strong><i class="fa-solid fa-triangle-exclamation"></i></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-7"><div class="dc-admin-card"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><h4 class="mb-0">Payment Pending</h4><?php if (count($pendingPayments) > 5): ?><a href="<?= url('admin/payment') ?>" class="btn btn-sm btn-outline-dark">Lihat Semua</a><?php endif; ?></div><div class="table-responsive mt-3"><table class="table dc-table align-middle"><thead><tr><th>Kode</th><th>Nama</th><th>Total</th><th>Status</th></tr></thead><tbody><?php if (!count($visiblePendingPayments)): ?><tr><td colspan="4" class="text-center text-muted py-4">Tidak ada payment pending.</td></tr><?php endif; ?><?php foreach ($visiblePendingPayments as $p): ?><tr><td><?= e($p['kode']) ?></td><td><?= e($p['nama']) ?></td><td><?= rupiah($p['total']) ?></td><td><span class="dc-status pending">pending</span></td></tr><?php endforeach; ?></tbody></table></div><?php if (count($pendingPayments) > 5): ?><div class="text-end"><small class="text-muted"><?= e(count($pendingPayments) - 5) ?> payment pending lainnya ada di halaman Payment.</small></div><?php endif; ?></div></div>
  <div class="col-lg-5"><div class="dc-admin-card"><h4>Stok Hampir Habis</h4><?php foreach ($stats['low_stock'] as $m): ?><div class="dc-low-stock"><span><?= e($m['nama']) ?></span><strong><?= e($m['stok']) ?> stok</strong></div><?php endforeach; ?></div></div>
</div>
