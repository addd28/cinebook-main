<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config.php';

// Kiểm tra tham số id từ URL
$cinema_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cinema_id <= 0) {
    echo json_encode([
        "error" => "ID rạp không hợp lệ",
        "received_id" => $_GET['id'] ?? 'null'
    ]);
    exit;
}

try {
    // 1. Lấy thông tin rạp
    $cinema_query = "SELECT * FROM cinemas WHERE id = $cinema_id";
    $cinema_res = $conn->query($cinema_query);
    $cinema = $cinema_res->fetch_assoc();

    if (!$cinema) {
        echo json_encode(["error" => "Không tìm thấy rạp"]);
        exit;
    }

    // 2. Lấy danh sách phim có suất chiếu tại rạp này
    // Query này nối bảng: movies -> showtimes -> rooms
    $movies_query = "
        SELECT DISTINCT m.* FROM movies m
        JOIN showtimes s ON m.id = s.movie_id
        JOIN rooms r ON s.room_id = r.id
        WHERE r.cinema_id = $cinema_id
    ";
    $movies_res = $conn->query($movies_query);

    $movies = [];
    while ($movie = $movies_res->fetch_assoc()) {
        $movie_id = $movie['id'];

        // 3. Lấy các suất chiếu của phim tại rạp này
        // Lưu ý: bảng rooms của bạn dùng cột 'name' cho tên phòng
        $st_query = "
            SELECT s.id, s.show_time as start_time, s.show_date, r.name as room_name 
            FROM showtimes s
            JOIN rooms r ON s.room_id = r.id
            WHERE s.movie_id = $movie_id AND r.cinema_id = $cinema_id
            ORDER BY s.show_time ASC
        ";
        $st_res = $conn->query($st_query);
        
        $showtimes = [];
        while ($st = $st_res->fetch_assoc()) {
            $showtimes[] = $st;
        }

        $movie['showtimes'] = $showtimes;
        $movies[] = $movie;
    }

    echo json_encode([
        "cinema" => $cinema,
        "movies" => $movies
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>