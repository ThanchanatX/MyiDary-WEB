<?php
session_start();
session_unset();  // ล้างค่า session ทั้งหมด
session_destroy(); // ทำลาย session
header("Location: login.php");
exit();
