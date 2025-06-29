<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$mood = $_POST['mood'] ?? 'neutral';
$tags = trim($_POST['tags'] ?? '');

// เช็คว่ามีฉบับร่างอยู่ไหม
$checkStmt = $pdo->prepare("SELECT id FROM diary_entries WHERE user_id = ? AND is_draft = 1 LIMIT 1");
$checkStmt->execute([$user_id]);
$existingDraft = $checkStmt->fetch();

if ($existingDraft) {
    // อัปเดตฉบับร่างเดิม
    $updateStmt = $pdo->prepare("UPDATE diary_entries SET title = ?, content = ?, mood = ?, tags = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$title, $content, $mood, $tags, $existingDraft['id']]);
} else {
    // สร้างฉบับร่างใหม่
    $insertStmt = $pdo->prepare("INSERT INTO diary_entries (user_id, title, content, mood, tags, is_draft, entry_date, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())");
    $insertStmt->execute([$user_id, $title, $content, $mood, $tags]);
}

echo json_encode(['success' => true]);
