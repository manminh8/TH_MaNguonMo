<?php
// Tên file: models/OrderModel.php

class OrderModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Tạo đơn hàng mới trong bảng donhang.
     * @param array $data Thông tin đơn hàng (gồm MaNguoiDung, TenKhachHang, EmailKhachHang, SDTNhanHang, DiaChiGiaoHang, PhuongThucTT).
     * @param decimal $total Tổng giá trị đơn hàng (TongGia).
     * @return int|bool Mã đơn hàng mới nếu thành công, False nếu thất bại.
     */
    public function createOrder($data, $total)
    {
        // Cập nhật: Sử dụng tên cột và trạng thái đã thống nhất
        $sql = "INSERT INTO donhang (
                    MaNguoiDung, TenKhachHang, EmailKhachHang, SDTNhanHang, DiaChiGiaoHang, TongGia, PhuongThucThanhToan, TrangThai, NgayTao
                ) VALUES (
                    :manguoidung, :tenkhachhang, :emailkhachhang, :sdth, :diachi, :tonggia, :pttt, 'pending', NOW()
                )";
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Liên kết các tham số mới
            $stmt->bindValue(':manguoidung', $data['MaNguoiDung'] ?? null, PDO::PARAM_INT); 
            $stmt->bindParam(':tenkhachhang', $data['HoTen']); // Lấy từ $_SESSION['order_data']['HoTen']
            $stmt->bindParam(':emailkhachhang', $data['Email']); // Lấy từ $_SESSION['order_data']['Email']
            
            // Các trường đã có sẵn
            $stmt->bindParam(':sdth', $data['SDTNhanHang']);
            $stmt->bindParam(':diachi', $data['DiaChiGiaoHang']);
            $stmt->bindParam(':tonggia', $total);
            $stmt->bindParam(':pttt', $data['PhuongThucThanhToan']);
            
            $stmt->execute();

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // Log lỗi $e->getMessage()
            return false;
        }
    }

    /**
     * Lưu chi tiết các mặt hàng vào bảng chitietdonhang. (GIỮ NGUYÊN)
     * @param int $orderId Mã đơn hàng.
     * @param array $cartItems Danh sách sản phẩm trong giỏ hàng.
     * @return bool True nếu thành công, False nếu thất bại.
     */
    public function createOrderDetails($orderId, $cartItems)
    {
        // Logic Transaction và INSERT vào chitietdonhang giữ nguyên
        $sql = "INSERT INTO chitietdonhang (MaDonHang, MaSanPham, SoLuong, GiaBan) 
                 VALUES (:madonhang, :masp, :soluong, :giaban)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $this->pdo->beginTransaction();
            foreach ($cartItems as $item) {
                $stmt->bindParam(':madonhang', $orderId);
                // Giả định $item có các khóa 'MaSanPham', 'SoLuong', 'GiaBan'
                $stmt->bindParam(':masp', $item['id']); // Có thể là $item['MaSanPham']
                $stmt->bindParam(':soluong', $item['quantity']); // Có thể là $item['SoLuong']
                $stmt->bindParam(':giaban', $item['price']); // Có thể là $item['GiaBan']
                $stmt->execute();
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Log lỗi
            return false;
        }
    }


    /**
     * Lấy tất cả đơn hàng, bao gồm thông tin khách hàng (Guest hoặc User).
     * @return array Danh sách đơn hàng.
     */
    public function getAllOrders()
    {
        // Cập nhật: Thêm LEFT JOIN với nguoidung để lấy HoTen và Email (nếu có MaNguoiDung)
        $sql = "SELECT 
                    d.*, 
                    n.HoTen AS HoTenNguoiDung, 
                    n.Email AS EmailNguoiDung
                FROM 
                    donhang d
                LEFT JOIN 
                    nguoidung n ON d.MaNguoiDung = n.MaNguoiDung
                ORDER BY 
                    d.NgayTao DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết một đơn hàng bao gồm danh sách sản phẩm và thông tin người dùng.
     * @param int $orderId Mã đơn hàng.
     * @return array|bool Thông tin đơn hàng và chi tiết sản phẩm.
     */
    public function getOrderDetails($orderId)
    {
        // 1. Lấy thông tin chung của đơn hàng, kết hợp với thông tin người dùng (nếu có MaNguoiDung)
        $sqlOrder = "SELECT 
                        d.*,
                        n.HoTen AS HoTenNguoiDung, 
                        n.Email AS EmailNguoiDung
                     FROM 
                        donhang d
                     LEFT JOIN 
                        nguoidung n ON d.MaNguoiDung = n.MaNguoiDung
                     WHERE 
                        d.MaDonHang = :id";
        $stmtOrder = $this->pdo->prepare($sqlOrder);
        $stmtOrder->bindParam(':id', $orderId, PDO::PARAM_INT);
        $stmtOrder->execute();
        $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // 2. Lấy chi tiết sản phẩm (Giữ nguyên logic JOIN sanpham)
        $sqlItems = "SELECT 
                         ct.SoLuong, ct.GiaBan, 
                         sp.TenSanPham, sp.MaSanPham, sp.URLAnhChinh
                     FROM chitietdonhang ct
                     JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham
                     WHERE ct.MaDonHang = :id";
        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->bindParam(':id', $orderId, PDO::PARAM_INT);
        $stmtItems->execute();
        $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ghép chi tiết vào đơn hàng
        $order['items'] = $orderItems;
        return $order;
    }

    /**
     * Cập nhật trạng thái đơn hàng. (GIỮ NGUYÊN)
     * @param int $orderId Mã đơn hàng.
     * @param string $status Trạng thái mới (ví dụ: pending, processing, shipped, completed, cancelled).
     * @return bool
     */
    public function updateOrderStatus($orderId, $status)
    {
        $sql = "UPDATE donhang SET TrangThai = :status WHERE MaDonHang = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>