<?php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$nama = $_SESSION['name'] ?? ($_SESSION['nama_user'] ?? ($_SESSION['nama'] ?? 'Pengguna'));

// DETEKSI KELAS WALI KELAS YANG SEDANG LOGIN
$kelas_wali_filter = null;
if ($role === 'guru') {
    $q_wk = mysqli_query($koneksi, "SELECT k.nama AS nama_kelas FROM t_wali_kelas wk JOIN t_guru g ON wk.guru_id = g.id JOIN t_kelas k ON wk.kelas_id = k.id WHERE g.user_id = $user_id AND wk.status_aktif = 1 LIMIT 1");
    if ($wk_row = mysqli_fetch_assoc($q_wk)) {
        $kelas_wali_filter = $wk_row['nama_kelas']; 
    } elseif (stripos($nama, 'Ari Sandriana') !== false) {
        $kelas_wali_filter = 'XII RPL 1';
    }
}

// Proses Hapus Data Riwayat (Opsional jika ingin fitur hapus di riwayat)
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM t_pelanggaran_siswa WHERE id = $id");
    header("Location: riwayat.php?msg=hapus");
    exit();
}

// QUERY UTAMA: Hanya menampilkan status 'Selesai' dengan urutan ASC (dari kecil ke besar agar nomor 1 di atas)
$sql = "SELECT ps.*, s.nama AS nama_siswa, k.nama AS nama_kelas, p.nama AS nama_pelanggaran, g.nama AS nama_guru 
        FROM t_pelanggaran_siswa ps 
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id 
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id 
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id 
        LEFT JOIN t_guru g ON ps.guru_id = g.id 
        WHERE ps.status = 'Selesai'";

if ($role === 'guru' && !empty($kelas_wali_filter)) {
    $sql .= " AND (k.nama = '" . mysqli_real_escape_string($koneksi, $kelas_wali_filter) . "' OR ps.nama_kelas = '" . mysqli_real_escape_string($koneksi, $kelas_wali_filter) . "')";
}

// Diurutkan secara ASC agar nomor urut 1 berada di baris teratas
$sql .= " ORDER BY ps.tanggal ASC, ps.id ASC";
$data = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #212529; color: #fff; padding-top: 15px; overflow-y: auto; }
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
    <h3 class="fw-bold mb-3">Riwayat Pelanggaran Siswa</h3>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'hapus'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Data riwayat berhasil dihapus. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">Daftar Riwayat Pelanggaran <?= $kelas_wali_filter ? '('.$kelas_wali_filter.')' : '' ?></div>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['tanggal'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggaran'] ?? '-'); ?></td>
                            <td><span class="badge bg-danger"><?= (int)($row['poin'] ?? 0); ?></span></td>
                            <td><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></td>
                            <td>
                                <a href="riwayat.php?hapus=<?= (int)$row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data riwayat ini?')">Hapus</a>
                            </td>
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
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            zeroRecords: "Belum ada data riwayat",
            paginate: { first: "Pertama", last: "Terakhir", next: "Berikutnya", previous: "Sebelumnya" }
        }
    });
});
</script>
</body>
</html>