<?php
require_once 'config.php';

// Accept JSON input or standard POST fields
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$kode_motif = $input['kode_motif'] ?? $_POST['kode_motif'] ?? '';
$nama = $input['nama'] ?? $_POST['nama'] ?? '';
$category_id = $input['category_id'] ?? $_POST['category_id'] ?? 0;
$ukuran = $input['ukuran'] ?? $_POST['ukuran'] ?? '';
$satuan = $input['satuan'] ?? $_POST['satuan'] ?? 'lembar';
$harga_beli = $input['harga_beli'] ?? $_POST['harga_beli'] ?? 0;
$harga_jual = $input['harga_jual'] ?? $_POST['harga_jual'] ?? 0;
$stok = $input['stok'] ?? $_POST['stok'] ?? 0;
$stok_minimum = $input['stok_minimum'] ?? $_POST['stok_minimum'] ?? 10;
$foto = $input['foto'] ?? $_POST['foto'] ?? null;

if (empty($kode_motif) || empty($nama) || empty($category_id)) {
    sendResponse([
        'success' => false,
        'message' => 'Kode motif, nama, dan kategori harus diisi'
    ], 400);
}

try {
    // Insert new product
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (kode_motif, nama, category_id, ukuran, satuan, harga_beli, harga_jual, stok, stok_minimum, foto) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $kode_motif,
        $nama,
        (int)$category_id,
        $ukuran,
        $satuan,
        (double)$harga_beli,
        (double)$harga_jual,
        (int)$stok,
        (int)$stok_minimum,
        $foto
    ]);

    $newId = $pdo->lastInsertId();

    // Fetch the inserted product to return it with the category name
    $stmtFetch = $pdo->prepare("
        SELECT p.*, c.nama as category_nama 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmtFetch->execute([$newId]);
    $p = $stmtFetch->fetch();

    sendResponse([
        'success' => true,
        'message' => 'Produk berhasil ditambahkan',
        'product' => [
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
        ]
    ]);

} catch (\PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Gagal menambahkan produk: ' . $e->getMessage()
    ], 500);
}
