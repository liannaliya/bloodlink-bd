<?php
// ============================================
// BloodLink BD — Aiven Database Configuration
// ============================================

define('DB_HOST', 'mysql-3ae83f44-iub-a4ea.c.aivencloud.com');      // e.g. mysql-xxx.aivencloud.com
define('DB_PORT', '24087');      // e.g. 12345
define('DB_USER', 'avnadmin');
define('DB_PASS', 'AVNS_f0YJYolCwtfuuAGSzdP');
define('DB_NAME', 'defaultdb');

// Create connection with port
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// Helper: send JSON response
function respond($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}
?>