<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once 'config.php'; 

$data = json_decode(file_get_contents("php://input"));
if (!$data) exit;

$action = $data->action; 
$seat_id = (int)$data->seat_id;
$showtime_id = (int)$data->showtime_id;
$user_id = (int)$data->user_id;

if ($action == 'hold') {
    // 1. Kiểm tra xem ghế đã bị giữ bởi người khác chưa
    $check_sql = "SELECT id FROM seat_reservations 
                  WHERE seat_id = ? AND showtime_id = ? 
                  AND expires_at > NOW()";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $seat_id, $showtime_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows == 0) {
        // 2. Chèn dữ liệu mới vào bảng (Sẽ thấy dữ liệu xuất hiện ở đây)
        $sql = "INSERT INTO seat_reservations (showtime_id, seat_id, user_id, reserved_at, expires_at, status) 
                VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE), 'holding')";
        $insert = $conn->prepare($sql);
        $insert->bind_param("iii", $showtime_id, $seat_id, $user_id);
        
        if ($insert->execute()) {
            echo json_encode(["success" => true, "message" => "Đã giữ ghế trong 5 phút"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi database"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Ghế này đã có người giữ"]);
    }
} 
elseif ($action == 'release') {
    // Xóa dữ liệu khi người dùng bỏ chọn ghế
    $sql = "DELETE FROM seat_reservations WHERE seat_id = ? AND showtime_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $seat_id, $showtime_id, $user_id);
    $stmt->execute();
    echo json_encode(["success" => true]);
}
?>