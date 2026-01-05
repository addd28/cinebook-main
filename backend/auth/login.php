<?php
// backend/api/login.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once '../db.php'; // Đường dẫn đến file db.php của bạn

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = mysqli_real_escape_string($conn, $data->email);
    $password = mysqli_real_escape_string($conn, $data->password);

    // 1. Kiểm tra trong bảng admins
    $sql_admin = "SELECT id, name, 'admin' as role FROM admins WHERE email='$email' AND password='$password' LIMIT 1";
    $res_admin = $conn->query($sql_admin);

    if ($res_admin && $res_admin->num_rows > 0) {
        $admin = $res_admin->fetch_assoc();
        echo json_encode(["success" => true, "user" => $admin]);
        exit();
    }

    // 2. Kiểm tra trong bảng users (nếu có)
    $sql_user = "SELECT id, fullname as name, 'user' as role FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $res_user = $conn->query($sql_user);

    if ($res_user && $res_user->num_rows > 0) {
        $user = $res_user->fetch_assoc();
        echo json_encode(["success" => true, "user" => $user]);
        exit();
    }

    echo json_encode(["success" => false, "message" => "Sai email hoặc mật khẩu!"]);
}