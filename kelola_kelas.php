
<?php
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$nama_user = $_SESSION['nama_user'] ?? 'Administrator Utama';

// SIMPAN / UPDATE DATA KELAS
if (isset($_POST['simpan'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);

    if ($id !== '') {
        $query = "UPDATE t_kelas
                  SET nama='$nama', tingkat='$tingkat', jurusan='$jurusan'
                  WHERE id='$id'";
    } else {
        $query = "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif)
                  VALUES ('$nama', '$tingkat', '$jurusan', 1)";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: kelola_kelas.php");
        exit();
    } else {
        die("Gagal menyimpan data kelas: " . mysqli_error($koneksi));
    }
}

// HAPUS DATA KELAS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    if (mysqli_query($koneksi, "DELETE FROM t_kelas WHERE id='$id_hapus'")) {
        header("Location: kelola_kelas.php");
        exit();
    } else {
        die("Gagal menghapus data kelas: " . mysqli_error($koneksi));
    }
}

// DATA UNTUK FORM EDIT
$edit = [
    'id' => '',
    'nama' => '',
    'tingkat' => '',
    'jurusan' => ''
];

if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $hasil_edit = mysqli_query($koneksi, "SELECT * FROM t_kelas WHERE id='$id_edit'");

    if ($hasil_edit && mysqli_num_rows($hasil_edit) > 0) {
        $edit = mysqli_fetch_assoc($hasil_edit);
    }
}

// AMBIL DATA KELAS
$data = mysqli_query($koneksi, "SELECT * FROM t_kelas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas</title>

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
                    <a href="kelola_siswa.php">Kelola Siswa</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="kelola_kelas.php" class="active">Kelola Kelas</a>
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

    <!-- KONTEN UTAMA -->
    <div class="main-content">
        <h2 class="mb-3">Kelola Kelas</h2>
        <a href="dashboard.php">Dashboard</a>
        <hr>

        <!-- FORM TAMBAH / EDIT -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <?= $edit['id'] ? 'Edit Data Kelas' : 'Tambah Data Kelas'; ?>
            </div>

            <div class="card-body">
                <form method="POST" action="kelola_kelas.php">
                    <input type="hidden" name="id"
                           value="<?= htmlspecialchars((string)$edit['id']); ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Kelas</label>
                        <input type="text" name="nama" class="form-control"
                               placeholder="Contoh: XII RPL 1"
                               value="<?= htmlspecialchars((string)$edit['nama']); ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tingkat</label>
                        <select name="tingkat" class="form-select" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <?php foreach (['X', 'XI', 'XII'] as $tingkat): ?>
                                <option value="<?= $tingkat; ?>"
                                    <?= $edit['tingkat'] === $tingkat ? 'selected' : ''; ?>>
                                    <?= $tingkat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" class="form-control"
                               placeholder="Contoh: RPL, TKJ"
                               value="<?= htmlspecialchars((string)$edit['jurusan']); ?>"
                               required>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan
                    </button>

                    <?php if ($edit['id']): ?>
                        <a href="kelola_kelas.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- TABEL KELAS -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Data Kelas
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelKelas"
                           class="table table-bordered table-striped table-hover align-middle w-100">

                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
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
                                    <td><?= htmlspecialchars((string)$r['nama']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['tingkat']); ?></td>
                                    <td><?= htmlspecialchars((string)$r['jurusan']); ?></td>
                                    <td class="text-center text-nowrap">
                                        <a href="kelola_kelas.php?edit=<?= urlencode((string)$r['id']); ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <a href="kelola_kelas.php?hapus=<?= urlencode((string)$r['id']); ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus data kelas ini?')">
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

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Pengaturan Tabel -->
    <script>
        $(document).ready(function () {
            $('#tabelKelas').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                searching: true,
                ordering: true,
                paging: true,
                info: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data yang ditampilkan",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Belum ada data kelas",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                },
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
