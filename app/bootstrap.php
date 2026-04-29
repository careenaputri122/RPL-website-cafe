<?php
require_once __DIR__ . '/Support/helpers.php';
require_once __DIR__ . '/Support/data.php';

date_default_timezone_set(app_config('timezone', 'Asia/Jakarta'));
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
init_demo_state();

$route = request_route();
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

function flash_result($result)
{
    set_flash(!empty($result['ok']) ? 'success' : 'danger', isset($result['message']) ? $result['message'] : 'Aksi selesai.');
}

function handle_post($route)
{
    if (!verify_csrf_token(isset($_POST['_csrf']) ? $_POST['_csrf'] : '')) {
        set_flash('danger', 'Sesi form tidak valid atau sudah kedaluwarsa. Silakan ulangi aksi.');
        redirect_back('home');
    }

    if ($route === 'login') {
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
        $result = authenticate_user($email, $password);
        if (!empty($result['ok'])) {
            $_SESSION['user'] = $result['user'];
            set_flash('success', 'Login berhasil. Selamat datang, ' . $result['user']['name'] . '.');
            redirect_to($result['user']['role'] === 'admin' ? 'admin/dashboard' : 'home');
        }
        set_flash('danger', $result['message']);
        redirect_to('login');
    }

    if ($route === 'register') {
        $result = register_customer($_POST);
        if (!empty($result['ok'])) {
            $_SESSION['user'] = $result['user'];
            set_flash('success', 'Registrasi berhasil. Anda sudah login sebagai pelanggan.');
            redirect_to('home');
        }
        set_flash('danger', $result['message']);
        redirect_to('register');
    }

    if ($route === 'logout') {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        set_flash('success', 'Anda berhasil logout.');
        redirect_to('home');
    }

    if ($route === 'profile/save' || $route === 'admin/profile/save') {
        require_login();
        $result = save_profile($_POST);
        flash_result($result);
        redirect_to($route === 'admin/profile/save' ? 'admin/profile' : 'profile');
    }

    if ($route === 'reservasi/store') {
        require_login();
        $result = create_reservation($_POST);
        flash_result($result);
        if (!empty($result['ok'])) {
            $resId = (int)$result['id'];
            $payId = isset($result['payment_id']) ? (int)$result['payment_id'] : 0;
            if ($payId > 0) {
                redirect_to('payment?res_id=' . $resId);
            } else {
                redirect_to('riwayat');
            }
        }
        redirect_to('reservasi');
    }

    if ($route === 'payment_reservasi/upload') {
        require_login();
        $resId = (int)(isset($_POST['res_id']) ? $_POST['res_id'] : 0);
        $payment = find_payment_by_reservasi($resId);
        if (!$payment) {
            set_flash('danger', 'Payment reservasi tidak ditemukan.');
            redirect_to('riwayat');
        }
        list($filePath, $uploadError) = save_uploaded_file('bukti_tf', 'uploads/payments', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2 * 1024 * 1024);
        if ($uploadError) {
            set_flash('danger', $uploadError);
            redirect_to('payment?res_id=' . $resId);
        }
        if (!$filePath) {
            set_flash('danger', 'Pilih file bukti transfer.');
            redirect_to('payment?res_id=' . $resId);
        }

        $pdo = db();
        $old = db_one('SELECT bukti_tf FROM payment WHERE id_payment = ? LIMIT 1', [$payment['id']]);
        if ($old && !empty($old['bukti_tf'])) {
            delete_uploaded_file_if_local($old['bukti_tf']);
        }
        db_exec("UPDATE payment SET bukti_tf = ?, status_payment = 'pending', tanggal_upload = NOW() WHERE id_payment = ?", [$filePath, $payment['id']]);
        set_flash('success', 'Bukti pembayaran reservasi berhasil diunggah. Admin akan verifikasi.');
        redirect_to('riwayat');
    }

    if ($route === 'pesan/store') {
        require_login();
        $result = create_order($_POST);
        flash_result($result);
        if (!empty($result['ok'])) {
            redirect_to('payment?order_id=' . (int)$result['id']);
        }
        redirect_to('pesan');
    }

    if ($route === 'payment/upload') {
        require_login();
        $orderId = (int)(isset($_POST['order_id']) ? $_POST['order_id'] : 0);
        $result = upload_payment_receipt($orderId);
        flash_result($result);
        redirect_to(!empty($result['ok']) ? 'riwayat' : 'payment?order_id=' . $orderId);
    }

    if ($route === 'admin/menu/save') {
        require_admin();
        $result = save_menu($_POST);
        flash_result($result);
        redirect_to('admin/menu');
    }

    if ($route === 'admin/menu/delete') {
        require_admin();
        $result = delete_menu(isset($_POST['id']) ? $_POST['id'] : 0);
        flash_result($result);
        redirect_to('admin/menu');
    }

    if ($route === 'admin/meja/save') {
        require_admin();
        $result = save_table_data($_POST);
        flash_result($result);
        redirect_to('admin/meja');
    }

    if ($route === 'admin/reservasi/status') {
        require_admin();
        $result = update_reservation_status(isset($_POST['id']) ? $_POST['id'] : 0, isset($_POST['status']) ? $_POST['status'] : 'pending');
        flash_result($result);
        redirect_to('admin/reservasi');
    }

    if ($route === 'admin/reservasi/delete') {
        require_admin();
        $result = delete_reservation(isset($_POST['id']) ? $_POST['id'] : 0);
        flash_result($result);
        redirect_to('admin/reservasi');
    }

    if ($route === 'admin/pesanan/status') {
        require_admin();
        $result = update_order_status(isset($_POST['id']) ? $_POST['id'] : 0, isset($_POST['status']) ? $_POST['status'] : 'pending');
        flash_result($result);
        redirect_to('admin/pesanan');
    }

    if ($route === 'admin/payment_reservasi/verify') {
        require_admin();
        $result = verify_payment(isset($_POST['id']) ? $_POST['id'] : 0, isset($_POST['status']) ? $_POST['status'] : 'verified', isset($_POST['catatan_admin']) ? $_POST['catatan_admin'] : '');
        flash_result($result);
        redirect_to('admin/payment');
    }

    if ($route === 'admin/payment/verify') {
        require_admin();
        $result = verify_payment(isset($_POST['id']) ? $_POST['id'] : 0, isset($_POST['status']) ? $_POST['status'] : 'verified', isset($_POST['catatan_admin']) ? $_POST['catatan_admin'] : '');
        flash_result($result);
        redirect_to('admin/payment');
    }

    set_flash('danger', 'Aksi tidak dikenal.');
    redirect_to('home');
}

