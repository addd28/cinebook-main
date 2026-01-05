<?php
// backend/auth/logout.php
session_start();

// 1. Xóa sạch biến Session
$_SESSION = array();

// 2. Hủy Cookie Session để guard.php không còn nhận diện được
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hủy Session trên server
session_destroy();

// 4. QUAN TRỌNG: Đẩy về trang Login của React (Vite)
header("Location: http://localhost:5173/login");
exit();
?>