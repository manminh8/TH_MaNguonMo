<?php
// Tên file: admin/users/list.php
$rootPath = __DIR__ . '/../..'; 
require_once __DIR__ . '/../check_admin.php'; 
require_once $rootPath . '/core/database.php';
require_once $rootPath . '/models/UserModel.php';
require_once $rootPath . '/core/functions.php'; // Nếu cần hàm định dạng

$userModel = new UserModel($pdo);
$users = $userModel->getAllUsers();

$message = $_GET['msg'] ?? null;

// Xử lý thay đổi vai trò
if (isset($_GET['action']) && $_GET['action'] === 'change_role' && isset($_GET['id']) && isset($_GET['role'])) {
    $userId = (int)$_GET['id'];
    $newRole = strtolower($_GET['role']);
    
    // Không cho phép thay đổi vai trò của chính admin đang đăng nhập (MaNguoiDung = 4 trong dữ liệu mẫu)
    if ($userId == $_SESSION['user_id'] || $userId == 4) {
        $message = 'Không thể thay đổi vai trò của chính bạn hoặc tài khoản mặc định.';
    } elseif ($userModel->updateRole($userId, $newRole)) {
        header('Location: list.php?msg=' . urlencode('Cập nhật vai trò thành công cho ID ' . $userId));
        exit();
    } else {
        $message = 'Lỗi cập nhật vai trò.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Danh sách Người dùng</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table class="table table-striped table-hover mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ Tên</th>
                    <th>Email</th>
                    <th>Vai Trò</th>
                    <th>Ngày Tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center">Chưa có người dùng nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): 
                        $isCurrentAdmin = $user['MaNguoiDung'] == ($_SESSION['user_id'] ?? 4); 
                    ?>
                        <tr>
                            <td><?php echo $user['MaNguoiDung']; ?></td>
                            <td><?php echo htmlspecialchars($user['HoTen']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $user['VaiTro'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['VaiTro'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['NgayTao'])); ?></td>
                            <td>
                                <?php if (!$isCurrentAdmin): ?>
                                    <?php if ($user['VaiTro'] === 'customer'): ?>
                                        <a href="?action=change_role&id=<?php echo $user['MaNguoiDung']; ?>&role=admin" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn thăng cấp người dùng này lên Admin?');">
                                            Thăng cấp Admin
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=change_role&id=<?php echo $user['MaNguoiDung']; ?>&role=customer" 
                                           class="btn btn-sm btn-primary" 
                                           onclick="return confirm('Bạn có chắc chắn muốn hạ cấp Admin này xuống Khách hàng?');">
                                            Hạ cấp Khách hàng
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Tài khoản hiện tại</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>