if ($method === 'POST') {
    handle_post($route);
}

switch ($route) {
    case 'home':
    case '':
        render('customer/home', ['current_page' => 'home', 'menus' => get_featured_menus(6), 'testimonials' => default_testimonials()]);
        break;
    case 'menu':
        render('customer/menu', ['current_page' => 'menu', 'menus' => get_menus()]);
        break;
    case 'reservasi':
        render('customer/reservasi', ['current_page' => 'reservasi', 'tables' => get_tables()]);
        break;
    case 'pesan':
        render('customer/pesan', ['current_page' => 'pesan', 'menus' => get_menus(), 'tables' => get_tables(), 'reservations' => get_reservations(false)]);
        break;
    case 'riwayat':
        require_login();
        render('customer/riwayat', ['current_page' => 'riwayat', 'reservations' => get_reservations(false), 'orders' => get_orders(false), 'payments' => get_payments(false)]);
        break;
    case 'payment':
        require_login();
        $orderId = (int)(isset($_GET['order_id']) ? $_GET['order_id'] : 0);
        $resId = (int)(isset($_GET['res_id']) ? $_GET['res_id'] : 0);
        if ($resId > 0) {
            $payment = find_payment_by_reservasi($resId);
            $reservation = find_reservation($resId, false);
            render('customer/payment-reservasi', ['current_page' => 'payment', 'payment' => $payment, 'reservation' => $reservation]);
        } else {
            render('customer/payment', ['current_page' => 'payment', 'order' => find_order($orderId), 'payment' => find_payment_by_order($orderId)]);
        }
        break;
    case 'profile':
        require_login();
        render('customer/profile', ['current_page' => 'profile']);
        break;
    case 'login':
        render_auth('auth/login', ['page_title' => 'Login']);
        break;
    case 'register':
        render_auth('auth/register', ['page_title' => 'Daftar']);
        break;
    case 'logout':
        unset($_SESSION['user']);
        session_regenerate_id(true);
        set_flash('success', 'Anda berhasil logout.');
        redirect_to('home');
        break;
    case 'admin':
    case 'admin/dashboard':
        render_admin('admin/dashboard', ['current_admin_page' => 'admin/dashboard', 'stats' => dashboard_stats(), 'payments' => get_payments(true), 'reservations' => get_reservations(true)]);
        break;
    case 'admin/menu':
        render_admin('admin/menu', ['current_admin_page' => 'admin/menu', 'menus' => get_menus()]);
        break;
    case 'admin/meja':
        render_admin('admin/meja', ['current_admin_page' => 'admin/meja', 'tables' => get_tables()]);
        break;
    case 'admin/reservasi':
        render_admin('admin/reservasi', ['current_admin_page' => 'admin/reservasi', 'reservations' => get_reservations(true)]);
        break;
    case 'admin/reservasi/detail':
        $reservationId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
        render_admin('admin/reservasi_detail', ['current_admin_page' => 'admin/reservasi', 'detail' => get_reservation_detail($reservationId)]);
        break;
    case 'admin/pesanan':
        render_admin('admin/pesanan', ['current_admin_page' => 'admin/pesanan', 'orders' => get_orders(true)]);
        break;
    case 'admin/payment':
        render_admin('admin/payment', ['current_admin_page' => 'admin/payment', 'payments' => get_payments(true)]);
        break;
    case 'admin/laporan':
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
        $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
        render_admin('admin/laporan', ['current_admin_page' => 'admin/laporan', 'report' => sales_report($start, $end, $jenis)]);
        break;
    case 'admin/profile':
        render_admin('admin/profile', ['current_admin_page' => 'admin/profile']);
        break;
    default:
        http_response_code(404);
        render('customer/404', ['current_page' => '404', 'route' => $route, 'hide_cta' => true]);
}