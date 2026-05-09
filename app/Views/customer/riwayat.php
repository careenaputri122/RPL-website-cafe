<?php
// Gabungkan reservasi + pesanan jadi satu timeline, urutkan by created_at DESC
$timeline = [];
foreach ($reservations as $r) {
    $timeline[] = [
        'type'       => 'reservasi',
        'data'       => $r,
        'payment'    => find_payment_by_reservation($r['id']),
        'created_at' => $r['created_at'],
    ];
}
foreach ($orders as $o) {
    $timeline[] = [
        'type'       => 'pesanan',
        'data'       => $o,
        'payment'    => find_payment_by_order($o['id']),
        'created_at' => $o['created_at'],
    ];
}
usort($timeline, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
?>

<section class="dc-page-hero small">
  <div class="container">
    <span class="dc-section-badge">RIWAYAT</span>
    <h1>Riwayat Reservasi & Pesanan</h1>
    <p>Pantau status reservasi, pesanan, dan payment Anda.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="dc-panel" style="max-width: 720px; margin: 0 auto;">

      <?php if (!count($timeline)): ?>
        <div class="alert alert-light border text-center">
          Belum ada transaksi.
          <a href="<?= url('pesan') ?>">Pesan menu</a> atau
          <a href="<?= url('reservasi') ?>">buat reservasi</a> sekarang.
        </div>
      <?php endif; ?>

      <div class="dc-history-list" style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($timeline as $item):
          $type    = $item['type'];
          $pay     = $item['payment'];
          $payStatus = $pay ? $pay['status'] : 'pending';
        ?>
        <div class="dc-history-item" style="display: flex; flex-direction: column; gap: 8px;">

          <?php if ($type === 'reservasi'):
            $r = $item['data'];
          ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
              <div>
                <strong><?= e($r['kode']) ?></strong>
                <p class="mb-1"><?= e($r['tanggal']) ?> pukul <?= e($r['jam']) ?> &middot; <?= e($r['jumlah_orang']) ?> orang &middot; Meja <?= e($r['no_meja']) ?></p>
                <?php if (!empty($r['catatan'])): ?>
                  <small class="text-muted"><?= e($r['catatan']) ?></small>
                <?php endif; ?>
              </div>
              <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0;">
                <span class="dc-status" style="font-size: 10px; padding: 2px 8px; border-radius: 999px; background: #EEEDFE; color: #3C3489; font-weight: 500;">Reservasi</span>
                <span class="dc-status <?= e($r['status']) ?>">
                  <?= $r['status'] === 'confirmed' ? 'Reservasi: confirmed' : 'Reservasi: ' . e($r['status']) ?>
                </span>
                <?php if ($pay): ?>
                  <span class="dc-status <?= e($payStatus) ?>">Booking: <?= e($payStatus) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div style="border-top: 0.5px solid var(--color-border-tertiary, #e5e5e5); padding-top: 8px;">
              <?php if ($r['status'] === 'pending' && (!$pay || $payStatus !== 'verified')): ?>
                <a class="dc-small-link" href="<?= url('reservasi/payment?id=' . $r['id']) ?>">
                  <i class="fa-solid fa-credit-card"></i> Bayar Booking Fee
                </a>
              <?php elseif ($r['status'] === 'confirmed'): ?>
                <a class="dc-small-link" href="<?= url('pesan?jenis=reservasi&reservasi_id=' . $r['id']) ?>">
                  Pre-order menu
                </a>
              <?php endif; ?>
            </div>

          <?php else:
            $o = $item['data'];
          ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
              <div>
                <strong><?= e($o['kode']) ?></strong>
                <span class="text-muted" style="font-size: 13px;">&nbsp;&middot; <?= e(ucfirst($o['jenis'])) ?></span>
                <p class="mb-1"><?= rupiah($o['total']) ?> &middot; DP <?= rupiah($o['deposit']) ?>
                  <?php if (!empty($o['no_meja']) && $o['no_meja'] !== '-'): ?>
                    &middot; Meja <?= e($o['no_meja']) ?>
                  <?php endif; ?>
                </p>
                <small class="text-muted">
                  <?php foreach ($o['items'] as $idx => $it): ?>
                    <?= e($it['nama']) ?> x<?= e($it['qty']) ?><?= $idx < count($o['items']) - 1 ? ' &middot; ' : '' ?>
                  <?php endforeach; ?>
                </small>
                <?php if (!empty($o['reservation_id'])): ?>
                  <br><small class="text-muted">Terhubung reservasi #<?= e($o['reservation_id']) ?></small>
                <?php endif; ?>
              </div>
              <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0;">
                <span class="dc-status" style="font-size: 10px; padding: 2px 8px; border-radius: 999px; background: #E1F5EE; color: #085041; font-weight: 500;">Pesanan</span>
                <span class="dc-status <?= e($o['status']) ?>"><?= e($o['status']) ?></span>
                <span class="dc-status <?= e($payStatus) ?>">Bayar: <?= e($payStatus) ?></span>
              </div>
            </div>
            <div style="border-top: 0.5px solid var(--color-border-tertiary, #e5e5e5); padding-top: 8px;">
              <a class="dc-small-link" href="<?= url('payment?order_id=' . $o['id']) ?>">Detail Payment</a>
            </div>

          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>
