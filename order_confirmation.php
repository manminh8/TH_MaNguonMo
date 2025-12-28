<?php
// Tên file: order_confirmation.php
// Mục đích: Xử lý dữ liệu giỏ hàng, lưu vào donhang và chitietdonhang, sau đó chuyển hướng.

session_start();

// --- 1. Kiểm tra Dữ liệu cần thiết ---

// A. Kiểm tra session giỏ hàng và dữ liệu đơn hàng đã chuẩn bị từ checkout.php
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['order_data'])) {
    header('Location: index.php'); // Quay về trang chủ nếu không có dữ liệu
    exit;
}

// Lấy dữ liệu đã chuẩn bị
$order_data = $_SESSION['order_data'];
$cart_items = $_SESSION['cart'];

// --- 2. Kết nối và Khởi tạo Model ---

$rootPath = __DIR__ . '/..';
// Giả định các file này nằm ở cấp độ gốc của ứng dụng (ngang hàng với checkout.php)
require_once $rootPath . '/core/public/database.php';
require_once $rootPath . '/models/OrderModel.php';
// require_once $rootPath . '/models/ProductModel.php'; // Cần cho việc trừ tồn kho

// Khởi tạo đối tượng Model
$orderModel = new OrderModel($pdo);
// $productModel = new ProductModel($pdo); // Nếu bạn có model sản phẩm

// --- 3. Xử lý Lưu Đơn hàng (Sử dụng Transaction an toàn) ---

$MaDonHangMoi = false;
$success = false;

try {
    // Bắt đầu Transaction để đảm bảo tính toàn vẹn dữ liệu
    $pdo->beginTransaction(); 

    // 3.1. Tạo Đơn hàng mới (bảng donhang)
    $TongGia = $order_data['TongGia']; 
    
    // Hàm createOrder đã được sửa để chấp nhận các trường mới
    $MaDonHangMoi = $orderModel->createOrder($order_data, $TongGia);

    if ($MaDonHangMoi) {
        
        // 3.2. Lưu chi tiết Đơn hàng (bảng chitietdonhang)
        $detailsSuccess = $orderModel->createOrderDetails($MaDonHangMoi, $cart_items);

        if ($detailsSuccess) {
            // 3.3. Cập nhật tồn kho (Nếu có ProductModel)
            // Lưu ý: Cần thêm hàm updateStock($MaSanPham, $SoLuongGiam) vào ProductModel
            /*
            foreach ($cart_items as $item) {
                $productModel->updateStock($item['id'], $item['quantity']); 
            }
            */

            $pdo->commit(); // Ghi dữ liệu vào database
            $success = true;
        } else {
            // Chi tiết đơn hàng bị lỗi
            $pdo->rollBack();
            $error_message = 'Lỗi: Không thể lưu chi tiết sản phẩm.';
        }

    } else {
        // Lỗi tạo đơn hàng chính
        $pdo->rollBack();
        $error_message = 'Lỗi: Không thể tạo đơn hàng chính.';
    }

} catch (Exception $e) {
    $pdo->rollBack();
    $error_message = 'Lỗi hệ thống: ' . $e->getMessage();
    // Nên ghi log lỗi chi tiết tại đây
}

// --- 4. Chuyển hướng Sau khi Xử lý ---

if ($success) {
    // Xóa giỏ hàng và dữ liệu đặt hàng trong session
    unset($_SESSION['cart']); 
    unset($_SESSION['order_data']);

    // Chuyển hướng đến trang cảm ơn (Truyền ID đơn hàng để hiển thị)
    header('Location: thank_you.php?order_id=' . $MaDonHangMoi);
    exit;
} else {
    // Quay lại trang checkout với thông báo lỗi
    // Lưu ý: Bạn cần phải điều chỉnh checkout.php để hiển thị $error_message
    header('Location: checkout.php?error=' . urlencode($error_message));
    exit;
}