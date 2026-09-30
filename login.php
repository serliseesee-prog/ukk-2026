
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
             * Mengecek password
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Pelanggaran Siswa</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f4f8fc;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* BACKGROUND */
        .background-shape {
            position: fixed;
            width: 250px;
            height: 250px;
            background: #e5effa;
            border-radius: 50%;
            z-index: -1;
        }

        .shape-one {
            top: -100px;
            left: -80px;
        }

        .shape-two {
            bottom: -100px;
            right: -80px;
        }

        /* CARD LOGIN */
        .login-card {
            width: 430px;
            max-width: 90%;
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        /* ICON */
        .login-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: #eaf3ff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #3978c7;
            font-size: 32px;
        }

        /* JUDUL */
        .login-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #1d2d44;
            margin-bottom: 8px;
        }

        .login-subtitle {
            text-align: center;
            color: #7b8794;
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* LABEL */
        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
        }

        /* INPUT */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group-text {
            background: #f8fafc;
            border: 1px solid #dce5ef;
            color: #718096;
        }

        .form-control {
            height: 50px;
            border: 1px solid #dce5ef;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: #6ea8dc;
            box-shadow: 0 0 0 3px rgba(110, 168, 220, 0.15);
            background: white;
        }

        /* PASSWORD BUTTON */
        .password-button {
            border: 1px solid #dce5ef;
            border-left: none;
            background: #f8fafc;
            color: #718096;
            cursor: pointer;
        }

        .password-button:hover {
            color: #3978c7;
        }

        /* ERROR */
        .error-box {
            background: #fff0f0;
            color: #d9534f;
            border: 1px solid #f5c2c0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        /* LOGIN BUTTON */
        .login-button {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 10px;
            background: #3978c7;
            color: white;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-button:hover {
            background: #2865ae;
            transform: translateY(-1px);
        }

        /* FOOTER */
        .login-footer {
            text-align: center;
            color: #9aa7b5;
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 0;
        }

        .footer-line {
            display: inline-block;
            width: 35px;
            height: 1px;
            background: #b9c8d8;
            vertical-align: middle;
            margin: 0 8px;
        }

        /* RESPONSIVE */
        @media (max-width: 500px) {

            .login-card {
                padding: 35px 25px;
            }

            .login-title {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

    <!-- Background -->
    <div class="background-shape shape-one"></div>
    <div class="background-shape shape-two"></div>


    <!-- LOGIN CARD -->
    <div class="login-card">

        <!-- ICON -->
        <div class="login-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>


        <!-- JUDUL -->
        <h1 class="login-title">
            Login
        </h1>

        <p class="login-subtitle">
            Masuk untuk melanjutkan ke sistem<br>
            Pelanggaran Siswa
        </p>


        <!-- PESAN ERROR -->
        <?php if ($error != "") : ?>

            <div class="error-box">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <!-- FORM LOGIN -->
        <form method="POST">

            <!-- EMAIL -->
            <label class="form-label">
                Email
            </label>

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Masukkan email"
                    required
                >

            </div>


            <!-- PASSWORD -->
            <label class="form-label">
                Password
            </label>

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>

                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Masukkan password"
                    required
                >

                <button
                    type="button"
                    class="input-group-text password-button"
                    onclick="lihatPassword()"
                >
                    <i class="bi bi-eye" id="iconPassword"></i>
                </button>

            </div>


            <!-- TOMBOL LOGIN -->
            <button
                type="submit"
                name="login"
                class="login-button"
            >
                Login
            </button>

        </form>


        <!-- FOOTER -->
        <p class="login-footer">
            <span class="footer-line"></span>
            Selamat datang kembali
            <span class="footer-line"></span>
        </p>

    </div>


    <!-- JAVASCRIPT PASSWORD -->
    <script>

        function lihatPassword() {

            const password = document.getElementById("password");
            const icon = document.getElementById("iconPassword");

            if (password.type === "password") {

                password.type = "text";

                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");

            } else {

                password.type = "password";

                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");

            }

        }

    </script>

</body>

</html>
