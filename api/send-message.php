<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=rizqy_hub;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$data = json_decode(file_get_contents('php://input'), true);
$convId = $data['conversation_id'] ?? null;
$sender = $data['sender_id'] ?? null;
$content = trim($data['content'] ?? '');

if (!$convId || !$sender || !$content) { echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']); exit; }

list($u1, $u2) = explode('_', $convId);
$receiver = ($sender == $u1) ? $u2 : $u1;

// فلترة بسيطة للمحتوى (أرقام، إيميلات، روابط)
$is_blocked = preg_match('/(?:\+?966|0)?5\d{8}|\b[\w.-]+@[\w.-]+\.\w{2,}\b|https?:\/\/|www\.|@(snap|insta|tele|whats)/i', $content) ? 1 : 0;

// الإدراج في عمود message بدلاً من content
$stmt = $pdo->prepare("INSERT INTO chats (sender_id, receiver_id, message, created_at) VALUES (:s, :r, :m, NOW())");
$stmt->execute(['s' => $sender, 'r' => $receiver, 'm' => $content]);

echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
?>