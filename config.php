<?php
// CORS Headers - Crucial for Flutter Web/Chrome to work correctly
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = '127.0.0.1';
$db = 'db_tokoabadi';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Attempt to connect to the database
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the database doesn't exist, we'll try to connect to the server first
    try {
        $pdo_init = new PDO("mysql:host=$host;port=3307;charset=$charset", $user, $pass, $options);
        // We'll let setup.php handle DB creation, but if any other page is called first, we return 500
        if (!isset($_SERVER['PHP_SELF']) || basename($_SERVER['PHP_SELF']) !== 'setup.php') {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database not initialized. Please visit setup.php first to initialize the database.',
                'error' => $e->getMessage()
            ]);
            exit();
        }
        $pdo = $pdo_init;
    } catch (\PDOException $e2) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.',
            'error' => $e2->getMessage()
        ]);
        exit();
    }
}

// Common helper to output JSON and exit
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
