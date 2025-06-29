
<header class="mb-4 fixed-top shadow-sm bg-white">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center py-3">
      <div class="d-flex align-items-center gap-3">
        <img src="https://cdn.discordapp.com/attachments/1357773110543122445/1369592591506477087/TNSXLOGO-BLACK.png?ex=681c6bff&is=681b1a7f&hm=99a52bea3b9ccb53060a83a0b745fb1adda81a18f6d4bd14cc76977f62cef4b2&" height="50" alt="logo">
      </div>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <!-- รูปโปรไฟล์ + ชื่อผู้ใช้ -->
        <a href="editprofile.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <img src="<?= htmlspecialchars($profileImage) ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
          <span class="fw-bold d-none d-md-inline text-dark"><?= htmlspecialchars($user['username']) ?></span>
        </a>
        <!-- ปุ่มต่างๆ -->
        <a href="write.php" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
          <i class="fas fa-plus"></i> เขียนใหม่
        </a>
        <a href="write.php?draft=1" class="btn btn-sm btn-warning d-flex align-items-center gap-1">
          <i class="fas fa-file-alt"></i> ฉบับร่าง
        </a>
        <a href="logout.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
          <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
        </a>
      </div>
    </div>
  </div>
</header>
ิ<br><br><br>