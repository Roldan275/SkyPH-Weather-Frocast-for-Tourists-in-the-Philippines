<?php
$touristSpots = [
    ['name' => 'Boracay White Beach', 'location' => 'Malay, Aklan', 'weather' => 'Sunny ☀️'],
    ['name' => 'Chocolate Hills', 'location' => 'Carmen, Bohol', 'weather' => 'Sunny ☀️'],
    ['name' => 'Banaue Rice Terraces', 'location' => 'Ifugao', 'weather' => 'Sunny ☀️'],
    ['name' => 'Kalanggaman Island', 'location' => 'Leyte', 'weather' => 'Clear 🌤️'],
    ['name' => 'Mayon Volcano', 'location' => 'Albay', 'weather' => 'Cloudy ☁️']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sky-PH Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="main">

    <!-- HEADER -->
    <div class="header">
        <input type="text" id="searchInput" placeholder="☰  Hinted search text           ">
        <div class="user">👤 User</div>
    </div>

    <!-- WELCOME CARD -->
    <div class="welcome">
        <h2>Welcome To Sky-PH, <span>User</span></h2>
        <p>Search and check weather forecasts for your favorite tourist destinations</p>

        <div class="search-box">
            <span class="search-icon">🔍︎</span>
            <input type="text" id="searchInput2" placeholder="Search tourist spot...">
        </div>
    </div>

    <!-- RESULT / EMPTY STATE -->
    <div id="emptyState" class="empty">
        ☀️ Select a tourist spot to view detailed weather forecast
    </div>

    <!-- RESULTS -->
    <div id="results" class="cards"></div>

</div>

<script>
const touristSpots = <?php echo json_encode($touristSpots); ?>;

const searchInputs = [
    document.getElementById('searchInput'),
    document.getElementById('searchInput2')
];

const resultsDiv = document.getElementById('results');
const emptyState = document.getElementById('emptyState');

function search(query) {
    query = query.toLowerCase().trim();
    resultsDiv.innerHTML = '';

    if (!query) {
        emptyState.style.display = "block";
        return;
    }

    const filtered = touristSpots.filter(spot =>
        spot.name.toLowerCase().includes(query)
    );

    if (filtered.length === 0) {
        emptyState.innerHTML = `❌ No results for "<b>${query}</b>"`;
        emptyState.style.display = "block";
        return;
    }

    emptyState.style.display = "none";

    filtered.forEach(spot => {
        const card = document.createElement('div');
        card.classList.add('card');

        card.innerHTML = `
            <div class="img"></div>
            <h3>${spot.name}</h3>
            <p>📍 ${spot.location}</p>
            <span class="weather">${spot.weather}</span>
            <button onclick="viewForecast('${spot.name}')">View Forecast</button>
        `;

        resultsDiv.appendChild(card);
    });
}

function viewForecast(name) {
    alert("Showing forecast for: " + name);
}

/* Sync both search bars */
searchInputs.forEach(input => {
    input.addEventListener('input', (e) => {
        searchInputs.forEach(i => i.value = e.target.value);
        search(e.target.value);
    });
});
</script>

</body>
</html>