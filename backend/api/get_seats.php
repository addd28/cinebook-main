<?php
// Tạm thời bật lỗi để bạn theo dõi, xóa khi xong dự án
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once 'config.php';

// 1. Kiểm tra kết nối
if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$showtime_id = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : 0;
if ($showtime_id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid showtime_id"]);
    exit;
}

// 2. Dọn dẹp ghế hết hạn (Nếu bảng seat_reservations tồn tại)
$conn->query("DELETE FROM seat_reservations WHERE expires_at < NOW() AND status IN ('pending', 'holding')");

// 3. Lấy thông tin phòng
$stmt_info = $conn->prepare("SELECT s.room_id, r.name as room_name FROM showtimes s JOIN rooms r ON s.room_id = r.id WHERE s.id = ?");
$stmt_info->bind_param("i", $showtime_id);
$stmt_info->execute();
$info = $stmt_info->get_result()->fetch_assoc();

if (!$info) {
    echo json_encode(["success" => false, "error" => "Showtime not found"]);
    exit;
}
$room_id = $info['room_id'];

// --- BƯỚC QUAN TRỌNG: KIỂM TRA BẢNG TỒN TẠI TRƯỚC KHI TRUY VẤN ---
$check_tickets = $conn->query("SHOW TABLES LIKE 'tickets'");
$has_tickets = $check_tickets->num_rows > 0;

$check_prices = $conn->query("SHOW TABLES LIKE 'ticket_prices'");
$has_prices = $check_prices->num_rows > 0;

$check_res = $conn->query("SHOW TABLES LIKE 'seat_reservations'");
$has_res = $check_res->num_rows > 0;

// 4. Xây dựng câu SQL linh hoạt (Dynamic SQL)
$sql = "SELECT s.id, s.seat_number, s.seat_type ";

// Thêm cột từ các bảng nếu chúng tồn tại
$sql .= ($has_prices) ? ", tp.price as db_price " : ", 80000 as db_price ";
$sql .= ($has_res) ? ", res.status as res_status, res.user_id as res_user_id " : ", NULL as res_status, NULL as res_user_id ";
$sql .= ($has_tickets) ? ", t.id as ticket_id " : ", NULL as ticket_id ";

$sql .= " FROM seats s ";

// Thêm các lệnh JOIN nếu bảng tồn tại
if ($has_prices) $sql .= " LEFT JOIN ticket_prices tp ON s.seat_type = tp.seat_type ";
if ($has_res)    $sql .= " LEFT JOIN seat_reservations res ON s.id = res.seat_id AND res.showtime_id = $showtime_id ";
if ($has_tickets) $sql .= " LEFT JOIN tickets t ON s.id = t.seat_id AND t.showtime_id = $showtime_id ";

$sql .= " WHERE s.room_id = $room_id ORDER BY LENGTH(s.seat_number) ASC, s.seat_number ASC";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$seats = [];
while ($row = $result->fetch_assoc()) {
    $status = 'available';
    
    // Kiểm tra trạng thái dựa trên bảng tickets (nếu có) và reservations (nếu có)
    if (isset($row['ticket_id']) && $row['ticket_id'] !== null) {
        $status = 'occupied';
    } else if (isset($row['res_status']) && ($row['res_status'] === 'pending' || $row['res_status'] === 'holding' || $row['res_status'] === 'confirmed')) {
        $status = ($row['res_status'] === 'confirmed') ? 'occupied' : 'holding';
    }

    $seats[] = [
        "id" => (int)$row['id'],
        "seat_number" => $row['seat_number'],
        "seat_type" => strtolower($row['seat_type'] ?? 'standard'),
        "price" => (int)$row['db_price'],
        "status" => $status,
        "reserved_by" => $row['res_user_id'] ? (int)$row['res_user_id'] : null
    ];
}

// 5. Kết quả trả về
echo json_encode([
    "success" => true,
    "room_name" => $info['room_name'],
    "seats" => $seats
]);

$stmt_info->close();
$conn->close();
?>