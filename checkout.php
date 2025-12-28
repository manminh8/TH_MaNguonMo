<?php
// checkout.php - Trang đặt hàng / Thanh toán
session_start();

// Kiểm tra xem giỏ hàng đã tồn tại chưa
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Chuyển hướng nếu giỏ hàng trống
    header('Location: cart.php');
    exit;
}

$page_title = "Tiến hành Thanh toán";
$cart_items = $_SESSION['cart'];
$total_amount = 0; // Tổng tiền cuối cùng

// 1. Tính toán lại tổng tiền từ giỏ hàng
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Giả định: Có thể thêm phí vận chuyển, mã giảm giá ở đây
$shipping_fee = 30000; // Ví dụ: 30,000 VNĐ
$final_total = $total_amount + $shipping_fee;

// 2. Xử lý khi Form được gửi (Đặt hàng)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin khách hàng từ form
    $customer_info = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'payment_method' => $_POST['payment_method'] ?? 'COD', // Mặc định là COD
    ];

    // TODO: 
    // 1. Kiểm tra tính hợp lệ của dữ liệu (Validation)
    // 2. Thực hiện logic đặt hàng (Lưu vào DB, Trừ Tồn kho)
    //    -> Thường được xử lý trong file order_processing.php
    
    // Lưu thông tin đơn hàng và chuyển hướng đến trang xác nhận
    $_SESSION['order_details'] = [
        'items' => $cart_items,
        'customer' => $customer_info,
        'final_total' => $final_total,
    ];

    // Chuyển hướng đến trang xác nhận hoặc xử lý đơn hàng
    header('Location: order_confirmation.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: auto; display: flex; gap: 30px; }
        .main-content, .order-summary { padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .main-content { flex: 2; }
        .order-summary { flex: 1; height: fit-content; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="tel"], textarea, select {
            width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .order-summary table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .order-summary th, .order-summary td { padding: 8px 0; border-bottom: 1px solid #eee; }
        .summary-total { font-size: 1.2em; font-weight: bold; color: #d9534f; }
        button { background-color: #5cb85c; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1.1em; }
        button:hover { background-color: #4cae4c; }
    </style>
</head>
<body>

<div class="container">
    
    <div class="main-content">
        <h2>Thông tin Giao hàng</h2>
        <form method="POST" action="checkout.php">
            
            <label for="name">Họ và Tên (*)</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email (*)</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Số Điện Thoại (*)</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="address">Địa chỉ Giao hàng (*)</label>
            <input type="text" id="address" name="address" required>

            <label for="notes">Ghi chú cho đơn hàng</label>
            <textarea id="notes" name="notes" rows="3"></textarea>
            
            <hr>
            
            <h2>Phương thức Thanh toán</h2>
            <select id="payment_method" name="payment_method" required>
                <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                <option value="BANK_TRANSFER">Chuyển khoản ngân hàng</option>
                </select>
            
            <hr>
            
            <button type="submit">HOÀN TẤT ĐẶT HÀNG</button>
        </form>
    </div>

    <div class="order-summary">
        <h2>Tóm tắt Đơn hàng</h2>
        <table>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td style="width: 70%;"><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                        <td class="text-right"><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</td>
                    </tr>
                <?php endforeach; ?>
                
                <tr>
                    <td>Tạm tính:</td>
                    <td class="text-right"><?php echo number_format($total_amount); ?> VNĐ</td>
                </tr>
                <tr>
                    <td>Phí vận chuyển:</td>
                    <td class="text-right"><?php echo number_format($shipping_fee); ?> VNĐ</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="summary-total">Tổng thanh toán:</td>
                    <td class="summary-total text-right"><?php echo number_format($final_total); ?> VNĐ</td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

</body>
</html>