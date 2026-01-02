<?php
// Pastikan koneksi database sudah tersedia di file induk yang memanggil footer ini
// Atau gunakan 'global $pdo;' jika perlu mengakses variabel koneksi dari luar

// LOGIKA FORMULIR (Dipindah ke sini agar bisa dipakai di mana saja)
$form_status = "";
if (isset($_POST['submit_contact'])) {
    // Tangkap data
    $fname = htmlspecialchars($_POST['first_name'] ?? '');
    $lname = htmlspecialchars($_POST['last_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $msg = htmlspecialchars($_POST['message'] ?? '');

    // Validasi & Simpan
    if (!empty($fname) && !empty($email) && !empty($msg)) {
        try {
            // Asumsi $pdo sudah didefinisikan di config/database.php yang di-include di halaman utama
            if (isset($pdo)) {
                $stmt = $pdo->prepare("INSERT INTO forms (first_name, last_name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$fname, $lname, $email, $phone, $msg]);
                $form_status = "success";
            } else {
                $form_status = "error_db"; // Database belum connect
            }
        } catch (Exception $e) {
            $form_status = "error";
        }
    } else {
        $form_status = "empty";
    }
}
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-left">
            <h3>Form</h3>
            <p class="footer-sub">Let us know if you got any problem, question, or even suggestion!</p>

            <?php if ($form_status == 'success'): ?>
                <div
                    style="background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #c8e6c9;">
                    ✅ Pesan terkirim!
                </div>
            <?php elseif ($form_status == 'error'): ?>
                <div
                    style="background:#ffebee; color:#c62828; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffcdd2;">
                    ❌ Gagal mengirim.
                </div>
            <?php elseif ($form_status == 'empty'): ?>
                <div
                    style="background:#fff3e0; color:#ef6c00; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #ffe0b2;">
                    ⚠️ Isi data wajib!
                </div>
            <?php endif; ?>

            <form class="footer-form" method="post" action="#footer-area">
                <div id="footer-area"></div>
                <div class="row">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name">
                </div>
                <div class="row">
                    <input type="email" name="email" placeholder="Mail" required>
                    <input type="text" name="phone" placeholder="Phone">
                </div>
                <textarea name="message" rows="5" placeholder="Message" required></textarea>
                <button type="submit" name="submit_contact" class="footer-submit">SUBMIT</button>
            </form>
        </div>

        <div class="footer-right">
            <h3>Contact Information</h3>
            <p class="contact-line">Jl. Kaliurang Km 14,5, Sleman, Yogyakarta 55584</p>
            <p class="contact-line">Call Us : +62 81 334 61 00</p>
            <div class="socials">
                <a href="#"><i class="fab fa-facebook"></i> facebook</a>
                <a href="#"><i class="fab fa-instagram"></i> instagram</a>
            </div>
        </div>
    </div>
</footer>