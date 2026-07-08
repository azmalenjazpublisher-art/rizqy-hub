<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=rizqy_hub;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$data = json_decode(file_get_contents('php://input'), true);
$u1 = $data['customer_id'] ?? null;
$u2 = $data['producer_id'] ?? null;

if (!$u1 || !$u2) { echo json_encode(['success' => false, 'message' => 'المعرفات مطلوبة']); exit; }

// جلب اسم البائع
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $u2]);
$producer = $stmt->fetch();

// إنشاء معرف محادثة افتراضي يعتمد على معرفي المستخدمين (للتوافق مع الجدول الحالي)
$convId = ($u1 < $u2) ? "{$u1}_{$u2}" : "{$u2}_{$u1}";

echo json_encode([
    'success' => true,
    'conversation_id' => $convId,
    'producer_name' => $producer['name'] ?? 'الأسرة المنتجة'
]);
?>