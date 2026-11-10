
<?php
require "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    // Validasi data
    if (
        $name === "" ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 100 ||
        strlen($password) < 8
    ) {
        $error = "Isi data dengan benar. Password minimal 8 karakter.";
    } else {
        // Periksa apakah email sudah terdaftar
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        if (!$check) {
            $error = "Terjadi kesalahan pada database.";
        } else {
            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $error = "Email sudah terdaftar. Silakan login.";
            } else {
                // Amankan password sebelum disimpan
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, password)
                     VALUES (?, ?, ?)"
                );

                if (!$stmt) {
                    $error = "Terjadi kesalahan saat membuat akun.";
                } else {
                    $stmt->bind_param("sss", $name, $email, $hash);

                    if ($stmt->execute()) {
                        // Arahkan ke halaman login
                        header("Location: login.php?registered=1");
                        exit;
                    } else {
                        $error = "Registrasi gagal. Silakan coba kembali.";
                    }

                    $stmt->close();
                }
            }

            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Account | SOLE STUDIO</title>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f6f5f2;
    color:#191919;
    min-height:100vh;
    display:grid;
    place-items:center;
    padding:20px
}
.card{
    background:white;
    width:100%;
    max-width:420px;
    padding:38px;
    border:1px solid #eee
}
.logo{
    text-align:center;
    font-size:24px;
    font-weight:bold;
    letter-spacing:2px
}
h1{
    text-align:center;
    font-size:25px;
    margin:30px 0 8px
}
.sub{
    text-align:center;
    color:#777;
    font-size:13px;
    margin-bottom:25px
}
label{
    display:block;
    font-size:13px;
    margin:17px 0 8px
}
input{
    width:100%;
    padding:13px;
    border:1px solid #ddd;
    outline:none
}
input:focus{border-color:#333}
button{
    width:100%;
    padding:14px;
    background:#191919;
    color:white;
    border:0;
    margin-top:22px;
    cursor:pointer
}
p{font-size:13px;line-height:1.6}
.bottom{
    text-align:center;
    margin-top:22px;
    color:#777
}
.bottom a{
    color:#191919;
    font-weight:bold
}
.error{
    background:#fff0f0;
    padding:10px;
    color:#a21d1d;
    margin:15px 0
}
</style>
</head>

<body>
<div class="card">

    <a href="index.php"
       class="logo"
       style="display:block;color:inherit;text-decoration:none">
       SOLE STUDIO.
    </a>

    <h1>Create Account</h1>
    <p class="sub">Join us and find your perfect pair.</p>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="name">Full Name</label>
        <input
            id="name"
            type="text"
            name="name"
            maxlength="100"
            required
            value="<?= htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="email">Email Address</label>
        <input
            id="email"
            type="email"
            name="email"
            maxlength="100"
            required
            value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="password">Password</label>
        <input
            id="password"
            type="password"
            name="password"
            minlength="8"
            required
        >

        <button type="submit">CREATE ACCOUNT</button>
    </form>

    <p class="bottom">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>
</body>
</html>
