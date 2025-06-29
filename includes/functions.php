<?php
// เริ่ม session ทุกหน้าที่เรียกใช้ฟังก์ชันนี้
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ฟังก์ชันเช็คว่าผู้ใช้ล็อกอินอยู่หรือไม่
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันเปลี่ยนเส้นทางไปยังหน้าอื่น
function redirect($url) {
    header("Location: $url");
    exit();
}

// ฟังก์ชันเข้ารหัสรหัสผ่าน
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันตรวจสอบรหัสผ่าน
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
