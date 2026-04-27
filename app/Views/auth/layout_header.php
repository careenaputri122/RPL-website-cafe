<!DOCTYPE html>
<html lang="id"><head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? e($page_title) . ' - ' . e(app_config('name')) : e(app_config('name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head><body class="dc-auth-body">
<?php require __DIR__ . '/../partials/flash.php'; ?>
