<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Lấy giá trị tìm kiếm từ query string
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập thông tin tìm kiếm.']);
    exit;
}

// Truy vấn tìm kiếm yêu cầu dựa trên CCCD/CMND hoặc số thẻ BHXH
$sql = "SELECT r.*, u.fullname, u.id_number, u.card_number 
        FROM requests r 
        JOIN users u ON r.user_id = u.id 
        WHERE u.id_number LIKE :search OR u.card_number LIKE :search";
$stmt = $conn->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($requests) {
    echo json_encode(['success' => true, 'requests' => $requests]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy yêu cầu nào phù hợp.']);
}
?>