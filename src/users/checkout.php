<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$uid = $_SESSION['user_id'];

// Ambil data keranjang
$query = "SELECT c.id as cart_id, c.quantity, c.product_id, p.name, p.price, p.image 
          FROM carts c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

// Redirect jika keranjang kosong
if (count($items) == 0) {
    header("Location: cart.php");
    exit;
}

// Hitung total
$grand_total = 0;
foreach ($items as $i) {
    $grand_total += ($i['price'] * $i['quantity']);
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - EximGo</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">


</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="checkout-container">
        <!-- Form Section -->
        <div class="checkout-form-section">
            <a href="cart.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
            </a>

            <form action="process_checkout.php" method="POST" id="checkoutForm">
                <!-- Data Pengiriman -->
                <div class="section-title">
                    <i class="fas fa-shipping-fast"></i>
                    Data Pengiriman
                </div>

                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="shipping_name" class="form-control"
                        value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon <span class="required">*</span></label>
                    <input type="tel" name="shipping_phone" class="form-control" placeholder="contoh: 08123456789"
                        required>
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap <span class="required">*</span></label>
                    <textarea name="shipping_address" class="form-control"
                        placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kota/Kabupaten <span class="required">*</span></label>
                        <input type="text" name="shipping_city" class="form-control"
                            placeholder="contoh: Jakarta Selatan" required>
                    </div>

                    <div class="form-group">
                        <label>Kode Pos <span class="required">*</span></label>
                        <input type="text" name="shipping_postal_code" class="form-control" placeholder="contoh: 12345"
                            maxlength="5" required>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fas fa-credit-card"></i>
                    Metode Pembayaran
                </div>

                <div class="payment-methods">
                    <div class="payment-option">
                        <input type="radio" name="payment_method" id="transfer" value="transfer_bank" required>
                        <label for="transfer">
                            <i class="fas fa-university"></i>
                            <div class="payment-info">
                                <strong>Transfer Bank</strong>
                                <small>BCA, Mandiri, BNI, BRI</small>
                            </div>
                        </label>
                    </div>

                    <div class="payment-option">
                        <input type="radio" name="payment_method" id="ewallet" value="e-wallet">
                        <label for="ewallet">
                            <i class="fas fa-wallet"></i>
                            <div class="payment-info">
                                <strong>E-Wallet</strong>
                                <small>GoPay, OVO, Dana, ShopeePay</small>
                            </div>
                        </label>
                    </div>

                    <div class="payment-option">
                        <input type="radio" name="payment_method" id="cod" value="cod">
                        <label for="cod">
                            <i class="fas fa-money-bill-wave"></i>
                            <div class="payment-info">
                                <strong>Cash on Delivery (COD)</strong>
                                <small>Bayar saat barang sampai</small>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-submit-order">
                    <i class="fas fa-lock"></i> Proses Pembayaran
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <div class="section-title">
                <i class="fas fa-receipt"></i>
                Ringkasan Pesanan
            </div>

            <div class="order-items">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <img src="../assets/images/uploads/<?= $item['image'] ?? 'default.jpg' ?>" alt="Product">
                        <div class="order-item-info">
                            <h4>
                                <?= htmlspecialchars($item['name']) ?>
                            </h4>
                            <div class="item-qty">
                                <?= $item['quantity'] ?> x Rp
                                <?= number_format($item['price']) ?>
                            </div>
                        </div>
                        <div class="order-item-price">
                            Rp
                            <?= number_format($item['price'] * $item['quantity']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-totals">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp
                        <?= number_format($grand_total) ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <span>Gratis</span>
                </div>
                <div class="summary-row grand-total">
                    <span>Total Pembayaran</span>
                    <span>Rp
                        <?= number_format($grand_total) ?>
                    </span>
                </div>
            </div>

            <div
                style="margin-top: 20px; padding: 15px; background: #f0f9f6; border-radius: 8px; font-size: 0.85rem; color: #2d7f68;">
                <i class="fas fa-info-circle"></i> Pesanan Anda akan diproses setelah pembayaran dikonfirmasi
            </div>
        </div>
    </div>

</body>

</html>