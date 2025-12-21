<?php
session_start();

require_once 'config/database.php';

// 3. LOGIKA FORMULIR FOOTER (Harus ada di sini agar footer berfungsi)
$form_status = "";
if (isset($_POST['submit_contact'])) {
    // Tangkap data
    $fname = htmlspecialchars($_POST['first_name'] ?? '');
    $lname = htmlspecialchars($_POST['last_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $msg = htmlspecialchars($_POST['message'] ?? '');

    // Validasi & Simpan
    if (!empty($fname) && !empty($email) && !empty($msg)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO forms (first_name, last_name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fname, $lname, $email, $phone, $msg]);
            $form_status = "success";
        } catch (Exception $e) {
            $form_status = "error";
        }
    } else {
        $form_status = "empty";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>

<body>
    <nav>
        <div class="logo">Exim<span>Go</span> </div>

        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <div class="nav-kanan">
            <ul class="pilihan">
                <li><a href="users/tentangKami.php">TENTANG KAMI</a></li>
                <li><a href="users/tentangPet.php">TENTANG PET</a></li>
                <li><a href="">BERITA</a></li>
            </ul>
            <div class="auth">
                <button class="login"><a href="auth/login.php">Login</a></button>
                <button class="register"><a href="auth/register.php">Register</a></button>
            </div>
        </div>
    </nav>

    <main>
        <div class="hero">
            <h1>Selamat datang di Eximgo</h1>
            <p>EximGo bagian dari PT Inocycle Technology Group, pelopor teknologi daur ulang plastik di Indonesia yang
                memproduksi Recycled Polyester Staple Fiber (Re-PSF) berkualitas tinggi untuk pasar global.
            </p>
        </div>
    </main>

    <div class="cards">
        <div class="card">
            <h2>Pengelolaan Limbah Terintegrasi</h2>
            <p>Kami mengumpulkan dan memilah sampah botol plastik (PET) dari berbagai sumber menggunakan jaringan rantai
                pasok yang luas dan efisien.</p>
        </div>
        <div class="card">
            <h2>Teknologi Daur Ulang Modern</h2>
            <p>Mengubah sampah plastik menjadi serat daur ulang berkualitas premium melalui proses pencucian dan
                pemrosesan berteknologi tinggi.</p>
        </div>
        <div class="card">
            <h2>Distribusi Serat & Non-Woven</h2>
            <p>Menyediakan pasokan Recycled Polyester Staple Fiber (Re-PSF) dan produk non-woven siap pakai untuk
                kebutuhan industri lokal maupun ekspor.</p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="../assets/js/index.js"></script>
</body>

</html>