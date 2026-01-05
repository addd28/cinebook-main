<?php
include_once 'config.php';

$result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
$data = [];

while($row = $result->fetch_assoc()) {
    $row['image_url'] = $upload_url . "/news/" . $row['image'];
    $data[] = $row;
}

echo json_encode($data);
?>