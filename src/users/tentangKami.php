<?php
require_once '../config/database.php';
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - EximGo</title>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/tentangKami.css">
</head>

<body>

    <nav>
    <div class="logo">
        <?php if (isset($_SESSION['user_id'])): ?>
            Exim<span>Go</span>
        <?php else: ?>
            <a href="../index.php" style="text-decoration: none; color: inherit;">Exim<span>Go</span></a>
        <?php endif; ?>
    </div>

    <div class="nav-kanan">
        <ul class="pilihan">
            <li><a href="tentangKami.php">TENTANG KAMI</a></li>
            <li><a href="tentangPet.php">TENTANG PET</a></li>
            <li><a href="#">BERITA</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="catalog.php">SHOP</a></li>
                <li><a href="profile.php">AKUN</a></li>
                
                <li><a href="../auth/logout.php" style="color: red;">LOGOUT</a></li>
            <?php endif; ?>
        </ul>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="auth">
                <button class="login"><a href="../auth/login.php">Login</a></button>
                <button class="register"><a href="../auth/register.php">Register</a></button>
            </div>
        <?php endif; ?>
    </div>
</nav>

    <main class="main-info-mode">
        <div class="summary-card">

            <div class="card-content">
                <h2>Mengenai Kami: EximGo & Daur Ulang</h2>
                <p>Kami terinspirasi oleh model bisnis *Clean-Tech* seperti PT Inocycle Technology Group Tbk ("INOV")
                    yang berfokus pada solusi daur ulang global untuk masalah lokal.</p>

                <div class="separator"></div>

                <h3>Bisnis Daur Ulang (INOV Model)</h3>
                <p>INOV adalah perusahaan teknologi bersih yang mengolah botol PET dan limbah plastik menjadi
                    <strong>Recycled Polyester Staple Fiber (Re-PSF)</strong>, yaitu serat sintetis ramah lingkungan.
                </p>
                <ul>
                    <li>INOV adalah produsen serat daur ulang terbesar yang memiliki sertifikasi <strong>Global Recycled
                            Standards (GRS)</strong> di Indonesia.</li>
                    <li>Mereka berkontribusi mengurangi polusi dengan memproses miliaran sampah botol per tahun.</li>
                    <li>Produk Re-PSF digunakan untuk berbagai industri seperti garmen, otomotif, hingga produk rumah
                        tangga ("homeware"), contohnya bantal dan kasur.</li>
                </ul>
                <p style="font-style: italic; border-left: 3px solid var(--accent-color); padding-left: 10px;">
                    Kami di EximGo mendukung penuh ekosistem ekonomi sirkular ini dengan menyediakan platform untuk
                    produk-produk berkelanjutan.
                </p>
            </div>
        </div>

    </main>
</body>

</html>