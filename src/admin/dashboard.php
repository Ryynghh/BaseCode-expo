<?php
require_once '../config/database.php';
session_start();

// Cek Security: Harus Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Hitung Jumlah User
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

// Hitung Total Produk
$stmt2 = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = $stmt2->fetchColumn();

// Hitung Total Order
$stmt3 = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt3->fetchColumn();

// Ambil Recent Activities (Form Submissions & Orders) - Gabungkan dan urutkan berdasarkan waktu
$query = "
    (SELECT 'form' as type, 
            CONCAT(first_name, ' ', last_name) as user_name,
            'mengirim form kontak' as activity,
            created_at
     FROM forms
     ORDER BY created_at DESC
     LIMIT 5)
    
    UNION ALL
    
    (SELECT 'order' as type,
            u.full_name as user_name,
            CONCAT('membuat order baru (', o.order_number, ')') as activity,
            o.created_at
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC
     LIMIT 5)
    
    ORDER BY created_at DESC
    LIMIT 10
";

$activities = $pdo->query($query)->fetchAll();

// Function untuk format waktu relatif
function timeAgo($datetime)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0)
        return $diff->y . ' tahun lalu';
    if ($diff->m > 0)
        return $diff->m . ' bulan lalu';
    if ($diff->d > 0)
        return $diff->d . ' hari lalu';
    if ($diff->h > 0)
        return $diff->h . ' jam lalu';
    if ($diff->i > 0)
        return $diff->i . ' menit lalu';
    return 'Baru saja';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - EximGo</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Sidebar Overlay untuk Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-container">

        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="brand-text">
                    <span>EximGo Admin</span>
                </div>
            </div>

            <nav class="side-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../admin/products.php"><i class="fa-solid fa-tags"></i> Manajemen Produk</a></li>
                    <li><a href="../admin/member.php"><i class="fas fa-users"></i> Manajemen Member</a></li>
                    <li><a href="../admin/orders.php"><i class="fas fa-shopping-cart"></i> Manajemen Order</a></li>
                    <li><a href="../admin/form.php"><i class="fa-solid fa-envelope"></i>Manajemen Form</a></li>
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
                    <h3 style="margin:0;">Dashboard</h3>
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../auth/logout.php">Logout</a>
                </div>
            </header>

            <div class="content-wrapper">

                <div class="section-header">
                    <h2>Overview <i class="fas fa-chevron-right text-muted"></i></h2>
                </div>

                <div class="cards-grid">
                    <div class="card card-blue">
                        <div class="card-header">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <a class="view-detail" href="../admin/products.php">View Detail</a>
                        </div>
                        <div class="card-body">
                            <h3>Jumlah Produk</h3>
                            <div class="number"><?= $total_products ?></div>
                        </div>
                        <div class="card-decoration"></div>
                    </div>

                    <div class="card card-orange">
                        <div class="card-header">
                            <i class="fas fa-user"></i>
                            <a class="view-detail" href="../admin/member.php">View Detail</a>
                        </div>
                        <div class="card-body">
                            <h3>Total User</h3>
                            <div class="number"><?= $total_users ?></div>
                        </div>
                        <div class="card-decoration"></div>
                    </div>

                    <div class="card card-green">
                        <div class="card-header">
                            <i class="fas fa-shopping-cart"></i>
                            <a class="view-detail" href="../admin/orders.php">View Detail</a>
                        </div>
                        <div class="card-body">
                            <h3>Total Order</h3>
                            <div class="number"><?= $total_orders ?></div>
                        </div>
                        <div class="card-decoration"></div>
                    </div>
                </div>

                <div class="section-header mt-4">
                    <h2>Aktivitas Terbaru <i class="fas fa-chevron-right text-muted"></i></h2>
                </div>

                <div class="charts-row">

                    <div class="activity-container">
                        <div class="activity-header">
                            <h3>User Activity</h3>
                            <a href="#" style="color: #888; font-size: 0.85rem; text-decoration: none;">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </a>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($activities)): ?>
                                <div style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                    Belum ada aktivitas user
                                </div>
                            <?php else: ?>
                                <?php foreach ($activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="avatar-sm <?= $activity['type'] === 'order' ? 'blue' : '' ?>">
                                            <?= strtoupper(substr($activity['user_name'], 0, 2)) ?>
                                        </div>
                                        <div class="activity-info">
                                            <p>
                                                <strong><?= htmlspecialchars($activity['user_name']) ?></strong>
                                                <?php if ($activity['type'] === 'order'): ?>
                                                    <span class="indicator green"></span>
                                                <?php endif; ?>
                                            </p>
                                            <small>
                                                <?php if ($activity['type'] === 'form'): ?>
                                                    <i class="fas fa-envelope"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-shopping-cart"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($activity['activity']) ?>
                                            </small>
                                        </div>
                                        <span class="time"><?= timeAgo($activity['created_at']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($activities)): ?>
                            <div style="margin-top: 20px; text-align: center;">
                                <a href="#"
                                    style="color: #2d7f68; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                                    Lihat Semua Aktivitas <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Orders Summary -->
                    <div class="activity-container">
                        <div class="activity-header">
                            <h3>Order Terbaru</h3>
                            <a href="../admin/orders.php"
                                style="color: #888; font-size: 0.85rem; text-decoration: none;">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php
                        // Ambil 5 order terbaru
                        $recent_orders = $pdo->query("
                            SELECT o.order_number, o.total_amount, o.status, o.created_at, u.full_name
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            ORDER BY o.created_at DESC
                            LIMIT 5
                        ")->fetchAll();
                        ?>
                        <div class="recent-orders-list">
                            <?php if (empty($recent_orders)): ?>
                                <div style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-shopping-bag" style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                    Belum ada order
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <div class="order-summary-item">
                                        <div class="order-summary-info">
                                            <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                            <small><?= htmlspecialchars($order['full_name']) ?></small>
                                        </div>
                                        <div class="order-summary-meta">
                                            <span class="status-badge-sm status-<?= $order['status'] ?>">
                                                <?php
                                                $status_labels = [
                                                    'pending' => 'Pending',
                                                    'processing' => 'Proses',
                                                    'shipped' => 'Kirim',
                                                    'completed' => 'Selesai',
                                                    'cancelled' => 'Batal'
                                                ];
                                                echo $status_labels[$order['status']] ?? $order['status'];
                                                ?>
                                            </span>
                                            <strong style="color: #2d7f68; font-size: 0.9rem;">
                                                Rp <?= number_format($order['total_amount'] / 1000) ?>K
                                            </strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
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

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            hamburgerMenu.classList.remove('active');
        });

        // Close sidebar when clicking menu item
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
    </script>

</body>

</html>