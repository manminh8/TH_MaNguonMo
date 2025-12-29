<?php
// cart.php - Trang hiển thị giỏ hàng
session_start();

// Thiết lập tiêu đề trang
$page_title = "Giỏ hàng của bạn";

// Khởi tạo giỏ hàng nếu chưa tồn tại
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

foreach ($_SESSION['cart'] as $product_id => $item) {
    if (!is_numeric($product_id) || (int)$product_id <= 0) {
        unset($_SESSION['cart'][$product_id]); // Xóa khỏi giỏ hàng
    }
}
$cart_items = $_SESSION['cart'];
$total_amount = 0; // Tổng tiền của giỏ hàng
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; }
        h1 { border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .cart-image { width: 50px; height: auto; }
        .empty-cart { text-align: center; color: #888; margin-top: 50px; }
        .total-row { font-weight: bold; background-color: #e0f7fa; }
    </style>
</head>
<body>

<div class="container">
    <h1><?php echo $page_title; ?></h1>

    <?php if (empty($cart_items)): ?>
        <p class="empty-cart">Giỏ hàng của bạn đang trống.</p>
        <p><a href="index.php">Tiếp tục mua sắm</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th class="text-right">Giá</th>
                    <th class="text-right">Số Lượng</th>
                    <th class="text-right">Thành Tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $product_id => $item): 
                    // Tính toán thành tiền cho từng sản phẩm
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_amount += $subtotal;
                ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-image"></td>
                        <td>
                            <?php echo htmlspecialchars($item['name']); ?><br>
                            <small>(ID: <?php echo htmlspecialchars($product_id); ?>)</small>
                        </td>
                        <td class="text-right"><?php echo number_format($item['price']); ?> VNĐ</td>
                        <td class="text-right">
                            <?php echo htmlspecialchars($item['quantity']); ?>
                            </td>
                        <td class="text-right"><?php echo number_format($subtotal); ?> VNĐ</td>
                        <td>
                            <a href="update_cart.php?action=remove&product_id=<?php echo htmlspecialchars($product_id); ?>">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td colspan="4" class="text-right">Tổng Cộng:</td>
                    <td class="text-right"><?php echo number_format($total_amount); ?> VNĐ</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <p style="text-align: right; margin-top: 20px;">
            <a href="checkout.php" style="padding: 10px 20px; background-color: green; color: white; text-decoration: none; border-radius: 5px;">Tiến hành Thanh toán</a>
        </p>

    <?php endif; ?>
</div>

</body>
</html>