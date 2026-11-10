
<?php
require "koneksi.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role FROM users WHERE email=?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "admin") {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Email atau password tidak sesuai.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | SOLE STUDIO</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;
background:#f6f5f2;color:#191919;min-height:100vh;display:grid;place-items:center;padding:20px}
.card{background:white;width:100%;max-width:420px;padding:38px;border:1px solid #eee}
.logo{text-align:center;font-size:24px;font-weight:bold;letter-spacing:2px}
h1{text-align:center;font-size:27px;margin:30px 0 8px}
.sub{text-align:center;color:#777;font-size:13px;margin-bottom:25px}
label{display:block;font-size:13px;margin:17px 0 8px}
input{width:100%;padding:13px;border:1px solid #ddd}
button{width:100%;padding:14px;background:#191919;color:white;border:0;margin-top:22px;cursor:pointer}
p{font-size:13px;line-height:1.6}
.bottom{text-align:center;margin-top:22px;color:#777}
.bottom a{color:#191919;font-weight:bold}
.error{background:#fff0f0;padding:10px;color:#a21d1d;margin:15px 0}
.success{background:#edf8ee;padding:10px;color:#236b32;margin:15px 0}
</style>
</head>
<body>
<div class="card">
    <a href="index.php" class="logo" style="display:block;color:inherit;text-decoration:none">SOLE STUDIO.</a>
    <h1>Welcome Back</h1>
    <p class="sub">Login to continue your shopping.</p>

    <?php if (isset($_GET["registered"])): ?>
        <p class="success">Akun berhasil dibuat. Silakan login.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" required
               value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">SIGN IN</button>
    </form>
    <p class="bottom">Don't have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
