<?php
// api/update-product-status.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=rizqy_hub;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$stock_status = $data['stock_status'] ?? null;

if (!$product_id || !$stock_status) {
    echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
    exit;
}

// تحديث عمود stock بدلاً من stock_status
$stock_value = ($stock_status === 'available') ? 1 : 0;

$stmt = $pdo->prepare("UPDATE products SET stock = :stock WHERE id = :id");
$stmt->execute(['stock' => $stock_value, 'id' => $product_id]);

echo json_encode(['success' => true, 'message' => 'تم التحديث بنجاح']);
?>