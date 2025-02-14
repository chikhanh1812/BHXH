<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$cardNumber = $data['cardNumber'];

// Lấy thông tin người dùng và kiểm tra nợ phí
$sql = "SELECT * FROM users WHERE card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':cardNumber' => $cardNumber]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.']);
    exit;
}

$lastPaymentDate = new DateTime($user['last_payment_date']);
$currentDate = new DateTime();

// Kiểm tra xem last_payment_date có phải là ngày trong tương lai hay không
if ($lastPaymentDate > $currentDate) {
    // Nếu last_payment_date là ngày trong tương lai, người dùng không có nợ phí
    echo json_encode([
        'success' => true,
        'user' => $user, // Trả về thông tin người dùng
        'debt' => [
            'amount' => 0,
            'daysUnpaid' => 0
        ],
        'lastPaymentDate' => $user['last_payment_date'],
        'expirationDate' => $user['last_payment_date'] // Thêm thông tin ngày hết hạn
    ]);
} else {
    // Nếu last_payment_date là ngày trong quá khứ, tính toán số ngày chưa thanh toán
    $interval = $currentDate->diff($lastPaymentDate);
    $daysUnpaid = $interval->days;

    $feePerDay = 10000; // Phí mỗi ngày
    $amount = $daysUnpaid * $feePerDay;

    echo json_encode([
        'success' => true,
        'user' => $user, // Trả về thông tin người dùng
        'debt' => [
            'amount' => $amount,
            'daysUnpaid' => $daysUnpaid
        ],
        'lastPaymentDate' => $user['last_payment_date']
    ]);
}