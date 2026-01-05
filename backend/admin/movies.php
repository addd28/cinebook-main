<?php
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../auth/login.php?msg=unauthorized");
        exit();
    }

    include_once '../db.php';
    include_once 'layout.php';

    $upload_dir = "../../uploads/movies/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // 1. HANDLE ADD MOVIE
    if (isset($_POST['add_movie'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $director = mysqli_real_escape_string($conn, $_POST['director']);
        $duration = (int)$_POST['duration'];
        $release_date = mysqli_real_escape_string($conn, $_POST['release_date']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $synopsis = mysqli_real_escape_string($conn, $_POST['synopsis']);
        $trailer_url = mysqli_real_escape_string($conn, $_POST['trailer_url']);
        $genres = isset($_POST['genres']) ? $_POST['genres'] : [];

        $poster = "default_poster.jpg";
        if (!empty($_FILES['poster']['name'])) {
            $safe_file_name = mysqli_real_escape_string($conn, $_FILES['poster']['name']);
            $poster = time() . "_" . $safe_file_name;
            move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $poster);
        }

        $sql = "INSERT INTO movies (title, director, duration, release_date, status, synopsis, poster, trailer_url) 
                VALUES ('$title', '$director', '$duration', '$release_date', '$status', '$synopsis', '$poster', '$trailer_url')";
        
        if ($conn->query($sql)) {
            $movie_id = $conn->insert_id;
            foreach ($genres as $gid) {
                $gid = (int)$gid;
                $conn->query("INSERT INTO movie_genres (movie_id, genre_id) VALUES ($movie_id, $gid)");
            }
        }
        header("Location: movies.php?msg=added"); exit();
    }

    // 2. HANDLE UPDATE MOVIE
    if (isset($_POST['update_movie'])) {
        $id = (int)$_POST['movie_id'];
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $director = mysqli_real_escape_string($conn, $_POST['director']);
        $duration = (int)$_POST['duration'];
        $release_date = mysqli_real_escape_string($conn, $_POST['release_date']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $synopsis = mysqli_real_escape_string($conn, $_POST['synopsis']);
        $trailer_url = mysqli_real_escape_string($conn, $_POST['trailer_url']);
        $genres = isset($_POST['genres']) ? $_POST['genres'] : [];

        $poster_update = "";
        if (!empty($_FILES['poster']['name'])) {
            $safe_file_name = mysqli_real_escape_string($conn, $_FILES['poster']['name']);
            $poster = time() . "_" . $safe_file_name;
            if(move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $poster)) {
                $poster_update = ", poster='$poster'";
            }
        }

        $sql = "UPDATE movies SET title='$title', director='$director', duration='$duration', 
                release_date='$release_date', status='$status', synopsis='$synopsis',
                trailer_url='$trailer_url' $poster_update WHERE id=$id";
        
        if($conn->query($sql)) {
            $conn->query("DELETE FROM movie_genres WHERE movie_id=$id");
            foreach ($genres as $gid) {
                $gid = (int)$gid;
                $conn->query("INSERT INTO movie_genres (movie_id, genre_id) VALUES ($id, $gid)");
            }
        }
        header("Location: movies.php?msg=updated"); exit();
    }

    // 3. HANDLE DELETE MOVIE
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM movies WHERE id=$id");
        header("Location: movies.php?msg=deleted"); exit();
    }

    ob_start();
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Movie Management</h3>
            <p class="text-muted small mb-0">Manage movie listings, genres, and screening status</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#movieModal">
            <i class='bx bx-plus me-1'></i> Add New Movie
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Poster</th>
                        <th>Movie Info</th>
                        <th>Director</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM movies ORDER BY id DESC");
                    while ($row = $res->fetch_assoc()):
                        $m_id = $row['id'];
                        $g_ids = [];
                        $gr = $conn->query("SELECT genre_id FROM movie_genres WHERE movie_id=$m_id");
                        while($g_row = $gr->fetch_assoc()) $g_ids[] = $g_row['genre_id'];
                    ?>
                    <tr>
                        <td class="ps-4">
                            <img src="../../uploads/movies/<?php echo $row['poster']; ?>" class="rounded-3 shadow-sm" style="width: 50px; height: 70px; object-fit: cover;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $row['title']; ?></div>
                            <small class="text-muted"><?php echo $row['duration']; ?> min | <?php echo $row['release_date']; ?></small>
                        </td>
                        <td><small><?php echo $row['director'] ?: 'N/A'; ?></small></td>
                        <td>
                            <span class="badge <?php echo ($row['status'] == 'now_showing' ? 'bg-success' : 'bg-warning'); ?> rounded-pill">
                                <?php echo str_replace('_', ' ', $row['status']); ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light border edit-btn" 
                                data-id="<?php echo $row['id']; ?>"
                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                data-director="<?php echo htmlspecialchars($row['director']); ?>"
                                data-duration="<?php echo $row['duration']; ?>"
                                data-date="<?php echo $row['release_date']; ?>"
                                data-status="<?php echo $row['status']; ?>"
                                data-trailer="<?php echo htmlspecialchars($row['trailer_url']); ?>"
                                data-synopsis="<?php echo htmlspecialchars($row['synopsis']); ?>"
                                data-genres="<?php echo implode(',', $g_ids); ?>">
                                <i class='bx bx-edit-alt'></i>
                            </button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this movie?')">
                                <i class='bx bx-trash'></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="movieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-dark text-white">
                    <h5 class="fw-bold mb-0">Movie Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="movie_id" id="movie_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Title</label>
                            <input type="text" name="title" id="form_title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Director</label>
                            <input type="text" name="director" id="form_director" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Duration (min)</label>
                            <input type="number" name="duration" id="form_duration" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Release Date</label>
                            <input type="date" name="release_date" id="form_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" id="form_status" class="form-select">
                                <option value="now_showing">Now Showing</option>
                                <option value="coming_soon">Coming Soon</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-primary">Genres</label>
                            <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light">
                                <?php
                                $gen_res = $conn->query("SELECT * FROM genres ORDER BY name");
                                while($g = $gen_res->fetch_assoc()):
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input genre-cb" type="checkbox" name="genres[]" value="<?php echo $g['id']; ?>" id="g_<?php echo $g['id']; ?>">
                                    <label class="form-check-label small" for="g_<?php echo $g['id']; ?>"><?php echo $g['name']; ?></label>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Trailer URL</label>
                            <input type="text" name="trailer_url" id="form_trailer" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Poster Image</label>
                            <input type="file" name="poster" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Synopsis</label>
                            <textarea name="synopsis" id="form_synopsis" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_movie" id="saveBtn" class="btn btn-dark w-100 py-2">SAVE MOVIE</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const movieModalEl = document.getElementById('movieModal');
        const movieModal = new bootstrap.Modal(movieModalEl);
        const saveBtn = document.getElementById('saveBtn');

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('movie_id').value = this.dataset.id;
                document.getElementById('form_title').value = this.dataset.title;
                document.getElementById('form_director').value = this.dataset.director;
                document.getElementById('form_duration').value = this.dataset.duration;
                document.getElementById('form_date').value = this.dataset.date;
                document.getElementById('form_status').value = this.dataset.status;
                document.getElementById('form_trailer').value = this.dataset.trailer;
                document.getElementById('form_synopsis').value = this.dataset.synopsis;

                const genres = this.dataset.genres ? this.dataset.genres.split(',') : [];
                document.querySelectorAll('.genre-cb').forEach(cb => {
                    cb.checked = genres.includes(cb.value);
                });

                saveBtn.name = "update_movie";
                saveBtn.innerText = "UPDATE MOVIE";
                saveBtn.className = "btn btn-primary w-100 py-2";
                movieModal.show();
            });
        });

        movieModalEl.addEventListener('hidden.bs.modal', function () {
            movieModalEl.querySelector('form').reset();
            saveBtn.name = "add_movie";
            saveBtn.innerText = "SAVE MOVIE";
            saveBtn.className = "btn btn-dark w-100 py-2";
        });
    });
    </script>

<?php
    $content = ob_get_clean();
    renderLayout("Manage Movies", $content);
?>