<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['name'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-white min-vh-100 p-3 border-end">

            <h4 class="text-primary mb-4">
                PELANGGARAN SISWA
            </h4>

            <ul class="nav nav-pills flex-column">

                <!-- DASHBOARD -->
                <?php if ($role === 'guru') : ?>

                    <li class="nav-item mb-2">
                        <a href="dashboard.php" class="nav-link active">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Catatan Pelanggaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Tindakan
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Laporan
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Riwayat
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Rekap poin
                        </a>
                    </li>

                <?php endif; ?>


                <!-- MENU KHUSUS ADMIN -->
                <?php if ($role === 'admin') : ?>

                    <li class="nav-item mb-2">
                        <a href="menu1.php" class="nav-link text-dark">
                            Kelola Siswa
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu2.php" class="nav-link text-dark">
                            Kelola Guru
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu3.php" class="nav-link text-dark">
                            Kelola Kelas
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu4.php" class="nav-link text-dark">
                            Kelola Tahun Ajaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu5.php" class="nav-link text-dark">
                            Penempatan Siswa
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu6.php" class="nav-link text-dark">
                            Kelola Wali Kelas
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu7.php" class="nav-link text-dark">
                            Kelola Kategori Pelanggaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu8.php" class="nav-link text-dark">
                            Kelola Jenis Pelanggaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="menu9.php" class="nav-link text-dark">
                            Cetak / Export
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="about.php" class="nav-link text-dark">
                            About Me
                        </a>
                    </li>

                <?php endif; ?>


                <!-- GARIS PEMBATAS -->
                <hr class="text-secondary">

                <!-- LOGOUT -->
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>

        </div>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="text-primary">Dashboard</h2>

            <p class="text-secondary">
                Selamat datang,
                <strong class="text-dark">
                    <?= htmlspecialchars($nama); ?>
                </strong>
                <br>
                Anda login sebagai
                <strong class="text-primary">
                    <?= strtoupper(htmlspecialchars($role)); ?>
                </strong>
            </p>


            <div class="row">

                <!-- CARD DATA SISWA -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-primary">

                        <div class="card-body">

                            <h5 class="card-title text-primary">
                                Data Siswa
                            </h5>

                            <h2 class="text-dark">
                                120
                            </h2>

                        </div>

                    </div>

                </div>


                <!-- CARD DATA GURU -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-primary">

                        <div class="card-body">

                            <h5 class="card-title text-primary">
                                Data Guru
                            </h5>

                            <h2 class="text-dark">
                                25
                            </h2>

                        </div>

                    </div>

                </div>


                <!-- CARD KELAS -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-primary">

                        <div class="card-body">

                            <h5 class="card-title text-primary">
                                Kelas
                            </h5>

                            <h2 class="text-dark">
                                12
                            </h2>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>
</div>


<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>