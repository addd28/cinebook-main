<?php
include_once 'config.php';

// Cấp quyền CORS để React (Port 3000) có thể truy cập
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$result = $conn->query("SELECT * FROM cinemas ORDER BY city ASC");
$data = [];

while($row = $result->fetch_assoc()) {
    /** * KIỂM TRA: 
     * Nếu cột image_url trong database đã lưu full link "http://localhost:8888/..." 
     * thì chúng ta chỉ việc giữ nguyên. 
     * Nếu trong database chỉ lưu tên file "123.jpg", hãy dùng:
     * $row['image_url'] = "http://localhost:8888/uploads/cinemas/" . $row['image_url'];
     */
    
    // Nếu image_url trống hoặc null, mới dùng ảnh mặc định
    if (empty($row['image_url'])) {
        $row['image_url'] = "http://localhost:8888/uploads/cinemas/default_cinema.jpg";
    }

    $data[] = $row;
}

echo json_encode($data);
?>