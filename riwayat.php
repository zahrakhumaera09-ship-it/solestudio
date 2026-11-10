
<?php
session_start();
require "koneksi.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$pesanan = [];
$error = "";

// Ambil riwayat pesanan milik pengguna yang sedang login
$sql = "
    SELECT
        o.id AS order_id,
        o.total,
        o.status,
        o.created_at,
        oi.quantity,
        oi.price,
        p.name AS product_name,
        p.image AS product_image
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.id DESC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $order_id = $row["order_id"];

        if (!isset($pesanan[$order_id])) {
            $pesanan[$order_id] = [
                "id" => $order_id,
                "total" => $row["total"],
                "status" => $row["status"],
                "tanggal" => $row["created_at"],
                "items" => []
            ];
        }

        if ($row["product_name"] !== null) {
            $pesanan[$order_id]["items"][] = [
                "nama" => $row["product_name"],
                "gambar" => $row["product_image"],
                "jumlah" => $row["quantity"],
                "harga" => $row["price"]
            ];
        }
    }

    $stmt->close();
} else {
    $error = "Riwayat pesanan belum dapat ditampilkan. Periksa struktur tabel database.";
}

// Format harga rupiah
function rupiah($angka) {
    return "Rp " . number_format((float) $angka, 0, ",", ".");
}

// Format status pesanan
function statusPesanan($status) {
    $status = strtolower(trim((string) $status));

    switch ($status) {
        case "pending":
            return "Menunggu Pembayaran";
        case "paid":
            return "Sudah Dibayar";
        case "processing":
            return "Sedang Diproses";
        case "shipped":
            return "Sedang Dikirim";
        case "completed":
            return "Selesai";
        case "cancelled":
            return "Dibatalkan";
        default:
            return $status !== "" ? ucfirst($status) : "Belum Ada Status";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Pesanan | SOLE STUDIO</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f6f5f2;
    color: #191919;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 7%;
    background: #fff;
    border-bottom: 1px solid #eaeaea;
}

.logo {
    font-size: 23px;
    font-weight: bold;
    letter-spacing: 2px;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 25px;
    font-size: 14px;
}

.nav-menu a {
    color: #555;
}

.nav-menu a:hover,
.nav-menu .active {
    color: #191919;
    font-weight: bold;
}

/* Konten */
.container {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 45px 25px 70px;
}

.page-heading {
    margin-bottom: 30px;
}

.page-heading .eyebrow {
    font-size: 11px;
    letter-spacing: 3px;
    color: #777;
    margin-bottom: 12px;
}

.page-heading h1 {
    margin: 0 0 12px;
    font-size: clamp(28px, 4vw, 38px);
    letter-spacing: -1px;
}

.page-heading p {
    margin: 0;
    color: #777;
    font-size: 14px;
    line-height: 1.7;
}

/* Kartu pesanan */
.order-card {
    background: #fff;
    border: 1px solid #e9e7e3;
    border-radius: 12px;
    margin-bottom: 22px;
    overflow: hidden;
}

.order-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 20px 24px;
    border-bottom: 1px solid #eee;
    flex-wrap: wrap;
}

.order-number {
    font-weight: bold;
    font-size: 15px;
    margin-bottom: 7px;
}

.order-date {
    font-size: 12px;
    color: #777;
}

.status {
    display: inline-block;
    padding: 8px 12px;
    border-radius: 20px;
    background: #f1f1f1;
    color: #333;
    font-size: 11px;
    font-weight: bold;
}

.status-paid,
.status-completed {
    background: #e8f5e9;
    color: #236b31;
}

.status-cancelled {
    background: #fff0f0;
    color: #a21d1d;
}

.status-shipped,
.status-processing {
    background: #eaf2ff;
    color: #2456a6;
}

.order-body {
    padding: 10px 24px;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 17px;
    padding: 16px 0;
    border-bottom: 1px solid #f0efed;
}

.product-item:last-child {
    border-bottom: none;
}

.product-image {
    width: 85px;
    height: 85px;
    flex-shrink: 0;
    object-fit: cover;
    border-radius: 8px;
    background: #f2f2f2;
}

.product-placeholder {
    width: 85px;
    height: 85px;
    flex-shrink: 0;
    border-radius: 8px;
    background: #f0efed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #777;
}

.product-info {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 8px;
    overflow-wrap: anywhere;
}

.product-quantity {
    color: #777;
    font-size: 12px;
    margin-bottom: 8px;
}

.product-price {
    font-size: 13px;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 20px 24px;
    background: #fcfbfa;
    border-top: 1px solid #eee;
}

.total-label {
    font-size: 12px;
    color: #777;
    margin-bottom: 6px;
}

.total-price {
    font-size: 19px;
    font-weight: bold;
}

