<?php
session_start();
require 'includes/config2.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เปลี่ยนรหัสผ่าน
    if (!empty($_POST['new_password']) && $_POST['new_password'] === $_POST['confirm_password']) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $success = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
    } elseif (!empty($_POST['new_password'])) {
        $error = "รหัสผ่านไม่ตรงกัน";
    }

    // อัปโหลดรูปโปรไฟล์
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_image']['tmp_name'];
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
$filename = 'assets/uploads/profile_' . $user_id . '.' . $ext;
move_uploaded_file($tmp, $filename);

        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$filename, $user_id]);
        $user['profile_image'] = $filename;
        $success .= " และอัปโหลดรูปเรียบร้อยแล้ว";
    }
}
?>
<?php
$profileImage = $user['profile_image'] ?: 'user/default-avatar.png'; // รูปโปรไฟล์เริ่มต้น
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แก้ไขโปรไฟล์</title>
  <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/editprofile.css">
  <style>
     .card-header {
      background: linear-gradient(to right,rgb(30, 86, 207),rgb(91, 166, 228));
      color: white;
    }

  </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container p-0">
    <center><h1 class="h4 mb-4 mt-4">การตั้งค่าโปรไฟล์</h1></center>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">เมนูการตั้งค่า</h5>
                </div>
                <div class="list-group list-group-flush">
  <a href="editprofile.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) === 'editprofile.php' ? 'active' : '' ?>">
    บัญชีผู้ใช้
  </a>
  <a href="login_alerts.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) === 'login_alerts.php' ? 'active' : '' ?>">
    การแจ้งเตือน
  </a>
  <a href="security_settings.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) === 'security_settings.php' ? 'active' : '' ?>">
    ความปลอดภัย
  </a>
</div>

            </div>
        </div>

        <div class="col-md-8">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ข้อมูลสาธารณะ</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อผู้ใช้</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>

                                </div>
                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านใหม่</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <input type="password" name="confirm_password" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?= $user['profile_image'] ?>" class="rounded-circle img-fluid mb-2" width="128" height="128">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-8x text-secondary mb-2"></i>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <input type="file" name="profile_image" class="form-control">
                                </div>
                                <small class="text-muted d-block mt-2">ใช้ภาพ .jpg อย่างน้อย 128x128</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3"><i class="fa fa-save me-1"></i>บันทึก</button>
                        <a href="dashboard.php" class="btn btn-secondary mt-3">กลับ</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
<script>
<?php if ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'สำเร็จ',
        text: '<?= $success ?>',
        confirmButtonText: 'ตกลง'
    });
<?php elseif ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: '<?= $error ?>',
        confirmButtonText: 'ลองใหม่'
    });
<?php endif; ?>
</script>

</body>

</html>
