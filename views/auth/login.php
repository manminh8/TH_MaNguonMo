<?php
session_start();

// Kiểm tra nếu đã đăng nhập thì redirect
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: ../../admin/index.php');
    } else {
        header('Location: ../../index.php');
    }
    exit();
}

// Bao gồm các file logic cần thiết (Giữ nguyên đường dẫn)
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../models/UserModel.php';

// Khởi tạo UserModel
$userModel = new UserModel($pdo);

$message = '';

// Kiểm tra nếu có thông báo thành công từ trang đăng ký
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Xóa thông báo sau khi hiển thị
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Kiểm tra Email/Mật khẩu
    $loggedInUser = $userModel->loginUser($email, $password);

    if ($loggedInUser) {
        // 2. Đăng nhập thành công: Thiết lập Session
        $_SESSION['user_id'] = $loggedInUser['MaNguoiDung'];
        $_SESSION['user_role'] = $loggedInUser['VaiTro'];
        $_SESSION['user_fullname'] = $loggedInUser['HoTen'];

        // 3. Chuyển hướng theo vai trò (Admin hay Customer)
        if ($loggedInUser['VaiTro'] === 'admin') {
            // Chuyển hướng đến Dashboard Admin
            header('Location: ../../admin/index.php');
        } else {
            // Chuyển hướng đến Trang chủ (Customer)
            header('Location: ../../index.php');
        }
        exit();
    } else {
        // 4. Đăng nhập thất bại
        $message = "Email hoặc mật khẩu không đúng. Vui lòng thử lại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            max-width: 400px;
            margin-top: 80px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center">
        <div class="login-container">
            <h3 class="text-center mb-4 text-success">Đăng Nhập Hệ Thống</h3>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="togglePassword">
                    <label class="form-check-label" for="togglePassword">Hiện Mật khẩu</label>
                </div>

                <button type="submit" class="btn btn-success w-100">Đăng Nhập</button>
            </form>

            <p class="text-center mt-3">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mã JavaScript cho chức năng hiện/ẩn mật khẩu
        document.getElementById('togglePassword').addEventListener('change', function() {
            const input = document.getElementById('password');
            input.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>

</html>