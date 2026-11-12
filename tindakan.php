
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';

// Periksa login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data session
$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_user'] ?? $_SESSION['name'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? '';

// Admin dan guru boleh mengakses
if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit();
}

// Fungsi keamanan output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$pesan = '';
$error = '';

// Proses simpan tindakan
if (isset($_POST['update'])) {

    $id = (int)($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = $_POST['status'] ?? '';

    $status_valid = ['Pending', 'Proses', 'Selesai'];

    if ($id <= 0) {
        $error = "ID pelanggaran tidak valid!";
    } elseif ($tindakan === '') {
        $error = "Tindakan tidak boleh kosong!";
    } elseif (!in_array($status, $status_valid, true)) {
        $error = "Status tidak valid!";
    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_siswa
             SET tindakan = ?, status = ?
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $tindakan,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header("Location: tindakan.php?pesan=berhasil");
                exit();
            } else {
                $error = "Gagal menyimpan tindakan!";
                mysqli_stmt_close($stmt);
            }

        } else {
            $error = "Query database gagal!";
        }
    }
}

// Notifikasi
if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil') {
    $pesan = "Tindakan berhasil diperbarui!";
}

// Ambil data pelanggaran
$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM t_pelanggaran_siswa
     WHERE status IS NULL
        OR status = ''
        OR status != 'Selesai'
     ORDER BY tanggal DESC, id DESC"
);

if (!$query) {
    $error = "Gagal mengambil data: " . mysqli_error($koneksi);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tindakan - Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: #212529;
            color: white;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 5px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: white;
            background: #0d6efd;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column p-3">

    <h4 class="text-white mb-4">Pelanggaran</h4>

    <ul class="nav nav-pills flex-column mb-auto">

        <li class="nav-item mb-1">
            <a href="dashboard.php">Dashboard</a>
        </li>

        <?php if ($role === 'admin'): ?>

            <li class="nav-item mb-1">
                <a href="kelola_guru.php">Kelola Guru</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_siswa.php">Kelola Siswa</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_kelas.php">Kelola Kelas</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_tahun_ajaran.php">Kelola Tahun Ajaran</a>
            </li>

            <li class="nav-item mb-1">
                <a href="penempatan_siswa.php">Penempatan Siswa</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_wali_kelas.php">Kelola Wali Kelas</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_kategori_pelanggaran.php">Kelola Kategori Pelanggaran</a>
            </li>

            <li class="nav-item mb-1">
                <a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a>
            </li>

        <?php endif; ?>

        <?php if ($role === 'guru'): ?>

            <li class="nav-item mb-1">
                <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
            </li>

            <li class="nav-item mb-1">
                <a href="tindakan.php" class="active">Tindakan</a>
            </li>

            <li class="nav-item mb-1">
                <a href="riwayat.php">Riwayat</a>
            </li>

            <li class="nav-item mb-1">
                <a href="rekap_poin.php">Rekap Poin</a>
            </li>

        <?php endif; ?>

        <li class="nav-item mb-1">
            <a href="laporan.php">Laporan</a>
        </li>

        <li class="nav-item mb-1">
            <a href="about_me.php">About Me</a>
        </li>

    </ul>

    <hr>

    <a href="logout.php" class="text-danger">Logout</a>

</div>


<!-- KONTEN UTAMA -->
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Tindakan Pelanggaran Siswa</h2>

        <span class="badge bg-secondary">
            <?= e($nama_user); ?>
            (<?= e(ucfirst($role)); ?>)
        </span>

    </div>


    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?= e($pesan); ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= e($error); ?>
        </div>

    <?php endif; ?>


    <!-- TABEL DATA -->
    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="mb-3">
                Daftar Tindakan Pelanggaran
            </h5>

            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle">

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
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($query && mysqli_num_rows($query) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <?php $form_id = 'form_tindakan_' . (int)$row['id']; ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= e($row['tanggal']); ?>
                                </td>

                                <td>
                                    <?= e($row['nama_siswa']); ?>
                                </td>

                                <td>
                                    <?= e($row['nama_kelas']); ?>
                                </td>

                                <td>
                                    <?= e($row['nama_pelanggaran']); ?>
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        <?= e($row['poin']); ?>
                                    </span>
                                </td>

                                <td style="min-width:180px;">

                                    <textarea
                                        name="tindakan"
                                        form="<?= $form_id; ?>"
                                        class="form-control form-control-sm"
                                        rows="2"
                                        placeholder="Masukkan tindakan..."
                                        required><?= e($row['tindakan'] ?? ''); ?></textarea>

                                </td>

                                <td style="min-width:130px;">

                                    <select
                                        name="status"
                                        form="<?= $form_id; ?>"
                                        class="form-select form-select-sm"
                                        required>

                                        <option value="Pending"
                                            <?= ($row['status'] === 'Pending' || empty($row['status'])) ? 'selected' : ''; ?>>
                                            Pending
                                        </option>

                                        <option value="Proses"
                                            <?= ($row['status'] === 'Proses') ? 'selected' : ''; ?>>
                                            Proses
                                        </option>

                                        <option value="Selesai"
                                            <?= ($row['status'] === 'Selesai') ? 'selected' : ''; ?>>
                                            Selesai
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <form
                                        id="<?= $form_id; ?>"
                                        method="POST"
                                        action="tindakan.php">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row['id']; ?>">

                                        <button
                                            type="submit"
                                            name="update"
                                            class="btn btn-primary btn-sm">
                                            Simpan
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Belum ada data tindakan pelanggaran.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
