<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// --- LOGIC TAMBAH KE KERANJANG (DENGAN PENGURANGAN STOK LANGSUNG) ---
if (isset($_POST['add_to_cart'])) {
    $pid = $_POST['product_id'];

    // Cek stok dulu sebelum eksekusi
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] > 0) {
        try {
            $pdo->beginTransaction(); // Mulai transaksi agar aman

            // 1. KURANGI STOK DI DATABASE LANGSUNG
            $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = ?")->execute([$pid]);

            // 2. CEK CART
            $check = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $check->execute([$uid, $pid]);
            $existing = $check->fetch();

            if ($existing) {
                // Update quantity di cart
                $pdo->prepare("UPDATE carts SET quantity = quantity + 1 WHERE id = ?")->execute([$existing['id']]);
            } else {
                // Insert baru ke cart
                $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$uid, $pid]);
            }

            $pdo->commit(); // Simpan perubahan
            $msg = "Produk masuk keranjang! Stok diamankan untuk Anda.";

        } catch (Exception $e) {
            $pdo->rollBack(); // Batalkan jika error
            $msg = "Gagal menambahkan ke keranjang.";
        }
    } else {
        // Handle jika user nakal mematikan tombol disabled via inspect element
        $msg = "Maaf, stok baru saja habis!";
    }
}

// Ambil data produk
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

// Hitung jumlah item di keranjang
$stmt_count = $pdo->prepare("SELECT SUM(quantity) FROM carts WHERE user_id = ?");
$stmt_count->execute([$uid]);
$cart_count = $stmt_count->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - EximGo</title>
    <link rel="stylesheet" href="../assets/css/catalog.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <nav>
        <div class="logo">Exim<span>Go</span> </div>
        <div class="nav-kanan">
            <ul class="nav-links">
                <li><a href="tentangKami.php">TENTANG KAMI</a></li>
                <li><a href="tentangPet.php">TENTANG PET</a></li>
                <li><a href="">BERITA</a></li>
                <li><a href="catalog.php" class="active">Shop</a></li>

                <li>
                    <a href="cart.php" class="cart-wrapper">
                        <i class="fa-solid fa-cart-shopping"></i> Keranjang
                        <?php if ($cart_count > 0): ?>
                            <span class="badge-cart"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li><a href="profile.php">Akun</a></li>
                <li><a href="../auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container" style="padding-top: 50px; padding-bottom: 80px;">
        <div class="catalog-header">
            <h2>Explore Collection</h2>
            <p style="color:var(--text-light); margin-top:5px;">Temukan produk ekspor terbaik pilihan kami.</p>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $msg; ?></span>
                <a href="cart.php" style="color:#1b5e20; font-weight:700; margin-left:auto;">Lihat Keranjang &rarr;</a>
            </div>
        <?php endif; ?>

        <div class="grid-products">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="../assets/images/uploads/<?= $p['image'] ?? 'default.jpg' ?>"
                            alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>

                    <div class="product-info">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <div class="stock-label">
                            <?php if ($p['stock'] > 10): ?>
                                <span style="color:var(--accent-color);"><i class="fa-solid fa-box"></i> Stok:
                                    <?= $p['stock'] ?></span>
                            <?php elseif ($p['stock'] > 0): ?>
                                <span style="color:#ff9800;"><i class="fa-solid fa-triangle-exclamation"></i> Sisa:
                                    <?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span style="color:#f44336;">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-footer">
                        <div class="price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>

                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <?php if ($p['stock'] > 0): ?>
                                <button type="submit" name="add_to_cart" class="btn-buy">
                                    <span>Add</span> <i class="fa-solid fa-plus" style="font-size:0.8rem;"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-buy" disabled>Habis</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>