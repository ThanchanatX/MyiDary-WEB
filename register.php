<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจสอบความถูกต้อง
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif ($password !== $confirm_password) {
        $errors[] = "รหัสผ่านไม่ตรงกัน";
    } else {
        // ตรวจสอบว่า email หรือ username ซ้ำหรือไม่
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว";
        } else {
            // สมัครสมาชิก
            $hashed = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);
            redirect('login.php');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก - My Diary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Kanit', sans-serif;
    }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg p-8 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">สมัครสมาชิก</h2>

        <?php if ($errors): ?>
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" class="w-full p-2 border rounded" required>
            <input type="email" name="email" placeholder="อีเมล" class="w-full p-2 border rounded" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" class="w-full p-2 border rounded" required>
            <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" class="w-full p-2 border rounded" required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">สมัครสมาชิก</button>
        </form>

        <p class="text-center mt-4 text-sm">มีบัญชีแล้ว? <a href="login.php" class="text-blue-600 hover:underline">เข้าสู่ระบบ</a></p>
    </div>
    
</body>
</html>