.btn-shop {
    display: inline-block;
    background: #191919;
    color: #fff;
    padding: 12px 18px;
    border-radius: 6px;
    font-size: 12px;
}

.btn-shop:hover {
    background: #333;
}

/* Pesanan kosong */
.empty-state {
    background: #fff;
    border: 1px solid #e9e7e3;
    border-radius: 12px;
    padding: 55px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 42px;
    margin-bottom: 15px;
}

.empty-state h2 {
    font-size: 21px;
    margin: 0 0 10px;
}

.empty-state p {
    color: #777;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 24px;
}

.error-message {
    padding: 15px;
    background: #fff0f0;
    color: #a21d1d;
    border-radius: 8px;
    font-size: 14px;
}

/* Footer */
.footer {
    text-align: center;
    color: #888;
    font-size: 12px;
    padding: 25px;
    border-top: 1px solid #e9e7e3;
}

/* Responsif */
@media (max-width: 600px) {
    .navbar {
        padding: 18px 5%;
        gap: 15px;
    }

    .logo {
        font-size: 18px;
        letter-spacing: 1px;
    }

    .nav-menu {
        gap: 13px;
        font-size: 12px;
    }

    .container {
        padding: 32px 15px 50px;
    }

    .order-header,
    .order-footer {
        padding: 17px;
    }

    .order-body {
        padding: 8px 17px;
    }

    .product-image,
    .product-placeholder {
        width: 70px;
        height: 70px;
    }

    .total-price {
        font-size: 16px;
    }

    .btn-shop {
        padding: 11px 12px;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="logo">SOLE STUDIO.</a>

    <div class="nav-menu">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="riwayat.php" class="active">Pesanan</a>
    </div>
</nav>

<main class="container">

    <div class="page-heading">
        <div class="eyebrow">YOUR ACCOUNT</div>
        <h1>Riwayat Pesanan</h1>
        <p>Lihat daftar pesanan dan status pembelian kamu di SOLE STUDIO.</p>
    </div>

    <?php if ($error !== ""): ?>

        <div class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>

    <?php elseif (empty($pesanan)): ?>

        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h2>Belum Ada Pesanan</h2>
            <p>
                Kamu belum memiliki riwayat pesanan.
                Yuk, temukan sepatu favoritmu sekarang.
            </p>
            <a href="index.php" class="btn-shop">
                MULAI BELANJA
            </a>
        </div>

    <?php else: ?>

        <?php foreach ($pesanan as $order): ?>

            <?php
            $status = strtolower(trim((string) $order["status"]));

            $statusClass = in_array(
                $status,
                ["paid", "completed", "cancelled", "shipped", "processing"],
                true
            ) ? "status-" . $status : "";
            ?>

            <section class="order-card">

                <div class="order-header">
                    <div>
                        <div class="order-number">
                            Pesanan #<?= (int) $order["id"] ?>
                        </div>

                        <div class="order-date">
                            <?= !empty($order["tanggal"])
                                ? date("d M Y, H:i", strtotime($order["tanggal"]))
                                : "Tanggal tidak tersedia" ?>
                        </div>
                    </div>

                    <span class="status <?= htmlspecialchars($statusClass) ?>">
                        <?= htmlspecialchars(statusPesanan($status), ENT_QUOTES, "UTF-8") ?>
                    </span>
                </div>

                <div class="order-body">

                    <?php foreach ($order["items"] as $item): ?>

                        <div class="product-item">

                            <?php if (!empty($item["gambar"])): ?>
                                <img
                                    class="product-image"
                                    src="<?= htmlspecialchars($item["gambar"], ENT_QUOTES, "UTF-8") ?>"
                                    alt="<?= htmlspecialchars($item["nama"], ENT_QUOTES, "UTF-8") ?>"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                >
                                <div class="product-placeholder" style="display:none">
                                    Sepatu
                                </div>
                            <?php else: ?>
                                <div class="product-placeholder">
                                    Sepatu
                                </div>
                            <?php endif; ?>

                            <div class="product-info">
                                <div class="product-name">
                                    <?= htmlspecialchars($item["nama"], ENT_QUOTES, "UTF-8") ?>
                                </div>

                                <div class="product-quantity">
                                    <?= (int) $item["jumlah"] ?> barang
                                </div>

                                <div class="product-price">
                                    <?= rupiah($item["harga"]) ?>
                                </div>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="order-footer">
                    <div>
                        <div class="total-label">Total Pembayaran</div>
                        <div class="total-price">
                            <?= rupiah($order["total"]) ?>
                        </div>
                    </div>

                    <a href="index.php" class="btn-shop">
                        BELANJA LAGI
                    </a>
                </div>

            </section>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<footer class="footer">
    &copy; <?= date("Y") ?> SOLE STUDIO. All rights reserved.
</footer>

</body>
</html>
