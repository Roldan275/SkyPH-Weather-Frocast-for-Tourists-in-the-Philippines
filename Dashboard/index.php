<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login WeatherForcast/login.php");
    exit();
}

include 'db.php';
include_once "../api/weather_cache.php";

$user_id = $_SESSION['user_id'];

$sql_user = "SELECT * FROM users_table WHERE Id = $user_id";
$result_user = $conn->query($sql_user);

if ($result_user && $result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $user_name = $user['first_name'] . ' ' . $user['last_name'];
    $user_email = $user['email'];
} else {
    header("Location: logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky-PH Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

    <nav class="sidebar">
        <h3>DASHBOARD</h3>
        <button id="sidebarSearch" class="menu-btn"><i class="fa fa-search"></i> Search</button>
        <button id="sidebarProfile" class="menu-btn"><i class="fa fa-user"></i> Profile</button>
        <button class="menu-btn" id="logout" onclick="window.location.href='logout.php'">
            <i class="fa fa-sign-out-alt"></i> Log Out
        </button>
    </nav>

    <main class="main">
        <header class="topbar">
            <button id="toggleBtn" class="toggle-btn">
                <i class="fa fa-bars"></i>
            </button>

            <div class="logo">
                <img src="image/logo.png" alt="SkyPH Logo">
            </div>

            <div class="user" id="profileIcon" style="cursor: pointer;">
                <span><?= htmlspecialchars($user_name); ?></span>
                <i class="fa-solid fa-circle-user"></i>
            </div>
        </header>

        <section class="welcome">
            <h2>Welcome <span><?= htmlspecialchars($user_name); ?></span></h2>
            <p>Plan your next adventure. Check real-time weather for the Philippines' top destinations.</p>
            <div class="welcome-search">
                <input type="text" class="searchInput" placeholder="Search tourist spots...">
                <button id="searchBtn">Search</button>
            </div>
        </section>

        <section class="forecast-container" id="forecastBox">
            <div class="empty-placeholder">
                <i class="fa fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                <p>Select a destination below to view the 5-day forecast and location map.</p>
            </div>
        </section>

        <section class="featured-section">
            <h3>Featured Tourist Spots</h3>
            <div class="cards">
                <?php
                $sql = "SELECT id, name, city, province, description, image, latitude, longitude FROM touristspots_table";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                        $image = !empty($row['image']) ? 'data:image/jpeg;base64,' . base64_encode($row['image']) : 'image/placeholder.png';
                ?>
                    <article class="card">
                        <img src="<?= $image; ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                        <div class="card-body">
                            <h4><?= htmlspecialchars($row['name']); ?></h4>
                            <p class="description"><?= htmlspecialchars($row['description']); ?></p>
                            <br>
                            <p class="location-tag">
                                <i class="fa fa-location-dot"></i> 
                                <?= htmlspecialchars($row['city']); ?>, <?= htmlspecialchars($row['province']); ?>
                            </p>
                            <button class="viewForecastBtn"
                                data-name="<?= htmlspecialchars($row['name']); ?>"
                                data-city="<?= htmlspecialchars($row['city']); ?>"
                                data-province="<?= htmlspecialchars($row['province']); ?>"
                                data-lat="<?= $row['latitude']; ?>"
                                data-lon="<?= $row['longitude']; ?>">
                                View Forecast
                            </button>
                        </div>
                    </article>
                <?php endwhile; endif; ?>
            </div>
        </section>
    </main>

    <div id="profilePopup" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Profile</h3>
                <span class="close-popup">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?= htmlspecialchars($user_name); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_email); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Close</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="function.js"></script>
</body>
</html>