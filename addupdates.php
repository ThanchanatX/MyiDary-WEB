<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

// ตรวจสอบว่าเป็น admin (ตัวอย่าง: user_id == 1)
if ($_SESSION['user_id'] != 1) {
    die("Only admin can access this page.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO changelog (version, title, content_added, content_fixed, content_changed, date_released)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['version'],
        $_POST['title'],
        $_POST['added'],
        $_POST['fixed'],
        $_POST['changed'],
        $_POST['date']
    ]);
    header("Location: updates.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Changelog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-5">
    <h2 class="mb-4">Add New Changelog</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Version</label>
            <input type="text" name="version" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Date Released</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Added</label>
            <textarea name="added" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Fixed</label>
            <textarea name="fixed" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Changed</label>
            <textarea name="changed" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
</div>
</body>
</html>
