<?php
// add_to_cart.php - Phiên bản Tối ưu
session_start();

// Thiết lập header cho phản hồi JSON
header('Content-Type: application/json');

// --- Cấu hình và Tải tài nguyên ---
// File 1: Include database connection và ProductModel
require_once 'core/database.php';
require_once 'models/ProductModel.php';
// ---------------------------------

// Kiểm tra phương thức yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy và kiểm tra dữ liệu từ POST
$product_id = $_POST['product_id'] ?? null;
// Ép kiểu và đảm bảo số lượng >= 1
$quantity = (int)($_POST['quantity'] ?? 1); 

if (!$product_id || $quantity <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm hoặc số lượng không hợp lệ']);
    exit;
}

try {
    // 1. Lấy thông tin sản phẩm từ CSDL
    // Sử dụng ProductModel và đối tượng $pdo giả định đã được khởi tạo trong database.php
    $productModel = new ProductModel($pdo); 
    $product = $productModel->getProductById($product_id);
    
    // 2. Kiểm tra tính khả dụng của sản phẩm
    if (!$product || ($product['TrangThai'] ?? '') !== 'active' || ($product['TonKho'] ?? 0) <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không khả dụng hoặc đã hết hàng']);
        exit;
    }

    $available_stock = $product['TonKho'];

    // 3. Khởi tạo giỏ hàng nếu chưa tồn tại
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // --- Logic Tối ưu từ File 2: Sử dụng product_id làm key ---
    $current_quantity = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
    $new_total_quantity = $current_quantity + $quantity;

    // 4. Kiểm tra Tồn kho chi tiết
    if ($new_total_quantity > $available_stock) {
        echo json_encode([
            'success' => false,
            'message' => "Chỉ còn {$available_stock} sản phẩm trong kho. Bạn đã có {$current_quantity} sản phẩm này trong giỏ."
        ]);
        exit;
    }

    // 5. Thêm hoặc cập nhật sản phẩm vào giỏ hàng
    // Truy cập trực tiếp bằng product_id (Hiệu suất cao hơn foreach)
    $_SESSION['cart'][$product_id] = [
        'name' => $product['TenSanPham'],
        'price' => $product['GiaBan'],
        'image' => $product['URLAnhChinh'],
        'quantity' => $new_total_quantity, // Cập nhật tổng số lượng
        'stock' => $available_stock
    ];

    // Tính tổng số lượng sản phẩm khác nhau trong giỏ
    $cart_count = count($_SESSION['cart']);

    // Phản hồi thành công
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm vào giỏ hàng thành công',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    // Xử lý lỗi CSDL hoặc các lỗi Exception khác (Từ File 1)
    http_response_code(500); // Internal Server Error
    error_log("Cart Error: " . $e->getMessage()); // Ghi log lỗi
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình xử lý: ' . $e->getMessage()]);
}
?>