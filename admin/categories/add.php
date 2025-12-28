<?php
// Tên file: admin/categories/add.php
$rootPath = __DIR__ . '/../..'; 
require_once __DIR__ . '/../check_admin.php'; // Bảo vệ trang Admin (lùi 1 cấp)
require_once $rootPath . '/core/database.php'; // Lùi 2 cấp
require_once $rootPath . '/models/ProductModel.php'; // Lùi 2 cấp

$productModel = new ProductModel($pdo);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $message = "Tên danh mục không được để trống.";
        $messageType = 'danger';
    } else {
        $success = $productModel->addCategory($name, $description);

        if ($success) {
            $message = "Thêm danh mục **" . htmlspecialchars($name) . "** thành công!";
            $messageType = 'success';
            // Tùy chọn: Chuyển hướng sang trang danh sách danh mục (nếu có list.php)
            // header('Location: list.php?msg=' . urlencode($message)); exit();
        } else {
            $message = "Lỗi: Không thể thêm danh mục vào database. Vui lòng kiểm tra kết nối/lỗi SQL.";
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Thêm Danh mục sản phẩm</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="add.php">
            
            <div class="mb-3">
                <label for="name" class="form-label">Tên Danh mục (*)</label>
                <input type="text" class="form-control" id="name" name="name" required 
                       value="<?php echo $_POST['name'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $_POST['description'] ?? ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Thêm Danh mục</button>
            <a href="../products/add.php" class="btn btn-secondary">Quay lại trang Thêm Sản phẩm</a>
        </form>
    </div>
</body>
</html>