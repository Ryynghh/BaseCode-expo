<?php
// 1. MULAI SESSION
session_start();

// 2. HUBUNGKAN DATABASE
// Perhatikan: Path-nya 'config/database.php', BUKAN '../config/database.php'
require_once 'config/database.php'; 

// 3. LOGIKA FORMULIR FOOTER (Harus ada di sini agar footer berfungsi)
$form_status = "";
if (isset($_POST['submit_contact'])) {
    // Tangkap data
    $fname = htmlspecialchars($_POST['first_name'] ?? '');
    $lname = htmlspecialchars($_POST['last_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $msg   = htmlspecialchars($_POST['message'] ?? '');

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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EximGo - Solusi Ekspor</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav>
        <div class="logo">Exim<span>Go</span> </div>
        <div class="nav-kanan">
            <ul class="pilihan">
                <li><a href="users/tentangKami.php">TENTANG KAMI</a></li>
                <li><a href="users/tentangPet.php">TENTANG PET</a></li>
                <li><a href="#">BERITA</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="users/catalog.php">SHOP</a></li>
                    <li><a href="users/cart.php">KERANJANG</a></li>
                    <li><a href="users/profile.php">AKUN</a></li>
                    <li><a href="auth/logout.php" style="color:red;">LOGOUT</a></li>
                <?php else: ?>
                    <div class="auth">
                        <button class="login"><a href="auth/login.php">Login</a></button>
                        <button class="register"><a href="auth/register.php">Register</a></button>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main>
        <div class="hero">
            <h1>Selamat datang di EximGo</h1>
            <p>Platform marketplace terpercaya yang menghubungkan produk daur ulang berkualitas tinggi dengan pasar global. Kami mendukung ekonomi sirkular untuk masa depan yang lebih hijau.</p>
            
            <div style="margin-top: 20px;">
                <a href="users/catalog.php" style="background:var(--accent-color); color:white; padding:10px 20px; border-radius:50px; text-decoration:none; font-weight:bold;">Mulai Belanja</a>
            </div>
        </div>
    </main>

    <div class="cards">
        <div class="card">
            <h2>Kualitas Ekspor</h2>
            <p>Produk kami telah melalui proses kurasi ketat untuk memenuhi standar pasar internasional.</p>
        </div>
        <div class="card">
            <h2>Ramah Lingkungan</h2>
            <p>Fokus pada produk hasil daur ulang (Recycled PET, Plastik, dll) untuk keberlanjutan bumi.</p>
        </div>
        <div class="card">
            <h2>Transaksi Aman</h2>
            <p>Sistem pembayaran dan pengiriman yang transparan serta terjamin keamanannya.</p>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-left">
                <h3>Form</h3>
                <p class="footer-sub">Let us know if you got any problem, question, or even suggestion!</p>

                <?php if ($form_status == 'success'): ?>
                    <div style="background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #c8e6c9;">
                        ✅ Pesan terkirim!
                    </div>
                <?php elseif ($form_status == 'error'): ?>
                    <div style="background:#ffebee; color:#c62828; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffcdd2;">
                        ❌ Gagal mengirim.
                    </div>
                <?php elseif ($form_status == 'empty'): ?>
                    <div style="background:#fff3e0; color:#ef6c00; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffe0b2;">
                        ⚠️ Isi data wajib!
                    </div>
                <?php endif; ?>

                <form class="footer-form" method="post" action="index.php#footer-area">
                    <div id="footer-area"></div>
                    <div class="row">
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name">
                    </div>
                    <div class="row">
                        <input type="email" name="email" placeholder="Mail" required>
                        <input type="text" name="phone" placeholder="Phone">
                    </div>
                    <textarea name="message" rows="5" placeholder="Message" required></textarea>
                    <button type="submit" name="submit_contact" class="footer-submit">SUBMIT</button>
                </form>
            </div>
            <div class="footer-right">
                <h3>Contact Information</h3>
                <p class="contact-line">Jl. Kaliurang Km 14,5, Sleman, Yogyakarta 55584</p>
                <p class="contact-line">Call Us : +62 81 334 61 00</p>
                <div class="socials">
                    <a href="#">facebook</a>
                    <a href="#">instagram</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>