<?php
// 1. LOGIKA DATABASE (Wajib Paling Atas)
require_once '../config/database.php';
session_start();

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$uid = $_SESSION['user_id'];

// --- LOGIKA FORMULIR FOOTER ---
$form_status = "";
if (isset($_POST['submit_contact'])) {
    $fname = htmlspecialchars($_POST['first_name'] ?? '');
    $lname = htmlspecialchars($_POST['last_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $msg   = htmlspecialchars($_POST['message'] ?? '');

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

// --- LOGIKA KATALOG (Add to Cart) ---
if (isset($_POST['add_to_cart'])) {
    $pid = $_POST['product_id'];
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = ?")->execute([$pid]);
            
            $check = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $check->execute([$uid, $pid]);
            $existing = $check->fetch();

            if ($existing) {
                $pdo->prepare("UPDATE carts SET quantity = quantity + 1 WHERE id = ?")->execute([$existing['id']]);
            } else {
                $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$uid, $pid]);
            }
            $pdo->commit();
            $msg = "Produk masuk keranjang!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal menambahkan.";
        }
    } else {
        $msg = "Stok habis!";
    }
}

// Data Produk & Cart Count
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
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
    <link rel="stylesheet" href="../assets/css/hero-banner.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container" style="padding-top: 50px; padding-bottom: 80px;">
        

        <div class="catalog-header" id="products">
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
                        <img src="../assets/images/uploads/<?= $p['image'] ?? 'default.jpg' ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <div class="stock-label">Stok: <?= $p['stock'] ?></div>
                    </div>
                    <div class="product-footer">
                        <div class="price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <?php if ($p['stock'] > 0): ?>
                                <button type="submit" name="add_to_cart" class="btn-buy">Add <i class="fa-solid fa-plus"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn-buy" disabled>Habis</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-left">
                <h3>Form</h3>
                <p class="footer-sub">Let us know if you got any problem, question, or even suggestion!</p>

                <?php if ($form_status == 'success'): ?>
                    <div style="background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #c8e6c9;">✅ Pesan terkirim!</div>
                <?php elseif ($form_status == 'error'): ?>
                    <div style="background:#ffebee; color:#c62828; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffcdd2;">❌ Gagal mengirim.</div>
                <?php elseif ($form_status == 'empty'): ?>
                    <div style="background:#fff3e0; color:#ef6c00; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffe0b2;">⚠️ Isi data wajib!</div>
                <?php endif; ?>

                <form class="footer-form" method="post" action="#footer-area">
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