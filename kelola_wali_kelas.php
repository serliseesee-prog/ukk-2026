
<?php
// KONEKSI DATABASE
$lokasiKoneksi = __DIR__ . '/config/koneksi.php';

if (!file_exists($lokasiKoneksi)) {
    $lokasiKoneksi = __DIR__ . '/koneksi.php';
}

if (!file_exists($lokasiKoneksi)) {
    die("File koneksi.php tidak ditemukan.");
}

require_once $lokasiKoneksi;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HAK AKSES ADMIN
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'admin'
) {
    header("Location: login.php");
    exit();
}

$nama_user = $_SESSION['nama_user'] ?? 'Admin';
$error = '';
$pesan = $_SESSION['pesan'] ?? '';
unset($_SESSION['pesan']);

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// DATA FORM
$edit = [
    'id' => '',
    'tahun_ajaran_id' => '',
    'kelas_id' => '',
    'guru_id' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

// SIMPAN DAN EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $tahun = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas = (int)($_POST['kelas_id'] ?? 0);
    $guru = (int)($_POST['guru_id'] ?? 0);
    $mulai = trim($_POST['tanggal_mulai'] ?? '');
    $selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status = isset($_POST['status_aktif']) ? 1 : 0;

    $edit = [
        'id' => $id,
        'tahun_ajaran_id' => $tahun,
        'kelas_id' => $kelas,
        'guru_id' => $guru,
        'tanggal_mulai' => $mulai,
        'tanggal_selesai' => $selesai,
        'status_aktif' => $status
    ];

    if ($tahun <= 0 || $kelas <= 0 || $guru <= 0) {
        $error = 'Tahun ajaran, kelas, dan guru wajib dipilih.';
    } elseif ($mulai !== '' && $selesai !== '' && $selesai < $mulai) {
        $error = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    } else {
        $tanggalMulai = $mulai !== '' ? $mulai : null;
        $tanggalSelesai = $selesai !== '' ? $selesai : null;

        if ($id > 0) {
            $sql = "UPDATE t_wali_kelas
                    SET tahun_ajaran_id = ?, kelas_id = ?, guru_id = ?,
                        tanggal_mulai = ?, tanggal_selesai = ?,
                        status_aktif = ?
                    WHERE id = ?";
        } else {
            $sql = "INSERT INTO t_wali_kelas
                    (tahun_ajaran_id, kelas_id, guru_id,
                     tanggal_mulai, tanggal_selesai, status_aktif)
                    VALUES (?, ?, ?, ?, ?, ?)";
        }

        $stmt = mysqli_prepare($koneksi, $sql);

        if ($stmt) {
            if ($id > 0) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "iiissii",
                    $tahun,
                    $kelas,
                    $guru,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $status,
                    $id
                );
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    "iiissi",
                    $tahun,
                    $kelas,
                    $guru,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $status
                );
            }

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['pesan'] = $id > 0
                    ? 'Data wali kelas berhasil diperbarui.'
                    : 'Data wali kelas berhasil ditambahkan.';

                mysqli_stmt_close($stmt);
                header("Location: kelola_wali_kelas.php");
                exit();
            } else {
                $error = 'Gagal menyimpan data: ' . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = 'Query gagal: ' . mysqli_error($koneksi);
        }
    }
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];

    if ($idHapus > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM t_wali_kelas WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $idHapus);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan'] = 'Data wali kelas berhasil dihapus.';
        } else {
            $_SESSION['pesan'] =
                'Data tidak dapat dihapus: ' . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: kelola_wali_kelas.php");
    exit();
}

// AMBIL DATA EDIT
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_wali_kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);
    $hasilEdit = mysqli_stmt_get_result($stmt);

    if ($hasilEdit && mysqli_num_rows($hasilEdit) > 0) {
        $edit = mysqli_fetch_assoc($hasilEdit);
    }

    mysqli_stmt_close($stmt);
}

// DATA PILIHAN
$guru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_guru
     WHERE status_aktif = 1 ORDER BY nama ASC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas
     WHERE status_aktif = 1 ORDER BY nama ASC"
);

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran
     ORDER BY nama DESC"
);

// DAFTAR WALI KELAS
$data = mysqli_query(
    $koneksi,
    "SELECT
        wk.id,
        wk.tahun_ajaran_id,
        wk.kelas_id,
        wk.guru_id,
        wk.tanggal_mulai,
        wk.tanggal_selesai,
        wk.status_aktif,
        g.nama AS nama_guru,
        k.nama AS nama_kelas,
        ta.nama AS nama_tahun
     FROM t_wali_kelas wk
     LEFT JOIN t_guru g ON wk.guru_id = g.id
     LEFT JOIN t_kelas k ON wk.kelas_id = k.id
     LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
     ORDER BY wk.id DESC"
);

