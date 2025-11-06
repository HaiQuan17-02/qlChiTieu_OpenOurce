# Hướng dẫn đẩy dự án lên GitHub

## Bước 1: Cài đặt Git

1. Tải Git từ: https://git-scm.com/download/win
2. Cài đặt với các tùy chọn mặc định
3. Sau khi cài xong, mở lại PowerShell/CMD

## Bước 2: Cấu hình Git (chỉ cần làm 1 lần)

```bash
git config --global user.name "Tên của bạn"
git config --global user.email "email@example.com"
```

## Bước 3: Khởi tạo Git repository

Trong thư mục dự án, chạy các lệnh sau:

```bash
# Khởi tạo repository
git init

# Thêm tất cả files vào staging
git add .

# Commit lần đầu
git commit -m "Initial commit: Quản lý chi tiêu cá nhân"

# Đổi tên nhánh thành main (nếu cần)
git branch -M main

# Thêm remote repository (URL từ GitHub)
git remote add origin https://github.com/thuyetdeptrai/OpenSource.git

# Đẩy code lên GitHub
git push -u origin main
```

## Bước 4: Xác thực với GitHub

Khi push lần đầu, GitHub sẽ yêu cầu xác thực:
- Username: Tên đăng nhập GitHub của bạn
- Password: Sử dụng Personal Access Token (không phải mật khẩu GitHub)

### Tạo Personal Access Token:
1. Vào GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Chọn quyền: `repo` (full control)
4. Copy token và dùng khi push

## Lệnh thay thế (nếu đã có Git repository)

Nếu repository đã được khởi tạo trước đó, chỉ cần:

```bash
git remote add origin https://github.com/thuyetdeptrai/OpenSource.git
git branch -M main
git push -u origin main
```

## Lưu ý bảo mật

⚠️ **QUAN TRỌNG**: File `function/db_connection.php` chứa thông tin database nhạy cảm. 

**Tùy chọn 1**: Xóa thông tin nhạy cảm trước khi push:
- Tạo file `function/db_connection.example.php` với thông tin mẫu
- Giữ `db_connection.php` trong `.gitignore`

**Tùy chọn 2**: Nếu đã push nhầm, có thể:
- Đổi password database
- Hoặc xóa commit chứa file nhạy cảm (cần force push)

## Các lệnh Git hữu ích

```bash
# Xem trạng thái
git status

# Xem các thay đổi
git diff

# Commit sau khi thay đổi
git add .
git commit -m "Mô tả thay đổi"
git push

# Xem lịch sử commit
git log

# Xem remote repository
git remote -v
```

