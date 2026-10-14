
<?php
// KONEKSI DATABASE
$lokasiKoneksi = __DIR__ . '/config/koneksi.php';

if (!file_exists($lokasiKoneksi)) {
    $lokasiKoneksi = __DIR__ . '/koneksi.php';
}

if (!file_exists($lokasiKoneksi)) {
    die("File koneksi.php tidak ditemukan. Periksa folder config.");
}

require_once $lokasiKoneksi;

// SESSION DAN HAK AKSES
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// FUNGSI MENGAMAN KELUARAN HTML
function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// DATA AWAL FORM
$edit = [
    'id' => '',
    'siswa_id' => '',
    'tahun_ajaran_id' => '',
    'kelas_id' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

// PROSES SIMPAN DAN EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    $edit = [
        'id' => $id,
        'siswa_id' => $siswa_id,
        'tahun_ajaran_id' => $tahun_ajaran_id,
        'kelas_id' => $kelas_id,
        'tanggal_mulai' => $tanggal_mulai,
        'tanggal_selesai' => $tanggal_selesai,
        'status_aktif' => $status_aktif
    ];

    if ($siswa_id <= 0 || $tahun_ajaran_id <= 0 || $kelas_id <= 0) {
        $error = 'Siswa, tahun ajaran, dan kelas wajib dipilih.';
    } elseif (
        $tanggal_mulai !== '' &&
        $tanggal_selesai !== '' &&
        $tanggal_selesai < $tanggal_mulai
    ) {
        $error = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    } else {
        $mulai = $tanggal_mulai !== '' ? $tanggal_mulai : null;
        $selesai = $tanggal_selesai !== '' ? $tanggal_selesai : null;

        if ($id > 0) {
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE t_kelas_siswa
                 SET siswa_id = ?, tahun_ajaran_id = ?, kelas_id = ?,
                     tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiissii",
                $siswa_id,
                $tahun_ajaran_id,
                $kelas_id,
                $mulai,
                $selesai,
                $status_aktif,
                $id
            );
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_kelas_siswa
                 (siswa_id, tahun_ajaran_id, kelas_id,
                  tanggal_mulai, tanggal_selesai, status_aktif)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiissi",
                $siswa_id,
                $tahun_ajaran_id,
                $kelas_id,
                $mulai,
                $selesai,
                $status_aktif
            );
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan'] = $id > 0
                ? 'Penempatan siswa berhasil diperbarui.'
                : 'Penempatan siswa berhasil ditambahkan.';

            mysqli_stmt_close($stmt);
            header("Location: penempatan_siswa.php");
            exit();
        } else {
            $error = 'Data gagal disimpan: ' . mysqli_error($koneksi);
        }

        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    if ($id_hapus > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM t_kelas_siswa WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_hapus);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan'] = 'Penempatan siswa berhasil dihapus.';
        } else {
            $_SESSION['pesan'] =
                'Data gagal dihapus. ' . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: penempatan_siswa.php");
    exit();
}

// PROSES AMBIL DATA EDIT
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    if ($id_edit > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM t_kelas_siswa WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_edit);
        mysqli_stmt_execute($stmt);
        $hasil_edit = mysqli_stmt_get_result($stmt);

        if ($hasil_edit && mysqli_num_rows($hasil_edit) > 0) {
            $edit = mysqli_fetch_assoc($hasil_edit);
        }

        mysqli_stmt_close($stmt);
    }
}

// DATA SISWA
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// DATA TAHUN AJARAN
$tahun_ajaran = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     ORDER BY nama DESC"
);

// DATA KELAS
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// DATA PENEMPATAN SISWA
$data = mysqli_query(
    $koneksi,
    "SELECT
        ks.id,
        ks.siswa_id,
        ks.tahun_ajaran_id,
        ks.kelas_id,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif,
        s.nis,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS nama_tahun_ajaran
     FROM t_kelas_siswa ks
     LEFT JOIN t_siswa s ON ks.siswa_id = s.id
     LEFT JOIN t_kelas k ON ks.kelas_id = k.id
     LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
     ORDER BY ks.id DESC"
);

