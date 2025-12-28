<?php
// Tên file: models/ProductModel.php
// Vai trò: Xử lý tất cả các thao tác CRUD liên quan đến Sản phẩm, Danh mục và Ảnh

class ProductModel
{
    private $pdo;

    /**
     * Khởi tạo ProductModel với đối tượng kết nối cơ sở dữ liệu PDO.
     * @param PDO $pdo Đối tượng kết nối DB đã được khởi tạo.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ==========================================================
    //                        PHẦN 1: QUẢN LÝ DANH MỤC
    // ==========================================================

    /**
     * Lấy tất cả danh mục (dùng cho dropdown khi thêm/sửa sản phẩm)
     * @return array Danh sách danh mục
     */
    public function getAllCategories()
    {
        $sql = "SELECT * FROM DanhMuc ORDER BY TenDanhMuc ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Thêm Danh mục mới
     */
    public function addCategory($name, $description = null)
    {
        $sql = "INSERT INTO DanhMuc (TenDanhMuc, MoTa) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $description]);
    }

    // ==========================================================
    //                        PHẦN 2: QUẢN LÝ SẢN PHẨM (CREATE/READ)
    // ==========================================================

    /**
     * Lấy tất cả sản phẩm (dùng cho admin/products/list.php)
     * @return array Danh sách sản phẩm kèm theo tên danh mục
     */
    public function getAllProducts()
    {
        // Sử dụng JOIN để lấy tên danh mục từ bảng DanhMuc
        $sql = "SELECT p.*, d.TenDanhMuc 
                FROM SanPham p
                JOIN DanhMuc d ON p.MaDanhMuc = d.MaDanhMuc 
                ORDER BY p.NgayTao DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Thêm sản phẩm mới (CREATE) - Xử lý Transaction cho nhiều bảng
     * @param array $data Dữ liệu sản phẩm (không bao gồm ảnh phụ)
     * @param array $extraImageUrls Mảng các URL tương đối của ảnh phụ
     * @return int|bool MaSanPham mới hoặc FALSE nếu thất bại
     */
    public function addProduct($data, $extraImageUrls = [])
    {
        $this->pdo->beginTransaction();

        try {
            // 1. CHÈN VÀO BẢNG SanPham
            $sql = "INSERT INTO SanPham (MaDanhMuc, TenSanPham, MoTa, GiaBan, TonKho, URLAnhChinh, TrangThai) 
                    VALUES (:ma_dm, :ten_sp, :mo_ta, :gia_ban, :ton_kho, :url_chinh, :trang_thai)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'ma_dm'     => $data['MaDanhMuc'],
                'ten_sp'    => $data['TenSanPham'],
                'mo_ta'     => $data['MoTa'],
                'gia_ban'   => $data['GiaBan'],
                'ton_kho'   => $data['TonKho'],
                'url_chinh' => $data['URLAnhChinh'],
                'trang_thai' => $data['TrangThai'] ?? 'active'
            ]);

            $newProductId = $this->pdo->lastInsertId();

            // 2. CHÈN VÀO BẢNG AnhSanPham (nếu có ảnh phụ)
            if ($newProductId && !empty($extraImageUrls)) {
                $this->addExtraImages($newProductId, $extraImageUrls);
            }

            $this->pdo->commit();
            return $newProductId;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // In lỗi ra log để debug
            // error_log("Lỗi thêm sản phẩm: " . $e->getMessage()); 
            return false;
        }
    }

    
    // ==========================================================
    //                        PHẦN 3: QUẢN LÝ ẢNH PHỤ
    // ==========================================================

    /**
     * Thêm các URL ảnh phụ vào bảng AnhSanPham
     * @param int $productId MaSanPham của sản phẩm
     * @param array $urls Mảng các URL tương đối của ảnh
     * @return bool TRUE nếu thành công, FALSE nếu thất bại
     */
    public function addExtraImages(int $productId, array $urls)
    {
        if (empty($urls)) {
            return true;
        }

        $sqlImages = "INSERT INTO AnhSanPham (MaSanPham, URLAnh) VALUES (?, ?)";
        $stmtImages = $this->pdo->prepare($sqlImages);

        foreach ($urls as $url) {
            if (!$stmtImages->execute([$productId, $url])) {
                return false;
            }
        }
        return true;
    }


    /**
     * Xóa sản phẩm theo MaSanPham
     * @param int $productId MaSanPham cần xóa
     * @return array|bool Dữ liệu sản phẩm (để xóa ảnh) hoặc FALSE nếu thất bại
     */
    public function deleteProduct(int $productId)
    {
        $this->pdo->beginTransaction();

        try {
            // 1. Lấy thông tin ảnh chính trước khi xóa (để xóa file)
            $sqlSelect = "SELECT URLAnhChinh FROM SanPham WHERE MaSanPham = ?";
            $stmtSelect = $this->pdo->prepare($sqlSelect);
            $stmtSelect->execute([$productId]);
            $productInfo = $stmtSelect->fetch();

            if (!$productInfo) {
                $this->pdo->rollBack();
                return false; // Không tìm thấy sản phẩm
            }

            // 2. Xóa tất cả ảnh phụ liên quan (AnhSanPham)
            $sqlDeleteImages = "DELETE FROM AnhSanPham WHERE MaSanPham = ?";
            $stmtDeleteImages = $this->pdo->prepare($sqlDeleteImages);
            $stmtDeleteImages->execute([$productId]);

            // 3. Xóa sản phẩm khỏi bảng SanPham
            $sqlDeleteProduct = "DELETE FROM SanPham WHERE MaSanPham = ?";
            $stmtDeleteProduct = $this->pdo->prepare($sqlDeleteProduct);
            $stmtDeleteProduct->execute([$productId]);

            $this->pdo->commit();
            return $productInfo; // Trả về thông tin ảnh để xóa file vật lý

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // error_log("Lỗi xóa sản phẩm: " . $e->getMessage()); 
            return false;
        }
    }


