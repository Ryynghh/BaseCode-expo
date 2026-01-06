<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Ambil semua pesanan user
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$uid]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - EximGo</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .orders-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px 80px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 2rem; color: #2d7f68; margin-bottom: 10px; }
        .page-header p { color: #888; font-size: 0.95rem; }
        .tabs-container { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tab-btn { padding: 12px 20px; background: none; border: none; color: #666; font-weight: 500; cursor: pointer; transition: 0.3s; white-space: nowrap; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #2d7f68; border-bottom-color: #2d7f68; }
        .tab-btn:hover { color: #2d7f68; }
        .orders-list { display: flex; flex-direction: column; gap: 20px; }
        .order-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); transition: 0.3s; }
        .order-card:hover { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12); transform: translateY(-2px); }
        .order-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; }
        .order-info h3 { font-size: 1.1rem; color: #333; margin-bottom: 5px; }
        .order-date { color: #888; font-size: 0.85rem; }
        .order-date i { margin-right: 5px; }
        .status-badge { padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .order-body { display: grid; gap: 20px; }
        .order-items-preview { display: flex; gap: 15px; flex-wrap: wrap; }
        .item-preview { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 10px; flex: 1; min-width: 250px; }
        .item-preview img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .item-info h4 { font-size: 0.95rem; color: #333; margin-bottom: 3px; }
        .item-qty { font-size: 0.85rem; color: #666; }
        .order-details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-group { background: #f8f9fa; padding: 15px; border-radius: 10px; }
        .detail-group h4 { font-size: 0.9rem; color: #2d7f68; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .detail-item:last-child { margin-bottom: 0; }
        .detail-label { color: #666; }
        .detail-value { color: #333; font-weight: 500; }
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0; }
        .order-total { font-size: 1.2rem; font-weight: 700; color: #2d7f68; }
        .order-actions { display: flex; gap: 10px; }
        .btn-action { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 0.9rem; }
        .btn-detail { background: #e3f2fd; color: #1976d2; }
        .btn-detail:hover { background: #bbdefb; }
        .btn-reorder { background: linear-gradient(135deg, #2d7f68, #1cd44d); color: white; }
        .btn-reorder:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(45, 127, 104, 0.3); }
        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-state i { font-size: 5rem; color: #ddd; margin-bottom: 20px; }
        .empty-state h3 { font-size: 1.5rem; color: #666; margin-bottom: 10px; }
        .empty-state p { color: #888; margin-bottom: 30px; }
        .btn-shop { padding: 15px 30px; background: linear-gradient(135deg, #2d7f68, #1cd44d); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; }
        .btn-shop:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(45, 127, 104, 0.3); }
        /* Modal Detail */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; overflow-y: auto; }
        .modal.active { display: flex; align-items: center; justify-content: center; padding: 20px; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .modal-header h2 { font-size: 1.5rem; color: #2d7f68; }
        .btn-close { width: 35px; height: 35px; border-radius: 50%; border: none; background: #f0f0f0; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .btn-close:hover { background: #e0e0e0; }
        .modal-body .detail-section { margin-bottom: 25px; }
        .modal-body .detail-section h3 { font-size: 1.1rem; color: #2d7f68; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .modal-body .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .modal-body .detail-row:last-child { border-bottom: none; }
        /* Responsive */
        @media (max-width: 992px) { .order-details { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .orders-container { padding: 20px 15px 60px; } .page-header h1 { font-size: 1.5rem; } .order-card { padding: 20px; } .order-header { flex-direction: column; align-items: flex-start; gap: 10px; } .order-items-preview { flex-direction: column; } .item-preview { min-width: 100%; } .order-footer { flex-direction: column; gap: 15px; align-items: flex-start; } .order-actions { width: 100%; flex-direction: column; } .btn-action { width: 100%; justify-content: center; } .modal-content { padding: 20px; } }
        @media (max-width: 480px) { .tabs-container { gap: 5px; } .tab-btn { padding: 10px 15px; font-size: 0.9rem; } }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="orders-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Pesanan Saya</h1>
            <p>Kelola dan lacak pesanan Anda di sini</p>
        </div>

        <div class="tabs-container">
            <button class="tab-btn active" onclick="filterOrders('all')">
                Semua Pesanan (<?= count($orders) ?>)
            </button>
            <button class="tab-btn" onclick="filterOrders('pending')">Menunggu Pembayaran</button>
            <button class="tab-btn" onclick="filterOrders('processing')">Diproses</button>
            <button class="tab-btn" onclick="filterOrders('shipped')">Dikirim</button>
            <button class="tab-btn" onclick="filterOrders('completed')">Selesai</button>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Anda belum pernah melakukan pemesanan</p>
                <a href="catalog.php" class="btn-shop">
                    <i class="fas fa-shopping-cart"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list" id="ordersList">
                <?php foreach ($orders as $order):
                    // PERBAIKAN DI SINI:
                    // 1. JOIN ke tabel products untuk ambil kolom 'image'
                    // 2. Select p.name agar kita punya nama produk yang benar
                    $stmt = $pdo->prepare("
                        SELECT oi.*, p.image, p.name as product_name
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ? 
                        LIMIT 3
                    ");
                    $stmt->execute([$order['id']]);
                    $items = $stmt->fetchAll();
                    ?>
                    <div class="order-card" data-status="<?= $order['status'] ?>">
                        <div class="order-header">
                            <div class="order-info">
                                <h3><?= htmlspecialchars($order['order_number']) ?></h3>
                                <div class="order-date">
                                    <i class="far fa-calendar"></i>
                                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?php
                                $status_labels = [
                                    'pending' => 'Menunggu Pembayaran',
                                    'processing' => 'Diproses',
                                    'shipped' => 'Dikirim',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                echo $status_labels[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </div>

                        <div class="order-body">
                            <div class="order-items-preview">
                                <?php foreach ($items as $item): ?>
                                    <div class="item-preview">
                                        <img src="../assets/images/uploads/<?= !empty($item['image']) ? $item['image'] : 'default.jpg' ?>"
                                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                                             onerror="this.src='../assets/images/uploads/default.jpg'">
                                        
                                        <div class="item-info">
                                            <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                            <div class="item-qty">
                                                <?= $item['quantity'] ?> x Rp <?= number_format($item['price']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($order['item_count'] > 3): ?>
                                    <div class="item-preview" style="justify-content: center; color: #666;">
                                        + <?= $order['item_count'] - 3 ?> produk lainnya
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="order-details">
                                <div class="detail-group">
                                    <h4><i class="fas fa-shipping-fast"></i> Pengiriman</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">Nama:</span>
                                        <span class="detail-value"><?= htmlspecialchars($order['shipping_name']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Telepon:</span>
                                        <span class="detail-value"><?= htmlspecialchars($order['shipping_phone']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Kota:</span>
                                        <span class="detail-value"><?= htmlspecialchars($order['shipping_city']) ?></span>
                                    </div>
                                </div>

                                <div class="detail-group">
                                    <h4><i class="fas fa-credit-card"></i> Pembayaran</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">Metode:</span>
                                        <span class="detail-value">
                                            <?php
                                            $methods = [
                                                'transfer_bank' => 'Transfer Bank',
                                                'e-wallet' => 'E-Wallet',
                                                'cod' => 'COD'
                                            ];
                                            echo $methods[$order['payment_method']] ?? $order['payment_method'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Total Item:</span>
                                        <span class="detail-value"><?= $order['item_count'] ?> item</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">
                                Total: Rp <?= number_format($order['total_amount']) ?>
                            </div>
                            <div class="order-actions">
                                <button class="btn-action btn-detail" onclick="showOrderDetail(<?= $order['id'] ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <a href="catalog.php" class="btn-action btn-reorder">
                                        <i class="fas fa-redo"></i> Pesan Lagi
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Pesanan</h2>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                </div>
        </div>
    </div>

    <script>
        // Filter Orders by Status
        function filterOrders(status) {
            const cards = document.querySelectorAll('.order-card');
            const tabs = document.querySelectorAll('.tab-btn');

            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Show Order Detail Modal
        function showOrderDetail(orderId) {
            const modal = document.getElementById('orderModal');
            const modalBody = document.getElementById('modalBody');

            // Fetch order detail via AJAX
            fetch(`get_order_detail.php?id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    modal.classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<p>Terjadi kesalahan saat memuat data.</p>';
                });
        }

        // Close Modal
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>