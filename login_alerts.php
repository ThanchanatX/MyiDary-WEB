<?php 
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;


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

$profileImage = $user['profile_image'] ?: 'user/default-avatar.png';

$stmt = $conn->prepare("SELECT * FROM login_logs WHERE user_id = ? ORDER BY login_time DESC LIMIT 10");
$stmt->execute([$user_id]);
$logs = $stmt->fetchAll();
$latest_log_id = $logs[0]['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แจ้งเตือนเข้าสู่ระบบ</title>
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

    .table-hover tbody tr:hover {
      background-color: #f0f9ff;
    }
    .login-log-item:hover {
  background-color: #eaf6ff;
  transform: scale(1.01);
  transition: all 0.2s ease-in-out;
}

  </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container p-0">
  <center><h1 class="h4 mb-4 mt-4">แจ้งเตือนเข้าสู่ระบบ</h1></center>
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

      <div class="card mt-4 shadow-sm">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fas fa-history me-2"></i>ประวัติการเข้าสู่ระบบล่าสุด</h5>
        </div>
        <div class="card-body">
        <?php foreach ($logs as $log):
  $time = date("d M Y H:i", strtotime($log['login_time']));
  $ip = htmlspecialchars($log['ip_address']);
  $agent = $log['user_agent'];
  $is_current = $log['id'] == $latest_log_id;

  // ใช้ DeviceDetector
  $dd = new DeviceDetector($agent);
  $dd->parse();

  if ($dd->isBot()) {
    $device = 'Bot';
    $os = $dd->getBot()['name'] ?? 'Bot';
    $browser = '-';
  } else {
    $client = $dd->getClient(); // Browser
    $osInfo = $dd->getOs();     // OS
    $brand = $dd->getBrandName(); // เช่น Apple
    $model = $dd->getModel();     // เช่น iPhone 13

    $browser = $client['name'] ?? 'ไม่ทราบ';
    $os = $osInfo['name'] ?? 'ไม่ทราบ';
    $device = ($brand && $model) ? "$brand $model" : ucfirst($dd->getDeviceName());
  }

  // Icon OS
  $os_icon = '<i class="fas fa-desktop text-secondary fa-2x me-2"></i>';
  if (stripos($os, 'Windows') !== false) {
    $os_icon = '<i class="fab fa-windows text-primary fa-2x me-2"></i>';
  } elseif (stripos($os, 'Mac') !== false || stripos($os, 'iOS') !== false) {
    $os_icon = '<i class="fab fa-apple text-dark fa-2x me-2"></i>';
  } elseif (stripos($os, 'Android') !== false) {
    $os_icon = '<i class="fab fa-android text-success fa-2x me-2"></i>';
  } elseif (stripos($os, 'Linux') !== false) {
    $os_icon = '<i class="fab fa-linux text-danger fa-2x me-2"></i>';
  }
?>
  <div class="mb-4 p-3 border rounded shadow-sm position-relative bg-light login-log-item">
    <div class="d-flex align-items-center mb-2">
      <?= $os_icon ?>
      <div>
        <div><strong><?= htmlspecialchars($device) ?></strong> · <?= htmlspecialchars($os) ?> · <?= htmlspecialchars($browser) ?></div>
        <div class="text-muted small">
          <i class="fas fa-map-marker-alt me-1"></i>IP: <?= htmlspecialchars($ip) ?>
          <span class="mx-2">|</span>
          <i class="fas fa-clock me-1"></i><?= $time ?>
        </div>
      </div>
      <?php if ($is_current): ?>
        <span class="badge bg-success ms-auto">ใช้งานอยู่ตอนนี้</span>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
