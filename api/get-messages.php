<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=localhost;dbname=rizqy_hub;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$convId = $_GET['conversation_id'] ?? null;
if (!$convId) { echo json_encode(['success' => false, 'message' => 'المعرف مطلوب']); exit; }

list($u1, $u2) = explode('_', $convId);

// جلب الرسائل بين الطرفين فقط (باستخدام عمود message الفعلي)
$stmt = $pdo->prepare("
    SELECT id, sender_id, message as content, created_at 
    FROM chats 
    WHERE deleted_at IS NULL 
    AND ((sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u2 AND receiver_id = :u1))
    ORDER BY created_at ASC
    LIMIT 100
");
$stmt->execute(['u1' => $u1, 'u2' => $u2]);
echo json_encode(['success' => true, 'messages' => $stmt->fetchAll()]);
?>