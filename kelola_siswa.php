
<?php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$edit = ['id'=>'','nis'=>'','nisn'=>'','nama'=>'','jenis_kelamin'=>'','tanggal_lahir'=>'','alamat'=>''];

if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nis = trim($_POST['nis']);
    $nisn = trim($_POST['nisn']);

    if (!preg_match('/^[0-9]{10}$/', $nis) || !preg_match('/^[0-9]{10}$/', $nisn)) {
        echo "<script>alert('NIS dan NISN harus tepat 10 angka!');history.back();</script>";
        exit();
    }

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jk = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $nis = mysqli_real_escape_string($koneksi, $nis);
    $nisn = mysqli_real_escape_string($koneksi, $nisn);

    if ($id) {
        $sql = "UPDATE t_siswa SET nis='$nis',nisn='$nisn',nama='$nama',
                jenis_kelamin='$jk',tanggal_lahir='$tgl',alamat='$alamat' WHERE id='$id'";
    } else {
        $sql = "INSERT INTO t_siswa
                (nis,nisn,nama,jenis_kelamin,tanggal_lahir,alamat,status_aktif)
                VALUES ('$nis','$nisn','$nama','$jk','$tgl','$alamat',1)";
    }

    if (mysqli_query($koneksi, $sql)) {
        header("Location: kelola_siswa.php");
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM t_siswa WHERE id=$id");
    header("Location: kelola_siswa.php");
    exit();
}

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $q = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE id=$id");
    if ($q && mysqli_num_rows($q)) $edit = mysqli_fetch_assoc($q);
}

$data = mysqli_query($koneksi, "SELECT * FROM t_siswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Siswa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f8f9fa}
.sidebar{position:fixed;top:0;left:0;width:250px;height:100vh;background:#212529;color:white;overflow-y:auto}
.sidebar a{display:block;padding:10px 20px;color:#adb5bd;text-decoration:none}
.sidebar a:hover,.sidebar a.active{background:#0d6efd;color:white}
.main-content{margin-left:250px;padding:20px}
@media(max-width:768px){.sidebar{position:relative;width:100%;height:auto}.main-content{margin-left:0}}
</style>
</head>
<body>

<div class="sidebar p-3">
    <h4 class="mb-4">Pelanggaran Siswa</h4>
    <a href="dashboard.php">Dashboard</a>
    <?php if($role==='admin'): ?>
        <a href="kelola_guru.php">Kelola Guru</a>
        <a href="kelola_siswa.php" class="active">Kelola Siswa</a>
        <a href="kelola_kelas.php">Kelola Kelas</a>
        <a href="kelola_tahun_ajaran.php">Kelola Tahun Ajaran</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="kelola_kategori_pelanggaran.php">Kategori Pelanggaran</a>
        <a href="laporan.php">Laporan</a>
        <a href="kelola_jenis_pelanggaran.php">Jenis Pelanggaran</a>
        <a href="about_me.php">About Me</a>
    <?php endif; ?>
    <hr>
    <a href="logout.php" class="text-danger">Logout</a>
</div>

<div class="main-content">
    <h2>Kelola Siswa</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali</a>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <?= $edit['id'] ? 'Edit Siswa' : 'Tambah Siswa' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$edit['id']) ?>">

                <label class="form-label">NIS</label>
                <input name="nis" class="form-control mb-3" type="text"
                       maxlength="10" minlength="10" pattern="[0-9]{10}"
                       value="<?= htmlspecialchars((string)$edit['nis']) ?>" required>

                <label class="form-label">NISN</label>
                <input name="nisn" class="form-control mb-3" type="text"
                       maxlength="10" minlength="10" pattern="[0-9]{10}"
                       value="<?= htmlspecialchars((string)$edit['nisn']) ?>" required>

                <label class="form-label">Nama Siswa</label>
                <input name="nama" class="form-control mb-3"
                       value="<?= htmlspecialchars((string)$edit['nama']) ?>" required>

                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select mb-3" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L" <?= $edit['jenis_kelamin']==='L'?'selected':'' ?>>Laki-laki</option>
                    <option value="P" <?= $edit['jenis_kelamin']==='P'?'selected':'' ?>>Perempuan</option>
                </select>

                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control mb-3"
                       value="<?= htmlspecialchars((string)$edit['tanggal_lahir']) ?>" required>

                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control mb-3" required><?= htmlspecialchars((string)$edit['alamat']) ?></textarea>

                <button name="simpan" class="btn btn-primary">Simpan</button>
                <a href="kelola_siswa.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white">Data Siswa</div>
        <div class="table-responsive p-3">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>No</th><th>NIS</th><th>NISN</th><th>Nama</th><th>JK</th><th>Tanggal Lahir</th><th>Alamat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php $no=1; while($r=mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($r['nis']) ?></td>
                        <td><?= htmlspecialchars($r['nisn']) ?></td>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['jenis_kelamin']) ?></td>
                        <td><?= htmlspecialchars($r['tanggal_lahir']) ?></td>
                        <td><?= htmlspecialchars($r['alamat']) ?></td>
                        <td>
                            <a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus data siswa ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
