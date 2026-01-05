<?php
include_once 'config.php';

$result = $conn->query("SELECT * FROM foods WHERE status = 1 ORDER BY id DESC");
$data = [];

while($row = $result->fetch_assoc()) {
    $row['image_url'] = $upload_url . "/foods/" . $row['image'];
    $row['price'] = (int)$row['price'];
    $data[] = $row;
}

echo json_encode($data);
?>