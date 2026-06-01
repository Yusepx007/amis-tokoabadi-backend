<?php
require_once 'config.php';

try {
    // Select products and category nama
    $stmt = $pdo->query("
        SELECT p.*, c.nama as category_nama 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id ASC
    ");
    $products = $stmt->fetchAll();

    // Map types to match Flutter models expectations
    $formatted = [];
    foreach ($products as $p) {
        $formatted[] = [
            'id' => (int)$p['id'],
            'kode_motif' => $p['kode_motif'],
            'nama' => $p['nama'],
            'category_id' => (int)$p['category_id'],
            'category_nama' => $p['category_nama'],
            'ukuran' => $p['ukuran'],
            'satuan' => $p['satuan'],
            'harga_beli' => (double)$p['harga_beli'],
            'harga_jual' => (double)$p['harga_jual'],
            'stok' => (int)$p['stok'],
            'stok_minimum' => (int)$p['stok_minimum'],
            'foto' => $p['foto']
        ];
    }

    sendResponse([
        'success' => true,
        'products' => $formatted
    ]);

} catch (\PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Gagal mengambil data produk',
        'error' => $e->getMessage()
    ], 500);
}
