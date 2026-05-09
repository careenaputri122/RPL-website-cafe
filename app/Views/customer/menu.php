<section class="dc-page-hero small">
  <div class="container"><span class="dc-section-badge">KATALOG</span><h1>Menu Kami</h1><p>Temukan hidangan favorit Anda dari pilihan menu autentik kami.</p></div>
</section>
<section class="py-5 dc-catalog-section"><div class="container">
  <div class="dc-toolbar mb-4">
    <div class="dc-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="menuSearch" placeholder="Cari menu..."></div>
    <div class="dc-filter-group" id="menuFilter"><button class="active" data-category="Semua">Semua</button><button data-category="Makanan">Makanan</button><button data-category="Minuman">Minuman</button><button data-category="Dessert">Dessert</button></div>
  </div>
  <div class="row g-4" id="menuGrid">
    <?php foreach ($menus as $item): $available = (int)$item['stok'] > 0; ?>
    <div class="col-sm-6 col-lg-4 col-xl-3 menu-filter-item" data-name="<?= e(strtolower($item['nama'])) ?>" data-category="<?= e($item['kategori']) ?>">
      <div class="dc-product-card <?= !$available ? 'is-empty' : '' ?>">
        <div class="dc-product-img"><img src="<?= e($item['foto']) ?>" alt="<?= e($item['nama']) ?>"><span class="dc-stock-badge <?= $available ? 'ok' : 'empty' ?>"><?= $available ? 'Tersedia' : 'Habis' ?></span></div>
        <div class="dc-product-body"><span class="dc-product-cat"><?= e($item['kategori']) ?></span><h5><?= e($item['nama']) ?></h5><p><?= e($item['deskripsi']) ?></p><div class="d-flex justify-content-between align-items-center"><strong><?= rupiah($item['harga']) ?></strong>
          <a class="dc-add-mini <?= !$available ? 'disabled' : '' ?>"
   href="<?= is_logged_in() ? url('pesan') : '#' ?>"
   <?= !is_logged_in() ? 'data-bs-toggle="modal" data-bs-target="#loginModal"' : '' ?>>
  <i class="fa-solid fa-plus"></i>
</a>
        </div></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div></section>
