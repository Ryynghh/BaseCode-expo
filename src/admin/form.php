<?php
require_once '../config/database.php';
session_start();

// 1. Cek Security (Wajib Login & Role Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Logic Hapus Pesan
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Pesan berhasil dihapus.";
    } catch (Exception $e) {
        $error = "Gagal menghapus pesan.";
    }
}

// 3. Ambil Semua Data Pesan (Terbaru di atas)
$forms = $pdo->query("SELECT * FROM forms ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesan - EximGo Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS Khusus untuk Halaman Form */
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
            vertical-align: top;
        }

        .custom-table tr:hover {
            background-color: #fafffb;
        }

        .msg-content {
            max-width: 300px;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.5;
        }

        .contact-info div {
            margin-bottom: 3px;
            font-size: 0.9rem;
        }

        .contact-info i {
            color: var(--accent-color);
            width: 20px;
        }

        .btn-delete {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-delete:hover {
            background-color: #ffcdd2;
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

        /* RESPONSIVE TABLE */
        @media (max-width: 992px) {
            .table-card {
                padding: 20px;
            }

            .custom-table {
                font-size: 0.9rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 12px 10px;
            }

            .msg-content {
                max-width: 250px;
            }
        }

        @media (max-width: 768px) {
            .table-card {
                padding: 15px;
                border-radius: 10px;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .custom-table {
                min-width: 800px;
                font-size: 0.85rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 10px 8px;
            }

            .msg-content {
                max-width: 200px;
                font-size: 0.85rem;
            }

            .contact-info div {
                font-size: 0.85rem;
            }

            .btn-delete {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .section-header {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 480px) {
            .table-card {
                padding: 10px;
            }

            .custom-table {
                min-width: 700px;
                font-size: 0.8rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 8px 6px;
            }

            .alert-box {
                padding: 12px;
                font-size: 0.9rem;
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
                    <li><a href="../admin/orders.php"><i class="fas fa-shopping-cart"></i> Manajemen Order</a></li>
                    <li class="active"><a href="../admin/form.php"><i class="fa-solid fa-envelope"></i> Manajemen
                            Form</a></li>
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
                    <h3 style="margin:0;">Inbox Pesan</h3>
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../auth/logout.php">Logout</a>
                </div>
            </header>

            <div class="content-wrapper">

                <div class="section-header">
                    <h2>Daftar Pesan Masuk</h2>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">
                        Total: <b><?= count($forms) ?></b> pesan
                    </div>
                </div>

                <?php if (isset($msg)): ?>
                    <div class="alert-box success"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert-box error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Pengirim</th>
                                    <th width="25%">Kontak</th>
                                    <th width="35%">Isi Pesan</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($forms)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding: 40px; color:#999;">
                                            <i class="far fa-envelope-open"
                                                style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                            Belum ada pesan masuk.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($forms as $f): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($f['first_name'] . ' ' . $f['last_name']) ?></strong><br>
                                                <small style="color:#888;">
                                                    <i class="far fa-clock"></i>
                                                    <?= isset($f['created_at']) ? date('d M Y, H:i', strtotime($f['created_at'])) : '-' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($f['email']) ?>
                                                    </div>
                                                    <div><i class="fas fa-phone"></i>
                                                        <?= htmlspecialchars($f['phone'] ?: '-') ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="msg-content">
                                                    <?= nl2br(htmlspecialchars($f['message'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="?delete=<?= $f['id'] ?>" class="btn-delete"
                                                    onclick="return confirm('Yakin ingin menghapus pesan dari <?= htmlspecialchars($f['first_name']) ?>?');">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </a>
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