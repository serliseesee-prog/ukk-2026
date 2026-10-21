
<?php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$edit = ['id'=>'','nama'=>'','tingkat'=>'','jurusan'=>''];

if (isset($_POST['simpan'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);

    $sql = $id
        ? "UPDATE t_kelas SET nama='$nama', tingkat='$tingkat', jurusan='$jurusan' WHERE id=$id"
        : "INSERT INTO t_kelas (nama,tingkat,jurusan,status_aktif) VALUES ('$nama','$tingkat','$jurusan',1)";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: kelola_kelas.php");
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM t_kelas WHERE id=$id");
    header("Location: kelola_kelas.php");
    exit();
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($koneksi, "SELECT * FROM t_kelas WHERE id=$id");
    if ($q && mysqli_num_rows($q)) $edit = mysqli_fetch_assoc($q);
}

$data = mysqli_query($koneksi, "SELECT * FROM t_kelas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Kelas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
body{background:#f8f9fa}
.sidebar{position:fixed;top:0;left:0;width:250px;height:100vh;background:#212529;overflow-y:auto}
.sidebar a{display:block;padding:10px 20px;color:#adb5bd;text-decoration:none}
.sidebar a:hover,.sidebar a.active{background:#0d6efd;color:white}
.main-content{margin-left:250px;padding:20px}
@media(max-width:768px){.sidebar{position:relative;width:100%;height:auto}.main-content{margin-left:0}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar p-3">
    <h4 class="text-white mb-4">Pelanggaran Siswa</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_guru.php">Kelola Guru</a>
    <a href="kelola_siswa.php">Kelola Siswa</a>
    <a href="kelola_kelas.php" class="active">Kelola Kelas</a>
    <a href="kelola_tahun_ajaran.php">Kelola Tahun Ajaran</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_wali_kelas.php">Kelola Wali Kelas</a>
    <a href="kelola_kategori_pelanggaran.php">Kategori Pelanggaran</a>
    <a href="laporan.php">Laporan</a>
    <a href="kelola_jenis_pelanggaran.php">Jenis Pelanggaran</a>
    <a href="about_me.php">About Me</a>
    <hr class="text-secondary">
    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN -->
<div class="main-content">
    <h2>Kelola Kelas</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <?= $edit['id'] ? 'Edit Kelas' : 'Tambah Kelas' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">

                <label class="form-label">Nama Kelas</label>
                <input name="nama" class="form-control mb-3"
                       value="<?= htmlspecialchars($edit['nama']) ?>" required>

                <label class="form-label">Tingkat</label>
                <select name="tingkat" class="form-select mb-3" required>
                    <option value="">Pilih Tingkat</option>
                    <?php foreach (['X','XI','XII'] as $t): ?>
                    <option value="<?= $t ?>" <?= $edit['tingkat']===$t?'selected':'' ?>>
                        <?= $t ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Jurusan</label>
                <input name="jurusan" class="form-control mb-3"
                       placeholder="Contoh: RPL, TKJ"
                       value="<?= htmlspecialchars($edit['jurusan']) ?>" required>

                <button name="simpan" class="btn btn-primary">Simpan</button>
                <a href="kelola_kelas.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <div class="card">
        <div class="card-header bg-dark text-white">Data Kelas</div>
        <div class="card-body table-responsive">
            <table id="tabelKelas" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th>No</th><th>Nama Kelas</th><th>Tingkat</th>
                        <th>Jurusan</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while ($r=mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['tingkat']) ?></td>
                        <td><?= htmlspecialchars($r['jurusan']) ?></td>
                        <td class="text-nowrap">
                            <a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin ingin menghapus kelas ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT DATATABLES -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data kelas",
            paginate: {
                previous: "Sebelumnya",
                next: "Berikutnya"
            }
        },
        columnDefs: [{
            targets: 4,
            orderable: false,
            searchable: false
        }]
    });
});
</script>

</body>
</html>
