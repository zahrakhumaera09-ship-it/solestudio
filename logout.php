```php
<?php
session_start();

// Jika Logout dikonfirmasi
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["confirm_logout"])) {

    // Hapus data session
    $_SESSION = [];

    // Hapus cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Hancurkan session
    session_destroy();

    // Arahkan ke halaman login
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Logout | SOLE STUDIO</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: #f6f5f2;
    font-family: Arial, sans-serif;
    color: #191919;
}

.logout-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(0, 0, 0, 0.55);
}

.logout-box {
    width: 100%;
    max-width: 360px;
    padding: 30px;
    background: #fff;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
    animation: logoutShow 0.2s ease;
}

.logout-icon {
    width: 55px;
    height: 55px;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f1f1f1;
    font-size: 27px;
}

.logout-box h2 {
    margin: 0 0 10px;
    font-size: 23px;
}

.logout-box p {
    margin: 0 0 24px;
    color: #777;
    font-size: 14px;
    line-height: 1.6;
}

.logout-actions {
    display: flex;
    gap: 12px;
}

.btn-cancel,
.btn-logout {
    flex: 1;
    padding: 12px;
    border-radius: 7px;
    font-size: 14px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.btn-cancel {
    border: 1px solid #ddd;
    background: #fff;
    color: #191919;
}

.btn-logout {
    border: 1px solid #191919;
    background: #191919;
    color: #fff;
}

.btn-cancel:hover {
    background: #f5f5f5;
}

.btn-logout:hover {
    background: #333;
}

@keyframes logoutShow {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@media (max-width: 400px) {
    .logout-box {
        padding: 24px 20px;
    }
}
</style>
</head>

<body>

<div class="logout-overlay">
    <div class="logout-box">

        <div class="logout-icon">↪</div>

        <h2>Logout?</h2>

        <p>
            Apakah kamu yakin ingin keluar dari akun SOLE STUDIO?
        </p>

        <div class="logout-actions">

            <a href="index.php" class="btn-cancel">
                Cancel
            </a>

            <form method="POST" action="logout.php"
                  style="display:flex;flex:1">
                <button
                    type="submit"
                    name="confirm_logout"
                    value="1"
                    class="btn-logout"
                    style="width:100%;font-family:inherit">
                    Logout
                </button>
            </form>

        </div>

    </div>
</div>

</body>
</html>
```