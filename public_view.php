<?php
require_once 'includes/config.php';

$token = $_GET['token'] ?? '';
if (!$token) exit('ลิงก์ไม่ถูกต้อง');

$stmt = $pdo->prepare("SELECT d.* FROM shared_entries s JOIN diary_entries d ON s.entry_id = d.id WHERE s.share_token = ?");
$stmt->execute([$token]);
$entry = $stmt->fetch();
if (!$entry) exit('ไม่พบข้อมูลบันทึก');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $comment = trim($_POST['comment']);
    if ($name && $comment) {
        $cstmt = $pdo->prepare("INSERT INTO comments (entry_id, name, comment) VALUES (?, ?, ?)");
        $cstmt->execute([$entry['id'], $name, $comment]);
    }
}

$cstmt = $pdo->prepare("SELECT * FROM comments WHERE entry_id = ? ORDER BY created_at DESC");
$cstmt->execute([$entry['id']]);
$comments = $cstmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($entry['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-light container py-4">
    <div class="card p-4 shadow mb-4">
        <h2><?= htmlspecialchars($entry['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($entry['content'])) ?></p>
        <?php if (!empty($entry['image_path'])): ?>
            <img src="<?= htmlspecialchars($entry['image_path']) ?>" class="img-fluid rounded mt-3">
        <?php endif; ?>
        <p class="text-muted mt-3">วันที่: <?= $entry['entry_date'] ?> | อารมณ์: <?= $entry['mood'] ?></p>
    </div>

    <div class="card p-4 shadow">
        <h4>💬 ความคิดเห็น</h4>
        <form method="POST" class="mb-3">
    <input name="name" class="form-control mb-2" placeholder="ชื่อของคุณ" required>
    
    <!-- ✅ Emoji Picker UI -->
    <div class="mb-2">
        <strong>😀 เลือกอิโมจิ:</strong>
        <div>
            <?php
            $emojis = ['😀', '😢', '😡', '😂', '😍', '👍', '🙏', '❤️', '😴', '😎'];
            foreach ($emojis as $emoji) {
                echo "<button type='button' class='btn btn-sm btn-light emoji-btn me-1 mb-1'>{$emoji}</button>";
            }
            ?>
        </div>
    </div>

    <!-- ช่องพิมพ์คอมเมนต์ -->
    <textarea name="comment" id="comment" class="form-control mb-2" rows="3" placeholder="แสดงความคิดเห็น..." required></textarea>
    <button class="btn btn-primary">ส่งความคิดเห็น</button>
</form>

        <?php foreach ($comments as $c): ?>
            <div class="border p-2 mb-2 rounded bg-white">
                <strong><?= htmlspecialchars($c['name']) ?></strong><br>
                <p class="mb-1"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                <small class="text-muted"><?= $c['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
<script>
document.querySelectorAll('.emoji-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const emoji = this.textContent;
        const textarea = document.getElementById('comment');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.slice(0, start) + emoji + text.slice(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    });
});
</script>
