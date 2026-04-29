document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.getElementById('mainNavbar');
  if (navbar) window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 50));

  function rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }

  function setupFilter(searchId, filterId, itemSelector) {
    const search = document.getElementById(searchId);
    const group = document.getElementById(filterId);
    const items = Array.from(document.querySelectorAll(itemSelector));
    let category = 'Semua';
    function apply() {
      const q = search ? search.value.toLowerCase().trim() : '';
      items.forEach(item => {
        const okCat = category === 'Semua' || item.dataset.category === category;
        const okText = !q || (item.dataset.name || '').includes(q);
        item.style.display = okCat && okText ? '' : 'none';
      });
    }
    if (search) search.addEventListener('input', apply);
    if (group) group.querySelectorAll('button').forEach(btn => btn.addEventListener('click', function () {
      group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      category = this.dataset.category;
      apply();
    }));
  }
  setupFilter('menuSearch', 'menuFilter', '.menu-filter-item');
  setupFilter('orderSearch', 'orderFilter', '.order-item');

  const timeButtons = document.querySelectorAll('.dc-time-btn');
  const jamInput = document.getElementById('jamReservasi');
  const summaryTime = document.getElementById('summaryTime');
  timeButtons.forEach(btn => btn.addEventListener('click', function () {
    timeButtons.forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    if (jamInput) jamInput.value = this.dataset.time;
    if (summaryTime) summaryTime.textContent = this.dataset.time;
  }));
  const jumlahOrang = document.getElementById('jumlahOrang');
  const summaryPeople = document.getElementById('summaryPeople');
  if (jumlahOrang && summaryPeople) jumlahOrang.addEventListener('input', () => summaryPeople.textContent = jumlahOrang.value + ' orang');
  const tanggalReservasi = document.getElementById('tanggalReservasi');
  const summaryDate = document.getElementById('summaryDate');
  if (tanggalReservasi && summaryDate) tanggalReservasi.addEventListener('change', () => summaryDate.textContent = tanggalReservasi.value);

  const orderJenis = document.getElementById('orderJenis');
  const tablePanel = document.getElementById('tablePanel');
  const reservationPanel = document.getElementById('reservationPanel');
  var checkoutReady = false;
  function applyService(service) {
    if (orderJenis) orderJenis.value = service;
    if (tablePanel) tablePanel.style.display = service === 'dine-in' ? '' : 'none';
    if (reservationPanel) reservationPanel.style.display = service === 'reservasi' ? '' : 'none';
    if (checkoutReady) updateCheckoutState();
  }
  const activeServiceBtn = document.querySelector('.dc-order-tabs button.active');
  if (activeServiceBtn) applyService(activeServiceBtn.dataset.service);
  document.querySelectorAll('.dc-order-tabs button').forEach(btn => btn.addEventListener('click', function () {
    document.querySelectorAll('.dc-order-tabs button').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    applyService(this.dataset.service);
  }));
  const orderTable = document.getElementById('orderTable');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const checkoutHint = document.getElementById('checkoutHint');
  const reservationSelect = document.querySelector('select[name="reservation_id"]');
  function updateCheckoutState() {
    if (!checkoutBtn) return;
    const values = Object.values(cart || {});
    const service = orderJenis ? orderJenis.value : 'dine-in';
    let ok = values.length > 0;
    let message = values.length ? 'Siap lanjut ke payment.' : 'Keranjang masih kosong.';
    if (ok && service === 'dine-in' && (!orderTable || !orderTable.value)) {
      ok = false;
      message = 'Pilih meja terlebih dahulu untuk dine-in.';
    }
    if (ok && service === 'reservasi' && (!reservationSelect || !reservationSelect.value)) {
      ok = false;
      message = 'Pilih reservasi aktif untuk pre-order.';
    }
    checkoutBtn.disabled = !ok;
    if (checkoutHint) checkoutHint.textContent = message;
  }
  if (reservationSelect) reservationSelect.addEventListener('change', updateCheckoutState);
  checkoutReady = true;
  document.querySelectorAll('.dc-table-seat:not(:disabled)').forEach(btn => btn.addEventListener('click', function () {
    document.querySelectorAll('.dc-table-seat').forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    if (orderTable) orderTable.value = this.dataset.table;
    // reservasi page table picker
    const reservasiMeja = document.getElementById('reservasiMeja');
    if (reservasiMeja) reservasiMeja.value = this.dataset.table;
    const summaryMeja = document.getElementById('summaryMeja');
    if (summaryMeja) summaryMeja.textContent = 'Meja ' + this.dataset.table;
    updateCheckoutState();
  }));

  var cart = {};
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  const cartData = document.getElementById('cartData');
  function renderCart() {
    if (!cartItems) return;
    const values = Object.values(cart);
    if (!values.length) {
      cartItems.className = 'dc-cart-empty';
      cartItems.innerHTML = '<i class="fa-solid fa-cart-shopping"></i><p>Keranjang masih kosong<br><small>Tambahkan menu dari daftar</small></p>';
    } else {
      cartItems.className = '';
      cartItems.innerHTML = values.map(item => '<div class="dc-cart-row"><div><strong>' + item.nama + '</strong><small>' + rupiah(item.harga) + '</small></div><div class="dc-cart-qty"><button type="button" data-minus="' + item.id + '">-</button><span>' + item.qty + '</span><button type="button" data-plus="' + item.id + '">+</button></div></div>').join('');
    }
    const total = values.reduce((sum, item) => sum + item.harga * item.qty, 0);
    if (cartTotal) cartTotal.textContent = rupiah(total);
    if (cartData) cartData.value = JSON.stringify(values);
    updateCheckoutState();
  }
  document.querySelectorAll('.cart-add').forEach(btn => btn.addEventListener('click', function () {
    const id = this.dataset.id;
    const stock = Number(this.dataset.stock || 9999);
    if (!cart[id]) cart[id] = { id: Number(id), nama: this.dataset.name, harga: Number(this.dataset.price), qty: 0, stock: stock };
    if (cart[id].qty >= stock) {
      alert('Jumlah melebihi stok tersedia.');
      return;
    }
    cart[id].qty += 1;
    renderCart();
  }));
  updateCheckoutState();
  if (cartItems) cartItems.addEventListener('click', function (e) {
    const plus = e.target.getAttribute('data-plus');
    const minus = e.target.getAttribute('data-minus');
    if (plus && cart[plus]) {
      const stock = cart[plus].stock || 9999;
      if (cart[plus].qty >= stock) {
        alert('Jumlah melebihi stok tersedia (' + stock + ').');
      } else {
        cart[plus].qty += 1;
        renderCart();
      }
    }
    if (minus && cart[minus]) { cart[minus].qty -= 1; if (cart[minus].qty <= 0) delete cart[minus]; renderCart(); }
  });

  document.querySelectorAll('.menu-edit').forEach(btn => btn.addEventListener('click', function () {
    const m = JSON.parse(this.dataset.menu);
    document.getElementById('menuId').value = m.id;
    document.getElementById('menuNama').value = m.nama;
    document.getElementById('menuKategori').value = m.kategori;
    document.getElementById('menuHarga').value = m.harga;
    document.getElementById('menuStok').value = m.stok;
    if (document.getElementById('menuMinStok')) document.getElementById('menuMinStok').value = m.minimum_stok || 5;
    if (document.getElementById('menuFotoRaw')) document.getElementById('menuFotoRaw').value = m.foto_raw || '';
    document.getElementById('menuFoto').value = (m.foto_raw || '').startsWith('http') ? m.foto_raw : '';
    document.getElementById('menuDeskripsi').value = m.deskripsi;
    document.getElementById('menuUnggulan').checked = Number(m.unggulan) === 1;
    window.scrollTo({top: 0, behavior: 'smooth'});
  }));
  document.querySelectorAll('.table-edit').forEach(btn => btn.addEventListener('click', function () {
    const t = JSON.parse(this.dataset.table);
    document.getElementById('tableId').value = t.id;
    document.getElementById('tableNo').value = t.no_meja;
    document.getElementById('tableCap').value = t.kapasitas;
    document.getElementById('tableStatus').value = t.status;
    window.scrollTo({top: 0, behavior: 'smooth'});
  }));
});
