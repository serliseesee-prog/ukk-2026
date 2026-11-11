<?php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

function e($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

// AMBIL TAHUN AJARAN YANG AKTIF OTOMATIS
$query_ta_aktif = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran WHERE status_aktif = 1 LIMIT 1");
$tahun_aktif = mysqli_fetch_assoc($query_ta_aktif);
$tahun_ajaran_id_aktif = $tahun_aktif['id'] ?? '';
$nama_tahun_aktif = $tahun_aktif['nama'] ?? 'Tidak ada Tahun Ajaran Aktif';

// SIMPAN / EDIT DENGAN VALIDASI KETAT (1 GURU HANYA 1 KELAS DI TAHUN AJARAN AKTIF)
if (isset($_POST['simpan'])) {
    $id = (int)($_POST['id'] ?? 0);
    $tahun = (int)$tahun_ajaran_id_aktif; 
    $kelas = (int)($_POST['kelas_id'] ?? 0);
    $guru = (int)($_POST['guru_id'] ?? 0);
    $status = 1; // Default status aktif

    if ($id) {
        // Mode Edit: Cek apakah guru ini sudah menjadi wali kelas di KELAS LAIN pada tahun ajaran yang sama
        $sql_cek = "SELECT id FROM t_wali_kelas WHERE guru_id = ? AND tahun_ajaran_id = ? AND id != ?";
        $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "iii", $guru, $tahun, $id);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($result_cek) > 0) {
            header("Location: kelola_wali_kelas.php?pesan=duplikat_guru");
            exit();
        }

        $sql = "UPDATE t_wali_kelas SET tahun_ajaran_id=?, kelas_id=?, guru_id=?, status_aktif=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "iiiii", $tahun, $kelas, $guru, $status, $id);
        mysqli_stmt_execute($stmt);
        
        header("Location: kelola_wali_kelas.php?pesan=sukses_edit");
        exit();
    } else {
        // Mode Tambah: Cek apakah guru ini sudah terdaftar sebagai wali kelas di tahun ajaran tersebut
        $sql_cek = "SELECT id FROM t_wali_kelas WHERE guru_id = ? AND tahun_ajaran_id = ?";
        $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "ii", $guru, $tahun);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($result_cek) > 0) {
            header("Location: kelola_wali_kelas.php?pesan=duplikat_guru");
            exit();
        }

        $sql = "INSERT INTO t_wali_kelas (tahun_ajaran_id, kelas_id, guru_id, status_aktif) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "iiii", $tahun, $kelas, $guru, $status);
        mysqli_stmt_execute($stmt);
        
        header("Location: kelola_wali_kelas.php?pesan=sukses_tambah");
        exit();
    }
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_wali_kelas WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: kelola_wali_kelas.php?pesan=sukses_hapus");
    exit();
}

// DATA EDIT
$edit = [
    'id' => '', 'tahun_ajaran_id' => '', 'kelas_id' => '', 'guru_id' => ''
];

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM t_wali_kelas WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: $edit;
}

$guru = mysqli_query($koneksi, "SELECT id,nama FROM t_guru WHERE status_aktif=1 ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id,nama FROM t_kelas WHERE status_aktif=1 ORDER BY nama");

$data = mysqli_query($koneksi, "
    SELECT wk.*, g.nama AS nama_guru, k.nama AS nama_kelas,
           ta.nama AS nama_tahun
    FROM t_wali_kelas wk
    LEFT JOIN t_guru g ON wk.guru_id=g.id
    LEFT JOIN t_kelas k ON wk.kelas_id=k.id
    LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id=ta.id
    ORDER BY wk.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Wali Kelas</title>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- DataTables Bootstrap 5 CSS -->
<link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
body {
    background: #f8f9fa;
}
.sidebar {
    min-height: 100vh;
    background: #212529;
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
    background: #0d6efd;
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
        <li class="nav-item mb-1"><a href="penempatan_siswa.php">Penempatan Siswa</a></li>
        <li class="nav-item mb-1"><a href="kelola_wali_kelas.php" class="active">Kelola Wali Kelas</a></li>
        <li class="nav-item mb-1"><a href="kelola_kategori_pelanggaran.php">Kelola Kategori Pelanggaran</a></li>
        <li class="nav-item mb-1"><a href="kelola_jenis_pelanggaran.php">Kelola Jenis Pelanggaran</a></li>
        <li class="nav-item mb-1"><a href="laporan.php">Laporan</a></li>
        <li class="nav-item mb-1"><a href="about_me.php">About Me</a></li>
    </ul>
    <hr>
    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN -->
<div class="main-content">
    <h2 class="mb-3">Kelola Wali Kelas</h2>
    <hr>

    <!-- NOTIFIKASI / ALERT -->
    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] == 'duplikat_guru'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> Guru tersebut sudah terdaftar sebagai wali kelas untuk kelas lain pada tahun ajaran aktif ini. Satu guru tidak boleh memegang dua kelas sekaligus.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'sukses_tambah'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> Data wali kelas baru berhasil ditambahkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> Data wali kelas berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> Data wali kelas berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <?= $edit['id'] ? 'Edit Wali Kelas' : 'Tambah Wali Kelas' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= e($edit['id']) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="hidden" name="tahun_ajaran_id" value="<?= $tahun_ajaran_id_aktif ?>">
                        <input type="text" class="form-control bg-light" value="<?= e($nama_tahun_aktif) ?>" readonly>
                        <small class="text-muted">Tahun ajaran otomatis diset ke tahun ajaran yang sedang aktif.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php 
                            mysqli_data_seek($kelas, 0);
                            while($k = mysqli_fetch_assoc($kelas)): 
                            ?>
                            <option value="<?= $k['id'] ?>" <?= $edit['kelas_id'] == $k['id'] ? 'selected' : '' ?>>
                                <?= e($k['nama']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Guru Wali Kelas</label>
                        <select name="guru_id" id="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php 
                            mysqli_data_seek($guru, 0);
                            while($g = mysqli_fetch_assoc($guru)): 
                            ?>
                            <option value="<?= $g['id'] ?>" <?= $edit['guru_id'] == $g['id'] ? 'selected' : '' ?>>
                                <?= e($g['nama']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                <?php if($edit['id']): ?>
                    <a href="kelola_wali_kelas.php" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- TABEL WALI KELAS -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Daftar Wali Kelas</div>
        <div class="card-body table-responsive">
            <table id="tabelWaliKelas" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($r = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($r['nama_guru']) ?></td>
                        <td><?= e($r['nama_kelas']) ?></td>
                        <td><?= e($r['nama_tahun']) ?></td>
                        <td>
                            <span class="badge bg-<?= $r['status_aktif'] ? 'success' : 'secondary' ?>">
                                <?= $r['status_aktif'] ? 'Aktif' : 'Tidak Aktif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?hapus=<?= (int)$r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    // Inisialisasi DataTable
    new DataTable('#tabelWaliKelas', {
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { orderable: false, searchable: false, targets: [0, 5] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data wali kelas",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Inisialisasi Select2 untuk pencarian di dropdown Kelas dan Guru
    $('#kelas_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Kelas --',
        allowClear: true
    });

    $('#guru_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Guru --',
        allowClear: true
    });
});
</script>
</body>
</html>