<?php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// SIMPAN DATA PELANGGARAN
// =========================
if (isset($_POST['simpan'])) {
    $siswa_id       = (int) ($_POST['siswa_id'] ?? 0);
    $kelas_id       = (int) ($_POST['kelas_id'] ?? 0);
    $pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);
    $guru_id        = (int) ($_POST['guru_id'] ?? 0);

    $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan   = trim($_POST['tindakan'] ?? '');
    $status     = $_POST['status'] ?? 'Pending';

    // Ambil nama siswa dari tabel t_siswa
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama FROM t_siswa WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $siswa_id);
    mysqli_stmt_execute($stmt);
    $s = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Ambil nama kelas dari tabel t_kelas
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama FROM t_kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Ambil data pelanggaran
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama, poin, pelanggaran_kategori_id
         FROM t_pelanggaran
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
    mysqli_stmt_execute($stmt);
    $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Ambil nama guru dari tabel t_guru
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama FROM t_guru WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($s && $k && $p && $g) {
        $tahun_ajaran_id = 46;

        $nama_siswa       = $s['nama'];
        $nama_kelas       = $k['nama'];
        $nama_pelanggaran = $p['nama'];
        $kategori_id      = (int) $p['pelanggaran_kategori_id'];
        $nama_guru        = $g['nama'];
        $poin             = (int) $p['poin'];

        $stmt = mysqli_prepare(
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
                status,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )"
        );

        $types = "iisisisiisssiss";

        mysqli_stmt_bind_param(
            $stmt,
            $types,
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

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: catat_pelanggaran.php?msg=sukses");
            exit();
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: catat_pelanggaran.php?msg=gagal");
    exit();
}

