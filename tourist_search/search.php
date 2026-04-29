<?php
include '../db.php';

$touristSpots = [];

if (isset($conn)) {
    $query = "SELECT name, region, province, city FROM touristspots_table";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $location = $row['city'] . ', ' . $row['province'];
        $fullAddress = $row['city'] . ', ' . $row['province'] . ', ' . $row['region'];

        $touristSpots[] = [
            'name' => $row['name'],
            'location' => $location,
            'fullAddress' => $fullAddress,
            'region' => $row['region'],
            'province' => $row['province'],
            'city' => $row['city']
        ];
    }
?>

<!DOCTYPE html>
<html>
<head>
<title>Sky-PH Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="main">

<div class="header">
    <div class="search-wrapper">
        <input type="text" id="hintSearch" placeholder="Search by location...">
        <div id="hintDropdown" class="dropdown"></div>
    </div>
</div>

<div class="welcome">
    <h2>Search Tourist Spots</h2>

    <div class="search-wrapper">
        <input type="text" id="touristSearch" placeholder="Search tourist spot...">
        <div id="touristDropdown" class="dropdown"></div>
    </div>
</div>

<div id="emptyState" class="empty">Select a tourist spot</div>

<div id="results" class="cards"></div>

<!-- MAP (legacy, not used) -->
<div id="mapContainer" style="display:none;"></div>

</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="modalTitle"></h2>
        <p id="modalAddress"></p>
        <iframe id="modalMap"></iframe>
        <div id="modalWeather"></div>
    </div>
</div>

<script>
const API_KEY = "YOUR_API_KEY_HERE"; // ← Replace with your OpenWeatherMap API Key

const touristSpots = <?php echo json_encode($touristSpots); ?>;

const touristInput = document.getElementById('touristSearch');
const touristDropdown = document.getElementById('touristDropdown');
const resultsDiv = document.getElementById('results');
const emptyState = document.getElementById('emptyState');

let history = JSON.parse(localStorage.getItem('searchHistory')) || [];

/* INPUT */
touristInput.addEventListener('input', () => {
    const query = touristInput.value.toLowerCase().trim();
    showTouristSuggestions(query);
    searchTourist(query);
});

/* SUGGESTIONS */
function showTouristSuggestions(query) {
    touristDropdown.innerHTML = '';

    if (!query && history.length > 0) {
        touristDropdown.innerHTML += `<div class="label">Recent Searches</div>`;
        history.forEach(itemText => {
            const item = document.createElement('div');
            item.textContent = itemText;
            item.onclick = () => {
                touristInput.value = itemText;
                searchTourist(itemText.toLowerCase());
                touristDropdown.innerHTML = '';
            };
            touristDropdown.appendChild(item);
        });
        touristDropdown.innerHTML += `<div class="clear-btn" onclick="clearHistory()">Clear History</div>`;
        return;
    }

    const matches = touristSpots.filter(spot =>
        spot.name.toLowerCase().includes(query) ||
        spot.location.toLowerCase().includes(query)
    ).slice(0,5);

    matches.forEach(spot => {
        const item = document.createElement('div');
        item.innerHTML = `
            <strong>${spot.name}</strong><br>
            <small>📍 ${spot.fullAddress}</small>
        `;

        item.onclick = () => {
            saveHistory(touristInput.value, spot.name);
            touristInput.value = spot.name;
            searchTourist(spot.name.toLowerCase());
            touristDropdown.innerHTML = '';
        };

        touristDropdown.appendChild(item);
    });
}

/* SEARCH */
function searchTourist(query) {
    query = query.toLowerCase().trim();

    resultsDiv.innerHTML = '';

    const filtered = touristSpots.filter(spot =>
        spot.name.toLowerCase().includes(query)
    );

    if (filtered.length === 0) {
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
            <p>📍 ${spot.fullAddress}</p>
            <button onclick="viewPlace('${spot.name}','${spot.fullAddress}')">View Info</button>
            <button onclick="viewForecast('${spot.name}','${spot.fullAddress}')">View Forecast</button>
        `;

        resultsDiv.appendChild(card);
    });
}

/* MODAL */
function openModal() {
    document.getElementById('modal').style.display = "flex";
}
function closeModal() {
    document.getElementById('modal').style.display = "none";
}

/* VIEW INFO */
function viewPlace(name, address) {
    openModal();

    document.getElementById('modalTitle').innerText = name;
    document.getElementById('modalAddress').innerText = address;
    document.getElementById('modalWeather').innerHTML = "";

    const encoded = encodeURIComponent(address);
    document.getElementById('modalMap').src =
        `https://www.google.com/maps?q=${encoded}&output=embed`;
}

/* VIEW WEATHER (with real API) */
function viewForecast(name, address) {
    openModal();

    document.getElementById('modalTitle').innerText = "Weather: " + name;
    document.getElementById('modalAddress').innerText = address;
    document.getElementById('modalMap').src = "";

    fetchWeather(address);
}

function fetchWeather(address) {
    const weatherDiv = document.getElementById('modalWeather');
    weatherDiv.innerHTML = `<p>Loading weather...</p>`;

    // Use city/region parts for query
    const q = encodeURIComponent(address);

    fetch(`https://api.openweathermap.org/data/2.5/weather?q=${q}&units=metric&appid=${API_KEY}`)
    .then(res => res.json())
    .then(data => {
        if(data.cod !== 200) {
            weatherDiv.innerHTML = `<p>Weather not found for this location.</p>`;
            return;
        }

        const temp = data.main.temp;
        const desc = data.weather[0].description;
        const icon = data.weather[0].icon;
        const humidity = data.main.humidity;
        const wind = data.wind.speed;

        weatherDiv.innerHTML = `
            <div class="weather-info">
                <img src="https://openweathermap.org/img/wn/${icon}@2x.png" alt="${desc}">
                <h3>${temp}°C</h3>
                <p style="text-transform:capitalize;">${desc}</p>
                <p>💧 Humidity: ${humidity}%</p>
                <p>🌬 Wind: ${wind} m/s</p>
            </div>
        `;
    })
    .catch(err => {
        weatherDiv.innerHTML = `<p>Error fetching weather.</p>`;
    });
}

/* HISTORY */
function saveHistory(typed, selected) {
    const entry = typed + " → " + selected;

    if (!history.includes(entry)) {
        history.unshift(entry);
        if (history.length > 5) history.pop();
        localStorage.setItem('searchHistory', JSON.stringify(history));
    }
}

function clearHistory() {
    history = [];
    localStorage.removeItem('searchHistory');
    touristDropdown.innerHTML = '';
}
</script>

</body>
</html>
