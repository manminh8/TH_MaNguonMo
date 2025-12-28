<?php
// Tên file: admin/reports/sales_report.php
$rootPath = __DIR__ . '/../..'; 
require_once __DIR__ . '/../check_admin.php'; 
require_once $rootPath . '/core/database.php';
require_once $rootPath . '/models/ReportModel.php';
require_once $rootPath . '/core/functions.php'; // Cho hàm định dạng tiền tệ

$reportModel = new ReportModel($pdo);

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$limit = 10;

// Lấy dữ liệu báo cáo
$totalRevenue = $reportModel->getTotalRevenue($startDate, $endDate);
$topSelling = $reportModel->getTopSellingProducts($limit);

// Hàm format tiền tệ (giả định có trong core/functions.php)
if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return number_format($amount, 0, ',', '.') . ' đ';
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Doanh số</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Báo cáo Doanh số & Bán chạy</h2>

        <div class="card mb-4">
            <div class="card-header">Bộ lọc Báo cáo</div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">Từ ngày:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate ?? ''); ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">Đến ngày:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate ?? ''); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Xem Báo cáo</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Tổng Doanh Thu (Đã hoàn thành)</h5>
                        <p class="card-text fs-2 fw-bold"><?php echo format_currency($totalRevenue); ?></p>
                        <p class="card-text"><small>Tính theo các đơn hàng có trạng thái 'completed'</small></p>
                    </div>
                </div>
            </div>
        </div>

        <h3>Top <?php echo $limit; ?> Sản phẩm Bán chạy</h3>
        <table class="table table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sản phẩm</th>
                    <th>Tổng Số lượng đã bán</th>
                    <th>Tổng Doanh thu từ SP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topSelling)): ?>
                    <tr><td colspan="4" class="text-center">Chưa có dữ liệu bán hàng hoàn thành.</td></tr>
                <?php else: ?>
                    <?php foreach ($topSelling as $item): ?>
                        <tr>
                            <td><?php echo $item['MaSanPham']; ?></td>
                            <td><?php echo htmlspecialchars($item['TenSanPham']); ?></td>
                            <td><?php echo number_format($item['TotalSold']); ?></td>
                            <td><?php echo format_currency($item['TotalRevenueFromProduct']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>