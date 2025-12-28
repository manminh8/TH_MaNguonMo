<?php
// Tên file: admin/products/list.php
$rootPath = __DIR__ . '/../..';

// 1. Bảo vệ trang (Đi từ products/ qua admin/ đến check_admin.php)
// __DIR__ . '/../check_admin.php'
require_once __DIR__ . '/../check_admin.php';

// 2. Bao gồm database.php (Đi từ products/ qua admin/ ra gốc dự án, rồi vào core/)
require_once $rootPath . '/core/database.php';

// 3. Bao gồm functions.php (Hàm upload file)
require_once $rootPath . '/core/functions.php';

// 4. Bao gồm ProductModel.php
require_once $rootPath . '/models/ProductModel.php';

// Khởi tạo Model
$productModel = new ProductModel($pdo);
$products = $productModel->getAllProducts(); // Lấy tất cả sản phẩm

// Xử lý thông báo (nếu có chuyển hướng từ trang add.php)
$message = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh Sách Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>



<body>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4">
                <h1 class="h2 mt-3 mb-4">Quản lý Sản phẩm</h1>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <a href="add.php" class="btn btn-success mb-3">
                    <i class="fas fa-plus"></i> Thêm Sản phẩm mới
                </a>

                <a href="../index.php" class="btn btn-warning mb-3">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                <table class="table table-striped table-hover border">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Chưa có sản phẩm nào được thêm.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?php echo $p['MaSanPham']; ?></td>
                                    <td>
                                        <img src="../../<?php echo htmlspecialchars($p['URLAnhChinh']); ?>"
                                            alt="<?php echo htmlspecialchars($p['TenSanPham']); ?>"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo htmlspecialchars($p['TenSanPham']); ?></td>
                                    <td><?php echo htmlspecialchars($p['TenDanhMuc']); ?></td>
                                    <td><?php echo number_format($p['GiaBan'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo $p['TonKho']; ?></td>
                                    <td><?php echo $p['TrangThai']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($p['NgayTao'])); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $p['MaSanPham']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $p['MaSanPham']; ?>)">Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
    <form id="delete-form" method="POST" action="delete.php" style="display:none;">
        <input type="hidden" name="id" id="delete-id">
        <input type="hidden" name="action" value="delete">
    </form>
    <script>
        function confirmDelete(id) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm ID ' + id + ' này không?')) {
                // Gán ID sản phẩm vào input ẩn
                document.getElementById('delete-id').value = id;

                // Gửi form bằng phương thức POST
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</body>

</html>