<?php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$nama_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : (isset($_SESSION['name']) ? $_SESSION['name'] : '');
$is_wali_kelas = isset($_SESSION['is_wali_kelas']) && $_SESSION['is_wali_kelas'] === true;

if ($role === 'guru' && !$is_wali_kelas) {
    echo "<script>alert('Akses ditolak!'); window.location='dashboard.php';</script>";
    exit();
}

$where_arr = [];
$kelas = '';

// Filter hak akses wali kelas berdasarkan relasi t_wali_kelas
if ($role !== 'admin') {
    $user_id_esc = mysqli_real_escape_string($koneksi, $user_id);
    $nama_user_esc = mysqli_real_escape_string($koneksi, $nama_user);
    
    // Ambil ID dan Nama Kelas yang diampu oleh Wali Kelas yang sedang login
    $q_wk = mysqli_query($koneksi, "SELECT k.id, k.nama FROM t_wali_kelas tw JOIN t_kelas k ON tw.kelas_id = k.id JOIN t_guru g ON tw.guru_id = g.id WHERE (g.user_id = '$user_id_esc' OR g.nama = '$nama_user_esc') AND tw.status_aktif = 1 LIMIT 1");
    if ($q_wk && mysqli_num_rows($q_wk) > 0) {
        $data_wk = mysqli_fetch_assoc($q_wk);
        $kelas_id = $data_wk['id'];
        $kelas = $data_wk['nama'];
        
        // Filter berdasarkan kelas_id atau nama_kelas agar lebih akurat
        $where_arr[] = "(ps.kelas_id = '$kelas_id' OR ps.nama_kelas = '" . mysqli_real_escape_string($koneksi, $kelas) . "')";
    } else {
        // Jika tidak ditemukan sebagai wali kelas, paksa query kosong agar tidak menampilkan data orang lain
        $where_arr[] = "1 = 0";
    }
}

// Tangkap nilai filter tanggal
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
if (!empty($filter_tanggal)) {
    $filter_tanggal_esc = mysqli_real_escape_string($koneksi, $filter_tanggal);
    $where_arr[] = "DATE(ps.tanggal) = '$filter_tanggal_esc'";
}

// Gabungkan klausa WHERE jika ada kondisi
$where_clause = "";
if (count($where_arr) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where_arr);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. EXPORT EXCEL SEMUA
if ($action === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Pelanggaran_Kelas_" . ($kelas ? $kelas : 'Semua') . ".xls");
    $q = mysqli_query($koneksi, "SELECT ps.* FROM t_pelanggaran_siswa ps $where_clause ORDER BY ps.tanggal DESC");
    echo "<table border='1'><tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Guru</th></tr>";
    $no = 1; 
    while($r = mysqli_fetch_assoc($q)){ 
        echo "<tr><td>".$no."</td><td>{$r['tanggal']}</td><td>{$r['nama_siswa']}</td><td>{$r['nama_kelas']}</td><td>{$r['nama_pelanggaran']}</td><td>{$r['poin']}</td><td>{$r['nama_guru']}</td></tr>"; 
        $no++; 
    }
    echo "</table>"; 
    exit();
}

// 2. EXPORT EXCEL SATUAN
if ($action === 'excel_satuan' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM t_pelanggaran_siswa WHERE id = '$id' LIMIT 1"));
    if(!$d) exit("Data tidak ditemukan.");
    
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Detail_Pelanggaran_{$d['nama_siswa']}.xls");
    
    echo "<table border='1'>";
    echo "<tr><th colspan='2' style='background:#f2f2f2;'>DETAIL PELANGGARAN SISWA</th></tr>";
    echo "<tr><td><strong>Tanggal</strong></td><td>{$d['tanggal']}</td></tr>";
    echo "<tr><td><strong>Nama Siswa</strong></td><td>{$d['nama_siswa']}</td></tr>";
    echo "<tr><td><strong>Kelas</strong></td><td>{$d['nama_kelas']}</td></tr>";
    echo "<tr><td><strong>Pelanggaran</strong></td><td>{$d['nama_pelanggaran']}</td></tr>";
    echo "<tr><td><strong>Poin</strong></td><td>{$d['poin']}</td></tr>";
    echo "<tr><td><strong>Guru Pencatat</strong></td><td>{$d['nama_guru']}</td></tr>";
    echo "</table>";
    exit();
}

