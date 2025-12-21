<?php
require_once '../config/database.php';
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eximgo.my.id</title>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/tentangPet.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <main class="main-info-mode">
        <div class="summary-card">
            <div class="card-content">
                <h2>Polyethylene Terephthalate (PET) & Daur Ulang</h2>

                <h3>Apa itu PET?</h3>
                <p>PET adalah jenis plastik (kode #1) yang umum dipakai untuk kemasan minuman berkarbonasi, air minum,
                    jus, dan produk farmasi. PET dipilih karena merupakan salah satu polimer yang dapat didaur ulang
                    berkali-kali (solusi <em>cradle-to-cradle</em>).</p>

                <div class="separator"></div>

                <h3>Bagaimana PET Didaur Ulang?</h3>
                <ol>
                    <li>Botol PET bekas dikumpulkan dan dijadikan bahan baku utama.</li>
                    <li>Botol diproses menjadi serpihan (<em>flakes chips</em>).</li>
                    <li>Serpihan diolah menjadi <strong>Recycled Polyester Staple Fiber (Re-PSF)</strong>.</li>
                </ol>

                <h3>Pemanfaatan PET</h3>
                <ul>
                    <li>Pengisi serat untuk bantal, selimut, jaket, dan pakaian.</li>
                    <li>Bahan baku karpet dan suku cadang otomotif.</li>
                    <li>Aplikasi industri seperti geotekstil untuk konstruksi.</li>
                </ul>
            </div>
        </div>
    </main>
</body>

</html>