    /**
     * Lấy thông tin chi tiết của một sản phẩm dựa trên ID.
     * Đồng thời lấy tên Danh mục.
     * @param int $productId MaSanPham cần lấy.
     * @return array|false Dữ liệu sản phẩm hoặc FALSE nếu không tìm thấy.
     */
    public function getProductById(int $productId)
    {
        // JOIN với bảng DanhMuc để lấy tên danh mục
        $sql = "SELECT s.*, d.TenDanhMuc FROM SanPham s 
            JOIN DanhMuc d ON s.MaDanhMuc = d.MaDanhMuc
            WHERE s.MaSanPham = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }


    /**
     * Cập nhật thông tin sản phẩm.
     * @param int $id MaSanPham cần cập nhật.
     * @param array $data Mảng dữ liệu mới.
     * @return bool TRUE nếu cập nhật thành công, FALSE nếu thất bại.
     */
    public function updateProduct(int $id, array $data)
    {
        // Chỉ lấy các cột hợp lệ cho việc cập nhật (Tránh update LuotXem, NgayTao...)
        $fields = ['MaDanhMuc', 'TenSanPham', 'GiaBan', 'MoTa', 'TonKho', 'URLAnhChinh', 'TrangThai'];
        $setClauses = [];
        $values = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return false; // Không có gì để cập nhật
        }

        $sql = "UPDATE SanPham SET " . implode(', ', $setClauses) . " WHERE MaSanPham = ?";
        $values[] = $id;


        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Lấy sản phẩm để hiển thị trên trang chủ
     * Chỉ lấy sản phẩm có TrangThai = 'active'
     * @param int $limit Số lượng sản phẩm muốn lấy
     * @return array
     */
    public function getProductsForHomepage(int $limit)
    {
        $sql = "SELECT MaSanPham, TenSanPham, GiaBan, TonKho, TrangThai, URLAnhChinh 
                FROM SanPham 
                WHERE TrangThai = 'active' 
                ORDER BY NgayTao DESC 
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách sản phẩm (có thể lọc theo danh mục)
     * Chỉ lấy sản phẩm có TrangThai = 'active'
     * @param int|null $categoryId Mã danh mục để lọc (null nếu lấy tất cả)
     * @return array
     */
    public function getActiveProducts(?int $categoryId = null)
    {
        $sql = "SELECT MaSanPham, TenSanPham, GiaBan, URLAnhChinh, TonKho, TrangThai
            FROM SanPham 
            WHERE TrangThai = 'active'";
        $params = [];

        if ($categoryId) {
            $sql .= " AND MaDanhMuc = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY NgayTao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Lấy tất cả thông tin chi tiết của một sản phẩm theo ID
     * @param int $productId Mã sản phẩm
     * @return array|false
     */
    public function getProductDetails(int $productId)
    {
        // Chúng ta lấy tất cả các trường cần thiết, bao gồm cả mô tả
        $sql = "SELECT sp.*, dm.TenDanhMuc 
            FROM SanPham sp
            JOIN DanhMuc dm ON sp.MaDanhMuc = dm.MaDanhMuc
            WHERE sp.MaSanPham = ? AND sp.TrangThai = 'active'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);

        return $stmt->fetch();
    }

    /**
     * [Tùy chọn] Lấy danh sách ảnh phụ của sản phẩm (nếu bạn đã có bảng AnhSanPham)
     * @param int $productId Mã sản phẩm
     * @return array
     */
    public function getProductImages(int $productId)
    {
        // Giả định bạn có bảng AnhSanPham với cột URLAnh
        $sql = "SELECT URLAnh FROM AnhSanPham WHERE MaSanPham = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Giảm số lượng tồn kho của sản phẩm sau khi đặt hàng.
     * @param int $productId Mã sản phẩm.
     * @param int $quantity Số lượng cần giảm.
     * @return bool True nếu cập nhật thành công, False nếu thất bại.
     */
    public function decreaseStock($productId, $quantity)
    {
        $sql = "UPDATE sanpham SET TonKho = TonKho - :quantity WHERE MaSanPham = :id AND TonKho >= :quantity";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();

            // Kiểm tra xem có hàng nào bị ảnh hưởng không
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Ghi log lỗi nếu cần thiết
            return false;
        }
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public function searchProducts($keyword)
    {
        $sql = "SELECT * FROM SanPham 
            WHERE (TenSanPham LIKE :keyword OR MoTa LIKE :keyword) 
            AND TrangThai = 'active' 
            ORDER BY NgayTao DESC";

        $stmt = $this->pdo->prepare($sql);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViewCount($product_id)
    {
        $sql = "UPDATE SanPham SET LuotXem = LuotXem + 1 WHERE MaSanPham = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$product_id]);
    }

    /**
     * Lấy sản phẩm bán chạy
     */
    public function getBestSellingProducts($limit = 8)
    {
        $sql = "SELECT p.*, SUM(ct.SoLuong) as total_sold
            FROM SanPham p
            LEFT JOIN ChiTietDonHang ct ON p.MaSanPham = ct.MaSanPham
            LEFT JOIN DonHang dh ON ct.MaDonHang = dh.MaDonHang
            WHERE p.TrangThai = 'active'
            GROUP BY p.MaSanPham
            ORDER BY total_sold DESC, p.NgayTao DESC
            LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    // ... Các hàm getProductById, updateProduct, deleteProduct sẽ được thêm sau ...
}
