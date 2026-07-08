<?php
/**
 * 📄 ملف: create-order.php
 * 🎯 الوظيفة: استقبال بيانات السلة وحفظها كطلب في قاعدة البيانات
 * 🗄️ الجداول المستخدمة: orders, order_items
 */

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// معالجة طلبات CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// إعدادات قاعدة البيانات
$host = 'localhost';
$db   = 'rizqy_hub';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // قراءة البيانات المرسلة من السلة
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // التحقق من البيانات الأساسية
    if (!$data || empty($data['items']) || empty($data['customer_id'])) {
        echo json_encode(['success' => false, 'message' => 'بيانات الطلب غير مكتملة']);
        exit;
    }

    // بدء معاملة (Transaction) لضمان حفظ البيانات بشكل صحيح
    $pdo->beginTransaction();

    // 1️⃣ إدخال البيانات الرئيسية للطلب
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            customer_id, 
            customer_name, 
            customer_email, 
            total_amount, 
            total_items, 
            status, 
            payment_method, 
            created_at, 
            updated_at
        ) VALUES (
            :customer_id, 
            :customer_name, 
            :customer_email, 
            :total_amount, 
            :total_items, 
            'pending', 
            'cash_on_delivery', 
            NOW(), 
            NOW()
        )
    ");

    $stmt->execute([
        'customer_id'   => $data['customer_id'],
        'customer_name' => $data['customer_name'] ?? 'زائر',
        'customer_email'=> $data['customer_email'] ?? '',
        'total_amount'  => $data['total_amount'],
        'total_items'   => $data['total_items']
    ]);

    // الحصول على معرف الطلب الجديد
    $order_id = $pdo->lastInsertId();

    // 2️⃣ إدخال عناصر الطلب (المنتجات)
    $stmtItems = $pdo->prepare("
        INSERT INTO order_items (
            order_id, 
            product_id, 
            product_name, 
            price, 
            quantity, 
            subtotal
        ) VALUES (
            :order_id, 
            :product_id, 
            :product_name, 
            :price, 
            :quantity, 
            :subtotal
        )
    ");

    foreach ($data['items'] as $item) {
        $stmtItems->execute([
            'order_id'     => $order_id,
            'product_id'   => $item['id'] ?? $item['product_id'],
            'product_name' => $item['name'] ?? $item['product_name'],
            'price'        => $item['price'],
            'quantity'     => $item['quantity'],
            'subtotal'     => $item['price'] * $item['quantity']
        ]);
    }

    // تأكيد المعاملة
    $pdo->commit();

    // إرجاع النتيجة الناجحة
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ الطلب بنجاح',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // في حالة حدوث خطأ، إلغاء المعاملة
    $pdo->rollBack();
    
    error_log("Order Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()
    ]);
}
?>