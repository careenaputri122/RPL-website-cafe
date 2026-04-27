<?php
// Redirect root project ke front controller di folder public.
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$target = ($base === '' || $base === '/') ? '/public/' : $base . '/public/';
header('Location: ' . $target);
exit;
