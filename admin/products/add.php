<?php
// Tên file: admin/products/add.php
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

// Lấy danh sách danh mục để đổ vào dropdown
$categories = $productModel->getAllCategories(); 

$message = '';
$messageType = '';
$uploadDir = __DIR__ . '/../../public/assets/images/products/'; // Thư mục đích lưu ảnh

// Đảm bảo thư mục lưu ảnh tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- BƯỚC 1: XỬ LÝ UPLOAD ẢNH CHÍNH ---
    $mainImageName = null;
    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif']; 

    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $mainImageName = handleFileUpload($_FILES['main_image'], $uploadDir, $allowedImageTypes);
        
        if (!$mainImageName) {
            $message = "Lỗi khi upload ảnh chính. Kiểm tra định dạng hoặc kích thước.";
            $messageType = 'danger';
        }
    } else {
        $message = "Vui lòng chọn ảnh chính cho sản phẩm.";
        $messageType = 'danger';
    }

    // --- BƯỚC 2: LƯU VÀO DB NẾU UPLOAD THÀNH CÔNG ---
    if ($mainImageName && $messageType !== 'danger') {
        $productData = [
            'MaDanhMuc' => $_POST['category_id'],
            'TenSanPham' => trim($_POST['name']),
            'MoTa' => $_POST['description'],
            'GiaBan' => (float)$_POST['price'],
            'TonKho' => (int)$_POST['stock'],
            'TrangThai' => $_POST['status'] ?? 'active',
            // Lưu URL tương đối để dùng trong HTML
            'URLAnhChinh' => 'public/assets/images/products/' . $mainImageName 
        ];

        $newProductId = $productModel->addProduct($productData); 

        if ($newProductId) {
            // Xử lý upload và lưu ảnh phụ (Nếu bạn có thêm input cho ảnh phụ)
            // ... (Logic tương tự như trên) ...

            $message = "Thêm sản phẩm thành công! ID: " . $newProductId;
            $messageType = 'success';
            // Tùy chọn: Chuyển hướng đến trang list.php
            // header('Location: list.php?msg=' . urlencode($message)); exit();
        } else {
            // Xóa file đã upload nếu DB bị lỗi
            if (file_exists($uploadDir . $mainImageName)) {
                unlink($uploadDir . $mainImageName);
            }
            $message = "Lỗi: Không thể thêm sản phẩm vào database.";
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Thêm Sản phẩm mới</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="add.php" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="category_id" class="form-label">Danh mục (*)</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">-- Chọn Danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['MaDanhMuc']; ?>">
                            <?php echo htmlspecialchars($cat['TenDanhMuc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">Tên Sản phẩm (*)</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Giá bán (*)</label>
                    <input type="number" class="form-control" id="price" name="price" required min="0" step="1000">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock" class="form-label">Tồn kho (*)</label>
                    <input type="number" class="form-control" id="stock" name="stock" required min="0">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="main_image" class="form-label">Ảnh chính (*)</label>
                <input type="file" class="form-control" id="main_image" name="main_image" accept="image/*" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="active">Active (Hiển thị)</option>
                    <option value="hidden">Hidden (Ẩn)</option>
                    <option value="pending">Pending (Chờ duyệt)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Thêm Sản phẩm</button>
            <a href="list.php" class="btn btn-secondary">Quay lại danh sách</a>
        </form>
    </div>
</body>
</html>