<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-robot"></i> Gundam Shop
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="fas fa-box"></i> Sản phẩm
                    </a>
                </li>

                <?php if ($categories): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-list"></i> Danh mục
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a class="dropdown-item" href="products.php?category=<?php echo $category['MaDanhMuc']; ?>">
                                        <?php echo htmlspecialchars($category['TenDanhMuc']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Search Form -->
            <form class="d-flex me-3" action="search.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" placeholder="Tìm kiếm sản phẩm...">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Cart & User Account -->
            <div class="d-flex align-items-center">
                <a href="cart.php" class="btn btn-outline-light me-2 position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                    </span>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Đã đăng nhập - Hiển thị dropdown tài khoản -->
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? 'Tài khoản'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="admin/index.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Quản trị
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Tài khoản của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="orders.php">
                                    <i class="fas fa-receipt me-2"></i>Đơn hàng của tôi
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Chưa đăng nhập - Hiển thị nút đăng nhập/đăng ký -->
                    <a href="views/auth/login.php" class="btn btn-outline-success me-2">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                    <a href="views/auth/register.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Đăng ký
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>