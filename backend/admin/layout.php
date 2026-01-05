<?php
function renderLayout($title, $content)
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> - CineBook Admin</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

        <style>
            :root {
                --sidebar-bg: #111111;
                --orange: #ff9800;
                --content-bg: #f4f6f9;
            }

            body {
                background-color: var(--content-bg);
                font-family: 'Segoe UI', Tahoma, Geneva, sans-serif;
                margin: 0;
            }

            /* SIDEBAR: Black & Orange */
            .sidebar {
                width: 260px;
                height: 100vh;
                position: fixed;
                background: var(--sidebar-bg);
                border-right: 1px solid #222;
                z-index: 1000;
            }

            .sidebar-brand {
                padding: 30px;
                font-size: 24px;
                font-weight: 900;
                color: var(--orange);
                text-align: center;
                border-bottom: 1px solid #333;
                letter-spacing: 2px;
            }

            .nav-heading {
                padding: 25px 25px 10px;
                font-size: 11px;
                font-weight: 800;
                color: #ffffff !important;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                opacity: 0.8;
            }

            .nav-link {
                color: #aaaaaa;
                padding: 12px 25px;
                transition: 0.3s;
                font-weight: 500;
                text-decoration: none;
                display: flex;
                align-items: center;
            }

            .nav-link:hover,
            .nav-link.active {
                color: var(--orange);
                background: rgba(255, 152, 0, 0.1);
            }

            .nav-link i {
                font-size: 20px;
                margin-right: 12px;
            }

            /* CONTENT: White Background */
            .main-content {
                margin-left: 260px;
                padding: 40px;
                min-height: 100vh;
            }

            .card {
                background: #ffffff;
                border: none;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }

            .text-orange {
                color: var(--orange) !important;
            }

            .btn-orange {
                background: var(--orange);
                color: #000;
                font-weight: bold;
                border: none;
            }

            .btn-orange:hover {
                background: #e68900;
                color: #000;
            }
        </style>
    </head>

    <body>
        <div class="sidebar">
            <div class="sidebar-brand">CINEBOOK</div>
            <div class="nav flex-column">
                <div class="nav-heading">System Management</div>
                <a href="index.php" class="nav-link"><i class='bx bxs-dashboard'></i> Dashboard</a>
                <a href="movies.php" class="nav-link"><i class='bx bxs-film'></i> Now Showing</a>
                <a href="cinemas.php" class="nav-link"><i class='bx bxs-buildings'></i> Cinemas & Rooms</a>
                <a href="seats_manager.php" class="nav-link"><i class='bx bx-grid-horizontal'></i> Seat Maps</a>
                <a href="reviews.php" class="nav-link"><i class='bx bxs-star-half'></i> Manage Reviews</a>
                <div class="nav-heading">Operations</div>
                <a href="showtimes.php" class="nav-link"><i class='bx bxs-calendar-event'></i> Showtimes</a>
                <a href="bookings.php" class="nav-link"><i class='bx bxs-coupon'></i> Booking Orders</a>
                <a href="foods.php" class="nav-link"><i class='bx bxs-drink'></i> Snacks & Drinks</a>
                <a href="news.php" class="nav-link"><i class='bx bx-news'></i> News</a>
                
                <a href="http://localhost:8888/backend/auth/logout.php"
                    class="nav-link text-danger mt-4"
                    onclick="return confirm('Are you sure you want to log out?')">
                    <i class='bx bx-log-out'></i> Logout
                </a>
            </div>
        </div>
        <div class="main-content">
            <?php echo $content; ?>
        </div>
    </body>

    </html>
<?php } ?>