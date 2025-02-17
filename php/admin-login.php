<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

// Kiểm tra thông tin đăng nhập
$sql = "SELECT * FROM admin WHERE username = :username";
$stmt = $conn->prepare($sql);
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && $password === $admin['password']) {
    echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.']);
}
?>