<?php
require_once '../config/database.php';
session_start();

// 1. Cek Security (Wajib Login & Role Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Logic Hapus User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Cegah admin menghapus dirinya sendiri
        if ($id == $_SESSION['user_id']) {
            $error = "Anda tidak dapat menghapus akun Anda sendiri!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "User berhasil dihapus.";
        }
    } catch (Exception $e) {
        $error = "Gagal menghapus user.";
    }
}

// 3. Ambil Semua Data User (Terbaru di atas)
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - EximGo Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS Khusus untuk Halaman User */
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2d7f68, #1cd44d);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .user-details strong {
            display: block;
            font-size: 0.95rem;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .user-details small {
            color: #888;
            font-size: 0.85rem;
        }

        .badge-role {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-admin {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-user {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .status-active {
            color: #2e7d32;
            font-weight: 600;
        }

        .status-inactive {
            color: #d32f2f;
            font-weight: 600;
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

        .btn-delete:disabled {
            background-color: #f5f5f5;
            color: #bdbdbd;
            cursor: not-allowed;
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

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
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
                min-width: 700px;
                font-size: 0.85rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 10px 8px;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .user-details strong {
                font-size: 0.9rem;
            }

            .user-details small {
                font-size: 0.8rem;
            }

            .btn-delete {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .section-header {
                margin-bottom: 15px;
            }

            .stats-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .stat-box {
                padding: 15px;
            }

            .stat-box h4 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .table-card {
                padding: 10px;
            }

            .custom-table {
                min-width: 650px;
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
                    <li class="active"><a href="../admin/user.php"><i class="fas fa-users"></i> Manajemen Member</a>
                    </li>
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
                    <h3 style="margin:0;">Manajemen User</h3>
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../auth/logout.php">Logout</a>
                </div>
            </header>

            <div class="content-wrapper">

                <div class="section-header">
                    <h2>Daftar User Terdaftar</h2>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">
                        Total: <b>
                            <?= count($users) ?>
                        </b> user
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
                    $total_admin = 0;
                    $total_user = 0;
                    foreach ($users as $u) {
                        if ($u['role'] === 'admin')
                            $total_admin++;
                        else
                            $total_user++;
                    }
                    ?>
                    <div class="stat-box">
                        <h4>
                            <?= $total_admin ?>
                        </h4>
                        <p><i class="fas fa-user-shield"></i> Total Admin</p>
                    </div>
                    <div class="stat-box">
                        <h4>
                            <?= $total_user ?>
                        </h4>
                        <p><i class="fas fa-users"></i> Total Member</p>
                    </div>
                    <div class="stat-box">
                        <h4>
                            <?= count($users) ?>
                        </h4>
                        <p><i class="fas fa-user-check"></i> Total User</p>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">User</th>
                                    <th width="20%">Email</th>
                                    <th width="15%">Role</th>
                                    <th width="15%">Tanggal Daftar</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 40px; color:#999;">
                                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                            Belum ada user terdaftar.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <?= $no++ ?>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($u['full_name'], 0, 2)) ?>
                                                    </div>
                                                    <div class="user-details">
                                                        <strong>
                                                            <?= htmlspecialchars($u['full_name']) ?>
                                                        </strong>
                                                        <small>
                                                            <i class="fas fa-circle"
                                                                style="font-size: 6px; color: #66bb6a;"></i>
                                                            Online
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="fas fa-envelope"
                                                    style="color: var(--accent-color); margin-right: 5px;"></i>
                                                <?= htmlspecialchars($u['email']) ?>
                                            </td>
                                            <td>
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="badge-role badge-admin">
                                                        <i class="fas fa-shield-alt"></i> Admin
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-role badge-user">
                                                        <i class="fas fa-user"></i> User
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <i class="far fa-calendar-alt" style="margin-right: 5px; color: #888;"></i>
                                                <?= isset($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                                    <span style="font-size: 0.85rem; color: #888;">
                                                        <i class="fas fa-lock"></i> Akun Anda
                                                    </span>
                                                <?php else: ?>
                                                    <a href="?delete=<?= $u['id'] ?>" class="btn-delete"
                                                        onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars($u['full_name']) ?>?');">
                                                        <i class="fas fa-trash-alt"></i> Hapus
                                                    </a>
                                                <?php endif; ?>
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