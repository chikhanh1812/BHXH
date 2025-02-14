<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

$host = 'localhost';
$dbname = 'bhxh';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $e->getMessage()]);
    exit;
}

// Lấy ngày hiện tại
$currentDate = date('Y-m-d');

// Cập nhật trạng thái tài khoản nếu ngày thanh toán cuối cùng là trước ngày hiện tại
$sql = "UPDATE users SET status = 'inactive' WHERE last_payment_date < :currentDate";
$stmt = $conn->prepare($sql);
$stmt->execute([':currentDate' => $currentDate]);

echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái tài khoản thành công.']);
?>