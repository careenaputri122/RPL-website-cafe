<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">PAYMENT</span><h1>Pembayaran</h1><p>Lihat total harga, info rekening, dan upload bukti transfer.</p></div></section>
<section class="py-5"><div class="container">
  <?php if (!$order): ?>
    <div class="dc-panel"><h4>Pesanan tidak ditemukan.</h4><p class="text-muted">Pastikan Anda login dengan akun yang membuat pesanan.</p><a href="<?= url('riwayat') ?>" class="btn dc-btn-submit">Kembali</a></div>
  <?php else: $bank = app_config('bank'); ?>
  <div class="row g-4">
    <div class="col-lg-7"><div class="dc-panel"><h4>Ringkasan Pesanan <?= e($order['kode']) ?></h4><p>Status pesanan: <span class="dc-status <?= e($order['status']) ?>"><?= e($order['status']) ?></span></p><div class="table-responsive"><table class="table dc-table"><thead><tr><th>Menu</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($order['items'] as $it): ?><tr><td><?= e($it['nama']) ?></td><td><?= e($it['qty']) ?></td><td><?= rupiah($it['harga']) ?></td><td><?= rupiah($it['harga'] * $it['qty']) ?></td></tr><?php endforeach; ?></tbody></table></div><div class="dc-total-line"><span>Total</span><strong><?= rupiah($order['total']) ?></strong></div><div class="dc-total-line"><span>Yang dibayar sekarang</span><strong><?= rupiah($order['deposit']) ?></strong></div><p class="small text-muted mt-3 mb-0">Untuk pesanan reservasi, sistem memakai DP 50%. Dine-in dan take away dibayar penuh.</p></div></div>
    <div class="col-lg-5"><div class="dc-panel"><h4>Info Rekening Cafe</h4><div class="dc-bank-box"><span><?= e($bank['nama_bank']) ?></span><strong><?= e($bank['nomor_rekening']) ?></strong><p>a.n. <?= e($bank['atas_nama']) ?></p></div>
      <?php if ($order['status'] === 'dibatalkan'): ?>
        <div class="alert alert-warning">Pesanan sudah dibatalkan. Upload bukti transfer tidak tersedia untuk pesanan ini.</div>
      <?php else: ?>
        <form method="POST" action="<?= url('payment/upload') ?>" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= e($order['id']) ?>"><label class="dc-form-label">Upload Bukti Transfer</label><input type="file" name="bukti_tf" class="form-control dc-input mb-2" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf" required><small class="text-muted d-block mb-3">Format: JPG, PNG, WEBP, atau PDF. Maksimal 2 MB.</small><button class="btn dc-btn-submit w-100">Kirim Bukti Transfer</button></form>
      <?php endif; ?>
      <?php if ($payment): ?><hr><p>Status payment: <span class="dc-status <?= e($payment['status']) ?>"><?= e($payment['status']) ?></span></p><p class="small text-muted">Bukti: <?= e($payment['bukti_tf']) ?></p><?php if (!empty($payment['bukti_url'])): ?><a href="<?= e($payment['bukti_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark mb-2"><i class="fa-solid fa-eye"></i> Lihat Bukti</a><?php endif; ?><?php if ($payment['catatan_admin']): ?><p>Catatan admin: <?= e($payment['catatan_admin']) ?></p><?php endif; ?><?php endif; ?></div></div>
  </div>
  <?php endif; ?>
</div></section>
