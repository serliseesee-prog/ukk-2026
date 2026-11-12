
<?php
require_once __DIR__ . '/koneksi.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// CEK SESSION ADMIN
$user_id = $_SESSION['user_id'] ?? '';
$role = strtolower(trim($_SESSION['role'] ?? ''));

if (empty($user_id) || $role !== 'admin') {
    header("Location: login.php");
    exit();
}

// AMBIL TAHUN AJARAN AKTIF
$query_ta_aktif = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran WHERE status_aktif = 1 LIMIT 1");

if (!$query_ta_aktif) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahun_aktif = mysqli_fetch_assoc($query_ta_aktif);
$tahun_ajaran_id_aktif = $tahun_aktif['id'] ?? '';
$nama_tahun_aktif = $tahun_aktif['nama'] ?? 'Tidak ada Tahun Ajaran Aktif';

// PROSES SIMPAN DAN EDIT
if (isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? $tahun_ajaran_id_aktif);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $status_aktif = 1;

    if ($siswa_id > 0 && $tahun_ajaran_id > 0 && $kelas_id > 0) {

        if ($id > 0) {
            // UPDATE PENEMPATAN
            $stmt = mysqli_prepare($koneksi, "UPDATE t_kelas_siswa SET siswa_id=?, tahun_ajaran_id=?, kelas_id=?, status_aktif=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "iiiii", $siswa_id, $tahun_ajaran_id, $kelas_id, $status_aktif, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: penempatan_siswa.php?pesan=sukses_edit");
            exit();

        } else {
            // CEK DUPLIKASI
            $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_kelas_siswa WHERE siswa_id=? AND tahun_ajaran_id=?");
            mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $tahun_ajaran_id);
            mysqli_stmt_execute($stmt);
            $result_cek = mysqli_stmt_get_result($stmt);
            $duplikat = mysqli_num_rows($result_cek) > 0;
            mysqli_stmt_close($stmt);

            if ($duplikat) {
                header("Location: penempatan_siswa.php?pesan=duplikat");
                exit();
            }

            // INSERT PENEMPATAN
            $stmt = mysqli_prepare($koneksi, "INSERT INTO t_kelas_siswa (siswa_id, tahun_ajaran_id, kelas_id, status_aktif) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiii", $siswa_id, $tahun_ajaran_id, $kelas_id, $status_aktif);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: penempatan_siswa.php?pesan=sukses_tambah");
            exit();
        }
    }
}

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id_penempatan = (int)$_GET['hapus'];

    if ($id_penempatan > 0) {
        mysqli_query($koneksi, "DELETE FROM t_kelas_siswa WHERE id='$id_penempatan'");
    }

    header("Location: penempatan_siswa.php?pesan=sukses_hapus");
    exit();
}

// AMBIL DATA EDIT
$edit_id = (int)($_GET['edit'] ?? 0);
$edit = null;

if ($edit_id > 0) {
    $edit_query = mysqli_query($koneksi, "SELECT * FROM t_kelas_siswa WHERE id='$edit_id'");
    $edit = mysqli_fetch_assoc($edit_query);
}

// AMBIL DATA PENEMPATAN
$query_data = "
    SELECT
        ks.*,
        TRIM(s.nama) AS nama_siswa,
        TRIM(s.nis) AS nis,
        TRIM(k.nama) AS nama_kelas,
        TRIM(ta.nama) AS nama_tahun
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s ON ks.siswa_id = s.id
    LEFT JOIN t_kelas k ON ks.kelas_id = k.id
    LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    ORDER BY ks.id ASC
";

$data_penempatan = mysqli_query($koneksi, $query_data);

if (!$data_penempatan) {
    die("Gagal mengambil data penempatan: " . mysqli_error($koneksi));
}

// DATA DROPDOWN
$data_siswa = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE status_aktif=1 ORDER BY nama ASC");
$data_kelas = mysqli_query($koneksi, "SELECT * FROM t_kelas WHERE status_aktif=1 ORDER BY nama ASC");

if (!$data_siswa || !$data_kelas) {
    die("Gagal mengambil data siswa atau kelas: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
            padding: 10px 20px;
            border-radius: 5px;
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
        <li class="nav-item mb-1"><a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a></li>
        <li class="nav-item mb-1"><a href="laporan.php">Laporan</a></li>
        <li class="nav-item mb-1"><a href="about_me.php">About Me</a></li>
    </ul>

    <hr>
    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN UTAMA -->
<div class="main-content">

    <h2>Penempatan Siswa</h2>

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['pesan'])): ?>

        <?php if ($_GET['pesan'] == 'duplikat'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal!</strong>
                Siswa tersebut sudah memiliki penempatan pada tahun ajaran aktif ini.
                Silakan gunakan tombol Edit jika ingin memindahkan kelas.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'sukses_tambah'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Berhasil!</strong> Penempatan siswa berhasil ditambahkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Berhasil!</strong> Data penempatan berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Berhasil!</strong> Data penempatan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
            <?= $edit ? 'Edit Penempatan Siswa' : 'Tambah Penempatan Siswa'; ?>
        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id'] ?? ''); ?>">

                <div class="mb-3">
                    <label class="form-label">Siswa</label>

                    <select name="siswa_id" class="form-control" required>
                        <option value="">Pilih Siswa</option>

                        <?php while ($s = mysqli_fetch_assoc($data_siswa)): ?>
                            <option value="<?= (int)$s['id']; ?>"
                                <?= (($edit['siswa_id'] ?? '') == $s['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tahun Ajaran</label>

                    <input type="hidden" name="tahun_ajaran_id"
                        value="<?= htmlspecialchars($edit['tahun_ajaran_id'] ?? $tahun_ajaran_id_aktif); ?>">

                    <input type="text" class="form-control bg-light"
                        value="<?= htmlspecialchars($edit ? ($edit['tahun_ajaran_id'] ? ($nama_tahun_aktif) : $nama_tahun_aktif) : $nama_tahun_aktif); ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>

                    <select name="kelas_id" class="form-control" required>
                        <option value="">Pilih Kelas</option>

                        <?php while ($k = mysqli_fetch_assoc($data_kelas)): ?>
                            <option value="<?= (int)$k['id']; ?>"
                                <?= (($edit['kelas_id'] ?? '') == $k['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k['nama']); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    <?= $edit ? 'Update Data' : 'Simpan'; ?>
                </button>

                <?php if ($edit): ?>
                    <a href="penempatan_siswa.php" class="btn btn-secondary">Batal</a>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">
            Data Penempatan Siswa
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelPenempatan" class="table table-bordered table-striped table-hover align-middle w-100">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $no = 1; ?>

                        <?php while ($r = mysqli_fetch_assoc($data_penempatan)): ?>

                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($r['nis'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($r['nama_siswa'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($r['nama_kelas'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($r['nama_tahun'] ?? ''); ?></td>

                                <td>
                                    <?php if (($r['status_aktif'] ?? 1) == 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="penempatan_siswa.php?edit=<?= (int)$r['id']; ?>"
                                            class="btn btn-sm btn-warning">Edit</a>

                                        <a href="penempatan_siswa.php?hapus=<?= (int)$r['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus penempatan siswa ini?')">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
        language: {
            search: "Cari data:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data penempatan",
            infoEmpty: "Tidak ada data penempatan",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data penempatan tidak ditemukan",
            emptyTable: "Belum ada data penempatan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[0, 'asc']],
        columnDefs: [
            { targets: 0, orderable: true },
            { targets: 6, orderable: false, searchable: false }
        ]
    });
});
</script>

</body>
</html>
