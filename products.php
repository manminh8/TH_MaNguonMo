<?php
// products.php - Danh sách sản phẩm
session_start();

// Define base path and URL (Cần thiết cho việc include/liên kết)
define('BASE_PATH', dirname(__FILE__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', BASE_PATH));

require_once 'core/database.php';
require_once 'core/functions.php'; // Nếu có các hàm hữu ích
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

$productModel = new ProductModel($pdo);
$categoryModel = new CategoryModel($pdo);

// Lấy tham số lọc
$category_id = $_GET['category'] ?? null;
$search = $_GET['q'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12; // Số sản phẩm trên mỗi trang

// Xử lý Lọc và Tìm kiếm
$products = [];
if ($category_id) {
    $currentCategory = $categoryModel->getCategoryById($category_id);
    $page_title = 'Sản phẩm theo Danh mục: ' . htmlspecialchars($currentCategory['TenDanhMuc'] ?? 'Không rõ');
    
    // LỖI Ở ĐÂY: Thay vì truyền $category_id vào hàm, coder lại gọi hàm không tham số
    // Điều này khiến hệ thống luôn lấy "Tất cả sản phẩm" dù người dùng đã chọn danh mục.
    $products = $productModel->getActiveProducts();
} elseif ($search) {
    $products = $productModel->searchProducts($search);
    $page_title = 'Kết quả tìm kiếm cho: "' . htmlspecialchars($search) . '"';
} else {
    $products = $productModel->getActiveProducts();
    $page_title = 'Tất cả Sản phẩm';
}

$categories = $categoryModel->getAllCategories();

// Phân trang
$total_products = count($products);
$total_pages = max(1, (int) ceil($total_products / $per_page));
$page = min($page, $total_pages);

$offset = ($page - 1) * $per_page;
$paginated_products = array_slice($products, $offset, $per_page);

// Chuẩn bị URL cơ sở cho phân trang (giữ lại category/search)
$query = [];
if (!empty($category_id)) $query['category'] = $category_id;
if (!empty($search)) $query['q'] = $search;

$base_pagination_url = 'products.php';
$base_pagination_url .= !empty($query) ? ('?' . http_build_query($query)) : '';

$join_page_param = (strpos($base_pagination_url, '?') !== false) ? '&page=' : '?page=';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Gundam Model Shop</title>

    <!-- Bootstrap trước -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS custom sau cùng để override Bootstrap -->
    <link rel="stylesheet" href="./public/assets/css/styles.css">
</head>

<body class="page-products">

<?php
// navigation.php cần được include ở đây.
// Đảm bảo file navigation.php có thể truy cập biến $categories
include 'views/layout/navigation.php';
?>

<main class="py-5">
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

        <div class="row">
            <div class="col-lg-3">
                <!-- THÊM filter-card để CSS đổi sang màu xanh ngọc -->
                <div class="card shadow-sm mb-4 filter-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Lọc theo Danh mục</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="products.php"
                           class="list-group-item list-group-item-action <?php echo is_null($category_id) ? 'active' : ''; ?>">
                            Tất cả Sản phẩm
                        </a>

                        <?php foreach ($categories as $category): ?>
                            <a href="products.php?category=<?php echo $category['MaDanhMuc']; ?>"
                               class="list-group-item list-group-item-action <?php echo $category_id == $category['MaDanhMuc'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['TenDanhMuc']); ?>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9">
                <h1 class="mb-4 display-6"><?php echo $page_title; ?></h1>
                <hr>

                <?php if ($total_products > 0): ?>
                    <p class="text-muted">Hiển thị <?php echo count($paginated_products); ?> trên tổng số <?php echo $total_products; ?> sản phẩm.</p>

                    <div class="row">
                        <?php foreach ($paginated_products as $product): ?>
                            <div class="col-xl-4 col-md-6 col-sm-12 mb-4">
                                <div class="card product-card h-100 shadow-sm">
                                    <?php if (!empty($product['URLAnhChinh'])): ?>
                                        <a href="product.php?id=<?php echo $product['MaSanPham']; ?>">
                                            <img src="<?php echo htmlspecialchars($product['URLAnhChinh']); ?>"
                                                 alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>"
                                                 class="card-img-top product-img-list">
                                        </a>
                                    <?php else: ?>
                                        <img src="assets/images/no-image.jpg"
                                             alt="No image"
                                             class="card-img-top product-img-list">
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">
                                            <a href="product.php?id=<?php echo $product['MaSanPham']; ?>"
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($product['TenSanPham']); ?>
                                            </a>
                                        </h5>

                                        <div class="product-price mb-3">
                                            <span class="fw-bold fs-5 text-primary">
                                                <?php echo number_format($product['GiaBan'], 0, ',', '.') . ' VNĐ'; ?>
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-box me-1"></i>
                                                Còn lại: <?php echo $product['TonKho']; ?> sản phẩm
                                            </small>
                                        </div>

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
                    </div>

                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base_pagination_url . $join_page_param . ($page - 1); ?>" tabindex="-1">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $base_pagination_url . $join_page_param . $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base_pagination_url . $join_page_param . ($page + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>

                <?php else: ?>
                    <div class="alert alert-warning text-center mt-5" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Không tìm thấy sản phẩm nào phù hợp với tiêu chí tìm kiếm/lọc.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'views/layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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
</script>

</body>
</html>
