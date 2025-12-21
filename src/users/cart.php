<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$uid = $_SESSION['user_id'];

// --- LOGIC HAPUS ITEM (PENTING: KEMBALIKAN STOK) ---
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];

    // 1. Ambil info barang yang mau dihapus (kita butuh Product ID dan Quantity)
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM carts WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $uid]);
    $item = $stmt->fetch();

    if ($item) {
        // 2. KEMBALIKAN STOK KE DATABASE
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
            ->execute([$item['quantity'], $item['product_id']]);

        // 3. Hapus dari keranjang
        $pdo->prepare("DELETE FROM carts WHERE id = ?")->execute([$cart_id]);
    }

    header("Location: cart.php");
    exit;
}

// --- LOGIC CHECKOUT FINAL (STOK SUDAH AMAN, TINGGAL CATAT) ---
if (isset($_POST['checkout_all'])) {
    // Ambil item di keranjang
    $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = ?");
    $stmt->execute([$uid]);
    $cart_items = $stmt->fetchAll();

    if (count($cart_items) > 0) {
        $pdo->beginTransaction();
        try {
            foreach ($cart_items as $item) {
                // CATAT KE TRANSAKSI SAJA
                // (Tidak perlu kurangi stok lagi, karena sudah dikurangi saat tombol Add ditekan)
                $pdo->prepare("INSERT INTO transactions (user_id, product_id, quantity) VALUES (?, ?, ?)")
                    ->execute([$uid, $item['product_id'], $item['quantity']]);
            }

            // Kosongkan Keranjang
            $pdo->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$uid]);

            $pdo->commit();
            $success_msg = "Pembayaran berhasil! Barang segera dikirim.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    } else {
        $error_msg = "Keranjang Anda kosong.";
    }
}
// --- TAMPILKAN DATA KERANJANG ---
$query = "SELECT c.id as cart_id, c.quantity, p.name, p.price, p.image 
          FROM carts c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

// Hitung Grand Total
$grand_total = 0;
foreach ($items as $i) {
    $grand_total += ($i['price'] * $i['quantity']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">EximGo</div>
                <ul class="nav-links">
                    <li><a href="catalog.php">Shop</a></li>
                    <li><a href="profile.php">Akun</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top: 40px; padding-bottom: 80px;">

        <div class="page-header">
            <h2>Shopping Cart</h2>
            <p style="color:#888;">Kelola barang belanjaan Anda sebelum checkout.</p>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert-box alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <h3>Berhasil Checkout!</h3>
                <p><?= $success_msg ?></p>
                <a href="catalog.php" class="btn-secondary">Belanja Lagi</a>
            </div>
        <?php elseif (count($items) == 0): ?>
            <div class="alert-box alert-empty">
                <i class="fa-solid fa-basket-shopping" style="font-size:4rem; color:#ddd; margin-bottom:20px;"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p style="color:#888; margin-bottom: 20px;">Anda belum menambahkan produk apapun.</p>
                <a href="catalog.php" class="btn-secondary">Mulai Belanja</a>
            </div>
        <?php else: ?>

            <div class="cart-container">
                <div class="cart-list">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <img src="../assets/images/uploads/<?= $item['image'] ?? 'default.jpg' ?>" class="item-img"
                                alt="Produk">

                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <div class="item-meta">
                                    Harga Satuan: Rp <?= number_format($item['price']) ?> <br>
                                    Jumlah: <b><?= $item['quantity'] ?> pcs</b>
                                </div>
                            </div>

                            <div class="item-total">
                                <div class="price-tag">Rp <?= number_format($item['price'] * $item['quantity']) ?></div>
                                <a href="?remove=<?= $item['cart_id'] ?>" class="item-remove"
                                    onclick="return confirm('Yakin hapus barang ini?')">
                                    <i class="fa-regular fa-trash-can"></i> Hapus
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Ringkasan Belanja</h3>

                    <div class="summary-row">
                        <span>Total Barang</span>
                        <span><?= count($items) ?> Item</span>
                    </div>
                    <div class="summary-row">
                        <span>Biaya Pengiriman</span>
                        <span>Gratis</span>
                    </div>

                    <div class="summary-row total-row">
                        <span>Total Tagihan</span>
                        <span>Rp <?= number_format($grand_total) ?></span>
                    </div>

                    <form method="POST">
                        <button type="submit" name="checkout_all" class="btn-checkout"
                            onclick="return confirm('Proses pembayaran sekarang?')">
                            Checkout Sekarang <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>

                    <div style="margin-top: 20px; font-size: 0.8rem; color: #aaa; text-align: center;">
                        <i class="fa-solid fa-shield-halved"></i> Transaksi Aman & Terenkripsi
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>