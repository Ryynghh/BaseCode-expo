<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <nav>
        <div class="logo">Exim<span>Go</span> </div>
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
            <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Tenetur ut enim voluptatem non dolores
                obcaecati beatae placeat neque harum eius. Impedit ipsa culpa excepturi nam, vitae esse aut atque earum.
            </p>
        </div>
    </main>

    <div class="cards">
        <div class="card">
            <h2>Layanan 1</h2>
            <p>Deskripsi singkat tentang layanan atau fitur pertama yang ditawarkan.</p>
        </div>
        <div class="card">
            <h2>Layanan 2</h2>
            <p>Deskripsi singkat tentang layanan atau fitur kedua yang ditawarkan.</p>
        </div>
        <div class="card">
            <h2>Layanan 3</h2>
            <p>Deskripsi singkat tentang layanan atau fitur ketiga yang ditawarkan.</p>
        </div>
    </div>
</body>

</html>