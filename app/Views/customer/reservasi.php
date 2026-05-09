<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">BOOKING</span><h1>Reservasi Meja</h1><p>Pesan meja favorit Anda dengan mudah.</p></div></section>
<section class="py-5"><div class="container">
  <div class="dc-stepper mb-4">
    <div class="dc-stepper-step active"><span class="dc-step-circle">1</span><span class="dc-step-label">Tanggal &amp; Waktu</span></div>
    <div class="dc-stepper-line"></div>
    <div class="dc-stepper-step"><span class="dc-step-circle">2</span><span class="dc-step-label">Detail Tamu</span></div>
    <div class="dc-stepper-line"></div>
    <div class="dc-stepper-step"><span class="dc-step-circle">3</span><span class="dc-step-label">Konfirmasi</span></div>
  </div>
  <form method="POST" action="<?= url('reservasi/store') ?>" class="row g-4 dc-reservation-form"><?= csrf_field() ?>
    <div class="col-lg-8">
      <div class="dc-panel">
        <h5>Pilih Tanggal</h5><input type="date" name="tanggal" id="tanggalReservasi" class="form-control dc-input" min="<?= date('Y-m-d') ?>" value="<?= e(old('tanggal', date('Y-m-d'))) ?>" required>
        <h5 class="mt-4">Pilih Jam</h5><input type="hidden" name="jam" id="jamReservasi" value="<?= e(old('jam', '19:00')) ?>">
        <div class="dc-time-grid">
          <?php $oldJam = old('jam', '19:00'); foreach (['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30'] as $time): ?>
            <button type="button" class="dc-time-btn <?= $time === $oldJam ? 'selected' : '' ?>" data-time="<?= e($time) ?>"><?= e($time) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="row g-3 mt-2">
          <div class="col-md-6"><label class="dc-form-label">Jumlah Orang</label><input type="number" name="jumlah_orang" id="jumlahOrang" min="1" max="20" value="<?= e(old('jumlah_orang', '2')) ?>" class="form-control dc-input" required></div>
          <div class="col-12">
            <label class="dc-form-label">Pilih Meja <small class="text-muted fw-normal">(opsional — kosongkan untuk otomatis)</small></label>
            <input type="hidden" name="no_meja" id="reservasiMeja" value="<?= e(old('no_meja', '')) ?>">
            <div class="dc-table-grid mt-2">
              <?php $oldMeja = old('no_meja', ''); foreach ($tables as $t): ?>
                <?php $available = $t['available'] ?? ($t['status'] === 'tersedia'); ?>
                <button type="button" class="dc-table-seat <?= $available ? 'tersedia' : 'terisi' ?> <?= $t['no_meja'] === $oldMeja ? 'selected' : '' ?>" 
                  data-table="<?= e($t['no_meja']) ?>" 
                  <?= !$available && $t['no_meja'] !== $oldMeja ? 'disabled' : '' ?>>
                  <strong><?= e($t['no_meja']) ?></strong>
                  <span><?= e($t['kapasitas']) ?> org</span>
                  <small><?= $available ? 'Tersedia' : 'Terisi' ?></small>
                </button>
              <?php endforeach; ?>
            </div>
            <div class="dc-legend"><span><i class="ok"></i> Tersedia</span><span><i class="bad"></i> Terisi</span></div>
          </div>
          <div class="col-12"><label class="dc-form-label">Catatan</label><textarea name="catatan" rows="4" class="form-control dc-input" placeholder="Contoh: dekat jendela, kursi bayi, ulang tahun..."><?= e(old('catatan', '')) ?></textarea></div>
        </div>

        <div class="mt-5">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5>Pilih Menu <small class="text-muted fw-normal">(Pre-order — Opsional)</small></h5>
            <span class="badge bg-light text-dark border">Dibayar 50% dimuka</span>
          </div>
          <input type="hidden" name="cart_data" id="cartData" value="<?= e(old('cart_data', '[]')) ?>">
          
          <div class="dc-toolbar mb-3">
            <div class="dc-search w-100">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="menuSearch" placeholder="Cari menu favorit...">
            </div>
          </div>

          <div class="row g-3 dc-mini-menu-list" style="max-height: 400px; overflow-y: auto;">
            <?php foreach ($menus as $m): $available = (int)$m['stok'] > 0; ?>
            <div class="col-md-6 menu-item-card" data-name="<?= e(strtolower($m['nama'])) ?>">
              <div class="dc-order-menu-card mini <?= !$available ? 'is-empty' : '' ?>">
                <img src="<?= e($m['foto']) ?>" alt="<?= e($m['nama']) ?>">
                <div class="flex-grow-1">
                  <h6 class="mb-0 small fw-bold"><?= e($m['nama']) ?></h6>
                  <strong class="text-gold d-block small"><?= rupiah($m['harga']) ?></strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary cart-btn-minus" data-id="<?= e($m['id']) ?>" style="display:none;">-</button>
                  <span class="cart-qty small fw-bold" data-id="<?= e($m['id']) ?>" style="display:none;">0</span>
                  <button type="button" class="dc-add-mini cart-btn-plus" data-id="<?= e($m['id']) ?>" data-name="<?= e($m['nama']) ?>" data-price="<?= e($m['harga']) ?>" data-stock="<?= e($m['stok']) ?>" <?= !$available ? 'disabled' : '' ?>><i class="fa-solid fa-plus"></i></button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <button class="btn dc-btn-submit mt-5 w-100 py-3" type="submit">Konfirmasi & Buat Reservasi</button>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dc-summary-card sticky-lg-top">
        <h5>Ringkasan Reservasi</h5>
        <ul>
          <li><i class="fa-regular fa-calendar"></i> <span id="summaryDate"><?= date('d M Y') ?></span></li>
          <li><i class="fa-regular fa-clock"></i> <span id="summaryTime">19:00</span></li>
          <li><i class="fa-solid fa-user-group"></i> <span id="summaryPeople">2 orang</span></li>
          <li><i class="fa-solid fa-chair"></i> <span id="summaryMeja">Meja terbaik tersedia</span></li>
        </ul>
        <hr>
        <div id="preorderSummary" style="display:none;">
          <h6>Pre-order Menu</h6>
          <div id="summaryItems" class="small text-muted mb-3"></div>
          <div class="d-flex justify-content-between mb-2"><span>Total Menu</span><strong id="summaryMenuTotal">Rp 0</strong></div>
          <div class="d-flex justify-content-between mb-3"><span>DP Menu (50%)</span><strong id="summaryMenuDP" class="text-success">Rp 0</strong></div>
          <hr>
        </div>
        <div class="d-flex justify-content-between"><span>Biaya Booking Meja</span><strong>Rp 15.000</strong></div>
        <div class="d-flex justify-content-between mt-3 p-2 bg-light rounded">
          <strong>Total Bayar Sekarang</strong>
          <strong class="text-primary fs-5" id="summaryGrandTotal">Rp 15.000</strong>
        </div>
        <small class="text-muted d-block mt-3">*Sisa pembayaran menu dilunasi saat kedatangan di cafe.</small>
      </div>
    </div>
  </form>
