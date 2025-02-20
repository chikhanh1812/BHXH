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

$searchValue = $data['search-input'];

// Truy vấn thông tin người dùng
$sql = "SELECT * FROM users WHERE card_number = :searchValue OR id_number = :searchValue";
$stmt = $conn->prepare($sql);
$stmt->execute([':searchValue' => $searchValue]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.']);
    exit;
}

// Truy vấn hồ sơ khám bệnh
$sql = "SELECT * FROM medical_records WHERE card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':cardNumber' => $user['card_number']]);
$medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả về kết quả
echo json_encode([
    'success' => true,
    'user' => $user,
    'medicalRecords' => $medicalRecords
]);
?>