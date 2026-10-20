
<?php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// HAPUS DATA PELANGGARAN
// =========================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE id = ?"
    );

    if (!$stmt) {
        die("Gagal menyiapkan query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tindakan.php?msg=hapus");
    exit();
}

// =========================
// AMBIL DATA TINDAKAN
// =========================
$sql = "
    SELECT
        ps.id,
        ps.tanggal,
        ps.poin,
        ps.status,
        ps.keterangan,
        ps.tindakan AS nama_tindakan,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        p.nama AS nama_pelanggaran,
        g.nama AS nama_guru
    FROM t_pelanggaran_siswa AS ps
    LEFT JOIN t_siswa AS s
        ON ps.siswa_id = s.id
    LEFT JOIN t_kelas AS k
        ON ps.kelas_id = k.id
    LEFT JOIN t_pelanggaran AS p
        ON ps.pelanggaran_id = p.id
    LEFT JOIN t_guru AS g
        ON ps.guru_id = g.id
    ORDER BY ps.tanggal DESC, ps.id DESC
";

$data = mysqli_query($koneksi, $sql);

if (!$data) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Tindakan Pelanggaran</title>

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

        /* SIDEBAR SAMA DENGAN CATAT PELANGGARAN */
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

        <li class="nav-item mb-1">
            <a href="about_me.php">About Me</a>
        </li>
    </ul>

    <hr>

    <a href="logout.php" class="text-danger">Logout</a>
</div>

<!-- KONTEN UTAMA -->
<div class="main">

    <h3 class="fw-bold mb-3">Data Tindakan Pelanggaran</h3>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'hapus'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Data pelanggaran berhasil dihapus.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            Daftar Tindakan Pelanggaran Siswa
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table
                    id="tabelTindakan"
                    class="table table-bordered table-striped table-hover w-100"
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
                            <th>Tindakan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td data-order="<?= htmlspecialchars($row['tanggal'] ?? ''); ?>">
                                    <?= htmlspecialchars($row['tanggal'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_pelanggaran'] ?? '-'); ?>
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        <?= (int) ($row['poin'] ?? 0); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_tindakan'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $row['status'] ?? 'Pending';
                                    $warna = 'secondary';

                                    if ($status === 'Selesai') {
                                        $warna = 'success';
                                    } elseif ($status === 'Proses') {
                                        $warna = 'primary';
                                    } elseif ($status === 'Pending') {
                                        $warna = 'warning text-dark';
                                    }
                                    ?>

                                    <span class="badge bg-<?= $warna; ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="tindakan.php?hapus=<?= (int) $row['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data ini?')"
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelTindakan').DataTable({
        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        order: [[1, 'desc']],

        columnDefs: [
            {
                targets: [0, 9],
                orderable: false
            }
        ],

        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari data tindakan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data tindakan',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });
});
</script>

</body>
</html>
