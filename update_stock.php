<?php
require_once 'config.php';

// Accept JSON input or standard POST fields
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$id = $input['id'] ?? $_POST['id'] ?? 0;
$stok = $input['stok'] ?? $_POST['stok'] ?? null;

if (empty($id) || $stok === null) {
    sendResponse([
        'success' => false,
        'message' => 'ID produk dan jumlah stok harus diisi'
    ], 400);
}

try {
    // Check if product exists
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) {
        sendResponse([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ], 404);
    }

    // Update stock
    $stmt = $pdo->prepare("UPDATE products SET stok = ? WHERE id = ?");
    $stmt->execute([(int)$stok, (int)$id]);

    sendResponse([
        'success' => true,
        'message' => 'Stok produk berhasil diperbarui',
        'id' => (int)$id,
        'stok' => (int)$stok
    ]);

} catch (\PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Gagal memperbarui stok: ' . $e->getMessage()
    ], 500);
}
