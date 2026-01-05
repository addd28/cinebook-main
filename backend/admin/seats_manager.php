<?php
// 1. Initialize session (Prevent multiple call errors)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Check access permissions
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login if unauthorized
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

// 3. Include essential files
include_once '../db.php';
include_once 'layout.php';

// PHP Form Handling Logic
if (isset($_POST['quick_generate'])) {
    $room_id = $_POST['room_id'];
    $num_rows = (int)$_POST['num_rows'];
    $num_cols = (int)$_POST['num_cols'];
    $conn->query("DELETE FROM seats WHERE room_id = $room_id");
    $alphabet = range('A', 'Z');
    for ($i = 0; $i < $num_rows; $i++) {
        $row_char = $alphabet[$i];
        $type = ($i < 3) ? 'gold' : (($i < 7) ? 'platinum' : 'box');
        for ($j = 1; $j <= $num_cols; $j++) {
            $seat_num = $row_char . $j;
            $conn->query("INSERT INTO seats (room_id, seat_number, seat_type) VALUES ($room_id, '$seat_num', '$type')");
        }
    }
    $total = $num_rows * $num_cols;
    $conn->query("UPDATE rooms SET total_seats = $total WHERE id = $room_id");
    header("Location: seats_manager.php?room_id=$room_id&msg=success"); exit();
}

if (isset($_POST['update_seat'])) {
    $seat_id = $_POST['seat_id'];
    $new_type = $_POST['seat_type'];
    $room_id = $_POST['room_id'];
    $conn->query("UPDATE seats SET seat_type = '$new_type' WHERE id = $seat_id");
    header("Location: seats_manager.php?room_id=$room_id&msg=updated"); exit();
}

if (isset($_GET['delete_seat'])) {
    $s_id = $_GET['delete_seat'];
    $r_id = $_GET['room_id'];
    $conn->query("DELETE FROM seats WHERE id = $s_id");
    $conn->query("UPDATE rooms SET total_seats = total_seats - 1 WHERE id = $r_id");
    header("Location: seats_manager.php?room_id=$r_id&msg=deleted"); exit();
}

$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : 0;
ob_start();
?>

<div class="row g-4">
    <div class="col-md-4">
        <h3 class="fw-bold mb-1" style="color: #222;">Seat Map</h3>
        <p class="text-muted small mb-4">Select a room to set up seat positions</p>

        <div class="list-group shadow-sm rounded-4 border-0">
            <?php
            $rooms = $conn->query("SELECT r.*, c.name as cname, c.address FROM rooms r JOIN cinemas c ON r.cinema_id = c.id");
            while($r = $rooms->fetch_assoc()):
                $active = ($room_id == $r['id']);
            ?>
            <a href="?room_id=<?php echo $r['id']; ?>" class="list-group-item list-group-item-action border-0 p-3 mb-2 rounded-4" 
               style="background: <?php echo $active ? '#fff7ed' : '#fff'; ?>; border-left: 5px solid <?php echo $active ? 'var(--orange)' : 'transparent'; ?> !important;">
                <div class="fw-bold fs-5 text-dark"><?php echo $r['cname']; ?></div>
                <div class="text-orange small fw-bold"><?php echo $r['name']; ?></div>
                <div class="text-muted small" style="font-size: 11px;"><i class='bx bx-map'></i> <?php echo $r['address']; ?></div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="col-md-8">
        <?php if($room_id > 0): ?>
        <div class="card p-4">
            <div class="alert alert-warning border-0 small mb-4 py-2">Click on a seat to change its category or delete it.</div>
            
            
            
            <div class="mx-auto w-75 mb-5 shadow-sm text-center" style="border-top: 5px solid #333; background: #eee; height: 35px; border-radius: 50% / 100% 100% 0 0;">
                <div class="pt-2 fw-bold text-muted small" style="letter-spacing: 10px;">SCREEN</div>
            </div>
            
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php
                $seats = $conn->query("SELECT * FROM seats WHERE room_id = $room_id ORDER BY seat_number ASC");
                while($s = $seats->fetch_assoc()):
                    $bg = ($s['seat_type'] == 'gold') ? 'bg-warning' : (($s['seat_type'] == 'platinum') ? 'bg-info text-white' : 'bg-danger text-white');
                ?>
                    <div class="seat-item <?php echo $bg; ?> rounded-2 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                         style="width: 38px; height: 38px; font-size: 10px; cursor:pointer;"
                         onclick="openEditSeat('<?php echo $s['id']; ?>', '<?php echo $s['seat_number']; ?>', '<?php echo $s['seat_type']; ?>')">
                        <?php echo $s['seat_number']; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card p-5 text-center text-muted"><h5>Please select a screening room</h5></div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="editSeatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form class="modal-content border-0 shadow-lg" method="POST">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold mb-0">Update Seat <span id="display_seat_name" class="text-orange"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="seat_id" id="modal_seat_id">
                <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                <select name="seat_type" id="modal_seat_type" class="form-select mb-3">
                    <option value="gold">Gold</option>
                    <option value="platinum">Platinum</option>
                    <option value="box">Box</option>
                </select>
                <div class="d-grid gap-2">
                    <button type="submit" name="update_seat" class="btn btn-dark fw-bold rounded-pill">Save Changes</button>
                    <a id="delete_link" href="#" class="btn btn-link text-danger small" onclick="return confirm('Delete this seat?')">Delete Seat</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSeat(id, name, type) {
    document.getElementById('modal_seat_id').value = id;
    document.getElementById('display_seat_name').innerText = name;
    document.getElementById('modal_seat_type').value = type;
    document.getElementById('delete_link').href = '?room_id=<?php echo $room_id; ?>&delete_seat=' + id;
    
    // Initialize and show Modal
    var myModal = new bootstrap.Modal(document.getElementById('editSeatModal'));
    myModal.show();
}
</script>

<?php
$content = ob_get_clean();
renderLayout("Seat Management", $content);
?>