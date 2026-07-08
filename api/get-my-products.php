<?php
// api/get-my-products.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$host = 'localhost';
$db   = 'rizqy_hub';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $family_id = $_GET['family_id'] ?? null;
    
    if (!$family_id) {
        echo json_encode(['success' => false, 'message' => 'معرف الأسرة مطلوب']);
        exit;
    }
    
    // ✅ استخدام الأسماء الصحيحة للأعمدة
    $stmt = $pdo->prepare("
        SELECT id, name, description, price, category, subcategory, stock, region, tags, is_featured, created_at
        FROM products
        WHERE family_id = :family_id AND deleted_at IS NULL
        ORDER BY created_at DESC
    ");
    $stmt->execute(['family_id' => $family_id]);
    $products = $stmt->fetchAll();
    
    // تهيئة البيانات لتناسب الواجهة
    foreach ($products as &$product) {
        // تحديد حالة التوفر بناءً على المخزون
        $product['stock_status'] = ($product['stock'] > 0) ? 'available' : 'out_of_stock';
        
        // إضافة مصفوفة صور فارغة (لأن العمود غير موجود)
        $product['images'] = [];
        
        // تعيين المنتج_name ليكون الاسم
        $product['product_name'] = $product['name'];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
}
?>