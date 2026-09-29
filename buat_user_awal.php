<?php
include 'config/koneksi.php';

$nama='administrator';
$email='admin@gmail.com';
$password= '123456';
$role='admin';

$nama='guru';
$email='guru@gmail.com';
$password= '123456';
$role='guru';

$sql = "INSERT INTO t_users (name, email, password, role)";
$sql .= "VALUES ('$nama', '$email', '$password', '$role')";

if (mysqli_query($koneksi, $sql)){
    echo 'User admin berhasil dibuat.Silahkan hapus file.';
}else {
    echo 'Gagal membuat user: ' . mysqli_error($koneksi);

}
?>