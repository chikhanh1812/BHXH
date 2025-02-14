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

 // Gửi email xác nhận đăng ký thành công
 require 'vendor/autoload.php'; // Load thư viện PHPMailer
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

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

// Hàm kiểm tra số điện thoại đã tồn tại chưa
function isPhoneExists($conn, $phone) {
    $sql = "SELECT COUNT(*) FROM users WHERE phone = :phone";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':phone' => $phone]);
    return $stmt->fetchColumn() > 0;
}

// Hàm kiểm tra email đã tồn tại chưa
function isEmailExists($conn, $email) {
    $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn() > 0;
}

// Hàm kiểm tra số CMND/CCCD đã tồn tại chưa
function isIdNumberExists($conn, $idNumber) {
    $sql = "SELECT COUNT(*) FROM users WHERE id_number = :idNumber";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idNumber' => $idNumber]);
    return $stmt->fetchColumn() > 0;
}

// Hàm tạo Số Thẻ BHXH ngẫu nhiên
function generateCardNumber($conn) {
    do {
        $cardNumber = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $sql = "SELECT COUNT(*) FROM users WHERE card_number = :cardNumber";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':cardNumber' => $cardNumber]);
    } while ($stmt->fetchColumn() > 0);

    return $cardNumber;
}

// Kiểm tra các thông tin trước khi đăng ký
if (isPhoneExists($conn, $data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Số điện thoại đã tồn tại!']);
    exit;
}

if (isEmailExists($conn, $data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email đã tồn tại!']);
    exit;
}

if (isIdNumberExists($conn, $data['idNumber'])) {
    echo json_encode(['success' => false, 'message' => 'Số CMND/CCCD đã tồn tại!']);
    exit;
}

// Tạo Số Thẻ BHXH ngẫu nhiên
$cardNumber = generateCardNumber($conn);

// Chuẩn bị câu lệnh SQL để thêm người dùng mới
$sql = "INSERT INTO users (fullname, phone, email, address, dob, gender, id_number, card_number) 
        VALUES (:fullname, :phone, :email, :address, :dob, :gender, :idNumber, :cardNumber)";
$stmt = $conn->prepare($sql);

// Thực thi câu lệnh
try {
    $stmt->execute([
        ':fullname' => $data['fullname'],
        ':phone' => $data['phone'],
        ':email' => $data['email'],
        ':address' => $data['address'],
        ':dob' => $data['dob'],
        ':gender' => $data['gender'],
        ':idNumber' => $data['idNumber'],
        ':cardNumber' => $cardNumber
    ]);


    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'chikhanhlam0@gmail.com'; // Email của bạn
        $mail->Password = 'qhvtnfdnoevpzoim'; // Mật khẩu email của bạn
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Thiết lập encoding UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Thiết lập thông tin người gửi và người nhận
        $mail->setFrom('no-reply@yourdomain.com', 'Bảo hiểm xã hội');
        $mail->addAddress($data['email']); // Gửi đến email của người dùng

        // Thiết lập nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đăng ký thành công - Bảo hiểm xã hội';
        $mail->Body = "
            <h1>Chúc mừng bạn đã đăng ký thành công!</h1>
            <p>Dưới đây là thông tin đăng ký của bạn:</p>
            <ul>
                <li><strong>Họ và tên:</strong> {$data['fullname']}</li>
                <li><strong>Số điện thoại:</strong> {$data['phone']}</li>
                <li><strong>Email:</strong> {$data['email']}</li>
                <li><strong>Địa chỉ:</strong> {$data['address']}</li>
                <li><strong>Ngày sinh:</strong> {$data['dob']}</li>
                <li><strong>Giới tính:</strong> {$data['gender']}</li>
                <li><strong>Số CMND/CCCD:</strong> {$data['idNumber']}</li>
                <li><strong>Số thẻ BHXH:</strong> {$cardNumber}</li>
            </ul>
            <p>Vui lòng thanh toán phí để kích hoạt tài khoản của bạn.</p>
            <p>Trân trọng,</p>
            <p>Bảo hiểm xã hội Việt Nam</p>
        ";

        // Gửi email
        $mail->send();

        // Trả về kết quả thành công
        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác nhận.',
            'cardNumber' => $cardNumber,
            'idNumber' => $data['idNumber']
        ]);
    } catch (Exception $e) {
        // Nếu có lỗi khi gửi email, vẫn trả về kết quả thành công nhưng ghi log lỗi
        error_log('Lỗi khi gửi email: ' . $mail->ErrorInfo);
        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công, nhưng có lỗi khi gửi email xác nhận.',
            'cardNumber' => $cardNumber,
            'idNumber' => $data['idNumber']
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage()]);
}
?>