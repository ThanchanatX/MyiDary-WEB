<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$title = $content = $mood = $tags = "";
$image_path = null;

// โหลดฉบับร่างล่าสุด ถ้ามี
$draftStmt = $pdo->prepare("SELECT * FROM diary_entries WHERE user_id = ? AND is_draft = 1 ORDER BY updated_at DESC LIMIT 1");
$draftStmt->execute([$_SESSION['user_id']]);
$draft = $draftStmt->fetch();

if ($draft) {
    $title = $draft['title'];
    $content = $draft['content'];
    $mood = $draft['mood'];
    $tags = $draft['tags'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood = $_POST['mood'] ?? 'neutral';
    $tags = trim($_POST['tags'] ?? '');
    $entry_date = date('Y-m-d');

    if (empty($content)) {
        $errors[] = "กรุณากรอกเนื้อหาบันทึก";
    }

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "assets/uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image_path = $targetFile;
        } else {
            $errors[] = "อัปโหลดรูปภาพไม่สำเร็จ";
        }
    }

    if (!$errors) {
        // ลบฉบับร่างก่อน (ถ้ามี)
        $pdo->prepare("DELETE FROM diary_entries WHERE user_id = ? AND is_draft = 1")->execute([$_SESSION['user_id']]);

        // บันทึกจริง
        $stmt = $pdo->prepare("INSERT INTO diary_entries (user_id, entry_date, title, content, mood, tags, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $entry_date,
            $title,
            $content,
            $mood,
            $tags,
            $image_path
        ]);
        redirect('dashboard.php');
    }
}
if (isset($_GET['draft']) && $_GET['draft'] == 1) {
    $stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE user_id = ? AND is_draft = 1 ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $draft = $stmt->fetch();

    if ($draft) {
        // Redirect ไปหน้าแก้ไขฉบับร่าง พร้อมพารามิเตอร์ id
        header("Location: write.php?id=" . $draft['id']);
        exit;
    } else {
        // ถ้าไม่มีฉบับร่าง ให้ redirect ไปเขียนใหม่
        header("Location: write.php");
        exit;
    }
}

?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>เขียนบันทึก - My Diary</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Kanit', sans-serif;
                background-color: #f2f4f8;
            }

            .form-container {
                animation: fadeInUp 0.6s ease forwards;
                opacity: 0;
            }

            .form-control:focus {
                box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
                transition: box-shadow 0.3s ease;
            }

            .form-select:focus {
                box-shadow: 0 0 0 0.25rem rgba(25,135,84,0.25);
                transition: box-shadow 0.3s ease;
            }

            .btn-primary {
                transition: background-color 0.3s ease, transform 0.2s ease;
            }

            .btn-primary:hover {
                background-color: #0b5ed7;
                transform: scale(1.03);
            }

            .card-hover:hover {
                box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
                transform: translateY(-4px);
                transition: all 0.3s ease;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>
    <body class="py-4">
    <div class="container">
    <div class="mx-auto form-container" style="max-width: 700px;">
        <center><h1 class="mb-4">เขียนบันทึกใหม่</h1></center>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow card-hover">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">หัวข้อ (ไม่จำเป็น)</label>
                        <input type="text" name="title" id="title" class="form-control"
                               placeholder="เช่น: วันที่เหนื่อยสุดๆ"
                               value="<?= htmlspecialchars($title ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">เนื้อหา</label>
                        <textarea name="content" id="content" rows="6" class="form-control"
                                  placeholder="เขียนเรื่องราวของคุณที่นี่..." required><?= htmlspecialchars($content ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mood" class="form-label">อารมณ์</label>
                            <select name="mood" id="mood" class="form-select">
                                <option value="neutral" <?= ($mood ?? '') === 'neutral' ? 'selected' : '' ?>>😐 ปกติ</option>
                                <option value="happy" <?= ($mood ?? '') === 'happy' ? 'selected' : '' ?>>😊 สุขใจ</option>
                                <option value="sad" <?= ($mood ?? '') === 'sad' ? 'selected' : '' ?>>😢 เศร้า</option>
                                <option value="angry" <?= ($mood ?? '') === 'angry' ? 'selected' : '' ?>>😠 โมโห</option>
                                <option value="excited" <?= ($mood ?? '') === 'excited' ? 'selected' : '' ?>>🤩 ตื่นเต้น</option>
                                <option value="tired" <?= ($mood ?? '') === 'tired' ? 'selected' : '' ?>>😴 เหนื่อย</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tags" class="form-label">แท็ก</label>
                            <input type="text" name="tags" id="tags" class="form-control"
                                   placeholder="เช่น: งาน, โรงเรียน"
                                   value="<?= htmlspecialchars($tags ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">อัปโหลดรูปภาพ (ไม่จำเป็น)</label>
                        <input type="file" name="image" id="image" accept="image/*" class="form-control">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">ย้อนกลับ</a>
                        <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
let autosaveTimer;
const delay = 1000; // 3 วินาที

function autosave() {
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('content', document.getElementById('content').value);
    formData.append('mood', document.getElementById('mood').value);
    formData.append('tags', document.getElementById('tags').value);

    fetch('autosave.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
          if (data.success) {
              console.log("Auto-saved!");
          }
      });
}
document.addEventListener('DOMContentLoaded', () => {
    ['title', 'content', 'mood', 'tags'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(autosave, delay);
            });
        }
    });
});

</script>

    </body>
    </html>
