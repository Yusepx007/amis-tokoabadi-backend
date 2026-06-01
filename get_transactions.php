<?php
require_once 'config.php';

try {
    // Select transactions
    $stmt = $pdo->query("
        SELECT t.*, u.nama as user_name 
        FROM transactions t 
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.tanggal DESC, t.id DESC
    ");
    $transactions = $stmt->fetchAll();

    $formatted = [];
    foreach ($transactions as $t) {
        // Fetch items for this transaction
        $stmtItems = $pdo->prepare("
            SELECT ti.*, p.nama as product_nama 
            FROM transaction_items ti
            LEFT JOIN products p ON ti.product_id = p.id
            WHERE ti.transaction_id = ?
        ");
        $stmtItems->execute([$t['id']]);
        $items = $stmtItems->fetchAll();

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'id' => (int)$item['id'],
                'transaction_id' => (int)$item['transaction_id'],
                'product_id' => (int)$item['product_id'],
                'product_nama' => $item['product_nama'] ?? '',
                'jumlah' => (int)$item['jumlah'],
                'harga_satuan' => (double)$item['harga_satuan'],
                'subtotal' => (double)$item['subtotal']
            ];
        }

        // Format dates in standard ISO8601 string
        $formatted[] = [
            'id' => (int)$t['id'],
            'kode_transaksi' => $t['kode_transaksi'],
            'user_id' => (int)$t['user_id'],
            'user_name' => $t['user_name'] ?? 'Unknown User',
            'tipe' => $t['tipe'],
            'status' => $t['status'],
            'total_harga' => (double)$t['total_harga'],
            'tanggal' => date('c', strtotime($t['tanggal'])),
            'items' => $formattedItems
        ];
    }

    sendResponse([
        'success' => true,
        'transactions' => $formatted
    ]);

} catch (\PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Gagal mengambil data transaksi',
        'error' => $e->getMessage()
    ], 500);
}
