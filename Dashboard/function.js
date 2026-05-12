document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       SIDEBAR & UI TOGGLES
    ========================= */
    const sidebar = document.querySelector(".sidebar");
    const main = document.querySelector(".main");
    const toggleBtn = document.getElementById("toggleBtn");

    if (toggleBtn && sidebar && main) {
        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
            main.classList.toggle("sidebar-hidden");
        });
    }

    /* =========================
       SEARCH FUNCTION
    ========================= */
    const searchInput = document.querySelector(".searchInput");

    function handleSearch() {
        if (!searchInput) return;
        const value = searchInput.value.toLowerCase();
        const cards = document.querySelectorAll(".card");
        cards.forEach(card => {
            const name = card.querySelector("h4")?.innerText.toLowerCase() || "";
            const location = card.querySelector("p")?.innerText.toLowerCase() || "";
            card.style.display = (name.includes(value) || location.includes(value)) ? "block" : "none";
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", handleSearch);
    }

    const forecastBox = document.getElementById("forecastBox");
    const buttons = document.querySelectorAll(".viewForecastBtn");

    /* =========================
       PROFILE POPUP
    ========================= */
    const profilePopup = document.getElementById("profilePopup");
    const profileIcon = document.getElementById("profileIcon");
    const profileBtn = document.querySelectorAll(".menu-btn")[1];
    const closePopup = document.querySelector(".close-popup");
    const cancelBtn = document.querySelector(".cancel-btn");

    function openProfile() {
        if (profilePopup) profilePopup.style.display = "flex";
    }

    function closeProfile() {
        if (profilePopup) profilePopup.style.display = "none";
    }

    if (profileIcon) profileIcon.addEventListener("click", openProfile);
    if (profileBtn) profileBtn.addEventListener("click", openProfile);
    if (closePopup) closePopup.addEventListener("click", closeProfile);
    if (cancelBtn) cancelBtn.addEventListener("click", closeProfile);

    window.addEventListener("click", (e) => {
        if (e.target === profilePopup) closeProfile();
    });

    function updateWeatherDisplay(data, selectedIndex, location) {
    const list = data.five_day;
    const current = list[selectedIndex];

    forecastBox.classList.add("has-data");
    forecastBox.style.display = "grid";
    
    forecastBox.innerHTML = `
        <div class="weather-left">
            <div class="weather-main-card">
                <div class="card-prediction-label">
                    ${location.name}, ${location.city}<br>${current.day} Prediction
                </div>
                <div class="temp-large">${current.temp}°</div>
                <div class="condition-group">
                    <h2>${current.condition}</h2>
                    <p>Feels like ${current.temp}°</p>
                </div>
            </div>
            <div class="details-grid">
                ${renderDetailItem('fa-wind', 'Wind', current.wind + ' m/s')}
                ${renderDetailItem('fa-tint', 'Humidity', current.humidity + '%')}
                ${renderDetailItem('fa-eye', 'Visibility', (current.visibility || 'N/A') + ' m')}
                ${renderDetailItem('fa-sun', 'UV Index', current.uvi ?? '0')}
                ${renderDetailItem('fa-tachometer-alt', 'Pressure', current.pressure + ' hPa')}
                ${renderDetailItem('fa-cloud-sun', 'Dew Point', (current.dew_point ?? 'N/A') + '°C')}
            </div>
        </div>

        <div class="weather-right">
            <h3>5-Day Forecast</h3>
            <div class="forecast-tabs-wrapper">
                <div class="daily-tabs-internal">
                    ${list.map((d, i) => `
                        <div class="tab-card ${i === selectedIndex ? 'active' : ''}" data-index="${i}">
                            <span class="tab-day">${d.day}</span>
                            <img src="https://openweathermap.org/img/wn/${d.icon}@2x.png" alt="weather">
                            <span class="tab-temp">${d.temp}°C</span>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div id="map-section-inner" style="margin-top: 25px;">
                <h4 style="margin-bottom: 10px; color: #64748b;">Location Map</h4>
                <div id="map" style="width: 100%; height: 250px; border-radius: 20px; border: 1px solid #edf2f7; z-index: 1;"></div>
            </div>

            <div class="forecast-footer">
                <p><i class="fa fa-shield-alt"></i> SkyPH Tourism Verified Data</p>
            </div>
        </div>
    `;

    // Re-attach tab listeners
    document.querySelectorAll(".tab-card").forEach(card => {
        card.addEventListener("click", () => {
            updateWeatherDisplay(data, parseInt(card.dataset.index), location);
            initMap(location.lat, location.lon, location.name);
        });
    });
}

function renderDetailItem(icon, label, value) {
    return `
        <div class="detail-item">
            <i class="fa ${icon}"></i>
            <span>${label}</span>
            <b>${value}</b>
        </div>
    `;
}

/* =========================
       API & MAP INITIALIZATION
    ========================= */
    buttons.forEach(btn => {
        btn.addEventListener("click", async () => {
            const { lat, lon, name, city, province } = btn.dataset;
            if (!lat || !lon) return;

            const location = {
                name,
                city,
                province,
                lat: lat.trim(),
                lon: lon.trim()
            };

            // Show loading state
            forecastBox.style.display = "block";
            forecastBox.innerHTML = `
                <div class="loading-spinner" style="text-align:center; padding: 50px;">
                    <i class="fa fa-spinner fa-spin" style="font-size: 3rem; color: #2563eb;"></i>
                    <p style="margin-top: 10px;">Fetching Climate Data...</p>
                </div>
            `;

            try {
                const res = await fetch(`../api/get_weather.php?lat=${location.lat}&lon=${location.lon}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                updateWeatherDisplay(data, 0, location);
                initMap(location.lat, location.lon, location.name);
                forecastBox.scrollIntoView({ behavior: 'smooth' });

            } catch (err) {
                forecastBox.innerHTML = `<div class="error-msg" style="color:red; text-align:center; padding: 20px;">Error: ${err.message}</div>`;
            }
        });
    });
});

/* =========================
   FREE MAPS (LEAFLET.JS)
========================= */
let map;
let marker;

function initMap(lat, lon, title) {
    const latNum = parseFloat(lat);
    const lonNum = parseFloat(lon);

    if (isNaN(latNum) || isNaN(lonNum)) return;

    const mapDiv = document.getElementById("map");
    if (!mapDiv) return;

    if (!map) {
        map = L.map('map').setView([latNum, lonNum], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([latNum, lonNum]).addTo(map)
            .bindPopup(`<b>${title}</b>`)
            .openPopup();
    } else {
        map.setView([latNum, lonNum], 13);
        marker.setLatLng([latNum, lonNum])
              .setPopupContent(`<b>${title}</b>`)
              .openPopup();
    }
    
    setTimeout(() => {
        map.invalidateSize();
    }, 400);
}