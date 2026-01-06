<?php
session_start();

if (!isset($_SESSION['order_number'])) {
    header("Location: catalog.php");
    exit;
}

$order_number = $_SESSION['order_number'];
$order_total = $_SESSION['order_total'];
$payment_method = $_SESSION['payment_method'];

// Clear session setelah ditampilkan
unset($_SESSION['order_number']);
unset($_SESSION['order_total']);
unset($_SESSION['payment_method']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - EximGo</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../assets/css/order_success.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">


</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1>Pesanan Berhasil Dibuat!</h1>
        <p>Terima kasih telah berbelanja di EximGo. Pesanan Anda sedang dalam proses.</p>

        <div class="order-details">
            <div class="order-detail-row">
                <label>Nomor Pesanan</label>
                <span>
                    <?= htmlspecialchars($order_number) ?>
                </span>
            </div>
            <div class="order-detail-row">
                <label>Metode Pembayaran</label>
                <span>
                    <?php
                    $methods = [
                        'transfer_bank' => 'Transfer Bank',
                        'e-wallet' => 'E-Wallet',
                        'cod' => 'Cash on Delivery'
                    ];
                    echo $methods[$payment_method] ?? $payment_method;
                    ?>
                </span>
            </div>
            <div class="order-detail-row">
                <label>Total Pembayaran</label>
                <span>Rp
                    <?= number_format($order_total) ?>
                </span>
            </div>
        </div>

        <?php if ($payment_method === 'transfer_bank'): ?>
            <div class="payment-info-box">
                <h4><i class="fas fa-info-circle"></i> Instruksi Pembayaran</h4>
                <p><strong>Silakan transfer ke rekening berikut:</strong></p>
                <p>Bank BCA: 1234567890 a.n. PT EximGo Indonesia</p>
                <p>Bank Mandiri: 0987654321 a.n. PT EximGo Indonesia</p>
                <p>Setelah transfer, konfirmasi pembayaran melalui WhatsApp atau email kami.</p>
            </div>
        <?php elseif ($payment_method === 'e-wallet'): ?>
            <div class="payment-info-box">
                <h4><i class="fas fa-info-circle"></i> Instruksi Pembayaran</h4>
                <p><strong>Silakan transfer ke:</strong></p>
                <p>GoPay/OVO/Dana: 081234567890</p>
                <p>Konfirmasi pembayaran melalui WhatsApp atau email.</p>
            </div>
        <?php elseif ($payment_method === 'cod'): ?>
            <div class="payment-info-box">
                <h4><i class="fas fa-info-circle"></i> Informasi Pengiriman</h4>
                <p>Pesanan Anda akan segera diproses dan dikirim.</p>
                <p>Pembayaran dilakukan saat barang sampai di tujuan.</p>
                <p>Estimasi pengiriman: 2-3 hari kerja.</p>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="catalog.php" class="btn-action btn-secondary">
                <i class="fas fa-shopping-bag"></i> Belanja Lagi
            </a>
            <a href="my_orders.php" class="btn-action btn-primary">
                <i class="fas fa-receipt"></i> Lihat Pesanan
            </a>
        </div>
    </div>

</body>

</html>