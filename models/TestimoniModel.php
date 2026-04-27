<?php
class TestimoniModel {
    private $pdo;

    private $demoData = [
        [
            'id' => 1,
            'nama' => 'Budi Santoso',
            'peran' => 'Pelanggan Setia',
            'rating' => 5,
            'komentar' => 'Sistem reservasinya sangat mudah digunakan! Saya bisa pesan meja untuk ulang tahun istri dengan mudah. Makanannya juga luar biasa enak, terutama Ayam Bakar Madunya. Pasti akan kembali lagi!',
            'avatar' => 'https://i.pravatar.cc/80?img=11'
        ],
        [
            'id' => 2,
            'nama' => 'Siti Rahayu',
            'peran' => 'Food Blogger',
            'rating' => 5,
            'komentar' => 'Cafe Nusantara selalu jadi pilihan utama saya ketika ingin menikmati hidangan Indonesia berkualitas. Suasananya nyaman dan pelayanannya ramah. Nasi Goreng Spesialnya juara!',
            'avatar' => 'https://i.pravatar.cc/80?img=5'
        ],
        [
            'id' => 3,
            'nama' => 'Andi Pratama',
            'peran' => 'Pelanggan Baru',
            'rating' => 5,
            'komentar' => 'Baru pertama kali ke sini tapi sudah langsung jatuh cinta. Matcha Lattenya enak banget dan tempatnya sangat instagramable. Pasti balik lagi sama keluarga!',
            'avatar' => 'https://i.pravatar.cc/80?img=15'
        ],
    ];

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }

    public function getAll() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM testimoni ORDER BY id DESC");
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }
        return $this->demoData;
    }
}
