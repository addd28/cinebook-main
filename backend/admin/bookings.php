<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

include_once '../db.php';
include_once 'layout.php';

// 1. XỬ LÝ LOGIC (Delete/Update)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM bookings WHERE id = $id");
    header("Location: bookings.php?msg=deleted"); exit();
}

if (isset($_GET['status_id']) && isset($_GET['current_status'])) {
    $id = intval($_GET['status_id']);
    $new_status = ($_GET['current_status'] == 'pending') ? 'confirmed' : 'pending';
    $conn->query("UPDATE bookings SET status = '$new_status' WHERE id = $id");
    header("Location: bookings.php?msg=status_updated"); exit();
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Booking Management</h3>
        <p class="text-muted small mb-0">Detailed transaction list and payment status management</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button class="btn btn-success rounded-pill px-3"><i class='bx bx-spreadsheet'></i> Export Excel</button>
        <button class="btn btn-dark rounded-pill px-3" onclick="window.print()"><i class='bx bx-printer'></i> Print PDF</button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Order ID</th>
                    <th>Customer</th>
                    <th>Movie & Showtime</th>
                    <th>Seats</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th class="text-end pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- 4. QUERY DỮ LIỆU ĐÃ SỬA THEO BẢNG USERS CỦA BẠN ---
                // Chỉ lấy cột 'name' vì bảng của bạn không có fullname/username
                $sql = "SELECT b.id, b.seats, b.total_price, b.status, b.created_at, 
                               u.email, u.name as u_name,
                               m.title as movie_name, c.name as cinema_name, s.show_date, s.show_time 
                        FROM bookings b
                        LEFT JOIN users u ON b.user_id = u.id
                        LEFT JOIN showtimes s ON b.showtime_id = s.id
                        LEFT JOIN movies m ON s.movie_id = m.id
                        LEFT JOIN rooms r ON s.room_id = r.id
                        LEFT JOIN cinemas c ON r.cinema_id = c.id
                        GROUP BY b.id 
                        ORDER BY b.created_at DESC";
                
                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0):
                    while ($row = $res->fetch_assoc()):
                        // Hiển thị tên từ cột 'u_name'
                        $client_name = !empty($row['u_name']) ? $row['u_name'] : 'Guest';
                ?>
                <tr>
                    <td class="ps-4 fw-bold">#BK-<?=str_pad($row['id'], 5, '0', STR_PAD_LEFT)?></td>
                    <td>
                        <div class="fw-bold text-dark"><?=$client_name?></div>
                        <div class="text-muted small"><?=$row['email']?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary"><?=$row['movie_name']?></div>
                        <div class="small text-muted">
                            <?=$row['cinema_name']?><br>
                            <?=date('M d, Y', strtotime($row['show_date']))?> - <?=$row['show_time']?>
                        </div>
                    </td>
                    <td><span class="badge bg-dark rounded-pill"><?=$row['seats']?></span></td>
                    <td><b class="text-danger"><?=number_format($row['total_price'])?> VND</b></td>
                    <td>
                        <a href="?status_id=<?=$row['id']?>&current_status=<?=$row['status']?>" 
                           class="badge text-decoration-none rounded-pill px-3 py-2 <?=$row['status'] == 'confirmed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'?>">
                             <?=$row['status'] == 'confirmed' ? 'Paid' : 'Pending'?>
                        </a>
                    </td>
                    <td class="text-end pe-4 no-print">
                        <a href="?delete_id=<?=$row['id']?>" class="text-danger" onclick="return confirm('Delete this order?')">
                            <i class='bx bx-trash fs-5'></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No bookings found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
    .bg-warning-subtle { background-color: #fff3cd; color: #664d03; }
    @media print { .no-print { display: none !important; } }
</style>

<?php
$content = ob_get_clean();
renderLayout("Booking Management", $content);
?>