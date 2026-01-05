<?php
// 1. Initialize session (Prevent multiple call errors)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Access Control
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login if not authorized
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

// 3. Connect core files
include_once '../db.php';

$filename = "Revenue_Cinebook_" . date('d-m-Y') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");

// Output BOM for UTF-8 compatibility in Excel
echo "\xEF\xBB\xBF"; 

// Column Headers
echo "Order ID" . "\t" . "Booking Date" . "\t" . "Customer" . "\t" . "Movie Title" . "\t" . "Revenue" . "\n";

// Query: Using 'paid' status to match your database structure
$sql = "SELECT b.*, u.*, m.title 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN showtimes st ON b.showtime_id = st.id
        JOIN movies m ON st.movie_id = m.id
        WHERE b.status = 'paid' 
        ORDER BY b.created_at DESC";

$res = $conn->query($sql);

if ($res) {
    while($row = $res->fetch_assoc()) {
        // Handle customer name fallback
        $name = $row['fullname'] ?? $row['username'] ?? $row['name'] ?? 'Guest';
        
        echo "#BK-" . $row['id'] . "\t";
        echo date('d/m/Y H:i', strtotime($row['created_at'])) . "\t";
        echo $name . "\t";
        echo $row['title'] . "\t";
        echo $row['total_price'] . "\n";
    }
}
exit();
?>