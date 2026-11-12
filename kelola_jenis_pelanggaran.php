
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

// FUNGSI ESCAPE
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// SIMPAN / UPDATE DATA
if (isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode'] ?? '');
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $poin = (int)($_POST['poin'] ?? 0);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '');

    if ($id > 0) {
        $query = "UPDATE t_pelanggaran SET
                  pelanggaran_kategori_id='$kategori_id',
                  kode='$kode',
                  nama='$nama',
                  poin='$poin',
                  deskripsi='$deskripsi'
                  WHERE id='$id'";
    } else {
        $query = "INSERT INTO t_pelanggaran
                  (pelanggaran_kategori_id, kode, nama, poin, deskripsi)
                  VALUES ('$kategori_id', '$kode', '$nama', '$poin', '$deskripsi')";
    }

    if (!mysqli_query($koneksi, $query)) {
        die("Gagal menyimpan data: " . e(mysqli_error($koneksi)));
    }

    header("Location: kelola_jenis_pelanggaran.php");
    exit();
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        $hapus = mysqli_query($koneksi,
            "DELETE FROM t_pelanggaran WHERE id='$id'");

        if (!$hapus) {
            die("Gagal menghapus data: " . e(mysqli_error($koneksi)));
        }
    }

    header("Location: kelola_jenis_pelanggaran.php");
    exit();
}

// DATA EDIT
$edit = [
    'id' => '',
    'pelanggaran_kategori_id' => '',
    'kode' => '',
    'nama' => '',
    'poin' => '',
    'deskripsi' => ''
];

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $resultEdit = mysqli_query($koneksi,
        "SELECT * FROM t_pelanggaran WHERE id='$id'");

    if ($resultEdit && mysqli_num_rows($resultEdit) > 0) {
        $edit = mysqli_fetch_assoc($resultEdit);
    }
}

// DATA KATEGORI AKTIF UNTUK DROPDOWN
$data_kategori = mysqli_query($koneksi,
    "SELECT * FROM t_pelanggaran_kategori
     WHERE status_aktif=1
     ORDER BY nama ASC");

if (!$data_kategori) {
    die("Gagal mengambil kategori: " . e(mysqli_error($koneksi)));
}

// DATA JENIS PELANGGARAN
$data = mysqli_query($koneksi,
    "SELECT p.*, pk.nama AS nama_kategori
     FROM t_pelanggaran p
     LEFT JOIN t_pelanggaran_kategori pk
     ON p.pelanggaran_kategori_id = pk.id
     ORDER BY p.id ASC");

if (!$data) {
    die("Gagal mengambil data: " . e(mysqli_error($koneksi)));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Jenis Pelanggaran</title>

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

        <li class="nav-item mb-1">
            <a href="dashboard.php">Dashboard</a>
        </li>

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
            <a href="kelola_jenis_pelanggaran.php" class="active">
                Kelola Jenis Pelanggaran
            </a>
        </li>

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

    <h2>Kelola Jenis Pelanggaran</h2>

    <div class="mb-3"></div>

    <!-- FORM TAMBAH / EDIT -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
            <?= $edit['id'] ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran'; ?>
        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id" value="<?= e($edit['id']); ?>">

                <div class="mb-3">
                    <label class="form-label">Kategori Pelanggaran</label>

                    <select name="pelanggaran_kategori_id" class="form-control" required>
                        <option value="">Pilih Kategori</option>

                        <?php while ($k = mysqli_fetch_assoc($data_kategori)): ?>
                            <option value="<?= (int)$k['id']; ?>"
                                <?= ($edit['pelanggaran_kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                                <?= e($k['nama']); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kode Pelanggaran</label>
                    <input type="text" name="kode" class="form-control"
                        value="<?= e($edit['kode']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pelanggaran</label>
                    <input type="text" name="nama" class="form-control"
                        value="<?= e($edit['nama']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Poin</label>
                    <input type="number" name="poin" class="form-control"
                        value="<?= e($edit['poin']); ?>" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                        rows="3"><?= e($edit['deskripsi']); ?></textarea>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    <?= $edit['id'] ? 'Update Data' : 'Simpan'; ?>
                </button>

                <?php if ($edit['id']): ?>
                    <a href="kelola_jenis_pelanggaran.php" class="btn btn-secondary">
                        Batal
                    </a>
                <?php endif; ?>

            </form>

        </div>
    </div>

    <!-- TABEL DATA JENIS PELANGGARAN -->
    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">
            Data Jenis Pelanggaran
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelJenis"
                    class="table table-bordered table-striped table-hover align-middle w-100">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Kode</th>
                            <th>Nama Pelanggaran</th>
                            <th>Poin</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $no = 1; ?>

                        <?php while ($r = mysqli_fetch_assoc($data)): ?>

                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= e($r['nama_kategori'] ?? '-'); ?></td>
                                <td><?= e($r['kode']); ?></td>
                                <td><?= e($r['nama']); ?></td>
                                <td><?= e($r['poin']); ?></td>
                                <td><?= !empty($r['deskripsi']) ? e($r['deskripsi']) : '-'; ?></td>

                                <td>
                                    <div class="d-flex gap-1">

                                        <a href="kelola_jenis_pelanggaran.php?edit=<?= (int)$r['id']; ?>"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <a href="kelola_jenis_pelanggaran.php?hapus=<?= (int)$r['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')">
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

    $('#tabelJenis').DataTable({

        language: {
            search: "Cari data:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data pelanggaran",
            infoEmpty: "Tidak ada data pelanggaran",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data pelanggaran tidak ditemukan",
            emptyTable: "Belum ada data pelanggaran",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },

        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[1, 'asc']],

        columnDefs: [
            {
                targets: [0, 6],
                orderable: false,
                searchable: false
            }
        ],

        stateSave: false

    });

});
</script>

</body>
</html>
