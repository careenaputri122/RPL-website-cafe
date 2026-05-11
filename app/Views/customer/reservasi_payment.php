<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">BOOKING PAYMENT</span><h1>Bayar Biaya Booking</h1><p>Selesaikan pembayaran booking fee untuk mengkonfirmasi reservasi Anda.</p></div></section>
<section class="py-5"><div class="container">
  <?php if (!$reservation): ?>
    <div class="dc-panel"><h4>Reservasi tidak ditemukan.</h4><p class="text-muted">Pastikan Anda login dengan akun yang membuat reservasi.</p><a href="<?= url('riwayat') ?>" class="btn dc-btn-submit">Kembali ke Riwayat</a></div>
  <?php else: $bank = app_config('bank'); ?>
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="dc-panel">
        <h4>Ringkasan Reservasi <?= e($reservation['kode']) ?></h4>
        <ul class="list-unstyled mt-3 mb-0" style="display:flex;flex-direction:column;gap:.6rem;">
          <li><i class="fa-regular fa-calendar me-2 text-muted"></i> <?= e($reservation['tanggal']) ?></li>
          <li><i class="fa-regular fa-clock me-2 text-muted"></i> <?= e($reservation['jam']) ?></li>
          <li><i class="fa-solid fa-user-group me-2 text-muted"></i> <?= e($reservation['jumlah_orang']) ?> orang</li>
          <li><i class="fa-solid fa-chair me-2 text-muted"></i> Meja <?= e($reservation['no_meja']) ?></li>
          <?php if ($reservation['catatan']): ?>
          <li><i class="fa-solid fa-note-sticky me-2 text-muted"></i> <?= e($reservation['catatan']) ?></li>
          <?php endif; ?>
        </ul>

        <?php
          // Hitung rincian pembayaran
          $biayaBooking  = (float)($reservation['biaya_booking'] ?? 15000);
          // Jika booking_payment sudah ada, ambil total dari DB (booking fee + DP pre-order)
          $totalBayar    = $booking_payment ? (float)$booking_payment['total'] : $biayaBooking;
          $dpPreorder    = max(0, $totalBayar - $biayaBooking);
          $adaPreorder   = $dpPreorder > 0;
        ?>

        <hr class="my-3">
        <div class="dc-total-line">
          <span>Biaya Booking Meja</span>
          <strong><?= rupiah($biayaBooking) ?></strong>
        </div>
        <?php if ($adaPreorder): ?>
        <div class="dc-total-line text-muted small">
          <span>DP Pre-order Menu (50%)</span>
          <strong><?= rupiah($dpPreorder) ?></strong>
        </div>
        <?php endif; ?>
        <div class="dc-total-line mt-2 p-2 bg-light rounded">
          <strong>Total Bayar Sekarang</strong>
          <strong class="text-primary fs-5"><?= rupiah($totalBayar) ?></strong>
        </div>

        <p class="small text-muted mt-2 mb-0">
          <?php if ($adaPreorder): ?>
            <i class="fa-solid fa-circle-info me-1"></i>
            Total sudah mencakup booking fee <strong><?= rupiah($biayaBooking) ?></strong>
            dan DP 50% pre-order menu <strong><?= rupiah($dpPreorder) ?></strong>.
            Sisa pembayaran menu dilunasi saat kedatangan.
          <?php else: ?>
            Biaya booking dibayar sekali untuk mengkonfirmasi reservasi Anda.
          <?php endif; ?>
        </p>
      </div>

    </div>
    <div class="col-lg-5">
      <div class="dc-panel">
        <h4>Info Rekening Cafe</h4>
        <div class="dc-bank-box">
          <span><?= e($bank['nama_bank']) ?></span>
          <strong><?= e($bank['nomor_rekening']) ?></strong>
          <p>a.n. <?= e($bank['atas_nama']) ?></p>
        </div>
        <?php if ($reservation['status'] === 'cancelled'): ?>
          <div class="alert alert-warning mt-3">Reservasi sudah dibatalkan. Pembayaran tidak tersedia.</div>
        <?php elseif ($booking_payment && $booking_payment['status'] === 'verified'): ?>
  <div class="alert alert-success mt-3"><i class="fa-solid fa-circle-check me-2"></i>Booking fee sudah terverifikasi. Reservasi Anda telah dikonfirmasi.</div>
  <p class="small text-muted mt-2 mb-3">Mau pre-order menu sekarang atau langsung pesan saat datang?</p>
  <?php /* FIX LOG-04: sertakan reservasi_id & jenis agar halaman pesan langsung pre-select reservasi */ ?>
  <a href="<?= url('pesan?jenis=reservasi&reservasi_id=' . (int)$reservation['id']) ?>" class="btn dc-btn-submit w-100 mb-2">
    <i class="fa-solid fa-utensils me-2"></i>Ya, Pre-order Menu Sekarang
  </a>
  <a href="<?= url('riwayat') ?>" class="btn btn-outline-secondary w-100">
    Tidak, Pesan Langsung di Cafe
  </a>
        <?php else: ?>
          <form method="POST" action="<?= url('reservasi/payment/upload') ?>" enctype="multipart/form-data" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="reservasi_id" value="<?= e($reservation['id']) ?>">
            <label class="dc-form-label">Upload Bukti Transfer</label>
            <input type="file" name="bukti_tf" class="form-control dc-input mb-2" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf" required>
            <small class="text-muted d-block mb-3">Format: JPG, PNG, WEBP, atau PDF. Maksimal 2 MB.</small>
            <button class="btn dc-btn-submit w-100">Kirim Bukti Transfer</button>
          </form>
          <?php if ($booking_payment): ?>
            <hr>
            <p class="mb-1">Status payment: <span class="dc-status <?= e($booking_payment['status']) ?>"><?= e($booking_payment['status']) ?></span></p>
            <?php if (!empty($booking_payment['bukti_url'])): ?>
              <a href="<?= e($booking_payment['bukti_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark mb-2"><i class="fa-solid fa-eye"></i> Lihat Bukti</a>
            <?php endif; ?>
            <?php if ($booking_payment['catatan_admin']): ?>
              <p class="small text-muted">Catatan admin: <?= e($booking_payment['catatan_admin']) ?></p>
            <?php endif; ?>
            <?php if ($booking_payment['status'] === 'rejected'): ?>
              <div class="alert alert-danger mt-2 small">Payment ditolak. Silakan upload ulang bukti transfer yang benar.</div>
            <?php elseif ($booking_payment['status'] === 'pending'): ?>
              
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div></section>