<?php

session_start();

include "config/koneksi.php";

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string(
        $koneksi,
        $_POST['email']
    );

    $password = $_POST['password'];

    if ($email == "" || $password == "") {

        $error = "Email dan password harus diisi!";

    } else {

        $query = mysqli_query(
            $koneksi,
            "SELECT * FROM t_users 
             WHERE email = '$email'
             AND role IN ('admin', 'guru')
             LIMIT 1"
        );

        if (!$query) {

            die("Query gagal: " . mysqli_error($koneksi));

        }

        if (mysqli_num_rows($query) > 0) {

            $user = mysqli_fetch_assoc($query);

            /*
             * Mengecek password yang sudah di-hash
             */
            if ($password == $user['password']) {

                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Password salah!";

            }

        } else {

            $error = "Email tidak ditemukan atau akun tidak memiliki akses!";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Login</title>

</head>

<body>

<h2>LOGIN</h2>

<?php

if ($error != "") {

    echo "<p>$error</p>";

}

?>

<form method="POST">

    <p>
        Email
        <br>
        <input
            type="email"
            name="email"
            required
        >
    </p>

    <p>
        Password
        <br>
        <input
            type="password"
            name="password"
            required
        >
    </p>

    <p>
        <button
            type="submit"
            name="login"
        >
            Login
        </button>
    </p>

</form>

</body>

</html>