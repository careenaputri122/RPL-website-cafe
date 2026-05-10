<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">PAYMENT</span><h1>Pembayaran Gabungan</h1><p>Bayar semua tagihan Anda sekaligus dalam satu struk.</p></div></section>

<section class="py-5"><div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <form method="POST" action="<?= url('payment/bulk/upload') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="dc-panel mb-4">
          <h5>Daftar Tagihan Tertunda</h5>
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead class="text-muted small">
                <tr>
                  <th>Item</th>
                  <th>Jenis</th>
                  <th class="text-end">Jumlah Tagihan</th>
                </tr>
              </thead>
              <tbody>
                <?php $totalSemua = 0; foreach ($unpaidPayments as $p): $totalSemua += $p['total']; ?>
                <tr>
                  <td><strong><?= e($p['kode']) ?></strong></td>
                  <td><span class="badge bg-light text-dark border"><?= e($p['tipe'] ?? '-') ?></span></td>
                  <td class="text-end fw-bold"><?= rupiah($p['total']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="border-top">
                  <td colspan="2" class="pt-3"><h5>Total Yang Harus Dibayar</h5></td>
                  <td class="text-end pt-3"><h4 class="text-success"><?= rupiah($totalSemua) ?></h4></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div class="dc-panel">
          <h5>Upload Bukti Pembayaran</h5>
          <p class="small text-muted">Pastikan nominal transfer sesuai dengan total di atas agar verifikasi lancar.</p>
          
          <div class="dc-bank-info p-3 bg-light rounded mb-4">
            <div class="row">
<?php $bankInfo = app_config('bank'); ?>
              <div class="col-sm-6">
                <small class="text-muted d-block">Bank Tujuan:</small>
                <strong>Bank <?= e($bankInfo['nama_bank']) ?></strong>
              </div>
              <div class="col-sm-6 text-sm-end">
                <small class="text-muted d-block">Nomor Rekening:</small>
                <strong><?= e($bankInfo['nomor_rekening']) ?> (a/n <?= e($bankInfo['atas_nama']) ?>)</strong>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="dc-form-label">Pilih Foto Struk / Screenshot Transfer</label>
            <input type="file" name="bukti_tf" class="form-control dc-input" accept="image/*" required>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn dc-btn-submit btn-lg">Bayar Semua Sekarang</button>
            <a href="<?= url('riwayat') ?>" class="btn btn-link text-muted">Batal & Kembali ke Riwayat</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div></section>
