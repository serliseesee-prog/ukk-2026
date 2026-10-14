<?php
// Pengaturan Koneksi Database
$lokasiKoneksi = __DIR__ . '/config/koneksi.php';
if (!file_exists($lokasiKoneksi)) {
    $lokasiKoneksi = __DIR__ . '/koneksi.php';
}
if (!file_exists($lokasiKoneksi)) {
    die("File koneksi.php tidak ditemukan. Periksa kembali struktur folder Anda.");
}
require_once $lokasiKoneksi;

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi hak akses (Hanya Admin / Guru yang bisa akses laporan)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$nama_user = $_SESSION['nama_user'] ?? 'Pengguna';

// Fungsi pengaman output HTML
function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Menangkap parameter filter
$filter_kelas = $_GET['kelas_id'] ?? '';
$filter_tahun = $_GET['tahun_ajaran_id'] ?? '';
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$filter_tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

// Menyusun query dengan kondisi filter
$query = "
    SELECT 
        ps.id,
        ps.tanggal,
        ps.keterangan,
        ps.poin,
        ps.tindakan,
        ps.status,
        s.nis,
        COALESCE(ps.nama_siswa, s.nama) AS nama_siswa,
        COALESCE(ps.nama_kelas, k.nama) AS nama_kelas,
        COALESCE(ps.nama_pelanggaran, p.nama) AS nama_pelanggaran,
        COALESCE(ps.nama_guru, g.nama) AS nama_guru,
        ta.nama AS nama_tahun_ajaran
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    LEFT JOIN t_kelas k ON ps.kelas_id = k.id
    LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    LEFT JOIN t_guru g ON ps.guru_id = g.id
    LEFT JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($filter_kelas !== '') {
    $query .= " AND ps.kelas_id = ?";
    $params[] = (int)$filter_kelas;
    $types .= "i";
}

if ($filter_tahun !== '') {
    $query .= " AND ps.tahun_ajaran_id = ?";
    $params[] = (int)$filter_tahun;
    $types .= "i";
}

if ($filter_tanggal_mulai !== '') {
    $query .= " AND ps.tanggal >= ?";
    $params[] = $filter_tanggal_mulai;
    $types .= "s";
}

if ($filter_tanggal_selesai !== '') {
    $query .= " AND ps.tanggal <= ?";
    $params[] = $filter_tanggal_selesai;
    $types .= "s";
}

$query .= " ORDER BY ps.tanggal DESC, ps.id DESC";

// Eksekusi query dengan Prepared Statement
$stmt = mysqli_prepare($koneksi, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result_laporan = mysqli_stmt_get_result($stmt);

// Ambil data dropdown untuk filter
$data_kelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas WHERE status_aktif = 1 ORDER BY nama ASC");
$data_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran Siswa - Sistem Pelanggaran Siswa</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR NAVIGASI -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="mb-3">Pelanggaran Siswa</h4>
            <div class="text-white-50 small mb-3">Pengguna: <?= e($nama_user); ?></div>
            
            <nav class="nav nav-pills flex-column gap-1">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="kelola_guru.php" class="nav-link text-white">Kelola Guru</a>
                <a href="kelola_siswa.php" class="nav-link text-white">Kelola Siswa</a>
                <a href="kelola_kelas.php" class="nav-link text-white">Kelola Kelas</a>
                <a href="kelola_tahun_ajaran.php" class="nav-link text-white">Kelola Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="kelola_wali_kelas.php" class="nav-link text-white">Kelola Wali Kelas</a>
                <a href="kelola_kategori_pelanggaran.php" class="nav-link text-white">Kelola Kategori Pelanggaran</a>
                <a href="laporan.php" class="nav-link active" aria-current="page">Laporan</a>
                <a href="kelola_jenis_pelanggaran.php" class="nav-link text-white">Kelola Jenis Pelanggaran</a>
                <a href="cetak/export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="about_me.php" class="nav-link text-white">About Me</a>
            </nav>
            
            <hr class="border-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="text-primary mb-1">Laporan Pelanggaran Siswa</h2>
            <p class="text-secondary">Rekapitulasi dan filter data catatan pelanggaran siswa.</p>

            <!-- FORM FILTER LAPORAN -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary text-white">
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="laporan.php" class="row g-3">
                        <div class="col-md-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select">
                                <option value="">-- Semua Tahun Ajaran --</option>
                                <?php while ($ta = mysqli_fetch_assoc($data_tahun)): ?>
                                    <option value="<?= $ta['id']; ?>" <?= $filter_tahun == $ta['id'] ? 'selected' : ''; ?>>
                                        <?= e($ta['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="kelas_id" class="form-label">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="">-- Semua Kelas --</option>
                                <?php while ($k = mysqli_fetch_assoc($data_kelas)): ?>
                                    <option value="<?= $k['id']; ?>" <?= $filter_kelas == $k['id'] ? 'selected' : ''; ?>>
                                        <?= e($k['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="tanggal_mulai" class="form-label">Dari Tanggal</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?= e($filter_tanggal_mulai); ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="tanggal_selesai" class="form-label">Sampai Tanggal</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?= e($filter_tanggal_selesai); ?>">
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <a href="laporan.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABEL HASIL LAPORAN -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Hasil Data Laporan Pelanggaran</span>
                    <button onclick="window.print()" class="btn btn-sm btn-light">Cetak / Print</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Tanggal</th>
                                    <th>NIS & Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th class="text-center">Poin</th>
                                    <th>Guru Pelapor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_laporan && mysqli_num_rows($result_laporan) > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while ($r = mysqli_fetch_assoc($result_laporan)): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= e($r['tanggal']); ?></td>
                                            <td>
                                                <strong><?= e($r['nis'] ?? '-'); ?></strong><br>
                                                <?= e($r['nama_siswa']); ?>
                                            </td>
                                            <td><?= e($r['nama_kelas']); ?></td>
                                            <td><?= e($r['nama_pelanggaran']); ?></td>
                                            <td class="text-center"><span class="badge bg-danger"><?= e($r['poin']); ?></span></td>
                                            <td><?= e($r['nama_guru']); ?></td>
                                            <td>
                                                <?php 
                                                    $status = strtolower($r['status'] ?? 'pending');
                                                    $badge_bg = 'secondary';
                                                    if ($status === 'selesai') $badge_bg = 'success';
                                                    elseif ($status === 'proses') $badge_bg = 'warning text-dark';
                                                ?>
                                                <span class="badge bg-<?= $badge_bg; ?>"><?= e(ucfirst($r['status'] ?? 'Pending')); ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary py-3">Tidak ada data laporan pelanggaran yang sesuai dengan filter.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="dashboard.php" class="btn btn-outline-secondary mt-3">Kembali ke Dashboard</a>
        </main>
    </div>
</div>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>