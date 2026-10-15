<?php
require_once 'koneksi.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }

// Tangkap filter jika ada
$tahun_id = $_GET['tahun_id'] ?? '';
$kelas_id = $_GET['kelas_id'] ?? '';
$action   = $_GET['action'] ?? '';

$where = [];
if ($tahun_id) { $where[] = "ks.tahun_ajaran_id = '$tahun_id'"; }
if ($kelas_id) { $where[] = "ks.kelas_id = '$kelas_id'"; }
$sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";

// Query data laporan penempatan siswa atau pelanggaran
$query = "SELECT ks.*, s.nis, s.nama AS nama_siswa, s.jenis_kelamin, k.nama AS nama_kelas, ta.nama AS nama_tahun
          FROM t_kelas_siswa ks
          JOIN t_siswa s ON ks.siswa_id = s.id
          JOIN t_kelas k ON ks.kelas_id = k.id
          JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
          $sqlWhere ORDER BY ks.id DESC";
$data = mysqli_query($koneksi, $query);

// Export Excel
if ($action == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_" . date('Ymd') . ".xls");
    echo "<h3>Laporan Penempatan Siswa</h3><table border='1'><tr><th>No</th><th>Tahun Ajaran</th><th>NIS</th><th>Nama Siswa</th><th>L/P</th><th>Kelas</th><th>Status</th></tr>";
    $no = 1;
    while ($r = mysqli_fetch_assoc($data)) {
        echo "<tr><td>{$no}</td><td>{$r['nama_tahun']}</td><td>'{$r['nis']}</td><td>{$r['nama_siswa']}</td><td>{$r['jenis_kelamin']}</td><td>{$r['nama_kelas']}</td><td>".($r['status_aktif'] == 1 ? 'Aktif' : 'Tidak Aktif')."</td></tr>";
        $no++;
    }
    echo "</table>";
    exit;
}

$listTahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
$listKelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; overflow: hidden; }
        .sidebar { min-height: 100vh; background-color: #212529; color: #fff; position: fixed; width: 250px; top: 0; left: 0; overflow-y: auto; z-index: 1000; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .main-content { margin-left: 250px; height: 100vh; overflow-y: auto; padding: 20px; }
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0; padding: 0; overflow: visible; height: auto; }
            body { overflow: visible; }
        }
    </style>
</head>
<body <?= $action == 'print' ? 'onload="window.print()"' : '' ?>>

    <!-- SIDEBAR -->
    <div class="sidebar d-flex flex-column p-3">
        <h4 class="text-white mb-4">Pelanggaran Siswa</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-1"><a href="dashboard.php">Dashboard</a></li>
            <li class="nav-item mb-1"><a href="kelola_guru.php">Kelola Guru</a></li>
            <li class="nav-item mb-1"><a href="kelola_siswa.php">Kelola Siswa</a></li>
            <li class="nav-item mb-1"><a href="kelola_kelas.php">Kelola Kelas</a></li>
            <li class="nav-item mb-1"><a href="kelola_tahun_ajaran.php">Kelola Tahun Ajaran</a></li>
            <li class="nav-item mb-1"><a href="penempatan_siswa.php">Penempatan Siswa</a></li>
            <li class="nav-item mb-1"><a href="kelola_wali_kelas.php">Kelola Wali Kelas</a></li>
            <li class="nav-item mb-1"><a href="kelola_kategori_pelanggaran.php">Kelola Kategori Pelanggaran</a></li>
            <li class="nav-item mb-1"><a href="laporan.php" class="active">laporan</a></li>
            <li class="nav-item mb-1"><a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a></li>
           
            <li class="nav-item mb-1"><a href="about.php">About Me</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Laporan Penempatan Siswa</h2>
        <a href="dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a><hr>

        <!-- FILTER & AKSI -->
        <div class="card shadow-sm mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tahun Ajaran:</label>
                        <select name="tahun_id" class="form-control">
                            <option value="">-- Semua Tahun Ajaran --</option>
                            <?php while($t = mysqli_fetch_assoc($listTahun)): ?>
                                <option value="<?= $t['id'] ?>" <?= $tahun_id == $t['id'] ? 'selected':'' ?>><?= $t['nama'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelas:</label>
                        <select name="kelas_id" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            <?php while($k = mysqli_fetch_assoc($listKelas)): ?>
                                <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected':'' ?>><?= $k['nama'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="laporan.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
                <hr>
                <div>
                    <a href="laporan.php?<?= http_build_query(array_merge($_GET, ['action'=>'print'])) ?>" target="_blank" class="btn btn-dark btn-sm">🖨️ Cetak / Print</a>
                    <a href="laporan.php?<?= http_build_query(array_merge($_GET, ['action'=>'excel'])) ?>" class="btn btn-success btn-sm ms-2">📊 Export Excel</a>
                </div>
            </div>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tahun Ajaran</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>L/P</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($data) > 0): ?>
                            <?php $no=1; while($r = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $r['nama_tahun']; ?></td>
                                <td><?= $r['nis']; ?></td>
                                <td><?= $r['nama_siswa']; ?></td>
                                <td class="text-center"><?= $r['jenis_kelamin']; ?></td>
                                <td><?= $r['nama_kelas']; ?></td>
                                <td class="text-center">
                                    <?= $r['status_aktif'] == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>'; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data laporan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>