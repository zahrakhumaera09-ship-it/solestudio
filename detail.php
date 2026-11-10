
<?php
require "koneksi.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    $size = (int)($_POST["size"] ?? 0);
    $qty = (int)($_POST["quantity"] ?? 1);
    $uid = (int)$_SESSION["user_id"];

    if (!in_array($size, [38,39,40,41,42,43,44], true)
        || $qty < 1 || $qty > (int)$product["stock"]) {
        $error = "Ukuran atau jumlah produk tidak valid.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO cart (user_id, product_id, size, quantity)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
        );
        $stmt->bind_param("iiii", $uid, $id, $size, $qty);

        if ($stmt->execute()) {
            header("Location: cart.php");
            exit;
        }
        $error = "Produk gagal ditambahkan. Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($product["name"]) ?> | SOLE STUDIO</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;color:#191919}
a{text-decoration:none;color:inherit}
nav{padding:25px 7%;border-bottom:1px solid #eee;font-size:23px;font-weight:bold;letter-spacing:2px}
main{width:86%;max-width:1000px;margin:55px auto;display:grid;
grid-template-columns:1fr 1fr;gap:55px;align-items:center}
img{width:100%;aspect-ratio:4/4.5;object-fit:cover;background:#f4f4f4}
.back{display:inline-block;margin-bottom:25px;color:#777;font-size:13px}
h1{font-size:36px;letter-spacing:-1px;margin:12px 0}
.brand{font-size:11px;letter-spacing:2px;color:#888}
.price{font-size:22px;font-weight:bold;margin:20px 0}
.desc{font-size:14px;line-height:1.8;color:#666;margin-bottom:25px}
label{display:block;font-size:13px;font-weight:bold;margin:17px 0 9px}
select,input{padding:12px;border:1px solid #ddd;background:white}
select{width:100%}
input{width:100%}
button{width:100%;padding:15px;background:#191919;color:white;border:0;
margin-top:20px;cursor:pointer}
.error{color:#b42318;font-size:13px;margin:12px 0}
.stock{font-size:12px;color:#777;margin-top:15px}
@media(max-width:700px){main{grid-template-columns:1fr;gap:25px;margin:25px auto;width:90%}h1{font-size:30px}}
</style>
</head>
<body>
<nav><a href="index.php">SOLE STUDIO.</a></nav>
<main>
    <div>
        <img src="<?= htmlspecialchars($product["image"], ENT_QUOTES, "UTF-8") ?>"
             alt="<?= htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") ?>">
    </div>
    <div>
        <a href="index.php" class="back">← Back to collection</a>
        <div class="brand"><?= htmlspecialchars($product["brand"]) ?></div>
        <h1><?= htmlspecialchars($product["name"]) ?></h1>
        <div class="price">Rp <?= number_format($product["price"],0,",",".") ?></div>
        <p class="desc"><?= nl2br(htmlspecialchars($product["description"] ?? "")) ?></p>
        <p class="stock">Stock available: <?= (int)$product["stock"] ?></p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="size">Choose Size (EU)</label>
            <select name="size" id="size" required>
                <option value="">Select your size</option>
                <?php foreach ([38,39,40,41,42,43,44] as $s): ?>
                    <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity"
                   value="1" min="1" max="<?= (int)$product["stock"] ?>" required>

            <button type="submit" <?= (int)$product["stock"] < 1 ? "disabled" : "" ?>>
                ADD TO BAG
            </button>
        </form>
    </div>
</main>
</body>
</html>
