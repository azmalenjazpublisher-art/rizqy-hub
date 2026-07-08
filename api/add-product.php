<?php
/**
 * 📄 ملف: add-product.php
 * 🎯 الوظيفة: إضافة منتج جديد مع رفع الصور محلياً
 * 🗄️ قاعدة البيانات: rizqy_hub (MySQL عبر XAMPP)
 */

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 🔹 إعدادات قاعدة البيانات
$host = 'localhost';
$db   = 'rizqy_hub';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 🔹 1️⃣ التحقق من البيانات المطلوبة
    $required = ['product_name', 'description', 'price', 'category', 'family_id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "الحقل $field مطلوب"]);
            exit;
        }
    }
    
    // 🔹 2️⃣ تنظيف وتحضير البيانات
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $phone = trim($_POST['phone'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $family_id = intval($_POST['family_id']);
    
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'السعر يجب أن يكون أكبر من صفر']);
        exit;
    }
    
    // 🔹 3️⃣ معالجة ورفع الصور
    $uploaded_images = [];
    $upload_dir = __DIR__ . '/../images/products/';
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (!empty($_FILES['images']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $max_files = 5;
        
        $file_count = min(count($_FILES['images']['name']), $max_files);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['images']['name'][$i];
                $file_tmp = $_FILES['images']['tmp_name'][$i];
                $file_type = $_FILES['images']['type'][$i];
                $file_size = $_FILES['images']['size'][$i];
                
                // التحقق من نوع الملف
                if (!in_array($file_type, $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'نوع الصورة غير مدعوم: ' . $file_name]);
                    exit;
                }
                
                // التحقق من حجم الملف
                if ($file_size > $max_size) {
                    echo json_encode(['success' => false, 'message' => 'حجم الصورة كبير جداً: ' . $file_name]);
                    exit;
                }
                
                // توليد اسم فريد للملف
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_name = uniqid('prod_', true) . '.' . $ext;
                $new_name = preg_replace('/[^a-zA-Z0-9_\.]/', '', $new_name);
                
                $destination = $upload_dir . $new_name;
                
                if (move_uploaded_file($file_tmp, $destination)) {
                    $uploaded_images[] = $new_name;
                }
            }
        }
    }
    
    // 🔹 4️⃣ إدخال المنتج في قاعدة البيانات
    $stmt = $pdo->prepare("
        INSERT INTO products (
            name, description, price, category, subcategory, 
            stock, region, tags, is_featured, family_id, 
            created_at, updated_at
        ) VALUES (
            :name, :description, :price, :category, NULL,
            1, :region, :tags, 0, :family_id,
            NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        'name' => $product_name,
        'description' => $description,
        'price' => $price,
        'category' => $category,
        'region' => $region,
        'tags' => $tags,
        'family_id' => $family_id
    ]);
    
    $product_id = $pdo->lastInsertId();
    
    // 🔹 5️⃣ (اختياري) حفظ أسماء الصور في حقل منفصل إذا أردت
    // يمكن إضافة عمود 'image_files' في جدول products لتخزين أسماء الملفات
    
    // ✅ الإجابة الناجحة
    echo json_encode([
        'success' => true,
        'message' => 'تم إضافة المنتج بنجاح',
        'product_id' => $product_id,
        'uploaded_images' => $uploaded_images
    ]);
    
} catch (PDOException $e) {
    error_log("Add Product Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage()
    ]);
}
?>