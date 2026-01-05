<?php
// 1. Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Access Control
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

// 3. Include Core Files
include_once '../db.php';
include_once 'layout.php';

$today = date('Y-m-d');
$status_filter = "confirmed"; // Paid status

// 1. General Statistics
$stats_today = $conn->query("SELECT SUM(total_price) as sum, COUNT(*) as count FROM bookings WHERE status='$status_filter' AND DATE(created_at) = '$today'")->fetch_assoc();
$total_all_time = $conn->query("SELECT SUM(total_price) as sum FROM bookings WHERE status='$status_filter'")->fetch_assoc();

ob_start();
?>

<div class="content-wrapper p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Reports & Analytics</h2>
            <p class="text-muted small">Real-time business performance data</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-dark rounded-pill px-3" onclick="window.print()">
                <i class='bx bx-printer me-1'></i> Print PDF
            </button>
            <a href="export_excel.php" class="btn btn-success rounded-pill px-3">
                <i class='bx bx-spreadsheet me-1'></i> Export Excel
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: #e3f2fd;">
                <small class="text-muted fw-bold">TODAY'S REVENUE</small>
                <h3 class="fw-bold mt-2">$<?php echo number_format($stats_today['sum'] ?? 0); ?></h3>
                <div class="text-success small"><i class='bx bx-trending-up'></i> System Stable</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: #f1f8e9;">
                <small class="text-muted fw-bold">TICKETS SOLD TODAY</small>
                <h3 class="fw-bold mt-2"><?php echo $stats_today['count'] ?? 0; ?> tickets</h3>
                <div class="text-muted small">Successful transactions</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: #fff3e0;">
                <small class="text-muted fw-bold">TOTAL REVENUE</small>
                <h3 class="fw-bold mt-2">$<?php echo number_format($total_all_time['sum'] ?? 0); ?></h3>
                <div class="text-muted small">All-time accumulation</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-4"><i class='bx bxs-hot text-danger me-2'></i>Top 5 Best Selling Movies</h5>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <tbody>
                            <?php
                            $sql_top = "SELECT m.title, SUM(b.total_price) as revenue 
                                        FROM bookings b 
                                        JOIN showtimes st ON b.showtime_id = st.id 
                                        JOIN movies m ON st.movie_id = m.id 
                                        WHERE b.status = '$status_filter'
                                        GROUP BY m.id ORDER BY revenue DESC LIMIT 5";
                            $res_top = $conn->query($sql_top);
                            while($top = $res_top->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="ps-0 text-truncate" style="max-width: 200px;"><?php echo $top['title']; ?></td>
                                <td class="text-end fw-bold text-orange">$<?php echo number_format($top['revenue']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-4">Latest Transactions</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th>Customer</th>
                                <th>Movie</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_recent = "SELECT b.created_at, u.*, m.title as movie_title, b.total_price 
                                           FROM bookings b
                                           JOIN users u ON b.user_id = u.id
                                           JOIN showtimes st ON b.showtime_id = st.id
                                           JOIN movies m ON st.movie_id = m.id
                                           WHERE b.status = '$status_filter'
                                           ORDER BY b.created_at DESC LIMIT 6";
                            $res_recent = $conn->query($sql_recent);
                            while($order = $res_recent->fetch_assoc()):
                                $name = $order['fullname'] ?? $order['username'] ?? $order['name'] ?? 'Guest';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold small"><?php echo $name; ?></div>
                                    <div style="font-size: 10px;" class="text-muted"><?php echo date('H:i M d, Y', strtotime($order['created_at'])); ?></div>
                                </td>
                                <td class="small text-truncate" style="max-width: 150px;"><?php echo $order['movie_title']; ?></td>
                                <td class="fw-bold text-success">+$<?php echo number_format($order['total_price']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-orange { color: #ff9800; }
    @media print {
        .no-print, .sidebar, .navbar { display: none !important; }
        .content-wrapper { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>

<?php
$content = ob_get_clean();
renderLayout("Revenue Report", $content);
?>