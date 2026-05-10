<?php $admin_title = 'Daftar Pesanan'; ?>
<div class="dc-admin-card mb-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h4 class="mb-1">Semua Pesanan</h4><p class="text-muted mb-0">Alur: pending &rarr; diproses &rarr; selesai / dibatalkan.</p></div>
  </div>
  <div class="table-responsive mt-3"><table class="table dc-table align-middle"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Jenis</th><th>Meja</th><th>Item</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($orders as $o): $payment = find_payment_by_order($o['id']); $paidTotal = isset($o['paid_total']) ? (float)$o['paid_total'] : (float)$o['deposit']; $remaining = isset($o['remaining']) ? (float)$o['remaining'] : max(0, (float)$o['total'] - $paidTotal); ?>
      <tr>
        <td><?= e($o['kode']) ?><br><small><?= e($o['created_at']) ?></small></td>
        <td><?= e($o['nama']) ?><br><small><?= e($o['email']) ?></small></td>
        <td><?= e($o['jenis']) ?></td>
        <td><?= e($o['no_meja']) ?></td>
        <td><?php foreach ($o['items'] as $it): ?><span class="dc-chip"><?= e($it['nama']) ?> x<?= e($it['qty']) ?></span><?php endforeach; ?></td>
        <td><?= rupiah($o['total']) ?><br><small>Dibayar: <?= rupiah($paidTotal) ?></small><br><small>Sisa: <?= rupiah($remaining) ?></small></td>
        <td><span class="dc-status <?= e($o['status']) ?>"><?= e($o['status']) ?></span><?php if ($payment): ?><br><small>Payment: </small><span class="dc-status <?= e($payment['status']) ?>"><?= e($payment['status']) ?></span><?php endif; ?></td>
        <td>
          <?php if ($o['status'] !== 'dibatalkan' && $o['status'] !== 'selesai'): ?>
            <div class="d-flex flex-wrap gap-1">
              <?php if ($payment && $payment['status'] === 'verified' && $o['status'] !== 'diproses'): ?><form method="POST" action="<?= url('admin/pesanan/status') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><button name="status" value="diproses" class="btn btn-sm btn-primary">Proses</button></form><?php endif; ?>
              <?php if ($o['status'] === 'diproses'): ?><form method="POST" action="<?= url('admin/pesanan/status') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><button name="status" value="selesai" class="btn btn-sm btn-success">Selesai</button></form><?php endif; ?>
              <form method="POST" action="<?= url('admin/pesanan/status') ?>" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan dan meja dine-in akan tersedia kembali.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><button name="status" value="dibatalkan" class="btn btn-sm btn-outline-danger">Batalkan</button></form>
            </div>
          <?php else: ?>
            <span class="text-muted small">Tidak ada aksi</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div>
