
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Me</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { margin: 0; background: #f8f9fa; font-family: Arial, sans-serif; }
        .sidebar { background: #212529; min-height: 100vh; padding: 20px 15px; }
        .sidebar a { color: white; padding: 12px; margin-bottom: 5px; border-radius: 7px; }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd; }
        .main-content { padding: 25px; }
        .profile-wrapper { min-height: 75vh; display: flex; justify-content: center; align-items: center; }
        .profile-card { width: 100%; max-width: 600px; border: none; border-radius: 20px; box-shadow: 0 4px 12px #0001; }
        .profile-photo { width: 130px; height: 130px; object-fit: cover; border-radius: 50%; border: 4px solid #e9ecef; }
        .profile-info { text-align: left; line-height: 1.8; }
        @media (max-width: 767px) {
            .sidebar { min-height: auto; }
            .main-content { padding: 15px; }
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4 class="text-white mb-4">My Website</h4>
            <ul class="nav flex-column">
                <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a href="about.php" class="nav-link active"><i class="bi bi-person-circle me-2"></i>About Me</a></li>
                <hr class="text-secondary">
                <li><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="col-md-9 col-lg-10 main-content">
            <h1 class="fw-bold">About Me</h1>
            <p class="text-secondary">Informasi profil pengembang atau pengguna sistem.</p>
            <hr>

            <div class="profile-wrapper">
                <div class="card profile-card">
                    <div class="card-body text-center p-4">

                        <img src="serli.jpeg" alt="Foto Sherly Satriani" class="profile-photo mb-3">

                        <h3 class="fw-bold">
                            <?= htmlspecialchars($nama); ?>
                        </h3>

                        <span class="badge bg-danger">
                            <?= strtoupper(htmlspecialchars($role)); ?>
                        </span>

                        <hr>

                        <div class="profile-info">
                            <p><strong>Aplikasi:</strong> Sistem Informasi Pelanggaran Siswa</p>
                            <p><strong>Institusi:</strong> SMK Muhammadiyah</p>
                            <p><strong>Tahun:</strong> 2026</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
