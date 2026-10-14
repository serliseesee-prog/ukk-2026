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

// Validasi hak akses (Hanya Admin)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

$nama_user = $_SESSION['nama_user'] ?? 'Admin';
$error = '';
$pesan = $_SESSION['pesan'] ?? '';
unset($_SESSION['pesan']);

// Fungsi pengaman output HTML
function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Inisialisasi data form edit
$edit = [
    'id' => '',
    'pelanggaran_kategori_id' => '',
    'kode' => '',
    'nama' => '',
    'poin' => '',
    'deskripsi' => '',
    'status_aktif' => 1
];

// 1. PROSES SIMPAN (TAMBAH & EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $pelanggaran_kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $poin = (int)($_POST['poin'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    // Simpan data sementara ke array edit jika terjadi error
    $edit = [
        'id' => $id,
        'pelanggaran_kategori_id' => $pelanggaran_kategori_id,
        'kode' => $kode,
        'nama' => $nama,
        'poin' => $poin,
        'deskripsi' => $deskripsi,
        'status_aktif' => $status_aktif
    ];

    if ($pelanggaran_kategori_id <= 0 || $kode === '' || $nama === '') {
        $error = "Kategori, Kode, dan Nama Jenis Pelanggaran wajib diisi.";
    } else {
        if ($id > 0) {
            // Query Update data tabel t_pelanggaran
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE t_pelanggaran 
                 SET pelanggaran_kategori_id = ?, kode = ?, nama = ?, poin = ?, deskripsi = ?, status_aktif = ?, updated_at = NOW() 
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, "issisii", $pelanggaran_kategori_id, $kode, $nama, $poin, $deskripsi, $status_aktif, $id);
        } else {
            // Query Insert data baru
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_pelanggaran (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            mysqli_stmt_bind_param($stmt, "issisi", $pelanggaran_kategori_id, $kode, $nama, $poin, $deskripsi, $status_aktif);
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan'] = $id > 0 ? "Jenis pelanggaran berhasil diperbarui." : "Jenis pelanggaran berhasil ditambahkan.";
            mysqli_stmt_close($stmt);
            header("Location: kelola_jenis_pelanggaran.php");
            exit();
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }

        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

// 2. PROSES HAPUS DATA
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    if ($id_hapus > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM t_pelanggaran WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_hapus);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan'] = "Jenis pelanggaran berhasil dihapus.";
        } else {
            $_SESSION['pesan'] = "Gagal menghapus data. Jenis pelanggaran ini mungkin sedang digunakan pada laporan pelanggaran siswa.";
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: kelola_jenis_pelanggaran.php");
    exit();
}

// 3. AMBIL DATA UNTUK FORM EDIT
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    if ($id_edit > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT id, pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif FROM t_pelanggaran WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_edit);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);
        
        if ($result_edit && mysqli_num_rows($result_edit) > 0) {
            $edit = mysqli_fetch_assoc($result_edit);
        }
        mysqli_stmt_close($stmt);
    }
}

// 4. AMBIL DATA KATEGORI UNTUK DROPDOWN
$data_kategori = mysqli_query($koneksi, "SELECT id, nama FROM t_pelanggaran_kategori WHERE status_aktif = 1 ORDER BY nama ASC");

// 5. AMBIL SEMUA DATA JENIS PELANGGARAN BESERTA KATEGORINYA
$query_data = "
    SELECT p.id, p.kode, p.nama, p.poin, p.deskripsi, p.status_aktif, pk.nama AS nama_kategori
    FROM t_pelanggaran p
    LEFT JOIN t_pelanggaran_kategori pk ON p.pelanggaran_kategori_id = pk.id
    ORDER BY p.id DESC
";
$data = mysqli_query($koneksi, $query_data);
if (!$data) {
    die("Gagal mengambil data jenis pelanggaran: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jenis Pelanggaran - Sistem Pelanggaran Siswa</title>
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
                <a href="laporan.php" class="nav-link text-white">Laporan</a>
                <a href="kelola_jenis_pelanggaran.php" class="nav-link active" aria-current="page">Kelola Jenis Pelanggaran</a>
                <a href="cetak/export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="about_me.php" class="nav-link text-white">About Me</a>
            </nav>
            
            <hr class="border-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="text-primary mb-1">Kelola Jenis Pelanggaran</h2>
            <p class="text-secondary">Tambah, ubah, dan atur daftar jenis pelanggaran beserta bobot poinnya.</p>

            <!-- Notifikasi Pesan Sukses -->
            <?php if ($pesan !== ''): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= e($pesan); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Notifikasi Pesan Error -->
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH / EDIT JENIS PELANGGARAN -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <?= !empty($edit['id']) ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="kelola_jenis_pelanggaran.php">
                        <input type="hidden" name="id" value="<?= e($edit['id']); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pelanggaran_kategori_id" class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
                                <select name="pelanggaran_kategori_id" id="pelanggaran_kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php while ($kat = mysqli_fetch_assoc($data_kategori)): ?>
                                        <option value="<?= $kat['id']; ?>" <?= (int)$edit['pelanggaran_kategori_id'] === (int)$kat['id'] ? 'selected' : ''; ?>>
                                            <?= e($kat['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="kode" class="form-label">Kode Pelanggaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode" name="kode" 
                                       value="<?= e($edit['kode']); ?>" placeholder="Contoh: PLG-001" maxlength="30" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="poin" class="form-label">Jumlah Poin</label>
                                <input type="number" class="form-control" id="poin" name="poin" 
                                       value="<?= e($edit['poin']); ?>" placeholder="Contoh: 10" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Pelanggaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?= e($edit['nama']); ?>" placeholder="Contoh: Terlambat Masuk Sekolah" maxlength="150" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Keterangan detail pelanggaran..."><?= e($edit['deskripsi']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="status_aktif" name="status_aktif" value="1" 
                                    <?= (int)$edit['status_aktif'] === 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status_aktif">
                                    Status Aktif
                                </label>
                            </div>
                        </div>

                        <button type="submit" name="simpan" class="btn btn-primary">
                            <?= !empty($edit['id']) ? 'Simpan Perubahan' : 'Simpan Data'; ?>
                        </button>

                        <?php if (!empty($edit['id'])): ?>
                            <a href="kelola_jenis_pelanggaran.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- TABEL DAFTAR JENIS PELANGGARAN -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    Daftar Jenis Pelanggaran
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Kode</th>
                                    <th>Nama Pelanggaran</th>
                                    <th>Kategori</th>
                                    <th width="8%" class="text-center">Poin</th>
                                    <th width="12%">Status</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($data) > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while ($r = mysqli_fetch_assoc($data)): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><code><?= e($r['kode']); ?></code></td>
                                            <td><?= e($r['nama']); ?></td>
                                            <td><?= e($r['nama_kategori'] ?? '-'); ?></td>
                                            <td class="text-center"><span class="badge bg-danger"><?= e($r['poin'] ?? 0); ?></span></td>
                                            <td>
                                                <?php if ((int)$r['status_aktif'] === 1): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="kelola_jenis_pelanggaran.php?edit=<?= (int)$r['id']; ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                                <a href="kelola_jenis_pelanggaran.php?hapus=<?= (int)$r['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus jenis pelanggaran ini?');">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-3">Belum ada data jenis pelanggaran.</td>
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