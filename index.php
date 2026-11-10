
<?php
require "koneksi.php";

$keyword = trim($_GET["q"] ?? "");

if ($keyword !== "") {
    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE name LIKE ? OR brand LIKE ? ORDER BY id DESC"
    );
    $search = "%".$keyword."%";
    $stmt->bind_param("ss", $search, $search);
} else {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
}

$stmt->execute();
$products = $stmt->get_result();

$cartCount = 0;

if (isset($_SESSION["user_id"])) {
    $uid = (int) $_SESSION["user_id"];

    $count = $conn->prepare(
        "SELECT COALESCE(SUM(quantity),0) AS total
         FROM cart WHERE user_id=?"
    );
    $count->bind_param("i", $uid);
    $count->execute();

    $cartCount = (int) $count->get_result()->fetch_assoc()["total"];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>SOLE STUDIO | Modern Footwear</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    color: #191919;
    background: #fff;
}

a {
    text-decoration: none;
    color: inherit;
}

/* NAVBAR */
nav {
    min-height: 76px;
    padding: 14px 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 1px solid #eee;
    background: #fff;
}

.logo {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 2px;
    white-space: nowrap;
}

.navlinks {
    display: flex;
    gap: 25px;
    align-items: center;
    font-size: 14px;
}

.navlinks a {
    transition: color 0.2s ease;
}

.navlinks a:hover {
    color: #888;
}

/* MENU PESANAN */
.orderlink {
    font-weight: 500;
}

.cartlink {
    border: 1px solid #222;
    padding: 10px 15px;
    border-radius: 4px;
    white-space: nowrap;
}

.cartlink:hover {
    background: #191919;
    color: #fff !important;
}

/* HERO */
.hero {
    margin: 28px auto;
    width: 90%;
    min-height: 390px;
    background: #f1f0ec;
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    border-radius: 6px;
    overflow: hidden;
}

.hero-text {
    padding: 50px;
}

.eyebrow {
    font-size: 11px;
    letter-spacing: 3px;
    color: #777;
    margin-bottom: 18px;
}

.hero h1 {
    font-size: clamp(34px, 4.5vw, 58px);
    line-height: 1.08;
    letter-spacing: -2px;
    margin-bottom: 18px;
}

.hero p {
    line-height: 1.8;
    color: #666;
    font-size: 14px;
    margin-bottom: 25px;
}

.btn {
    display: inline-block;
    background: #191919;
    color: #fff;
    padding: 14px 22px;
    font-size: 12px;
    letter-spacing: 1px;
    border: 0;
    cursor: pointer;
}

.btn:hover {
    background: #383838;
}

.hero img {
    width: 100%;
    height: 390px;
    object-fit: cover;
}

/* COLLECTION */
.section {
    width: 90%;
    margin: 65px auto;
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 12px;
}

.section h2 {
    font-size: 28px;
    letter-spacing: -1px;
}

.search {
    display: flex;
    gap: 8px;
}

.search input {
    width: 180px;
    padding: 11px;
    border: 1px solid #ddd;
    outline: none;
}

.search input:focus {
    border-color: #191919;
}

.search button {
    padding: 10px 14px;
    background: #222;
    color: #fff;
    border: 0;
    cursor: pointer;
}

.products {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.product {
    min-width: 0;
}

.product img {
    width: 100%;
    aspect-ratio: 4 / 4.5;
    object-fit: cover;
    background: #f4f4f4;
}

.product h3 {
    font-size: 15px;
    margin: 14px 0 6px;
}

.brand {
    font-size: 11px;
    color: #888;
    letter-spacing: 1px;
}

.price {
    font-size: 14px;
    font-weight: bold;
    margin: 10px 0 14px;
}

.product .btn {
    padding: 11px 14px;
    font-size: 11px;
}

.empty {
    color: #777;
    padding: 30px 0;
}

/* FOOTER */
footer {
    margin-top: 70px;
    background: #171717;
    color: #fff;
    padding: 35px 7%;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

footer p {
    font-size: 12px;
    color: #aaa;
    margin-top: 8px;
}

/* TABLET */
@media (max-width: 800px) {
    nav {
        padding: 14px 5%;
        min-height: 68px;
    }

    .navlinks {
        gap: 14px;
        font-size: 12px;
    }

    .hero {
        grid-template-columns: 1fr;
    }

    .hero-text {
        padding: 30px;
    }

    .hero img {
        height: 260px;
    }

    .products {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .section-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .search {
        width: 100%;
    }

    .search input {
        flex: 1;
        min-width: 0;
    }
}

/* HP */
@media (max-width: 520px) {
    nav {
        align-items: flex-start;
        flex-direction: column;
        gap: 15px;
        padding: 18px 5%;
    }

    .logo {
        font-size: 21px;
    }

    .navlinks {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 13px;
    }

    .cartlink {
        padding: 8px 10px;
    }

    .hero h1 {
        font-size: 37px;
    }

    .section h2 {
        font-size: 24px;
    }

    .product .btn {
        padding: 10px;
        font-size: 10px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav>
    <a href="index.php" class="logo">SOLE STUDIO.</a>

    <div class="navlinks">
        <a href="index.php#collection">Shop</a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="riwayat.php" class="orderlink">Pesanan</a>

            <a href="logout.php">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>

        <?php endif; ?>

        <a href="cart.php" class="cartlink">
            Bag (<?= $cartCount ?>)
        </a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-text">
        <div class="eyebrow">SOLE STUDIO / COLLECTION 2026</div>

        <h1>Find Your<br>Perfect Pair.</h1>

        <p>
            Everyday essentials. Timeless design.<br>
            Temukan sepatu yang cocok untuk setiap langkahmu.
        </p>

        <a href="#collection" class="btn">
            EXPLORE COLLECTION →
        </a>
    </div>

    <img
        src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1000"
        alt="Koleksi sneakers"
    >
</section>

<!-- KOLEKSI PRODUK -->
<section class="section" id="collection">

    <div class="section-head">
        <div>
            <div class="eyebrow">MADE FOR EVERYDAY</div>
            <h2>Our Collection</h2>
        </div>

        <form class="search" method="GET" action="index.php">
            <input
                type="search"
                name="q"
                placeholder="Cari sepatu..."
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit">Search</button>
        </form>
    </div>

    <div class="products">

        <?php if ($products->num_rows > 0): ?>

            <?php while ($p = $products->fetch_assoc()): ?>

                <article class="product">

                    <a href="detail.php?id=<?= (int) $p["id"] ?>">
                        <img
                            src="<?= htmlspecialchars($p["image"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                            alt="<?= htmlspecialchars($p["name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                        >
                    </a>

                    <div class="brand">
                        <?= htmlspecialchars($p["brand"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </div>

                    <h3>
                        <?= htmlspecialchars($p["name"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </h3>

                    <div class="price">
                        Rp <?= number_format((float) $p["price"], 0, ",", ".") ?>
                    </div>

                    <a
                        class="btn"
                        href="detail.php?id=<?= (int) $p["id"] ?>"
                    >
                        VIEW PRODUCT
                    </a>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty">Produk tidak ditemukan.</p>

        <?php endif; ?>

    </div>
</section>

<!-- FOOTER -->
<footer>
    <div>
        <div class="logo">SOLE STUDIO.</div>
        <p>Simple shoes. Timeless style.</p>
    </div>

    <p>© <?= date("Y") ?> SOLE STUDIO. All rights reserved.</p>
</footer>

</body>
</html>