// 3. CETAK SEMUA
if ($action === 'cetak_semua') {
    $q = mysqli_query($koneksi, "SELECT ps.* FROM t_pelanggaran_siswa ps $where_clause ORDER BY ps.tanggal DESC");
    echo "<body onload='window.print()'>
    <div style='margin-bottom: 15px;' class='no-print'>
        <a href='laporan.php' style='text-decoration: none; padding: 6px 12px; background: #0d6efd; color: #fff; border-radius: 4px; display: inline-block;'>« Kembali ke Laporan</a>
        <button onclick='window.print()' style='padding: 6px 12px; cursor: pointer; margin-left: 5px;'>🖨️ Cetak Ulang</button>
    </div>
    <style>@media print { .no-print { display: none; } }</style>
    <h3>Laporan Pelanggaran Siswa " . ($kelas ? "Kelas " . htmlspecialchars($kelas) : "") . "</h3><table border='1' width='100%' style='border-collapse:collapse; font-family:Arial; font-size:12px;'><tr style='background:#eee;'><th>No</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Guru</th></tr>";
    $no = 1; 
    while($r = mysqli_fetch_assoc($q)){ 
        echo "<tr><td>".$no."</td><td>{$r['tanggal']}</td><td>{$r['nama_siswa']}</td><td>{$r['nama_kelas']}</td><td>{$r['nama_pelanggaran']}</td><td>{$r['poin']}</td><td>{$r['nama_guru']}</td></tr>"; 
        $no++; 
    }
    echo "</table></body>"; 
    exit();
}

// 4. CETAK SATUAN
if ($action === 'cetak_satuan' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM t_pelanggaran_siswa WHERE id = '$id' LIMIT 1"));
    if(!$d) exit("Data tidak ditemukan.");
    echo "<body onload='window.print()'>
    <div style='width:600px; margin:10px auto;' class='no-print'>
        <a href='laporan.php' style='text-decoration: none; padding: 6px 12px; background: #0d6efd; color: #fff; border-radius: 4px; display: inline-block;'>« Kembali ke Laporan</a>
        <button onclick='window.print()' style='padding: 6px 12px; cursor: pointer; margin-left: 5px;'>🖨️ Cetak Ulang</button>
    </div>
    <style>@media print { .no-print { display: none; } }</style>
    <div style='width:600px; margin:20px auto; font-family:Arial; border:1px solid #000; padding:20px;'>
    <h3 style='text-align:center;'>SURAT BUKTI PELANGGARAN SISWA</h3><hr>
    <table>
    <tr><td width='150'><strong>Tanggal</strong></td><td>: {$d['tanggal']}</td></tr>
    <tr><td><strong>Nama Siswa</strong></td><td>: {$d['nama_siswa']}</td></tr>
    <tr><td><strong>Kelas</strong></td><td>: {$d['nama_kelas']}</td></tr>
    <tr><td><strong>Pelanggaran</strong></td><td>: {$d['nama_pelanggaran']}</td></tr>
    <tr><td><strong>Poin</strong></td><td>: <span style='color:red;'>{$d['poin']} Poin</span></td></tr>
    <tr><td><strong>Pencatat</strong></td><td>: {$d['nama_guru']}</td></tr>
    </table><br><br>
    <div style='float:right; text-align:center;'>Mengetahui,<br><br><br><b>( _________________ )</b></div>
    </div></body>"; 
    exit();
}

