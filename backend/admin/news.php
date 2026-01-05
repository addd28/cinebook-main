<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: ../auth/login.php?msg=unauthorized"); exit(); }
include_once '../db.php';
include_once 'layout.php';

// Helper function to create Slugs from Titles
function create_slug($string) {
    $search = array('+( |-|/)+', '/([^a-z0-9-.])+/');
    $replace = array('-', '');
    return strtolower(preg_replace($search, $replace, strtolower($string)));
}

// --- 1. HANDLE ADD NEWS ---
if (isset($_POST['add_news'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content_txt = mysqli_real_escape_string($conn, $_POST['content']);
    $slug = create_slug($title); // Auto-generate slug

    $image = "news_default.jpg";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        if (!is_dir("../../uploads/news/")) { mkdir("../../uploads/news/", 0777, true); }
        move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/news/" . $image);
    }
    
    // SQL statement including fields: title, slug, content, image
    $sql = "INSERT INTO news (title, slug, content, image) VALUES ('$title', '$slug', '$content_txt', '$image')";
    $conn->query($sql);
    header("Location: news.php?msg=added"); exit();
}

// --- 2. HANDLE UPDATE NEWS ---
if (isset($_POST['update_news'])) {
    $id = $_POST['news_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content_txt = mysqli_real_escape_string($conn, $_POST['content']);
    $slug = create_slug($title);

    $sql_image = "";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/news/" . $image)) {
            $sql_image = ", image='$image'";
        }
    }

    $sql = "UPDATE news SET title='$title', slug='$slug', content='$content_txt' $sql_image WHERE id=$id";
    $conn->query($sql);
    header("Location: news.php?msg=updated"); exit();
}

// --- 3. HANDLE DELETE NEWS ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT image FROM news WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if ($row['image'] != 'news_default.jpg') @unlink("../../uploads/news/" . $row['image']);
    }
    $conn->query("DELETE FROM news WHERE id=$id");
    header("Location: news.php?msg=deleted"); exit();
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">News & Promotions</h3>
        <p class="text-muted small mb-0">Manage articles and content displayed on the system</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newsModal">
        <i class='bx bx-plus me-1'></i> Post New Article
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0">
        <i class='bx bxs-check-circle me-1'></i> Action successful!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php
    $res = $conn->query("SELECT * FROM news ORDER BY id DESC");
    while ($row = $res->fetch_assoc()):
    ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="position-relative">
                <img src="../../uploads/news/<?php echo $row['image']; ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
            </div>
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-2 text-truncate"><?php echo $row['title']; ?></h6>
                <p class="text-muted small mb-3 text-truncate-2" style="height: 40px; overflow: hidden;">
                    <?php echo strip_tags($row['content']); ?>
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                    <div class="btn-group shadow-sm rounded-3">
                        <button class="btn btn-white btn-sm border edit-news-btn"
                                data-id="<?php echo $row['id']; ?>"
                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                data-content="<?php echo htmlspecialchars($row['content']); ?>">
                            <i class='bx bx-edit-alt text-primary'></i>
                        </button>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-white btn-sm border" onclick="return confirm('Delete this article?')">
                            <i class='bx bx-trash text-danger'></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="newsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Create New Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="small fw-bold">Title</label>
                <input type="text" name="title" class="form-control rounded-3 border-2 mb-3" placeholder="Enter article title" required>
                
                <label class="small fw-bold">Cover Image</label>
                <input type="file" name="image" class="form-control rounded-3 border-2 mb-3" accept="image/*">
                
                <label class="small fw-bold">Content</label>
                <textarea name="content" class="form-control rounded-3 border-2" rows="6" placeholder="Write your content here..." required></textarea>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="add_news" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow">PUBLISH NOW</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST" enctype="multipart/form-data">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-primary">Edit Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="news_id" id="edit_news_id">
                <label class="small fw-bold">Title</label>
                <input type="text" name="title" id="edit_news_title" class="form-control rounded-3 border-2 mb-3" required>
                
                <label class="small fw-bold">Change Cover Image (leave blank to keep current)</label>
                <input type="file" name="image" class="form-control rounded-3 border-2 mb-3" accept="image/*">
                
                <label class="small fw-bold">Content</label>
                <textarea name="content" id="edit_news_content" class="form-control rounded-3 border-2" rows="6" required></textarea>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="update_news" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow">UPDATE ARTICLE</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-news-btn').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('edit_news_id').value = this.dataset.id;
        document.getElementById('edit_news_title').value = this.dataset.title;
        document.getElementById('edit_news_content').value = this.dataset.content;
        new bootstrap.Modal(document.getElementById('editNewsModal')).show();
    };
});
</script>

<?php
$content = ob_get_clean();
renderLayout("News Management", $content);
?>