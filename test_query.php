<?php
require 'config.php';
try {
    echo "CURRENT DB DSN: " . $dsn . "\n";
    $stmt = $pdo->query("SELECT id, kode_motif, nama, foto FROM products");
    $products = $stmt->fetchAll();
    echo "PRODUCTS IN DATABASE:\n";
    foreach ($products as $p) {
        echo "ID: " . $p['id'] . " | Kode: " . $p['kode_motif'] . " | Foto: " . $p['foto'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
