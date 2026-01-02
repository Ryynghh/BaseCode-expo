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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        .checkout-form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #2d7f68;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .form-group label .required {
            color: #d32f2f;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #2d7f68;
            box-shadow: 0 0 0 3px rgba(45, 127, 104, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .payment-methods {
            display: grid;
            gap: 12px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-option label {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .payment-option input[type="radio"]:checked+label {
            border-color: #2d7f68;
            background: #f0f9f6;
        }

        .payment-option label i {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #2d7f68;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info strong {
            display: block;
            font-size: 1rem;
            color: #333;
            margin-bottom: 3px;
        }

        .payment-info small {
            color: #888;
            font-size: 0.85rem;
        }

        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-item:first-child {
            padding-top: 0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-info h4 {
            font-size: 0.95rem;
            margin: 0 0 5px 0;
            color: #333;
        }

        .order-item-info .item-qty {
            font-size: 0.85rem;
            color: #888;
        }

        .order-item-price {
            font-weight: 600;
            color: #2d7f68;
        }

        .summary-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .summary-row.grand-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d7f68;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #e0e0e0;
        }

        .btn-submit-order {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        .btn-submit-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 127, 104, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2d7f68;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .back-link:hover {
            gap: 12px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
                order: -1;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .checkout-container {
                padding: 20px 15px;
            }

            .checkout-form-section,
            .order-summary {
                padding: 20px;
            }

            .section-title {
                font-size: 1.1rem;
            }
        }
    </style>
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