if (!$guru || !$kelas || !$tahun || !$data) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
        <h4 class="mb-4">Pelanggaran Siswa</h4>
        <p class="text-white-50 small">
            Pengguna: <?= e($nama_user); ?>
        </p>

        <nav class="nav nav-pills flex-column gap-1">
            <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            <a href="kelola_guru.php" class="nav-link text-white">Kelola Guru</a>
            <a href="kelola_siswa.php" class="nav-link text-white">Kelola Siswa</a>
            <a href="kelola_kelas.php" class="nav-link text-white">Kelola Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link text-white">Kelola Tahun Ajaran</a>
            <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
            <a href="kelola_wali_kelas.php" class="nav-link active">Kelola Wali Kelas</a>
            <a href="kelola_kategori_pelanggaran.php" class="nav-link text-white">Kelola Kategori Pelanggaran</a>
            <a href="laporan.php" class="nav-link text-white">Laporan</a>
            <a href="kelola_jenis_pelanggaran.php" class="nav-link text-white">Kelola Jenis Pelanggaran</a>
            <a href="cetak/export.php" class="nav-link text-white">Cetak / Export</a>
            <a href="about_me.php" class="nav-link text-white">About Me</a>
            <hr>
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </nav>
    </aside>

    <!-- KONTEN -->
    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="text-primary">Kelola Wali Kelas</h2>
        <p class="text-secondary">
            Atur guru wali kelas berdasarkan kelas dan tahun ajaran.
        </p>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= e($pesan); ?>
                <button type="button" class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <!-- FORM -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <?= !empty($edit['id']) ? 'Edit Wali Kelas' : 'Tambah Wali Kelas'; ?>
            </div>

            <div class="card-body">
                <form method="POST" action="kelola_wali_kelas.php">
                    <input type="hidden" name="id" value="<?= e($edit['id']); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" class="form-select" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                <?php while ($ta = mysqli_fetch_assoc($tahun)): ?>
                                    <option value="<?= e($ta['id']); ?>"
                                        <?= (string)$edit['tahun_ajaran_id'] ===
                                            (string)$ta['id'] ? 'selected' : ''; ?>>
                                        <?= e($ta['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php while ($k = mysqli_fetch_assoc($kelas)): ?>
                                    <option value="<?= e($k['id']); ?>"
                                        <?= (string)$edit['kelas_id'] ===
                                            (string)$k['id'] ? 'selected' : ''; ?>>
                                        <?= e($k['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Guru Wali Kelas</label>
                            <select name="guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php while ($g = mysqli_fetch_assoc($guru)): ?>
                                    <option value="<?= e($g['id']); ?>"
                                        <?= (string)$edit['guru_id'] ===
                                            (string)$g['id'] ? 'selected' : ''; ?>>
                                        <?= e($g['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Aktif</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       name="status_aktif" id="status_aktif"
                                       value="1"
                                       <?= (int)$edit['status_aktif'] === 1
                                           ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status_aktif">
                                    Aktif
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai"
                                   class="form-control"
                                   value="<?= e($edit['tanggal_mulai']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai"
                                   class="form-control"
                                   value="<?= e($edit['tanggal_selesai']); ?>">
                        </div>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        <?= !empty($edit['id']) ? 'Simpan Perubahan' : 'Simpan'; ?>
                    </button>

                    <?php if (!empty($edit['id'])): ?>
                        <a href="kelola_wali_kelas.php" class="btn btn-secondary">
                            Batal
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- TABEL -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                Daftar Wali Kelas
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Guru</th>
                                <th>Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($data) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($r = mysqli_fetch_assoc($data)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= e($r['nama_guru']); ?></td>
                                    <td><?= e($r['nama_kelas']); ?></td>
                                    <td><?= e($r['nama_tahun']); ?></td>
                                    <td><?= e($r['tanggal_mulai'] ?: '-'); ?></td>
                                    <td><?= e($r['tanggal_selesai'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ((int)$r['status_aktif'] === 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="kelola_wali_kelas.php?edit=<?= (int)$r['id']; ?>"
                                           class="btn btn-sm btn-warning mb-1">Edit</a>
                                        <a href="kelola_wali_kelas.php?hapus=<?= (int)$r['id']; ?>"
                                           class="btn btn-sm btn-danger mb-1"
                                           onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-secondary">
                                    Belum ada data wali kelas.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a href="dashboard.php" class="btn btn-outline-secondary mt-3">
            Kembali ke Dashboard
        </a>
    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
