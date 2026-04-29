<?php if (isset($payment) && $payment): ?>
<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">PAYMENT</span><h1>Pembayaran Reservasi</h1><p>Lakukan pembayaran deposit booking Rp15.000.</p></div></section>

<section class="py-5"><div class="container">
  <?php if (has_flash('success')): ?>
    <div class="alert alert-success"><?= flash('success') ?></div>
  <?php endif; ?>
  
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="dc-panel">
        <div class="text-center mb-4">
          <div class="dc-badge-large mb-3">Reservasi</div>
          <h3><?= e($reservation['kode']) ?></h3>
          <p class="text-muted"><?= e($reservation['nama']) ?> - <?= e($reservation['no_meja']) ?> (<?= e($reservation['jumlah_orang']) ?> org)</p>
          <p class="text-muted"><?= date('d M Y, H:i', strtotime($reservation['tanggal'] . ' ' . $reservation['jam'])) ?></p>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <div class="dc-payment-info">
              <h6><i class="fa-solid fa-receipt"></i> Payment #<?= substr($payment['kode'], 0, 8) ?></h6>
              <p class="text-success fw-bold fs-4 mb-1">Rp 15.000</p>
              <small class="text-muted">Deposit booking meja</small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="dc-payment-status <?= $payment['status'] ?>">
              <span class="badge <?= $payment['status'] === 'verified' ? 'bg-success' : ($payment['status'] === 'pending' ? 'bg-warning' : 'bg-secondary') ?>">
                <?= ucfirst(str_replace('_', ' ', $payment['status'])) ?>
              </span>
              <?php if ($payment['tanggal_upload']): ?>
                <small class="d-block mt-1">Uploaded: <?= date('d M Y H:i', strtotime($payment['tanggal_upload'])) ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if ($payment['status'] === 'pending' && !payment_has_receipt($payment)): ?>
        <form method="POST" action="<?= url('payment_reservasi/upload') ?>" enctype="multipart/form-data" class="dc-upload-form">
          <?= csrf_field() ?>
          <input type="hidden" name="res_id" value="<?= (int)$reservation['id'] ?>">
          <label class="dc-form-label">Upload Bukti Transfer</label>
          <input type="file" name="bukti_tf" class="form-control dc-input" accept="image/*,.pdf" required>
          <div class="text-center mt-4">
            <button type="submit" class="btn dc-btn-primary btn-lg">Kirim Bukti Pembayaran</button>
          </div>
        </form>
        <?php elseif (payment_has_receipt($payment)): ?>
        <div class="text-center">
          <div class="dc-receipt-preview mb-4">
            <?php if ($payment['bukti_url']): ?>
              <img src="<?= $payment['bukti_url'] ?>" class="img-fluid rounded shadow" style="max-height: 300px;">
            <?php else: ?>
              <div class="bg-light p-5 rounded">
                <i class="fa-solid fa-file-image fa-3x text-muted"></i>
                <p class="text-muted mt-2">Bukti transfer</p>
              </div>
            <?php endif; ?>
          </div>
          <p class="text-success"><i class="fa-solid fa-check-circle"></i> Bukti sudah diunggah, menunggu verifikasi admin.</p>
        </div>
        <?php endif; ?>

        <hr class="my-5">
        <div class="row">
          <div class="col-md-6">
            <div class="dc-bank-info">
              <h6><i class="fa-solid fa-building-columns"></i> Transfer ke:</h6>
              <div class="mt-3">
                <p><strong>Bank BCA</strong><br>a/n Cafe Nusantara<br>1234 5678 90</p>
                <p class="small text-muted mb-0">Screenshot + catatan: "Reservasi <?= e($reservation['kode']) ?>"</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="dc-steps">
              <h6>Langkah Selanjutnya:</h6>
              <ol class="small">
                <li>Transfer tepat Rp15.000</li>
                <li>Upload screenshot bukti</li>
                <li>Tunggu verifikasi (≤2 jam)</li>
                <li>Reservasi dikonfirmasi via WhatsApp</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div></section>

<?php if (isset($payment['catatan_admin']) && $payment['catatan_admin']): ?>
<div class="alert alert-info"><?= e($payment['catatan_admin']) ?></div>
<?php endif; ?>

<?php else: ?>
<div class="container py-5 text-center">
  <div class="dc-empty-state">
    <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-4"></i>
    <h4>Reservasi tidak ditemukan</h4>
    <p>Data reservasi atau payment tidak valid.</p>
    <a href="<?= url('reservasi') ?>" class="btn dc-btn-primary">Buat Reservasi Baru</a>
  </div>
</div>
<?php endif; ?>
