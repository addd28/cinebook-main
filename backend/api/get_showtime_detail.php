<?php
// backend/api/get_showtime_detail.php
include_once 'config.php'; 

$showtime_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($showtime_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid Showtime ID"]);
    exit;
}

// Truy vấn lấy chi tiết suất chiếu, phim và rạp dựa trên ID
$query = "SELECT 
            st.id, 
            st.show_time, 
            st.show_date,
            st.movie_id,
            st.room_id,
            m.title as movie_title,
            m.image as movie_image,
            c.id as cinema_id,
            c.name as cinema_name,
            r.name as room_name
          FROM showtimes st
          JOIN movies m ON st.movie_id = m.id
          JOIN rooms r ON st.room_id = r.id
          JOIN cinemas c ON r.cinema_id = c.id
          WHERE st.id = $showtime_id 
          LIMIT 1";

$res = $conn->query($query);

if($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    // Đồng bộ URL ảnh theo hàm formatImageUrl trong config.php của bạn
    $row['movie_image_url'] = formatImageUrl($row['movie_image'], 'movies', $upload_url);
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Showtime not found."]);
}

$conn->close();
?>