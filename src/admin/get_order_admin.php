<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    echo "<p>Data tidak ditemukan.</p>";
    exit;
}

$order_id = $_GET['id'];

// 1. Ambil data order dengan user info
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.email, u.id as user_id
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<p>Order tidak ditemukan.</p>";
    exit;
}

// 2. PERBAIKAN QUERY ITEM (JOIN KE PRODUCTS UNTUK AMBIL GAMBAR)
// Kita ambil oi.* (data item), p.image (gambar), dan p.name (nama produk asli)
$stmt = $pdo->prepare("
    SELECT oi.*, p.image, p.name as product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$status_labels = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$payment_methods = [
    'transfer_bank' => 'Transfer Bank',
    'e-wallet' => 'E-Wallet',
    'cod' => 'Cash on Delivery'
];
?>

<style>
    .detail-section { margin-bottom: 25px; }
    .detail-section h3 { font-size: 1.1rem; color: #2d7f68; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: #f8f9fa; padding: 15px; border-radius: 10px; }
    .info-item { display: flex; flex-direction: column; gap: 5px; }
    .info-label { color: #666; font-size: 0.85rem; }
    .info-value { color: #333; font-weight: 500; font-size: 0.95rem; }
    .items-list { display: flex; flex-direction: column; gap: 12px; }
    .item-row { display: flex; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px; }
    .item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .item-details { flex: 1; }
    .item-details h4 { font-size: 0.95rem; color: #333; margin-bottom: 5px; }
    .item-price { color: #666; font-size: 0.85rem; }
    .item-subtotal { font-weight: 600; color: #2d7f68; align-self: center; }
    .total-section { background: #f0f9f6; padding: 15px; border-radius: 10px; margin-top: 15px; }
    .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
    .total-row.grand { font-size: 1.2rem; font-weight: 700; color: #2d7f68; padding-top: 12px; border-top: 2px dashed #2d7f68; }
    .user-card { background: #e3f2fd; padding: 15px; border-radius: 10px; display: flex; align-items: center; gap: 15px; }
    .user-avatar-large { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #2d7f68, #1cd44d); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.5rem; }
    .user-info-detail h4 { margin: 0 0 5px 0; color: #1976d2; }
    .user-info-detail p { margin: 2px 0; color: #666; font-size: 0.9rem; }
    @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
</style>

<div class="detail-section">
    <h3><i class="fas fa-user"></i> Informasi Customer</h3>
    <div class="user-card">
        <div class="user-avatar-large">
            <?= strtoupper(substr($order['full_name'], 0, 2)) ?>
        </div>
        <div class="user-info-detail">
            <h4><?= htmlspecialchars($order['full_name']) ?></h4>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($order['email']) ?></p>
            <p><i class="fas fa-id-badge"></i> User ID: #<?= $order['user_id'] ?></p>
        </div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-info-circle"></i> Informasi Order</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nomor Order</span>
            <span class="info-value"><?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Order</span>
            <span class="info-value"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value">
                <span class="status-badge status-<?= $order['status'] ?>">
                    <?= $status_labels[$order['status']] ?? $order['status'] ?>
                </span>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Metode Pembayaran</span>
            <span class="info-value"><?= $payment_methods[$order['payment_method']] ?? $order['payment_method'] ?></span>
        </div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-box"></i> Produk yang Dipesan</h3>
    <div class="items-list">
        <?php foreach ($items as $item): ?>
            <div class="item-row">
                <img src="../assets/images/uploads/<?= !empty($item['image']) ? $item['image'] : 'default.jpg' ?>" 
                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                     onerror="this.src='../assets/images/uploads/default.jpg'">
                
                <div class="item-details">
                    <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                    <div class="item-price">
                        <?= $item['quantity'] ?> x Rp <?= number_format($item['price']) ?>
                    </div>
                </div>
                <div class="item-subtotal">
                    Rp <?= number_format($item['subtotal'] ?? ($item['quantity'] * $item['price'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp <?= number_format($order['total_amount']) ?></span>
        </div>
        <div class="total-row">
            <span>Biaya Pengiriman</span>
            <span>Gratis</span>
        </div>
        <div class="total-row grand">
            <span>Total Pembayaran</span>
            <span>Rp <?= number_format($order['total_amount']) ?></span>
        </div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-truck"></i> Informasi Pengiriman</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nama Penerima</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_name']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Nomor Telepon</span>
            <span class="info-value">
                <a href="tel:<?= $order['shipping_phone'] ?>" style="color: #2d7f68; text-decoration: none;">
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($order['shipping_phone']) ?>
                </a>
            </span>
        </div>
        <div class="info-item" style="grid-column: 1 / -1;">
            <span class="info-label">Alamat Lengkap</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_address']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Kota</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_city']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Kode Pos</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_postal_code']) ?></span>
        </div>
    </div>
</div>

<?php if ($order['status'] === 'pending'): ?>
    <div class="detail-section" style="background: #fff3cd; padding: 15px; border-radius: 10px;">
        <h3 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Status: Menunggu Pembayaran</h3>
        <p style="color: #856404; margin: 10px 0; font-size: 0.9rem;">
            Customer belum melakukan pembayaran. Setelah customer melakukan transfer/pembayaran,
            Anda dapat mengubah status menjadi "Diproses".
        </p>
    </div>
<?php endif; ?>

<?php if ($order['status'] === 'processing'): ?>
    <div class="detail-section" style="background: #cfe2ff; padding: 15px; border-radius: 10px;">
        <h3 style="color: #084298;"><i class="fas fa-box"></i> Status: Sedang Diproses</h3>
        <p style="color: #084298; margin: 10px 0; font-size: 0.9rem;">
            Order sedang dikemas. Setelah barang siap dikirim, ubah status menjadi "Dikirim".
        </p>
    </div>
<?php endif; ?>

<?php if ($order['status'] === 'shipped'): ?>
    <div class="detail-section" style="background: #d1ecf1; padding: 15px; border-radius: 10px;">
        <h3 style="color: #0c5460;"><i class="fas fa-shipping-fast"></i> Status: Dalam Pengiriman</h3>
        <p style="color: #0c5460; margin: 10px 0; font-size: 0.9rem;">
            Barang sedang dalam perjalanan ke customer. Setelah customer menerima barang,
            ubah status menjadi "Selesai".
        </p>
    </div>
<?php endif; ?>

<?php if ($order['status'] === 'completed'): ?>
    <div class="detail-section" style="background: #d4edda; padding: 15px; border-radius: 10px;">
        <h3 style="color: #155724;"><i class="fas fa-check-circle"></i> Status: Selesai</h3>
        <p style="color: #155724; margin: 10px 0; font-size: 0.9rem;">
            Order telah selesai. Customer telah menerima barang.
        </p>
    </div>
<?php endif; ?>