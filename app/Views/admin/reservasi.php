<?php
$admin_title = 'Kelola Reservasi';
$filterDate = isset($filters['tanggal']) ? $filters['tanggal'] : '';
$filterStatus = isset($filters['status']) ? $filters['status'] : '';
?>
<div class="dc-admin-card">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div><h4 class="mb-1">Daftar Reservasi</h4><p class="text-muted mb-0">Alur status: pending &rarr; confirmed / cancelled.</p></div>
    <form class="d-flex gap-2" method="GET" action="<?= base_url('index.php') ?>">
      <input type="hidden" name="page" value="admin"><input type="hidden" name="action" value="reservasi">
      <input type="date" name="tanggal" class="form-control dc-input" value="<?= e($filterDate) ?>">
      <select name="status" class="form-select dc-input"><option value="">Semua Status</option><?php foreach (['pending','confirmed','cancelled','expired'] as $s): ?><option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
      <button class="btn dc-btn-submit">Filter</button>
      <?php if ($filterDate || $filterStatus): ?><a class="btn btn-outline-secondary" href="<?= url('admin/reservasi') ?>">Reset</a><?php endif; ?>
    </form>
  </div>
  <?php if ($filterDate || $filterStatus): ?>
    <div class="alert alert-light border">
      Menampilkan reservasi<?= $filterDate ? ' tanggal ' . e($filterDate) : '' ?><?= $filterStatus ? ' dengan status ' . e($filterStatus) : '' ?>.
    </div>
  <?php endif; ?>
  <div class="table-responsive"><table class="table dc-table align-middle"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Tanggal</th><th>Jam</th><th>Orang</th><th>Meja</th><th>Status</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>
    <?php if (!count($reservations)): ?>
      <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada reservasi untuk filter ini.</td></tr>
    <?php endif; ?>
    <?php foreach ($reservations as $r): ?>
      <tr>
        <td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?><br><small><?= e($r['email']) ?></small></td><td><?= e($r['tanggal']) ?></td><td><?= e($r['jam']) ?></td><td><?= e($r['jumlah_orang']) ?></td><td><?= e($r['no_meja']) ?></td><td><span class="dc-status <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td><td><?= e($r['catatan']) ?></td>
        <td><div class="d-flex flex-wrap gap-1">
          <a href="<?= url('admin/reservasi/detail?id=' . $r['id']) ?>" class="btn btn-sm btn-outline-dark">Detail</a>
         
          <?php if ($r['status'] !== 'cancelled'): ?><form method="POST" action="<?= url('admin/reservasi/status') ?>" onsubmit="return confirm('Batalkan reservasi ini? Pesanan pre-order terkait akan ikut dibatalkan dan stok dikembalikan.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button name="status" value="cancelled" class="btn btn-sm btn-outline-warning">Batalkan</button></form><?php endif; ?>
          <form method="POST" action="<?= url('admin/reservasi/delete') ?>" onsubmit="return confirm('Hapus reservasi ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
        </div></td>
      </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div>
