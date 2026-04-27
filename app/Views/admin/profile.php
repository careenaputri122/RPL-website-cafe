<?php $admin_title = 'Profil Admin'; $user = current_user(); ?>
<div class="dc-admin-card" style="max-width:720px;"><h4>Edit Profil Admin</h4><form method="POST" action="<?= url('admin/profile/save') ?>"><?= csrf_field() ?>
  <label class="dc-form-label">Nama</label><input name="name" class="form-control dc-input mb-3" value="<?= e($user['name']) ?>" required>
  <label class="dc-form-label">Email</label><input name="email" type="email" class="form-control dc-input mb-3" value="<?= e($user['email']) ?>" required>
  <label class="dc-form-label">No. Telepon</label><input name="phone" class="form-control dc-input mb-3" value="<?= e(isset($user['phone']) ? $user['phone'] : '') ?>">
  <label class="dc-form-label">Password Baru <span class="text-muted fw-normal">(opsional)</span></label><input type="password" name="new_password" class="form-control dc-input mb-3" placeholder="Kosongkan jika tidak diganti">
  <button class="btn dc-btn-submit">Simpan Profil</button>
</form></div>
