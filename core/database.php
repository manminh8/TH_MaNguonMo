<?php
// BẢN SAO NỀN TẢNG CHO KẾT NỐI PHP VỚI MYSQL (PDO_MYSQL)

// PHẦN CẤU HÌNH (WAMPP/XAMPP MẶC ĐỊNH)
$host = 'localhost';
$db   = 'lamlaidoan_db'; // Tên DB bạn vừa tạo
$user = 'root';       // User mặc định của XAMPP/WAMPP
$pass = '';           // Mật khẩu mặc định là rỗng

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Tạo kết nối PDO
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Lỗi kết nối MySQL: " . $e->getMessage());
}
?>