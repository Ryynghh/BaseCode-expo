<?php
require_once '../config/database.php';
session_start();

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// 2. LOGIC TAMBAH KE KERANJANG
if (isset($_POST['add_to_cart'])) {
    $pid = $_POST['product_id'];

    // Cek stok
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] > 0) {
        try {
            $pdo->beginTransaction();

            // A. Kurangi Stok
            $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = ?")->execute([$pid]);

            // B. Cek apakah produk sudah ada di cart user ini
            $check = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $check->execute([$uid, $pid]);
            $existing = $check->fetch();

            if ($existing) {
                // Update quantity
                $pdo->prepare("UPDATE carts SET quantity = quantity + 1 WHERE id = ?")->execute([$existing['id']]);
            } else {
                // Insert baru
                $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$uid, $pid]);
            }

            $pdo->commit();
            $msg = "Produk masuk keranjang! Stok diamankan untuk Anda.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal menambahkan ke keranjang.";
        }
    } else {
        $msg = "Maaf, stok baru saja habis!";
    }
}

// 3. Ambil data produk
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

// CATATAN: Kode hitung cart dihapus disini karena sudah ditangani oleh navbar.php
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>

    <link rel="stylesheet" href="../assets/css/catalog.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/hero-banner.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"> -->
</head>

<body>


    <?php include '../includes/navbar.php'; ?>

    <main class="container">


        <div class="hero-banner">
            <div class="hero-ship-image" style="background-image: url('../assets/images/uploads/kapal.jpg');"></div>
            <div class="hero-content">
                <div class="subtitle">Trusted Export Partner</div>
                <h1>Premium Quality Products</h1>
                <p>Kami menyediakan produk ekspor berkualitas tinggi dengan jaminan keamanan pengiriman internasional
                    melalui partner logistik terpercaya.</p>
                <a href="#products" class="hero-button">Lihat Koleksi</a>
            </div>
            <div class="hero-curve"></div>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $msg; ?></span>
                <a href="cart.php" style="color:#1b5e20; font-weight:700; margin-left:auto;">Lihat Keranjang &rarr;</a>
            </div>
        <?php endif; ?>

        <div id="products" class="catalog-header" style="margin-top: 40px;">
            <h2>Koleksi Terbaru</h2>
        </div>

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
                                <span style="color:var(--accent-color);">
                                    <i class="fa-solid fa-box"></i> Stok: <?= $p['stock'] ?>
                                </span>
                            <?php elseif ($p['stock'] > 0): ?>
                                <span style="color:#ff9800;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Sisa: <?= $p['stock'] ?>
                                </span>
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
                                <button type="button" class="btn-buy" disabled style="background:#ccc;">Habis</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>