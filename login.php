<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบ";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password'])) {
            // เข้าสู่ระบบสำเร็จ
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        
            // บันทึก log การเข้าสู่ระบบ
            $user_id = $user['id'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $os = php_uname('s');
            $device = get_browser(null, true)['platform'] ?? 'Unknown';
        
            $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, os_info, login_time) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $ip, $agent, $device]);
        
            redirect('dashboard.php');
        }
        
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - My Diary</title>
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
        <h2 class="text-2xl font-bold mb-6 text-center">เข้าสู่ระบบ</h2>

        <?php if ($errors): ?>
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้หรืออีเมล" class="w-full p-2 border rounded" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" class="w-full p-2 border rounded" required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">เข้าสู่ระบบ</button>
        </form>

        <p class="text-center mt-4 text-sm">ยังไม่มีบัญชี? <a href="register.php" class="text-blue-600 hover:underline">สมัครสมาชิก</a></p>
    </div>
                    
</body>
</html>
