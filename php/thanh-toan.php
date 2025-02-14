<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

require 'vendor/autoload.php'; // Đảm bảo bạn đã cài đặt thư viện PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
$daysToPay = $data['daysToPay']; // Số ngày thanh toán trước (30, 90, 180, 365)

// Lấy thông tin người dùng
$sql = "SELECT * FROM users WHERE card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':cardNumber' => $cardNumber]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.']);
    exit;
}

// Tính toán số tiền cần thanh toán
$feePerDay = 10000; // Phí mỗi ngày
$amount = $daysToPay * $feePerDay;

// Cập nhật last_payment_date và trạng thái tài khoản thành active
$newLastPaymentDate = date('Y-m-d', strtotime("+$daysToPay days"));
$sql = "UPDATE users SET last_payment_date = :lastPaymentDate, status = 'active' WHERE card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':lastPaymentDate' => $newLastPaymentDate, ':cardNumber' => $cardNumber]);

// Thêm thông tin thanh toán vào bảng payments
$sql = "INSERT INTO payments (user_id, amount, status) VALUES (:user_id, :amount, 'completed')";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':user_id' => $user['id'],
    ':amount' => $amount
]);

// Gửi email thông báo thanh toán thành công
$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP và gửi email
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
    $mail->SMTPAuth = true;
    $mail->Username = 'chikhanhlam0@gmail.com'; // Thay bằng email của bạn
    $mail->Password = 'qhvtnfdnoevpzoim'; // Thay bằng mật khẩu email của bạn
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Thiết lập encoding UTF-8
    $mail->CharSet = 'UTF-8'; // Thiết lập bảng mã UTF-8
    $mail->Encoding = 'base64'; // Mã hóa nội dung email

    $mail->setFrom('no-reply@yourdomain.com', 'Bảo hiểm xã hội');
    $mail->addAddress($user['email']); // Gửi email đến người dùng

    $mail->isHTML(true);
    $mail->Subject = 'Thanh toán thành công';
    $mail->Body = "Bạn đã thanh toán thành công số tiền $amount VND cho $daysToPay ngày. Ngày hết hạn bảo hiểm mới của bạn là $newLastPaymentDate.";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Thanh toán thành công. Email thông báo đã được gửi.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi email: ' . $mail->ErrorInfo]);
}
?>