if (!$siswa || !$tahun_ajaran || !$kelas || !$data) {
    die("Gagal mengambil data database: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa - Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="mb-4">Pelanggaran Siswa</h4>

            <div class="text-white-50 small mb-3">
                Pengguna: <?= e($nama_user); ?>
            </div>

            <nav class="nav nav-pills flex-column gap-1">
                <a href="dashboard.php" class="nav-link text-white">
                    Dashboard
                </a>

                <a href="kelola_guru.php" class="nav-link text-white">
                    Kelola Guru
                </a>

                <a href="kelola_siswa.php" class="nav-link text-white">
                    Kelola Siswa
                </a>

                <a href="kelola_kelas.php" class="nav-link text-white">
                    Kelola Kelas
                </a>

                <a href="kelola_tahun_ajaran.php" class="nav-link text-white">
                    Kelola Tahun Ajaran
                </a>

                <a href="penempatan_siswa.php" class="nav-link active"
                   aria-current="page">
                    Penempatan Siswa
                </a>

                <a href="kelola_wali_kelas.php" class="nav-link text-white">
                    Kelola Wali Kelas
                </a>

                <a href="kelola_kategori_pelanggaran.php"
                   class="nav-link text-white">
                    Kelola Kategori Pelanggaran
                </a>

                <a href="laporan.php" class="nav-link text-white">
                    Laporan
                </a>

                <a href="kelola_jenis_pelanggaran.php"
                   class="nav-link text-white">
                    Kelola Jenis Pelanggaran
                </a>

                <a href="cetak/export.php" class="nav-link text-white">
                    Cetak / Export
                </a>

                <a href="about_me.php" class="nav-link text-white">
                    About Me
                </a>

                <hr>

                <a href="logout.php" class="nav-link text-danger">
                    Logout
                </a>
            </nav>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="text-primary mb-1">Penempatan Siswa</h2>
            <p class="text-secondary">
                Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.
            </p>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?= e($pesan); ?>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>

            <!-- FORM PENEMPATAN -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <?= !empty($edit['id'])
                        ? 'Edit Penempatan Siswa'
                        : 'Tambah Penempatan Siswa'; ?>
                </div>

                <div class="card-body">
                    <form method="POST" action="penempatan_siswa.php">
                        <input type="hidden" name="id"
                               value="<?= e($edit['id']); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>
                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                                        <option value="<?= e($s['id']); ?>"
                                            <?= (string)$edit['siswa_id'] ===
                                                (string)$s['id'] ? 'selected' : ''; ?>>
                                            <?= e($s['nis']); ?> -
                                            <?= e($s['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id"
                                        class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($ta = mysqli_fetch_assoc($tahun_ajaran)): ?>
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
                                <label class="form-label">Status Aktif</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="status_aktif"
                                           name="status_aktif"
                                           value="1"
                                           <?= (int)$edit['status_aktif'] === 1
                                               ? 'checked' : ''; ?>>
                                    <label class="form-check-label"
                                           for="status_aktif">
                                        Penempatan aktif
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

                        <button type="submit" name="simpan"
                                class="btn btn-primary">
                            <?= !empty($edit['id']) ? 'Simpan Perubahan' : 'Simpan'; ?>
                        </button>

                        <?php if (!empty($edit['id'])): ?>
                            <a href="penempatan_siswa.php"
                               class="btn btn-secondary">
                                Batal
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- TABEL DATA -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    Data Penempatan Siswa
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped
                                      table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (mysqli_num_rows($data) > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while ($r = mysqli_fetch_assoc($data)): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= e($r['nis']); ?></td>
                                            <td><?= e($r['nama_siswa']); ?></td>
                                            <td><?= e($r['nama_kelas']); ?></td>
                                            <td><?= e($r['nama_tahun_ajaran']); ?></td>
                                            <td><?= e($r['tanggal_mulai'] ?: '-'); ?></td>
                                            <td><?= e($r['tanggal_selesai'] ?: '-'); ?></td>

                                            <td>
                                                <?php if ((int)$r['status_aktif'] === 1): ?>
                                                    <span class="badge bg-success">
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        Tidak Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <a href="penempatan_siswa.php?edit=<?= (int)$r['id']; ?>"
                                                   class="btn btn-sm btn-warning mb-1">
                                                    Edit
                                                </a>

                                                <a href="penempatan_siswa.php?hapus=<?= (int)$r['id']; ?>"
                                                   class="btn btn-sm btn-danger mb-1"
                                                   onclick="return confirm('Yakin ingin menghapus penempatan siswa ini?')">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-secondary">
                                            Belum ada data penempatan siswa.
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
