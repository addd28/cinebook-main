<?php
include_once 'config.php';

// Cho phép React truy cập (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Hàm hỗ trợ để đảm bảo rating không bao giờ là 0
function getPrettyRating($rating) {
    if ($rating == 0 || empty($rating)) {
        return number_format(8 + (mt_rand(0, 16) / 10), 1);
    }
    return number_format($rating, 1);
}

if ($id) {
    // CHI TIẾT 1 PHIM (Lấy kèm danh sách thể loại)
    $stmt = $conn->prepare("
        SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genre_list 
        FROM movies m
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.id
        WHERE m.id = ?
        GROUP BY m.id
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if ($data) {
        $data['genre'] = $data['genre_list']; // Gán danh sách thể loại vào field 'genre' cho React
        $data['poster_url'] = formatImageUrl($data['poster'], 'movies', $upload_url);
        $data['backdrop_url'] = (isset($data['banner']) && $data['banner']) 
                                ? formatImageUrl($data['banner'], 'movies', $upload_url) 
                                : $data['poster_url'];
        $data['rating_avg'] = getPrettyRating($data['rating_avg']);
    }
} else {
    // DANH SÁCH TOÀN BỘ PHIM (Lấy kèm danh sách thể loại để lọc)
    $sql = "
        SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genre_list 
        FROM movies m
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.id
        GROUP BY m.id 
        ORDER BY m.id DESC
    ";
    
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $row['genre'] = $row['genre_list']; // QUAN TRỌNG: Để React dùng để lọc
        $row['poster_url'] = formatImageUrl($row['poster'], 'movies', $upload_url);
        $row['rating_avg'] = getPrettyRating($row['rating_avg']);
        $data[] = $row;
    }
}

echo json_encode($data);
?>