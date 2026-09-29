<?php

session_start();

if (!isset($_SESSION['id'])) {

    header("Location: login.php");
    exit;

}

$nama = $_SESSION['name'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

</head>

<body>

<h1>Dashboard</h1>

<p>
    Selamat datang,
    <strong>
        <?php echo htmlspecialchars($nama); ?>
    </strong>
</p>

<p>
    Anda login sebagai:
    <strong>
        <?php echo ucfirst($role); ?>
    </strong>
</p>

<hr>

<h3>Menu</h3>




<?php if ($role == "admin") { ?>

    <p>
        <a href="menu1.php">
            kelola guru
        </a>
    </p>

    <p>
        <a href="menu2.php">
            kelola siswa
        </a>
    </p>

    <p>
        <a href="menu3.php">
            kelola kelas
        </a>
    </p>

    <p>
        <a href="menu4.php">
            kelola tahun ajaran
        </a>
    </p>

    <p>
            <a href="menu5.php">
                penempatan siswa
            </a>
    </p>

    <p>
        <a href="menu4.php">
            kelola wali kelas
        </a>
    </p>

     <p>
        <a href="menu4.php">
            kelola kategori pelanggan
        </a>
    </p>

    <p>
        <a href="menu4.php">
            kelola jenis pelanggaran
        </a>
    </p>

    <p>
        <a href="menu4.php">
            cetak/export
        </a>
    </p>


<?php } elseif ($role == "guru") { ?>

   <p>
        <a href="menu6.php">
            catatan pelanggaran
        </a>
    </p>

     <p>
        <a href="menu7.php">
            tindakan
        </a>
    </p>

      <p>
        <a href="menu8.php">
            laporan
        </a>
    </p>

    
      <p>
        <a href="menu9.php">
            riwayat
        </a>
    </p>

     <p>
        <a href="menu10.php">
            rekapan poin
        </a>
    </p>




<?php } ?>


<hr>

<p>
    <a href="logout.php">
        Logout
    </a>
</p>

</body>

</html>