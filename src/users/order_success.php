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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .success-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .success-container h1 {
            font-size: 2rem;
            color: #2d7f68;
            margin-bottom: 15px;
        }

        .success-container p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .order-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: left;
        }

        .order-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-detail-row:last-child {
            border-bottom: none;
            font-weight: 600;
            color: #2d7f68;
            font-size: 1.1rem;
        }

        .order-detail-row label {
            color: #666;
        }

        .order-detail-row span {
            color: #333;
            font-weight: 500;
        }

        .payment-info-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }

        .payment-info-box h4 {
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-info-box p {
            color: #856404;
            margin: 5px 0;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-action {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 127, 104, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 40px 20px;
                padding: 30px 20px;
            }

            .success-container h1 {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
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