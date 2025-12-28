<?php
// Tên file: core/functions.php

/**
 * Xử lý tải lên tệp tin và lưu vào thư mục đích an toàn (Dùng kiểm tra đuôi mở rộng thay vì finfo).
 *
 * @param array $fileInfo Thông tin file từ $_FILES['ten_input_file']
 * @param string $targetDir Đường dẫn tuyệt đối đến thư mục lưu trữ 
 * @param array $allowedTypes Mảng các MIME type được phép (ví dụ: ['image/jpeg', 'image/png'])
 * @param int $maxSize Kích thước tối đa cho phép (byte)
 * @return string|bool Tên file mới (ví dụ: 'sanpham_12345.jpg') hoặc FALSE nếu thất bại
 */
function handleFileUpload(array $fileInfo, string $targetDir, array $allowedTypes, int $maxSize = 5000000) {
    if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
        return false; // Lỗi upload cơ bản
    }

    // 1. Kiểm tra kích thước file
    if ($fileInfo['size'] > $maxSize) {
        return false;
    }
    
    // ----------------------------------------------------------------------
    // 2. KIỂM TRA MIME TYPE (THAY THẾ PHƯƠNG PHÁP finfo_open() BẰNG ĐUÔI FILE)
    // LƯU Ý: Phương pháp này kém bảo mật hơn nhưng giải quyết được lỗi WAMPP hiện tại.
    $validExtensions = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif'
    ];
    
    $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    
    $fileMimeType = $validExtensions[$extension] ?? null;
    
    // Kiểm tra: Đuôi file có hợp lệ VÀ MIME type tương ứng có trong $allowedTypes không
    if (!in_array($fileMimeType, $allowedTypes)) {
        return false; 
    }
    // ----------------------------------------------------------------------

    // 3. Tạo tên file mới duy nhất
    $newFileName = uniqid('gundam_') . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $newFileName;

    // 4. Di chuyển file từ thư mục tạm thời đến thư mục đích
    if (!move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
        return false;
    }

    // Trả về tên file đã được lưu
    return $newFileName;
}

// ... Có thể thêm các hàm khác vào đây
?>