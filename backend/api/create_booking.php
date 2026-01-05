<?php
include_once 'config.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->user_id) || empty($data->showtime_id) || empty($data->seats)) {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin đặt vé"]);
    exit;
}

$conn->begin_transaction();

try {
    // --- BƯỚC MỚI: KIỂM TRA TRÙNG GHẾ TRƯỚC KHI ĐẶT ---
    foreach ($data->seats as $seat_number) {
        // 1. Lấy seat_id từ seat_number
        $st_seat = $conn->prepare("SELECT id FROM seats WHERE seat_number = ?");
        $st_seat->bind_param("s", $seat_number);
        $st_seat->execute();
        $res_seat = $st_seat->get_result()->fetch_assoc();
        
        if (!$res_seat) {
            throw new Exception("Ghế $seat_number không tồn tại trong hệ thống.");
        }
        $seat_id = $res_seat['id'];

        // 2. Kiểm tra xem ghế này đã được đặt cho SUẤT CHIẾU NÀY chưa
        // Kiểm tra chéo qua bảng tickets và bookings
        $check_sql = "SELECT t.id FROM tickets t 
                      JOIN bookings b ON t.booking_id = b.id 
                      WHERE b.showtime_id = ? AND t.seat_id = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("ii", $data->showtime_id, $seat_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();

        if ($check_result->num_rows > 0) {
            // Nếu tìm thấy bất kỳ dòng nào, nghĩa là ghế đã bị người khác thanh toán trước
            throw new Exception("Ghế $seat_number đã có người đặt trước đó. Vui lòng chọn ghế khác.");
        }
    }
    // --- KẾT THÚC KIỂM TRA ---

    // 1. Tạo Booking chính (Chỉ chạy khi bước kiểm tra trên không có lỗi)
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, showtime_id, total_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->bind_param("iids", $data->user_id, $data->showtime_id, $data->total_amount, $data->payment_method);
    $stmt->execute();
    $booking_id = $conn->insert_id;

    // 2. Tạo Tickets và giải phóng bảng seat_reservations
    foreach ($data->seats as $seat_number) {
        // Tìm lại seat_id (Đã check ở trên nên chắc chắn có)
        $st_seat = $conn->prepare("SELECT id FROM seats WHERE seat_number = ?");
        $st_seat->bind_param("s", $seat_number);
        $st_seat->execute();
        $seat_id = $st_seat->get_result()->fetch_assoc()['id'];

        // Chèn vào bảng tickets
        $price = $data->total_amount / count($data->seats);
        $stmt_t = $conn->prepare("INSERT INTO tickets (booking_id, seat_id, price) VALUES (?, ?, ?)");
        $stmt_t->bind_param("iid", $booking_id, $seat_id, $price);
        $stmt_t->execute();
        
        // Xóa khỏi bảng giữ chỗ tạm thời (seat_reservations)
        $del_res = $conn->prepare("DELETE FROM seat_reservations WHERE seat_id = ? AND showtime_id = ?");
        $del_res->bind_param("ii", $seat_id, $data->showtime_id);
        $del_res->execute();
    }

    $conn->commit();
    echo json_encode(["success" => true, "booking_id" => $booking_id, "message" => "Đặt vé thành công"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>