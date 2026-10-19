
<?php
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// SIMPAN DAN EDIT DATA GURU
if (isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nip = mysqli_real_escape_string($koneksi, trim($_POST['nip'] ?? ''));
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email'] ?? ''));

    if ($id > 0) {
        $query = "UPDATE t_guru
                  SET nip='$nip', nama='$nama', email='$email'
                  WHERE id='$id'";
    } else {
        $query = "INSERT INTO t_guru (nip, nama, email)
                  VALUES ('$nip', '$nama', '$email')";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: kelola_guru.php");
        exit();
    } else {
        die("Gagal menyimpan data guru: " . htmlspecialchars(mysqli_error($koneksi)));
    }
}

// HAPUS DATA GURU
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    if (mysqli_query($koneksi, "DELETE FROM t_guru WHERE id='$id_hapus'")) {
        header("Location: kelola_guru.php");
        exit();
    } else {
        die("Gagal menghapus data guru: " . htmlspecialchars(mysqli_error($koneksi)));
    }
}

// AMBIL DATA UNTUK EDIT
$edit = [
    'id' => '',
    'nip' => '',
    'nama' => '',
    'email' => ''
];

if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT id, nip, nama, email
         FROM t_guru
         WHERE id='$id_edit'"
    );

    if ($hasil_edit && mysqli_num_rows($hasil_edit) > 0) {
        $edit = mysqli_fetch_assoc($hasil_edit);
    }
}

// AMBIL SEMUA DATA GURU
$data = mysqli_query(
    $koneksi,
    "SELECT id, nip, nama, email
     FROM t_guru
     ORDER BY nama ASC"
);

if (!$data) {
    die("Gagal mengambil data guru: " . htmlspecialchars(mysqli_error($koneksi)));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Guru</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
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
            <a href="kelola_guru.php" class="active">Kelola Guru</a>
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
            <a href="laporan.php">Laporan</a>
        </li>

        <li class="nav-item mb-1">
            <a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a>
        </li>

        <li class="nav-item mb-1">
            <a href="about.php">About Me</a>
        </li>
    </ul>

    <hr>

    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN UTAMA -->
<div class="main-content">

    <h2 class="mb-3">Kelola Guru</h2>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-secondary">
            Kembali ke Dashboard
        </a>
    </div>

    <!-- FORM TAMBAH / EDIT -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <?= !empty($edit['id']) ? 'Edit Data Guru' : 'Tambah Data Guru'; ?>
        </div>

        <div class="card-body">
            <form method="POST" action="kelola_guru.php">

                <input type="hidden" name="id"
                       value="<?= htmlspecialchars((string)$edit['id']); ?>">

                <div class="mb-3">
                    <label class="form-label">NIP:</label>
                    <input type="text" name="nip"
                           class="form-control"
                           value="<?= htmlspecialchars($edit['nip'] ?? ''); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama:</label>
                    <input type="text" name="nama"
                           class="form-control"
                           value="<?= htmlspecialchars($edit['nama'] ?? ''); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email"
                           class="form-control"
                           value="<?= htmlspecialchars($edit['email'] ?? ''); ?>"
                           required>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    <?= !empty($edit['id']) ? 'Update Data' : 'Simpan'; ?>
                </button>

                <?php if (!empty($edit['id'])): ?>
                    <a href="kelola_guru.php" class="btn btn-secondary">
                        Batal
                    </a>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <!-- TABEL DATA GURU -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Data Guru
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelGuru"
                       class="table table-bordered table-striped table-hover align-middle w-100">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1; ?>

                        <?php while ($r = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($r['nip'] ?? ''); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['nama'] ?? ''); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['email'] ?? ''); ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="kelola_guru.php?edit=<?= (int)$r['id']; ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <a href="kelola_guru.php?hapus=<?= (int)$r['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus data guru ini?')">
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

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- AKTIFKAN DATATABLES -->
<script>
$(document).ready(function () {
    $('#tabelGuru').DataTable({
        language: {
            search: "Cari data:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data guru",
            infoEmpty: "Tidak ada data guru",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data guru tidak ditemukan",
            emptyTable: "Belum ada data guru",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[2, 'asc']],
        columnDefs: [
            {
                targets: 4,
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>

</body>
</html>
