<?php
function app_config($key = null, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../../config/app.php';
    }
    if ($key === null) {
        return $config;
    }
    return array_key_exists($key, $config) ? $config[$key] : $default;
}

function detect_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/public/index.php';
    $dir = rtrim(str_replace('/index.php', '', $script), '/');
    return $scheme . '://' . $host . $dir;
}

function base_url($path = '') {
    $base = app_config('base_url', '');
    if (!$base) {
        $base = detect_base_url();
    }
    $base = rtrim($base, '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function url($route = '') {
    $route = trim($route, '/');
    if ($route === '') {
        return base_url('index.php');
    }

    // Link query string lebih aman untuk XAMPP/Laragon tanpa mod_rewrite.
    if (!app_config('pretty_url', false)) {
        $query = '';
        if (strpos($route, '?') !== false) {
            list($route, $query) = explode('?', $route, 2);
        }
        $segments = explode('/', trim($route, '/'));
        $page = isset($segments[0]) && $segments[0] !== '' ? $segments[0] : 'home';
        $action = count($segments) > 1 ? implode('/', array_slice($segments, 1)) : '';
        $params = ['page' => $page];
        if ($action !== '') {
            $params['action'] = $action;
        }
        $built = http_build_query($params);
        if ($query !== '') {
            $built .= '&' . $query;
        }
        return base_url('index.php') . '?' . $built;
    }

    return base_url($route);
}

function asset($path = '') {
    return base_url(ltrim($path, '/'));
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($number) {
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

function redirect_to($route = '') {
    header('Location: ' . url($route));
    exit;
}

function old($key, $default = '') {
    return isset($_SESSION['old_input'][$key]) ? $_SESSION['old_input'][$key] : $default;
}

function clear_old_input() {
    unset($_SESSION['old_input']);
}

function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type) {
    if (!isset($_SESSION['flash'][$type])) {
        return null;
    }
    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $message;
}

function has_flash($type) {
    return isset($_SESSION['flash'][$type]);
}

function flash($type) {
    return get_flash($type);
}

function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        try {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $_SESSION['_csrf_token'] = sha1(uniqid('', true) . mt_rand());
        }
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['_csrf_token']) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}

function redirect_back($fallbackRoute = 'home') {
    if (!empty($_POST)) {
        $_SESSION['old_input'] = $_POST;
    }
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        // Pastikan referer berasal dari host yang sama
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $currentHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if ($refererHost === $currentHost) {
            header('Location: ' . $referer);
            exit;
        }
    }
    redirect_to($fallbackRoute);
}

function request_route() {
    if (isset($_GET['page']) && $_GET['page'] !== '') {
        $page = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['page']);
        $action = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9_\/-]/', '', $_GET['action']) : '';
        return trim($page . ($action !== '' ? '/' . $action : ''), '/');
    }

    $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $baseDir = rtrim(str_replace('/index.php', '', $scriptName), '/');
    if ($baseDir !== '' && $baseDir !== '/' && substr($path, 0, strlen($baseDir)) === $baseDir) {
        $path = substr($path, strlen($baseDir));
    }
    $route = trim($path, '/');
    return $route === '' ? 'home' : $route;
}

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in() {
    return current_user() !== null;
}

function is_admin() {
    $user = current_user();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu. Gunakan demo: budi@email.com / password123.');
        $_SESSION['_open_login_modal'] = true;
        redirect_to('home');
    }
}

function require_admin() {
    if (!is_admin()) {
        set_flash('danger', 'Halaman admin hanya dapat diakses administrator.');
        redirect_to('login');
    }
}

function render($view, $data = []) {
    // Sanitasi nama view untuk mencegah path traversal
    $view = preg_replace('/[^a-zA-Z0-9_\/]/', '', $view);
    extract($data);
    $current_page = isset($current_page) ? $current_page : request_route();
    require __DIR__ . '/../Views/layouts/header.php';
    require __DIR__ . '/../Views/' . $view . '.php';
    require __DIR__ . '/../Views/layouts/footer.php';
    clear_old_input();
}

function render_auth($view, $data = []) {
    $view = preg_replace('/[^a-zA-Z0-9_\/]/', '', $view);
    extract($data);
    require __DIR__ . '/../Views/auth/layout_header.php';
    require __DIR__ . '/../Views/' . $view . '.php';
    require __DIR__ . '/../Views/auth/layout_footer.php';
    clear_old_input();
}

function render_admin($view, $data = []) {
    require_admin();
    $view = preg_replace('/[^a-zA-Z0-9_\/]/', '', $view);
    extract($data);
    $current_admin_page = isset($current_admin_page) ? $current_admin_page : request_route();
    require __DIR__ . '/../Views/admin/layout_header.php';
    require __DIR__ . '/../Views/' . $view . '.php';
    require __DIR__ . '/../Views/admin/layout_footer.php';
    clear_old_input();
}
