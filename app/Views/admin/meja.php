<?php $admin_title = 'Kelola Meja'; ?>
<div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-start gap-3">
  <i class="fa-solid fa-robot fs-4 mt-1 text-primary"></i>
  <div>
    <strong>Dua Sistem Status Meja yang Bekerja Terpisah</strong>
    <p class="mb-0 small text-muted mt-1">
      <strong>Dine-in:</strong> Status meja (<em>terisi/tersedia</em>) dikelola otomatis berdasarkan pesanan dine-in aktif.
      Ketika pesanan selesai atau dibatalkan, meja langsung kembali tersedia.<br>
      <strong>Reservasi:</strong> Ketersediaan meja untuk reservasi dikelola via sistem <em>time-overlap</em> — terpisah dari status dine-in di atas.
      Pelanggan yang reservasi tidak memblokir meja dari dine-in, dan sebaliknya.<br>
      <em>Field "Status" di form berikut hanya untuk keperluan khusus (misal: meja maintenance).</em>
    </p>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="dc-admin-card">
      <h4>Tambah / Edit Meja</h4>
      <form method="POST" action="<?= url('admin/meja/save') ?>"><?= csrf_field() ?>
        <input type="hidden" name="id" id="tableId">
        <label class="dc-form-label">No Meja</label>
        <input name="no_meja" id="tableNo" class="form-control dc-input mb-2" required>
        <label class="dc-form-label">Kapasitas</label>
        <input type="number" name="kapasitas" id="tableCap" class="form-control dc-input mb-2" required>
        <label class="dc-form-label">Status <small class="text-muted fw-normal">(otomatis — ubah hanya jika perlu)</small></label>
        <select name="status" id="tableStatus" class="form-select dc-input mb-3">
          <option value="tersedia">tersedia</option>
          <option value="terisi">terisi</option>
        </select>
        <button class="btn dc-btn-submit w-100">Simpan Meja</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="dc-admin-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Daftar Meja</h4>
        <small class="text-muted"><i class="fa-solid fa-rotate me-1"></i>Status diperbarui otomatis tiap ±1 menit</small>
      </div>
      <div class="row g-3">
        <?php foreach ($tables as $t): ?>
        <div class="col-sm-6 col-xl-4">
          <div class="dc-table-admin-card">
            <strong><?= e($t['no_meja']) ?></strong>
            <span><?= e($t['kapasitas']) ?> orang</span>
            <span class="dc-status <?= e($t['status']) ?>"><?= e($t['status']) ?></span>
            <button class="btn btn-sm btn-outline-dark table-edit" data-table='<?= e(json_encode($t)) ?>'>Edit</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

