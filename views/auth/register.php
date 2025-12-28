<?php
// Bắt đầu session để lưu thông báo (nếu có)
session_start(); 

// Bao gồm các file logic cần thiết
// Sử dụng đường dẫn tương đối từ views/auth/ ra thư mục gốc (..)
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../models/UserModel.php';

// Khởi tạo UserModel
// Biến $pdo được lấy từ file database.php
$userModel = new UserModel($pdo); 

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // 1. Kiểm tra dữ liệu đầu vào cơ bản
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "Vui lòng điền đầy đủ thông tin.";
    } elseif ($password !== $confirmPassword) {
        $message = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        // 2. Gọi hàm đăng ký từ Model
        $newUserId = $userModel->registerUser($fullName, $email, $password);
        
        if ($newUserId !== false) {
            // 3. Đăng ký thành công
            $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            
            // Chuyển hướng đến trang đăng nhập
            header('Location: login.php'); 
            exit();
        } else {
            // 4. Đăng ký thất bại (Thường là do email đã tồn tại)
            $message = "Đăng ký thất bại. Email này có thể đã được sử dụng.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS tùy chỉnh cho giao diện */
        body { background-color: #f8f9fa; }
        .register-container { 
            max-width: 450px; 
            margin-top: 50px; 
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="register-container">
        <h3 class="text-center mb-4 text-primary">Đăng Ký Tài Khoản Mua Hàng</h3>
        
        <?php if ($message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="full_name" class="form-label">Họ và tên (*)</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required 
                       value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email (*)</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu (*)</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu (*)</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-3">Tạo Tài Khoản</button>
        </form>
        
        <p class="text-center mt-3">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>