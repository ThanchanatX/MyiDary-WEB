<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) redirect('login.php');

$entry_id = $_GET['id'] ?? null;
if (!$entry_id) redirect('dashboard.php');

// สร้าง token แชร์
$token = bin2hex(random_bytes(10));
$stmt = $pdo->prepare("INSERT INTO shared_entries (entry_id, share_token) VALUES (?, ?)");
$stmt->execute([$entry_id, $token]);

echo "ลิงก์แชร์: <a href='public_view.php?token=$token'>คลิกที่นี่เพื่อดู</a>";
?>
