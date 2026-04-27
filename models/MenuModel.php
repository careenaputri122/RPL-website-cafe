<?php
class MenuModel {
    private $pdo;

    // Demo data jika DB belum tersedia
    private $demoData = [
        ['id' => 1, 'nama' => 'Nasi Goreng Spesial',  'harga' => 35000, 'kategori' => 'Makanan', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&q=80'],
        ['id' => 2, 'nama' => 'Es Kopi Susu',          'harga' => 22000, 'kategori' => 'Minuman', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400&q=80'],
        ['id' => 3, 'nama' => 'Tiramisu Slice',        'harga' => 32000, 'kategori' => 'Dessert', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&q=80'],
        ['id' => 4, 'nama' => 'Matcha Latte',          'harga' => 28000, 'kategori' => 'Minuman', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=400&q=80'],
        ['id' => 5, 'nama' => 'Ayam Bakar Madu',       'harga' => 42000, 'kategori' => 'Makanan', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400&q=80'],
        ['id' => 6, 'nama' => 'Lava Cake Coklat',      'harga' => 35000, 'kategori' => 'Dessert', 'populer' => 1, 'gambar' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&q=80'],
    ];

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }

    public function getPopuler($limit = 6) {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE populer = 1 LIMIT :limit");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }
        return array_slice($this->demoData, 0, $limit);
    }

    public function getAll() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM menu ORDER BY kategori");
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }
        return $this->demoData;
    }
}
