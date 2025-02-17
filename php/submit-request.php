<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

// Sử dụng PHPMailer để gửi email
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

// Kiểm tra xem dữ liệu có được gửi lên không
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

// Lấy dữ liệu từ form
$cardNumber = $_POST['card-number'];
$requestType = $_POST['request-type'];
$companyName = $_POST['company-name'] ?? null;
$companyAddress = $_POST['company-address'] ?? null;
$incidentDate = $_POST['incident-date'] ?? null;
$reason = $_POST['reason'] ?? null;
$proof = $_FILES['proof'] ?? null;

// Kiểm tra xem các trường bắt buộc có được điền đầy đủ không
if (empty($cardNumber) || empty($requestType)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$sql = "SELECT id, email, fullname FROM users WHERE card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':cardNumber' => $cardNumber]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.']);
    exit;
}

// Xử lý file upload (nếu có)
$proofUrl = null;
if ($proof && $proof['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $proofName = basename($proof['name']);
    $proofPath = $uploadDir . $proofName;
    if (move_uploaded_file($proof['tmp_name'], $proofPath)) {
        $proofUrl = $proofPath;
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tải lên file.']);
        exit;
    }
}

// Lưu thông tin yêu cầu trợ cấp vào cơ sở dữ liệu
$sql = "INSERT INTO requests (user_id, request_type, company_name, company_address, incident_date, reason, proof_url, status) 
        VALUES (:user_id, :request_type, :company_name, :company_address, :incident_date, :reason, :proof_url, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':user_id' => $user['id'],
    ':request_type' => $requestType,
    ':company_name' => $companyName,
    ':company_address' => $companyAddress,
    ':incident_date' => $incidentDate,
    ':reason' => $reason,
    ':proof_url' => $proofUrl
]);

// Gửi email thông báo
$mail = new PHPMailer(true);

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

    // Thiết lập thông tin người gửi và người nhận
    $mail->setFrom('no-reply@baohiemxahoi.com', 'Bảo hiểm xã hội');
    $mail->addAddress($user['email']); // Gửi email đến người dùng

    // Thiết lập nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Thông báo: Yêu cầu trợ cấp đã được nhận';

    // Nội dung email

    switch ($requestType) {
        case 'accident':
            $emailContent = "
                <h2>Thông báo từ Bảo hiểm xã hội</h2>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi đã nhận được yêu cầu trợ cấp của bạn với thông tin như sau:</p>
                <ul>
                    <li><strong>Loại trợ cấp:</strong> Tai nạn lao động, bệnh nghề nghiệp</li>
                    <li><strong>Tên cơ quan/công ty:</strong> {$companyName}</li>
                    <li><strong>Địa chỉ:</strong> {$companyAddress}</li>
                    <li><strong>Thời gian sự cố:</strong> {$incidentDate}</li>
                    <li><strong>Lý do:</strong> {$reason}</li>
                </ul>
                <p>Chúng tôi sẽ xem xét yêu cầu của bạn và thông báo kết quả trong thời gian sớm nhất.</p>
                <p>Trân trọng,</p>
                <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
            ";
            break;
        case 'retired':
            $emailContent = "
                <h2>Thông báo từ Bảo hiểm xã hội</h2>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi đã nhận được yêu cầu trợ cấp của bạn với thông tin như sau:</p>
                <ul>
                    <li><strong>Loại trợ cấp:</strong> Đã về hưu</li>
                    <li><strong>Tên cơ quan/công ty:</strong> {$companyName}</li>
                    <li><strong>Địa chỉ:</strong> {$companyAddress}</li>
                    <li><strong>Thời gian về hưu:</strong> {$incidentDate}</li>
                </ul>
                <p>Chúng tôi sẽ xem xét yêu cầu của bạn và thông báo kết quả trong thời gian sớm nhất.</p>
                <p>Trân trọng,</p>
                <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
            ";
            break;
        case 'unemployed':
            $emailContent = "
                <h2>Thông báo từ Bảo hiểm xã hội</h2>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi đã nhận được yêu cầu trợ cấp của bạn với thông tin như sau:</p>
                <ul>
                    <li><strong>Loại trợ cấp:</strong> Nghỉ việc hơn 1 năm</li>
                    <li><strong>Tên cơ quan/công ty:</strong> {$companyName}</li>
                    <li><strong>Địa chỉ:</strong> {$companyAddress}</li>
                    <li><strong>Thời gian nghĩ việc:</strong> {$incidentDate}</li>
                    <li><strong>Lý do:</strong> {$reason}</li>
                </ul>
                <p>Chúng tôi sẽ xem xét yêu cầu của bạn và thông báo kết quả trong thời gian sớm nhất.</p>
                <p>Trân trọng,</p>
                <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
            ";
            break;
        case 'abroad':
            $emailContent = "
                <h2>Thông báo từ Bảo hiểm xã hội</h2>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi đã nhận được yêu cầu trợ cấp của bạn với thông tin như sau:</p>
                <ul>
                    <li><strong>Loại trợ cấp:</strong> Định cư nước ngoài</li>
                    <li><strong>Nơi di cư:</strong> {$companyAddress}</li>
                    <li><strong>Thời gian di cư:</strong> {$incidentDate}</li>
                    <li><strong>Lý do:</strong> {$reason}</li>
                </ul>
                <p>Chúng tôi sẽ xem xét yêu cầu của bạn và thông báo kết quả trong thời gian sớm nhất.</p>
                <p>Trân trọng,</p>
                <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
            ";
            break;
        case 'illness':
            $emailContent = "
                <h2>Thông báo từ Bảo hiểm xã hội</h2>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi đã nhận được yêu cầu trợ cấp của bạn với thông tin như sau:</p>
                <ul>
                    <li><strong>Loại trợ cấp:</strong> Bệnh hiểm nghèo</li>
                    <li><strong>Tên cơ quan/công ty:</strong> {$companyName}</li>
                    <li><strong>Địa chỉ:</strong> {$companyAddress}</li>
                    <li><strong>Thời gian mất khả năng làm việc:</strong> {$incidentDate}</li>
                    <li><strong>Lý do:</strong> {$reason}</li>
                </ul>
                <p>Chúng tôi sẽ xem xét yêu cầu của bạn và thông báo kết quả trong thời gian sớm nhất.</p>
                <p>Trân trọng,</p>
                <p><strong>Bảo hiểm xã hội Việt Nam</strong></p>
            ";
            break;
    }


    $mail->Body = $emailContent;

    // Gửi email
    $mail->send();

    // Trả về thông báo thành công
    echo json_encode(['success' => true, 'message' => 'Yêu cầu trợ cấp của bạn đã được gửi thành công. Email thông báo đã được gửi.']);
} catch (Exception $e) {
    // Nếu có lỗi khi gửi email, vẫn trả về thành công nhưng ghi log lỗi
    error_log('Lỗi khi gửi email: ' . $mail->ErrorInfo);
    echo json_encode(['success' => true, 'message' => 'Yêu cầu trợ cấp của bạn đã được gửi thành công. Tuy nhiên, có lỗi khi gửi email thông báo.']);
}
?>