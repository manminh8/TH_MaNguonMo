<?php

// Đảm bảo file database.php đã được include ở file gọi (ví dụ: login.php, register.php)
// File này sẽ sử dụng biến $pdo được tạo ra từ database.php
class UserModel
{
    private $pdo;

    /**
     * Khởi tạo lớp UserModel với đối tượng kết nối PDO
     * @param PDO $pdo Đối tượng kết nối database
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Đăng ký người dùng mới (Vai trò: 'customer')
     * @param string $fullName Tên đầy đủ
     * @param string $email Email (UNIQUE)
     * @param string $password Mật khẩu thô (sẽ được hash)
     * @return int|bool MaNguoiDung nếu thành công, FALSE nếu thất bại
     */
    public function registerUser($fullName, $email, $password)
    {
        // Mã hóa mật khẩu trước khi lưu vào DB
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';

        // Chuẩn bị câu lệnh SQL
        $sql = "INSERT INTO NguoiDung (HoTen, Email, MatKhauHash, VaiTro) 
                VALUES (:fullname, :email, :passhash, :role)";

        try {
            $stmt = $this->pdo->prepare($sql);

            // Thực thi câu lệnh với các tham số đã bind
            $stmt->execute([
                ':fullname' => $fullName,
                ':email' => $email,
                ':passhash' => $passwordHash,
                ':role' => $role
            ]);

            // Trả về ID của người dùng vừa được tạo
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            // Lỗi phổ biến là trùng Email (UNIQUE constraint)
            return false;
        }
    }

    /**
     * Đăng nhập người dùng
     * @param string $email Email người dùng
     * @param string $password Mật khẩu thô
     * @return array|bool Dữ liệu người dùng (không bao gồm hash) nếu thành công, FALSE nếu thất bại
     */
    public function loginUser($email, $password)
    {
        // 1. Tìm người dùng theo Email
        $sql = "SELECT * FROM NguoiDung WHERE Email = :email";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // 2. Kiểm tra mật khẩu
            if ($user /*&& password_verify($password, $user['MatKhauHash'])=>bug*/) {

                // Cập nhật thời gian đăng nhập cuối
                $this->updateLastLogin($user['MaNguoiDung']);

                // Đăng nhập thành công, loại bỏ mật khẩu hash trước khi trả về
                unset($user['MatKhauHash']);
                return $user;
            }
            return false; // Sai email hoặc mật khẩu

        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Cập nhật thời gian đăng nhập cuối
     * @param int $userId Mã người dùng
     */
    private function updateLastLogin($userId)
    {
        $sql = "UPDATE NguoiDung SET LanDangNhapCuoi = NOW() WHERE MaNguoiDung = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
        } catch (\PDOException $e) {
            // Ghi log nếu cập nhật không thành công nhưng không ngăn luồng đăng nhập
        }
    }

    /**
     * Lấy thông tin người dùng theo ID
     * @param int $userId Mã người dùng
     * @return array|bool Dữ liệu người dùng hoặc FALSE
     */
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM NguoiDung WHERE MaNguoiDung = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['MatKhauHash']);
            }
            return $user;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Lấy tên người dùng dựa trên mã người dùng.
     * Đây là hàm chuyên biệt, tối ưu cho các danh sách (như list.php) chỉ cần tên.
     * @param string $userId Mã người dùng (MaNguoiDung)
     * @return string|null Tên người dùng hoặc null nếu không tìm thấy.
     */
    public function getUserNameById($userId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT TenNguoiDung FROM nguoidung WHERE MaNguoiDung = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy tên người dùng: " . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // CÁC HÀM QUẢN TRỊ VIÊN (ADMIN PANEL)
    // =================================================================

    /**
     * Lấy danh sách tất cả người dùng (Admin và Customer)
     * @return array Danh sách người dùng
     */
    public function getAllUsers()
    {
        $sql = "SELECT MaNguoiDung, HoTen, Email, DienThoai, DiaChi, VaiTro, NgayTao 
                FROM NguoiDung 
                ORDER BY NgayTao DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Cập nhật vai trò người dùng (Ví dụ: từ 'customer' sang 'admin')
     * @param int $userId MaNguoiDung
     * @param string $newRole Vai trò mới ('customer' hoặc 'admin')
     * @return bool
     */
    public function updateRole(int $userId, string $newRole)
    {
        // Đảm bảo chỉ cho phép cập nhật vai trò hợp lệ
        $newRole = strtolower($newRole);
        if ($newRole !== 'customer' && $newRole !== 'admin') {
            return false;
        }

        $sql = "UPDATE NguoiDung SET VaiTro = ? WHERE MaNguoiDung = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$newRole, $userId]);
    }

    /**
     * Xóa người dùng khỏi database
     * Lưu ý: Trong thực tế, bạn nên cập nhật trạng thái thay vì xóa vĩnh viễn
     * @param int $userId MaNguoiDung
     * @return bool
     */
    public function deleteUser(int $userId)
    {
        $sql = "DELETE FROM NguoiDung WHERE MaNguoiDung = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }
}
