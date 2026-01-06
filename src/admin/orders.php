<?php
require_once '../config/database.php';
session_start();

// 1. Cek Security (Wajib Login & Role Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Logic Update Status Order
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $msg = "Status order berhasil diupdate!";
    } catch (Exception $e) {
        $error = "Gagal update status: " . $e->getMessage();
    }
}

// 3. Logic Hapus Order
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Ambil data order untuk kembalikan stok
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        // Kembalikan stok produk
        foreach ($items as $item) {
            $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
                ->execute([$item['quantity'], $item['product_id']]);
        }

        // Hapus order (order_items akan terhapus otomatis karena CASCADE)
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Order berhasil dihapus dan stok dikembalikan.";
    } catch (Exception $e) {
        $error = "Gagal menghapus order: " . $e->getMessage();
    }
}

// 4. Ambil Semua Data Order dengan info user (Terbaru di atas)
$query = "SELECT o.*, u.full_name as user_name, u.email as user_email,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";
$orders = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Order - EximGo Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS Khusus untuk Halaman Orders */
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .custom-table th {
            text-align: left;
            padding: 15px;
            background-color: #f8f9fa;
            color: var(--text-dark);
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        .custom-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: var(--text-dark);
            vertical-align: middle;
        }

        .custom-table tr:hover {
            background-color: #fafffb;
        }

        .order-number {
            font-weight: 600;
            color: #2d7f68;
            display: block;
            margin-bottom: 3px;
        }

        .order-date {
            color: #888;
            font-size: 0.85rem;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .customer-details strong {
            display: block;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .customer-details small {
            color: #888;
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
            border: none;
        }

        .btn-view {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-view:hover {
            background: #bbdefb;
        }

        .btn-edit {
            background: #fff3cd;
            color: #856404;
        }

        .btn-edit:hover {
            background: #ffe69c;
        }

        .btn-delete {
            background: #ffebee;
            color: #d32f2f;
        }

        .btn-delete:hover {
            background: #ffcdd2;
        }

        .alert-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }

        .stat-box h4 {
            font-size: 2rem;
            margin: 0;
            font-weight: 700;
        }

        .stat-box p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: #2d7f68;
        }

        .btn-close {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: #f0f0f0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-section {
            margin-bottom: 25px;
        }

        .detail-section h3 {
            font-size: 1.1rem;
            color: #2d7f68;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            color: #666;
            font-size: 0.85rem;
        }

        .info-value {
            color: #333;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .items-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .item-row {
            display: flex;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .item-row img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h4 {
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .btn-submit {
            padding: 12px 25px;
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .table-card {
                padding: 15px;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .custom-table {
                min-width: 900px;
                font-size: 0.85rem;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar Overlay untuk Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-container">

        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="brand-text">
                    EximGo <span>Admin</span>
                </div>
            </div>

            <nav class="side-nav">
                <ul>
                    <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../admin/products.php"><i class="fa-solid fa-tags"></i> Manajemen Produk</a></li>
                    <li><a href="../admin/member.php"><i class="fas fa-users"></i> Manajemen Member</a></li>
                    <li class="active"><a href="../admin/orders.php"><i class="fas fa-shopping-cart"></i> Manajemen
                            Order</a></li>
                    <li><a href="../admin/form.php"><i class="fa-solid fa-envelope"></i> Manajemen Form</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="left-icons" style="display: flex; align-items: center; gap: 15px;">
                    <!-- Hamburger Menu -->
                    <div class="hamburger-menu" id="hamburgerMenu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <h3 style="margin:0;">Manajemen Order</h3>
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../auth/logout.php">Logout</a>
                </div>
            </header>

            <div class="content-wrapper">

                <div class="section-header">
                    <h2>Daftar Semua Order</h2>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">
                        Total: <b>
                            <?= count($orders) ?>
                        </b> order
                    </div>
                </div>

                <?php if (isset($msg)): ?>
                    <div class="alert-box success"><i class="fas fa-check-circle"></i>
                        <?= $msg ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert-box error"><i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Row -->
                <div class="stats-row">
                    <?php
                    $total_pending = 0;
                    $total_processing = 0;
                    $total_completed = 0;
                    $total_revenue = 0;

                    foreach ($orders as $o) {
                        if ($o['status'] === 'pending')
                            $total_pending++;
                        if ($o['status'] === 'processing')
                            $total_processing++;
                        if ($o['status'] === 'completed')
                            $total_completed++;
                        if ($o['status'] === 'completed')
                            $total_revenue += $o['total_amount'];
                    }
                    ?>
                    <div class="stat-box">
                        <h4>
                            <?= $total_pending ?>
                        </h4>
                        <p><i class="fas fa-clock"></i> Menunggu Pembayaran</p>
                    </div>
                    <div class="stat-box">
                        <h4>
                            <?= $total_processing ?>
                        </h4>
                        <p><i class="fas fa-box"></i> Sedang Diproses</p>
                    </div>
                    <div class="stat-box">
                        <h4>
                            <?= $total_completed ?>
                        </h4>
                        <p><i class="fas fa-check-circle"></i> Selesai</p>
                    </div>
                    <div class="stat-box">
                        <h4>Rp
                            <?= number_format($total_revenue / 1000) ?>K
                        </h4>
                        <p><i class="fas fa-money-bill-wave"></i> Total Revenue</p>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Order</th>
                                    <th width="20%">Customer</th>
                                    <th width="15%">Total</th>
                                    <th width="15%">Status</th>
                                    <th width="10%">Items</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 40px; color:#999;">
                                            <i class="fas fa-shopping-cart"
                                                style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                            Belum ada order.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <?= $no++ ?>
                                            </td>
                                            <td>
                                                <span class="order-number">
                                                    <?= htmlspecialchars($order['order_number']) ?>
                                                </span>
                                                <span class="order-date">
                                                    <i class="far fa-clock"></i>
                                                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        <?= strtoupper(substr($order['user_name'], 0, 2)) ?>
                                                    </div>
                                                    <div class="customer-details">
                                                        <strong>
                                                            <?= htmlspecialchars($order['user_name']) ?>
                                                        </strong>
                                                        <small>
                                                            <?= htmlspecialchars($order['user_email']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="font-weight: 600; color: #2d7f68;">
                                                Rp
                                                <?= number_format($order['total_amount']) ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?php
                                                    $labels = [
                                                        'pending' => 'Pending',
                                                        'processing' => 'Diproses',
                                                        'shipped' => 'Dikirim',
                                                        'completed' => 'Selesai',
                                                        'cancelled' => 'Dibatalkan'
                                                    ];
                                                    echo $labels[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-box"></i>
                                                <?= $order['item_count'] ?> item
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-icon btn-view" onclick="viewOrder(<?= $order['id'] ?>)"
                                                        title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn-icon btn-edit"
                                                        onclick="editStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')"
                                                        title="Edit Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?delete=<?= $order['id'] ?>" class="btn-icon btn-delete"
                                                        onclick="return confirm('Yakin ingin menghapus order ini? Stok produk akan dikembalikan.')"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Detail Order -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Order</h2>
                <button class="btn-close" onclick="closeModal('viewModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="viewModalBody"></div>
        </div>
    </div>

    <!-- Modal Edit Status -->
    <div class="modal" id="editModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Edit Status Order</h2>
                <button class="btn-close" onclick="closeModal('editModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="editOrderId">
                <div class="form-group">
                    <label>Status Order</label>
                    <select name="status" class="form-control" id="editStatus" required>
                        <option value="pending">Menunggu Pembayaran</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dikirim</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn-submit">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>
        </div>
    </div>

    <script>
        // Toggle Sidebar untuk Mobile
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        hamburgerMenu.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            hamburgerMenu.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            hamburgerMenu.classList.remove('active');
        });

        const menuItems = document.querySelectorAll('.side-nav a');
        menuItems.forEach(item => {
            item.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    hamburgerMenu.classList.remove('active');
                }
            });
        });

        // View Order Detail
        function viewOrder(orderId) {
            fetch(`get_order_admin.php?id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('viewModalBody').innerHTML = html;
                    document.getElementById('viewModal').classList.add('active');
                });
        }

        // Edit Status
        function editStatus(orderId, currentStatus) {
            document.getElementById('editOrderId').value = orderId;
            document.getElementById('editStatus').value = currentStatus;
            document.getElementById('editModal').classList.add('active');
        }

        // Close Modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });
    </script>

</body>

</html>