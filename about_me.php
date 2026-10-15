
<?php
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$nama = "Sherly Satriani";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me - Sistem Pelanggaran</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            color: #212529;
        }

        /* Sidebar */
        .sidebar {
            background-color: #212529;
            min-height: 100vh;
            padding: 16px 0;
        }

        .sidebar h4 {
            padding: 0 15px;
            font-weight: bold;
        }

        .sidebar .nav-link {
            color: #ffffff;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 7px;
        }

        .sidebar .nav-link:hover {
            background-color: #343a40;
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #ffffff;
        }

        .sidebar .logout {
            color: #dc3545;
        }

        .sidebar hr {
            margin: 18px 0;
            border-color: #495057;
        }

        /* Konten utama */
        .main-content {
            min-height: 100vh;
            padding: 25px;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #6c757d;
            margin-bottom: 18px;
        }

        .page-header hr {
            border-color: #adb5bd;
        }

        /* Membuat kartu tepat di tengah */
        .profile-wrapper {
            min-height: calc(100vh - 180px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Kartu profil */
        .profile-card {
            width: 100%;
            max-width: 600px;
            border: none;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .profile-card .card-body {
            padding: 35px 40px;
        }

        /* Foto profil */
        .profile-photo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .profile-photo {
            width: 130px;
            height: 130px;
            object-fit: cover;
            object-position: center;
            border-radius: 50%;
            border: 4px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Nama dan role */
        .profile-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .profile-info {
            text-align: left;
            line-height: 1.8;
            margin-top: 15px;
        }

        .profile-info p {
            margin-bottom: 8px;
        }

        /* Tampilan HP */
        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                padding: 15px;
            }

            .sidebar h4 {
                padding: 0;
            }

            .sidebar .nav-link {
                padding: 10px;
            }

            .main-content {
                padding: 18px;
            }

            .page-header h1 {
                font-size: 30px;
            }

            .profile-wrapper {
                min-height: auto;
                padding: 20px 0;
            }

            .profile-card .card-body {
                padding: 25px 20px;
            }

            .profile-photo {
                width: 110px;
                height: 110px;
            }

            .profile-name {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4 class="text-white mb-4">My Website</h4>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="about.php" class="nav-link active">
                        <i class="bi bi-person-circle me-2"></i>
                        About Me
                    </a>
                </li>

                <hr>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Konten utama -->
        <div class="col-md-9 col-lg-10 main-content">

            <div class="page-header">
                <h1>About Me</h1>
                <p>Informasi profil pengembang atau pengguna sistem.</p>
                <hr>
            </div>

            <!-- Profil di tengah -->
            <div class="profile-wrapper">

                <div class="card profile-card">
                    <div class="card-body text-center">

                        <!-- Foto profil asli -->
                        <div class="profile-photo-wrapper">
                            <img
                                src="foto-profil.jpg"
                                alt="Foto Profil Sherly Satriani"
                                class="profile-photo"
                            >
                        </div>

                        <!-- Nama -->
                        <h3 class="profile-name">
                            <?= htmlspecialchars($nama); ?>
                        </h3>

                        <!-- Role -->
                        <p class="mb-3">
                            <span class="badge bg-danger">
                                <?= strtoupper(htmlspecialchars($role)); ?>
                            </span>
                        </p>

                        <hr>

                        <!-- Informasi -->
                        <div class="profile-info">
                            <p>
                                <strong>Aplikasi:</strong>
                                Sistem Informasi Pelanggaran Siswa
                            </p>

                            <p>
                                <strong>Institusi:</strong>
                                SMK Muhammadiyah
                            </p>

                            <p class="mb-0">
                                <strong>Tahun:</strong>
                                2026
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
