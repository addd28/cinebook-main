<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once '../db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    $name = mysqli_real_escape_string($conn, $data->name);
    $email = mysqli_real_escape_string($conn, $data->email);
    $password = mysqli_real_escape_string($conn, $data->password);
    $phone = isset($data->phone) ? mysqli_real_escape_string($conn, $data->phone) : "";
    $city = isset($data->city) ? mysqli_real_escape_string($conn, $data->city) : "";

    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email đã tồn tại!"]);
    } else {
        $sql = "INSERT INTO users (name, email, password, phone, city) VALUES ('$name', '$email', '$password', '$phone', '$city')";
        if ($conn->query($sql)) echo json_encode(["success" => true]);
        else echo json_encode(["success" => false, "message" => $conn->error]);
    }
}
?>