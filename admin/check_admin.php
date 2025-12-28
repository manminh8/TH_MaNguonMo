<?php
// Tên file: admin/check_admin.php

// 1. Kiểm tra Session đã được khởi động chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// XÓA hoặc COMMENT biến $baseURL cũ
// $baseURL = '/ĐỒ ÁN THỰC TẬP'; 

// 2. Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    // CHUYỂN HƯỚNG BẰNG ĐƯỜNG DẪN TƯƠNG ĐỐI
    // Từ admin/ ra gốc dự án, rồi vào views/auth/login.php
    header('Location: ../views/auth/login.php'); 
    exit();
}

// 3. Kiểm tra vai trò (Authorization)
if ($_SESSION['user_role'] !== 'admin') {
    // CHUYỂN HƯỚNG BẰNG ĐƯỜNG DẪN TƯƠNG ĐỐI
    // Từ admin/ ra gốc dự án, rồi vào public/index.php
    header('Location: ../public/index.php'); 
    exit();
}
// ...