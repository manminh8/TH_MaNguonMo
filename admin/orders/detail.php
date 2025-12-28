<?php
// Tên file: admin/orders/detail.php
$rootPath = __DIR__ . '/../..'; 
require_once __DIR__ . '/../check_admin.php'; 
require_once $rootPath . '/core/database.php';
require_once $rootPath . '/models/OrderModel.php';
require_once $rootPath . '/core/functions.php';

$orderModel = new OrderModel($pdo);

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    header('Location: list.php?msg=' . urlencode('Lỗi: Thiếu ID đơn hàng.'));
    exit();
}

$order = $orderModel->getOrderDetails((int)$orderId);
if (!$order) {
    header('Location: list.php?msg=' . urlencode('Lỗi: Đơn hàng không tồn tại.'));
    exit();
}

$message = '';
$messageType = '';
$currentStatus = $order['TrangThai'];

// Danh sách các trạng thái hợp lệ cho đơn hàng (Cần khớp với DB nếu có Check Constraint)
$validStatuses = [
    'pending' => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao hàng',
    'completed' => 'Đã hoàn thành',
    'cancelled' => 'Đã hủy'
];

// --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $newStatus = trim($_POST['new_status']);
    
    if (array_key_exists($newStatus, $validStatuses)) {
        // Kiểm tra xem trạng thái mới có khác trạng thái cũ không
        if ($newStatus !== $currentStatus) {
            $success = $orderModel->updateOrderStatus((int)$orderId, $newStatus);

            if ($success) {
                // Cập nhật lại thông tin đơn hàng sau khi sửa
                $order = $orderModel->getOrderDetails((int)$orderId);
                $currentStatus = $order['TrangThai'];
                $message = "Cập nhật trạng thái đơn hàng thành công!";
                $messageType = 'success';
            } else {
                $message = "Lỗi: Không thể cập nhật trạng thái đơn hàng.";
                $messageType = 'danger';
            }
        }
    } else {
        $message = "Lỗi: Trạng thái mới không hợp lệ.";
        $messageType = 'danger';
    }
}
// --- KẾT THÚC XỬ LÝ CẬP NHẬT TRẠNG THÁI ---

// Hàm helper để hiển thị màu sắc trạng thái
function getStatusBadgeClass(string $status) {
    $status = strtolower($status);
    if ($status === 'completed') return 'bg-success';
    if ($status === 'processing' || $status === 'shipped') return 'bg-info';
    if ($status === 'cancelled') return 'bg-danger';
    return 'bg-warning'; // pending
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng #<?php echo $orderId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Chi Tiết Đơn Hàng #<?php echo $orderId; ?></h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">Thông tin Đơn hàng</div>
                    <div class="card-body">
                        <p><strong>Mã Đơn hàng:</strong> <?php echo $order['MaDonHang']; ?></p>
                        <p><strong>Ngày Đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['NgayTao'])); ?></p>
                        <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['MaNguoiDung']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['Email']); ?></p>
                        <p><strong>Tổng Giá:</strong> <span class="text-danger fw-bold"><?php echo number_format($order['TongGia'], 0, ',', '.'); ?> đ</span></p>
                        <p><strong>Phương thức TT:</strong> <?php echo htmlspecialchars($order['PhuongThucThanhToan'] ?? 'Chưa xác định'); ?></p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">Thông tin Giao hàng</div>
                    <div class="card-body">
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['MaNguoiDung']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['SDTNhanHang'] ?? 'N/A'); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DiaChiGiaoHang'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        Cập nhật Trạng thái
                    </div>
                    <div class="card-body">
                        <p class="fs-5">
                            Trạng thái hiện tại: 
                            <span class="badge <?php echo getStatusBadgeClass($currentStatus); ?>">
                                <?php echo htmlspecialchars(ucfirst($validStatuses[$currentStatus] ?? $currentStatus)); ?>
                            </span>
                        </p>
                        <form method="POST" action="detail.php?id=<?php echo $orderId; ?>">
                            <div class="mb-3">
                                <label for="new_status" class="form-label">Chọn trạng thái mới:</label>
                                <select class="form-select" id="new_status" name="new_status" required>
                                    <?php foreach ($validStatuses as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo strtolower($currentStatus) === $key ? 'disabled' : ''; ?>
                                            >
                                            <?php echo htmlspecialchars($value); ?> (<?php echo strtoupper($key); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Các trạng thái màu xám là trạng thái hiện tại và không thể chọn lại.</div>
                            </div>
                            <button type="submit" class="btn btn-warning">Cập nhật Trạng thái</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-4 mb-3">Sản phẩm trong Đơn hàng</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên Sản phẩm</th>
                    <th>Giá bán</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalAmount = 0; ?>
                <?php foreach ($order['items'] as $item): ?>
                    <?php $subtotal = $item['SoLuong'] * $item['GiaBan']; $totalAmount += $subtotal; ?>
                    <tr>
                        <td style="width: 100px;">
                            <?php if ($item['URLAnhChinh']): ?>
                                <img src="../../public<?php echo htmlspecialchars($item['URLAnhChinh']); ?>" alt="Ảnh SP" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['TenSanPham']); ?></td>
                        <td><?php echo number_format($item['GiaBan'], 0, ',', '.'); ?> đ</td>
                        <td><?php echo $item['SoLuong']; ?></td>
                        <td><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" class="text-end fw-bold">TỔNG ĐƠN HÀNG:</td>
                    <td class="text-danger fw-bold"><?php echo number_format($totalAmount, 0, ',', '.'); ?> đ</td>
                </tr>
            </tbody>
        </table>

        <a href="list.php" class="btn btn-secondary mt-3">← Trở về Danh sách Đơn hàng</a>
    </div>
</body>
</html>