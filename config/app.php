<?php
// App Configuration
define('APP_NAME', 'Damian Cafe');
define('APP_URL', 'http://localhost/RPL-WEBSITE-CAFE');
define('APP_VERSION', '1.0.0');

// Base path helper
function base_url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function asset($path = '') {
    return APP_URL . '/public/' . ltrim($path, '/');
}
