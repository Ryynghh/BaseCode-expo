<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Ambil data dari form
$shipping_name = $_POST['shipping_name'];
$shipping_phone = $_POST['shipping_phone'];
$shipping_address = $_POST['shipping_address'];
$shipping_city = $_POST['shipping_city'];
$shipping_postal_code = $_POST['shipping_postal_code'];
$payment_method = $_POST['payment_method'];

// Ambil data keranjang
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$uid]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    $_SESSION['error'] = "Keranjang Anda kosong!";
    header("Location: cart.php");
    exit;
}

// Hitung total
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

// Generate order number
$order_number = 'ORD-' . strtoupper(uniqid());

try {
    $pdo->beginTransaction();

    // 1. Simpan ke tabel orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, shipping_name, shipping_phone, shipping_address, 
                           shipping_city, shipping_postal_code, payment_method, total_amount, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $uid,
        $order_number,
        $shipping_name,
        $shipping_phone,
        $shipping_address,
        $shipping_city,
        $shipping_postal_code,
        $payment_method,
        $total_amount
    ]);

    $order_id = $pdo->lastInsertId();

    // 2. Simpan detail order ke tabel order_items
    foreach ($cart_items as $item) {
        $subtotal = $item['price'] * $item['quantity'];

        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $subtotal
        ]);
    }

    // 3. Kosongkan keranjang
    $pdo->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$uid]);

    $pdo->commit();

    // Simpan order number di session untuk halaman sukses
    $_SESSION['order_number'] = $order_number;
    $_SESSION['order_total'] = $total_amount;
    $_SESSION['payment_method'] = $payment_method;

    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>