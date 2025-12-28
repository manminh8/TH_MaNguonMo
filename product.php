<?php
// product.php - Chi tiết sản phẩm
session_start();

require_once 'core/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id) { header('Location: index.php'); exit; }

$productModel = new ProductModel($pdo);
$categoryModel = new CategoryModel($pdo);

$categories = $categoryModel->getAllCategories(); // để navigation đồng bộ

$product = $productModel->getProductDetails($product_id);

if (!$product || $product['TrangThai'] !== 'active') {
    header('Location: index.php');
    exit;
}

// Related products
$related_products = $productModel->getActiveProducts($product['MaDanhMuc']);
$related_products = array_filter($related_products, function ($p) use ($product_id) {
    return $p['MaSanPham'] != $product_id;
});
$related_products = array_slice($related_products, 0, 4);

// Increment view count
$productModel->incrementViewCount($product_id);

// FIX UI bug: điều kiện giá gốc/giá bán (chỉ hiển thị nếu có giảm)
$has_discount = !empty($product['GiaGoc']) && ($product['GiaGoc'] > $product['GiaBan']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['TenSanPham']); ?> - Chi tiết sản phẩm</title>

  <link rel="stylesheet" href="./public/assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="page-product-detail">

<?php include 'views/layout/navigation.php'; ?>

<main class="py-5">
  <div class="container">
    <div class="page-frame">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
          <li class="breadcrumb-item">
            <a href="products.php?category=<?php echo $product['MaDanhMuc']; ?>">
              <?php echo htmlspecialchars($product['TenDanhMuc'] ?? 'Danh mục'); ?>
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['TenSanPham']); ?></li>
        </ol>
      </nav>

      <!-- Product Detail -->
      <div class="row g-4">
        <!-- Images -->
        <div class="col-lg-6">
          <div class="card mb-3">
            <div class="card-body text-center">
              <?php if (!empty($product['URLAnhChinh'])): ?>
                <img src="<?php echo htmlspecialchars($product['URLAnhChinh']); ?>"
                     alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>"
                     class="img-fluid"
                     id="main-product-image">
              <?php else: ?>
                <img src="assets/images/no-image.jpg" alt="No image" class="img-fluid rounded">
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($product['URLAnhChinh'])): ?>
            <div class="row product-gallery g-2">
              <div class="col-3">
                <img src="<?php echo htmlspecialchars($product['URLAnhChinh']); ?>"
                     class="img-thumbnail w-100"
                     onclick="changeMainImage(this.src)">
              </div>
              <!-- thêm thumbnail khác nếu có -->
            </div>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h1 class="h2 mb-2"><?php echo htmlspecialchars($product['TenSanPham']); ?></h1>

              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="pd-price"><?php echo number_format($product['GiaBan'], 0, ',', '.'); ?> ₫</span>

                <?php if ($has_discount): ?>
                  <span class="pd-old-price ms-2"><?php echo number_format($product['GiaGoc'], 0, ',', '.'); ?> ₫</span>
                  <span class="pd-discount">
                    -<?php echo number_format((($product['GiaGoc'] - $product['GiaBan']) / $product['GiaGoc']) * 100, 0); ?>%
                  </span>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <div class="mb-2 text-muted">
                  <i class="fas fa-eye me-1"></i>
                  <?php echo number_format($product['LuotXem']); ?> lượt xem
                </div>
                <div class="mb-2 text-muted">
                  <i class="fas fa-box me-1"></i>
                  Mã SP: <?php echo htmlspecialchars($product['MaSanPham']); ?>
                </div>

                <?php if ($product['TonKho'] > 0): ?>
                  <div class="text-success fw-bold">
                    <i class="fas fa-check-circle me-1"></i>
                    Còn hàng (<?php echo $product['TonKho']; ?> sản phẩm)
                  </div>
                <?php else: ?>
                  <div class="text-danger fw-bold">
                    <i class="fas fa-times-circle me-1"></i>
                    Tạm hết hàng
                  </div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <h5 class="mb-2">Mô tả ngắn</h5>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($product['MoTa'])); ?></p>
              </div>

              <?php if ($product['TonKho'] > 0): ?>
                <form id="add-to-cart-form" method="POST">
                  <input type="hidden" name="product_id" value="<?php echo $product['MaSanPham']; ?>">
                  <input type="hidden" name="quantity" id="quantity_input" value="1">

                  <div class="row g-3 align-items-center mb-3">
                    <div class="col-auto"><label class="form-label mb-0 fw-bold">Số lượng:</label></div>
                    <div class="col-auto">
                      <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">-</button>

                        <input type="number"
                          id="quantity_display"
                          class="form-control quantity-input"
                          value="1"
                          min="1"
                          max="<?php echo $product['TonKho']; ?>"
                          oninput="updateQuantityHidden()">

                        <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity()">+</button>
                      </div>
                    </div>
                    <div class="col-auto">
                      <span class="text-muted">Tối đa: <?php echo $product['TonKho']; ?> sản phẩm</span>
                    </div>
                  </div>

                  <div class="d-grid gap-2 d-md-flex">
                    <button type="submit" class="btn btn-danger btn-lg flex-fill">
                      <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ hàng
                    </button>

                    <button type="button" class="btn btn-outline-primary btn-lg flex-fill">
                      <i class="fas fa-credit-card me-2"></i> Mua ngay
                    </button>
                  </div>
                </form>
              <?php else: ?>
                <div class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  Sản phẩm đang tạm hết hàng. Vui lòng quay lại sau!
                </div>
              <?php endif; ?>

              <div class="border-top pt-3 mt-3">
                <h6 class="mb-2 fw-bold">Chia sẻ</h6>
                <div class="d-flex gap-2">
                  <a href="#" class="btn btn-outline-primary btn-sm"><i class="fab fa-facebook-f"></i></a>
                  <a href="#" class="btn btn-outline-info btn-sm"><i class="fab fa-twitter"></i></a>
                  <a href="#" class="btn btn-outline-danger btn-sm"><i class="fab fa-pinterest"></i></a>
                  <a href="#" class="btn btn-outline-success btn-sm"><i class="fab fa-whatsapp"></i></a>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="row mt-5">
        <div class="col-12">
          <div class="card">
            <div class="card-header bg-transparent">
              <ul class="nav nav-tabs card-header-tabs" id="productTabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#description">Mô tả chi tiết</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#specifications">Thông số kỹ thuật</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#reviews">Đánh giá</a></li>
              </ul>
            </div>

            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade show active" id="description">
                  <div class="product-description">
                    <?php echo !empty($product['MoTaChiTiet']) ? $product['MoTaChiTiet'] : 'Đang cập nhật...'; ?>
                  </div>
                </div>

                <div class="tab-pane fade" id="specifications">
                  <?php if (!empty($product['ThongSoKyThuat'])): ?>
                    <?php echo $product['ThongSoKyThuat']; ?>
                  <?php else: ?>
                    <p class="text-muted">Đang cập nhật thông tin...</p>
                  <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="reviews">
                  <div class="text-center py-4">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="fw-bold">Chưa có đánh giá nào</h5>
                    <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                    <button class="btn btn-primary">Viết đánh giá</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related -->
      <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
          <div class="col-12">
            <h3 class="mb-4 fw-bold">Sản phẩm liên quan</h3>
            <div class="row">
              <?php foreach ($related_products as $related): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                  <div class="card related-product-card h-100">
                    <a href="product.php?id=<?php echo $related['MaSanPham']; ?>">
                      <?php if (!empty($related['URLAnhChinh'])): ?>
                        <img src="<?php echo htmlspecialchars($related['URLAnhChinh']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($related['TenSanPham']); ?>">
                      <?php else: ?>
                        <img src="assets/images/no-image.jpg" class="card-img-top" alt="No image">
                      <?php endif; ?>
                    </a>

                    <div class="card-body">
                      <h6 class="fw-bold mb-2">
                        <a href="product.php?id=<?php echo $related['MaSanPham']; ?>" class="text-decoration-none">
                          <?php echo htmlspecialchars(mb_substr($related['TenSanPham'], 0, 50) . (mb_strlen($related['TenSanPham']) > 50 ? '...' : '')); ?>
                        </a>
                      </h6>

                      <div class="fw-bold" style="color: var(--accent);">
                        <?php echo number_format($related['GiaBan'], 0, ',', '.'); ?> ₫
                      </div>
                    </div>

                    <div class="card-footer bg-transparent">
                      <a href="product.php?id=<?php echo $related['MaSanPham']; ?>" class="btn btn-outline-secondary btn-sm w-100">
                        Xem chi tiết
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div><!-- /page-frame -->
  </div>
