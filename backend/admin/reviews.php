<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: ../auth/login.php"); exit(); }

include_once '../db.php';
include_once 'layout.php';

// --- LOGIC PROCESSING ---
// 1. Delete review
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
    header("Location: reviews.php?msg=deleted"); exit();
}

// 2. Toggle Visibility (Show/Hide)
if (isset($_GET['toggle_id']) && isset($_GET['current'])) {
    $id = intval($_GET['toggle_id']);
    $new_status = ($_GET['current'] == 1) ? 0 : 1;
    $conn->query("UPDATE reviews SET status = $new_status WHERE id = $id");
    header("Location: reviews.php?msg=updated"); exit();
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Manage Reviews</h3>
        <p class="text-muted small mb-0">Moderate content and customer ratings</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">User</th>
                    <th>Movie</th>
                    <th>Rating</th>
                    <th>Content</th>
                    <th>Date Posted</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT r.*, u.name as user_name, m.title as movie_title 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        JOIN movies m ON r.movie_id = m.id 
                        ORDER BY r.created_at DESC";
                $res = $conn->query($sql);
                if ($res->num_rows > 0):
                    while ($row = $res->fetch_assoc()):
                ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold"><?= $row['user_name'] ?></div>
                        <div class="small text-muted">ID: #<?= $row['user_id'] ?></div>
                    </td>
                    <td><span class="badge bg-dark text-wrap" style="max-width: 150px;"><?= $row['movie_title'] ?></span></td>
                    <td>
                        <span class="text-warning fw-bold">
                            <?= $row['rating'] ?> <i class='bx bxs-star'></i>
                        </span>
                    </td>
                    <td style="max-width: 250px;" class="text-muted small">
                        <?= nl2br(htmlspecialchars($row['comment'])) ?>
                    </td>
                    <td class="small"><?= date('H:i M d, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="?toggle_id=<?= $row['id'] ?>&current=<?= $row['status'] ?>" 
                           class="badge text-decoration-none rounded-pill px-3 py-2 <?= $row['status'] == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                            <i class='bx <?= $row['status'] == 1 ? 'bx-show' : 'bx-hide' ?> me-1'></i>
                            <?= $row['status'] == 1 ? 'Visible' : 'Hidden' ?>
                        </a>
                    </td>
                    <td class="text-end pe-4">
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Permanently delete this review?')">
                            <i class='bx bx-trash fs-5'></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
    .bg-secondary-subtle { background-color: #e2e3e5; color: #41464b; }
</style>

<?php
$content = ob_get_clean();
renderLayout("Manage Reviews", $content);
?>