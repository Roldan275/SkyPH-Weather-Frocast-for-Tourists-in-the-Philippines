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
        SEARCH FUNCTIONALITY (WITH RED GLOW INTEGRATION)
    ========================= */
    const searchInput = document.querySelector(".searchInput");
    const sidebarSearchBtn = document.querySelectorAll(".menu-btn")[0]; // First button in sidebar

    function handleSearch() {
        if (!searchInput) return;

        const value = searchInput.value.toLowerCase();
        const cards = document.querySelectorAll(".card");

        cards.forEach(card => {
            const name = card.querySelector("h4")?.innerText.toLowerCase() || "";
            const location = card.querySelector("p")?.innerText.toLowerCase() || "";

            card.style.display =
                (name.includes(value) || location.includes(value))
                    ? "flex" /* Maintained flex state to protect button layouts */
                    : "none";
        });
    }

    if (sidebarSearchBtn && searchInput) {
        sidebarSearchBtn.addEventListener("click", () => {
            searchInput.focus();
            searchInput.scrollIntoView({ behavior: "smooth", block: "center" });
            
            // Added the active red glowing animation class
            searchInput.classList.add("highlighted-glow");

            // Automatically clears out the glow after 2.5 seconds
            setTimeout(() => {
                searchInput.classList.remove("highlighted-glow");
            }, 2500);
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", handleSearch);
        searchInput.addEventListener("blur", () => {
            searchInput.classList.remove("highlighted-glow");
        });
    }

    /* =========================
        PROFILE POPUP LOGIC
    ========================= */
    const profilePopup = document.getElementById("profilePopup");
    const profileIcon = document.getElementById("profileIcon"); // Upper right
    const sidebarProfileBtn = document.querySelectorAll(".menu-btn")[1]; // Second button in sidebar
    const closePopup = document.querySelector(".close-popup");
    const cancelBtn = document.querySelector(".cancel-btn");

    function openProfile() {
        if (profilePopup) profilePopup.style.display = "flex";
    }

    function closeProfile() {
        if (profilePopup) profilePopup.style.display = "none";
    }

    if (profileIcon) profileIcon.addEventListener("click", openProfile);
    if (sidebarProfileBtn) sidebarProfileBtn.addEventListener("click", openProfile);
    if (closePopup) closePopup.addEventListener("click", closeProfile);
    if (cancelBtn) cancelBtn.addEventListener("click", closeProfile);

    window.addEventListener("click", (e) => {
        if (e.target === profilePopup) closeProfile();
    });

    /* =========================
        WEATHER BACKGROUND & DISPLAY
    ========================= */
    const forecastBox = document.getElementById("forecastBox");
    const buttons = document.querySelectorAll(".viewForecastBtn");

    function getWeatherBackground(condition) {
        condition = condition.toLowerCase();
        if (condition.includes("rain")) return "image/rainy.jpg";
        if (condition.includes("cloud")) return "image/cloudy.jpg";
        if (condition.includes("clear") || condition.includes("sun")) return "image/sunny.jpg";
        if (condition.includes("storm") || condition.includes("thunder")) return "image/storm.jpg";
        return "image/default.jpg";
    }

    function updateWeatherDisplay(data, selectedIndex, location) {
        const list = data.five_day;
        const current = list[selectedIndex];

        forecastBox.classList.add("has-data");
        forecastBox.style.display = "grid";

        forecastBox.innerHTML = `
            <div class="weather-left">
                <div class="weather-main-card" style="background-image: url('${getWeatherBackground(current.condition)}'); background-size: cover; background-position: center; position: relative;">
                    <div class="weather-overlay"></div>
                    <div class="weather-content">
                        <div class="card-prediction-label">
                            ${location.name}, ${location.city}<br>
                            ${current.day} Prediction
                        </div>
                        <div class="temp-large">${current.temp}°</div>
                        <div class="condition-group">
                            <h2>${current.condition}</h2>
                            <p>Feels like ${current.temp}°</p>
                        </div>
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
            <div class="weather-right" style="position: relative;">
                
                <div id="closeForecastBtn" style="position: absolute; right: 15px; top: 32px; cursor: pointer; z-index: 10; color: #94a3b8; font-size: 1.5rem; transition: 0.2s;">
                    <i class="fa fa-times-circle"></i>
                </div>

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
            </div>
        `;

        // RESET LOGIC
        document.getElementById("closeForecastBtn").addEventListener("click", () => {
            forecastBox.classList.remove("has-data");
            forecastBox.style.display = "block";
            forecastBox.innerHTML = `
                <div class="empty-placeholder" style="text-align: center; padding: 40px; background: #e0eafd; border-radius: 12px; border: 1px solid #cbd5e1;">
                    <i class="fa fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <p>Select a destination below to view the 5-day forecast and location map.</p>
                </div>
            `;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        setTimeout(() => { initMap(location.lat, location.lon, location.name); }, 100);

        document.querySelectorAll(".tab-card").forEach(card => {
            card.addEventListener("click", () => {
                updateWeatherDisplay(data, parseInt(card.dataset.index), location);
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
        API FETCH
    ========================= */
    buttons.forEach(btn => {
        btn.addEventListener("click", async () => {
            const { lat, lon, name, city, province } = btn.dataset;
            if (!lat || !lon) return;

            const location = { name, city, province, lat: lat.trim(), lon: lon.trim() };
            forecastBox.style.display = "block";
            forecastBox.innerHTML = `<div class="loading-spinner" style="text-align:center; padding: 50px;"><i class="fa fa-spinner fa-spin" style="font-size: 3rem; color: #2563eb;"></i><p>Fetching Climate Data...</p></div>`;

            try {
                const res = await fetch(`../api/get_weather.php?lat=${location.lat}&lon=${location.lon}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                updateWeatherDisplay(data, 0, location);
                forecastBox.scrollIntoView({ behavior: 'smooth' });
            } catch (err) {
                forecastBox.innerHTML = `<div class="error-msg" style="color:red; text-align:center; padding:20px;">Error: ${err.message}</div>`;
            }
        });
    });
});

/* =========================
    LEAFLET MAP GLOBAL FUNCTIONS
========================= */
let map = null;
let marker = null;

function initMap(lat, lon, title) {
    const latNum = parseFloat(lat);
    const lonNum = parseFloat(lon);
    if (isNaN(latNum) || isNaN(lonNum)) return;

    const mapDiv = document.getElementById("map");
    if (!mapDiv) return;

    if (map !== null) {
        map.remove();
        map = null;
    }

    map = L.map("map").setView([latNum, lonNum], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([latNum, lonNum]).addTo(map).bindPopup(`<b>${title}</b>`).openPopup();

    setTimeout(() => { map.invalidateSize(); }, 300);
}