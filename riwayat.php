<?php
require_once 'koneksi.php';
require_once 'auth_check.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil nama user/guru yang sedang login dari session
$nama_user = $_SESSION['name'] ?? '';
$user_role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Riwayat Pelanggaran Selesai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #212529; color: #fff; padding-top: 15px; }
        .sidebar a { display: block; padding: 10px 20px; color: #adb5bd; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd; color: #fff; }
        .main { margin-left: 250px; padding: 30px; }
    </style>
</head>
<body>
<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-white mb-4">Pelanggaran Siswa</h4>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-1"><a href="dashboard.php">Dashboard</a></li>
        <li class="nav-item mb-1"><a href="catat_pelanggaran.php">Catat Pelanggaran</a></li>
        <li class="nav-item mb-1"><a href="tindakan.php">Tindakan</a></li>
        <li class="nav-item mb-1"><a href="riwayat.php" class="active">Riwayat</a></li>
        <li class="nav-item mb-1"><a href="rekap_poin.php">Rekap Poin</a></li>
        <li class="nav-item mb-1"><a href="laporan.php">Laporan</a></li>
        <li class="nav-item mb-1"><a href="about_me.php">About Me</a></li>
    </ul>
    <hr><a href="logout.php" class="text-danger">Logout</a>
</div>

<div class="main">
    <h3 class="fw-bold mb-3">Riwayat Pelanggaran (Status Selesai)</h3>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white">Daftar Pelanggaran Yang Telah Selesai Ditangani (<?= htmlspecialchars($nama_user); ?>)</div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelRiwayat" class="table table-bordered table-striped align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        
                        // Menyiapkan dasar kueri
                        $sql_riwayat = "SELECT ps.*, s.nama AS s_nama, k.nama AS k_nama, p.nama AS p_nama 
                                        FROM t_pelanggaran_siswa ps 
                                        LEFT JOIN t_siswa s ON ps.siswa_id = s.id 
                                        LEFT JOIN t_kelas k ON ps.kelas_id = k.id 
                                        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id 
                                        WHERE ps.status = 'Selesai'";

                        // Jika login sebagai guru/selain admin (misal: Aldi), batasi hanya pencatatannya sendiri
                        // Sesuaikan kondisi ini dengan struktur database Anda (apakah 'nama_guru' atau menggunakan 'user_id')
                        if ($user_role !== 'admin') {
                            $escaped_nama = mysqli_real_escape_string($koneksi, $nama_user);
                            $sql_riwayat .= " AND ps.nama_guru = '$escaped_nama'";
                        }

                        $sql_riwayat .= " ORDER BY ps.id DESC";
                        
                        $data = mysqli_query($koneksi, $sql_riwayat);
                        while($row = mysqli_fetch_assoc($data)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row['s_nama'] ?? $row['nama_siswa'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['k_nama'] ?? $row['nama_kelas'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['p_nama'] ?? $row['nama_pelanggaran'] ?? '-'); ?></td>
                            <td><span class="badge bg-danger"><?= (int)$row['poin']; ?></span></td>
                            <td><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['tindakan'] ?? '-'); ?></td>
                            <td><span class="badge bg-success">Selesai</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () { 
    $('#tabelRiwayat').DataTable({
        language: {
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Berikutnya", last: "Terakhir" },
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(disaring dari _MAX_ total entri)",
            lengthMenu: "Tampilkan _MENU_ entri",
            search: "Cari:",
            zeroRecords: "Tidak ditemukan data yang sesuai"
        }
    }); 
});
</script>
</body>
</html>