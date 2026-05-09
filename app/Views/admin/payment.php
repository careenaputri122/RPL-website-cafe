<?php $admin_title = 'Verifikasi Payment'; ?>
<div class="dc-admin-card">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div><h4 class="mb-1">Daftar Payment</h4><p class="text-muted mb-0">Alur: pending &rarr; verified / rejected. Jika rejected, pesanan dibatalkan dan stok dikembalikan.</p></div>
  </div>
  <div class="table-responsive"><table class="table dc-table align-middle"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Bukti Transfer</th><th>Status</th><th>Catatan Admin</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($payments as $p): $hasProof = !empty($p['bukti_url']) || (isset($p['bukti_tf']) && trim($p['bukti_tf']) !== '' && strtolower($p['bukti_tf']) !== 'belum ada file'); ?>
      <tr>
        <td><?= e($p['kode']) ?></td>
        <td><?= e($p['nama']) ?></td>
        <td><?= rupiah($p['total']) ?></td>
        <td>
          <?= e($p['bukti_tf']) ?><br><small><?= e($p['tanggal_upload'] ?: '-') ?></small>
          <?php if (!empty($p['bukti_url'])): ?><br><a href="<?= e($p['bukti_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark mt-1"><i class="fa-solid fa-eye"></i> Lihat Bukti</a><?php endif; ?>
          <?php if (!$hasProof): ?><div class="small text-danger mt-1">Belum ada bukti transfer.</div><?php endif; ?>
        </td>
        <td><span class="dc-status <?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
        <td><?= e($p['catatan_admin']) ?></td>
        <td>
         <form method="POST" action="<?= url($p['tipe'] === 'booking' ? 'admin/booking-payment/verify' : 'admin/payment/verify') ?>" class="dc-verify-form">  <?= csrf_field() ?> 
            <input type="hidden" name="id" value="<?= e($p['id']) ?>">
            <input name="catatan_admin" class="form-control form-control-sm mb-2" placeholder="Catatan admin" value="<?= e($p['catatan_admin']) ?>">
            <div class="d-flex gap-1">
              <button name="status" value="verified" class="btn btn-sm btn-success" <?= !$hasProof || $p['status'] === 'verified' ? 'disabled' : '' ?>>Verified</button>
              <button name="status" value="rejected" class="btn btn-sm btn-outline-danger" <?= $p['status'] === 'rejected' ? 'disabled' : '' ?> onclick="return confirm('Reject payment ini? Pesanan akan dibatalkan dan stok dikembalikan.')">Rejected</button>
            </div>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div>
