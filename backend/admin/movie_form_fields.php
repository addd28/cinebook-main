<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-bold">Title</label>
        <input type="text" name="title" id="<?php echo $prefix; ?>_title" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Director</label>
        <input type="text" name="director" id="<?php echo $prefix; ?>_director" class="form-control">
    </div>
    
    <div class="col-12">
        <label class="form-label small fw-bold text-primary">Cast</label>
        <input type="text" name="actors_list" id="<?php echo $prefix; ?>_actors" class="form-control" placeholder="Ví dụ: Tom Holland, Zendaya...">
        <small class="text-muted">Enter the actors' names separated by commas.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-bold">Duration</label>
        <input type="number" name="duration" id="<?php echo $prefix; ?>_duration" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-bold">Release Date</label>
        <input type="date" name="release_date" id="<?php echo $prefix; ?>_date" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-bold">Status</label>
        <select name="status" id="<?php echo $prefix; ?>_status" class="form-select">
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
                <input class="form-check-input genre-cb" type="checkbox" name="genres[]" value="<?php echo $g['id']; ?>" id="g_<?php echo $prefix . $g['id']; ?>">
                <label class="form-check-label small" for="g_<?php echo $prefix . $g['id']; ?>"><?php echo $g['name']; ?></label>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label small fw-bold">Trailer URL</label>
        <input type="text" name="trailer_url" id="<?php echo $prefix; ?>_trailer" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label small fw-bold">Poster</label>
        <input type="file" name="poster" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label small fw-bold">Synopsis</label>
        <textarea name="synopsis" id="<?php echo $prefix; ?>_synopsis" class="form-control" rows="3"></textarea>
    </div>
</div>