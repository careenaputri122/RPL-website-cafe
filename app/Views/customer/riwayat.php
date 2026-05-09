<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">RIWAYAT</span><h1>Riwayat Reservasi & Pesanan</h1><p>Pantau status reservasi, pesanan, dan payment Anda.</p></div></section>
<section class="py-5"><div class="container">
  <div class="row g-4">
    <div class="col-lg-6"><div class="dc-panel"><h4>Reservasi</h4><div class="dc-history-list">
      <?php if (!count($reservations)): ?><div class="alert alert-light border">Belum ada reservasi. <a href="<?= url('reservasi') ?>">Buat reservasi sekarang</a>.</div><?php endif; ?>
      <?php foreach ($reservations as $r): ?>
      <div class="dc-history-item"><div><strong><?= e($r['kode']) ?></strong><p><?= e($r['tanggal']) ?> pukul <?= e($r['jam']) ?> - <?= e($r['jumlah_orang']) ?> orang - Meja <?= e($r['no_meja']) ?></p><small><?= e($r['catatan']) ?></small><br><a class="btn dc-btn-submit btn-sm mt-2" href="<?= url('pesan?jenis=reservasi&reservasi_id=' . $r['id']) ?>"><i class="fa-solid fa-utensils"></i> Pre-order Menu</a></div><span class="dc-status <?= e($r['status']) ?>"><?= e($r['status'] === 'confirmed' ? 'Dikonfirmasi (Siap)' : ucfirst($r['status'])) ?></span></div>
      <?php endforeach; ?>
    </div></div></div>
    <div class="col-lg-6"><div class="dc-panel"><h4>Pesanan & Payment</h4><div class="dc-history-list">
      <?php if (!count($orders)): ?><div class="alert alert-light border">Belum ada pesanan. <a href="<?= url('pesan') ?>">Pesan menu sekarang</a>.</div><?php endif; ?>
      <?php foreach ($orders as $o): $payment = find_payment_by_order($o['id']); ?>
      <div class="dc-history-item"><div><strong><?= e($o['kode']) ?></strong><p><?= e(ucfirst($o['jenis'])) ?> - <?= rupiah($o['total']) ?> - DP/Payment <?= rupiah($o['deposit']) ?></p><small><?php foreach ($o['items'] as $it): ?><?= e($it['nama']) ?> x<?= e($it['qty']) ?>; <?php endforeach; ?></small><?php if (!empty($o['reservation_id'])): ?><br><small class="text-muted">Terhubung reservasi #<?= e($o['reservation_id']) ?></small><?php endif; ?></div><div class="text-end"><span class="dc-status <?= e($o['status']) ?>"><?= e($o['status'] === 'diproses' ? 'Sedang Dimasak' : ($o['status'] === 'selesai' ? 'Siap Disajikan' : ucfirst($o['status']))) ?></span><br><a class="btn dc-btn-submit btn-sm mt-2" href="<?= url('payment?order_id=' . $o['id']) ?>"><i class="fa-solid fa-receipt"></i> Detail Payment</a></div></div>
      <?php endforeach; ?>
    </div></div></div>
  </div>
</div></section>

