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

$stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $_SESSION['user_id']]);
$entry = $stmt->fetch();

if (!$entry) {
    echo "ไม่พบบันทึก";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood = $_POST['mood'] ?? 'neutral';
    $tags = trim($_POST['tags'] ?? '');

    if (empty($content)) {
        $errors[] = "กรุณากรอกเนื้อหา";
    }

    // ถ้ามีการอัปโหลดภาพใหม่
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "assets/uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image_path = $targetFile;

            // ลบภาพเก่า (ถ้ามี)
            if (!empty($entry['image_path']) && file_exists($entry['image_path'])) {
                unlink($entry['image_path']);
            }
        } else {
            $errors[] = "อัปโหลดรูปภาพไม่สำเร็จ";
        }
    } else {
        $image_path = $entry['image_path'];
    }

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE diary_entries SET title = ?, content = ?, mood = ?, tags = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $mood, $tags, $image_path, $entry_id, $_SESSION['user_id']]);
        redirect("view.php?id=$entry_id");
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขบันทึก - My Diary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>

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
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">✏️ แก้ไขบันทึก</h1>

        <?php if ($errors): ?>
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
            <input type="text" name="title" value="<?= htmlspecialchars($entry['title']) ?>" placeholder="หัวข้อ" class="w-full p-2 border rounded">
            
            <textarea name="content" rows="6" class="w-full p-2 border rounded"><?= htmlspecialchars($entry['content']) ?></textarea>
            
            <div class="flex flex-col md:flex-row gap-4">
                <select name="mood" class="w-full p-2 border rounded">
                    <?php
                    $moods = ['neutral' => '😐 ปกติ', 'happy' => '😊 สุขใจ', 'sad' => '😢 เศร้า', 'angry' => '😠 โมโห', 'excited' => '🤩 ตื่นเต้น', 'tired' => '😴 เหนื่อย'];
                    foreach ($moods as $val => $label):
                        $selected = $entry['mood'] === $val ? 'selected' : '';
                        echo "<option value=\"$val\" $selected>$label</option>";
                    endforeach;
                    ?>
                </select>

                <input type="text" name="tags" value="<?= htmlspecialchars($entry['tags']) ?>" class="w-full p-2 border rounded">
            </div>

            <?php if (!empty($entry['image_path']) && file_exists($entry['image_path'])): ?>
                <div>
                    <p class="text-sm text-gray-500 mb-1">รูปภาพเดิม:</p>
                    <img src="<?= htmlspecialchars($entry['image_path']) ?>" class="max-w-xs rounded">
                </div>
            <?php endif; ?>

            <input type="file" name="image" accept="image" class="w-full p-2 border rounded">

            <div class="flex justify-between">
                <a href="view.php?id=<?= $entry['id'] ?>" class="text-gray-600 hover:underline">ยกเลิก</a>
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
