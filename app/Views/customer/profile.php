<section class="dc-page-hero small"><div class="container"><span class="dc-section-badge">AKUN</span><h1>Edit Profil</h1><p>Perbarui data akun pelanggan.</p></div></section>
<section class="py-5"><div class="container"><div class="dc-panel mx-auto" style="max-width:720px;"><?php $user = current_user(); ?><form method="POST" action="<?= url('profile/save') ?>"><?= csrf_field() ?>
  <label class="dc-form-label">Nama</label><input name="name" class="form-control dc-input mb-3" value="<?= e($user['name']) ?>" required>
  <label class="dc-form-label">Email</label><input type="email" name="email" class="form-control dc-input mb-3" value="<?= e($user['email']) ?>" required>
  <label class="dc-form-label">No. Telepon</label><input name="phone" class="form-control dc-input mb-3" value="<?= e(isset($user['phone']) ? $user['phone'] : '') ?>">
  <label class="dc-form-label">Password Baru <span class="text-muted fw-normal">(opsional)</span></label><input type="password" name="new_password" class="form-control dc-input mb-3" placeholder="Kosongkan jika tidak diganti">
  <button class="btn dc-btn-submit">Simpan Profil</button>
</form></div></div></section>
