
<?php
require "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION["user_id"];
$error = "";
$success = false;
$orderId = 0;

$stmt = $conn->prepare(
    "SELECT c.product_id, c.size, c.quantity, p.name, p.price, p.stock
     FROM cart c JOIN products p ON p.id=c.product_id
     WHERE c.user_id=?"
);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += (float)$row["price"] * (int)$row["quantity"];
}

if (!$items) {
    header("Location: cart.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recipient = trim($_POST["recipient"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($recipient === "" || strlen($recipient) > 100 ||
        $phone === "" || strlen($phone) > 25 ||
        $address === "" || strlen($address) > 2000) {
        $error = "Lengkapi nama penerima, nomor telepon, dan alamat.";
    } else {
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT c.product_id, c.size, c.quantity,
                        p.name, p.price, p.stock
                 FROM cart c JOIN products p ON p.id=c.product_id
                 WHERE c.user_id=? FOR UPDATE"
            );
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $lockedItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (!$lockedItems) {
                throw new Exception("Keranjang kosong.");
            }

            $total = 0;
            foreach ($lockedItems as $item) {
                if ((int)$item["quantity"] < 1 ||
                    (int)$item["quantity"] > (int)$item["stock"]) {
                    throw new Exception("Stok tidak cukup untuk " . $item["name"]);
                }
                $total += (float)$item["price"] * (int)$item["quantity"];
            }

            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, recipient, phone, address, total)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssd", $uid, $recipient, $phone, $address, $total);
            $stmt->execute();
            $orderId = $conn->insert_id;

            $insertItem = $conn->prepare(
                "INSERT INTO order_items
                 (order_id, product_id, product_name, size, price, quantity)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $updateStock = $conn->prepare(
                "UPDATE products SET stock=stock-?
                 WHERE id=? AND stock>=?"
            );

            foreach ($lockedItems as $item) {
                $pid = (int)$item["product_id"];
                $size = (int)$item["size"];
                $qty = (int)$item["quantity"];
                $name = $item["name"];
                $price = (float)$item["price"];

                $insertItem->bind_param(
                    "iisidi", $orderId, $pid, $name, $size, $price, $qty
                );
                $insertItem->execute();

                $updateStock->bind_param("iii", $qty, $pid, $qty);
                $updateStock->execute();

                if ($updateStock->affected_rows !== 1) {
                    throw new Exception("Stok berubah. Silakan coba lagi.");
                }
            }

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
            $stmt->bind_param("i", $uid);
            $stmt->execute();

            $conn->commit();
            $success = true;
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e instanceof mysqli_sql_exception
                ? "Pesanan gagal disimpan. Silakan coba lagi."
                : $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout | SOLE STUDIO</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;color:#191919}
a{color:inherit;text-decoration:none}
nav{padding:24px 7%;border-bottom:1px solid #eee;font-size:23px;font-weight:bold;letter-spacing:2px}
main{width:90%;max-width:950px;margin:45px auto}
h1{font-size:32px;margin-bottom:8px}
.sub{font-size:13px;color:#777;margin-bottom:30px}
.layout{display:grid;grid-template-columns:1fr 300px;gap:35px}
.panel{border:1px solid #eee;padding:25px}
h2{font-size:18px;margin-bottom:22px}
label{display:block;font-size:13px;margin:18px 0 8px}
input,textarea{width:100%;padding:13px;border:1px solid #ddd;font:inherit}
textarea{min-height:110px;resize:vertical}
button,.btn{display:block;width:100%;text-align:center;background:#191919;color:white;
border:0;padding:15px;margin-top:22px;font-size:12px;cursor:pointer}
.summary{background:#f6f5f2;padding:25px;height:fit-content}
.line{display:flex;justify-content:space-between;gap:15px;font-size:13px;margin:15px 0}
.total{border-top:1px solid #ddd;padding-top:18px;font-weight:bold}
.error,.success{padding:13px;margin-bottom:20px;font-size:13px;line-height:1.6}
.error{background:#fff0f0;color:#a21d1d}
.success{background:#edf8ee;color:#236b32}
@media(max-width:700px){.layout{grid-template-columns:1fr}.summary{order:-1}main{margin:30px auto}}
</style>
</head>
<body>
<nav><a href="index.php">SOLE STUDIO.</a></nav>
<main>
    <h1>Checkout</h1>
    <p class="sub">Almost there. Complete your shipping details.</p>

    <?php if ($success): ?>
        <div class="success">
            Pesanan berhasil dibuat! Nomor pesanan kamu adalah
            <strong>#<?= (int)$orderId ?></strong>.
            <br>Status pesanan: Pending.
        </div>
        <a class="btn" href="index.php">CONTINUE SHOPPING</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="layout">
            <form method="POST" class="panel">
                <h2>Shipping Information</h2>

                <label for="recipient">Nama Penerima</label>
                <input id="recipient" name="recipient" maxlength="100"
                       value="<?= htmlspecialchars($_POST["recipient"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>

                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="tel" name="phone" maxlength="25"
                       value="<?= htmlspecialchars($_POST["phone"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>

                <label for="address">Alamat Lengkap</label>
                <textarea id="address" name="address" maxlength="2000" required><?= htmlspecialchars($_POST["address"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>

                <button type="submit">PLACE ORDER</button>
            </form>

            <aside class="summary">
                <h2>Order Summary</h2>
                <?php foreach ($items as $item): ?>
                    <div class="line">
                        <span><?= htmlspecialchars($item["name"]) ?>
                            × <?= (int)$item["quantity"] ?>
                            <br><small>Size <?= (int)$item["size"] ?></small>
                        </span>
                        <span>Rp <?= number_format($item["price"]*$item["quantity"],0,",",".") ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="line total">
                    <span>Total</span>
                    <span>Rp <?= number_format($total,0,",",".") ?></span>
                </div>
                <small>Ongkos kirim belum dihitung.</small>
            </aside>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
