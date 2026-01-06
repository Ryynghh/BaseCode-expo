<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo "<p>Data tidak ditemukan.</p>";
    exit;
}

$order_id = $_GET['id'];
$uid = $_SESSION['user_id'];

// 1. Ambil data order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $uid]);
$order = $stmt->fetch();

if (!$order) {
    echo "<p>Pesanan tidak ditemukan.</p>";
    exit;
}

// 2. PERBAIKAN QUERY: JOIN ke PRODUCTS untuk ambil gambar
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
    .info-box { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
    .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #666; font-size: 0.9rem; }
    .info-value { color: #333; font-weight: 500; font-size: 0.9rem; text-align: right; }
    .items-list { display: flex; flex-direction: column; gap: 12px; }
    .item-row { display: flex; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px; }
    .item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .item-details { flex: 1; }
    .item-details h4 { font-size: 0.95rem; color: #333; margin-bottom: 5px; margin-top: 0; }
    .item-details .item-price { color: #666; font-size: 0.85rem; }
    .item-subtotal { font-weight: 600; color: #2d7f68; align-self: center; }
    .total-section { background: #f0f9f6; padding: 15px; border-radius: 10px; margin-top: 20px; }
    .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
    .total-row.grand { font-size: 1.2rem; font-weight: 700; color: #2d7f68; padding-top: 12px; border-top: 2px dashed #2d7f68; }
    .status-timeline { display: flex; flex-direction: column; gap: 15px; padding-left: 20px; border-left: 3px solid #2d7f68; margin-left: 10px; }
    .timeline-item { position: relative; padding-left: 30px; }
    .timeline-item::before { content: ''; position: absolute; left: -26px; top: 5px; width: 15px; height: 15px; border-radius: 50%; background: #2d7f68; }
    .timeline-item.inactive::before { background: #ddd; }
    .timeline-label { font-weight: 600; color: #333; margin-bottom: 3px; }
    .timeline-date { color: #888; font-size: 0.85rem; }
</style>

<div class="detail-section">
    <h3><i class="fas fa-info-circle"></i> Informasi Pesanan</h3>
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nomor Pesanan</span>
            <span class="info-value"><?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Pesanan</span>
            <span class="info-value"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value">
                <span class="status-badge status-<?= $order['status'] ?>">
                    <?= $status_labels[$order['status']] ?? $order['status'] ?>
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Metode Pembayaran</span>
            <span class="info-value">
                <?= $payment_methods[$order['payment_method']] ?? $order['payment_method'] ?>
            </span>
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
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nama Penerima</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Nomor Telepon</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_phone']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Alamat</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_address']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kota</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_city']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kode Pos</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_postal_code']) ?></span>
        </div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-list-check"></i> Status Pesanan</h3>
    <div class="status-timeline">
        <div class="timeline-item">
            <div class="timeline-label">Pesanan Dibuat</div>
            <div class="timeline-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
        </div>
        <div class="timeline-item <?= in_array($order['status'], ['pending']) ? 'inactive' : '' ?>">
            <div class="timeline-label">Pembayaran Dikonfirmasi</div>
            <div class="timeline-date">
                <?= in_array($order['status'], ['pending']) ? 'Menunggu konfirmasi' : 'Sudah dikonfirmasi' ?>
            </div>
        </div>
        <div class="timeline-item <?= in_array($order['status'], ['pending', 'processing']) ? 'inactive' : '' ?>">
            <div class="timeline-label">Pesanan Dikirim</div>
            <div class="timeline-date">
                <?= in_array($order['status'], ['pending', 'processing']) ? 'Belum dikirim' : 'Dalam pengiriman' ?>
            </div>
        </div>
        <div class="timeline-item <?= $order['status'] !== 'completed' ? 'inactive' : '' ?>">
            <div class="timeline-label">Pesanan Selesai</div>
            <div class="timeline-date">
                <?= $order['status'] === 'completed' ? 'Pesanan telah diterima' : 'Belum selesai' ?>
            </div>
        </div>
    </div>
</div>

<?php if ($order['payment_method'] === 'transfer_bank' && $order['status'] === 'pending'): ?>
    <div class="detail-section" style="background: #fff3cd; padding: 15px; border-radius: 10px;">
        <h3 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Instruksi Pembayaran</h3>
        <p style="color: #856404; margin: 10px 0;">Silakan transfer ke rekening berikut:</p>
        <div style="background: white; padding: 15px; border-radius: 8px; margin: 10px 0;">
            <strong>Bank BCA:</strong> 1234567890 a.n. PT EximGo Indonesia<br>
            <strong>Bank Mandiri:</strong> 0987654321 a.n. PT EximGo Indonesia
        </div>
        <p style="color: #856404; font-size: 0.9rem; margin-top: 10px;">
            Setelah transfer, konfirmasi pembayaran melalui WhatsApp atau email kami.
        </p>
    </div>
<?php endif; ?>

<?php if ($order['payment_method'] === 'e-wallet' && $order['status'] === 'pending'): ?>
    <div class="detail-section" style="background: #fff3cd; padding: 15px; border-radius: 10px;">
        <h3 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Instruksi Pembayaran</h3>
        <p style="color: #856404; margin: 10px 0;">Silakan transfer ke:</p>
        <div style="background: white; padding: 15px; border-radius: 8px; margin: 10px 0;">
            <strong>GoPay/OVO/Dana:</strong> 081234567890
        </div>
        <p style="color: #856404; font-size: 0.9rem; margin-top: 10px;">
            Konfirmasi pembayaran melalui WhatsApp atau email.
        </p>
    </div>
<?php endif; ?>