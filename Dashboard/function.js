// Sidebar toggle
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
const main = document.querySelector('.main');

sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth <= 1024) {
        sidebar.classList.toggle('show');
    } else {
        sidebar.classList.toggle('hidden');
        main.classList.toggle('hidden-sidebar');
    }
});

// Close sidebar when clicking outside (mobile/tablet)
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    }
});

// Topbar search
function searchSpot() {
    let input = document.getElementById("searchBar").value.toLowerCase();
    let cards = document.querySelectorAll(".card");
    cards.forEach(card => {
        let title = card.querySelector("h4").innerText.toLowerCase();
        card.style.display = title.includes(input) ? "block" : "none";
    });
}

// Welcome search
const searchInputs = document.querySelectorAll(".searchInput");
searchInputs.forEach(input => input.addEventListener("keyup", handleSearch));
document.querySelector(".welcome-search button").addEventListener("click", handleSearch);

function handleSearch() {
    let value = document.querySelector(".searchInput").value.toLowerCase();
    let cards = document.querySelectorAll(".card");
    cards.forEach(card => {
        let name = card.querySelector("h4").innerText.toLowerCase();
        let location = card.querySelector("p").innerText.toLowerCase();
        card.style.display = (name.includes(value) || location.includes(value)) ? "block" : "none";
    });
}