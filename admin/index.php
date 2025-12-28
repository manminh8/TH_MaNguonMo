<?php
// Tên file: admin/index.php

// Bước 1: Áp dụng lớp bảo vệ
require_once 'check_admin.php';

// Bao gồm các file logic cần thiết
require_once __DIR__ . '/../core/database.php';

// Lưu ý: Đường dẫn logout đã được sửa lại (từ admin/ ra public/logout.php)
// Cần lùi 2 cấp (../..) để ra thư mục gốc, rồi vào public/
$logoutPath = '../core/public/logout.php';

// Đây là trang Dashboard Admin.
// Biến Session đã có: $_SESSION['user_fullname'], $_SESSION['user_id']
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="index.php">Admin Panel</a>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="<?php echo $logoutPath; ?>">Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>

                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Quản lý Sản phẩm</span>
                        </h6>
                        <li class="nav-item"><a class="nav-link" href="products/list.php">Danh sách Sản phẩm</a></li>
                        <li class="nav-item"><a class="nav-link" href="products/add.php">Thêm Sản phẩm</a></li>

                        <li class="nav-item"><a class="nav-link" href="categories/add.php">Thêm Danh mục</a></li>

                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Quản lý Khác</span>
                        </h6>
                        <li class="nav-item"><a class="nav-link" href="orders/list.php">Quản lý Đơn hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="reports/sales_report.php">Báo cáo & Thống kê</a></li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Tài khoản</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li>
                        <li class="nav-item"><a class="nav-link" href="../index.php">Quay lại Trang Chủ</a></li>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $logoutPath; ?>">Đăng xuất</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3">Chào mừng, Quản trị viên!</h1>
                <p>Đây là khu vực quản lý hệ thống bán hàng. Hãy chọn các chức năng từ menu bên trái.</p>

                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <div class="col">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Tổng Sản phẩm</h5>
                                <p class="card-text h3">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Đơn hàng mới</h5>
                                <p class="card-text h3">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu (Hôm nay)</h5>
                                <p class="card-text h3">0 VNĐ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>