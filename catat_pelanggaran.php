
<?php
require_once 'koneksi.php';

// CEK LOGIN DAN ROLE
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (strtolower($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// AMBIL ID GURU BERDASARKAN USER YANG LOGIN
$stmtGuru = mysqli_prepare(
    $koneksi,
    "SELECT id, nama FROM t_guru WHERE user_id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmtGuru, "i", $user_id);
mysqli_stmt_execute($stmtGuru);
$hasilGuru = mysqli_stmt_get_result($stmtGuru);
$guru = mysqli_fetch_assoc($hasilGuru);
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru untuk akun yang login tidak ditemukan.");
}

$guru_id = (int) $guru['id'];
$nama_guru = $guru['nama'] ?? 'Guru';
$pesan = '';
$jenis_pesan = '';

// PROSES SIMPAN CATATAN PELANGGARAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
    $pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');

    // Validasi tanggal
    $tanggalValid = DateTime::createFromFormat('Y-m-d', $tanggal);

    if (
        $siswa_id <= 0 ||
        $pelanggaran_id <= 0 ||
        !$tanggalValid ||
        $tanggalValid->format('Y-m-d') !== $tanggal ||
        $keterangan === '' ||
        $tindakan === ''
    ) {
        $pesan = "Semua kolom wajib diisi dengan benar.";
        $jenis_pesan = "danger";
    } else {
        // Ambil data siswa yang aktif beserta kelas dan tahun ajarannya
        $stmtSiswa = mysqli_prepare(
            $koneksi,
            "SELECT
                s.id,
                s.nama AS nama_siswa,
                ks.kelas_id,
                ks.tahun_ajaran_id,
                k.nama AS nama_kelas
             FROM t_siswa s
             INNER JOIN t_kelas_siswa ks ON ks.siswa_id = s.id
             INNER JOIN t_kelas k ON k.id = ks.kelas_id
             WHERE s.id = ?
               AND s.status_aktif = 1
               AND ks.status_aktif = 1
             ORDER BY ks.id DESC
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmtSiswa, "i", $siswa_id);
        mysqli_stmt_execute($stmtSiswa);
        $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
        $siswa = mysqli_fetch_assoc($hasilSiswa);
        mysqli_stmt_close($stmtSiswa);

        // Ambil data jenis pelanggaran aktif
        $stmtPelanggaran = mysqli_prepare(
            $koneksi,
            "SELECT id, nama, pelanggaran_kategori_id, poin
             FROM t_pelanggaran
             WHERE id = ? AND status_aktif = 1
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmtPelanggaran, "i", $pelanggaran_id);
        mysqli_stmt_execute($stmtPelanggaran);
        $hasilPelanggaran = mysqli_stmt_get_result($stmtPelanggaran);
        $pelanggaran = mysqli_fetch_assoc($hasilPelanggaran);
        mysqli_stmt_close($stmtPelanggaran);

        if (!$siswa) {
            $pesan = "Siswa aktif atau penempatan kelas aktif tidak ditemukan.";
            $jenis_pesan = "danger";
        } elseif (!$pelanggaran) {
            $pesan = "Jenis pelanggaran tidak ditemukan atau tidak aktif.";
            $jenis_pesan = "danger";
        } else {
            $tahun_ajaran_id = (int) $siswa['tahun_ajaran_id'];
            $kelas_id = (int) $siswa['kelas_id'];
            $kategori_id = (int) $pelanggaran['pelanggaran_kategori_id'];
            $poin = (int) $pelanggaran['poin'];

            $nama_siswa = $siswa['nama_siswa'];
            $nama_kelas = $siswa['nama_kelas'];
            $nama_pelanggaran = $pelanggaran['nama'];
            $status = 'tercatat';

            $stmtSimpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_pelanggaran_siswa (
                    tahun_ajaran_id,
                    siswa_id,
                    nama_siswa,
                    kelas_id,
                    nama_kelas,
                    pelanggaran_id,
                    nama_pelanggaran,
                    pelanggaran_kategori_id,
                    guru_id,
                    nama_guru,
                    tanggal,
                    keterangan,
                    poin,
                    tindakan,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmtSimpan,
                "iisisi siisssiss",
                $tahun_ajaran_id,
                $siswa_id,
                $nama_siswa,
                $kelas_id,
                $nama_kelas,
                $pelanggaran_id,
                $nama_pelanggaran,
                $kategori_id,
                $guru_id,
                $nama_guru,
                $tanggal,
                $keterangan,
                $poin,
                $tindakan,
                $status
            );

            if (mysqli_stmt_execute($stmtSimpan)) {
                mysqli_stmt_close($stmtSimpan);
                header("Location: catat_pelanggaran.php?berhasil=1");
                exit();
            } else {
                $pesan = "Gagal menyimpan catatan pelanggaran.";
                $jenis_pesan = "danger";
                mysqli_stmt_close($stmtSimpan);
            }
        }
    }
}

if (isset($_GET['berhasil'])) {
    $pesan = "Catatan pelanggaran berhasil disimpan.";
    $jenis_pesan = "success";
}

// DAFTAR SISWA AKTIF DENGAN PENEMPATAN KELAS AKTIF
$querySiswa = mysqli_query(
    $koneksi,
    "SELECT s.id, s.nis, s.nama, k.nama AS nama_kelas
     FROM t_siswa s
     INNER JOIN t_kelas_siswa ks ON ks.siswa_id = s.id
     INNER JOIN t_kelas k ON k.id = ks.kelas_id
     WHERE s.status_aktif = 1
       AND ks.status_aktif = 1
     ORDER BY s.nama ASC"
);

if (!$querySiswa) {
    die("Gagal mengambil data siswa: " . htmlspecialchars(mysqli_error($koneksi)));
}

// DAFTAR JENIS PELANGGARAN AKTIF
$queryPelanggaran = mysqli_query(
    $koneksi,
    "SELECT id, kode, nama, poin
     FROM t_pelanggaran
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryPelanggaran) {
    die("Gagal mengambil data pelanggaran: " . htmlspecialchars(mysqli_error($koneksi)));
}

// CATATAN YANG DIBUAT GURU LOGIN
$stmtRiwayat = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_siswa, nama_kelas, nama_pelanggaran,
            tanggal, poin, tindakan, status
     FROM t_pelanggaran_siswa
     WHERE guru_id = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param($stmtRiwayat, "i", $guru_id);
mysqli_stmt_execute($stmtRiwayat);
$riwayat = mysqli_stmt_get_result($stmtRiwayat);

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catatan Pelanggaran - Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            overflow-y: auto;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 14px;
            border-radius: 4px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-width: 0;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR GURU -->
<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-white mb-3">Pelanggaran Siswa</h4>

    <nav class="d-flex flex-column gap-1">
        <a href="dashboard.php">Dashboard</a>
        <a href="catat_pelanggaran.php" class="active">Catatan Pelanggaran</a>
        <a href="tindakan.php">Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>
    </nav>

    <hr>

    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN -->
<main class="main-content">

    <h2>Catatan Pelanggaran</h2>
    <p class="text-muted">Guru: <?= e($nama_guru); ?></p>

    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">
        Kembali ke Dashboard
    </a>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= e($jenis_pesan); ?> alert-dismissible fade show">
            <?= e($pesan); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FORM CATAT PELANGGARAN -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Form Catatan Pelanggaran
        </div>

        <div class="card-body">
            <form method="POST" action="catat_pelanggaran.php">

                <div class="mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($s = mysqli_fetch_assoc($querySiswa)): ?>
                            <option value="<?= (int)$s['id']; ?>">
                                <?= e($s['nis']); ?> -
                                <?= e($s['nama']); ?> -
                                <?= e($s['nama_kelas']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Pelanggaran</label>
                    <select name="pelanggaran_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggaran --</option>
                        <?php while ($p = mysqli_fetch_assoc($queryPelanggaran)): ?>
                            <option value="<?= (int)$p['id']; ?>">
                                <?= e($p['kode']); ?> -
                                <?= e($p['nama']); ?> (<?= (int)$p['poin']; ?> poin)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Pelanggaran</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= date('Y-m-d'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                              rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tindakan Awal</label>
                    <textarea name="tindakan" class="form-control"
                              rows="2" required></textarea>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    Simpan Catatan
                </button>
            </form>
        </div>
    </div>

    <!-- TABEL RIWAYAT GURU -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Catatan Pelanggaran yang Saya Buat
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelCatatan"
                       class="table table-bordered table-striped table-hover align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1; ?>
                        <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= e($r['tanggal']); ?></td>
                                <td><?= e($r['nama_siswa']); ?></td>
                                <td><?= e($r['nama_kelas']); ?></td>
                                <td><?= e($r['nama_pelanggaran']); ?></td>
                                <td><?= (int)$r['poin']; ?></td>
                                <td><?= e($r['tindakan']); ?></td>
                                <td><?= e($r['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelCatatan').DataTable({
        language: {
            search: "Cari data:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada catatan pelanggaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[0, 'asc']]
    });
});
</script>

</body>
</html>
