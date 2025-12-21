<?php
require_once '../config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];

        // LOGIC PEMISAH ROLE
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../users/catalog.php");
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 style="margin-bottom: 20px;">Selamat Datang Kembali</h2>

            <?php if (isset($_GET['success'])): ?>
                <p style="color: var(--accent-color); margin-bottom: 15px;">Registrasi berhasil! Silakan login.</p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Masuk</button>
            </form>
            <p style="margin-top: 20px; font-size: 0.9rem;">
                Belum punya akun? <a href="register.php" style="color: var(--accent-color);">Daftar disini</a>
            </p>
            <p style="margin-top: 10px; font-size: 0.9rem;">
                <a href="../index.php">Kembali ke Home</a>
            </p>
        </div>
    </div>
</body>

</html>