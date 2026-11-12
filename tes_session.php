
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Status sebelum: " . session_status() . "<br>";

$result = session_start();

echo "Hasil session_start: ";
var_dump($result);

echo "<br>Status sesudah: " . session_status() . "<br>";

$_SESSION['tes'] = 'BERHASIL';

echo "Session ID: " . session_id() . "<br>";
echo "Data session: " . ($_SESSION['tes'] ?? 'KOSONG');
?>
