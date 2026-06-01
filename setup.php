<?php
// Include configuration (handles CORS headers)
require_once 'config.php';

try {
    // 1. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS db_tokoabadi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE db_tokoabadi");

    // 2. Create tables
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('pemilik', 'admin', 'sales') NOT NULL,
        password VARCHAR(255) NOT NULL,
        token VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB");

    // Products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_motif VARCHAR(50) NOT NULL UNIQUE,
        nama VARCHAR(150) NOT NULL,
        category_id INT NOT NULL,
        ukuran VARCHAR(50) NOT NULL,
        satuan VARCHAR(50) NOT NULL DEFAULT 'lembar',
        harga_beli DECIMAL(15,2) NOT NULL,
        harga_jual DECIMAL(15,2) NOT NULL,
        stok INT NOT NULL DEFAULT 0,
        stok_minimum INT NOT NULL DEFAULT 10,
        foto VARCHAR(255) NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB");

    // Transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_transaksi VARCHAR(50) NOT NULL UNIQUE,
        user_id INT NOT NULL,
        tipe ENUM('penjualan', 'pembelian') NOT NULL DEFAULT 'penjualan',
        status ENUM('lunas', 'pending', 'batal') NOT NULL DEFAULT 'pending',
        total_harga DECIMAL(15,2) NOT NULL,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB");

    // Transaction items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        product_id INT NOT NULL,
        jumlah INT NOT NULL,
        harga_satuan DECIMAL(15,2) NOT NULL,
        subtotal DECIMAL(15,2) NOT NULL,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB");

    // 3. Seed data if empty
    
    // Seed Categories
    $countCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($countCategories == 0) {
        $pdo->exec("INSERT INTO categories (id, nama) VALUES 
            (1, 'PVC'),
            (2, 'Plafon'),
            (3, 'Wallpanel')");
    }

    // Seed Users (using password_hash for safety with demo1234 as default)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DELETE FROM users"); // Reset users so they get the fresh hashes
    $demoHash = password_hash('demo1234', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (id, nama, email, role, password, token) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 'Bapak Hendra', 'pemilik@abadiplaon.id', 'pemilik', $demoHash, 'mock_token_pemilik']);
    $stmt->execute([2, 'Siti Rahayu', 'admin@abadiplaon.id', 'admin', $demoHash, 'mock_token_admin']);
    $stmt->execute([3, 'Andi Wijaya', 'sales@abadiplaon.id', 'sales', $demoHash, 'mock_token_sales']);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Seed Products
    $countProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($countProducts == 0) {
        $stmt = $pdo->prepare("INSERT INTO products (id, kode_motif, nama, category_id, ukuran, satuan, harga_beli, harga_jual, stok, stok_minimum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([1, 'KAY-01', 'PVC Motif Kayu-01', 1, '20×40 cm', 'lembar', 65000, 85000, 48, 10]);
        $stmt->execute([2, 'MAR-03', 'Wallpanel Marmer-03', 3, '30×60 cm', 'lembar', 100000, 125000, 5, 10]);
        $stmt->execute([3, 'BAT-02', 'PVC Motif Batu-02', 1, '20×40 cm', 'lembar', 55000, 78000, 0, 10]);
        $stmt->execute([4, 'GYP-60', 'Plafon Gypsum 60×60', 2, '60×60 cm', 'lembar', 30000, 45000, 120, 20]);
        $stmt->execute([5, 'KAY-02', 'PVC Motif Kayu-02', 1, '20×40 cm', 'lembar', 68000, 90000, 35, 10]);
        $stmt->execute([6, 'PLF-W01', 'Plafon PVC Putih', 2, '20×40 cm', 'meter_lari', 45000, 65000, 8, 15]);
        $stmt->execute([7, 'WP-MRB-01', 'Wallpanel Marmer Hitam', 3, '60×120 cm', 'lembar', 120000, 165000, 22, 10]);
        $stmt->execute([8, 'KAY-03', 'PVC Motif Kayu-03', 1, '20×40 cm', 'lembar', 70000, 95000, 15, 10]);
    }

    // Seed Transactions
    $countTransactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    if ($countTransactions == 0) {
        // Transaction 1
        $stmtTrans = $pdo->prepare("INSERT INTO transactions (id, kode_transaksi, user_id, tipe, status, total_harga, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $twoHoursAgo = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $stmtTrans->execute([1, 'TRX-240', 3, 'penjualan', 'lunas', 1190000, $twoHoursAgo]);

        $stmtItem = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtItem->execute([1, 1, 8, 85000, 680000]);
        $stmtItem->execute([1, 4, 15, 45000, 510000]);

        // Transaction 2
        $fiveHoursAgo = date('Y-m-d H:i:s', strtotime('-5 hours'));
        $stmtTrans->execute([2, 'TRX-239', 2, 'penjualan', 'lunas', 510000, $fiveHoursAgo]);

        // Transaction 3
        $eightHoursAgo = date('Y-m-d H:i:s', strtotime('-8 hours'));
        $stmtTrans->execute([3, 'TRX-238', 3, 'penjualan', 'lunas', 990000, $eightHoursAgo]);

        // Transaction 4
        $tenHoursAgo = date('Y-m-d H:i:s', strtotime('-10 hours'));
        $stmtTrans->execute([4, 'TRX-237', 2, 'penjualan', 'pending', 340000, $tenHoursAgo]);

        // Transaction 5
        $twelveHoursAgo = date('Y-m-d H:i:s', strtotime('-12 hours'));
        $stmtTrans->execute([5, 'TRX-236', 3, 'penjualan', 'lunas', 2750000, $twelveHoursAgo]);
    }

    sendResponse([
        'success' => true,
        'message' => 'Database db_tokoabadi setup successfully! Tables created and mock data seeded.'
    ]);

} catch (\PDOException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Setup failed: ' . $e->getMessage()
    ], 500);
}
