<?php
// api/login.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'الطريقة غير مسموحة']);
    exit();
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
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'البريد الإلكتروني وكلمة المرور مطلوبان'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $email = trim($data['email']);
    $password = $data['password'];
    
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false, 
            'message' => 'البريد الإلكتروني وكلمة المرور مطلوبان'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'البريد الإلكتروني غير مسجل'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    if (password_verify($password, $user['password'])) {
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'كلمة المرور غير صحيحة'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>