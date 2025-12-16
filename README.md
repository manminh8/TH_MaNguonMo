# Cấu Trúc Dự Án Quản Lý Thư Viện

## Tổng quan
Đây là cấu trúc thư mục của dự án quản lý thư viện sử dụng PHP thuần.

## Cấu trúc thư mục

```
XayDungWebBanMoHinh/
├── admin/                      # Trang quản trị
│   ├── categories/             # Quản lý danh mục
│   │   └── add.php
│   ├── orders/                 # Quản lý đơn hàng
│   │   ├── detail.php
│   │   └── list.php
│   ├── products/               # Quản lý sản phẩm
│   │   ├── add.php
│   │   ├── delete.php
│   │   ├── edit.php
│   │   └── list.php
│   ├── reports/                # Báo cáo thống kê
│   │   └── sales_report.php
│   ├── users/                  # Quản lý người dùng
│   │   └── list.php
│   ├── check_admin.php
│   └── index.php
│
├── core/                       # Các file cốt lõi
│   ├── public/assets/
│   ├── database.php            
│   ├── functions.php           
│   └── logout.php
│
├── models/                     # Lớp Model
│   ├── CategoryModel.php
│   ├── OrderModel.php
│   ├── ProductModel.php
│   ├── ReportModel.php
│   └── UserModel.php
│
├── public/assets/              # Tài nguyên công khai
│   ├── css/
│   └── images/
│
├── views/                      # Giao diện
│   ├── auth/                   
│   └── layout/                 
│
├── index.php                   # Trang chủ
├── product.php                 # Chi tiết sản phẩm
├── products.php                # Danh sách sản phẩm
├── cart.php                    # Trang giỏ hàng
├── add_to_cart.php             # Thêm vào giỏ hàng
├── update_cart.php             # Cập nhật giỏ hàng
├── checkout.php                # Thanh toán
├── order_confirmation.php      # Xác nhận đơn hàng
└── logout.php                  # Đăng xuất
```