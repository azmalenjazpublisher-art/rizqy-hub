<?php
// api/get-products.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ تم التعديل: rizqy_hub بدلاً من rizq_db
$host   = 'localhost';
$db     = 'rizqy_hub';  // ✅ التصحيح هنا
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $category = $_GET['category'] ?? 'food';
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            product_name,
            description,
            price,
            category,
            stock_status,
            images,
            producer_id,
            created_at
        FROM products 
        WHERE category = :category 
          AND stock_status = 'available'
        ORDER BY created_at DESC
    ");
    $stmt->execute(['category' => $category]);
    $products = $stmt->fetchAll();
    
    foreach ($products as &$product) {
        if (!empty($product['images'])) {
            $product['images'] = array_filter(
                array_map('trim', explode(',', $product['images']))
            );
        } else {
            $product['images'] = [];
        }
        
        $product['product_name'] = htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8');
        $product['description'] = htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($products),
        'documents' => $products
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'فشل الاتصال بقاعدة البيانات',
        'message' => getenv('APP_DEBUG') === 'true' ? $e->getMessage() : 'يرجى التواصل مع الدعم الفني'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ غير متوقع',
        'message' => getenv('APP_DEBUG') === 'true' ? $e->getMessage() : 'يرجى المحاولة لاحقاً'
    ], JSON_UNESCAPED_UNICODE);
}
?>