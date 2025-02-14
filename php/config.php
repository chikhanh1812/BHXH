<?php
$host = 'localhost'; // Địa chỉ máy chủ MySQL
$dbname = 'bhxh'; // Tên database
$username = 'root'; // Tên đăng nhập MySQL
$password = ''; // Mật khẩu MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>