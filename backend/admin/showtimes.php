<?php
// 1. Initialize session (Prevent multiple call errors)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Access Control
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login if unauthorized
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

// 3. Include core files
include_once '../db.php';
include_once 'layout.php';

// 1. HANDLE ADD SHOWTIME
if (isset($_POST['add_showtime'])) {
    $movie_id = $_POST['movie_id'];
    $room_id = $_POST['room_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time']; 

    $sql = "INSERT INTO showtimes (movie_id, room_id, show_date, show_time) 
            VALUES ('$movie_id', '$room_id', '$show_date', '$show_time')";
    $conn->query($sql);
    header("Location: showtimes.php?msg=added");
    exit();
}

// 2. HANDLE UPDATE SHOWTIME
if (isset($_POST['update_showtime'])) {
    $id = $_POST['showtime_id'];
    $movie_id = $_POST['movie_id'];
    $room_id = $_POST['room_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];

    $sql = "UPDATE showtimes SET movie_id='$movie_id', room_id='$room_id', 
            show_date='$show_date', show_time='$show_time' WHERE id=$id";
    $conn->query($sql);
    header("Location: showtimes.php?msg=updated");
    exit();
}

// 3. HANDLE DELETE SHOWTIME
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM showtimes WHERE id=$id");
    header("Location: showtimes.php?msg=deleted");
    exit();
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Showtime Management</h3>
        <p class="text-muted small mb-0">Organize screening slots for the entire cinema system</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#showtimeModal">
        <i class='bx bx-calendar-plus me-1'></i> Add Showtime
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Movie</th>
                    <th>Cinema / Room</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody style="border-top: 0;">
                <?php
                $query = "SELECT st.*, m.title as movie_title, r.name as room_name, c.name as cinema_name 
                          FROM showtimes st
                          JOIN movies m ON st.movie_id = m.id
                          JOIN rooms r ON st.room_id = r.id
                          JOIN cinemas c ON r.cinema_id = c.id
                          ORDER BY st.show_date DESC, st.show_time ASC";
                $res = $conn->query($query);
                if ($res->num_rows > 0):
                    while ($row = $res->fetch_assoc()):
                ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark fs-6"><?php echo $row['movie_title']; ?></div>
                    </td>
                    <td>
                        <div class="small fw-bold text-orange"><?php echo $row['cinema_name']; ?></div>
                        <div class="text-muted small"><?php echo $row['room_name']; ?></div>
                    </td>
                    <td class="text-dark fw-medium">
                        <i class='bx bx-calendar me-1 text-muted'></i><?php echo date('M d, Y', strtotime($row['show_date'])); ?>
                    </td>
                    <td>
                        <span class="badge bg-dark text-orange px-3 py-2 rounded-pill fw-bold">
                            <i class='bx bx-time-five me-1'></i><?php echo date('H:i', strtotime($row['show_time'])); ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-light btn-sm rounded-3 border me-1 edit-btn"
                                data-id="<?php echo $row['id']; ?>"
                                data-movie="<?php echo $row['movie_id']; ?>"
                                data-room="<?php echo $row['room_id']; ?>"
                                data-date="<?php echo $row['show_date']; ?>"
                                data-time="<?php echo $row['show_time']; ?>">
                            <i class='bx bx-edit-alt text-primary'></i>
                        </button>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-light btn-sm rounded-3 border" onclick="return confirm('Delete this showtime?')">
                            <i class='bx bx-trash text-danger'></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">No showtimes have been created yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="showtimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-dark" id="modalTitle">Create New Showtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="showtime_id" id="showtime_id">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Movie</label>
                    <select name="movie_id" id="movie_id" class="form-select border-2 shadow-none rounded-3" required>
                        <option value="">-- Select Movie --</option>
                        <?php 
                        $movies = $conn->query("SELECT id, title FROM movies ORDER BY title ASC");
                        while($m = $movies->fetch_assoc()) echo "<option value='{$m['id']}'>{$m['title']}</option>";
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Cinema & Room</label>
                    <select name="room_id" id="room_id" class="form-select border-2 shadow-none rounded-3" required>
                        <option value="">-- Select Screening Room --</option>
                        <?php 
                        $rooms = $conn->query("SELECT r.id, r.name as rname, c.name as cname FROM rooms r JOIN cinemas c ON r.cinema_id = c.id ORDER BY c.name ASC");
                        while($r = $rooms->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['cname']} - {$r['rname']}</option>";
                        ?>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark">Show Date</label>
                        <input type="date" name="show_date" id="show_date" class="form-control border-2 shadow-none rounded-3" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark">Show Time</label>
                        <input type="time" name="show_time" id="show_time" class="form-control border-2 shadow-none rounded-3" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="add_showtime" id="btnSubmit" class="btn btn-warning w-100 rounded-pill py-2 fw-bold text-dark shadow-sm">
                    CONFIRM SHOWTIME
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Logic to handle Edit button clicks
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('modalTitle').innerText = 'Edit Showtime';
        document.getElementById('btnSubmit').name = 'update_showtime';
        document.getElementById('btnSubmit').innerText = 'UPDATE SHOWTIME';
        document.getElementById('btnSubmit').className = 'btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm';
        
        // Populate form with data
        document.getElementById('showtime_id').value = this.dataset.id;
        document.getElementById('movie_id').value = this.dataset.movie;
        document.getElementById('room_id').value = this.dataset.room;
        document.getElementById('show_date').value = this.dataset.date;
        document.getElementById('show_time').value = this.dataset.time;
        
        // Show the Modal
        var myModal = new bootstrap.Modal(document.getElementById('showtimeModal'));
        myModal.show();
    };
});

// Reset form when modal is closed
document.getElementById('showtimeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').innerText = 'Create New Showtime';
    document.getElementById('btnSubmit').name = 'add_showtime';
    document.getElementById('btnSubmit').innerText = 'CONFIRM SHOWTIME';
    document.getElementById('btnSubmit').className = 'btn btn-warning w-100 rounded-pill py-2 fw-bold text-dark shadow-sm';
    document.querySelector('form').reset();
});
</script>

<?php
$content = ob_get_clean();
renderLayout("Showtime Management", $content);
?>