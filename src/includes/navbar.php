<?php
// --- navbar.php ---

// 1. Pastikan session dimulai (cegah error jika dipanggil 2x)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Logika Hitung Cart (Hanya jika user login)
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    // Pastikan koneksi $pdo tersedia. 
    // Jika file ini di-include di file yang belum ada $pdo, kita perlu handling.
    // Asumsi: File induk (catalog.php/tentangKami.php) sudah require 'database.php'
    if (isset($pdo)) {
        $stmt_nav = $pdo->prepare("SELECT SUM(quantity) FROM carts WHERE user_id = ?");
        $stmt_nav->execute([$_SESSION['user_id']]);
        $cart_count = $stmt_nav->fetchColumn() ?: 0;
    }
}

// 3. Logika Active State (Untuk highlight menu)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="../assets/css/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<nav>
    <div class="logo">
        <a href="index.php">Exim<span>Go</span></a>
    </div>

    <div class="nav-kanan">
        <ul class="nav-links">
            <li><a href="tentangKami.php" class="<?= $current_page == 'tentangKami.php' ? 'active' : '' ?>">TENTANG
                    KAMI</a></li>
            <li><a href="tentangPet.php" class="<?= $current_page == 'tentangPet.php' ? 'active' : '' ?>">TENTANG
                    PET</a></li>
            <li><a href="#" class="<?= $current_page == 'berita.php' ? 'active' : '' ?>">BERITA</a></li>

            <li><a href="catalog.php" class="<?= $current_page == 'catalog.php' ? 'active' : '' ?>">SHOP</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="cart.php" class="cart-wrapper <?= $current_page == 'cart.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cart-shopping"></i> Keranjang
                        <?php if ($cart_count > 0): ?>
                            <span class="badge-cart"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">Akun</a></li>
                <li><a href="../auth/logout.php" style="color: #e74c3c;">Logout</a></li>

            <?php else: ?>
                <div class="auth-buttons">
                    <a href="../auth/login.php" class="btn-login">Login</a>
                    <a href="../auth/register.php" class="btn-register">Register</a>
                </div>
            <?php endif; ?>
        </ul>
    </div>
</nav>