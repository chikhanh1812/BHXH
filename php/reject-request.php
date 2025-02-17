<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

require 'vendor/autoload.php'; // Đảm bảo bạn đã cài đặt thư viện PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

$id = $_GET['id'];

// Lấy thông tin yêu cầu và email người dùng
$sql = "SELECT r.*, u.fullname, u.id_number, u.card_number, u.email 
        FROM requests r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy yêu cầu.']);
    exit;
}

// Cập nhật trạng thái yêu cầu thành "rejected"
$sql = "UPDATE requests SET status = 'rejected' WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);

// Gửi email thông báo yêu cầu bị từ chối
$mail = new PHPMailer(true);

function getRequestType($type) {
    switch ($type) {
        case 'accident':
            return 'Tai nạn lao động, bệnh nghề nghiệp';
        case 'retired':
            return 'Đã về hưu';
        case 'unemployed':
            return 'Nghỉ việc hơn 1 năm';
        case 'abroad':
            return 'Định cư nước ngoài';
        case 'illness':
            return 'Bệnh hiểm nghèo';
        default:
            return $type;
    }
}

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
    $mail->SMTPAuth = true;
    $mail->Username = 'chikhanhlam0@gmail.com'; // Thay bằng email của bạn
    $mail->Password = 'qhvtnfdnoevpzoim'; // Thay bằng mật khẩu email của bạn
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Thiết lập encoding UTF-8
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('no-reply@yourdomain.com', 'Bảo hiểm xã hội');
    $mail->addAddress($request['email']); // Gửi email đến người dùng

    $mail->isHTML(true);
    $mail->Subject = 'Yêu cầu trợ cấp của bạn đã bị từ chối';
    $mail->Body = "
        <p>Yêu cầu trợ cấp của bạn đã bị từ chối. Dưới đây là thông tin chi tiết:</p>
        <ul>
            <li><strong>Họ và tên:</strong> {$request['fullname']}</li>
            <li><strong>CCCD/CMND:</strong> {$request['id_number']}</li>
            <li><strong>Số thẻ BHXH:</strong> {$request['card_number']}</li>
            <li><strong>Loại trợ cấp:</strong> " . getRequestType($request['request_type']) . "</li>
        </ul>
        <p>Vui lòng liên hệ với chúng tôi để biết thêm chi tiết.</p>
        <p>Trân trọng,</p>
        <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Yêu cầu đã bị từ chối và email thông báo đã được gửi.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi email: ' . $mail->ErrorInfo]);
}
?>