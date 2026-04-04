<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sky-PH Dashboard</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>MENU</h3>
    <button class="menu-btn">Dashboard</button>
    <button class="menu-btn">Search</button>
    <button class="menu-btn">Profile</button>
    <button class="menu-btn" id="logout">Log Out</button>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">

        <!-- TOGGLE SIDEBAR -->
        <button id="sidebar-toggle" class="toggle-btn">&#9776;</button>

        <!-- LOGO -->
        <div class="logo">
            <img src="image/logo.png" alt="Sky-PH Logo">
        </div>

        <!-- TOPBAR SEARCH -->
        <div class="top-search">
            <input type="text" id="searchBar" placeholder="Search tourist spot..." onkeyup="searchSpot()">
        </div>

        <!-- USER -->
        <div class="user">
            <span>Roldan Abaloyan</span>
            <i class="fa-solid fa-circle-user user-icon"></i>
        </div>

    </div>

    <!-- WELCOME -->
    <div class="welcome">
        <h2>Welcome To Sky-PH, <span>Roldan Abaloyan</span></h2>
        <p>Search and check weather forecasts for your favorite tourist destinations</p>

        <!-- NEW SEARCH DESIGN -->
        <div class="welcome-search">
            <input type="text" class="searchInput" placeholder="Search tourist spots...">
            <button>Search</button>
        </div>
    </div>

    <!-- EMPTY STATE -->
    <div class="empty-box">
        Select a tourist spot to view detailed weather forecast
    </div>

    <!-- TITLE -->
    <h3>Featured Tourist Spots</h3>

    <!-- CARDS -->
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

    while($row = $result->fetch_assoc()):
    ?>

        <div class="card">
            <img src="<?= $row['image']; ?>" alt="<?= $row['name']; ?>">
            <div class="card-body">
                <h4><?= $row['name']; ?></h4>
                <p><?= $row['city']; ?>, <?= $row['province']; ?></p>
                <p><?= $row['weather_condition'] ?? 'No data'; ?></p>
                <p><?= $row['temperature'] ? $row['temperature'] . '°C' : ''; ?></p>
                <button>View Forecast</button>
            </div>
        </div>

    <?php endwhile; ?>

    </div>

</div>

<script src="function.js"></script>
</body>
</html>