<?php
// Cho phép cổng 5173 của Vite/React truy cập
header("Access-Control-Allow-Origin: http://localhost:5173"); 
header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include_once '../db.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = mysqli_real_escape_string($conn, $data->email);
    $password = mysqli_real_escape_string($conn, $data->password);

    // 1. Kiểm tra Admin
    $sql_admin = "SELECT id, name FROM admins WHERE email='$email' AND password='$password' LIMIT 1";
    $res_admin = $conn->query($sql_admin);

    if ($res_admin && $res_admin->num_rows > 0) {
        $admin = $res_admin->fetch_assoc();
        
        // Khởi tạo session để guard.php nhận diện
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        echo json_encode([
            "success" => true,
            "user" => ["id" => $admin['id'], "name" => $admin['name'], "role" => "admin"]
        ]);
        exit();
    }

    // 2. Kiểm tra User thường
    $sql_user = "SELECT id, name FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $res_user = $conn->query($sql_user);

    if ($res_user && $res_user->num_rows > 0) {
        $user = $res_user->fetch_assoc();
        echo json_encode([
            "success" => true,
            "user" => ["id" => $user['id'], "name" => $user['name'], "role" => "user"]
        ]);
        exit();
    }
    echo json_encode(["success" => false, "message" => "Invalid email or password!"]);
}
?>