// =========================
// HAPUS DATA PELANGGARAN
// =========================
if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $hapus_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: catat_pelanggaran.php?msg=hapus");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catat Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f8f9fa;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #212529;
            color: #fff;
            padding-top: 15px;
            overflow-y: auto;
        }

        .sidebar a {
            display: block;
            padding: 10px 20px;
            color: #adb5bd;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #0d6efd;
            color: #fff;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
            min-width: 0;
        }

        .card {
            border-radius: 10px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 15px;
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
            <a href="catat_pelanggaran.php" class="active">
                Catat Pelanggaran
            </a>
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

        <li class="nav-item mb-1">
            <a href="about_me.php">About Me</a>
        </li>
    </ul>

    <hr>

    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN UTAMA -->
<div class="main">

    <h3 class="fw-bold mb-3">Catat Pelanggaran</h3>

    <?php if (isset($_GET['msg'])): ?>

        <?php if ($_GET['msg'] === 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Data pelanggaran berhasil disimpan.

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>

        <?php elseif ($_GET['msg'] === 'hapus'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Data pelanggaran berhasil dihapus.

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>

        <?php else: ?>
            <div class="alert alert-danger alert-dismissible fade show">
                Data gagal diproses. Periksa kembali data dan struktur database.

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
            Form Tambah Catatan Pelanggaran
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Siswa</label>

                        <select
                            name="siswa_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Siswa --</option>

                            <?php
                            $q = mysqli_query(
                                $koneksi,
                                "SELECT id, nis, nama
                                 FROM t_siswa
                                 ORDER BY nama ASC"
                            );

                            while ($r = mysqli_fetch_assoc($q)):
                            ?>
                                <option value="<?= (int) $r['id']; ?>">
                                    <?= htmlspecialchars(
                                        $r['nis'] . ' - ' . $r['nama']
                                    ); ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kelas</label>

                        <select
                            name="kelas_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Kelas --</option>

                            <?php
                            $q = mysqli_query(
                                $koneksi,
                                "SELECT id, nama
                                 FROM t_kelas
                                 ORDER BY nama ASC"
                            );

                            while ($r = mysqli_fetch_assoc($q)):
                            ?>
                                <option value="<?= (int) $r['id']; ?>">
                                    <?= htmlspecialchars($r['nama']); ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pelanggaran</label>

                        <select
                            name="pelanggaran_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Pelanggaran --</option>

                            <?php
                            $q = mysqli_query(
                                $koneksi,
                                "SELECT id, kode, nama, poin
                                 FROM t_pelanggaran
                                 ORDER BY kode ASC"
                            );

                            while ($r = mysqli_fetch_assoc($q)):
                            ?>
                                <option value="<?= (int) $r['id']; ?>">
                                    <?= htmlspecialchars(
                                        $r['kode'] . ' - ' .
                                        $r['nama'] . ' (' .
                                        $r['poin'] . ' Poin)'
                                    ); ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Guru Pelapor</label>

                        <select
                            name="guru_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Guru --</option>

                            <?php
                            $q = mysqli_query(
                                $koneksi,
                                "SELECT id, nama
                                 FROM t_guru
                                 ORDER BY nama ASC"
                            );

                            while ($r = mysqli_fetch_assoc($q)):
                            ?>
                                <option value="<?= (int) $r['id']; ?>">
                                    <?= htmlspecialchars($r['nama']); ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal</label>

                        <input
                            type="date"
                            name="tanggal"
                            class="form-control"
                            value="<?= date('Y-m-d'); ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >
                            <option value="Pending">Pending</option>
                            <option value="Proses">Proses</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>

                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="2"
                        placeholder="Keterangan kejadian..."
                    ></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tindakan / Sanksi</label>

                    <input
                        type="text"
                        name="tindakan"
                        class="form-control"
                        placeholder="Tindakan atau sanksi..."
                    >
                </div>

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-primary"
                >
                    Simpan
                </button>

                <button
                    type="reset"
                    class="btn btn-secondary"
                >
                    Reset
                </button>

            </form>

        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-dark text-white">
            Data Pelanggaran Siswa
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tabelPelanggaran"
                    class="table table-bordered table-striped table-hover align-middle w-100"
                >

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        // Ambil nama asli dari tabel master menggunakan JOIN.
                        $data = mysqli_query(
                            $koneksi,
                            "SELECT
                                ps.id,
                                ps.tanggal,
                                ps.poin,
                                ps.status,
                                ps.tindakan,
                                ps.keterangan,
                                s.nama AS nama_siswa_asli,
                                k.nama AS nama_kelas_asli,
                                p.nama AS nama_pelanggaran_asli,
                                g.nama AS nama_guru_asli
                            FROM t_pelanggaran_siswa AS ps
                            LEFT JOIN t_siswa AS s
                                ON ps.siswa_id = s.id
                            LEFT JOIN t_kelas AS k
                                ON ps.kelas_id = k.id
                            LEFT JOIN t_pelanggaran AS p
                                ON ps.pelanggaran_id = p.id
                            LEFT JOIN t_guru AS g
                                ON ps.guru_id = g.id
                            ORDER BY ps.tanggal DESC, ps.id DESC"
                        );

                        if (!$data) {
                            die(
                                "Query data pelanggaran gagal: " .
                                mysqli_error($koneksi)
                            );
                        }

                        while ($row = mysqli_fetch_assoc($data)):
                        ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td data-order="<?= htmlspecialchars($row['tanggal']); ?>">
                                    <?= htmlspecialchars($row['tanggal']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_siswa_asli'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_kelas_asli'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_pelanggaran_asli'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        <?= (int) $row['poin']; ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_guru_asli'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?php
                                    $warna = 'secondary';

                                    if ($row['status'] === 'Pending') {
                                        $warna = 'warning text-dark';
                                    } elseif ($row['status'] === 'Proses') {
                                        $warna = 'primary';
                                    } elseif ($row['status'] === 'Selesai') {
                                        $warna = 'success';
                                    }
                                    ?>

                                    <span class="badge bg-<?= $warna; ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="catat_pelanggaran.php?hapus=<?= (int) $row['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data pelanggaran ini?')"
                                    >
                                        Hapus
                                    </a>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelPelanggaran').DataTable({
        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari data pelanggaran...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data pelanggaran',
            loadingRecords: 'Memuat data...',
            processing: 'Memproses...',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        },

        pageLength: 10,

        lengthMenu: [
            [5, 10, 25, 50, 100],
            [5, 10, 25, 50, 100]
        ],

        order: [[1, 'desc']],

        columnDefs: [
            {
                targets: [0, 8],
                orderable: false
            }
        ],

        responsive: true
    });
});
</script>

</body>
</html>