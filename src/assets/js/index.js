const menuToggle = document.querySelector('.menu-toggle');
const navKanan = document.querySelector('.nav-kanan');

menuToggle.addEventListener('click', () => {
    // Toggle class 'active' pada nav-kanan
    navKanan.classList.toggle('active');
});