<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// ดึงค่าจาก query string
$query = $_GET['query'] ?? '';
$date = $_GET['date'] ?? '';

// สร้าง SQL ตามเงื่อนไขค้นหา
$sql = "SELECT * FROM diary_entries WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if (!empty($query)) {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if (!empty($date)) {
    $sql .= " AND entry_date = ?";
    $params[] = $date;
}

$sql .= " ORDER BY entry_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();
?>
<?php
$stmt = $pdo->prepare("SELECT profile_image, username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$profileImage = $user['profile_image'] ?: 'user/default-avatar.png'; // รูปโปรไฟล์เริ่มต้น
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ไดอารี่ของฉัน - My Diary</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<body class="p-4">

    <!-- Toast Container: moved to bottom-right -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toast-message"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <header class="mb-4 fixed-top shadow-sm bg-white"> 
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center py-3">
      <div class="d-flex align-items-center gap-3">
        <img src="https://cdn.discordapp.com/attachments/1357773110543122445/1369592591506477087/TNSXLOGO-BLACK.png?ex=681c6bff&is=681b1a7f&hm=99a52bea3b9ccb53060a83a0b745fb1adda81a18f6d4bd14cc76977f62cef4b2&" height="50" alt="logo">
      </div>

      <div class="d-flex align-items-center gap-3 flex-wrap">
        <!-- รูปโปรไฟล์ + ชื่อผู้ใช้ -->
        <a href="editprofile.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <img src="<?= htmlspecialchars($profileImage) ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
          <span class="fw-bold d-none d-md-inline text-dark"><?= htmlspecialchars($user['username']) ?></span>
        </a>

        <!-- ปุ่มต่างๆ -->
        <a href="write.php" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
          <i class="fas fa-plus"></i> เขียนใหม่
        </a>
        <a href="write.php?draft=1" class="btn btn-sm btn-warning d-flex align-items-center gap-1">
          <i class="fas fa-file-alt"></i> ฉบับร่าง
        </a>
        <a href="logout.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
          <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
        </a>
      </div>
    </div>

    <!-- Search -->
    <form method="get" class="row g-2 align-items-center py-2">
      <div class="col-md-5">
        <input type="text" name="query" class="form-control" placeholder="🔍 ค้นหาหัวข้อหรือเนื้อหา..." value="<?= htmlspecialchars($query) ?>">
      </div>
      <div class="col-md-4">
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-1">
          <i class="fas fa-search"></i> ค้นหา
        </button>
      </div>
    </form>
  </div>
</header>
<!-- ให้เพิ่ม margin-top ให้กับ main container เพื่อลดผลกระทบของ fixed-top -->
<div class="container mt-5 pt-5">

        <center><h1 class="h5 mb-3 fade-in">บันทึกล่าสุด</h1></center>

<?php if (count($entries) === 0): ?>
    <p class="text-muted">ไม่พบบันทึกที่ตรงกับเงื่อนไขการค้นหา</p>
<?php else: ?>
    <ul class="timeline fade-in">
    <?php foreach ($entries as $index => $entry): ?>
    <li class="<?= $index === 0 ? 'latest-entry' : '' ?>">

        <li>
        <div class="timeline-time">
    <span class="date"><?= date('d M Y', strtotime($entry['entry_date'])) ?></span>
    <span class="time"><?= date('H:i', strtotime($entry['created_at'])) ?> น.</span>
</div>

            <div class="timeline-icon">
                <a href="view.php?id=<?= $entry['id'] ?>"></a>
            </div>
            <div class="timeline-body">
                <div class="timeline-header">
                    
                    <span class="username">
                    <?php if ($index === 0): ?>
    <span class="badge bg-success ms-2">ล่าสุด</span>
<?php endif; ?>

                        <?= htmlspecialchars($entry['title']) ?: '(ไม่มีชื่อ)' ?>
                        <?php if ($entry['is_draft']): ?>
                            <span class="badge bg-warning text-dark">ฉบับร่าง</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="timeline-content">
    <p>อารมณ์: 
        <span class="mood-icon">
            <!-- ใช้เงื่อนไข PHP เพื่อแสดงอิโมจิตามค่า mood -->
            <?php 
                switch ($entry['mood']) {
                    case 'happy':
                        echo '<i class="fas fa-smile"></i>'; // อิโมจิหน้ายิ้ม
                        break;
                    case 'sad':
                        echo '<i class="fas fa-sad-tear"></i>'; // อิโมจิหน้ายิ้มเศร้า
                        break;
                    case 'angry':
                        echo '<i class="fas fa-angry"></i>'; // อิโมจิหน้าโกรธ
                        break;
                    case 'surprised':
                        echo '<i class="fas fa-surprise"></i>'; // อิโมจิหน้าเซอร์ไพรส์
                        break;
                    default:
                        echo '<i class="fas fa-meh"></i>'; // อิโมจิหน้าปกติ
                        break;
                }
            ?>
        </span>
        <?= htmlspecialchars($entry['mood']) ?>
    </p>
    <p><?= nl2br(htmlspecialchars(substr($entry['content'], 0, 150))) ?>...</p>
</div>

                <div class="timeline-footer">
    <a href="view.php?id=<?= $entry['id'] ?>" class="btn btn-sm btn-primary shadow-sm">
        📖 อ่านต่อ
    </a>
</div>

            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php endif; ?>

    </div>
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const searchPerformed = <?= (!empty($query) || !empty($date)) ? 'true' : 'false' ?>;
        const entryCount = <?= count($entries) ?>;

        if (searchPerformed) {
            const toastMessage = document.getElementById('toast-message');
            const toastEl = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastEl);

            if (entryCount === 0) {
                toastMessage.textContent = 'ไม่พบบันทึกที่ตรงกับการค้นหา';
                toastEl.classList.remove('bg-success');
                toastEl.classList.add('bg-danger');
            } else {
                toastMessage.textContent = 'พบ ' + entryCount + ' รายการที่ตรงกับการค้นหา';
                toastEl.classList.remove('bg-danger');
                toastEl.classList.add('bg-success');
            }

            toast.show();
        }
    </script>
</body>
</html>
