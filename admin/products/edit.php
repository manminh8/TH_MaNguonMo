<?php
// Tên file: admin/products/edit.php
$rootPath = __DIR__ . '/../..';
require_once __DIR__ . '/../check_admin.php';
require_once $rootPath . '/core/database.php';
require_once $rootPath . '/core/functions.php';
require_once $rootPath . '/models/ProductModel.php';

$productModel = new ProductModel($pdo);

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: list.php?msg=' . urlencode('Lỗi: Thiếu ID sản phẩm cần chỉnh sửa.'));
    exit();
}

$product = $productModel->getProductById((int)$productId);
if (!$product) {
    header('Location: list.php?msg=' . urlencode('Lỗi: Sản phẩm không tồn tại.'));
    exit();
}

$categories = $productModel->getAllCategories();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Lấy giá trị trạng thái từ form POST (đảm bảo nó luôn là một trong 3 giá trị tiếng Anh)
    // Nếu có lỗi POST, chúng ta sẽ dựa vào giá trị cũ để chuẩn hóa
    $trangThaiValue = $_POST['TrangThai'] ?? $product['TrangThai'];

    $trangThaiStandardized = 'hidden'; // Mặc định an toàn

    // Ép buộc giá trị thành tiếng Anh, lowercase (cần thiết vì DB yêu cầu)
    $trangThaiLower = strtolower($trangThaiValue);

    if ($trangThaiLower === 'active' || $trangThaiLower === 'hiện') {
        $trangThaiStandardized = 'active';
    } elseif ($trangThaiLower === 'pending' || $trangThaiLower === 'chờ duyệt') {
        $trangThaiStandardized = 'pending';
    }
    // Các giá trị khác (inactive, ẩn) sẽ được xử lý ngầm thành 'inactive' (mặc định)

    $data = [
        'MaDanhMuc' => $_POST['MaDanhMuc'] ?? $product['MaDanhMuc'],
        'TenSanPham' => trim($_POST['TenSanPham'] ?? $product['TenSanPham']),
        'GiaBan' => (float)($_POST['GiaBan'] ?? $product['GiaBan']),
        'MoTa' => trim($_POST['MoTa'] ?? $product['MoTa']),
        'TonKho' => (int)($_POST['TonKho'] ?? $product['TonKho']),
        'TrangThai' => $trangThaiStandardized // CHẮC CHẮN GIÁ TRỊ LÀ TIẾNG ANH
    ];

    $newImagePath = $product['URLAnhChinh'];

    // --- XỬ LÝ UPLOAD ẢNH MỚI (Giống như trước) ---
    if (isset($_FILES['AnhChinh']) && $_FILES['AnhChinh']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $rootPath . '/public/assets/images/products/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        $newFileName = handleFileUpload($_FILES['AnhChinh'], $uploadDir, $allowedTypes);

        if ($newFileName) {
            $newImagePath = '/assets/images/products/' . $newFileName;
            $data['URLAnhChinh'] = $newImagePath;

            if (!empty($product['URLAnhChinh'])) {
                $oldPath = $rootPath . '/public' . $product['URLAnhChinh'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        } else {
            $message = "Lỗi upload ảnh mới. Vui lòng kiểm tra file.";
            $messageType = 'danger';
        }
    }
    // --- KẾT THÚC UPLOAD ẢNH MỚI ---

    // 3. Gọi hàm cập nhật
    if ($messageType !== 'danger') {
        $success = $productModel->updateProduct((int)$productId, $data); // Dòng 73 (gần đúng)

        if ($success) {
            header('Location: list.php?msg=' . urlencode('Cập nhật sản phẩm ID ' . $productId . ' thành công.'));
            exit();
        } else {
            $message = "Lỗi: Không thể cập nhật thông tin sản phẩm vào database.";
            $messageType = 'danger';
        }
    }
}

// Nếu POST bị lỗi, ưu tiên hiển thị dữ liệu đã gửi từ POST
$formData = $_POST ?: $product;

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Sản phẩm ID <?php echo $productId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Chỉnh Sửa Sản phẩm ID: <?php echo $productId; ?></h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="TenSanPham" class="form-label">Tên Sản phẩm (*)</label>
                <input type="text" class="form-control" id="TenSanPham" name="TenSanPham" required
                    value="<?php echo htmlspecialchars($formData['TenSanPham']); ?>">
            </div>

            <div class="mb-3">
                <label for="MaDanhMuc" class="form-label">Danh mục (*)</label>
                <select class="form-select" id="MaDanhMuc" name="MaDanhMuc" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['MaDanhMuc']; ?>"
                            <?php echo $formData['MaDanhMuc'] == $cat['MaDanhMuc'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['TenDanhMuc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="GiaBan" class="form-label">Giá bán (*)</label>
                <input type="number" step="0.01" class="form-control" id="GiaBan" name="GiaBan" required
                    value="<?php echo htmlspecialchars($formData['GiaBan']); ?>">
            </div>

            <div class="mb-3">
                <label for="TonKho" class="form-label">Tồn kho (*)</label>
                <input type="number" class="form-control" id="TonKho" name="TonKho" required
                    value="<?php echo htmlspecialchars($formData['TonKho']); ?>">
            </div>

            <div class="mb-3">
                <label for="TrangThai" class="form-label">Trạng thái (*)</label>
                <select class="form-select" id="TrangThai" name="TrangThai" required>
                    <?php
                    // Lấy giá trị, chuyển về lowercase để so sánh
                    $selectedStatus = strtolower($formData['TrangThai']);

                    // Chuẩn hóa giá trị cũ thành tiếng Anh nếu nó là tiếng Việt
                    if ($selectedStatus === 'hiện') $selectedStatus = 'active';
                    elseif ($selectedStatus === 'ẩn') $selectedStatus = 'hidden';
                    elseif ($selectedStatus === 'chờ duyệt') $selectedStatus = 'pending';
                    ?>

                    <option value="active"
                        <?php echo $selectedStatus === 'active' ? 'selected' : ''; ?>>
                        Hiện (Active)
                    </option>

                    <option value="inactive"
                        <?php echo $selectedStatus === 'hidden' ? 'selected' : ''; ?>>
                        Ẩn (Inactive)
                    </option>

                    <option value="pending"
                        <?php echo $selectedStatus === 'pending' ? 'selected' : ''; ?>>
                        Chờ duyệt (Pending)
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="MoTa" class="form-label">Mô tả</label>
                <textarea class="form-control" id="MoTa" name="MoTa" rows="3"><?php echo htmlspecialchars($formData['MoTa']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="AnhChinh" class="form-label">Ảnh chính hiện tại</label>
                <?php if ($formData['URLAnhChinh']): ?>
                    <div class="mb-2">
                        <img src="../../public<?php echo htmlspecialchars($formData['URLAnhChinh']); ?>"
                            alt="Ảnh hiện tại" style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="AnhChinh" name="AnhChinh" accept="image/jpeg,image/png,image/gif">
                <div class="form-text">Chọn file mới để thay thế ảnh hiện tại.</div>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật Sản phẩm</button>
            <a href="list.php" class="btn btn-secondary">Trở về Danh sách</a>
        </form>
    </div>
</body>

</html>