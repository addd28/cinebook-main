<?php
// --- 1. CORS Configuration ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- 2. Database Connection ---
include_once 'config.php'; 

// --- 3. Receive JSON Data from Frontend ---
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

// Map variables from React payload
$user_id       = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$showtime_id   = isset($data['showtime_id']) ? (int)$data['showtime_id'] : 0;
$seat_ids      = isset($data['seat_ids']) ? $data['seat_ids'] : [];     
$seat_names    = isset($data['seat_names']) ? $data['seat_names'] : ""; 
$total_price   = isset($data['total_price']) ? $data['total_price'] : 0;
$selectedFoods = isset($data['foods']) ? $data['foods'] : []; // Array of food items

if ($user_id <= 0 || $showtime_id <= 0 || empty($seat_ids)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Start Transaction to ensure data integrity
$conn->begin_transaction();

try {
    // STEP 1: Insert into `bookings` table 
    // FIXED: Removed 'concessions' column because it doesn't exist in your schema
    $sql_booking = "INSERT INTO bookings (user_id, showtime_id, seats, total_price, status, created_at) 
                    VALUES (?, ?, ?, ?, 'paid', NOW())";
    
    $stmt = $conn->prepare($sql_booking);
    $stmt->bind_param("iisd", $user_id, $showtime_id, $seat_names, $total_price);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert booking: " . $stmt->error);
    }
    
    $booking_id = $conn->insert_id;

    // STEP 2: Insert into `tickets` table & Cleanup `seat_reservations`
    $stmt_ticket = $conn->prepare("INSERT INTO tickets (booking_id, seat_id, showtime_id) VALUES (?, ?, ?)");
    $stmt_del_res = $conn->prepare("DELETE FROM seat_reservations WHERE seat_id = ? AND showtime_id = ?");

    foreach ($seat_ids as $sid) {
        $sid_int = (int)$sid;
        
        // Add to permanent tickets table
        $stmt_ticket->bind_param("iii", $booking_id, $sid_int, $showtime_id);
        $stmt_ticket->execute();

        // Remove from temporary seat_reservations table
        $stmt_del_res->bind_param("ii", $sid_int, $showtime_id);
        $stmt_del_res->execute();
    }

    // STEP 3: Insert into `booking_details_food` (Handle Snacks & Drinks)
    if (!empty($selectedFoods)) {
        $sql_food = "INSERT INTO booking_details_food (booking_id, food_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_food = $conn->prepare($sql_food);
        
        foreach ($selectedFoods as $food) {
            $f_id = (int)$food['id'];
            $f_qty = (int)$food['qty'];
            $f_price = (double)$food['price'];
            
            if ($f_qty > 0) {
                $stmt_food->bind_param("iiid", $booking_id, $f_id, $f_qty, $f_price);
                $stmt_food->execute();
            }
        }
    }

    // All steps successful - Commit to database
    $conn->commit();

    echo json_encode([
        "success" => true, 
        "message" => "Booking completed successfully!",
        "booking_id" => $booking_id
    ]);

} catch (Exception $e) {
    // Something went wrong - Rollback all changes
    $conn->rollback();
    echo json_encode([
        "success" => false, 
        "message" => "Database Error: " . $e->getMessage()
    ]);
}

$conn->close();
?>