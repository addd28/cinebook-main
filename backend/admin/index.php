<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}
include_once '../db.php';
include_once 'layout.php';

// Actual data queries (Example)
// $total_revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings")->fetch_assoc()['total'];
// $total_tickets = $conn->query("SELECT COUNT(*) as total FROM tickets")->fetch_assoc()['total'];

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Dashboard</h2>
        <p class="text-muted small mb-0">Welcome back! Here is today's business overview.</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold">
        <i class='bx bx-download me-1'></i> Export Report
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-white" style="background: #1e1e1e;">
            <div class="text-white-50 small fw-bold text-uppercase mb-2">Monthly Revenue</div>
            <h3 class="fw-bold mb-0 text-orange">45,000,000 <span class="fs-6 text-white fw-normal">VND</span></h3>
            <div class="small text-success mt-2"><i class='bx bx-trending-up'></i> +12% vs last month</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-white" style="background: #1e1e1e;">
            <div class="text-white-50 small fw-bold text-uppercase mb-2">Daily Tickets Sold</div>
            <h3 class="fw-bold mb-0 text-info">128 <span class="fs-6 text-white fw-normal">tickets</span></h3>
            <div class="small text-info mt-2">Occupancy Rate: 65%</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-white" style="background: #1e1e1e;">
            <div class="text-white-50 small fw-bold text-uppercase mb-2">Movies Screening</div>
            <h3 class="fw-bold mb-0">08 <span class="fs-6 text-white fw-normal">movies</span></h3>
            <div class="small text-white-50 mt-2">3 upcoming releases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-white" style="background: #1e1e1e;">
            <div class="text-white-50 small fw-bold text-uppercase mb-2">New Members</div>
            <h3 class="fw-bold mb-0 text-warning">24 <span class="fs-6 text-white fw-normal">users</span></h3>
            <div class="small text-white-50 mt-2">Active in last 24h</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recent Transactions</h5>
                <a href="bookings.php" class="small text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <tr>
                            <td class="fw-bold">Nguyen Van A</td>
                            <td>Captain America</td>
                            <td class="fw-bold">180,000 VND</td>
                            <td><span class="badge bg-success-subtle text-success rounded-pill px-3">Success</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tran Thi B</td>
                            <td>Avatar 2</td>
                            <td class="fw-bold">90,000 VND</td>
                            <td><span class="badge bg-warning-subtle text-warning rounded-pill px-3">Pending Payment</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-4">Top Box Office Movies</h5>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-bold">Avengers: Endgame</span>
                    <span class="small text-muted">45%</span>
                </div>
                <div class="progress rounded-pill" style="height: 8px;">
                    <div class="progress-bar bg-dark" style="width: 45%"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-bold">Doraemon Movie</span>
                    <span class="small text-muted">30%</span>
                </div>
                <div class="progress rounded-pill" style="height: 8px;">
                    <div class="progress-bar bg-dark" style="width: 30%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-orange { color: #ff9800 !important; }
    .progress-bar { background-color: #222 !important; }
</style>

<?php
$content = ob_get_clean();
renderLayout("Dashboard", $content);
?>