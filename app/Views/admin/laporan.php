<?php $admin_title = 'Laporan Penjualan'; $filterJenis = isset($report['filter_jenis']) ? $report['filter_jenis'] : ''; ?>
<div class="dc-admin-card mb-4 no-print">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h4 class="mb-1">Filter Laporan</h4><p class="text-muted mb-0">Laporan menghitung transaksi payment yang sudah verified.</p></div>
    <button onclick="window.print()" class="btn dc-btn-submit"><i class="fa-solid fa-print"></i> Cetak Laporan</button>
  </div>
  <form class="row g-3 mt-2" method="GET" action="<?= base_url('index.php') ?>">
    <input type="hidden" name="page" value="admin"><input type="hidden" name="action" value="laporan">
    <div class="col-md-3"><label class="dc-form-label">Dari Tanggal</label><input type="date" name="start" class="form-control dc-input" value="<?= e($report['start']) ?>"></div>
    <div class="col-md-3"><label class="dc-form-label">Sampai Tanggal</label><input type="date" name="end" class="form-control dc-input" value="<?= e($report['end']) ?>"></div>
    <div class="col-md-3"><label class="dc-form-label">Jenis Pesanan</label><select name="jenis" class="form-select dc-input"><option value="">Semua Jenis</option><?php foreach (['dine-in' => 'Dine-In', 'take-away' => 'Take Away', 'reservasi' => 'Reservasi'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $filterJenis === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3 d-flex align-items-end"><button class="btn dc-btn-submit w-100">Tampilkan</button></div>
  </form>
</div>

<div class="row g-3 mb-4 report-print-area">
  <div class="col-md-3"><div class="dc-admin-card h-100"><span class="dc-section-badge">TOTAL</span><h3><?= rupiah($report['total_pendapatan']) ?></h3><small><?= e($report['total_transaksi']) ?> transaksi verified</small></div></div>
  <?php foreach (['dine-in' => 'Dine-In', 'take-away' => 'Take Away', 'reservasi' => 'Reservasi'] as $key => $label): $sum = $report['summary_by_jenis'][$key]; ?>
    <div class="col-md-3"><div class="dc-admin-card h-100"><span class="dc-section-badge"><?= e($label) ?></span><h4><?= rupiah($sum['pendapatan']) ?></h4><small><?= e($sum['transaksi']) ?> transaksi</small></div></div>
  <?php endforeach; ?>
</div>

<div class="dc-admin-card report-print-area mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div><h4 class="mb-1">Laporan Penjualan Cafe Nusantara</h4><p class="text-muted mb-0">Periode <?= e($report['start']) ?> s.d. <?= e($report['end']) ?><?= $filterJenis ? ' - ' . e($filterJenis) : '' ?></p></div>
    <div class="text-end"><span class="dc-section-badge">PENDAPATAN</span><h3 class="mb-0"><?= rupiah($report['total_pendapatan']) ?></h3></div>
  </div>
  <div class="table-responsive"><table class="table dc-table align-middle"><thead><tr><th>Tanggal</th><th>Kode Payment</th><th>Kode Pesanan</th><th>Pelanggan</th><th>Jenis</th><th>Total Pesanan</th><th>Pendapatan</th><th>Status</th></tr></thead><tbody>
    <?php if (!count($report['rows'])): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi verified pada periode ini.</td></tr><?php endif; ?>
    <?php foreach ($report['rows'] as $row): ?><tr><td><?= e($row['tanggal']) ?></td><td><?= e($row['kode_payment']) ?></td><td><?= e($row['kode_pesanan']) ?></td><td><?= e($row['pelanggan']) ?></td><td><?= e($row['jenis']) ?></td><td><?= rupiah($row['total_harga']) ?></td><td><?= rupiah($row['deposit']) ?></td><td><span class="dc-status verified">verified</span></td></tr><?php endforeach; ?>
  </tbody><tfoot><tr><th colspan="6" class="text-end">Total Pendapatan</th><th><?= rupiah($report['total_pendapatan']) ?></th><th></th></tr></tfoot></table></div>
</div>

<div class="dc-admin-card report-print-area">
  <h4>Menu Terlaris</h4>
  <div class="table-responsive"><table class="table dc-table align-middle"><thead><tr><th>Menu</th><th>Jumlah Terjual</th><th>Total Penjualan</th></tr></thead><tbody>
    <?php if (!count($report['menu_terlaris'])): ?><tr><td colspan="3" class="text-center text-muted py-4">Belum ada data menu terjual.</td></tr><?php endif; ?>
    <?php foreach ($report['menu_terlaris'] as $m): ?><tr><td><?= e($m['nama']) ?></td><td><?= e((int)$m['qty']) ?></td><td><?= rupiah($m['total']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>
