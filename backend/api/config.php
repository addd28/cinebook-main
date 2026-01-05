<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$conn = new mysqli("localhost", "root", "root", "cinebook_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Kết nối thất bại: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// Lấy giao thức http hoặc https
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST']; // Sẽ là localhost:8888

// Thiết lập Base URL chuẩn
$base_url = $protocol . "://" . $host; 
$upload_url = $base_url . "/uploads";

// Hàm định dạng URL ảnh
function formatImageUrl($image, $subfolder, $upload_url) {
    if (!$image) return "https://placehold.co/300x450?text=No+Poster";
    // Trả về: http://localhost:8888/uploads/movies/tên_file.jpg
    return $upload_url . "/" . $subfolder . "/" . $image;
}
?>