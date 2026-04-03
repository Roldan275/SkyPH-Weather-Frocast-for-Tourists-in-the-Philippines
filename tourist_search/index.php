<?php
include 'db.php';

/* =========================
   FETCH FROM DATABASE
========================= */
$touristSpots = [];

if (isset($conn)) {
    $query = "SELECT name, region, province, city FROM touristspots_table";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $location = $row['city'] . ', ' . $row['province'];

        $touristSpots[] = [
            'name' => $row['name'],
            'location' => $location,
            'region' => $row['region'],
            'province' => $row['province'],
            'city' => $row['city']
        ];
    }
}
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
        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="hintSearch" placeholder="Search by region, province...">
            <div id="hintDropdown" class="dropdown"></div>
        </div>

        <div class="user">👤 Roldan Abaloyan</div>
    </div>

    <!-- WELCOME -->
    <div class="welcome">
        <h2>Welcome To Sky-PH, <span>Roldan Abaloyan</span></h2>
        <p>Search and check weather forecasts for your favorite tourist destinations</p>

        <!-- TOURIST SEARCH -->
        <div class="search-box">
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" id="touristSearch" placeholder="Search tourist spot...">
                <div id="touristDropdown" class="dropdown"></div>
            </div>
        </div>
    </div>

    <!-- EMPTY -->
    <div id="emptyState" class="empty">
        ☀️ Select a tourist spot to view detailed weather forecast
    </div>

    <!-- RESULTS -->
    <div id="results" class="cards"></div>

</div>

<script>
const touristSpots = <?php echo json_encode($touristSpots); ?>;

/* ELEMENTS */
const touristInput = document.getElementById('touristSearch');
const hintInput = document.getElementById('hintSearch');

const touristDropdown = document.getElementById('touristDropdown');
const hintDropdown = document.getElementById('hintDropdown');

const resultsDiv = document.getElementById('results');
const emptyState = document.getElementById('emptyState');

let history = [];

/* ================= TOURIST SEARCH ================= */
touristInput.addEventListener('input', () => {
    const query = touristInput.value.toLowerCase().trim();
    showTouristSuggestions(query);
    searchTourist(query);
});

function showTouristSuggestions(query) {
    touristDropdown.innerHTML = '';
    if (!query) return;

    const matches = touristSpots.filter(spot =>
        spot.name.toLowerCase().includes(query) ||
        spot.location.toLowerCase().includes(query)
    ).slice(0, 5);

    if (matches.length === 0) return;

    touristDropdown.innerHTML += `<div class="label">Tourist Spots</div>`;

    matches.forEach(spot => {
        const item = document.createElement('div');
        item.innerHTML = `${spot.name} <small>(${spot.location})</small>`;

        item.onclick = () => {
            const value = spot.name.toLowerCase();
            touristInput.value = spot.name;
            searchTourist(value);
            saveHistory(spot.name);
            touristDropdown.innerHTML = '';
        };

        touristDropdown.appendChild(item);
    });

    if (history.length > 0) {
        touristDropdown.innerHTML += `<div class="clear-btn" onclick="clearHistory()">Clear History</div>`;
    }
}

function searchTourist(query) {
    query = query.toLowerCase().trim();
    resultsDiv.innerHTML = '';

    if (!query) {
        emptyState.style.display = "block";
        return;
    }

    const filtered = touristSpots.filter(spot =>
        spot.name.toLowerCase().includes(query) ||
        spot.location.toLowerCase().includes(query)
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
            <button onclick="viewForecast('${spot.name}')">View Forecast</button>
        `;

        resultsDiv.appendChild(card);
    });
}

/* ================= HINT SEARCH (AUTO DETECT) ================= */
hintInput.addEventListener('input', () => {
    const query = hintInput.value.toLowerCase().trim();
    hintDropdown.innerHTML = '';

    if (!query) return;

    let locations = [];

    touristSpots.forEach(spot => {
        if (spot.region) locations.push({ name: spot.region, type: 'Region' });
        if (spot.province) locations.push({ name: spot.province, type: 'Province' });
        if (spot.city) locations.push({ name: spot.city, type: 'City' });
    });

    const unique = [];
    const seen = new Set();

    locations.forEach(loc => {
        const key = loc.name.toLowerCase();
        if (!seen.has(key)) {
            seen.add(key);
            unique.push(loc);
        }
    });

    const matches = unique.filter(loc =>
        loc.name.toLowerCase().includes(query)
    );

    if (matches.length === 0) return;

    hintDropdown.innerHTML += `<div class="label">Locations</div>`;

    matches.slice(0, 6).forEach(loc => {
        const item = document.createElement('div');
        item.innerHTML = `${loc.name} <small>(${loc.type})</small>`;

        item.onclick = () => {
            hintInput.value = loc.name;

            const filtered = touristSpots.filter(spot =>
                (spot.region && spot.region.toLowerCase().includes(loc.name.toLowerCase())) ||
                (spot.province && spot.province.toLowerCase().includes(loc.name.toLowerCase())) ||
                (spot.city && spot.city.toLowerCase().includes(loc.name.toLowerCase()))
            );

            showHintResults(filtered, loc.name);
            hintDropdown.innerHTML = '';
        };

        hintDropdown.appendChild(item);
    });
});

function showHintResults(data, label) {
    resultsDiv.innerHTML = '';

    if (data.length === 0) {
        emptyState.innerHTML = `❌ No places found in "<b>${label}</b>"`;
        emptyState.style.display = "block";
        return;
    }

    emptyState.style.display = "none";

    data.forEach(spot => {
        const card = document.createElement('div');
        card.classList.add('card');

        card.innerHTML = `
            <div class="img"></div>
            <h3>${spot.name}</h3>
            <p>📍 ${spot.location}</p>
            <button onclick="viewForecast('${spot.name}')">View Forecast</button>
        `;

        resultsDiv.appendChild(card);
    });
}

/* HISTORY */
function saveHistory(value) {
    history.push(value);
}

function clearHistory() {
    history = [];
    touristDropdown.innerHTML = '';
}

function viewForecast(name) {
    alert("Showing forecast for: " + name);
}
</script>

</body>
</html>
