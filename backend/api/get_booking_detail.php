<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../db.php';

// 1. Kiểm tra ID từ GET request
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid Booking ID"]);
    exit();
}

/**
 * 2. Truy vấn SQL sử dụng Prepared Statement để bảo mật
 * Lấy dữ liệu từ 5 bảng: bookings, showtimes, movies, rooms, cinemas
 * GROUP_CONCAT giúp gộp danh sách đồ ăn từ bảng phụ booking_details_food
 */
$sql = "SELECT 
            b.id, 
            b.seats, 
            b.total_price, 
            b.created_at,
            m.title AS movie_title, 
            m.poster AS movie_poster, 
            st.show_date, 
            st.show_time, 
            r.name AS room_name, 
            c.name AS cinema_name,
            (SELECT GROUP_CONCAT(CONCAT(bdf.quantity, 'x ', f.name) SEPARATOR ', ') 
             FROM booking_details_food bdf 
             JOIN foods f ON bdf.food_id = f.id 
             WHERE bdf.booking_id = b.id) as foods_list
        FROM bookings b
        JOIN showtimes st ON b.showtime_id = st.id
        JOIN movies m ON st.movie_id = m.id
        JOIN rooms r ON st.room_id = r.id
        JOIN cinemas c ON r.cinema_id = c.id
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // 3. Tạo mã đặt vé (Booking Code) chuyên nghiệp
    $booking_code = "CB" . str_pad($row['id'], 6, "0", STR_PAD_LEFT);

    // 4. Định dạng dữ liệu theo chuẩn Tiếng Anh cho frontend
    $formatted_data = [
        "id"           => $row['id'],
        "booking_code" => $booking_code,
        "movie_title"  => mb_convert_case($row['movie_title'], MB_CASE_UPPER, "UTF-8"),
        "poster"       => $row['movie_poster'],
        "cinema_name"  => $row['cinema_name'],
        "room_name"    => $row['room_name'],
        "seats"        => $row['seats'],
        // Wednesday, Dec 31, 2025
        "show_date"    => date("l, M d, Y", strtotime($row['show_date'])), 
        // 14:30
        "show_time"    => date("H:i", strtotime($row['show_time'])),
        "total_price"  => (float)$row['total_price'],
        "foods"        => $row['foods_list'] ? $row['foods_list'] : "None",
        "created_at"   => date("M d, Y H:i", strtotime($row['created_at']))
    ];

    echo json_encode($formatted_data);
} else {
    // Trả về lỗi 404 nếu không tìm thấy hóa đơn
    http_response_code(404);
    echo json_encode(["message" => "Booking not found in our system."]);
}

$stmt->close();
$conn->close();
?>