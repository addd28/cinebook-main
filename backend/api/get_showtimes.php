<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include_once 'config.php';

$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
$cinema_id = isset($_GET['cinema_id']) ? (int)$_GET['cinema_id'] : 0;

if ($movie_id > 0 && $cinema_id > 0) {
    // 1. Câu lệnh SQL tối ưu: lấy thông tin suất chiếu và tên phòng
    $sql = "SELECT 
                s.id, 
                s.show_time, 
                s.show_date,
                r.id as room_id,
                r.name as room_name 
            FROM showtimes s
            INNER JOIN rooms r ON s.room_id = r.id
            WHERE s.movie_id = ? AND r.cinema_id = ?
            ORDER BY s.show_date ASC, s.show_time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movie_id, $cinema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $showtimes = [];
    while ($row = $result->fetch_assoc()) {
        // Chuyển 21:57:00 thành 21:57
        // Cách 1: Dùng substr
        $row['show_time'] = substr($row['show_time'], 0, 5);

        // Đảm bảo trường 'time' cũng được cập nhật nếu React dùng t.time
        $row['time'] = $row['show_time'];

        $row['price'] = 90000;
        $showtimes[] = $row;
    }

    echo json_encode($showtimes);
} else {
    echo json_encode([]);
}