$query_laporan = mysqli_query($koneksi, "SELECT ps.* FROM t_pelanggaran_siswa ps $where_clause ORDER BY ps.tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #f8f9fa; } .sidebar { min-height: 100vh; background: #212529; color: #fff; position: fixed; width: 250px; top: 0; left: 0; } .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px 20px; } .sidebar a:hover, .sidebar a.active { color: #fff; background: #0d6efd; } .main-content { margin-left: 250px; padding: 30px; }</style>
</head>
<body>
<div class="container-fluid"><div class="row">
    <div class="sidebar d-flex flex-column p-3">
        <h4 class="text-white mb-4">Pelanggaran</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-1"><a href="dashboard.php">Dashboard</a></li>
            <?php if ($role === 'admin'): ?>
                <li class="nav-item mb-1"><a href="kelola_guru.php">Kelola Guru</a></li>
                <li class="nav-item mb-1"><a href="kelola_siswa.php">Kelola Siswa</a></li>
                <li class="nav-item mb-1"><a href="kelola_kelas.php">Kelola Kelas</a></li>
                <li class="nav-item mb-1"><a href="kelola_tahun_ajaran.php">Kelola Tahun Ajaran</a></li>
                <li class="nav-item mb-1"><a href="penempatan_siswa.php">Penempatan Siswa</a></li>
                <li class="nav-item mb-1"><a href="kelola_wali_kelas.php">Kelola Wali Kelas</a></li>
                <li class="nav-item mb-1"><a href="kelola_kategori_pelanggaran.php">Kelola Kategori Pelanggaran</a></li>
                <li class="nav-item mb-1"><a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a></li>
                <li class="nav-item mb-1"><a href="laporan.php" class="active">Laporan</a></li>
            <?php endif; ?>
            <?php if ($role === 'guru'): ?>
                <li class="nav-item mb-1"><a href="catat_pelanggaran.php">Catat Pelanggaran</a></li>
                <li class="nav-item mb-1"><a href="tindakan.php">Tindakan</a></li>
                <li class="nav-item mb-1"><a href="riwayat.php">Riwayat</a></li>
                <li class="nav-item mb-1"><a href="rekap_poin.php">Rekap Poin</a></li>
                <?php if ($is_wali_kelas): ?><li class="nav-item mb-1"><a href="laporan.php" class="active">Laporan</a></li><?php endif; ?>
            <?php endif; ?>
            <li class="nav-item mb-1"><a href="about_me.php">About Me</a></li>
        </ul>
        <hr><a href="logout.php" class="text-danger">Logout</a>
    </div>

    <div class="col-md-9 col-lg-10 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Halaman Laporan Pelanggaran Siswa</h2>
            <span class="badge bg-secondary"><?= htmlspecialchars($nama_user); ?> (<?= ucfirst($role); ?><?php if(!empty($kelas)) echo " - Kelas $kelas"; ?>)</span>
        </div>

        <!-- FORM FILTER TANGGAL -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Filter Berdasarkan Tanggal Pelanggaran</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($filter_tanggal); ?>">
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <a href="laporan.php" class="btn btn-secondary">🔄 Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title m-0">Data Pelanggaran <?php if(!empty($kelas)) echo "Kelas " . htmlspecialchars($kelas); ?></h5>
                <div>
                    <?php 
                        $query_string = !empty($filter_tanggal) ? "&tanggal=" . urlencode($filter_tanggal) : "";
                    ?>
                    <a href="laporan.php?action=excel<?= $query_string; ?>" class="btn btn-success btn-sm">📁 Export Excel</a>
                    <a href="laporan.php?action=cetak_semua<?= $query_string; ?>" target="_blank" class="btn btn-secondary btn-sm">🖨️ Cetak</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Guru</th><th class="text-center" style="width: 170px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($query_laporan && mysqli_num_rows($query_laporan) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_laporan)): ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $row['tanggal']; ?></td>
                                    <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_pelanggaran']); ?></td>
                                    <td><span class="badge bg-danger"><?= $row['poin']; ?></span></td>
                                    <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="laporan.php?action=cetak_satuan&id=<?= $row['id']; ?>" target="_blank" class="btn btn-primary btn-sm" title="Cetak">🖨️ Cetak</a>
                                            <a href="laporan.php?action=excel_satuan&id=<?= $row['id']; ?>" class="btn btn-success btn-sm" title="Excel">📁 Excel</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php $no++; endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">Belum ada data pelanggaran untuk kelas Anda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div></div>
</body>
</html>