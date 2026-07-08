<?php
// api/delete-product.php
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

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج مطلوب']);
    exit;
}

// حذف منطقي بدلاً من الحذف النهائي
$stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = :id");
$stmt->execute(['id' => $product_id]);

echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
?>