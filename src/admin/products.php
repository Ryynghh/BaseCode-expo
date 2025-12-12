<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$upload_dir = '../assets/images/uploads/';

// --- LOGIC HAPUS PRODUK ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Hapus gambar lama
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && $img != 'default.jpg' && file_exists($upload_dir . $img)) {
        unlink($upload_dir . $img);
    }

    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    header("Location: products.php?msg=deleted");
    exit;
}

// --- LOGIC TAMBAH & EDIT PRODUK ---
$edit_mode = false;
$product_data = null;

// Jika tombol Edit ditekan (ambil data lama)
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product_data = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];

    // Handle Image
    $img_name = $_POST['old_image'] ?? 'default.jpg'; // Pakai gambar lama defaultnya

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
            $img_name = $new_name;
            // Hapus gambar lama jika ada dan bukan default saat update
            if (!empty($_POST['old_image']) && $_POST['old_image'] != 'default.jpg' && file_exists($upload_dir . $_POST['old_image'])) {
                unlink($upload_dir . $_POST['old_image']);
            }
        }
    }

    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        // --- UPDATE ---
        $id = $_POST['product_id'];
        $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $desc, $price, $stock, $img_name, $id]);
        header("Location: products.php?msg=updated");
    } else {
        // --- INSERT ---
        $sql = "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $desc, $price, $stock, $img_name]);
        header("Location: products.php?msg=created");
    }
    exit;
}

// Ambil semua data untuk tabel
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/products.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="dashboard-container">

        <aside class="sidebar">
            <div class="brand">
                <div class="brand-text">
                    <span>EximGo Admin</span>
                </div>
            </div>

            <nav class="side-nav">
                <ul>
                    <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="#"><i class="fa-solid fa-tags"></i> Manajemen Produk</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Manajemen Member</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="left-icons">
                </div>
                <div class="right-actions">
                    <a class="logout-btn" href="../index.php">Logout</a>
                </div>
            </header>
            <div class="content-wrapper">
                <div class="page-header">
                    <h1>Manajemen Produk</h1>
                    <p>Tambah, edit, dan kelola stok produk EximGo.</p>
                </div>

                <div class="dashboard-grid">

                    <div class="grid-item form-section">
                        <div class="card">
                            <div class="card-header">
                                <h3><?= $edit_mode ? 'Edit Produk' : 'Tambah Produk Baru' ?></h3>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="product_id" value="<?= $product_data['id'] ?? '' ?>">
                                    <input type="hidden" name="old_image" value="<?= $product_data['image'] ?? '' ?>">

                                    <div class="form-group">
                                        <label>Nama Produk</label>
                                        <input type="text" name="name" class="form-control"
                                            value="<?= htmlspecialchars($product_data['name'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Harga (IDR)</label>
                                            <input type="number" name="price" class="form-control"
                                                value="<?= $product_data['price'] ?? '' ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Stok</label>
                                            <input type="number" name="stock" class="form-control"
                                                value="<?= $product_data['stock'] ?? '' ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <textarea name="description" class="form-control"
                                            rows="3"><?= htmlspecialchars($product_data['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Gambar Produk</label>
                                        <div class="image-preview-box">
                                            <img id="imgPreview"
                                                src="../assets/images/uploads/<?= $product_data['image'] ?? 'default-placeholder.png' ?>"
                                                alt="Preview">
                                        </div>
                                        <input type="file" name="image" id="imgInput" accept="image/*"
                                            class="form-control-file">
                                    </div>

                                    <div class="form-actions">
                                        <?php if ($edit_mode): ?>
                                            <a href="products.php" class="btn btn-secondary">Batal</a>
                                        <?php endif; ?>
                                        <button type="submit"
                                            class="btn btn-primary"><?= $edit_mode ? 'Update Produk' : 'Simpan Produk' ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="grid-item table-section">
                        <div class="card">
                            <div class="card-header">
                                <h3>Daftar Produk (<?= count($products) ?>)</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Img</th>
                                                <th>Nama Produk</th>
                                                <th>Harga</th>
                                                <th>Stok</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($products)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Belum ada data produk.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($products as $p): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="../assets/images/uploads/<?= $p['image'] ?? 'default.jpg' ?>"
                                                                class="img-thumb">
                                                        </td>
                                                        <td>
                                                            <span class="product-name"
                                                                style="color:black;"><?= htmlspecialchars($p['name']) ?></span><br>
                                                            <small class="text-muted"
                                                                style="color:black;"><?= substr($p['description'], 0, 30) ?>...</small>
                                                        </td>
                                                        <td style="color:black;">Rp
                                                            <?= number_format($p['price'], 0, ',', '.') ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($p['stock'] <= 5): ?>
                                                                <span class="badge badge-danger"><?= $p['stock'] ?></span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success"><?= $p['stock'] ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-edit"><i
                                                                        class="fas fa-edit"></i></a>
                                                                <a href="?delete=<?= $p['id'] ?>" class="btn-icon btn-delete"
                                                                    onclick="return confirm('Hapus produk ini permanen?')"><i
                                                                        class="fas fa-trash"></i></a>
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
                    </div>
                </div>

        </main>
    </div>

    <script>
        document.getElementById('imgInput').onchange = function (evt) {
            var tgt = evt.target || window.event.srcElement,
                files = tgt.files;
            if (FileReader && files && files.length) {
                var fr = new FileReader();
                fr.onload = function () {
                    document.getElementById('imgPreview').src = fr.result;
                }
                fr.readAsDataURL(files[0]);
            }
        }
    </script>
</body>

</html>