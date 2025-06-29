<?php
session_start();
require 'includes/config.php';

$stmt = $pdo->query("SELECT * FROM changelog ORDER BY date_released DESC");
$changelogs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Changelog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .version-badge { font-size: 0.8rem; padding: 0.35em 0.65em; }
        .changelog-item { position: relative; padding-left: 20px; }
        .changelog-item::before {
            content: ""; position: absolute; left: 0; top: 8px;
            width: 8px; height: 8px; border-radius: 50%;
            background-color: #0d6efd;
        }
        .timeline-line {
            position: absolute; left: 3px; top: 20px; bottom: -8px;
            width: 2px; background-color: #dee2e6;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Changelog</h1>
                <p class="text-muted">ติดตามการอัปเดตและการปรับปรุงล่าสุดของระบบ</p>
            </div>

            <?php foreach ($changelogs as $log): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary version-badge me-2"><?= htmlspecialchars($log['version']) ?></span>
                        <small class="text-muted"><?= date('F j, Y', strtotime($log['date_released'])) ?></small>
                    </div>

                    <h5 class="card-title"><?= htmlspecialchars($log['title']) ?></h5>

                    <div class="position-relative">
                        <?php if (!empty($log['content_added'])): ?>
                        <div class="changelog-item mb-3">
                            <div class="timeline-line"></div>
                            <h6 class="fw-bold text-success"><i class="fas fa-plus-circle"></i> Added</h6>
                            <p><?= nl2br(htmlspecialchars($log['content_added'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($log['content_fixed'])): ?>
                        <div class="changelog-item mb-3">
                            <div class="timeline-line"></div>
                            <h6 class="fw-bold text-danger"><i class="fas fa-bug"></i> Fixed</h6>
                            <p><?= nl2br(htmlspecialchars($log['content_fixed'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($log['content_changed'])): ?>
                        <div class="changelog-item">
                            <h6 class="fw-bold text-warning"><i class="fas fa-edit"></i> Changed</h6>
                            <p><?= nl2br(htmlspecialchars($log['content_changed'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
