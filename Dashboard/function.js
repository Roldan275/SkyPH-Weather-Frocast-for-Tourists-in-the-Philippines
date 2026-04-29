
document.addEventListener('DOMContentLoaded', () => {
    // Put all your code inside here
    const sidebarToggle = document.getElementById('toggleBtn');
    const sidebar = document.querySelector('.sidebar');
const main = document.querySelector('.main');

if (sidebarToggle && sidebar && main) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('hidden');
        main.classList.toggle('sidebar-hidden');
    });
}

    document.addEventListener('click', function(e) {
    // Change 'toggleBtn' to 'sidebarToggle' here
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        if (window.innerWidth <= 1024 && !sidebar.classList.contains('hidden')) {
            sidebar.classList.add('hidden');
            main.classList.add('sidebar-hidden');
        }
    }
});


// Welcome search
const searchInputs = document.querySelectorAll(".searchInput");
searchInputs.forEach(input => input.addEventListener("keyup", handleSearch));
const welcomeSearchButton = document.querySelector(".welcome-search button");
const searchInput = document.querySelector(".searchInput");
const sidebarSearchButton = document.querySelector(".sidebar .menu-btn"); // First menu-btn is Search

welcomeSearchButton.addEventListener("click", (e) => {
    handleSearch();
    searchInput.focus();
});

sidebarSearchButton.addEventListener("click", () => {
    searchInput.focus();
});

searchInput.addEventListener("focus", () => {
    searchInput.classList.add("highlighted");
});

searchInput.addEventListener("blur", () => {
    searchInput.classList.remove("highlighted");
});

function handleSearch() {
    let value = document.querySelector(".searchInput").value.toLowerCase();
    let cards = document.querySelectorAll(".card");
    cards.forEach(card => {
        let name = card.querySelector("h4").innerText.toLowerCase();
        let location = card.querySelector("p").innerText.toLowerCase();
        card.style.display = (name.includes(value) || location.includes(value)) ? "block" : "none";
    });
}

    // PROFILE MODAL
    // =========================

const profilePopup = document.getElementById("profilePopup");
const profileIcon = document.getElementById("profileIcon");
const profileBtn = document.querySelectorAll(".sidebar .menu-btn")[1]; // Profile button in sidebar
const closePopup = document.querySelector(".close-popup");
const cancelBtn = document.querySelector(".cancel-btn");

// OPEN POPUP (icon)
profileIcon.addEventListener("click", () => {
    profilePopup.style.display = "flex";
});

// OPEN POPUP (sidebar)
profileBtn.addEventListener("click", () => {
    profilePopup.style.display = "flex";
});

// CLOSE POPUP (X)
closePopup.addEventListener("click", () => {
    profilePopup.style.display = "none";
});

// CLOSE POPUP (Cancel)
cancelBtn.addEventListener("click", () => {
    profilePopup.style.display = "none";
});

// CLICK OUTSIDE CARD
window.addEventListener("click", (e) => {
    if (e.target === profilePopup) {
        profilePopup.style.display = "none";
    }
});
});