</div></section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  function rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
  
  // 1. Filter Menu
  const searchInput = document.getElementById('menuSearch');
  const menuItems = document.querySelectorAll('.menu-item-card');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      menuItems.forEach(item => {
        const name = item.dataset.name || '';
        item.style.display = !q || name.includes(q) ? '' : 'none';
      });
    });
  }

  // 2. Mini Cart Logic
  let resCart = {};
  const cartDataInput = document.getElementById('cartData');
  const preorderSummary = document.getElementById('preorderSummary');
  const summaryItems = document.getElementById('summaryItems');
  const summaryMenuTotal = document.getElementById('summaryMenuTotal');
  const summaryMenuDP = document.getElementById('summaryMenuDP');
  const summaryGrandTotal = document.getElementById('summaryGrandTotal');
  const BOOKING_FEE = 15000;

  function updateSummary() {
    const items = Object.values(resCart);
    const totalMenu = items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const dpMenu = Math.ceil(totalMenu * 0.5);
    const grandTotal = BOOKING_FEE + dpMenu;

    if (items.length > 0) {
      preorderSummary.style.display = 'block';
      summaryItems.innerHTML = items.map(it => it.name + ' x' + it.qty).join(', ');
      summaryMenuTotal.textContent = rupiah(totalMenu);
      summaryMenuDP.textContent = rupiah(dpMenu);
    } else {
      preorderSummary.style.display = 'none';
    }

    summaryGrandTotal.textContent = rupiah(grandTotal);
    cartDataInput.value = JSON.stringify(items);
  }

  document.querySelectorAll('.cart-btn-plus').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.dataset.id;
      const name = this.dataset.name;
      const price = Number(this.dataset.price);
      const stock = Number(this.dataset.stock);

      if (!resCart[id]) resCart[id] = { id, name, price, qty: 0, stock };
      if (resCart[id].qty >= stock) {
        alert('Stok tidak mencukupi.');
        return;
      }

      resCart[id].qty++;
      updateUI(id);
      updateSummary();
    });
  });

  document.querySelectorAll('.cart-btn-minus').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.dataset.id;
      if (resCart[id]) {
        resCart[id].qty--;
        if (resCart[id].qty <= 0) delete resCart[id];
        updateUI(id);
        updateSummary();
      }
    });
  });

  function updateUI(id) {
    const qtySpan = document.querySelector(`.cart-qty[data-id="${id}"]`);
    const minusBtn = document.querySelector(`.cart-btn-minus[data-id="${id}"]`);
    const qty = resCart[id] ? resCart[id].qty : 0;

    if (qtySpan) {
      qtySpan.textContent = qty;
      qtySpan.style.display = qty > 0 ? 'inline' : 'none';
    }
    if (minusBtn) {
      minusBtn.style.display = qty > 0 ? 'inline' : 'none';
    }
  }

  // Restore old input if exists
  try {
    const oldData = JSON.parse(cartDataInput.value || '[]');
    if (Array.isArray(oldData)) {
      oldData.forEach(it => {
        resCart[it.id] = it;
        updateUI(it.id);
      });
      updateSummary();
    }
  } catch(e) {}

  // 3. Table Availability Logic
  async function updateTableAvailability() {
    const tanggal = document.getElementById('tanggalReservasi').value;
    const jam     = document.getElementById('jamReservasi').value;
    if (!tanggal || !jam) return;

    try {
      const res    = await fetch(`<?= url('api/meja-availability') ?>?tanggal=${tanggal}&jam=${encodeURIComponent(jam)}`);
      const tables = await res.json();

      document.querySelectorAll('.dc-table-seat').forEach(btn => {
        const row = tables.find(t => t.no_meja === btn.dataset.table);
        if (!row) return;

        const ok = row.available;
        btn.disabled = !ok;
        btn.classList.toggle('tersedia', ok);
        btn.classList.toggle('terisi', !ok);
        btn.querySelector('small').textContent = ok ? 'Tersedia' : 'Terisi';

        if (!ok && btn.classList.contains('selected')) {
          btn.classList.remove('selected');
          document.getElementById('reservasiMeja').value = '';
          document.getElementById('summaryMeja').textContent = 'Meja terbaik tersedia';
        }
      });
    } catch (e) {
      console.error('Gagal update ketersediaan meja', e);
    }
  }

  document.getElementById('tanggalReservasi').addEventListener('change', updateTableAvailability);

  document.querySelectorAll('.dc-time-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      setTimeout(updateTableAvailability, 50);
    });
  });
});
</script>