</main>

<?php include 'views/layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function changeMainImage(src){ document.getElementById('main-product-image').src = src; }

function updateQuantityHidden(){
  const display = document.getElementById("quantity_display");
  const hidden  = document.getElementById("quantity_input");
  if (parseInt(display.value) < 1) display.value = 1;
  hidden.value = display.value;
}
function increaseQuantity(){
  const input = document.getElementById('quantity_display');
  const max = parseInt(input.max);
  if (parseInt(input.value) < max){
    input.value = parseInt(input.value) + 1;
    updateQuantityHidden();
  }
}
function decreaseQuantity(){
  const input = document.getElementById('quantity_display');
  if (parseInt(input.value) > 1){
    input.value = parseInt(input.value) - 1;
    updateQuantityHidden();
  }
}

document.getElementById("add-to-cart-form")?.addEventListener("submit", function(e){
  e.preventDefault();
  const formData = new FormData(this);

  fetch("add_to_cart.php", { method:"POST", body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.success){
        document.querySelectorAll('.cart-count').forEach(span => span.textContent = data.cart_count);
        alert("Đã thêm vào giỏ hàng!");
        window.location.href = "cart.php";
      } else {
        alert(data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert("Có lỗi xảy ra khi thêm vào giỏ hàng");
    });
});
</script>

</body>
</html>
