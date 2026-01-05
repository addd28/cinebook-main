<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu không có session admin, đẩy về trang login của React (Vite)
if (!isset($_SESSION['admin_id'])) {
    header("Location: http://localhost:5173/login");
    exit();
}
?>