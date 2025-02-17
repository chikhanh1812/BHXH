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

// Thêm tin tức vào cơ sở dữ liệu
$sql = "INSERT INTO news (title, content, image_url, article_url) VALUES (:title, :content, :image_url, :article_url)";
$stmt = $conn->prepare($sql);

try {
    $stmt->execute([
        ':title' => $data['title'],
        ':content' => $data['content'],
        ':image_url' => $data['image_url'],
        ':article_url' => $data['article_url']
    ]);
    echo json_encode(['success' => true, 'message' => 'Thêm tin tức thành công.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm tin tức: ' . $e->getMessage()]);
}
?>