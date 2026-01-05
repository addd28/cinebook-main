<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once 'config.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();

$sql = "SELECT 
            b.id, 
            b.total_amount, 
            b.payment_status, 
            b.payment_method,
            m.title as movie_title, 
            m.poster_url as movie_poster,
            c.name as cinema_name,
            s.show_date,
            s.start_time as showtime,
            GROUP_CONCAT(st.seat_number) as seats
        FROM bookings b
        JOIN showtimes s ON b.showtime_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN cinemas c ON s.cinema_id = c.id
        JOIN tickets t ON b.id = t.booking_id
        JOIN seats st ON t.seat_id = st.id
        WHERE b.user_id = ?
        GROUP BY b.id
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    // Chuyển chuỗi ghế (A1,A2) thành mảng để giống với code React của bạn
    $row['seats'] = explode(',', $row['seats']);
    $history[] = $row;
}

echo json_encode($history);
?>