<?php
/*
 * Data/service layer sederhana untuk PHP Native.
 * Urutan perbaikan yang sudah diterapkan:
 * 1) integrasi database MySQL dengan fallback session demo,
 * 2) login/register asli memakai password_hash/password_verify,
 * 3) CRUD menu/meja ke database,
 * 4) reservasi, pesanan, detail pesanan, payment ke database,
 * 5) pengurangan stok otomatis,
 * 6) laporan penjualan.
 *
 * FIX v2:
 * - Durasi reservasi default 2 jam (120 menit)
 * - Conflict check memakai time-overlap, bukan exact match jam
 * - Auto-expire reservasi pending/confirmed yang sudah lewat waktu
 */

// ============================================================
// DURASI DEFAULT RESERVASI (menit)
// ============================================================
define('RESERVATION_DURATION_MINUTES', 120);

function db_config()
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../../config/database.php';
    }
    return $config;
}

function db()
{
    static $pdo = null;
    static $failed = false;
    if ($failed) {
        return null;
    }
    if ($pdo !== null) {
        return $pdo;
    }

    if (!class_exists('PDO')) {
        $failed = true;
        return null;
    }

    $config = db_config();
    $charset = isset($config['charset']) ? $config['charset'] : 'utf8mb4';
    $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'] . ';charset=' . $charset;

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        $failed = true;
        error_log('[DB ERROR] ' . $e->getMessage());
        die('[DB ERROR] ' . $e->getMessage());
    }
}

function db_has_required_tables()
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    $pdo = db();
    if (!$pdo) {
        $ready = false;
        return false;
    }
    try {
        $tables = ['admin', 'pelanggan', 'menu', 'stok_menu', 'meja', 'reservasi', 'pesanan', 'detail_pesanan', 'payment', 'jenis_pesanan'];
        $config = db_config();
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
            $stmt->execute([$config['dbname'], $table]);
            if (!(int)$stmt->fetchColumn()) {
                $ready = false;
                return false;
            }
        }
        $ready = true;
        return true;
    } catch (Throwable $e) {
        $ready = false;
        return false;
    }
}

function using_database()
{
    return db_has_required_tables();
}

function db_one($sql, $params = [])
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function db_all($sql, $params = [])
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_exec($sql, $params = [])
{
    $pdo = db();
    if (!$pdo) {
        return false;
    }
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function starts_with_text($text, $prefix)
{
    return substr((string)$text, 0, strlen($prefix)) === $prefix;
}

function display_media_url($path, $folder = 'uploads/menu', $fallback = 'uploads/menu/default.svg')
{
    $path = trim((string)$path);
    if ($path === '') {
        return asset($fallback);
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    if (starts_with_text($path, 'uploads/')) {
        return asset($path);
    }
    return asset(trim($folder, '/') . '/' . ltrim($path, '/'));
}

function delete_uploaded_file_if_local($storedPath)
{
    $storedPath = trim((string)$storedPath);
    if ($storedPath === '' || preg_match('#^https?://#i', $storedPath)) {
        return;
    }
    if (!starts_with_text($storedPath, 'uploads/')) {
        return;
    }
    if (basename($storedPath) === 'default.svg' || basename($storedPath) === 'default.jpg') {
        return;
    }
    $file = __DIR__ . '/../../public/' . $storedPath;
    if (is_file($file)) {
        @unlink($file);
    }
}

function save_uploaded_file($field, $relativeDir, $allowedExt, $maxSizeBytes = 2097152)
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || !isset($_FILES[$field]['error'])) {
        return [null, null];
    }
    if ($_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload file gagal. Pastikan file dipilih dengan benar.'];
    }
    if ((int)$_FILES[$field]['size'] <= 0) {
        return [null, 'File kosong atau tidak valid.'];
    }
    if ((int)$_FILES[$field]['size'] > $maxSizeBytes) {
        return [null, 'Ukuran file maksimal ' . round($maxSizeBytes / 1024 / 1024, 1) . ' MB.'];
    }

    $tmp = isset($_FILES[$field]['tmp_name']) ? $_FILES[$field]['tmp_name'] : '';
    if (!$tmp || !is_uploaded_file($tmp)) {
        return [null, 'File upload tidak valid.'];
    }

    $original = isset($_FILES[$field]['name']) ? $_FILES[$field]['name'] : 'file';
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowedExt = array_map('strtolower', $allowedExt);
    if (!in_array($ext, $allowedExt, true)) {
        return [null, 'Format file tidak diizinkan. Gunakan: ' . implode(', ', $allowedExt) . '.'];
    }

    $mimeMap = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf', 'application/x-pdf'],
    ];
    $detectedMime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = (string)$finfo->file($tmp);
    } elseif (function_exists('mime_content_type')) {
        $detectedMime = (string)mime_content_type($tmp);
    }
    if ($detectedMime !== '' && isset($mimeMap[$ext]) && !in_array($detectedMime, $mimeMap[$ext], true)) {
        return [null, 'Tipe file tidak sesuai dengan ekstensi file.'];
    }
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && @getimagesize($tmp) === false) {
        return [null, 'File gambar tidak valid atau rusak.'];
    }

    $dir = __DIR__ . '/../../public/' . trim($relativeDir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $base = pathinfo($original, PATHINFO_FILENAME);
    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', $base);
    $safeBase = substr(trim($safeBase, '-'), 0, 60);
    if ($safeBase === '') {
        $safeBase = 'file';
    }
    $filename = date('YmdHis') . '-' . substr(sha1(uniqid('', true)), 0, 10) . '-' . $safeBase . '.' . $ext;
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $target)) {
        return [null, 'File tidak dapat disimpan ke folder uploads.'];
    }
    @chmod($target, 0644);
    return [trim($relativeDir, '/') . '/' . $filename, null];
}

