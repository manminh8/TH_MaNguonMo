<?php
// index.php - Trang chủ
session_start();

// Define base path and URL
define('BASE_PATH', dirname(__FILE__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', BASE_PATH));

// Include configuration
require_once 'core/database.php';
require_once 'core/functions.php';

// Include models
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

// Initialize models
$productModel = new ProductModel($pdo);
$categoryModel = new CategoryModel($pdo);

// Get data for homepage
$featuredProducts = $productModel->getProductsForHomepage(8); // Lấy 8 sản phẩm mới nhất
$categories = $categoryModel->getAllCategories();
?>

<!DOCTYPE html>
<html lang="vi">

<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gundam Model Shop - Mô Hình Gundam Chính Hãng</title>

    <link rel="stylesheet" href="./public/assets/css/styles.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->

</head>

<body>

    <?php include 'views/layout/navigation.php'; ?>
    <h1>Vương Thái Tài</h1>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Mô Hình Gundam Chính Hãng</h1>
            <p class="lead mb-4">Khám phá bộ sưu tập mô hình Gundam Bandai chính hãng từ Nhật Bản</p>
            <a href="products.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-shopping-bag me-2"></i> Mua ngay
            </a>
            <a href="#products" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-eye me-2"></i> Xem sản phẩm
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Categories Section -->
            <!-- <section class="categories-section mb-5">
                <h2 class="text-center mb-4">Danh Mục Sản Phẩm</h2>
                <div class="row">
                    <?php if ($categories && count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="card category-card h-100 text-center">
                                    <div class="card-body">
                                        <div class="category-icon mb-3">
                                            <i class="fas fa-robot fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php echo isset($category['MoTa']) ? htmlspecialchars(substr($category['MoTa'], 0, 100)) . '...' : ''; ?>
                                        </p>
                                        <a href="products.php?category=<?php echo $category['MaDanhMuc']; ?>"
                                            class="btn btn-outline-primary">
                                            Xem sản phẩm <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Chưa có danh mục nào.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section> -->

            <!-- Products Section -->
            <section id="products" class="products-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Sản Phẩm Mới Nhất</h2>
                    <a href="products.php" class="btn btn-link">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row">
                    <?php if ($featuredProducts && count($featuredProducts) > 0): ?>
                        <?php foreach ($featuredProducts as $product): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card product-card h-100">
                                    <!-- Product Image -->
                                    <?php if (!empty($product['URLAnhChinh'])): ?>
                                        <!-- START: ĐÃ SỬA LỖI ĐƯỜNG DẪN ẢNH CHÍNH (URLAnhChinh) -->
                                        <img src="<?php echo htmlspecialchars($product['URLAnhChinh']); ?>"
                                            alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>"
                                            class="img-fluid rounded"
                                            id="main-product-image">
                                        <!-- END: ĐÃ SỬA LỖI ĐƯỜNG DẪN ẢNH CHÍNH (URLAnhChinh) -->
                                    <?php else: ?>
                                        <img src="assets/images/no-image.jpg"
                                            alt="No image"
                                            class="img-fluid rounded">
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <!-- Product Name -->
                                        <h5 class="card-title">
                                            <a href="product.php?id=<?php echo $product['MaSanPham']; ?>"
                                                class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($product['TenSanPham']); ?>
                                            </a>
                                        </h5>

                                        <!-- Product Description -->
                                        <?php if (!empty($product['MoTa'])): ?>
                                            <p class="card-text text-muted small flex-grow-1">
                                                <?php echo htmlspecialchars(substr($product['MoTa'], 0, 80)) . '...'; ?>
                                            </p>
                                        <?php endif; ?>

                                        <!-- Product Price -->
                                        <div class="product-price mb-3">
                                            <span class="fw-bold fs-5 text-primary">
                                                <?php echo number_format($product['GiaBan'], 0, ',', '.') . ' VNĐ'; ?>
                                            </span>
                                        </div>

                                        <!-- Stock Info -->
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-box me-1"></i>
                                                Còn lại: <?php echo $product['TonKho']; ?> sản phẩm
                                            </small>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="d-grid gap-2 mt-auto">
                                            <?php if ($product['TonKho'] > 0 && $product['TrangThai'] === 'active'): ?>
                                                <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['MaSanPham']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100" disabled>
                                                    <i class="fas fa-times-circle me-2"></i> Hết hàng
                                                </button>
                                            <?php endif; ?>

                                            <a href="product.php?id=<?php echo $product['MaSanPham']; ?>"
                                                class="btn btn-outline-secondary">
                                                <i class="fas fa-eye me-2"></i> Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Chưa có sản phẩm nào. Hãy thêm sản phẩm từ trang quản trị.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Features Section -->
            <section class="features-section bg-light py-5 mt-5 rounded">
                <div class="container">
                    <h2 class="text-center mb-5">Tại Sao Chọn Chúng Tôi?</h2>
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            <div class="icon-box mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5>Chính Hãng 100%</h5>
                            <p class="text-muted">Sản phẩm Bandai chính hãng Nhật Bản, đầy đủ tem bảo hành</p>
                        </div>
                        <div class="col-md-3 text-center mb-4">
                            <div class="icon-box mb-3">
                                <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                            </div>
                            <h5>Giao Hàng Nhanh</h5>
                            <p class="text-muted">Giao hàng toàn quốc trong 2-4 ngày, miễn phí với đơn trên 500k</p>
                        </div>
                        <div class="col-md-3 text-center mb-4">
                            <div class="icon-box mb-3">
                                <i class="fas fa-headset fa-3x text-primary"></i>
                            </div>
                            <h5>Hỗ Trợ 24/7</h5>
                            <p class="text-muted">Đội ngũ tư vấn nhiệt tình, hỗ trợ kỹ thuật chuyên sâu</p>
                        </div>
                        <div class="col-md-3 text-center mb-4">
                            <div class="icon-box mb-3">
                                <i class="fas fa-undo-alt fa-3x text-primary"></i>
                            </div>
                            <h5>Đổi Trả Dễ Dàng</h5>
                            <p class="text-muted">Đổi trả trong 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-robot"></i> Gundam Shop</h5>
                    <p class="mt-3">
                        Chuyên cung cấp mô hình Gundam chính hãng Bandai.
                        Cam kết chất lượng, giá tốt nhất thị trường.
                    </p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-youtube fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok fa-lg"></i></a>
                    </div>
                </div>

                <div class="col-md-2">
                    <h5>Danh mục</h5>
                    <ul class="list-unstyled">
                        <?php if ($categories): ?>
                            <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                                <li>
                                    <a href="products.php?category=<?php echo $category['MaDanhMuc']; ?>"
                                        class="text-white-50 text-decoration-none">
                                        <?php echo htmlspecialchars($category['TenDanhMuc']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5>Hỗ trợ</h5>
                    <ul class="list-unstyled">
                        <li><a href="faq.php" class="text-white-50 text-decoration-none">Câu hỏi thường gặp</a></li>
                        <li><a href="shipping.php" class="text-white-50 text-decoration-none">Chính sách vận chuyển</a></li>
                        <li><a href="returns.php" class="text-white-50 text-decoration-none">Chính sách đổi trả</a></li>
                        <li><a href="privacy.php" class="text-white-50 text-decoration-none">Chính sách bảo mật</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Đường ABC, Quận 1, TP.HCM</li>
                        <li class="mt-2"><i class="fas fa-phone me-2"></i> 0909 123 456</li>
                        <li class="mt-2"><i class="fas fa-envelope me-2"></i> info@gundamshop.com</li>
                        <li class="mt-2"><i class="fas fa-clock me-2"></i> 9:00 - 18:00 (T2 - T7)</li>
                    </ul>
                </div>
            </div>

            <hr class="bg-white">

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Gundam Shop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/payment-methods.png"
                        alt="Payment Methods" height="30" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Add to cart with AJAX
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart count
                            document.querySelectorAll('.cart-count').forEach(span => {
                                span.textContent = data.cart_count;
                            });

                            // Show success message
                            alert('Đã thêm vào giỏ hàng!');
                        } else {
                            alert('Có lỗi xảy ra: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
                    });
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>

</html>