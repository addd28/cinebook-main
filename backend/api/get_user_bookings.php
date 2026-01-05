<?php
include_once 'config.php';

// Bật hiển thị lỗi để debug (Xóa sau khi xong)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$user_id) {
    echo json_encode(["message" => "User ID is required"]);
    exit;
}

// Câu truy vấn rút gọn để kiểm tra từng bảng
$sql = "SELECT 
            b.*, 
            m.title AS movie_title, 
            m.poster, 
            c.name AS cinema_name
        FROM bookings b
        LEFT JOIN showtimes st ON b.showtime_id = st.id
        LEFT JOIN movies m ON st.movie_id = m.id
        LEFT JOIN rooms r ON st.room_id = r.id
        LEFT JOIN cinemas c ON r.cinema_id = c.id
        WHERE b.user_id = $user_id
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    // Trả về lỗi SQL chính xác để chúng ta sửa
    http_response_code(500);
    echo json_encode(["sql_error" => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>