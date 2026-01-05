<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once '../db.php';
include_once 'layout.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Lấy thông tin chi tiết hóa đơn
$query = "SELECT b.*, m.title, m.image as movie_image, c.name as cinema_name, r.name as room_name, 
                 st.show_date, st.show_time 
          FROM bookings b
          JOIN showtimes st ON b.showtime_id = st.id
          JOIN movies m ON st.movie_id = m.id
          JOIN rooms r ON st.room_id = r.id
          JOIN cinemas c ON r.cinema_id = c.id
          WHERE b.id = $booking_id AND b.user_id = $user_id";

$res = $conn->query($query);
$b = $res->fetch_assoc();

if (!$b) {
    die("<div class='container mt-5'>Booking not found or unauthorized access.</div>");
}

ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="display-4 text-success mb-2"><i class='bx bxs-check-circle'></i></div>
                <h2 class="fw-bold">Thank you for your purchase!</h2>
                <p class="text-muted">Your booking is confirmed. Please show the QR code below at the counter to get your tickets.</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="row g-0">
                    <div class="col-md-4 bg-dark d-none d-md-block">
                        <img src="../../uploads/movies/<?php echo $b['movie_image']; ?>" class="img-fluid h-100 w-100" style="object-fit: cover;">
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="fw-bold text-dark mb-1"><?php echo $b['title']; ?></h4>
                                    <span class="badge bg-warning text-dark"><?php echo $b['cinema_name']; ?></span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Booking ID</small>
                                    <span class="fw-bold">#<?php echo $b['id']; ?></span>
                                </div>
                            </div>

                            <hr class="border-secondary opacity-25">

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <small class="text-muted d-block">DATE</small>
                                    <span class="fw-bold"><i class='bx bx-calendar me-1'></i><?php echo date('M d, Y', strtotime($b['show_date'])); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">TIME</small>
                                    <span class="fw-bold"><i class='bx bx-time-five me-1'></i><?php echo date('H:i', strtotime($b['show_time'])); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">ROOM</small>
                                    <span class="fw-bold"><?php echo $b['room_name']; ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">SEATS</small>
                                    <span class="fw-bold text-primary"><?php echo $b['seats']; ?></span>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 mb-4" style="background-color: #f8f9fa; border-left: 4px solid var(--orange);">
                                <h6 class="fw-bold mb-2 small"><i class='bx bxs-drink me-1'></i> POPCORN & DRINKS</h6>
                                <p class="small text-muted mb-0">
                                    <?php echo !empty($b['concessions']) ? $b['concessions'] : "No snacks ordered."; ?>
                                </p>
                            </div>

                            <div class="text-center py-3 bg-white rounded-3 border border-dashed">
                                <p class="small text-muted mb-2">Scan this code at the cinema gate</p>
                                ; ?>]
                                <div class="mt-2 fw-mono small text-uppercase letter-spacing-2"><?php echo md5($b['id']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Booked on: <?php echo date('M d, Y H:i', strtotime($b['created_at'])); ?>
                        </div>
                        <div class="text-end">
                            <h4 class="fw-bold text-success mb-0">$<?php echo number_format($b['total_price']); ?></h4>
                            <small class="text-muted uppercase">Paid via Online Banking</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center no-print">
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4">
                    <i class='bx bx-printer me-1'></i> Print Ticket
                </a>
                <a href="index.php" class="btn btn-dark rounded-pill px-4">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f4f7f6; }
    .card { border: none; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }
    .letter-spacing-2 { letter-spacing: 2px; }
    
    @media print {
        .no-print, nav, footer { display: none !important; }
        body { background-color: #white; }
        .container { width: 100% !important; max-width: 100% !important; }
        .shadow-lg { box-shadow: none !important; }
    }
</style>

<?php
$content = ob_get_clean();
renderLayout("Booking Confirmation", $content);
?>