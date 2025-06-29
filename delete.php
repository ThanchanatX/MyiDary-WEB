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

// ตรวจสอบว่าบันทึกนี้เป็นของผู้ใช้จริง ๆ
$stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $_SESSION['user_id']]);
$entry = $stmt->fetch();

if (!$entry) {
    echo "ไม่พบบันทึกหรือไม่มีสิทธิ์ลบ";
    exit;
}

// ลบรูปถ้ามี
if (!empty($entry['image_path']) && file_exists($entry['image_path'])) {
    unlink($entry['image_path']);
}

// ลบจากฐานข้อมูล
$stmt = $pdo->prepare("DELETE FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $_SESSION['user_id']]);

redirect('dashboard.php');
