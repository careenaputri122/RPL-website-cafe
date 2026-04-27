<?php
$highlight = isset($_GET['highlight']) ? (int)$_GET['highlight'] : 0;
?>
<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">CEK STATUS</span><h1>Cek Status Reservasi</h1><p>Pantau reservasi, pre-order, status payment, dan alur pesanan Anda.</p></div></section>
<section class="py-5"><div class="container">
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="dc-panel h-100"><h6>Reservasi</h6><p class="mb-0"><span class="dc-status pending">pending</span> &rarr; <span class="dc-status confirmed">confirmed</span> / <span class="dc-status cancelled">cancelled</span></p></div></div>
    <div class="col-md-3"><div class="dc-panel h-100"><h6>Payment</h6><p class="mb-0"><span class="dc-status pending">pending</span> &rarr; <span class="dc-status verified">verified</span> / <span class="dc-status rejected">rejected</span></p></div></div>
    <div class="col-md-3"><div class="dc-panel h-100"><h6>Pesanan</h6><p class="mb-0"><span class="dc-status pending">pending</span> &rarr; <span class="dc-status diproses">diproses</span> &rarr; <span class="dc-status selesai">selesai</span> / <span class="dc-status dibatalkan">dibatalkan</span></p></div></div>
    <div class="col-md-3"><div class="dc-panel h-100"><h6>Meja &amp; Stok</h6><p class="mb-0">Meja: tersedia &rarr; terisi &rarr; tersedia.<br>Stok kembali jika pesanan batal/payment rejected.</p></div></div>
  </div>

  <div class="dc-panel mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"><h4 class="mb-0">Status Reservasi Saya</h4><a href="<?= url('reservasi') ?>" class="btn dc-btn-submit"><i class="fa-solid fa-calendar-plus"></i> Buat Reservasi</a></div>
    <?php if (!count($reservations)): ?>
      <div class="alert alert-light border mb-0">Belum ada reservasi. Silakan buat reservasi terlebih dahulu.</div>
    <?php endif; ?>
    <div class="row g-3">
      <?php foreach ($reservations as $r): ?>
        <?php $relatedOrders = array_values(array_filter($orders, function ($o) use ($r) { return (int)(isset($o['reservation_id']) ? $o['reservation_id'] : 0) === (int)$r['id']; })); ?>
        <div class="col-lg-6">
          <div class="dc-history-item h-100 <?= $highlight === (int)$r['id'] ? 'border border-warning' : '' ?>">
            <div>
              <strong><?= e($r['kode']) ?></strong>
              <p><?= e($r['tanggal']) ?> pukul <?= e($r['jam']) ?> - <?= e($r['jumlah_orang']) ?> orang - Meja <?= e($r['no_meja']) ?></p>
              <small><?= e($r['catatan'] ?: 'Tidak ada catatan') ?></small>
              <?php if ($r['status'] !== 'cancelled'): ?><br><a class="dc-small-link" href="<?= url('pesan?jenis=reservasi&reservasi_id=' . $r['id']) ?>">Tambah pre-order menu</a><?php endif; ?>
              <?php if (count($relatedOrders)): ?>
                <div class="mt-2">
                  <?php foreach ($relatedOrders as $o): $pay = find_payment_by_order($o['id']); ?>
                    <small class="d-block">Pesanan <?= e($o['kode']) ?>: <span class="dc-status <?= e($o['status']) ?>"><?= e($o['status']) ?></span> Payment: <span class="dc-status <?= e($pay ? $pay['status'] : 'pending') ?>"><?= e($pay ? $pay['status'] : 'pending') ?></span></small>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <span class="dc-status <?= e($r['status']) ?>"><?= e($r['status']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="dc-panel">
    <h4>Pesanan &amp; Payment</h4>
    <?php if (!count($orders)): ?><div class="alert alert-light border">Belum ada pesanan. <a href="<?= url('pesan') ?>">Pesan menu sekarang</a>.</div><?php endif; ?>
    <div class="dc-history-list">
      <?php foreach ($orders as $o): $payment = find_payment_by_order($o['id']); ?>
        <div class="dc-history-item">
          <div>
            <strong><?= e($o['kode']) ?></strong>
            <p><?= e(ucfirst($o['jenis'])) ?> - <?= rupiah($o['total']) ?> - Tagihan <?= rupiah($o['deposit']) ?></p>
            <small><?php foreach ($o['items'] as $it): ?><?= e($it['nama']) ?> x<?= e($it['qty']) ?>; <?php endforeach; ?></small>
          </div>
          <div class="text-end"><span class="dc-status <?= e($o['status']) ?>"><?= e($o['status']) ?></span><br><span class="dc-status <?= e($payment ? $payment['status'] : 'pending') ?>"><?= e($payment ? $payment['status'] : 'pending') ?></span><br><a class="dc-small-link" href="<?= url('payment?order_id=' . $o['id']) ?>">Detail Payment</a></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div></section>
