<?php
require_once '../config/database.php';
session_start();
$uid = $_SESSION['user_id'];

// LOGIC HAPUS AKUN
if (isset($_POST['delete_account'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// LOGIC UPDATE AKUN
if (isset($_POST['update_profile'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pdo->prepare("UPDATE users SET full_name=?, email=? WHERE id=?")->execute([$name, $email, $uid]);
    $_SESSION['name'] = $name; // Update session
    $msg = "Profil berhasil diupdate.";
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Profil Saya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">EximGo</div>
                <ul class="nav-links">
                    <li><a href="catalog.php">Kembali ke Produk</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 50px; max-width: 500px;">
        <div class="auth-card">
            <h2>Pengaturan Akun</h2>
            <?php if (isset($msg))
                echo "<p style='color:green'>$msg</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn" style="width:100%">Simpan Perubahan</button>
            </form>

            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

            <div style="text-align: center;">
                <h4 style="color: red;">Zona Bahaya</h4>
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Menghapus akun akan menghilangkan semua
                    data history Anda.</p>
                <form method="POST">
                    <button type="submit" name="delete_account" class="btn delete-btn"
                        onclick="return confirm('Yakin ingin menghapus akun permanen?')">Hapus Akun Saya</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>