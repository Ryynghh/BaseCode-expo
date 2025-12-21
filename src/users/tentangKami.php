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
    <link rel="stylesheet" href="../assets/css/tentangKami.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <main class="main-info-mode">
        <div class="summary-card">

            <div class="card-content">
                <h2>Mengenai Kami: EximGo & Daur Ulang</h2>
                <p>Kami terinspirasi oleh model bisnis *Clean-Tech* seperti PT Inocycle Technology Group Tbk ("INOV")
                    yang berfokus pada solusi daur ulang global untuk masalah lokal.</p>

                <div class="separator"></div>

                <h3>Bisnis Daur Ulang (INOV Model)</h3>
                <p>INOV adalah perusahaan teknologi bersih yang mengolah botol PET dan limbah plastik menjadi
                    <strong>Recycled Polyester Staple Fiber (Re-PSF)</strong>, yaitu serat sintetis ramah lingkungan.
                </p>
                <ul>
                    <li>INOV adalah produsen serat daur ulang terbesar yang memiliki sertifikasi <strong>Global Recycled
                            Standards (GRS)</strong> di Indonesia.</li>
                    <li>Mereka berkontribusi mengurangi polusi dengan memproses miliaran sampah botol per tahun.</li>
                    <li>Produk Re-PSF digunakan untuk berbagai industri seperti garmen, otomotif, hingga produk rumah
                        tangga ("homeware"), contohnya bantal dan kasur.</li>
                </ul>
                <p style="font-style: italic; border-left: 3px solid var(--accent-color); padding-left: 10px;">
                    Kami di EximGo mendukung penuh ekosistem ekonomi sirkular ini dengan menyediakan platform untuk
                    produk-produk berkelanjutan.
                </p>
            </div>
        </div>

    </main>
</body>

</html>