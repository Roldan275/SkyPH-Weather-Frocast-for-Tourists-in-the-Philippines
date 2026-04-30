<?php
include 'db.php';
include_once "../api/weather_cache.php";

$user_id = 1;

$sql_user = "SELECT * FROM users_table WHERE Id = $user_id";
$result_user = $conn->query($sql_user);

if ($result_user && $result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $user_name = $user['first_name'] . ' ' . $user['last_name'];
    $user_email = $user['email'];
} else {
    $user_name = 'Guest';
    $user_email = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sky-PH Dashboard</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>DASHBOARD</h3>
    <button class="menu-btn">Search</button>
    <button class="menu-btn">Profile</button>
    <button class="menu-btn" id="logout">Log Out</button>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <button id="toggleBtn" class="toggle-btn">
            <i class="fa fa-bars"></i>
        </button>

        <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>

        <div class="user">
            <span><?= $user_name; ?></span>
            <i id="profileIcon" class="fa-solid fa-circle-user"></i>
        </div>
    </div>

    <!-- WELCOME -->
    <div class="welcome">
        <h2>Welcome, <span><?= $user_name; ?></span></h2>
        <p>Search and check weather forecasts for your favorite tourist destinations</p>
        <div class="welcome-search">
            <input type="text" class="searchInput" placeholder="Search tourist spots...">
            <button>Search</button>
        </div>
    </div>

    <!-- Main Weather Display Grid -->
<div class="forecast-container" id="forecastBox">
    <!-- This will be populated dynamically by function.js -->
    <div class="empty-placeholder">
        Select a tourist spot to view the forecast
    </div>
</div>

    <h3>Featured Tourist Spots</h3>

    <div class="cards">

    <?php
    $sql = "SELECT id, name, city, province, description, image, latitude, longitude FROM touristspots_table";
    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()):

        $image = !empty($row['image'])
            ? 'data:image/jpeg;base64,' . base64_encode($row['image'])
            : 'image/placeholder.png';

            $lat = $row['latitude']; 
            $lon = $row['longitude'];
    ?>

        <div class="card">
            <img src="<?= $image; ?>">

            <div class="card-body">
                <h4><?= $row['name']; ?></h4>
                <p><?= $row['description']; ?></p>
                <br>
                <p><?= $row['city']; ?>, <?= $row['province']; ?></p>
                

                <button class="viewForecastBtn"
                    data-name="<?= htmlspecialchars($row['name']); ?>"
                    data-city="<?= htmlspecialchars($row['city']); ?>"
                    data-province="<?= htmlspecialchars($row['province']); ?>"
                    data-lat="<?= $lat; ?>"
                    data-lon="<?= $lon; ?>">
                    View Forecast
                </button>
            </div>
        </div>

    <?php endwhile; ?>

    </div>
</div>

<!-- PROFILE POPUP -->
<div class="popup-overlay" id="profilePopup">
    <div class="popup-card">

        <div class="popup-header">
            <h3>Profile</h3>
            <span class="close-popup">&times;</span>
        </div>

        <div class="popup-avatar">
            <i class="fa-solid fa-circle-user"></i>
        </div>

        <form>
            <input type="text" value="<?= $user_name ?>">
            <input type="email" value="<?= $user_email ?>">

        </form>
        <button class="logout-btn" onclick="window.location.href='logout.php'">
            Log out
        </button>
    </div>
</div>

<script src="function.js"></script>
</body>
</html>