<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

// Kết nối đến cơ sở dữ liệu
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

// Lấy ID từ URL
$id = $_GET['id'];

// Xóa tin tức từ cơ sở dữ liệu
$sql = "DELETE FROM news WHERE id = :id";
$stmt = $conn->prepare($sql);

try {
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Xóa tin tức thành công.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa tin tức: ' . $e->getMessage()]);
}
?>