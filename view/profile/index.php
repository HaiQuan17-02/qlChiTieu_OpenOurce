<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/auth.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Hồ sơ của tôi';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get user info
$userId = getCurrentUserId();
$sql = "SELECT * FROM users WHERE id = ?";
$user = queryOne($sql, [$userId]);

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy thông tin người dùng');
    redirect(SITE_URL . '/dashboard.php');
}
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-person-circle"></i> Hồ sơ của tôi
                </h1>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <div class="row">
                <div class="col-md-4">
                    <!-- Avatar Card -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="<?= !empty($user['avatar']) ? SITE_URL . '/' . $user['avatar'] : 'https://via.placeholder.com/150' ?>" 
                                     alt="Avatar" class="rounded-circle" width="150" height="150" id="avatarPreview">
                            </div>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                <i class="bi bi-camera"></i> Đổi ảnh đại diện
                            </button>
                            <p class="text-muted mt-3 mb-0">
                                <small>Đã tham gia: <?= formatDate($user['created_at']) ?></small>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Security Info -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-shield-check"></i> Bảo mật</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Mã PIN</small>
                                <div>
                                    <?php if (hasPinCode()): ?>
                                        <span class="badge bg-success">Đã thiết lập</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa thiết lập</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted">Câu hỏi bảo mật</small>
                                <div>
                                    <?php if (!empty($user['security_question'])): ?>
                                        <span class="badge bg-success">Đã thiết lập</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa thiết lập</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?= SITE_URL ?>/view/auth/forgot_pin.php" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-shield-lock"></i> Quên mã PIN?
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Personal Info Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-person"></i> Thông tin cá nhân</h6>
                        </div>
                        <div class="card-body">
                            <form id="profileForm" method="POST" action="<?= SITE_URL ?>/handler/profile_handler.php">
                                <input type="hidden" name="action" value="update_info">
                                
                                <div class="mb-3">
                                    <label for="fullname" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" 
                                           value="<?= e($user['fullname']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= e($user['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= e($user['username']) ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi tên đăng nhập</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Cập nhật thông tin
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lock"></i> Đổi mật khẩu</h6>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm" method="POST" action="<?= SITE_URL ?>/handler/profile_handler.php">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key"></i> Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Security Question -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-question-circle"></i> Câu hỏi bảo mật</h6>
                        </div>
                        <div class="card-body">
                            <form id="securityForm" method="POST" action="<?= SITE_URL ?>/handler/profile_handler.php">
                                <input type="hidden" name="action" value="update_security">
                                
                                <div class="mb-3">
                                    <label for="security_question" class="form-label">Câu hỏi</label>
                                    <select class="form-select" id="security_question" name="security_question" required>
                                        <option value="">-- Chọn câu hỏi --</option>
                                        <option value="Tên trường THPT của bạn là gì?" <?= $user['security_question'] == 'Tên trường THPT của bạn là gì?' ? 'selected' : '' ?>>Tên trường THPT của bạn là gì?</option>
                                        <option value="Tên người bạn thân nhất của bạn là gì?" <?= $user['security_question'] == 'Tên người bạn thân nhất của bạn là gì?' ? 'selected' : '' ?>>Tên người bạn thân nhất của bạn là gì?</option>
                                        <option value="Biệt danh thời nhỏ của bạn là gì?" <?= $user['security_question'] == 'Biệt danh thời nhỏ của bạn là gì?' ? 'selected' : '' ?>>Biệt danh thời nhỏ của bạn là gì?</option>
                                        <option value="Tên thú cưng đầu tiên của bạn là gì?" <?= $user['security_question'] == 'Tên thú cưng đầu tiên của bạn là gì?' ? 'selected' : '' ?>>Tên thú cưng đầu tiên của bạn là gì?</option>
                                        <option value="Thành phố bạn sinh ra?" <?= $user['security_question'] == 'Thành phố bạn sinh ra?' ? 'selected' : '' ?>>Thành phố bạn sinh ra?</option>
                                        <option value="Món ăn yêu thích của bạn là gì?" <?= $user['security_question'] == 'Món ăn yêu thích của bạn là gì?' ? 'selected' : '' ?>>Món ăn yêu thích của bạn là gì?</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="security_answer" class="form-label">Câu trả lời</label>
                                    <input type="text" class="form-control" id="security_answer" name="security_answer" 
                                           value="<?= e($user['security_answer'] ?? '') ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shield-check"></i> Lưu câu hỏi bảo mật
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- Avatar Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera"></i> Đổi ảnh đại diện
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="avatarForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="avatar_file" class="form-label">Chọn ảnh</label>
                        <input type="file" class="form-control" id="avatar_file" name="avatar" accept="image/*" required>
                        <small class="text-muted">Kích thước tối đa: 5MB. Định dạng: JPG, PNG, GIF, WEBP</small>
                    </div>
                    <div class="text-center">
                        <img id="avatarModalPreview" src="" alt="Preview" class="img-thumbnail d-none" style="max-width: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Lưu ảnh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Avatar preview
document.getElementById('avatar_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarModalPreview').src = e.target.result;
            document.getElementById('avatarModalPreview').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

// Avatar form submit
document.getElementById('avatarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('avatar_file');
    if (!fileInput.files[0]) {
        alert('Vui lòng chọn file ảnh');
        return;
    }
    
    const formData = new FormData();
    formData.append('avatar', fileInput.files[0]);
    formData.append('action', 'update_avatar');
    
    fetch('<?= SITE_URL ?>/handler/profile_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra khi upload ảnh');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi upload ảnh: ' + error.message);
    });
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#avatarPreview {
    border: 4px solid #e9ecef;
    object-fit: cover;
}
</style>

