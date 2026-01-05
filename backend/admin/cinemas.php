<?php
// 1. Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Access Control
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php?msg=unauthorized");
    exit();
}

// 3. Connect core files
include_once '../db.php';
include_once 'layout.php';

// Image upload helper
function uploadCinemaImage($file) {
    if (isset($file) && $file['error'] == 0) {
        $upload_dir = "../../uploads/cinemas/"; 
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return "http://localhost:8888/uploads/cinemas/" . $filename;
        }
    }
    return null;
}

// ==========================================
// 1. PHP LOGIC PROCESSING
// ==========================================

// --- CINEMA LOGIC ---
if (isset($_POST['add_cinema'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $image_url = uploadCinemaImage($_FILES['cinema_image']);
    $conn->query("INSERT INTO cinemas (name, city, address, image_url) VALUES ('$name', '$city', '$address', '$image_url')");
    header("Location: cinemas.php?msg=added"); exit();
}

if (isset($_POST['update_cinema'])) {
    $id = intval($_POST['cinema_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $new_image = uploadCinemaImage($_FILES['cinema_image']);
    $img_update = $new_image ? ", image_url='$new_image'" : "";
    $conn->query("UPDATE cinemas SET name='$name', city='$city', address='$address' $img_update WHERE id=$id");
    header("Location: cinemas.php?msg=updated"); exit();
}

if (isset($_GET['delete_cinema'])) {
    $id = intval($_GET['delete_cinema']);
    $conn->query("DELETE FROM cinemas WHERE id=$id");
    header("Location: cinemas.php?msg=deleted"); exit();
}

// --- ROOM LOGIC ---
if (isset($_POST['add_room'])) {
    $cinema_id = intval($_POST['cinema_id']);
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    if ($conn->query("INSERT INTO rooms (cinema_id, name) VALUES ($cinema_id, '$room_name')")) {
        $room_id = $conn->insert_id;
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $values = [];
        foreach ($rows as $r_char) {
            for ($i = 1; $i <= 10; $i++) {
                $seat_num = $r_char . $i;
                $values[] = "($room_id, '$seat_num')";
            }
        }
        $conn->query("INSERT INTO seats (room_id, seat_number) VALUES " . implode(',', $values));
    }
    header("Location: cinemas.php?msg=room_added"); exit();
}

if (isset($_POST['update_room'])) {
    $room_id = intval($_POST['room_id']);
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    $conn->query("UPDATE rooms SET name='$room_name' WHERE id=$room_id");
    header("Location: cinemas.php?msg=room_updated"); exit();
}

if (isset($_GET['delete_room'])) {
    $id = intval($_GET['delete_room']);
    $conn->query("DELETE FROM rooms WHERE id=$id");
    header("Location: cinemas.php?msg=room_deleted"); exit();
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Cinemas & Rooms System</h3>
        <p class="text-muted small mb-0">Manage infrastructure and theater visual profile</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCinemaModal">
        <i class='bx bx-plus-circle me-1'></i> Add Cinema Complex
    </button>
</div>

<div class="row g-4">
    <?php
    $cinemas = $conn->query("SELECT * FROM cinemas ORDER BY id DESC");
    while($c = $cinemas->fetch_assoc()):
        $c_id = $c['id'];
    ?>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 cinema-card overflow-hidden">
            <div class="position-relative">
                <img src="<?= $c['image_url'] ?: 'https://via.placeholder.com/400x200' ?>" class="card-img-top" style="height: 160px; object-fit: cover;">
                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 shadow-sm"><?= $c['city'] ?></span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-1"><?= $c['name'] ?></h5>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown"><i class='bx bx-dots-vertical-rounded'></i></button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="openEditCinemaModal('<?=$c['id']?>', '<?=addslashes($c['name'])?>', '<?=$c['city']?>', '<?=addslashes($c['address'])?>', '<?=$c['image_url']?>')"><i class='bx bx-edit me-2 text-primary'></i>Edit Cinema</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?delete_cinema=<?=$c_id?>" onclick="return confirm('Delete this cinema?')"><i class='bx bx-trash me-2'></i>Delete Cinema</a></li>
                        </ul>
                    </div>
                </div>
                <p class="text-muted small mb-4 text-truncate"><i class='bx bx-map-pin text-orange'></i> <?= $c['address'] ?></p>
                
                <div class="room-section bg-light rounded-4 p-3 mb-3">
                    <div class="room-list" style="max-height: 150px; overflow-y: auto;">
                        <?php
                        $rooms = $conn->query("SELECT * FROM rooms WHERE cinema_id = $c_id");
                        while($r = $rooms->fetch_assoc()):
                        ?>
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 mb-2 shadow-sm border-start border-orange border-3">
                            <span class="ms-2 small fw-bold"><?= $r['name'] ?></span>
                            <div class="d-flex gap-1">
                                <button class="btn btn-link text-primary p-0 px-1" onclick="openEditRoomModal('<?=$r['id']?>', '<?=addslashes($r['name'])?>')"><i class='bx bx-edit-alt'></i></button>
                                <a href="?delete_room=<?=$r['id']?>" class="text-danger px-1" onclick="return confirm('Delete room?')"><i class='bx bx-trash'></i></a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <button class="btn btn-outline-dark w-100 rounded-pill btn-sm fw-bold" onclick="openAddRoomModal('<?=$c_id?>', '<?=addslashes($c['name'])?>')">+ Add Room</button>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="addCinemaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0"><h5 class="fw-bold">Add New Cinema Complex</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="small fw-bold">Cinema Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="small fw-bold">City</label><select name="city" class="form-select"><option value="Hanoi">Hanoi</option><option value="Ho Chi Minh City">Ho Chi Minh City</option><option value="Da Nang">Da Nang</option></select></div>
                <div class="mb-3"><label class="small fw-bold">Detailed Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                <div class="mb-0"><label class="small fw-bold">Cinema Image</label><input type="file" name="cinema_image" class="form-control" accept="image/*" required></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="add_cinema" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">SAVE CINEMA</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCinemaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0"><h5 class="fw-bold">Update Cinema Info</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="cinema_id" id="ec_id">
                <div class="mb-3"><label class="small fw-bold">Cinema Name</label><input type="text" name="name" id="ec_name" class="form-control" required></div>
                <div class="mb-3"><label class="small fw-bold">City</label><select name="city" id="ec_city" class="form-select"><option value="Hanoi">Hanoi</option><option value="Ho Chi Minh City">Ho Chi Minh City</option><option value="Da Nang">Da Nang</option></select></div>
                <div class="mb-3"><label class="small fw-bold">Address</label><textarea name="address" id="ec_address" class="form-control" rows="2"></textarea></div>
                <div class="mb-0">
                    <label class="small fw-bold">Change Image (optional)</label>
                    <input type="file" name="cinema_image" class="form-control" accept="image/*">
                    <div id="ec_img_preview" class="mt-2 text-center"></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="update_cinema" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">SAVE CHANGES</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="POST">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Add Room to <span id="ar_cinema_name" class="text-orange"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="cinema_id" id="ar_cinema_id">
                <div class="mb-3">
                    <label class="small fw-bold">Room Name</label>
                    <input type="text" name="room_name" class="form-control" placeholder="Ex: Room 01" required>
                </div>
                <div class="alert alert-dark py-2 small mb-0 border-0">
                    <i class='bx bx-info-circle me-1'></i> 80 seats (A1-H10) will be auto-generated.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="add_room" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">CREATE ROOM</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="POST">
            <div class="modal-header border-0 p-4 pb-0"><h5 class="fw-bold">Update Room Name</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="room_id" id="er_id">
                <div class="mb-3">
                    <label class="small fw-bold">New Room Name</label>
                    <input type="text" name="room_name" id="er_name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="update_room" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">SAVE CHANGES</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCinemaModal(id, name, city, address, img_url) {
    document.getElementById('ec_id').value = id;
    document.getElementById('ec_name').value = name;
    document.getElementById('ec_city').value = city;
    document.getElementById('ec_address').value = address;
    const preview = document.getElementById('ec_img_preview');
    preview.innerHTML = img_url ? `<img src="${img_url}" class="rounded shadow-sm" style="height: 80px; width: 140px; object-fit: cover;">` : "";
    new bootstrap.Modal(document.getElementById('editCinemaModal')).show();
}

function openAddRoomModal(id, name) {
    document.getElementById('ar_cinema_id').value = id;
    document.getElementById('ar_cinema_name').innerText = name;
    new bootstrap.Modal(document.getElementById('addRoomModal')).show();
}

function openEditRoomModal(id, name) {
    document.getElementById('er_id').value = id;
    document.getElementById('er_name').value = name;
    new bootstrap.Modal(document.getElementById('editRoomModal')).show();
}
</script>

<style>
    .text-orange { color: #ff9800 !important; }
    .cinema-card { transition: all 0.3s; border: 1px solid #eee !important; }
    .cinema-card:hover { transform: translateY(-5px); border-color: #ff9800 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    .room-list::-webkit-scrollbar { width: 4px; }
    .room-list::-webkit-scrollbar-thumb { background: #ff9800; border-radius: 10px; }
</style>

<?php
$content = ob_get_clean();
renderLayout("Cinema Management", $content);
?>