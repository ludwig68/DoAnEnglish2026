# Hướng Dẫn Cài Đặt Source Code Dự Án DoAnEnglish2026

Tài liệu này hướng dẫn các bước cài đặt và chạy dự án **DoAnEnglish2026** trên môi trường local. File sử dụng tiếng Việt có dấu theo mã hóa **UTF-8**; nếu bị lỗi font, hãy mở bằng VS Code và chọn `Reopen with Encoding` → `UTF-8`.

---

## 1. Tổng Quan Công Nghệ

- **Backend:** PHP, PDO, MySQL
- **Frontend:** Vue 3, Vite, Tailwind CSS
- **Database:** MySQL, file import `database/learning_english.sql`
- **Web server khuyến nghị:** WAMP hoặc XAMPP

---

## 2. Yêu Cầu Hệ Thống

Cần cài đặt sẵn các phần mềm sau:

- WAMP Server hoặc XAMPP có PHP và MySQL
- Node.js và npm
- Git
- Trình duyệt web: Chrome, Edge hoặc Firefox

Phiên bản tham khảo theo source hiện tại:

- PHP: 8.x
- MySQL: 8.x hoặc tương đương
- Node.js: 18.x trở lên
- npm: 9.x trở lên

Kiểm tra Node.js và npm:

```bash
node -v
npm -v
```

---

## 3. Tải Source Code Về Máy

Mở terminal/cmd và di chuyển vào thư mục web server.

Nếu dùng WAMP:

```bash
cd C:\wamp64\www
```

Nếu dùng XAMPP:

```bash
cd C:\xampp\htdocs
```

Clone source code:

```bash
git clone https://github.com/ludwig68/DoAnEnglish2026.git
```

Di chuyển vào thư mục dự án:

```bash
cd DoAnEnglish2026
```

Nếu đã có source code sẵn, chỉ cần copy cả thư mục `DoAnEnglish2026` vào:

- WAMP: `C:\wamp64\www\DoAnEnglish2026`
- XAMPP: `C:\xampp\htdocs\DoAnEnglish2026`

---

## 4. Khởi Động WAMP/XAMPP

1. Mở WAMP hoặc XAMPP Control Panel.
2. Khởi động Apache.
3. Khởi động MySQL.
4. Đảm bảo Apache và MySQL đang chạy thành công.

Với WAMP, biểu tượng WAMP nên chuyển sang màu xanh.

---

## 5. Tạo Và Import Database

### Cách 1: Import bằng phpMyAdmin

1. Mở trình duyệt và truy cập:

```text
http://localhost/phpmyadmin
```

2. Tạo database mới tên:

```text
learning_english
```

3. Chọn database `learning_english`.
4. Vào tab **Import**.
5. Chọn file:

```text
database/learning_english.sql
```

6. Bấm **Import** để nạp dữ liệu.

### Cách 2: Import bằng dòng lệnh

Nếu máy đã cấu hình lệnh `mysql`, chạy:

```bash
mysql -u root -p learning_english < database/learning_english.sql
```

Nếu tài khoản MySQL root không có mật khẩu, có thể dùng:

```bash
mysql -u root learning_english < database/learning_english.sql
```

---

## 6. Cấu Hình Kết Nối Database Backend

Mở file:

```text
backend/config/db.php
```

Kiểm tra các thông tin kết nối:

```php
$host = 'localhost';
$db   = 'learning_english';
$user = 'root';
$pass = '';
```

Mặc định source đang cấu hình cho WAMP/XAMPP:

- Host: `localhost`
- Database: `learning_english`
- User: `root`
- Password: rỗng

Nếu MySQL của bạn có mật khẩu, sửa biến `$pass` cho đúng.

Ví dụ:

```php
$pass = 'mat_khau_mysql_cua_ban';
```

---

## 7. Cấu Hình JWT

Backend đọc mã bảo mật JWT theo thứ tự:

1. Biến môi trường `JWT_SECRET`
2. File `backend/config/jwt.local.php`
3. File `backend/config/.jwt_secret`

Nếu chạy local, có thể để mặc định. Hệ thống sẽ đọc hoặc tự tạo file `.jwt_secret` nếu cần.

Nếu muốn cấu hình thủ công, tạo file:

```text
backend/config/jwt.local.php
```

Nội dung mẫu:

```php
<?php

return [
    'secret' => 'thay_bang_chuoi_bao_mat_dai_va_ngau_nhien'
];
```

Không nên commit file `jwt.local.php` lên repository công khai.

---

## 8. Cài Đặt Frontend

Di chuyển vào thư mục frontend:

```bash
cd frontend
```

Cài đặt thư viện:

```bash
npm install
```

File môi trường development hiện tại:

