<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$entry_id = $_GET['id'] ?? null;

if (!$entry_id) {
    redirect('dashboard.php');
}

// ดึงข้อมูลบันทึกจากฐานข้อมูล (เฉพาะของผู้ใช้คนนั้น)
$stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $_SESSION['user_id']]);
$entry = $stmt->fetch();

if (!$entry) {
    echo "ไม่พบบันทึกนี้";
    exit;
}

// ✅ ดึงความคิดเห็นทั้งหมดของ entry นี้
$cstmt = $pdo->prepare("SELECT * FROM comments WHERE entry_id = ? ORDER BY created_at DESC");
$cstmt->execute([$entry_id]);
$comments = $cstmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($entry['title'] ?: 'ดูบันทึก') ?> - My Diary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>
    /* --- Global Styles --- */
    body {
        font-family: 'Kanit', sans-serif; /* ฟอนต์หลัก */
        background-color: #f8f9fa; /* สีพื้นหลังอ่อนๆ */
        color: #343a40; /* สีข้อความหลัก */
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 20px;
        padding-bottom: 20px;
        box-sizing: border-box;
    }

    .container {
        width: 100%;
        max-width: 800px; /* กำหนดขนาดสูงสุดของคอนเทนเนอร์ */
    }

    /* --- Action Links --- */
    .action-links {
        display: flex;
        justify-content: flex-end; /* จัดปุ่มไปทางขวา */
        margin-bottom: 15px;
    }

    .action-links a {
        text-decoration: none;
        margin-left: 10px;
        padding: 8px 12px;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .action-links a.text-warning {
        color: #ffc107;
        border: 1px solid #ffc107;
    }

    .action-links a.text-warning:hover {
        background-color: #ffc107;
        color: #fff;
    }

    .action-links a.text-danger {
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .action-links a.text-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
    .action-links a.text-info {
        color: #dc3545;
        border: 1px solid #dc3545;
    }
    .action-links a.text-info:hover {
        background-color:rgb(16, 202, 62);
        color: #fff;
    }

    /* --- Card Styles --- */
    .card {
        background-color: #fff;
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        padding: 20px;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h1 {
        font-size: 1.75rem;
        margin-bottom: 0;
    }

    .card-body p {
        line-height: 1.7;
        margin-bottom: 1rem;
    }

    .card-footer {
        background-color: transparent;
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* --- Tag Styles --- */
    .text-primary {
        color: #007bff !important;
    }

    .small, small {
        font-size: 0.875em;
    }

    /* --- Image Styles --- */
    .img-fluid {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
        margin-top: 15px;
    }

    /* --- Fade In Animation --- */
    .fade-in {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* --- Responsive Adjustments --- */
    @media (max-width: 768px) {
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        .action-links {
            justify-content: center;
            margin-bottom: 10px;
        }

        .action-links a {
            margin: 0 5px;
            padding: 6px 10px;
            font-size: 0.9rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header .btn {
            margin-top: 10px;
        }
    }
</style>
</head>
<body class="bg-light min-vh-100 py-4">

    <div class="container">
        <div class="mt-6 d-flex justify-end action-links">
        <a href="share.php?id=<?= $entry['id'] ?>" class="text-info">🔗 แชร์</a>
            <a href="edit.php?id=<?= $entry['id'] ?>" class="text-warning me-3">✏️ แก้ไข</a>
            <a href="delete.php?id=<?= $entry['id'] ?>" class="text-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าจะลบ?')">🗑️ ลบ</a>
        </div>

        <div class="card shadow-sm rounded p-4 mt-5 fade-in">
            <div class="d-flex justify-content-between mb-4">
                <h1 class="h4"><?= htmlspecialchars($entry['title']) ?: '(ไม่มีชื่อ)' ?></h1>
                <a href="dashboard.php" class="btn btn-outline-secondary">← ย้อนกลับ</a>
            </div>

            <p class="text-muted small">📅 <?= htmlspecialchars($entry['entry_date']) ?> | อารมณ์: <?= htmlspecialchars($entry['mood']) ?></p>

            <?php if (!empty($entry['tags'])): ?>
                <p class="mt-2 text-primary small">#<?= htmlspecialchars($entry['tags']) ?></p>
            <?php endif; ?>

            <div class="mt-4 text-dark">
                <p><?= nl2br(htmlspecialchars($entry['content'])) ?></p>
            </div>

            <?php if (!empty($entry['image_path']) && file_exists($entry['image_path'])): ?>
                <div class="mt-4">
                    <img src="<?= htmlspecialchars($entry['image_path']) ?>" alt="ภาพประกอบ" class="img-fluid rounded">
                </div>
            <?php endif; ?>
        </div>
        <?php if ($comments): ?>
    <div class="card mt-4 p-4 shadow-sm fade-in">
        <h5 class="mb-3">💬 ความคิดเห็นจากผู้เข้าชม</h5>
        <?php foreach ($comments as $comment): ?>
            <div class="border-bottom pb-2 mb-3">
                <strong><?= htmlspecialchars($comment['name']) ?></strong>
                <p class="mb-1"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                <small class="text-muted"><?= $comment['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card mt-4 p-4 shadow-sm fade-in">
        <h5 class="mb-0">💬 ยังไม่มีความคิดเห็นจากผู้เข้าชม</h5>
    </div>
<?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
