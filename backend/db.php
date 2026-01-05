<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$host = "localhost";
$user = "root";
$pass = "root"; // MAMP: 'root', XAMPP: ''
$dbname = "cinebook_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Thiết lập múi giờ Việt Nam để tính toán expires_at chính xác
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>