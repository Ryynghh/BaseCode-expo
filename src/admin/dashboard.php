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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>
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
                    <li><a href="#"><i class="fas fa-users"></i> Manajemen Member</a></li>
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
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../index.php">Logout</a>
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
                            <a class="view-detail" href="">View Detail</a>
                        </div>
                        <div class="card-body">
                            <h3>Total User</h3>
                            <div class="number"><?= $total_users ?></div>
                        </div>
                        <div class="card-decoration"></div>
                    </div>

                    <div class="card card-green">
                        <div class="card-header">
                            <i class="fas fa-chart-line"></i>
                            <a class="view-detail" href="">View Detail</a>
                        </div>
                        <div class="card-body">
                            <h3>Total Laporan</h3>
                            <div class="number">65%</div>
                        </div>
                        <div class="card-decoration"></div>
                    </div>
                </div>

                <div class="section-header mt-4">
                    <h2>Secondary <i class="fas fa-chevron-right text-muted"></i></h2>
                </div>

                <div class="charts-row">

                    <div class="activity-container">
                        <div class="activity-header">
                            <h3>User Activity</h3>
                            <i class="fas fa-ellipsis-v"></i>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="avatar-sm">NN</div>
                                <div class="activity-info">
                                    <p><strong>User <span class="indicator green"></span></strong></p>
                                    <small>new comment</small>
                                </div>
                                <span class="time">4 minutes ago</span>
                            </div>
                            <div class="activity-item">
                                <div class="avatar-sm blue">NN</div>
                                <div class="activity-info">
                                    <p><strong>User</strong></p>
                                    <small>ordered 2 items</small>
                                </div>
                                <span class="time">20 minutes ago</span>
                            </div>
                            <div class="activity-item">
                                <div class="avatar-sm">NN</div>
                                <div class="activity-info">
                                    <p><strong>User</strong></p>
                                    <small>ordered 1 item</small>
                                </div>
                                <span class="time">30 minutes ago</span>
                            </div>
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

        // Close sidebar when clicking menu item (optional)
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