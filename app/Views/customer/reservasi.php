<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">BOOKING</span><h1>Reservasi Meja</h1><p>Pesan meja favorit Anda dengan mudah.</p></div></section>
<section class="py-5"><div class="container">
  <div class="dc-stepper mb-4"><span class="active">1 Tanggal & Waktu</span><span>2 Detail Tamu</span><span>3 Konfirmasi</span></div>
  <form method="POST" action="<?= url('reservasi/store') ?>" class="row g-4 dc-reservation-form"><?= csrf_field() ?>
    <div class="col-lg-8">
      <div class="dc-panel">
        <h5>Pilih Tanggal</h5><input type="date" name="tanggal" id="tanggalReservasi" class="form-control dc-input" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
        <h5 class="mt-4">Pilih Jam</h5><input type="hidden" name="jam" id="jamReservasi" value="19:00">
        <div class="dc-time-grid">
          <?php foreach (['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30'] as $time): ?>
            <button type="button" class="dc-time-btn <?= $time === '19:00' ? 'selected' : '' ?>" data-time="<?= e($time) ?>"><?= e($time) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="row g-3 mt-2">
          <div class="col-md-6"><label class="dc-form-label">Jumlah Orang</label><input type="number" name="jumlah_orang" id="jumlahOrang" min="1" max="20" value="2" class="form-control dc-input" required></div>
          <div class="col-md-6"><label class="dc-form-label">Pilih Meja</label><select name="no_meja" class="form-select dc-input"><option value="">Otomatis meja terbaik</option><?php foreach ($tables as $t): if ($t['status'] === 'tersedia'): ?><option value="<?= e($t['no_meja']) ?>"><?= e($t['no_meja']) ?> - <?= e($t['kapasitas']) ?> orang</option><?php endif; endforeach; ?></select></div>
          <div class="col-12"><label class="dc-form-label">Catatan</label><textarea name="catatan" rows="4" class="form-control dc-input" placeholder="Contoh: dekat jendela, kursi bayi, ulang tahun..."></textarea></div>
        </div>
        <button class="btn dc-btn-submit mt-4" type="submit">Buat Reservasi</button>
      </div>
    </div>
    <div class="col-lg-4"><div class="dc-summary-card sticky-lg-top"><h5>Ringkasan Reservasi</h5><ul><li><i class="fa-regular fa-calendar"></i> <span id="summaryDate"><?= date('d M Y') ?></span></li><li><i class="fa-regular fa-clock"></i> <span id="summaryTime">19:00</span></li><li><i class="fa-solid fa-user-group"></i> <span id="summaryPeople">2 orang</span></li><li><i class="fa-solid fa-chair"></i> Meja terbaik tersedia</li></ul><hr><div class="d-flex justify-content-between"><span>Biaya Booking</span><strong>Rp 15.000</strong></div><small class="text-muted d-block mt-2">*Deposit 50% dari total pesanan pre-order dibayar saat pesan menu. Sisa dilunasi saat kedatangan.</small></div></div>
  </form>
</div></section>
