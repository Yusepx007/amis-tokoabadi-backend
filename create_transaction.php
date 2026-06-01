<?php
require_once 'config.php';

// Accept JSON input or standard POST fields
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$user_id = $input['user_id'] ?? $_POST['user_id'] ?? 0;
$total_harga = $input['total_harga'] ?? $_POST['total_harga'] ?? 0;
$tipe = $input['tipe'] ?? $_POST['tipe'] ?? 'penjualan';
$status = $input['status'] ?? $_POST['status'] ?? 'lunas';
$items = $input['items'] ?? [];

// In case items is a stringified JSON (from flutter standard post request)
if (is_string($items)) {
    $items = json_decode($items, TRUE);
}

if (empty($user_id) || empty($items)) {
    sendResponse([
        'success' => false,
        'message' => 'User ID dan items pesanan tidak boleh kosong'
    ], 400);
}

try {
    $pdo->beginTransaction();

    // 1. Generate Transaction Code
    $kode = 'TRX-' . rand(10000, 99999);
    // Double check unique
    $checkKode = $pdo->prepare("SELECT id FROM transactions WHERE kode_transaksi = ?");
    $checkKode->execute([$kode]);
    if ($checkKode->fetch()) {
        $kode = 'TRX-' . rand(10000, 99999);
    }

    // 2. Insert into transactions table
    $stmtTrans = $pdo->prepare("
        INSERT INTO transactions (kode_transaksi, user_id, tipe, status, total_harga, tanggal) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmtTrans->execute([
        $kode,
        (int)$user_id,
        $tipe,
        $status,
        (double)$total_harga
    ]);

    $transactionId = $pdo->lastInsertId();

    // 3. Insert items and update product stocks
    $stmtItem = $pdo->prepare("
        INSERT INTO transaction_items (transaction_id, product_id, jumlah, harga_satuan, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmtUpdateStock = $pdo->prepare("
        UPDATE products 
        SET stok = stok - ? 
        WHERE id = ?
    ");

    $insertedItems = [];

    foreach ($items as $item) {
        $productId = $item['product_id'] ?? 0;
        $jumlah = $item['jumlah'] ?? 0;
        $hargaSatuan = $item['harga_satuan'] ?? 0;
        $subtotal = $item['subtotal'] ?? ($jumlah * $hargaSatuan);

        if (empty($productId) || empty($jumlah)) {
            throw new Exception("Product ID dan jumlah item tidak boleh kosong");
        }

        // Insert item
        $stmtItem->execute([
            $transactionId,
            (int)$productId,
            (int)$jumlah,
            (double)$hargaSatuan,
            (double)$subtotal
        ]);

        $itemId = $pdo->lastInsertId();

        // Get product name
        $stmtProd = $pdo->prepare("SELECT nama, stok FROM products WHERE id = ?");
        $stmtProd->execute([$productId]);
        $prod = $stmtProd->fetch();
        $productNama = $prod ? $prod['nama'] : 'Unknown Product';
        $currentStock = $prod ? (int)$prod['stok'] : 0;

        if ($currentStock < $jumlah && $tipe === 'penjualan') {
            throw new Exception("Stok untuk produk '$productNama' tidak mencukupi (Tersedia: $currentStock, Diminta: $jumlah)");
        }

        // Update product stock (potong stok)
        $stmtUpdateStock->execute([
            (int)$jumlah,
            (int)$productId
        ]);

        $insertedItems[] = [
            'id' => (int)$itemId,
            'transaction_id' => (int)$transactionId,
            'product_id' => (int)$productId,
            'product_nama' => $productNama,
            'jumlah' => (int)$jumlah,
            'harga_satuan' => (double)$hargaSatuan,
            'subtotal' => (double)$subtotal
        ];
    }

    // Commit Transaction
    $pdo->commit();

    // Fetch user name
    $stmtUser = $pdo->prepare("SELECT nama FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userName = $stmtUser->fetchColumn() ?: 'Unknown User';

    sendResponse([
        'success' => true,
        'message' => 'Transaksi berhasil dibuat',
        'transaction' => [
            'id' => (int)$transactionId,
            'kode_transaksi' => $kode,
            'user_id' => (int)$user_id,
            'user_name' => $userName,
            'tipe' => $tipe,
            'status' => $status,
            'total_harga' => (double)$total_harga,
            'tanggal' => date('c'),
            'items' => $insertedItems
        ]
    ]);

} catch (\Exception $e) {
    $pdo->rollBack();
    sendResponse([
        'success' => false,
        'message' => 'Gagal membuat transaksi: ' . $e->getMessage()
    ], 500);
}
