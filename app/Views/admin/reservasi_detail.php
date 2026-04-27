<?php $admin_title = 'Detail Reservasi'; ?>
<?php if (!$detail): ?>
  <div class="dc-admin-card"><div class="alert alert-warning mb-0">Reservasi tidak ditemukan.</div><a href="<?= url('admin/reservasi') ?>" class="btn dc-btn-submit mt-3">Kembali</a></div>
<?php else: $r = $detail['reservation']; ?>
  <div class="dc-admin-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div><a href="<?= url('admin/reservasi') ?>" class="dc-small-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke daftar reservasi</a><h4 class="mt-2 mb-1"><?= e($r['kode']) ?></h4><p class="text-muted mb-0">Detail reservasi, pesanan customer, dan status payment.</p></div>
      <span class="dc-status <?= e($r['status']) ?>"><?= e($r['status']) ?></span>
    </div>
    <hr>
    <div class="row g-3">
      <div class="col-md-4"><small class="text-muted">Pelanggan</small><h6><?= e($r['nama']) ?></h6><p><?= e($r['email']) ?></p></div>
      <div class="col-md-4"><small class="text-muted">Jadwal</small><h6><?= e($r['tanggal']) ?> pukul <?= e($r['jam']) ?></h6><p><?= e($r['jumlah_orang']) ?> orang</p></div>
      <div class="col-md-4"><small class="text-muted">Meja</small><h6><?= e($r['no_meja']) ?></h6><p>Biaya booking: <?= rupiah(isset($r['biaya_booking']) ? $r['biaya_booking'] : 15000) ?></p></div>
      <div class="col-12"><small class="text-muted">Catatan</small><p class="mb-0"><?= e($r['catatan'] ?: 'Tidak ada catatan.') ?></p></div>
    </div>
  </div>

  <div class="dc-admin-card">
    <h4>Pesanan Pre-order Terkait</h4>
    <?php if (!count($detail['orders'])): ?><div class="alert alert-light border mb-0">Belum ada pre-order yang terhubung dengan reservasi ini.</div><?php endif; ?>
    <?php foreach ($detail['orders'] as $o): $p = isset($o['payment']) ? $o['payment'] : null; ?>
      <div class="border rounded-4 p-3 mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2"><div><strong><?= e($o['kode']) ?></strong><p class="mb-0 text-muted"><?= e($o['jenis']) ?> - <?= rupiah($o['total']) ?> - Tagihan <?= rupiah($o['deposit']) ?></p></div><div class="text-end"><span class="dc-status <?= e($o['status']) ?>"><?= e($o['status']) ?></span><?php if ($p): ?><br><span class="dc-status <?= e($p['status']) ?>"><?= e($p['status']) ?></span><?php endif; ?></div></div>
        <div class="mb-2"><?php foreach ($o['items'] as $it): ?><span class="dc-chip"><?= e($it['nama']) ?> x<?= e($it['qty']) ?></span><?php endforeach; ?></div>
        <?php if ($p): ?>
          <small class="text-muted">Bukti transfer: <?= e($p['bukti_tf']) ?></small>
          <?php if (!empty($p['bukti_url'])): ?><br><a href="<?= e($p['bukti_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark mt-2"><i class="fa-solid fa-eye"></i> Lihat Bukti</a><?php endif; ?>
          <?php if ($p['catatan_admin']): ?><p class="mb-0 mt-2">Catatan admin: <?= e($p['catatan_admin']) ?></p><?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