function default_menus()
{
    return [
        ['id' => 1, 'nama' => 'Nasi Goreng Spesial', 'kategori' => 'Makanan', 'harga' => 35000, 'deskripsi' => 'Nasi goreng dengan telur, ayam suwir, dan bumbu rempah pilihan.', 'foto_raw' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80', 'stok' => 24, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 2, 'nama' => 'Ayam Bakar Madu', 'kategori' => 'Makanan', 'harga' => 42000, 'deskripsi' => 'Ayam bakar dengan marinasi madu dan kecap, disajikan dengan lalapan.', 'foto_raw' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&q=80', 'stok' => 18, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 3, 'nama' => 'Mi Goreng Seafood', 'kategori' => 'Makanan', 'harga' => 38000, 'deskripsi' => 'Mi goreng dengan udang, cumi, dan sayuran segar.', 'foto_raw' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600&q=80', 'stok' => 12, 'minimum_stok' => 5, 'unggulan' => 0],
        ['id' => 4, 'nama' => 'Soto Ayam Lamongan', 'kategori' => 'Makanan', 'harga' => 28000, 'deskripsi' => 'Soto ayam khas Lamongan dengan kuah bening gurih dan koya.', 'foto_raw' => 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=600&q=80', 'stok' => 0, 'minimum_stok' => 5, 'unggulan' => 0],
        ['id' => 5, 'nama' => 'Es Kopi Susu', 'kategori' => 'Minuman', 'harga' => 22000, 'deskripsi' => 'Kopi susu gula aren dengan espresso premium dan susu segar.', 'foto_raw' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&q=80', 'stok' => 30, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 6, 'nama' => 'Matcha Latte', 'kategori' => 'Minuman', 'harga' => 28000, 'deskripsi' => 'Matcha premium Jepang dengan susu oat, disajikan dingin atau panas.', 'foto_raw' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=600&q=80', 'stok' => 16, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 7, 'nama' => 'Jus Alpukat', 'kategori' => 'Minuman', 'harga' => 20000, 'deskripsi' => 'Jus alpukat segar dengan susu kental manis dan topping coklat.', 'foto_raw' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=600&q=80', 'stok' => 10, 'minimum_stok' => 5, 'unggulan' => 0],
        ['id' => 8, 'nama' => 'Teh Tarik', 'kategori' => 'Minuman', 'harga' => 15000, 'deskripsi' => 'Teh tarik khas Malaysia dengan aroma rempah lembut.', 'foto_raw' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&q=80', 'stok' => 28, 'minimum_stok' => 5, 'unggulan' => 0],
        ['id' => 9, 'nama' => 'Tiramisu Slice', 'kategori' => 'Dessert', 'harga' => 32000, 'deskripsi' => 'Tiramisu klasik Italia dengan mascarpone premium dan espresso.', 'foto_raw' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&q=80', 'stok' => 8, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 10, 'nama' => 'Lava Cake Coklat', 'kategori' => 'Dessert', 'harga' => 35000, 'deskripsi' => 'Molten chocolate lava cake dengan isian coklat lumer.', 'foto_raw' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=600&q=80', 'stok' => 6, 'minimum_stok' => 5, 'unggulan' => 1],
        ['id' => 11, 'nama' => 'Pancake Stack', 'kategori' => 'Dessert', 'harga' => 30000, 'deskripsi' => 'Tumpukan pancake fluffy dengan maple syrup, butter, dan buah segar.', 'foto_raw' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?w=600&q=80', 'stok' => 14, 'minimum_stok' => 5, 'unggulan' => 0],
        ['id' => 12, 'nama' => 'Paket Hemat Duo', 'kategori' => 'Makanan', 'harga' => 55000, 'deskripsi' => 'Nasi goreng + Es kopi susu, hemat 10% dari harga normal.', 'foto_raw' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80', 'foto' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80', 'stok' => 5, 'minimum_stok' => 5, 'unggulan' => 0],
    ];
}

function default_tables()
{
    return [
        ['id' => 1, 'no_meja' => 'A1', 'kapasitas' => 2, 'status' => 'tersedia'],
        ['id' => 2, 'no_meja' => 'A2', 'kapasitas' => 2, 'status' => 'terisi'],
        ['id' => 3, 'no_meja' => 'A3', 'kapasitas' => 4, 'status' => 'tersedia'],
        ['id' => 4, 'no_meja' => 'A4', 'kapasitas' => 4, 'status' => 'tersedia'],
        ['id' => 5, 'no_meja' => 'B1', 'kapasitas' => 6, 'status' => 'tersedia'],
        ['id' => 6, 'no_meja' => 'B2', 'kapasitas' => 6, 'status' => 'terisi'],
        ['id' => 7, 'no_meja' => 'B3', 'kapasitas' => 8, 'status' => 'tersedia'],
        ['id' => 8, 'no_meja' => 'B4', 'kapasitas' => 8, 'status' => 'tersedia'],
        ['id' => 9, 'no_meja' => 'C1', 'kapasitas' => 10, 'status' => 'tersedia'],
        ['id' => 10, 'no_meja' => 'C2', 'kapasitas' => 10, 'status' => 'tersedia'],
        ['id' => 11, 'no_meja' => 'VIP1', 'kapasitas' => 6, 'status' => 'tersedia'],
        ['id' => 12, 'no_meja' => 'VIP2', 'kapasitas' => 8, 'status' => 'terisi'],
    ];
}

function default_testimonials()
{
    return [
        ['nama' => 'Budi Santoso', 'peran' => 'Pelanggan Setia', 'rating' => 5.0, 'avatar' => 'https://i.pravatar.cc/120?img=11', 'komentar' => 'Sistem reservasinya sangat mudah digunakan! Saya bisa pesan meja untuk ulang tahun istri dengan mudah. Makanannya juga luar biasa enak, terutama Ayam Bakar Madunya. Pasti akan kembali lagi!'],
        ['nama' => 'Siti Rahayu', 'peran' => 'Food Blogger', 'rating' => 5.0, 'avatar' => 'https://i.pravatar.cc/120?img=5', 'komentar' => 'Cafe Nusantara selalu jadi pilihan utama saya ketika ingin menikmati hidangan Indonesia berkualitas. Suasananya nyaman dan pelayanannya ramah.'],
        ['nama' => 'Andi Pratama', 'peran' => 'Pelanggan Baru', 'rating' => 4.9, 'avatar' => 'https://i.pravatar.cc/120?img=15', 'komentar' => 'Baru pertama kali ke sini tapi langsung jatuh cinta. Matcha Lattenya enak dan tempatnya sangat nyaman untuk keluarga.'],
    ];
}

function init_demo_state()
{
    if (!isset($_SESSION['menus'])) {
        $_SESSION['menus'] = default_menus();
    }
    if (!isset($_SESSION['tables'])) {
        $_SESSION['tables'] = default_tables();
    }
    if (!isset($_SESSION['registered_users'])) {
        $_SESSION['registered_users'] = [];
    }
    if (!isset($_SESSION['reservations'])) {
        $_SESSION['reservations'] = [
            ['id' => 1, 'kode' => 'RSV-1001', 'nama' => 'Budi Santoso', 'email' => 'budi@email.com', 'tanggal' => date('Y-m-d'), 'jam' => '19:00', 'jumlah_orang' => 4, 'no_meja' => 'A3', 'catatan' => 'Dekat jendela', 'status' => 'confirmed', 'created_at' => date('Y-m-d H:i:s')],
        ];
    }
    if (!isset($_SESSION['orders'])) {
        $_SESSION['orders'] = [
            ['id' => 1, 'kode' => 'ORD-1001', 'reservation_id' => 1, 'nama' => 'Budi Santoso', 'email' => 'budi@email.com', 'jenis' => 'reservasi', 'no_meja' => 'A3', 'items' => [
                ['id' => 1, 'nama' => 'Nasi Goreng Spesial', 'harga' => 35000, 'qty' => 2],
                ['id' => 6, 'nama' => 'Matcha Latte', 'harga' => 28000, 'qty' => 2],
            ], 'total' => 126000, 'deposit' => 63000, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
        ];
    }
    if (!isset($_SESSION['payments'])) {
        $_SESSION['payments'] = [
            ['id' => 1, 'order_id' => 1, 'kode' => 'PAY-1001', 'nama' => 'Budi Santoso', 'total' => 63000, 'bukti_tf' => 'Belum ada file', 'bukti_url' => '', 'status' => 'pending', 'catatan_admin' => '', 'tanggal_upload' => null, 'tanggal_verifikasi' => null],
        ];
    }
}

// ============================================================
// AUTO-EXPIRE RESERVASI MASA LALU
// FIX #22: Throttle — query UPDATE hanya dijalankan max 1x per menit
// ============================================================
function auto_expire_past_reservations()
{
    if (!using_database()) {
        return;
    }
    // Throttle: skip jika sudah dijalankan dalam 60 detik terakhir
    $lastRun = isset($_SESSION['_expire_ran']) ? (int)$_SESSION['_expire_ran'] : 0;
    if ((time() - $lastRun) < 60) {
        return;
    }
    $_SESSION['_expire_ran'] = time();

    // Reservasi pending/confirmed yang tanggalnya sudah lewat,
    // atau hari ini tapi jam selesai (jam + durasi) sudah terlampaui
    db_exec(
        "UPDATE reservasi
         SET status_reservasi = 'cancelled'
         WHERE status_reservasi IN ('pending', 'confirmed')
           AND (
               tanggal < CURDATE()
               OR (
                   tanggal = CURDATE()
                   AND ADDTIME(jam, SEC_TO_TIME(? * 60)) <= CURTIME()
               )
           )",
        [RESERVATION_DURATION_MINUTES]
    );
}

// ============================================================
// HELPER: hitung jam selesai reservasi (string 'HH:MM:SS')
// ============================================================
function reservation_end_time($jamStart)
{
    // Normalkan ke HH:MM:SS
    $time = strlen((string)$jamStart) === 5 ? $jamStart . ':00' : $jamStart;
    return date('H:i:s', strtotime($time) + RESERVATION_DURATION_MINUTES * 60);
}

function map_menu_row($row)
{
    $foto = isset($row['foto']) ? $row['foto'] : '';
    return [
        'id' => (int)$row['id'],
        'nama' => $row['nama'],
        'kategori' => $row['kategori'],
        'harga' => (float)$row['harga'],
        'deskripsi' => isset($row['deskripsi']) ? $row['deskripsi'] : '',
        'foto_raw' => $foto,
        'foto' => display_media_url($foto, 'uploads/menu'),
        'stok' => (int)(isset($row['stok']) ? $row['stok'] : 0),
        'minimum_stok' => (int)(isset($row['minimum_stok']) ? $row['minimum_stok'] : 5),
        'unggulan' => (int)(isset($row['unggulan']) ? $row['unggulan'] : 0),
    ];
}

function get_menus()
{
    if (using_database()) {
        $rows = db_all('SELECT m.id_menu AS id, m.nama_menu AS nama, m.kategori, m.harga, m.deskripsi, m.foto, m.is_unggulan AS unggulan, COALESCE(s.jumlah_stok, 0) AS stok, COALESCE(s.minimum_stok, 5) AS minimum_stok FROM menu m LEFT JOIN stok_menu s ON s.id_menu = m.id_menu ORDER BY m.id_menu ASC');
        return array_map('map_menu_row', $rows);
    }
    return $_SESSION['menus'];
}

function get_featured_menus($limit = 6)
{
    $menus = array_values(array_filter(get_menus(), function ($m) {
        return !empty($m['unggulan']);
    }));
    return array_slice($menus, 0, $limit);
}

function get_tables()
{
    if (using_database()) {
        $rows = db_all('SELECT id_meja AS id, no_meja, kapasitas, status FROM meja ORDER BY id_meja ASC');
        return array_map(function ($row) {
            return ['id' => (int)$row['id'], 'no_meja' => $row['no_meja'], 'kapasitas' => (int)$row['kapasitas'], 'status' => $row['status']];
        }, $rows);
    }
    return $_SESSION['tables'];
}

// ============================================================
// FIX: get_tables_with_availability — pakai TIME OVERLAP
// Meja dianggap terisi jika ada reservasi active yang waktunya
// tumpang tindih dengan slot baru (bukan exact-match jam).
//
// Overlap condition:
//   existing_start < new_end  AND  existing_end > new_start
// ============================================================
function get_tables_with_availability($tanggal, $jam)
{
    $allTables = get_tables();
    if (!using_database()) {
        return $allTables;
    }

    $newStart = strlen((string)$jam) === 5 ? $jam . ':00' : $jam;
    $newEnd   = reservation_end_time($newStart);

    $reserved = db_all(
        "SELECT id_meja
         FROM reservasi
         WHERE tanggal = ?
           AND status_reservasi IN ('pending', 'confirmed')
           AND jam                                  < ?
           AND ADDTIME(jam, SEC_TO_TIME(? * 60))    > ?",
        [$tanggal, $newEnd, RESERVATION_DURATION_MINUTES, $newStart]
    );

    $reservedIds = array_map(fn($r) => (int)$r['id_meja'], $reserved);

    return array_map(function ($t) use ($reservedIds) {
        $t['available'] = !in_array((int)$t['id'], $reservedIds, true);
        return $t;
    }, $allTables);
}

function should_filter_customer($all)
{
    $user = current_user();
    return !$all && $user && isset($user['role']) && $user['role'] === 'customer';
}

function get_reservations($all = false)
{
    if (!$all && !current_user()) {
        return [];
    }
    if (using_database()) {
        $params = [];
        $where = '';
        $user = current_user();
        if (should_filter_customer($all)) {
            $where = 'WHERE p.id_pelanggan = ?';
            $params[] = (int)$user['id'];
        }
        $sql = "SELECT r.id_reservasi AS id, CONCAT('RSV-', LPAD(1000 + r.id_reservasi, 4, '0')) AS kode, r.nama_tamu AS nama, p.email, r.tanggal, TIME_FORMAT(r.jam, '%H:%i') AS jam, r.jumlah_orang, m.no_meja, r.catatan, r.status_reservasi AS status, r.created_at, r.biaya_booking FROM reservasi r JOIN pelanggan p ON p.id_pelanggan = r.id_pelanggan JOIN meja m ON m.id_meja = r.id_meja $where ORDER BY r.created_at DESC, r.id_reservasi DESC";
        return db_all($sql, $params);
    }
    $rows = $_SESSION['reservations'];
    if (should_filter_customer($all)) {
        $user = current_user();
        $rows = array_values(array_filter($rows, function ($r) use ($user) {
            return strtolower($r['email']) === strtolower($user['email']);
        }));
    }
    return $rows;
}

function get_order_items($orderId)
{
    if (using_database()) {
        $rows = db_all('SELECT d.id_menu AS id, m.nama_menu AS nama, d.harga_satuan AS harga, d.jumlah AS qty, d.subtotal FROM detail_pesanan d JOIN menu m ON m.id_menu = d.id_menu WHERE d.id_pesanan = ? ORDER BY d.id_detail ASC', [(int)$orderId]);
        return array_map(function ($row) {
            return ['id' => (int)$row['id'], 'nama' => $row['nama'], 'harga' => (float)$row['harga'], 'qty' => (int)$row['qty'], 'subtotal' => (float)$row['subtotal']];
        }, $rows);
    }
    return [];
}

function map_order_row($row, $allItems = [])
{
    $orderId = (int)$row['id'];
    // Jika $allItems sudah disediakan (batch load), gunakan itu. Fallback ke per-query.
    $items = isset($allItems[$orderId]) ? $allItems[$orderId] : get_order_items($orderId);
    return [
        'id' => $orderId,
        'kode' => $row['kode'],
        'reservation_id' => isset($row['reservation_id']) ? (int)$row['reservation_id'] : null,
        'nama' => $row['nama'],
        'email' => $row['email'],
        'jenis' => $row['jenis'],
        'no_meja' => isset($row['no_meja']) ? $row['no_meja'] : '',
        'items' => $items,
        'total' => (float)$row['total'],
        'deposit' => (float)$row['deposit'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
    ];
}

// FIX #1: Eliminasi N+1 Query — load semua items dalam SATU query, lalu group di PHP
function get_orders($all = false)
{
    if (!$all && !current_user()) {
        return [];
    }
    if (using_database()) {
        $params = [];
        $where = '';
        $user = current_user();
        if (should_filter_customer($all)) {
            $where = 'WHERE p.id_pelanggan = ?';
            $params[] = (int)$user['id'];
        }
        $sql = "SELECT ps.id_pesanan AS id, CONCAT('ORD-', LPAD(1000 + ps.id_pesanan, 4, '0')) AS kode, ps.id_reservasi AS reservation_id, p.nama, p.email, jp.nama_pesanan AS jenis, COALESCE(m.no_meja, '-') AS no_meja, ps.total_harga AS total, ps.deposit, ps.status_pesanan AS status, ps.created_at FROM pesanan ps JOIN pelanggan p ON p.id_pelanggan = ps.id_pelanggan JOIN jenis_pesanan jp ON jp.id_jenis_pesanan = ps.id_jenis_pesanan LEFT JOIN meja m ON m.id_meja = ps.id_meja $where ORDER BY ps.created_at DESC, ps.id_pesanan DESC";
        $orderRows = db_all($sql, $params);
        if (empty($orderRows)) {
            return [];
        }
        // Batch load semua items untuk semua pesanan sekaligus (1 query, bukan N query)
        $orderIds = array_map(fn($r) => (int)$r['id'], $orderRows);
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $itemRows = db_all(
            "SELECT d.id_pesanan, d.id_menu AS id, m.nama_menu AS nama,
                    d.harga_satuan AS harga, d.jumlah AS qty, d.subtotal
             FROM detail_pesanan d
             JOIN menu m ON m.id_menu = d.id_menu
             WHERE d.id_pesanan IN ($placeholders)
             ORDER BY d.id_pesanan ASC, d.id_detail ASC",
            $orderIds
        );
        // Group items by order_id
        $allItems = [];
        foreach ($itemRows as $item) {
            $pid = (int)$item['id_pesanan'];
            $allItems[$pid][] = [
                'id'       => (int)$item['id'],
                'nama'     => $item['nama'],
                'harga'    => (float)$item['harga'],
                'qty'      => (int)$item['qty'],
                'subtotal' => (float)$item['subtotal'],
            ];
        }
        return array_map(fn($row) => map_order_row($row, $allItems), $orderRows);
    }
    $rows = $_SESSION['orders'];
    if (should_filter_customer($all)) {
        $user = current_user();
        $rows = array_values(array_filter($rows, function ($o) use ($user) {
            return strtolower($o['email']) === strtolower($user['email']);
        }));
    }
    return $rows;
}

function map_payment_row($row)
{
    $bukti = isset($row['bukti_tf']) ? $row['bukti_tf'] : '';
    return [
        'id' => (int)$row['id'],
        'order_id' => isset($row['order_id']) ? (int)$row['order_id'] : null,
        'reservation_id' => isset($row['reservation_id']) ? (int)$row['reservation_id'] : null,
        'tipe' => isset($row['tipe']) ? $row['tipe'] : 'pesanan',
        'kode' => $row['kode'],
        'nama' => $row['nama'],
        'total' => (float)$row['total'],
        'bukti_tf' => $bukti ?: 'Belum ada file',
        'bukti_url' => ($bukti && starts_with_text($bukti, 'uploads/')) ? asset($bukti) : '',
        'status' => $row['status'],
        'catatan_admin' => isset($row['catatan_admin']) ? $row['catatan_admin'] : '',
        'tanggal_upload' => isset($row['tanggal_upload']) ? $row['tanggal_upload'] : null,
        'tanggal_verifikasi' => isset($row['tanggal_verifikasi']) ? $row['tanggal_verifikasi'] : null,
    ];
}

function get_payments($all = false)
{
    if (!$all && !current_user()) {
        return [];
    }
    if (using_database()) {
        $params = [];
        $whereClause = '';
        $user = current_user();
        if (should_filter_customer($all)) {
            $whereClause = 'WHERE pl.id_pelanggan = ?';
            $params[] = (int)$user['id'];
        }
        // LEFT JOIN agar payment tipe 'booking' (id_pesanan=NULL) tetap ikut tampil.
        // Nama pelanggan diambil via pesanan, atau fallback via reservasi jika pesanan NULL.
        // FIX #3: Ganti OR JOIN dengan COALESCE pada subquery — hindari duplikat baris
        $sql = "SELECT pay.*,
                       pay.id_payment AS id,
                       pay.id_pesanan AS order_id,
                       pay.id_reservasi AS reservation_id,
                       pay.status_payment AS status,
                       pay.jumlah AS total,
                       CONCAT('PAY-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode,
                       COALESCE(pl_ps.nama, pl_rsv.nama, 'Unknown') AS nama
                FROM payment pay
                LEFT JOIN pesanan ps       ON ps.id_pesanan      = pay.id_pesanan
                LEFT JOIN pelanggan pl_ps  ON pl_ps.id_pelanggan = ps.id_pelanggan
                LEFT JOIN reservasi rsv    ON rsv.id_reservasi   = pay.id_reservasi
                LEFT JOIN pelanggan pl_rsv ON pl_rsv.id_pelanggan= rsv.id_pelanggan
                LEFT JOIN pelanggan pl     ON pl.id_pelanggan    = COALESCE(ps.id_pelanggan, rsv.id_pelanggan)
                $whereClause
                ORDER BY pay.created_at DESC, pay.id_payment DESC";
        return array_map('map_payment_row', db_all($sql, $params));
    }
    $rows = $_SESSION['payments'];
    if (should_filter_customer($all)) {
        $orders = get_orders(false);
        $orderIds = array_map(function ($o) { return (int)$o['id']; }, $orders);
        $rows = array_values(array_filter($rows, function ($p) use ($orderIds) {
            return in_array((int)$p['order_id'], $orderIds, true);
        }));
    }
    return $rows;
}

function next_id($items)
{
    $max = 0;
    foreach ($items as $item) {
        if (isset($item['id']) && $item['id'] > $max) {
            $max = $item['id'];
        }
    }
    return $max + 1;
}

function find_menu($id)
{
    foreach (get_menus() as $menu) {
        if ((int)$menu['id'] === (int)$id) {
            return $menu;
        }
    }
    return null;
}

function find_order($id)
{
    $all = is_admin();
    foreach (get_orders($all) as $order) {
        if ((int)$order['id'] === (int)$id) {
            return $order;
        }
    }
    return null;
}

function find_payment_by_order($orderId)
{
    foreach (get_payments(is_admin()) as $payment) {
        if ((int)$payment['order_id'] === (int)$orderId) {
            return $payment;
        }
    }
    return null;
}

function find_payment_by_reservasi($resId)
{
    foreach (get_payments(is_admin()) as $payment) {
        if ((int)$payment['reservation_id'] === (int)$resId) {
            return $payment;
        }
    }
    return null;
}

// FIX #3: $userId digunakan secara eksplisit dengan query langsung ke DB
function get_unpaid_payments_by_user($userId)
{
    $userId = (int)$userId;

    if (using_database()) {
        // Query langsung ke DB — lebih cepat dan tidak memuat semua payments
        $rows = db_all(
            "SELECT pay.*,
                    pay.id_payment AS id,
                    pay.id_pesanan AS order_id,
                    pay.id_reservasi AS reservation_id,
                    pay.status_payment AS status,
                    pay.jumlah AS total,
                    CONCAT('PAY-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode,
                    COALESCE(pl_ps.nama, pl_rsv.nama) AS nama
             FROM payment pay
             LEFT JOIN pesanan ps      ON ps.id_pesanan     = pay.id_pesanan
             LEFT JOIN pelanggan pl_ps ON pl_ps.id_pelanggan= ps.id_pelanggan
             LEFT JOIN reservasi rsv   ON rsv.id_reservasi  = pay.id_reservasi
             LEFT JOIN pelanggan pl_rsv ON pl_rsv.id_pelanggan = rsv.id_pelanggan
             WHERE pay.status_payment = 'pending'
               AND pay.bukti_tf IS NULL
               AND COALESCE(ps.id_pelanggan, rsv.id_pelanggan) = ?",
            [$userId]
        );
        return array_map('map_payment_row', $rows);
    }
    // Session fallback
    $allPayments = get_payments(false);
    return array_values(array_filter($allPayments, function ($p) {
        return $p['status'] === 'pending' && !payment_has_receipt($p);
    }));
}

function find_reservation($id, $all = true)
{
    foreach (get_reservations($all) as $reservation) {
        if ((int)$reservation['id'] === (int)$id) {
            return $reservation;
        }
    }
    return null;
}

function get_reservation_detail($id)
{
    $reservation = find_reservation($id, true);
    if (!$reservation) {
        return null;
    }
    $orders = [];
    foreach (get_orders(true) as $order) {
        if ((int)(isset($order['reservation_id']) ? $order['reservation_id'] : 0) === (int)$id) {
            $order['payment'] = find_payment_by_order($order['id']);
            $orders[] = $order;
        }
    }
    return ['reservation' => $reservation, 'orders' => $orders];
}

function return_order_stock($orderId)
{
    $orderId = (int)$orderId;
    if ($orderId <= 0) {
        return;
    }
    if (using_database()) {
        // Cek status terakhir, jika sudah dibatalkan jangan kembalikan stok lagi (idempotent)
        $order = db_one('SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?', [$orderId]);
        if (!$order || $order['status_pesanan'] === 'dibatalkan') {
            return;
        }

        $items = db_all('SELECT id_menu, jumlah FROM detail_pesanan WHERE id_pesanan = ?', [$orderId]);
        foreach ($items as $item) {
            db_exec('UPDATE stok_menu SET jumlah_stok = jumlah_stok + ? WHERE id_menu = ?', [(int)$item['jumlah'], (int)$item['id_menu']]);
        }
        return;
    }
    foreach ($_SESSION['orders'] as $idx => $order) {
        if ((int)$order['id'] === $orderId) {
            if (isset($order['status']) && $order['status'] === 'dibatalkan') {
                return;
            }
            foreach ($order['items'] as $item) {
                foreach ($_SESSION['menus'] as $i => $menu) {
                    if ((int)$menu['id'] === (int)$item['id']) {
                        $_SESSION['menus'][$i]['stok'] = (int)$menu['stok'] + (int)$item['qty'];
                        break;
                    }
                }
            }
            return;
        }
    }
}

function release_table_for_order($orderId)
{
    $orderId = (int)$orderId;
    if ($orderId <= 0) {
        return;
    }
    if (using_database()) {
        // Ambil ID Meja dulu untuk memastikan kita hanya mengupdate meja yang benar-benar terkait
        $order = db_one('SELECT id_meja FROM pesanan WHERE id_pesanan = ?', [$orderId]);
        if ($order && !empty($order['id_meja'])) {
            db_exec("UPDATE meja SET status = 'tersedia' WHERE id_meja = ?", [(int)$order['id_meja']]);
        }
        return;
    }
    $noMeja = '';
    foreach ($_SESSION['orders'] as $order) {
        if ((int)$order['id'] === $orderId) {
            $noMeja = isset($order['no_meja']) ? $order['no_meja'] : '';
            break;
        }
    }
    if ($noMeja === '' || $noMeja === '-') {
        return;
    }
    foreach ($_SESSION['tables'] as $i => $table) {
        if ($table['no_meja'] === $noMeja) {
            $_SESSION['tables'][$i]['status'] = 'tersedia';
            break;
        }
    }
}

function payment_has_receipt($payment)
{
    if (!$payment) {
        return false;
    }
    $bukti = trim((string)(isset($payment['bukti_tf']) ? $payment['bukti_tf'] : ''));
    return $bukti !== '' && strtolower($bukti) !== 'belum ada file';
}

function update_order_status($id, $status)
{
    $id = (int)$id;
    if (!in_array($status, ['pending', 'diproses', 'selesai', 'dibatalkan'], true)) {
        return ['ok' => false, 'message' => 'Status pesanan tidak valid.'];
    }
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Pesanan tidak valid.'];
    }

    if (using_database()) {
        $order = db_one('SELECT ps.id_pesanan, ps.status_pesanan, ps.id_meja, jp.nama_pesanan AS jenis FROM pesanan ps JOIN jenis_pesanan jp ON jp.id_jenis_pesanan = ps.id_jenis_pesanan WHERE ps.id_pesanan = ? LIMIT 1', [$id]);
        if (!$order) {
            return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
        }
        $oldStatus = $order['status_pesanan'];
        if ($oldStatus === 'dibatalkan' && $status !== 'dibatalkan') {
            return ['ok' => false, 'message' => 'Pesanan yang sudah dibatalkan tidak dapat diaktifkan kembali.'];
        }
        if ($oldStatus === 'selesai' && $status !== 'selesai') {
            return ['ok' => false, 'message' => 'Pesanan yang sudah selesai tidak dapat diubah statusnya lagi.'];
        }

        // VALIDASI TRANSISI: Harus verified untuk diproses/selesai
        if (in_array($status, ['diproses', 'selesai'], true)) {
            $payment = db_one('SELECT status_payment FROM payment WHERE id_pesanan = ? LIMIT 1', [$id]);
            if (!$payment || $payment['status_payment'] !== 'verified') {
                return ['ok' => false, 'message' => 'Pesanan hanya bisa ' . $status . ' setelah payment verified.'];
            }
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($oldStatus !== 'dibatalkan' && $status === 'dibatalkan') {
                return_order_stock($id);
                release_table_for_order($id);
                db_exec("UPDATE payment SET status_payment = 'rejected', catatan_admin = COALESCE(catatan_admin, 'Pesanan dibatalkan oleh admin.'), tanggal_verifikasi = COALESCE(tanggal_verifikasi, NOW()) WHERE id_pesanan = ? AND status_payment = 'pending'", [$id]);
            }
            if ($status === 'selesai') {
                release_table_for_order($id);
            }
            db_exec('UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?', [$status, $id]);
            $pdo->commit();
            return ['ok' => true, 'message' => 'Status pesanan berhasil diubah menjadi ' . $status . '.'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Status pesanan gagal diperbarui: ' . $e->getMessage()];
        }
    }

    foreach ($_SESSION['orders'] as $i => $order) {
        if ((int)$order['id'] !== $id) {
            continue;
        }
        $oldStatus = isset($order['status']) ? $order['status'] : 'pending';
        if ($oldStatus === 'dibatalkan' && $status !== 'dibatalkan') {
            return ['ok' => false, 'message' => 'Pesanan yang sudah dibatalkan tidak dapat diaktifkan kembali.'];
        }
        if ($oldStatus === 'selesai' && $status !== 'selesai') {
            return ['ok' => false, 'message' => 'Pesanan yang sudah selesai tidak dapat diubah statusnya lagi.'];
        }

        if (in_array($status, ['diproses', 'selesai'], true)) {
            $payment = find_payment_by_order($id);
            if (!$payment || $payment['status'] !== 'verified') {
                return ['ok' => false, 'message' => 'Pesanan hanya bisa ' . $status . ' setelah payment verified.'];
            }
        }

        if ($oldStatus !== 'dibatalkan' && $status === 'dibatalkan') {
            return_order_stock($id);
            release_table_for_order($id);
            foreach ($_SESSION['payments'] as $pi => $payment) {
                if ((int)$payment['order_id'] === $id && $payment['status'] === 'pending') {
                    $_SESSION['payments'][$pi]['status'] = 'rejected';
                    $_SESSION['payments'][$pi]['catatan_admin'] = $_SESSION['payments'][$pi]['catatan_admin'] ?: 'Pesanan dibatalkan oleh admin.';
                    $_SESSION['payments'][$pi]['tanggal_verifikasi'] = date('Y-m-d H:i:s');
                }
            }
        }
        if ($status === 'selesai') {
            release_table_for_order($id);
        }
        $_SESSION['orders'][$i]['status'] = $status;
        return ['ok' => true, 'message' => 'Status pesanan berhasil diubah menjadi ' . $status . '.'];
    }
    return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
}

function dashboard_stats()
{
    if (using_database()) {
        $today = date('Y-m-d');
        $income = db_one("SELECT COALESCE(SUM(pay.jumlah),0) AS total FROM payment pay WHERE pay.status_payment = 'verified' AND pay.tipe IN ('pesanan', 'booking') AND DATE(COALESCE(pay.tanggal_verifikasi, pay.tanggal_upload, pay.created_at)) = ?", [$today]);
        $reservations = db_one('SELECT COUNT(*) AS total FROM reservasi WHERE tanggal = ?', [$today]);
        $pending = db_one("SELECT COUNT(*) AS total FROM payment WHERE status_payment = 'pending'");
        $lowRows = db_all("SELECT m.id_menu AS id, m.nama_menu AS nama, m.kategori, m.harga, m.deskripsi, m.foto, m.is_unggulan AS unggulan, s.jumlah_stok AS stok, s.minimum_stok FROM menu m JOIN stok_menu s ON s.id_menu = m.id_menu WHERE s.jumlah_stok <= s.minimum_stok ORDER BY s.jumlah_stok ASC");
        $lowStock = array_map('map_menu_row', $lowRows);
        return [
            'income_today' => (float)$income['total'],
            'reservations_today' => (int)$reservations['total'],
            'pending_payments' => (int)$pending['total'],
            'low_stock_count' => count($lowStock),
            'low_stock' => $lowStock,
        ];
    }

    $today = date('Y-m-d');
    $reservationsToday = 0;
    foreach (get_reservations(true) as $r) {
        if ($r['tanggal'] === $today) {
            $reservationsToday++;
        }
    }
    $pendingPayments = 0;
    $incomeToday = 0;
    foreach (get_payments(true) as $p) {
        if ($p['status'] === 'pending') {
            $pendingPayments++;
        }
        if ($p['status'] === 'verified') {
            $tglVerifikasi = $p['tanggal_verifikasi'] ? substr($p['tanggal_verifikasi'], 0, 10) : null;
            if ($tglVerifikasi === $today) {
                $incomeToday += (float)$p['total'];
            }
        }
    }
    $lowStock = array_filter(get_menus(), function ($m) {
        $minimum = isset($m['minimum_stok']) ? (int)$m['minimum_stok'] : 5;
        return (int)$m['stok'] <= $minimum;
    });
    return [
        'income_today' => $incomeToday,
        'reservations_today' => $reservationsToday,
        'pending_payments' => $pendingPayments,
        'low_stock_count' => count($lowStock),
        'low_stock' => $lowStock,
    ];
}

function authenticate_user($email, $password)
{
    $email = trim($email);
    if (using_database()) {
        $admin = db_one('SELECT id_admin AS id, nama AS name, email, no_telp AS phone, password FROM admin WHERE email = ? LIMIT 1', [$email]);
        if ($admin && password_verify($password, $admin['password'])) {
            return ['ok' => true, 'user' => ['id' => (int)$admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'phone' => $admin['phone'], 'role' => 'admin']];
        }
        $customer = db_one('SELECT id_pelanggan AS id, nama AS name, email, no_telp AS phone, password FROM pelanggan WHERE email = ? LIMIT 1', [$email]);
        if ($customer && password_verify($password, $customer['password'])) {
            return ['ok' => true, 'user' => ['id' => (int)$customer['id'], 'name' => $customer['name'], 'email' => $customer['email'], 'phone' => $customer['phone'], 'role' => 'customer']];
        }
        return ['ok' => false, 'message' => 'Email atau password salah.'];
    }

    $users = [
        ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@cafe.com', 'password' => 'admin123', 'role' => 'admin', 'phone' => '081234567890'],
        ['id' => 2, 'name' => 'Budi Santoso', 'email' => 'budi@email.com', 'password' => 'password123', 'role' => 'customer', 'phone' => '081234567891'],
    ];
    foreach ($_SESSION['registered_users'] as $u) {
        $users[] = $u;
    }
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email) && $user['password'] === $password) {
            unset($user['password']);
            return ['ok' => true, 'user' => $user];
        }
    }
    return ['ok' => false, 'message' => 'Email atau password salah. Coba admin@cafe.com/admin123 atau budi@email.com/password123.'];
}

function register_customer($data)
{
    $name = trim(isset($data['name']) ? $data['name'] : '');
    $email = trim(isset($data['email']) ? $data['email'] : '');
    $phone = trim(isset($data['phone']) ? $data['phone'] : '');
    $password = (string)(isset($data['password']) ? $data['password'] : '');

    if ($name === '' || $email === '' || $password === '') {
        return ['ok' => false, 'message' => 'Nama, email, dan password wajib diisi.'];
    }
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) {
        return ['ok' => false, 'message' => 'Format nomor telepon tidak valid.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Format email tidak valid.'];
    }
    if (strlen($password) < 6) {
        return ['ok' => false, 'message' => 'Password minimal 6 karakter.'];
    }

    if (using_database()) {
        $exists = db_one('SELECT email FROM pelanggan WHERE email = ? UNION SELECT email FROM admin WHERE email = ? LIMIT 1', [$email, $email]);
        if ($exists) {
            return ['ok' => false, 'message' => 'Email sudah terdaftar. Gunakan email lain.'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_exec('INSERT INTO pelanggan (nama, email, no_telp, password) VALUES (?, ?, ?, ?)', [$name, $email, $phone, $hash]);
        $id = (int)db()->lastInsertId();
        return ['ok' => true, 'user' => ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone, 'role' => 'customer']];
    }

    foreach ($_SESSION['registered_users'] as $u) {
        if (strtolower($u['email']) === strtolower($email)) {
            return ['ok' => false, 'message' => 'Email sudah terdaftar di session demo.'];
        }
    }
    $user = ['id' => next_id($_SESSION['registered_users']), 'name' => $name, 'email' => $email, 'phone' => $phone, 'password' => $password, 'role' => 'customer'];
    $_SESSION['registered_users'][] = $user;
    unset($user['password']);
    return ['ok' => true, 'user' => $user];
}

function save_profile($data)
{
    $user = current_user();
    if (!$user) {
        return ['ok' => false, 'message' => 'Silakan login terlebih dahulu.'];
    }
    $name = trim(isset($data['name']) ? $data['name'] : $user['name']);
    $email = trim(isset($data['email']) ? $data['email'] : $user['email']);
    $phone = trim(isset($data['phone']) ? $data['phone'] : (isset($user['phone']) ? $user['phone'] : ''));
    $newPassword = trim(isset($data['new_password']) ? $data['new_password'] : '');

    if ($name === '' || $email === '') {
        return ['ok' => false, 'message' => 'Nama dan email wajib diisi.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Format email tidak valid.'];
    }
    if ($newPassword !== '' && strlen($newPassword) < 6) {
        return ['ok' => false, 'message' => 'Password baru minimal 6 karakter.'];
    }

    if (using_database()) {
        $role = $user['role'];
        $table = $role === 'admin' ? 'admin' : 'pelanggan';
        $idCol = $role === 'admin' ? 'id_admin' : 'id_pelanggan';
        $existsInSameTable = db_one("SELECT email FROM $table WHERE email = ? AND $idCol <> ? LIMIT 1", [$email, (int)$user['id']]);
        $otherTable = $role === 'admin' ? 'pelanggan' : 'admin';
        $existsInOtherTable = db_one("SELECT email FROM $otherTable WHERE email = ? LIMIT 1", [$email]);
        if ($existsInSameTable || $existsInOtherTable) {
            return ['ok' => false, 'message' => 'Email sudah digunakan akun lain.'];
        }
        if ($newPassword !== '') {
            db_exec("UPDATE $table SET nama = ?, email = ?, no_telp = ?, password = ? WHERE $idCol = ?", [$name, $email, $phone, password_hash($newPassword, PASSWORD_DEFAULT), (int)$user['id']]);
        } else {
            db_exec("UPDATE $table SET nama = ?, email = ?, no_telp = ? WHERE $idCol = ?", [$name, $email, $phone, (int)$user['id']]);
        }
    }

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['phone'] = $phone;
    return ['ok' => true, 'message' => 'Profil berhasil diperbarui.'];
}

// ============================================================
// FIX: create_reservation — conflict check pakai TIME OVERLAP
// ============================================================
function create_reservation($data)
{
    $user = current_user();
    if (!$user || $user['role'] !== 'customer') {
        return ['ok' => false, 'message' => 'Reservasi hanya dapat dibuat oleh pelanggan.'];
    }
    $tanggal = trim(isset($data['tanggal']) ? $data['tanggal'] : date('Y-m-d'));
    $jam = trim(isset($data['jam']) ? $data['jam'] : '19:00');
    $jumlah = max(1, (int)(isset($data['jumlah_orang']) ? $data['jumlah_orang'] : 1));
    $noMeja = trim(isset($data['no_meja']) ? $data['no_meja'] : '');
    $catatan = trim(isset($data['catatan']) ? $data['catatan'] : '');
    $cartItems = parse_cart_items(isset($data['cart_data']) ? $data['cart_data'] : '[]');

    if (!$tanggal || strtotime($tanggal) < strtotime(date('Y-m-d'))) {
        return ['ok' => false, 'message' => 'Tanggal reservasi tidak boleh sebelum hari ini.'];
    }
    if ($jumlah > 20) {
        return ['ok' => false, 'message' => 'Jumlah orang maksimal 20 untuk satu reservasi.'];
    }

    $bookingFee = 15000;

    if (using_database()) {
        $pdo = db();
        $time    = strlen($jam) === 5 ? $jam . ':00' : $jam;
        $timeEnd = reservation_end_time($time);

        if ($noMeja !== '') {
            $table = db_one('SELECT * FROM meja WHERE no_meja = ? LIMIT 1', [$noMeja]);
        } else {
            $table = db_one("SELECT * FROM meja WHERE status = 'tersedia' AND kapasitas >= ? ORDER BY kapasitas ASC, id_meja ASC LIMIT 1", [$jumlah]);
        }
        if (!$table) {
            return ['ok' => false, 'message' => 'Meja yang sesuai belum tersedia. Coba pilih meja lain atau ubah jumlah orang.'];
        }
        if ((int)$table['kapasitas'] < $jumlah) {
            return ['ok' => false, 'message' => 'Kapasitas meja tidak cukup untuk jumlah orang yang dipilih.'];
        }

        // Cek overlap: ada reservasi lain di meja yang sama, tanggal sama,
        // dengan rentang waktu yang bertabrakan
        $conflict = db_one(
            "SELECT COUNT(*) AS total
             FROM reservasi
             WHERE id_meja = ?
               AND tanggal = ?
               AND status_reservasi IN ('pending', 'confirmed')
               AND jam                                  < ?
               AND ADDTIME(jam, SEC_TO_TIME(? * 60))    > ?",
            [(int)$table['id_meja'], $tanggal, $timeEnd, RESERVATION_DURATION_MINUTES, $time]
        );
        if ((int)$conflict['total'] > 0) {
            return ['ok' => false, 'message' => 'Meja sudah dipesan pada rentang waktu tersebut. Pilih jam lain (minimal ' . RESERVATION_DURATION_MINUTES . ' menit setelah/sebelum reservasi yang ada).'];
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO reservasi (id_pelanggan, id_meja, nama_tamu, tanggal, jam, jumlah_orang, status_reservasi, biaya_booking, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$user['id'], (int)$table['id_meja'], $user['name'], $tanggal, $time, $jumlah, 'pending', $bookingFee, $catatan]);
            $resId = (int)$pdo->lastInsertId();
            
            $totalPayment = $bookingFee;
            $orderId = null;

            if (!empty($cartItems)) {
                $idJenisRow = db_one('SELECT id_jenis_pesanan FROM jenis_pesanan WHERE nama_pesanan = ? LIMIT 1', ['reservasi']);
                $idJenis = (int)$idJenisRow['id_jenis_pesanan'];
                
                $orderTotal = 0;
                $processedItems = [];
                foreach ($cartItems as $menuId => $qty) {
                    $mStmt = $pdo->prepare('SELECT m.id_menu, m.nama_menu, m.harga, s.jumlah_stok FROM menu m JOIN stok_menu s ON s.id_menu = m.id_menu WHERE m.id_menu = ? FOR UPDATE');
                    $mStmt->execute([(int)$menuId]);
                    $menu = $mStmt->fetch();
                    if (!$menu) throw new Exception('Menu tidak ditemukan.');
                    if ((int)$menu['jumlah_stok'] < $qty) throw new Exception('Stok ' . $menu['nama_menu'] . ' tidak mencukupi.');
                    
                    $subtotal = (float)$menu['harga'] * $qty;
                    $orderTotal += $subtotal;
                    $processedItems[] = ['id' => $menu['id_menu'], 'qty' => $qty, 'price' => (float)$menu['harga'], 'subtotal' => $subtotal];
                }
                
                $orderDeposit = ceil($orderTotal * 0.5);
                $stmt = $pdo->prepare('INSERT INTO pesanan (id_pelanggan, id_reservasi, id_meja, id_jenis_pesanan, total_harga, deposit, status_pesanan) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([(int)$user['id'], $resId, (int)$table['id_meja'], $idJenis, $orderTotal, $orderDeposit, 'pending']);
                $orderId = (int)$pdo->lastInsertId();

                $detailStmt = $pdo->prepare('INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)');
                $stockStmt = $pdo->prepare('UPDATE stok_menu SET jumlah_stok = jumlah_stok - ? WHERE id_menu = ?');
                foreach ($processedItems as $it) {
                    $detailStmt->execute([$orderId, $it['id'], $it['qty'], $it['price'], $it['subtotal']]);
                    $stockStmt->execute([$it['qty'], $it['id']]);
                }
                $totalPayment += $orderDeposit;
            }

            // Combined payment (tipe=booking)
            $payStmt = $pdo->prepare('INSERT INTO payment (id_reservasi, id_pesanan, tipe, jumlah, status_payment) VALUES (?, ?, ?, ?, ?)');
            $payStmt->execute([$resId, $orderId, 'booking', $totalPayment, 'pending']);
            
            $pdo->commit();
            $msg = $orderId ? 'Reservasi & Pre-order berhasil dibuat.' : 'Reservasi berhasil dibuat.';
            return ['ok' => true, 'id' => $resId, 'kode' => 'RSV-' . str_pad((string)(1000 + $resId), 4, '0', STR_PAD_LEFT), 'message' => $msg . ' Silakan lakukan pembayaran total ' . rupiah($totalPayment)];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()];
        }
    }

    // ---- SESSION / DEMO fallback ----
    $available = array_values(array_filter(get_tables(), function ($t) use ($jumlah, $noMeja) {
        if ($t['status'] !== 'tersedia') return false;
        return ($noMeja !== '') ? ($t['no_meja'] === $noMeja) : ((int)$t['kapasitas'] >= $jumlah);
    }));
    if (!count($available)) return ['ok' => false, 'message' => 'Meja tidak tersedia.'];
    $table = $available[0];

    $resId = next_id($_SESSION['reservations']);
    $_SESSION['reservations'][] = [
        'id' => $resId, 'kode' => 'RSV-' . (1000 + $resId), 'nama' => $user['name'], 'email' => $user['email'], 
        'tanggal' => $tanggal, 'jam' => $jam, 'jumlah_orang' => $jumlah, 'no_meja' => $table['no_meja'], 
        'catatan' => $catatan, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s'), 'biaya_booking' => $bookingFee
    ];

    $totalPayment = $bookingFee;
    $orderId = null;
    if (!empty($cartItems)) {
        $orderTotal = 0;
        $processedItems = [];
        foreach ($cartItems as $menuId => $qty) {
            $menu = find_menu($menuId);
            if (!$menu || (int)$menu['stok'] < $qty) return ['ok' => false, 'message' => 'Stok menu tidak cukup.'];
            $sub = (float)$menu['harga'] * $qty;
            $orderTotal += $sub;
            $processedItems[] = ['id' => $menu['id'], 'nama' => $menu['nama'], 'harga' => $menu['harga'], 'qty' => $qty];
            // Deduct session stock
            foreach ($_SESSION['menus'] as $idx => $m) {
                if ((int)$m['id'] === (int)$menuId) $_SESSION['menus'][$idx]['stok'] -= $qty;
            }
        }
        $orderDeposit = ceil($orderTotal * 0.5);
        $orderId = next_id($_SESSION['orders']);
        $_SESSION['orders'][] = [
            'id' => $orderId, 'kode' => 'ORD-' . (1000 + $orderId), 'reservation_id' => $resId, 'nama' => $user['name'],
            'email' => $user['email'], 'jenis' => 'reservasi', 'no_meja' => $table['no_meja'], 'items' => $processedItems,
            'total' => $orderTotal, 'deposit' => $orderDeposit, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')
        ];
        $totalPayment += $orderDeposit;
    }

    $payId = next_id($_SESSION['payments']);
    $_SESSION['payments'][] = [
        'id' => $payId, 'order_id' => $orderId, 'reservation_id' => $resId, 'kode' => 'PAY-' . (1000 + $payId), 
        'nama' => $user['name'], 'tipe' => 'booking', 'total' => $totalPayment, 'bukti_tf' => 'Belum ada file', 'status' => 'pending'
    ];

    return ['ok' => true, 'id' => $resId, 'payment_id' => $payId, 'message' => 'Berhasil dibuat. Silakan bayar ' . rupiah($totalPayment)];
}

function parse_cart_items($cartJson)
{
    $items = json_decode((string)$cartJson, true);
    if (!is_array($items)) {
        return [];
    }
    $clean = [];
    foreach ($items as $item) {
        if (!isset($item['id'])) {
            continue;
        }
        $id = (int)$item['id'];
        $qty = max(1, (int)(isset($item['qty']) ? $item['qty'] : 1));
        if (!isset($clean[$id])) {
            $clean[$id] = 0;
        }
        $clean[$id] += $qty;
    }
    return $clean;
}

function create_order($data)
{
    $user = current_user();
    if (!$user || $user['role'] !== 'customer') {
        return ['ok' => false, 'message' => 'Pesanan hanya dapat dibuat oleh pelanggan.'];
    }
    $jenis = isset($data['jenis']) ? $data['jenis'] : 'dine-in';
    if (!in_array($jenis, ['dine-in', 'take-away', 'reservasi'], true)) {
        $jenis = 'dine-in';
    }
    $items = parse_cart_items(isset($data['cart_data']) ? $data['cart_data'] : '[]');
    if (!count($items)) {
        return ['ok' => false, 'message' => 'Keranjang masih kosong. Pilih minimal satu menu.'];
    }

    if (using_database()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $idJenisRow = db_one('SELECT id_jenis_pesanan FROM jenis_pesanan WHERE nama_pesanan = ? LIMIT 1', [$jenis]);
            if (!$idJenisRow) {
                throw new Exception('Jenis pesanan tidak ditemukan.');
            }
            $idJenis = (int)$idJenisRow['id_jenis_pesanan'];
            $idMeja = null;
            $idReservasi = null;

            if ($jenis === 'dine-in') {
                $noMeja = trim(isset($data['no_meja']) ? $data['no_meja'] : '');
                if ($noMeja === '') {
                    throw new Exception('Pilih meja untuk pesanan dine-in.');
                }
                $tableStmt = $pdo->prepare("SELECT * FROM meja WHERE no_meja = ? AND status = 'tersedia' LIMIT 1 FOR UPDATE");
                $tableStmt->execute([$noMeja]);
                $table = $tableStmt->fetch();
                if (!$table) {
                    throw new Exception('Meja tidak tersedia. Pilih meja lain.');
                }
                $idMeja = (int)$table['id_meja'];
            } elseif ($jenis === 'reservasi') {
                $idReservasi = (int)(isset($data['reservation_id']) ? $data['reservation_id'] : 0);
                if ($idReservasi <= 0) {
                    throw new Exception('Pilih reservasi yang akan diberi pre-order.');
                }
                $res = db_one('SELECT r.*, m.no_meja FROM reservasi r JOIN meja m ON m.id_meja = r.id_meja WHERE r.id_reservasi = ? AND r.id_pelanggan = ? AND r.status_reservasi <> ?', [$idReservasi, (int)$user['id'], 'cancelled']);
                if (!$res) {
                    throw new Exception('Reservasi tidak ditemukan atau sudah dibatalkan.');
                }
                $idMeja = (int)$res['id_meja'];
            }

            $orderItems = [];
            $total = 0;
            foreach ($items as $menuId => $qty) {
                $stmt = $pdo->prepare('SELECT m.id_menu, m.nama_menu, m.harga, COALESCE(s.jumlah_stok,0) AS stok FROM menu m JOIN stok_menu s ON s.id_menu = m.id_menu WHERE m.id_menu = ? FOR UPDATE');
                $stmt->execute([(int)$menuId]);
                $menu = $stmt->fetch();
                if (!$menu) {
                    throw new Exception('Menu tidak ditemukan.');
                }
                if ((int)$menu['stok'] < $qty) {
                    throw new Exception('Stok ' . $menu['nama_menu'] . ' tidak mencukupi. Stok tersisa: ' . (int)$menu['stok']);
                }
                $harga = (float)$menu['harga'];
                $subtotal = $harga * $qty;
                $total += $subtotal;
                $orderItems[] = ['id' => (int)$menu['id_menu'], 'nama' => $menu['nama_menu'], 'harga' => $harga, 'qty' => $qty, 'subtotal' => $subtotal];
            }
            $deposit = $jenis === 'reservasi' ? ceil($total * 0.5) : $total;
            $stmt = $pdo->prepare('INSERT INTO pesanan (id_pelanggan, id_reservasi, id_meja, id_jenis_pesanan, total_harga, deposit, status_pesanan) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$user['id'], $idReservasi ?: null, $idMeja ?: null, $idJenis, $total, $deposit, 'pending']);
            $orderId = (int)$pdo->lastInsertId();

            $detailStmt = $pdo->prepare('INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE stok_menu SET jumlah_stok = jumlah_stok - ? WHERE id_menu = ?');
            foreach ($orderItems as $item) {
                $detailStmt->execute([$orderId, $item['id'], $item['qty'], $item['harga'], $item['subtotal']]);
                $stockStmt->execute([$item['qty'], $item['id']]);
            }

            if ($jenis === 'dine-in' && $idMeja) {
                db_exec("UPDATE meja SET status = 'terisi' WHERE id_meja = ?", [$idMeja]);
            }

            $stmt = $pdo->prepare('INSERT INTO payment (id_pesanan, bukti_tf, tanggal_upload, status_payment, catatan_admin) VALUES (?, NULL, NULL, ?, NULL)');
            $stmt->execute([$orderId, 'pending']);
            $pdo->commit();
            return ['ok' => true, 'id' => $orderId, 'kode' => 'ORD-' . str_pad((string)(1000 + $orderId), 4, '0', STR_PAD_LEFT), 'message' => 'Pesanan berhasil dibuat. Lanjutkan pembayaran.'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    $selectedTable = '';
    $selectedReservationId = 0;
    if ($jenis === 'dine-in') {
        $selectedTable = trim(isset($data['no_meja']) ? $data['no_meja'] : '');
        if ($selectedTable === '') {
            return ['ok' => false, 'message' => 'Pilih meja untuk pesanan dine-in.'];
        }
        $tableOk = false;
        foreach ($_SESSION['tables'] as $table) {
            if ($table['no_meja'] === $selectedTable && $table['status'] === 'tersedia') {
                $tableOk = true;
                break;
            }
        }
        if (!$tableOk) {
            return ['ok' => false, 'message' => 'Meja tidak tersedia. Pilih meja lain.'];
        }
    } elseif ($jenis === 'reservasi') {
        $selectedReservationId = (int)(isset($data['reservation_id']) ? $data['reservation_id'] : 0);
        if ($selectedReservationId <= 0) {
            return ['ok' => false, 'message' => 'Pilih reservasi yang akan diberi pre-order.'];
        }
        $reservation = null;
        foreach (get_reservations(false) as $r) {
            if ((int)$r['id'] === $selectedReservationId && $r['status'] !== 'cancelled') {
                $reservation = $r;
                break;
            }
        }
        if (!$reservation) {
            return ['ok' => false, 'message' => 'Reservasi tidak ditemukan atau sudah dibatalkan.'];
        }
        $selectedTable = $reservation['no_meja'];
    }

    $cleanItems = [];
    $total = 0;
    foreach ($items as $menuId => $qty) {
        $menu = find_menu($menuId);
        if (!$menu) {
            return ['ok' => false, 'message' => 'Menu tidak ditemukan.'];
        }
        if ((int)$menu['stok'] < $qty) {
            return ['ok' => false, 'message' => 'Stok ' . $menu['nama'] . ' tidak mencukupi.'];
        }
        $cleanItems[] = ['id' => (int)$menuId, 'nama' => $menu['nama'], 'harga' => (float)$menu['harga'], 'qty' => $qty];
        $total += (float)$menu['harga'] * $qty;
    }
    foreach ($_SESSION['menus'] as $i => $menu) {
        $id = (int)$menu['id'];
        if (isset($items[$id])) {
            $_SESSION['menus'][$i]['stok'] = max(0, (int)$menu['stok'] - (int)$items[$id]);
        }
    }
    $orderId = next_id($_SESSION['orders']);
    $deposit = $jenis === 'reservasi' ? ceil($total * 0.5) : $total;
    $_SESSION['orders'][] = [
        'id' => $orderId,
        'kode' => 'ORD-' . str_pad((string)(1000 + $orderId), 4, '0', STR_PAD_LEFT),
        'reservation_id' => $selectedReservationId ?: null,
        'nama' => $user['name'],
        'email' => $user['email'],
        'jenis' => $jenis,
        'no_meja' => $selectedTable,
        'items' => $cleanItems,
        'total' => $total,
        'deposit' => $deposit,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    if ($jenis === 'dine-in' && $selectedTable !== '') {
        foreach ($_SESSION['tables'] as $i => $table) {
            if ($table['no_meja'] === $selectedTable) {
                $_SESSION['tables'][$i]['status'] = 'terisi';
                break;
            }
        }
    }
    $paymentId = next_id($_SESSION['payments']);
    $_SESSION['payments'][] = ['id' => $paymentId, 'order_id' => $orderId, 'kode' => 'PAY-' . str_pad((string)(1000 + $paymentId), 4, '0', STR_PAD_LEFT), 'nama' => $user['name'], 'total' => $deposit, 'bukti_tf' => 'Belum ada file', 'bukti_url' => '', 'status' => 'pending', 'catatan_admin' => '', 'tanggal_upload' => null, 'tanggal_verifikasi' => null];
    return ['ok' => true, 'id' => $orderId, 'kode' => 'ORD-' . str_pad((string)(1000 + $orderId), 4, '0', STR_PAD_LEFT), 'message' => 'Pesanan berhasil dibuat. Lanjutkan pembayaran.'];
}

function upload_payment_receipt($orderId)
{
    $user = current_user();
    if (!$user || $user['role'] !== 'customer') {
        return ['ok' => false, 'message' => 'Upload payment hanya dapat dilakukan pelanggan.'];
    }
    $orderId = (int)$orderId;
    if ($orderId <= 0) {
        return ['ok' => false, 'message' => 'Pesanan tidak valid.'];
    }
    list($filePath, $uploadError) = save_uploaded_file('bukti_tf', 'uploads/payments', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2 * 1024 * 1024);
    if ($uploadError) {
        return ['ok' => false, 'message' => $uploadError];
    }
    if (!$filePath) {
        return ['ok' => false, 'message' => 'Pilih file bukti transfer terlebih dahulu.'];
    }

    if (using_database()) {
        $order = db_one('SELECT id_pesanan, status_pesanan FROM pesanan WHERE id_pesanan = ? AND id_pelanggan = ? LIMIT 1', [$orderId, (int)$user['id']]);
        if (!$order) {
            delete_uploaded_file_if_local($filePath);
            return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
        }
        if ($order['status_pesanan'] === 'dibatalkan') {
            delete_uploaded_file_if_local($filePath);
            return ['ok' => false, 'message' => 'Pesanan sudah dibatalkan. Silakan buat pesanan baru.'];
        }
        $old = db_one('SELECT bukti_tf FROM payment WHERE id_pesanan = ? LIMIT 1', [$orderId]);
        if ($old && !empty($old['bukti_tf'])) {
            delete_uploaded_file_if_local($old['bukti_tf']);
        }
        db_exec("UPDATE payment SET bukti_tf = ?, status_payment = 'pending', tanggal_upload = NOW(), catatan_admin = NULL, tanggal_verifikasi = NULL, id_admin = NULL WHERE id_pesanan = ?", [$filePath, $orderId]);
        return ['ok' => true, 'message' => 'Bukti pembayaran berhasil dikirim. Admin akan melakukan verifikasi.'];
    }

    $orderFound = null;
    foreach ($_SESSION['orders'] as $order) {
        if ((int)$order['id'] === $orderId) {
            $orderFound = $order;
            break;
        }
    }
    if (!$orderFound) {
        delete_uploaded_file_if_local($filePath);
        return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
    }
    if (isset($orderFound['status']) && $orderFound['status'] === 'dibatalkan') {
        delete_uploaded_file_if_local($filePath);
        return ['ok' => false, 'message' => 'Pesanan sudah dibatalkan. Silakan buat pesanan baru.'];
    }

    foreach ($_SESSION['payments'] as $i => $payment) {
        if ((int)$payment['order_id'] === $orderId) {
            $_SESSION['payments'][$i]['bukti_tf'] = $filePath;
            $_SESSION['payments'][$i]['bukti_url'] = asset($filePath);
            $_SESSION['payments'][$i]['status'] = 'pending';
            $_SESSION['payments'][$i]['tanggal_upload'] = date('Y-m-d H:i:s');
            $_SESSION['payments'][$i]['catatan_admin'] = '';
            return ['ok' => true, 'message' => 'Bukti pembayaran berhasil dikirim. Admin akan melakukan verifikasi.'];
        }
    }
    return ['ok' => false, 'message' => 'Data pembayaran tidak ditemukan.'];
}

function save_menu($data)
{
    $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;
    $nama = trim(isset($data['nama']) ? $data['nama'] : '');
    $kategori = isset($data['kategori']) ? $data['kategori'] : 'Makanan';
    $harga = max(0, (float)(isset($data['harga']) ? $data['harga'] : 0));
    $stok = max(0, (int)(isset($data['stok']) ? $data['stok'] : 0));
    $minimum = max(0, (int)(isset($data['minimum_stok']) ? $data['minimum_stok'] : 5));
    $deskripsi = trim(isset($data['deskripsi']) ? $data['deskripsi'] : '');
    $unggulan = isset($data['unggulan']) ? 1 : 0;
    $fotoLama = trim(isset($data['foto_lama']) ? $data['foto_lama'] : '');
    $fotoUrl = trim(isset($data['foto_url']) ? $data['foto_url'] : '');

    if ($nama === '' || $harga <= 0) {
        return ['ok' => false, 'message' => 'Nama menu dan harga wajib diisi dengan benar.'];
    }
    if (!in_array($kategori, ['Makanan', 'Minuman', 'Dessert'], true)) {
        $kategori = 'Makanan';
    }
    list($uploaded, $uploadError) = save_uploaded_file('foto_upload', 'uploads/menu', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
    if ($uploadError) {
        return ['ok' => false, 'message' => $uploadError];
    }
    $foto = $fotoLama;
    if ($uploaded) {
        $foto = $uploaded;
    } elseif ($fotoUrl !== '') {
        $foto = $fotoUrl;
    } elseif ($foto === '') {
        $foto = 'uploads/menu/default.svg';
    }

    if (using_database()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($id) {
                db_exec('UPDATE menu SET nama_menu = ?, kategori = ?, harga = ?, deskripsi = ?, foto = ?, is_unggulan = ? WHERE id_menu = ?', [$nama, $kategori, $harga, $deskripsi, $foto, $unggulan, $id]);
                $exists = db_one('SELECT id_stok FROM stok_menu WHERE id_menu = ? LIMIT 1', [$id]);
                if ($exists) {
                    db_exec('UPDATE stok_menu SET jumlah_stok = ?, minimum_stok = ? WHERE id_menu = ?', [$stok, $minimum, $id]);
                } else {
                    db_exec('INSERT INTO stok_menu (id_menu, jumlah_stok, minimum_stok) VALUES (?, ?, ?)', [$id, $stok, $minimum]);
                }
            } else {
                db_exec('INSERT INTO menu (nama_menu, kategori, harga, deskripsi, foto, is_unggulan) VALUES (?, ?, ?, ?, ?, ?)', [$nama, $kategori, $harga, $deskripsi, $foto, $unggulan]);
                $id = (int)$pdo->lastInsertId();
                db_exec('INSERT INTO stok_menu (id_menu, jumlah_stok, minimum_stok) VALUES (?, ?, ?)', [$id, $stok, $minimum]);
            }
            $pdo->commit();
            if ($uploaded && $fotoLama !== $uploaded) {
                delete_uploaded_file_if_local($fotoLama);
            }
            return ['ok' => true, 'message' => 'Data menu berhasil disimpan.'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            if ($uploaded) {
                delete_uploaded_file_if_local($uploaded);
            }
            return ['ok' => false, 'message' => 'Data menu gagal disimpan: ' . $e->getMessage()];
        }
    }

    $dataMenu = ['id' => $id ?: next_id($_SESSION['menus']), 'nama' => $nama, 'kategori' => $kategori, 'harga' => $harga, 'deskripsi' => $deskripsi, 'foto_raw' => $foto, 'foto' => display_media_url($foto), 'stok' => $stok, 'minimum_stok' => $minimum, 'unggulan' => $unggulan];
    $updated = false;
    foreach ($_SESSION['menus'] as $i => $menu) {
        if ($id && (int)$menu['id'] === $id) {
            $_SESSION['menus'][$i] = $dataMenu;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        $_SESSION['menus'][] = $dataMenu;
    }
    return ['ok' => true, 'message' => 'Data menu berhasil disimpan.'];
}

function delete_menu($id)
{
    $id = (int)$id;
    if (using_database()) {
        $row = db_one('SELECT foto FROM menu WHERE id_menu = ? LIMIT 1', [$id]);
        try {
            db_exec('DELETE FROM menu WHERE id_menu = ?', [$id]);
            if ($row) {
                delete_uploaded_file_if_local($row['foto']);
            }
            return ['ok' => true, 'message' => 'Menu berhasil dihapus.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Menu tidak dapat dihapus karena sudah dipakai pada pesanan.'];
        }
    }
    $_SESSION['menus'] = array_values(array_filter($_SESSION['menus'], function ($m) use ($id) {
        return (int)$m['id'] !== $id;
    }));
    return ['ok' => true, 'message' => 'Menu berhasil dihapus.'];
}

function save_table_data($data)
{
    $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;
    $noMeja = trim(isset($data['no_meja']) ? $data['no_meja'] : '');
    $kapasitas = max(1, (int)(isset($data['kapasitas']) ? $data['kapasitas'] : 1));
    $status = isset($data['status']) ? $data['status'] : 'tersedia';
    if ($noMeja === '') {
        return ['ok' => false, 'message' => 'Nomor meja wajib diisi.'];
    }
    if (!in_array($status, ['tersedia', 'terisi'], true)) {
        $status = 'tersedia';
    }
    if (using_database()) {
        try {
            if ($id) {
                db_exec('UPDATE meja SET no_meja = ?, kapasitas = ?, status = ? WHERE id_meja = ?', [$noMeja, $kapasitas, $status, $id]);
            } else {
                db_exec('INSERT INTO meja (no_meja, kapasitas, status) VALUES (?, ?, ?)', [$noMeja, $kapasitas, $status]);
            }
            return ['ok' => true, 'message' => 'Data meja berhasil disimpan.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Data meja gagal disimpan. Pastikan nomor meja tidak duplikat.'];
        }
    }
    $table = ['id' => $id ?: next_id($_SESSION['tables']), 'no_meja' => $noMeja, 'kapasitas' => $kapasitas, 'status' => $status];
    $updated = false;
    foreach ($_SESSION['tables'] as $i => $t) {
        if ($id && (int)$t['id'] === $id) {
            $_SESSION['tables'][$i] = $table;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        $_SESSION['tables'][] = $table;
    }
    return ['ok' => true, 'message' => 'Data meja berhasil disimpan.'];
}

function update_reservation_status($id, $status)
{
    $id = (int)$id;
    if (!in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
        return ['ok' => false, 'message' => 'Status reservasi tidak valid.'];
    }
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Reservasi tidak valid.'];
    }
    if (using_database()) {
        try {
            $reservation = db_one('SELECT id_reservasi FROM reservasi WHERE id_reservasi = ? LIMIT 1', [$id]);
            if (!$reservation) {
                return ['ok' => false, 'message' => 'Reservasi tidak ditemukan.'];
            }
            db_exec('UPDATE reservasi SET status_reservasi = ? WHERE id_reservasi = ?', [$status, $id]);
            if ($status === 'cancelled') {
                $orders = db_all('SELECT id_pesanan FROM pesanan WHERE id_reservasi = ?', [$id]);
                foreach ($orders as $order) {
                    update_order_status((int)$order['id_pesanan'], 'dibatalkan');
                }
                db_exec("UPDATE meja m JOIN reservasi r ON r.id_meja = m.id_meja SET m.status = 'tersedia' WHERE r.id_reservasi = ? AND m.status = 'terisi'", [$id]);
            }
            return ['ok' => true, 'message' => 'Status reservasi berhasil diperbarui menjadi ' . $status . '.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Status reservasi gagal diperbarui: ' . $e->getMessage()];
        }
    }
    foreach ($_SESSION['reservations'] as $i => $r) {
        if ((int)$r['id'] === $id) {
            $_SESSION['reservations'][$i]['status'] = $status;
            if ($status === 'cancelled') {
                foreach ($_SESSION['orders'] as $order) {
                    if ((int)(isset($order['reservation_id']) ? $order['reservation_id'] : 0) === $id) {
                        update_order_status((int)$order['id'], 'dibatalkan');
                    }
                }
            }
            return ['ok' => true, 'message' => 'Status reservasi berhasil diperbarui menjadi ' . $status . '.'];
        }
    }
    return ['ok' => false, 'message' => 'Reservasi tidak ditemukan.'];
}

function delete_reservation($id)
{
    $id = (int)$id;
    if (using_database()) {
        try {
            db_exec('DELETE FROM reservasi WHERE id_reservasi = ?', [$id]);
            return ['ok' => true, 'message' => 'Reservasi berhasil dihapus.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Reservasi tidak dapat dihapus karena masih terhubung dengan data lain.'];
        }
    }
    $_SESSION['reservations'] = array_values(array_filter($_SESSION['reservations'], function ($r) use ($id) {
        return (int)$r['id'] !== $id;
    }));
    foreach ($_SESSION['orders'] as $i => $o) {
        if ((int)(isset($o['reservation_id']) ? $o['reservation_id'] : 0) === $id) {
            $_SESSION['orders'][$i]['reservation_id'] = null;
        }
    }
    return ['ok' => true, 'message' => 'Reservasi berhasil dihapus.'];
}

function verify_payment($id, $status, $catatan)
{
    $id = (int)$id;
    $status = $status === 'rejected' ? 'rejected' : 'verified';
    $catatan = trim((string)$catatan);
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Payment tidak valid.'];
    }

    if (using_database()) {
        // FIX #2: LEFT JOIN agar booking payments (id_pesanan=NULL) juga bisa diverifikasi
        $payment = db_one(
            'SELECT pay.*,
                    COALESCE(ps.status_pesanan, NULL) AS status_pesanan
             FROM payment pay
             LEFT JOIN pesanan ps ON ps.id_pesanan = pay.id_pesanan
             WHERE pay.id_payment = ? LIMIT 1',
            [$id]
        );
        if (!$payment) {
            return ['ok' => false, 'message' => 'Data payment tidak ditemukan.'];
        }
        if ($status === 'verified') {
            if (!payment_has_receipt($payment)) {
                return ['ok' => false, 'message' => 'Payment belum memiliki bukti transfer. Tidak bisa diverifikasi.'];
            }
            if ($payment['status_pesanan'] === 'dibatalkan') {
                return ['ok' => false, 'message' => 'Pesanan sudah dibatalkan sehingga payment tidak dapat diverifikasi.'];
            }
        }

        $adminId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $pdo = db();
        $pdo->beginTransaction();
        try {
            db_exec('UPDATE payment SET status_payment = ?, catatan_admin = ?, tanggal_verifikasi = NOW(), id_admin = ? WHERE id_payment = ?', [$status, $catatan, $adminId, $id]);
            if ($status === 'verified') {
                db_exec("UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = ? AND status_pesanan <> 'dibatalkan'", [(int)$payment['id_pesanan']]);
                // Otomatis konfirmasi reservasi jika ada
                if (!empty($payment['id_reservasi'])) {
                    db_exec("UPDATE reservasi SET status_reservasi = 'confirmed' WHERE id_reservasi = ? AND status_reservasi = 'pending'", [(int)$payment['id_reservasi']]);
                }
            } else {
                if ($payment['status_pesanan'] !== 'dibatalkan') {
                    return_order_stock((int)$payment['id_pesanan']);
                    release_table_for_order((int)$payment['id_pesanan']);
                    db_exec("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id_pesanan = ?", [(int)$payment['id_pesanan']]);
                    // Otomatis batalkan reservasi jika ada
                    if (!empty($payment['id_reservasi'])) {
                        db_exec("UPDATE reservasi SET status_reservasi = 'cancelled' WHERE id_reservasi = ?", [(int)$payment['id_reservasi']]);
                    }
                }
            }
            $pdo->commit();
            return ['ok' => true, 'message' => $status === 'verified' ? 'Payment verified. Pesanan masuk status diproses.' : 'Payment rejected. Pesanan dibatalkan dan stok dikembalikan.'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Status payment gagal diperbarui: ' . $e->getMessage()];
        }
    }

    foreach ($_SESSION['payments'] as $i => $payment) {
        if ((int)$payment['id'] !== $id) {
            continue;
        }
        $orderIndex = null;
        foreach ($_SESSION['orders'] as $oi => $order) {
            if ((int)$order['id'] === (int)(isset($payment['order_id']) ? $payment['order_id'] : 0)) {
                $orderIndex = $oi;
                break;
            }
        }
        $resIndex = null;
        foreach ($_SESSION['reservations'] as $ri => $res) {
            if ((int)$res['id'] === (int)(isset($payment['reservation_id']) ? $payment['reservation_id'] : 0)) {
                $resIndex = $ri;
                break;
            }
        }

        if ($status === 'verified') {
            if (!payment_has_receipt($payment)) {
                return ['ok' => false, 'message' => 'Payment belum memiliki bukti transfer. Tidak bisa diverifikasi.'];
            }
            if ($orderIndex !== null && $_SESSION['orders'][$orderIndex]['status'] === 'dibatalkan') {
                return ['ok' => false, 'message' => 'Pesanan sudah dibatalkan sehingga payment tidak dapat diverifikasi.'];
            }
        }
        $_SESSION['payments'][$i]['status'] = $status;
        $_SESSION['payments'][$i]['catatan_admin'] = $catatan;
        $_SESSION['payments'][$i]['tanggal_verifikasi'] = date('Y-m-d H:i:s');

        if ($orderIndex !== null) {
            if ($status === 'verified') {
                $_SESSION['orders'][$orderIndex]['status'] = 'diproses';
            } else {
                if ($_SESSION['orders'][$orderIndex]['status'] !== 'dibatalkan') {
                    return_order_stock((int)$payment['order_id']);
                    release_table_for_order((int)$payment['order_id']);
                    $_SESSION['orders'][$orderIndex]['status'] = 'dibatalkan';
                }
            }
        }
        if ($resIndex !== null) {
            if ($status === 'verified') {
                if ($_SESSION['reservations'][$resIndex]['status'] === 'pending') {
                    $_SESSION['reservations'][$resIndex]['status'] = 'confirmed';
                }
            } else {
                $_SESSION['reservations'][$resIndex]['status'] = 'cancelled';
            }
        }
        return ['ok' => true, 'message' => $status === 'verified' ? 'Payment verified. Pesanan masuk status diproses.' : 'Payment rejected. Pesanan dibatalkan dan stok dikembalikan.'];
    }
    return ['ok' => false, 'message' => 'Data payment tidak ditemukan.'];
}

function sales_report($startDate = '', $endDate = '', $jenisFilter = '')
{
    $startDate = $startDate ?: date('Y-m-01');
    $endDate = $endDate ?: date('Y-m-d');
    $allowedJenis = ['dine-in', 'take-away', 'reservasi'];
    $jenisFilter = in_array($jenisFilter, $allowedJenis, true) ? $jenisFilter : '';
    $summary = [
        'dine-in' => ['transaksi' => 0, 'pendapatan' => 0],
        'take-away' => ['transaksi' => 0, 'pendapatan' => 0],
        'reservasi' => ['transaksi' => 0, 'pendapatan' => 0],
    ];

    if (using_database()) {
        $where = "pay.status_payment = 'verified' AND DATE(COALESCE(pay.tanggal_verifikasi, pay.tanggal_upload, pay.created_at)) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        if ($jenisFilter !== '') {
            $where .= ' AND jp.nama_pesanan = ?';
            $params[] = $jenisFilter;
        }
        // FIX #18: Laporan pesanan biasa (tipe=pesanan)
        $rows = db_all("SELECT ps.id_pesanan, pay.id_payment,
                               CONCAT('PAY-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode_payment,
                               CONCAT('ORD-', LPAD(1000 + ps.id_pesanan, 4, '0')) AS kode_pesanan,
                               pl.nama AS pelanggan, jp.nama_pesanan AS jenis,
                               ps.total_harga, ps.deposit, pay.status_payment,
                               DATE(COALESCE(pay.tanggal_verifikasi, pay.tanggal_upload, pay.created_at)) AS tanggal
                        FROM payment pay
                        JOIN pesanan ps ON ps.id_pesanan = pay.id_pesanan
                        JOIN pelanggan pl ON pl.id_pelanggan = ps.id_pelanggan
                        JOIN jenis_pesanan jp ON jp.id_jenis_pesanan = ps.id_jenis_pesanan
                        WHERE $where ORDER BY tanggal DESC, pay.id_payment DESC", $params);

        // FIX #18: Tambahkan booking payments ke laporan (sebelumnya tidak masuk sama sekali)
        $bookingWhere = "pay.status_payment = 'verified' AND pay.tipe = 'booking'
                         AND DATE(COALESCE(pay.tanggal_verifikasi, pay.created_at)) BETWEEN ? AND ?";
        $bookingParams = [$startDate, $endDate];
        $bookingRows = ($jenisFilter === '' || $jenisFilter === 'reservasi')
            ? db_all("SELECT NULL AS id_pesanan, pay.id_payment,
                             CONCAT('BKP-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode_payment,
                             CONCAT('RSV-', LPAD(1000 + r.id_reservasi, 4, '0')) AS kode_pesanan,
                             pl.nama AS pelanggan, 'reservasi' AS jenis,
                             pay.jumlah AS total_harga, pay.jumlah AS deposit, pay.status_payment,
                             DATE(COALESCE(pay.tanggal_verifikasi, pay.created_at)) AS tanggal
                      FROM payment pay
                      JOIN reservasi r ON r.id_reservasi = pay.id_reservasi
                      JOIN pelanggan pl ON pl.id_pelanggan = r.id_pelanggan
                      WHERE $bookingWhere ORDER BY tanggal DESC", $bookingParams)
            : [];

        // Gabungkan dan sort ulang berdasar tanggal DESC
        $rows = array_merge($rows, $bookingRows);
        usort($rows, fn($a, $b) => strcmp((string)$b['tanggal'], (string)$a['tanggal']));

        $total = 0;
        foreach ($rows as $r) {
            $jenis = $r['jenis'];
            $pendapatan = (float)$r['deposit'];
            $total += $pendapatan;
            if (isset($summary[$jenis])) {
                $summary[$jenis]['transaksi']++;
                $summary[$jenis]['pendapatan'] += $pendapatan;
            }
        }
        $menuTerlaris = db_all("SELECT m.nama_menu AS nama, SUM(d.jumlah) AS qty, SUM(d.subtotal) AS total FROM payment pay JOIN pesanan ps ON ps.id_pesanan = pay.id_pesanan JOIN jenis_pesanan jp ON jp.id_jenis_pesanan = ps.id_jenis_pesanan JOIN detail_pesanan d ON d.id_pesanan = ps.id_pesanan JOIN menu m ON m.id_menu = d.id_menu WHERE $where GROUP BY m.id_menu, m.nama_menu ORDER BY qty DESC, total DESC LIMIT 5", $params);
        return [
            'start' => $startDate,
            'end' => $endDate,
            'filter_jenis' => $jenisFilter,
            'rows' => $rows,
            'total_transaksi' => count($rows),
            'total_pendapatan' => $total,
            'summary_by_jenis' => $summary,
            'menu_terlaris' => $menuTerlaris,
        ];
    }

    $rows = [];
    $matchedOrders = [];
    $orders = get_orders(true);
    foreach (get_payments(true) as $p) {
        if ($p['status'] !== 'verified') {
            continue;
        }
        $tgl = $p['tanggal_verifikasi'] ? substr($p['tanggal_verifikasi'], 0, 10) : date('Y-m-d');
        if ($tgl < $startDate || $tgl > $endDate) {
            continue;
        }
        $order = null;
        foreach ($orders as $o) {
            if ((int)$o['id'] === (int)$p['order_id']) {
                $order = $o;
                break;
            }
        }
        if (!$order) {
            continue;
        }
        if ($jenisFilter !== '' && $order['jenis'] !== $jenisFilter) {
            continue;
        }
        $matchedOrders[] = $order;
        $rows[] = [
            'id_pesanan' => $order['id'],
            'kode_payment' => $p['kode'],
            'kode_pesanan' => $order['kode'],
            'pelanggan' => $p['nama'],
            'jenis' => $order['jenis'],
            'total_harga' => $order['total'],
            'deposit' => $p['total'],
            'status_payment' => $p['status'],
            'tanggal' => $tgl,
        ];
        if (isset($summary[$order['jenis']])) {
            $summary[$order['jenis']]['transaksi']++;
            $summary[$order['jenis']]['pendapatan'] += (float)$p['total'];
        }
    }
    $total = 0;
    foreach ($rows as $r) {
        $total += (float)$r['deposit'];
    }
    $menuAgg = [];
    foreach ($matchedOrders as $order) {
        foreach ($order['items'] as $item) {
            $name = $item['nama'];
            if (!isset($menuAgg[$name])) {
                $menuAgg[$name] = ['nama' => $name, 'qty' => 0, 'total' => 0];
            }
            $menuAgg[$name]['qty'] += (int)$item['qty'];
            $menuAgg[$name]['total'] += (float)$item['harga'] * (int)$item['qty'];
        }
    }
    $menuTerlaris = array_values($menuAgg);
    usort($menuTerlaris, function ($a, $b) {
        if ($a['qty'] === $b['qty']) {
            return $b['total'] <=> $a['total'];
        }
        return $b['qty'] <=> $a['qty'];
    });
    $menuTerlaris = array_slice($menuTerlaris, 0, 5);
    return [
        'start' => $startDate,
        'end' => $endDate,
        'filter_jenis' => $jenisFilter,
        'rows' => $rows,
        'total_transaksi' => count($rows),
        'total_pendapatan' => $total,
        'summary_by_jenis' => $summary,
        'menu_terlaris' => $menuTerlaris,
    ];
}

// ============================================================
// BOOKING PAYMENT FUNCTIONS
// ============================================================

function find_payment_by_reservation($reservationId)
{
    $reservationId = (int)$reservationId;
    if (using_database()) {
        $row = db_one(
            "SELECT pay.*, pay.id_payment AS id, pay.id_pesanan AS order_id, pay.id_reservasi AS reservation_id,
                    CONCAT('BKP-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode,
                    pl.nama, pay.jumlah AS total, pay.status_payment AS status
             FROM payment pay
             JOIN reservasi r ON r.id_reservasi = pay.id_reservasi
             JOIN pelanggan pl ON pl.id_pelanggan = r.id_pelanggan
             WHERE pay.id_reservasi = ? LIMIT 1",
            [$reservationId]
        );
        return $row ? map_payment_row($row) : null;
    }
    return null;
}

function get_all_payments_for_admin()
{
    if (using_database()) {
        // FIX: Ganti OR JOIN dengan COALESCE untuk menghindari baris duplikat
        $sql = "SELECT pay.*, pay.id_payment AS id, pay.id_pesanan AS order_id, pay.id_reservasi AS reservation_id,
                       pay.status_payment AS status, pay.jumlah AS total,
                       CONCAT('PAY-', LPAD(1000 + pay.id_payment, 4, '0')) AS kode,
                       COALESCE(pl_ps.nama, pl_rsv.nama, 'Unknown') AS nama
                FROM payment pay
                LEFT JOIN pesanan ps       ON ps.id_pesanan      = pay.id_pesanan
                LEFT JOIN pelanggan pl_ps  ON pl_ps.id_pelanggan = ps.id_pelanggan
                LEFT JOIN reservasi rsv    ON rsv.id_reservasi   = pay.id_reservasi
                LEFT JOIN pelanggan pl_rsv ON pl_rsv.id_pelanggan= rsv.id_pelanggan
                ORDER BY pay.created_at DESC, pay.id_payment DESC";
        $rows = db_all($sql);
        return array_map(function($row) {
            $mapped = map_payment_row($row);
            if ($mapped['tipe'] === 'booking') {
                $mapped['kode'] = str_replace('PAY-', 'BKP-', $mapped['kode']);
            }
            return $mapped;
        }, $rows);
    }
    return get_payments(true);
}

function upload_booking_receipt($reservationId)
{
    $user = current_user();
    if (!$user || $user['role'] !== 'customer') {
        return ['ok' => false, 'message' => 'Upload payment hanya dapat dilakukan pelanggan.'];
    }
    $reservationId = (int)$reservationId;
    if ($reservationId <= 0) {
        return ['ok' => false, 'message' => 'Reservasi tidak valid.'];
    }

    list($filePath, $uploadError) = save_uploaded_file('bukti_tf', 'uploads/payments', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2 * 1024 * 1024);
    if ($uploadError) {
        return ['ok' => false, 'message' => $uploadError];
    }
    if (!$filePath) {
        return ['ok' => false, 'message' => 'Pilih file bukti transfer terlebih dahulu.'];
    }

    if (using_database()) {
        $reservation = db_one(
            'SELECT * FROM reservasi WHERE id_reservasi = ? AND id_pelanggan = ? LIMIT 1',
            [$reservationId, (int)$user['id']]
        );
        if (!$reservation) {
            delete_uploaded_file_if_local($filePath);
            return ['ok' => false, 'message' => 'Reservasi tidak ditemukan.'];
        }
        if ($reservation['status_reservasi'] === 'cancelled') {
            delete_uploaded_file_if_local($filePath);
            return ['ok' => false, 'message' => 'Reservasi sudah dibatalkan.'];
        }
        $existing = db_one(
            "SELECT * FROM payment WHERE id_reservasi = ? AND tipe = 'booking' LIMIT 1",
            [$reservationId]
        );
        if ($existing) {
            if (!empty($existing['bukti_tf'])) {
                delete_uploaded_file_if_local($existing['bukti_tf']);
            }
            db_exec(
                "UPDATE payment SET bukti_tf = ?, status_payment = 'pending', tanggal_upload = NOW(), catatan_admin = NULL, tanggal_verifikasi = NULL, id_admin = NULL WHERE id_reservasi = ? AND tipe = 'booking'",
                [$filePath, $reservationId]
            );
        } else {
            db_exec(
                "INSERT INTO payment (id_pesanan, id_reservasi, tipe, jumlah, bukti_tf, status_payment, tanggal_upload) VALUES (NULL, ?, 'booking', ?, ?, 'pending', NOW())",
                [$reservationId, (float)$reservation['biaya_booking'], $filePath]
            );
        }
        return ['ok' => true, 'message' => 'Bukti pembayaran booking fee berhasil dikirim. Admin akan melakukan verifikasi.'];
    }
    // Mode session/demo: fungsi ini memerlukan database, tidak bisa diproses tanpa DB
    delete_uploaded_file_if_local($filePath);
    return ['ok' => false, 'message' => 'Fitur upload booking hanya tersedia saat database aktif. Pastikan MySQL berjalan dan SQL sudah diimport.'];
}

function verify_booking_payment($id, $status, $catatan)
{
    $id = (int)$id;
    $status = $status === 'rejected' ? 'rejected' : 'verified';
    $catatan = trim((string)$catatan);
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Payment tidak valid.'];
    }

    if (using_database()) {
        $payment = db_one(
            "SELECT pay.*, r.status_reservasi FROM payment pay
             JOIN reservasi r ON r.id_reservasi = pay.id_reservasi
             WHERE pay.id_payment = ? AND pay.tipe = 'booking' LIMIT 1",
            [$id]
        );
        if (!$payment) {
            return ['ok' => false, 'message' => 'Data payment booking tidak ditemukan.'];
        }
        if ($status === 'verified' && !payment_has_receipt($payment)) {
            return ['ok' => false, 'message' => 'Payment belum memiliki bukti transfer.'];
        }
        if ($status === 'verified' && $payment['status_reservasi'] === 'cancelled') {
            return ['ok' => false, 'message' => 'Reservasi sudah dibatalkan.'];
        }

        $adminId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $pdo = db();
        $pdo->beginTransaction();
        try {
            db_exec(
                'UPDATE payment SET status_payment = ?, catatan_admin = ?, tanggal_verifikasi = NOW(), id_admin = ? WHERE id_payment = ?',
                [$status, $catatan, $adminId, $id]
            );
            if ($status === 'verified') {
                db_exec(
                    "UPDATE reservasi SET status_reservasi = 'confirmed' WHERE id_reservasi = ? AND status_reservasi <> 'cancelled'",
                    [(int)$payment['id_reservasi']]
                );
                // Otomatis pindahkan pesanan terkait ke diproses
                if (!empty($payment['id_pesanan'])) {
                    db_exec("UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = ? AND status_pesanan <> 'dibatalkan'", [(int)$payment['id_pesanan']]);
                }
            } else {
                db_exec(
                    "UPDATE reservasi SET status_reservasi = 'cancelled' WHERE id_reservasi = ?",
                    [(int)$payment['id_reservasi']]
                );
                // Otomatis batalkan pesanan terkait dan kembalikan stok
                if (!empty($payment['id_pesanan'])) {
                    return_order_stock((int)$payment['id_pesanan']);
                    release_table_for_order((int)$payment['id_pesanan']);
                    db_exec("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id_pesanan = ?", [(int)$payment['id_pesanan']]);
                }
            }
            $pdo->commit();
            return [
                'ok' => true,
                'message' => $status === 'verified'
                    ? 'Booking payment verified. Reservasi dikonfirmasi dan pesanan diproses.'
                    : 'Booking payment rejected. Reservasi & pesanan dibatalkan.'
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()];
        }
    }

    // SESSION DEMO FALLBACK
    foreach ($_SESSION['payments'] as $i => $payment) {
        if ((int)$payment['id'] !== $id || (isset($payment['tipe']) ? $payment['tipe'] : '') !== 'booking') {
            continue;
        }

        $resIndex = null;
        foreach ($_SESSION['reservations'] as $ri => $res) {
            if ((int)$res['id'] === (int)(isset($payment['reservation_id']) ? $payment['reservation_id'] : 0)) {
                $resIndex = $ri;
                break;
            }
        }

        $orderIndex = null;
        if (!empty($payment['order_id'])) {
            foreach ($_SESSION['orders'] as $oi => $order) {
                if ((int)$order['id'] === (int)$payment['order_id']) {
                    $orderIndex = $oi;
                    break;
                }
            }
        }

        if ($status === 'verified') {
            if (!payment_has_receipt($payment)) {
                return ['ok' => false, 'message' => 'Payment belum memiliki bukti transfer.'];
            }
            if ($resIndex !== null && $_SESSION['reservations'][$resIndex]['status'] === 'cancelled') {
                return ['ok' => false, 'message' => 'Reservasi sudah dibatalkan.'];
            }
        }

        $_SESSION['payments'][$i]['status'] = $status;
        $_SESSION['payments'][$i]['catatan_admin'] = $catatan;
        $_SESSION['payments'][$i]['tanggal_verifikasi'] = date('Y-m-d H:i:s');

        if ($resIndex !== null) {
            $_SESSION['reservations'][$resIndex]['status'] = $status === 'verified' ? 'confirmed' : 'cancelled';
        }

        if ($orderIndex !== null) {
            if ($status === 'verified') {
                $_SESSION['orders'][$orderIndex]['status'] = 'diproses';
            } else {
                if ($_SESSION['orders'][$orderIndex]['status'] !== 'dibatalkan') {
                    return_order_stock((int)$payment['order_id']);
                    release_table_for_order((int)$payment['order_id']);
                    $_SESSION['orders'][$orderIndex]['status'] = 'dibatalkan';
                }
            }
        }

        return [
            'ok' => true,
            'message' => $status === 'verified'
                ? 'Booking payment verified. Reservasi dikonfirmasi dan pesanan diproses.'
                : 'Booking payment rejected. Reservasi & pesanan dibatalkan.'
        ];
    }

    return ['ok' => false, 'message' => 'Data payment booking tidak ditemukan.'];
}