```text
frontend/.env.development
```

Nội dung:

```env
VITE_API_BASE_URL=http://localhost/DoAnEnglish2026/backend/api
```

Nếu bạn đặt source ở thư mục khác, hãy sửa lại đường dẫn API cho đúng.

---

## 9. Chạy Dự Án Ở Môi Trường Local

### 9.1. Chạy backend

Backend PHP chạy thông qua Apache của WAMP/XAMPP, không cần chạy lệnh riêng.

Kiểm tra backend có truy cập được không bằng đường dẫn:

```text
http://localhost/DoAnEnglish2026/backend/api
```

Tùy từng API, trình duyệt có thể hiển thị JSON, thông báo lỗi method hoặc lỗi thiếu tham số. Điều này vẫn có nghĩa là Apache đã trỏ được vào backend.

### 9.2. Chạy frontend

Trong thư mục `frontend`, chạy:

```bash
npm run dev
```

Sau đó mở đường dẫn Vite hiển thị trên terminal, thường là:

```text
http://localhost:5173
```

---

## 10. Build Frontend Để Deploy

Trong thư mục `frontend`, chạy:

```bash
npm run build
```

Thư mục build sẽ được tạo tại:

```text
frontend/dist
```

Có thể dùng lệnh sau để xem bản build:

```bash
npm run preview
```

File production environment hiện tại:

```text
frontend/.env.production
```

Nội dung:

```env
VITE_API_BASE_URL=https://tttn375.cnttstu.online/backend/api
```

Khi deploy lên domain khác, cần sửa lại `VITE_API_BASE_URL` theo domain backend thực tế rồi build lại.

---

## 11. Cấu Trúc Thư Mục Chính

```text
DoAnEnglish2026/
├── backend/                 # Source backend PHP
│   ├── api/                 # Các API cho admin, auth, user, teacher, public
│   ├── config/              # Cấu hình database, JWT
│   ├── uploads/             # Thư mục upload file/hình ảnh
│   └── utils/               # Các helper dùng chung
├── database/                # File SQL và script liên quan database
│   └── learning_english.sql # File import database chính
├── frontend/                # Source frontend Vue 3/Vite
│   ├── src/                 # Component, router, views
│   ├── public/              # Static assets
│   └── package.json         # Cấu hình npm scripts và dependencies
└── HUONG_DAN_CAI_DAT.md     # File hướng dẫn cài đặt
```

---

## 12. Lỗi Thường Gặp Và Cách Xử Lý

### Lỗi không kết nối được database

Kiểm tra:

- MySQL đã chạy chưa
- Database `learning_english` đã được tạo/import chưa
- Thông tin trong `backend/config/db.php` có đúng không
- Mật khẩu user MySQL có đúng không

### Lỗi frontend không gọi được API

Kiểm tra:

- Apache đã chạy chưa
- Biến `VITE_API_BASE_URL` trong `frontend/.env.development` có đúng không
- Đường dẫn source có đúng là `DoAnEnglish2026` không
- Backend có truy cập được qua trình duyệt không

Sau khi sửa file `.env`, cần tắt và chạy lại frontend:

```bash
npm run dev
```

### Lỗi npm install

Thử các cách sau:

```bash
npm cache clean --force
npm install
```

Nếu vẫn lỗi, kiểm tra lại phiên bản Node.js.

### Lỗi upload file hoặc hình ảnh

Kiểm tra thư mục upload có tồn tại và có quyền ghi:

```text
backend/uploads/
backend/uploads/course-images/
backend/uploads/materials/
```

Trên Windows/WAMP/XAMPP thường không cần cấu hình thêm quyền. Nếu deploy lên Linux server, cần cấp quyền ghi cho các thư mục upload.

---

## 13. Checklist Cài Đặt Nhanh

- [ ] Cài WAMP/XAMPP, Node.js, npm, Git
- [ ] Copy hoặc clone source vào thư mục web server
- [ ] Khởi động Apache và MySQL
- [ ] Tạo database `learning_english`
- [ ] Import file `database/learning_english.sql`
- [ ] Kiểm tra `backend/config/db.php`
- [ ] Kiểm tra `frontend/.env.development`
- [ ] Chạy `npm install` trong thư mục `frontend`
- [ ] Chạy `npm run dev`
- [ ] Truy cập `http://localhost:5173`

---

## 14. Lệnh Tóm Tắt

```bash
cd C:\wamp64\www
git clone https://github.com/ludwig68/DoAnEnglish2026.git
cd DoAnEnglish2026\frontend
npm install
npm run dev
```

Sau đó import database `database/learning_english.sql` vào MySQL với tên database `learning_english` và mở:

```text
http://localhost:5173
```