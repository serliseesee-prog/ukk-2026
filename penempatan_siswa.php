<?php
require_once 'koneksi.php';

// Koneksi PDO agar sesuai dengan skrip sebelumnya (atau bisa disesuaikan dengan mysqli jika diperlukan)
$pdo = new PDO("mysql:host=localhost;dbname=db_ukk_2026;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Tangkap filter
$tahun_id = $_GET['tahun_id'] ?? '';
$kelas_id = $_GET['kelas_id'] ?? '';
$action   = $_GET['action'] ?? '';

$where = []; $params = [];
if ($tahun_id) { $where[] = "ks.tahun_ajaran_id = :th"; $params[':th'] = $tahun_id; }
if ($kelas_id) { $where[] = "ks.kelas_id = :kl"; $params[':kl'] = $kelas_id; }
$sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";

// Query data penempatan siswa
$query = "SELECT ks.*, s.nis, s.nama AS nama_siswa, s.jenis_kelamin, k.nama AS nama_kelas, ta.nama AS nama_tahun
          FROM t_kelas_siswa ks
          JOIN t_siswa s ON ks.siswa_id = s.id
          JOIN t_kelas k ON ks.kelas_id = k.id
          JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
          $sqlWhere ORDER BY ks.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Export Excel
if ($action == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Penempatan_Siswa_" . date('Ymd') . ".xls");
    echo "<h3>Laporan Penempatan Siswa</h3><table border='1'><tr><th>No</th><th>Tahun Ajaran</th><th>NIS</th><th>Nama Siswa</th><th>L/P</th><th>Kelas</th><th>Status</th></tr>";
    foreach ($data as $i => $r) {
        echo "<tr><td>".($i+1)."</td><td>{$r['nama_tahun']}</td><td>'{$r['nis']}</td><td>{$r['nama_siswa']}</td><td>{$r['jenis_kelamin']}</td><td>{$r['nama_kelas']}</td><td>".($r['status_aktif'] ? 'Aktif' : 'Non-Aktif')."</td></tr>";
    }
    echo "</table>";
    exit;
}

$listTahun = $pdo->query("SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC")->fetchAll();
$listKelas = $pdo->query("SELECT id, nama FROM t_kelas ORDER BY nama ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penempatan Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; color: #fff; position: fixed; width: 250px; top: 0; left: 0; overflow-y: auto; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .main-content { margin-left: 250px; padding: 20px; }
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
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
            <li class="nav-item mb-1"><a href="penempatan_siswa.php" class="active">Penempatan Siswa</a></li>
            <li class="nav-item mb-1"><a href="kelola_wali_kelas.php">Kelola Wali Kelas</a></li>
            <li class="nav-item mb-1"><a href="kelola_kategori_pelanggaran.php">Kelola Kategori Pelanggaran</a></li>
            <li class="nav-item mb-1"><a href="laporan.php">laporan</a></li>
            <li class="nav-item mb-1"><a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a></li>
          
            <li class="nav-item mb-1"><a href="about.php">About Me</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Penempatan Siswa</h2>
        <a href="dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a><hr>

        <!-- FORM FILTER & AKSI -->
        <div class="card shadow-sm mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tahun Ajaran:</label>
                        <select name="tahun_id" class="form-control">
                            <option value="">-- Semua Tahun Ajaran --</option>
                            <?php foreach($listTahun as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $tahun_id == $t['id'] ? 'selected':'' ?>><?= $t['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelas:</label>
                        <select name="kelas_id" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach($listKelas as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected':'' ?>><?= $k['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="penempatan_siswa.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
                <hr>
                <div>
                    <a href="penempatan_siswa.php?<?= http_build_query(array_merge($_GET, ['action'=>'print'])) ?>" target="_blank" class="btn btn-dark btn-sm">🖨️ Cetak / Print</a>
                    <a href="penempatan_siswa.php?<?= http_build_query(array_merge($_GET, ['action'=>'excel'])) ?>" class="btn btn-success btn-sm ms-2">📊 Export Excel</a>
                </div>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">Tahun Ajaran</th>
                            <th width="15%">NIS</th>
                            <th width="30%">Nama Siswa</th>
                            <th class="text-center" width="10%">L/P</th>
                            <th width="15%">Kelas</th>
                            <th class="text-center" width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data): foreach ($data as $i => $r): ?>
                        <tr>
                            <td class="text-center"><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($r['nama_tahun']) ?></td>
                            <td><?= htmlspecialchars($r['nis']) ?></td>
                            <td><?= htmlspecialchars($r['nama_siswa']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($r['jenis_kelamin']) ?></td>
                            <td><?= htmlspecialchars($r['nama_kelas']) ?></td>
                            <td class="text-center">
                                <?php if ($r['status_aktif'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data penempatan siswa.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>