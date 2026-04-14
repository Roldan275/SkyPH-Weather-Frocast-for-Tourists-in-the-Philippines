<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sky-PH Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="sidebar">
    <h3>MENU</h3>
    <button class="menu-btn">Dashboard</button>
    <button class="menu-btn">Search</button>
    <button class="menu-btn">Profile</button>
    <button class="menu-btn" id="logout">Log Out</button>
</div>

<div class="main">

    <div class="topbar">
        <button id="sidebar-toggle" class="toggle-btn">&#9776;</button>
        <div class="logo">
            <img src="image/logo.png" alt="Sky-PH Logo">
        </div>
        <div class="top-search">
            <input type="text" id="searchBar" placeholder="Search tourist spot..." onkeyup="searchSpot()">
        </div>
        <div class="user">
            <span>Roldan Abaloyan</span>
            <i class="fa-solid fa-circle-user user-icon"></i>
        </div>
    </div>

    <div class="welcome">
        <h2>Welcome To Sky-PH, <span>Roldan Abaloyan</span></h2>
        <p>Search and check weather forecasts for your favorite tourist destinations</p>
        <div class="welcome-search">
            <input type="text" class="searchInput" placeholder="Search tourist spots...">
            <button>Search</button>
        </div>
    </div>

    <div class="empty-box">
        Select a tourist spot to view detailed weather forecast
    </div>

    <h3>Featured Tourist Spots</h3>

    <div class="cards">
    <?php
    $sql = "
    SELECT 
        t.id,
        t.name,
        t.region,
        t.province,
        t.city,
        t.image,
        w.temperature,
        w.weather_condition
    FROM touristspots_table t
    LEFT JOIN weather_forecasts w
    ON t.id = w.tourist_spot_id
    AND w.id = (
        SELECT MAX(id)
        FROM weather_forecasts
        WHERE tourist_spot_id = t.id
    )
    ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0):
        while($row = $result->fetch_assoc()):
            // Convert BLOB to Base64 for display
            $imageSource = 'image/placeholder.png'; // Default if empty
            if (!empty($row['image'])) {
                $imageSource = 'data:image/jpeg;base64,' . base64_encode($row['image']);
            }
    ?>
        <div class="card">
            <img src="<?= $imageSource; ?>" alt="<?= $row['name']; ?>">
            <div class="card-body">
                <h4><?= $row['name']; ?></h4>
                <p><?= $row['city']; ?>, <?= $row['province']; ?></p>
                <p><?= $row['weather_condition'] ?? 'No data'; ?></p>
                <p><?= $row['temperature'] ? $row['temperature'] . '°C' : ''; ?></p>
                <button>View Forecast</button>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<p>No tourist spots found.</p>";
    endif;
    ?>
    </div>

</div>

<script src="function.js"></script>
</body>
</html>