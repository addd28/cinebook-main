<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection via config.php
include_once 'config.php';

// Receive data from React (JSON Body)
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id) && !empty($data->movie_id) && !empty($data->rating)) {
    $user_id  = (int)$data->user_id;
    $movie_id = (int)$data->movie_id;
    $rating   = (int)$data->rating; // Value from 1 - 5
    $comment  = isset($data->comment) ? htmlspecialchars(trim($data->comment)) : "";

    // --- STEP 1: VERIFY PURCHASE (VERIFIED PURCHASE ONLY) ---
    // Users can only rate if they have at least 1 booking with 'confirmed' or 'paid' status for this movie
    $check_purchase = $conn->prepare("
        SELECT b.id 
        FROM bookings b
        JOIN showtimes s ON b.showtime_id = s.id
        WHERE b.user_id = ? AND s.movie_id = ? AND b.status IN ('confirmed', 'paid')
        LIMIT 1
    ");
    $check_purchase->bind_param("ii", $user_id, $movie_id);
    $check_purchase->execute();
    $purchase_result = $check_purchase->get_result();
    
    if ($purchase_result->num_rows === 0) {
        echo json_encode([
            "success" => false, 
            "message" => "You haven't purchased a ticket for this movie. Only customers who have seen the movie can leave a review!"
        ]);
        exit;
    }

    // --- STEP 2: HANDLE REVIEW SAVING ---
    // Check if the user has already rated this movie to decide between INSERT or UPDATE
    $check_exists = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND movie_id = ?");
    $check_exists->bind_param("ii", $user_id, $movie_id);
    $check_exists->execute();
    $exists_result = $check_exists->get_result();
    
    if ($exists_result->num_rows > 0) {
        // Update existing review
        $sql = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND movie_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $rating, $comment, $user_id, $movie_id);
    } else {
        // Insert new review (default status = 1 means visible immediately)
        $sql = "INSERT INTO reviews (user_id, movie_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, 1, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $comment);
    }

    if ($stmt->execute()) {
        // --- STEP 3: UPDATE MOVIE AVERAGE RATING ---
        // Calculate average based on approved reviews (status = 1)
        $sql_avg = "SELECT AVG(rating) as avg_rating FROM reviews WHERE movie_id = ? AND status = 1";
        $stmt_avg = $conn->prepare($sql_avg);
        $stmt_avg->bind_param("i", $movie_id);
        $stmt_avg->execute();
        $res_avg = $stmt_avg->get_result()->fetch_assoc();
        
        $new_avg = round($res_avg['avg_rating'], 1); // Round to 1 decimal place (e.g., 4.5)

        // Update the new value in movies table for Frontend display
        $update_movie = $conn->prepare("UPDATE movies SET rating_avg = ? WHERE id = ?");
        $update_movie->bind_param("di", $new_avg, $movie_id);
        $update_movie->execute();

        echo json_encode([
            "success" => true, 
            "message" => "Thank you for your review! Your feedback helps us improve."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "System error: Unable to save review data."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid data received or missing required information."]);
}

$conn->close();
?>