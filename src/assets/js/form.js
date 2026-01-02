// Toggle Sidebar untuk Mobile
const hamburgerMenu = document.getElementById('hamburgerMenu');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

hamburgerMenu.addEventListener('click', function () {
    sidebar.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
    hamburgerMenu.classList.toggle('active');
});

// Close sidebar when clicking overlay
sidebarOverlay.addEventListener('click', function () {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    hamburgerMenu.classList.remove('active');
});

// Close sidebar when clicking menu item (optional)
const menuItems = document.querySelectorAll('.side-nav a');
menuItems.forEach(item => {
    item.addEventListener('click', function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            hamburgerMenu.classList.remove('active');
        }
    });
});