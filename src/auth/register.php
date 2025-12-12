<?php
require_once '../config/database.php';
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi sederhana
    if (!empty($name) && !empty($email) && !empty($password)) {

        // Cek apakah email sudah ada
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "Email sudah terdaftar!";
        } else {
            // Hashing Password (Security Best Practice)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert Data
            $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$name, $email, $hashed_password])) {
                header("Location: login.php?success=registered");
                exit;
            } else {
                $message = "Terjadi kesalahan saat mendaftar.";
            }
        }
    } else {
        $message = "Semua kolom wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - ImpEx Global</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 style="margin-bottom: 20px;">Buat Akun Baru</h2>

            <?php if ($message): ?>
                <p style="color: red; margin-bottom: 15px;"><?= $message; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Daftar Sekarang</button>
            </form>
            <p style="margin-top: 20px; font-size: 0.9rem;">
                Sudah punya akun? <a href="login.php" style="color: var(--accent-color);">Login disini</a>
            </p>
            <p style="margin-top: 10px; font-size: 0.9rem;">
                <a href="../index.php">Kembali ke Home</a>
            </p>
        </div>
    </div>
</body>

</html>