<?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
  <?php if ($message = get_flash($type)): ?>
    <div class="container dc-flash-wrap">
      <div class="alert alert-<?= e($type) ?> alert-dismissible fade show dc-alert" role="alert">
        <?= e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
