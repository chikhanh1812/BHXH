<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");

require 'vendor/autoload.php'; // Load thư viện PHPMailer

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

// Xử lý yêu cầu OPTIONS (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Dừng xử lý nếu là yêu cầu OPTIONS
}

// Chỉ xử lý yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

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

$idNumber = $data['idNumber'];
$cardNumber = $data['cardNumber'];

// Truy vấn email từ database
$sql = "SELECT email FROM users WHERE id_number = :idNumber AND card_number = :cardNumber";
$stmt = $conn->prepare($sql);
$stmt->execute([':idNumber' => $idNumber, ':cardNumber' => $cardNumber]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);



if ($user) {
    $email = $user['email'];
    $otp = rand(100000, 999999); // Tạo mã OTP ngẫu nhiên

    // Gửi OTP qua email
    $mail = new PHPMailer(true);
    try {
        // Cấu hình SMTP và gửi email
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chikhanhlam0@gmail.com';
        $mail->Password = 'qhvtnfdnoevpzoim';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Thiết lập encoding UTF-8
        $mail->CharSet = 'UTF-8'; // Thiết lập bảng mã UTF-8
        $mail->Encoding = 'base64'; // Mã hóa nội dung email

        $mail->setFrom('no-reply@yourdomain.com', 'Bảo hiểm xã hội');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận thông tin Bảo Hiểm Xã Hội';
        $mail->Body = "Mã OTP của bạn là: $otp";

        $mail->send();
        echo json_encode(['success' => true, 'email' => $email, 'otp' => $otp]); // Trả về mã OTP
    } catch (Exception $e) {
        error_log('Lỗi khi gửi email: ' . $mail->ErrorInfo);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi email: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.']);
}
?>