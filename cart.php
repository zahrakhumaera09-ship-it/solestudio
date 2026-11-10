
<?php
require "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cartId = (int)($_POST["cart_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "remove") {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $cartId, $uid);
        $stmt->execute();
    } elseif ($action === "update") {
        $qty = (int)($_POST["quantity"] ?? 1);

        $stmt = $conn->prepare(
            "SELECT c.quantity, p.stock FROM cart c
             JOIN products p ON p.id=c.product_id
             WHERE c.id=? AND c.user_id=?"
        );
        $stmt->bind_param("ii", $cartId, $uid);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();

        if ($item) {
            if ($qty < 1) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
                $stmt->bind_param("ii", $cartId, $uid);
                $stmt->execute();
            } elseif ($qty <= (int)$item["stock"]) {
                $stmt = $conn->prepare(
                    "UPDATE cart SET quantity=? WHERE id=? AND user_id=?"
                );
                $stmt->bind_param("iii", $qty, $cartId, $uid);
                $stmt->execute();
            } else {
                $message = "Jumlah melebihi stok yang tersedia.";
            }
        }
    }

    if ($message === "") {
        header("Location: cart.php");
        exit;
    }
}

$stmt = $conn->prepare(
    "SELECT c.id AS cart_id, c.size, c.quantity,
            p.name, p.price, p.image, p.stock
     FROM cart c
     JOIN products p ON p.id=c.product_id
     WHERE c.user_id=? ORDER BY c.id DESC"
);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $items[] = $item;
    $total += (float)$item["price"] * (int)$item["quantity"];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your Bag | SOLE STUDIO</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;color:#191919}
a{text-decoration:none;color:inherit}
nav{padding:24px 7%;border-bottom:1px solid #eee;font-weight:bold;font-size:23px;letter-spacing:2px}
main{width:88%;max-width:1050px;margin:45px auto}
h1{font-size:32px;margin-bottom:8px}
.sub{color:#777;font-size:13px;margin-bottom:30px}
.layout{display:grid;grid-template-columns:1fr 300px;gap:35px}
.item{display:grid;grid-template-columns:115px 1fr;gap:20px;padding:20px 0;border-bottom:1px solid #eee}
.item img{width:115px;height:125px;object-fit:cover;background:#f4f4f4}
.item h3{font-size:16px;margin:5px 0 10px}
.muted{font-size:12px;color:#777;line-height:1.8}
.itemprice{font-weight:bold;font-size:14px;margin:10px 0}
.controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
input{width:65px;padding:8px;border:1px solid #ddd}
button,.btn{padding:10px 13px;background:#191919;color:white;border:0;cursor:pointer;font-size:12px}
.remove{background:white;color:#555;border:1px solid #ddd}
.summary{background:#f6f5f2;padding:24px;height:fit-content}
.summary h2{font-size:18px;margin-bottom:22px}
.total{display:flex;justify-content:space-between;gap:10px;font-weight:bold;margin:20px 0}
.checkout{display:block;text-align:center;padding:14px;background:#191919;color:white;font-size:12px}
.back{display:inline-block;margin-top:22px;color:#777;font-size:13px}
.empty{padding:25px 0;color:#777}
.error{color:#b42318;font-size:13px;margin-bottom:15px}
@media(max-width:750px){.layout{grid-template-columns:1fr}main{width:90%;margin:30px auto}.summary{order:-1}}
@media(max-width:430px){.item{grid-template-columns:85px 1fr;gap:12px}.item img{width:85px;height:100px}h1{font-size:27px}}
</style>
</head>
<body>
<nav><a href="index.php">SOLE STUDIO.</a></nav>
<main>
    <h1>Your Shopping Bag</h1>
    <p class="sub">Review your items before checkout.</p>

    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!$items): ?>
        <p class="empty">Keranjangmu masih kosong.</p>
        <a class="btn" href="index.php">EXPLORE COLLECTION</a>
    <?php else: ?>
    <div class="layout">
        <div>
        <?php foreach ($items as $item): ?>
            <article class="item">
                <img src="<?= htmlspecialchars($item["image"], ENT_QUOTES, "UTF-8") ?>"
                     alt="<?= htmlspecialchars($item["name"], ENT_QUOTES, "UTF-8") ?>">
                <div>
                    <h3><?= htmlspecialchars($item["name"]) ?></h3>
                    <p class="muted">Size EU: <?= (int)$item["size"] ?></p>
                    <p class="muted">Stok: <?= (int)$item["stock"] ?></p>
                    <p class="itemprice">Rp <?= number_format($item["price"],0,",",".") ?></p>

                    <div class="controls">
                        <form method="POST" class="controls">
                            <input type="hidden" name="cart_id" value="<?= (int)$item["cart_id"] ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="number" name="quantity"
                                   min="0" max="<?= (int)$item["stock"] ?>"
                                   value="<?= (int)$item["quantity"] ?>" required>
                            <button type="submit">UPDATE</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?= (int)$item["cart_id"] ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="remove">REMOVE</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        </div>

        <aside class="summary">
            <h2>Order Summary</h2>
            <p class="muted">Subtotal</p>
            <div class="total">
                <span>Total</span>
                <span>Rp <?= number_format($total,0,",",".") ?></span>
            </div>
            <p class="muted">Ongkos kirim belum termasuk.</p>
            <a class="checkout" href="checkout.php">PROCEED TO CHECKOUT</a>
        </aside>
    </div>
    <?php endif; ?>

    <a href="index.php" class="back">
</main>
</body>
</html>
