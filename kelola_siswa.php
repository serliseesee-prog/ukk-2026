
<?php
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$nama_user = $_SESSION['nama_user'];

// SIMPAN / UPDATE DATA SISWA
if (isset($_POST['simpan'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nisn = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    if ($id !== '') {
        $query = "UPDATE t_siswa SET
                    nis='$nis',
                    nisn='$nisn',
                    nama='$nama',
                    jenis_kelamin='$jenis_kelamin',
                    tanggal_lahir='$tanggal_lahir',
                    alamat='$alamat'
                  WHERE id='$id'";
    } else {
        $query = "INSERT INTO t_siswa
                    (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
                  VALUES
                    ('$nis', '$nisn', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', 1)";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: kelola_siswa.php");
        exit();
    } else {
        die("Gagal menyimpan data: " . mysqli_error($koneksi));
    }
}

// HAPUS DATA SISWA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    if (mysqli_query($koneksi, "DELETE FROM t_siswa WHERE id='$id_hapus'")) {
        header("Location: kelola_siswa.php");
        exit();
    } else {
        die("Gagal menghapus data: " . mysqli_error($koneksi));
    }
}

// AMBIL DATA UNTUK EDIT
$edit = [
    'id' => '',
    'nis' => '',
    'nisn' => '',
    'nama' => '',
    'jenis_kelamin' => '',
    'tanggal_lahir' => '',
    'alamat' => ''
];

if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $hasil_edit = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE id='$id_edit'");

    if ($hasil_edit && mysqli_num_rows($hasil_edit) > 0) {
        $edit = mysqli_fetch_assoc($hasil_edit);
    }
}

// AMBIL SEMUA DATA SISWA
$data = mysqli_query($koneksi, "SELECT * FROM t_siswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
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

        .table-responsive {
            overflow-x: auto;
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

            <?php if ($role === 'admin'): ?>
                <li class="nav-item mb-1">
                    <a href="kelola_guru.php">Kelola Guru</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="kelola_siswa.php" class="active">Kelola Siswa</a>
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
                    <a href="about_me.php">About Me</a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'guru'): ?>
                <li class="nav-item mb-1">
                    <a href="catatan_pelanggaran.php">Catat Pelanggaran</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="tindakan.php">Tindakan</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="riwayat.php">Riwayat</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="rekap_poin.php">Rekap Poin</a>
                </li>
            <?php endif; ?>
        </ul>

        <hr class="text-secondary">
        <a href="logout.php" class="text-danger">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2 class="mb-3">Kelola Siswa</h2>
         <div class="mb-3">
        <a href="dashboard.php" class="btn btn-secondary">
            Kembali ke Dashboard
        </a>
    </div>
        <hr>

        <!-- FORM TAMBAH / EDIT -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <?= $edit['id'] ? 'Edit Data Siswa' : 'Tambah Data Siswa'; ?>
            </div>

            <div class="card-body">
                <form method="POST" action="kelola_siswa.php">
                    <input type="hidden" name="id"
                           value="<?= htmlspecialchars((string)$edit['id']); ?>">

                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" class="form-control"
                               value="<?= htmlspecialchars((string)$edit['nis']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn" class="form-control"
                               value="<?= htmlspecialchars((string)$edit['nisn']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" name="nama" class="form-control"
                               value="<?= htmlspecialchars((string)$edit['nama']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" <?= $edit['jenis_kelamin'] === 'L' ? 'selected' : ''; ?>>
                                Laki-laki (L)
                            </option>
                            <option value="P" <?= $edit['jenis_kelamin'] === 'P' ? 'selected' : ''; ?>>
                                Perempuan (P)
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control"
                               value="<?= htmlspecialchars((string)$edit['tanggal_lahir']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars((string)$edit['alamat']); ?></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan
                    </button>

                    <?php if ($edit['id']): ?>
                        <a href="kelola_siswa.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- TABEL DATA SISWA + JAVASCRIPT DATATABLES -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Data Siswa
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelSiswa" class="table table-bordered table-striped table-hover align-middle w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;
                            if ($data && mysqli_num_rows($data) > 0):
                                while ($r = mysqli_fetch_assoc($data)):
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars((string)$r['nis']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['nisn']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['nama']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['jenis_kelamin']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['tanggal_lahir']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['alamat']); ?></td>
                                    <td class="text-center text-nowrap">
                                        <a href="kelola_siswa.php?edit=<?= urlencode((string)$r['id']); ?>"
                                           class="btn btn-sm btn-warning">Edit</a>

                                        <a href="kelola_siswa.php?hapus=<?= urlencode((string)$r['id']); ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus data siswa ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                endwhile;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Pengaturan Tabel JavaScript -->
    <script>
        $(document).ready(function () {
            $('#tabelSiswa').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data yang ditampilkan",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Belum ada data siswa",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                },
                columnDefs: [
                    {
                        targets: 7,
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>

</body>
</html>
