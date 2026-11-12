
<?php
// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Deteksi apakah website berjalan di localhost
$localhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' ||
    $_SERVER['SERVER_NAME'] === '127.0.0.1'
);

if ($localhost) {
    // DATABASE XAMPP
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_ukk_2026";
} else {
    // DATABASE INFINITYFREE
    $host = "ISI_HOST_MYSQL_INFINITYFREE";
    $user = "ISI_USERNAME_DATABASE";
    $pass = "ISI_PASSWORD_DATABASE";
    $db   = "ISI_NAMA_DATABASE";
}

// Koneksi database
$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
?>
