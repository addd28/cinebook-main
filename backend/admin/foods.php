<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: ../auth/login.php?msg=unauthorized"); exit(); }
include_once '../db.php';
include_once 'layout.php';

// --- 1. HANDLE ADD NEW ITEM ---
if (isset($_POST['add_food'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (int)$_POST['price'];
    
    $image = "default_food.jpg";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        $target = "../../uploads/foods/" . $image;
        // Check and create directory if it doesn't exist
        if (!is_dir("../../uploads/foods/")) { mkdir("../../uploads/foods/", 0777, true); }
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }
    
    $conn->query("INSERT INTO foods (name, price, image, status) VALUES ('$name', '$price', '$image', 1)");
    header("Location: foods.php?msg=added"); exit();
}

// --- 2. HANDLE UPDATE ITEM ---
if (isset($_POST['update_food'])) {
    $id = $_POST['food_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (int)$_POST['price'];
    $status = $_POST['status'];

    $sql_image = "";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/foods/" . $image)) {
            $sql_image = ", image='$image'";
        }
    }

    $conn->query("UPDATE foods SET name='$name', price='$price', status='$status' $sql_image WHERE id=$id");
    header("Location: foods.php?msg=updated"); exit();
}

// --- 3. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM foods WHERE id=$id");
    header("Location: foods.php?msg=deleted"); exit();
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Popcorn & Drinks Category</h3>
        <p class="text-muted small mb-0">Manage menu items and update selling prices</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#foodModal">
        <i class='bx bx-plus me-1'></i> Add New Item
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" width="100">Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM foods ORDER BY id DESC");
                while ($row = $res->fetch_assoc()):
                ?>
                <tr>
                    <td class="ps-4">
                        <img src="../../uploads/foods/<?php echo $row['image']; ?>" class="rounded-3 shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                    </td>
                    <td><div class="fw-bold text-dark"><?php echo $row['name']; ?></div></td>
                    <td><span class="badge bg-light text-dark border fw-normal px-3 fs-6"><?php echo number_format($row['price']); ?> VND</span></td>
                    <td>
                        <span class="badge rounded-pill px-3 <?php echo $row['status'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                            <?php echo $row['status'] ? 'Available' : 'Out of Stock'; ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm rounded-3">
                            <button class="btn btn-white btn-sm border edit-food-btn" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-price="<?php echo $row['price']; ?>"
                                    data-status="<?php echo $row['status']; ?>">
                                <i class='bx bx-edit-alt text-primary fs-5'></i>
                            </button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-white btn-sm border" onclick="return confirm('Delete this item?')">
                                <i class='bx bx-trash text-danger fs-5'></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="foodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0"><h5 class="fw-bold">Add New Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <label class="small fw-bold">Item Name</label><input type="text" name="name" class="form-control rounded-3 border-2 mb-3" required>
                <label class="small fw-bold">Price (VND)</label><input type="number" name="price" class="form-control rounded-3 border-2 mb-3" required>
                <label class="small fw-bold">Image</label><input type="file" name="image" class="form-control rounded-3 border-2 shadow-sm">
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="add_food" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow">SAVE PRODUCT</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editFoodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0"><h5 class="fw-bold text-primary">Update Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="food_id" id="edit_food_id">
                <label class="small fw-bold">Item Name</label><input type="text" name="name" id="edit_food_name" class="form-control rounded-3 border-2 mb-3" required>
                <label class="small fw-bold">Price (VND)</label><input type="number" name="price" id="edit_food_price" class="form-control rounded-3 border-2 mb-3" required>
                <label class="small fw-bold">Status</label>
                <select name="status" id="edit_food_status" class="form-select rounded-3 border-2 mb-3">
                    <option value="1">Available</option>
                    <option value="0">Out of Stock</option>
                </select>
                <label class="small fw-bold">Change Image (Leave blank to keep current)</label>
                <input type="file" name="image" class="form-control rounded-3 border-2">
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="update_food" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow">UPDATE CHANGES</button>
            </div>
        </form>
    </div>
</div>

<script>
// Logic to populate Edit Modal
document.querySelectorAll('.edit-food-btn').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('edit_food_id').value = this.dataset.id;
        document.getElementById('edit_food_name').value = this.dataset.name;
        document.getElementById('edit_food_price').value = this.dataset.price;
        document.getElementById('edit_food_status').value = this.dataset.status;
        new bootstrap.Modal(document.getElementById('editFoodModal')).show();
    };
});
</script>

<?php $content = ob_get_clean(); renderLayout("Concessions Management", $content); ?>