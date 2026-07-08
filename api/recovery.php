<?php
// api/recovery.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ تم التعديل: rizqy_hub بدلاً من rizq_db
$host = 'localhost';
$db   = 'rizqy_hub';  // ✅ التصحيح هنا
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مطلوب']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => true, 
            'message' => 'تم إرسال رابط الاستعادة إلى بريدك (محاكاة للبيئة المحلية)'
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => 'إذا كان البريد مسجلاً، سيتم إرسال رابط الاستعادة'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Recovery Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'خطأ في الخادم']);
}
?>