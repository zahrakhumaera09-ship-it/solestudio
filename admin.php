
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "koneksi.php";

// Pastikan hanya admin yang bisa masuk
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}

$pesan = "";
$jenisPesan = "success";

// Daftar status pesanan
$statusPesanan = [
    "Pending",
    "Diproses",
    "Dikirim",
    "Selesai",
    "Dibatalkan"
];

// Fungsi untuk mengamankan teks HTML
function aman($teks) {
    return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8");
}

// Ambil data produk yang sedang diedit
$produkEdit = null;

if (isset($_GET["edit"]) && ctype_digit($_GET["edit"])) {
    $idEdit = (int) $_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();

    $hasilEdit = $stmt->get_result();
    $produkEdit = $hasilEdit->fetch_assoc();

    $stmt->close();
}

// Proses formulir
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    // TAMBAH PRODUK
    if ($aksi === "tambah_produk") {
        $nama = trim($_POST["name"] ?? "");
        $brand = trim($_POST["brand"] ?? "");
        $deskripsi = trim($_POST["description"] ?? "");
        $harga = (int) ($_POST["price"] ?? 0);
        $gambar = trim($_POST["image"] ?? "");
        $stok = (int) ($_POST["stock"] ?? 0);

        if (
            $nama === "" ||
            $brand === "" ||
            $harga <= 0 ||
            $stok < 0 ||
            $gambar === ""
        ) {
            $pesan = "Lengkapi data produk dengan benar.";
            $jenisPesan = "error";
        } elseif (
            !filter_var($gambar, FILTER_VALIDATE_URL) ||
            !in_array(strtolower((string) parse_url($gambar, PHP_URL_SCHEME)), ["http", "https"], true)
        ) {
            $pesan = "URL foto harus berupa alamat gambar yang valid.";
            $jenisPesan = "error";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO products
                (name, brand, description, price, image, stock)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssisi",
                $nama,
                $brand,
                $deskripsi,
                $harga,
                $gambar,
                $stok
            );

            if ($stmt->execute()) {
                $pesan = "Produk berhasil ditambahkan!";
            } else {
                $pesan = "Produk gagal ditambahkan.";
                $jenisPesan = "error";
            }

            $stmt->close();
        }
    }

    // EDIT PRODUK
    elseif ($aksi === "edit_produk") {
        $id = (int) ($_POST["id"] ?? 0);
        $nama = trim($_POST["name"] ?? "");
        $brand = trim($_POST["brand"] ?? "");
        $deskripsi = trim($_POST["description"] ?? "");
        $harga = (int) ($_POST["price"] ?? 0);
        $gambar = trim($_POST["image"] ?? "");
        $stok = (int) ($_POST["stock"] ?? 0);

        if (
            $id <= 0 ||
            $nama === "" ||
            $brand === "" ||
            $harga <= 0 ||
            $stok < 0 ||
            $gambar === ""
        ) {
            $pesan = "Data produk belum lengkap atau tidak valid.";
            $jenisPesan = "error";
        } elseif (
            !filter_var($gambar, FILTER_VALIDATE_URL) ||
            !in_array(strtolower((string) parse_url($gambar, PHP_URL_SCHEME)), ["http", "https"], true)
        ) {
            $pesan = "URL foto harus berupa alamat gambar yang valid.";
            $jenisPesan = "error";
        } else {
            $stmt = $conn->prepare(
                "UPDATE products
                SET name = ?, brand = ?, description = ?,
                    price = ?, image = ?, stock = ?
                WHERE id = ?"
            );

            $stmt->bind_param(
                "sssisii",
                $nama,
                $brand,
                $deskripsi,
                $harga,
                $gambar,
                $stok,
                $id
            );

            // Format parameter diperbaiki untuk tipe data:
            // string, string, string, integer, string, integer, integer.
            // Gunakan prepared statement baru dengan format yang benar.
            $stmt->close();

            $stmt = $conn->prepare(
                "UPDATE products
                SET name = ?, brand = ?, description = ?,
                    price = ?, image = ?, stock = ?
                WHERE id = ?"
            );

            $stmt->bind_param(
                "sss isii",
                $nama,
                $brand,
                $deskripsi,
                $harga,
                $gambar,
                $stok,
                $id
            );

            if ($stmt->execute()) {
                $pesan = "Produk berhasil diperbarui!";
            } else {
                $pesan = "Produk gagal diperbarui.";
                $jenisPesan = "error";
            }

            $stmt->close();
        }
    }

    // HAPUS PRODUK
    elseif ($aksi === "hapus_produk") {
        $id = (int) ($_POST["id"] ?? 0);

        if ($id > 0) {
            try {
                $stmt = $conn->prepare(
                    "DELETE FROM products WHERE id = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $pesan = "Produk berhasil dihapus.";
                } else {
                    $pesan = "Produk tidak ditemukan.";
                    $jenisPesan = "error";
                }

                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                $pesan = "Produk tidak bisa dihapus karena sudah terkait dengan data pesanan.";
                $jenisPesan = "error";
            }
        }
    }

    // UBAH STATUS PESANAN
    elseif ($aksi === "ubah_status") {
        $idOrder = (int) ($_POST["order_id"] ?? 0);
        $status = $_POST["status"] ?? "";

        if (
            $idOrder > 0 &&
            in_array($status, $statusPesanan, true)
        ) {
            $stmt = $conn->prepare(
                "UPDATE orders SET status = ? WHERE id = ?"
            );

            $stmt->bind_param("si", $status, $idOrder);

            if ($stmt->execute()) {
                $pesan = "Status pesanan berhasil diperbarui.";
            } else {
                $pesan = "Status pesanan gagal diperbarui.";
                $jenisPesan = "error";
            }

            $stmt->close();
        } else {
            $pesan = "Data status pesanan tidak valid.";
            $jenisPesan = "error";
        }
    }
}

// Hitung ringkasan dashboard
$totalProduk = (int) $conn
    ->query("SELECT COUNT(*) AS total FROM products")
    ->fetch_assoc()["total"];

$totalPesanan = (int) $conn
    ->query("SELECT COUNT(*) AS total FROM orders")
    ->fetch_assoc()["total"];

$totalPelanggan = (int) $conn
    ->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'")
    ->fetch_assoc()["total"];

$totalPendapatan = (int) $conn
    ->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE status = 'Selesai'")
    ->fetch_assoc()["total"];

// Ambil daftar produk
$produk = $conn->query(
    "SELECT * FROM products ORDER BY id DESC"
);

// Ambil pesanan dan nama pelanggan
$pesanan = $conn->query(
    "SELECT orders.*, users.name AS nama_pelanggan
     FROM orders
     LEFT JOIN users ON orders.user_id = users.id
     ORDER BY orders.id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | SOLE STUDIO</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6fa;
            color: #172033;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .sidebar {
            width: 245px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #111827;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        .logo {
            font-size: 23px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .logo span {
            color: #60a5fa;
        }

        .sidebar small {
            color: #9ca3af;
            margin-bottom: 38px;
        }

        .menu-label {
            font-size: 11px;
            letter-spacing: 1.5px;
            color: #9ca3af;
            margin: 10px 0 14px;
        }

        .menu {
            display: block;
            padding: 14px 15px;
            margin-bottom: 8px;
            color: #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            transition: .2s;
        }

        .menu:hover,
        .menu.active {
            background: #2563eb;
            color: white;
        }

        .sidebar-bottom {
            margin-top: auto;
        }

        .sidebar-bottom a {
            display: block;
            padding: 13px 15px;
            color: #fca5a5;
            border-radius: 10px;
        }

        .sidebar-bottom a:hover {
            background: #374151;
        }

        .main {
            margin-left: 245px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 28px;
        }

        .topbar h1 {
            font-size: 27px;
            margin-bottom: 7px;
        }

        .topbar p {
            color: #6b7280;
            font-size: 14px;
        }

        .admin-badge {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 23px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
        }

        .stat-card p {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .stat-card h2 {
            font-size: 27px;
        }

        .section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 28px;
            scroll-margin-top: 20px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 22px;
        }

        .section-heading h2 {
            font-size: 20px;
        }

        .section-heading p {
            color: #6b7280;
            font-size: 13px;
            margin-top: 7px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 17px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            font-size: 13px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: white;
            color: #111827;
            font-size: 13px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #2563eb;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 9px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: .2s;
        }

        .btn-blue {
            background: #2563eb;
            color: white;
        }

        .btn-blue:hover {
            background: #1d4ed8;
        }

        .btn-gray {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-gray:hover {
            background: #d1d5db;
        }

        .btn-red {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-red:hover {
            background: #fecaca;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .message.success {
            color: #166534;
            background: #dcfce7;
        }

        .message.error {
            color: #991b1b;
            background: #fee2e2;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th,
        td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            vertical-align: middle;
        }

        th {
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .product-image {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 9px;
            background: #f3f4f6;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .muted {
            color: #6b7280;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .actions .btn {
            padding: 9px 11px;
        }

        .status-form {
            display: flex;
            gap: 8px;
            min-width: 230px;
        }

        .status-form select {
            min-width: 135px;
            padding: 9px;
        }

        .status-form .btn {
            padding: 9px 11px;
        }

        .order-items {
            margin-top: 9px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 8px;
            min-width: 210px;
            line-height: 1.8;
        }

        .order-items summary {
            cursor: pointer;
            color: #2563eb;
            font-weight: bold;
        }

        .preview-image {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-top: 5px;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 30px;
        }

        .footer {
            color: #9ca3af;
            text-align: center;
            font-size: 12px;
            padding: 10px 0 25px;
        }

        @media (max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }

            .sidebar small,
            .menu-label,
            .sidebar-bottom {
                display: none;
            }

            .sidebar nav {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 18px;
            }

            .menu {
                margin: 0;
                padding: 10px;
                font-size: 12px;
            }

            .main {
                margin-left: 0;
                padding: 18px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar h1 {
                font-size: 23px;
            }

            .stats {
                gap: 10px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-card h2 {
                font-size: 21px;
            }

            .section {
                padding: 17px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <div class="logo">SOLE <span>STUDIO.</span></div>
    <small>ADMIN PANEL</small>

    <div class="menu-label">MENU UTAMA</div>

    <nav>
        <a href="#dashboard" class="menu active">▦ Dashboard</a>
        <a href="#form-produk" class="menu">＋ Tambah Produk</a>
        <a href="#daftar-produk" class="menu">▣ Kelola Produk</a>
        <a href="#pesanan" class="menu">▤ Kelola Pesanan</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="index.php">← Kembali ke Toko</a>
        <a href="logout.php">↪ Logout</a>
    </div>
</aside>

<main class="main">

    <header class="topbar" id="dashboard">
        <div>
            <h1>Dashboard Admin</h1>
            <p>Kelola produk dan pesanan SOLE STUDIO di sini.</p>
        </div>

        <div class="admin-badge">
            Admin: <?= aman($_SESSION["name"] ?? "Administrator") ?>
        </div>
    </header>

    <?php if ($pesan !== ""): ?>
        <div class="message <?= aman($jenisPesan) ?>">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <section class="stats">
        <div class="stat-card">
            <p>Total Produk</p>
            <h2><?= number_format($totalProduk, 0, ",", ".") ?></h2>
        </div>

        <div class="stat-card">
            <p>Total Pesanan</p>
            <h2><?= number_format($totalPesanan, 0, ",", ".") ?></h2>
        </div>

        <div class="stat-card">
            <p>Total Pelanggan</p>
            <h2><?= number_format($totalPelanggan, 0, ",", ".") ?></h2>
        </div>

        <div class="stat-card">
            <p>Pendapatan Pesanan Selesai</p>
            <h2 style="font-size:21px">
                Rp<?= number_format($totalPendapatan, 0, ",", ".") ?>
            </h2>
        </div>
    </section>

    <!-- FORM TAMBAH / EDIT PRODUK -->
    <section class="section" id="form-produk">
        <div class="section-heading">
            <div>
                <h2>
                    <?= $produkEdit ? "Edit Produk" : "Tambah Produk Baru" ?>
                </h2>
                <p>
                    Isi informasi produk dan masukkan URL foto sepatu.
                </p>
            </div>
        </div>

        <form method="POST" action="#form-produk">
            <input
                type="hidden"
                name="aksi"
                value="<?= $produkEdit ? "edit_produk" : "tambah_produk" ?>"
            >

            <?php if ($produkEdit): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $produkEdit["id"] ?>"
                >
            <?php endif; ?>

            <div class="form-grid">
                <div class="field">
                    <label>Nama Produk</label>
                    <input
                        type="text"
                        name="name"
                        placeholder="Contoh: Adidas Samba OG"
                        value="<?= aman($produkEdit["name"] ?? "") ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Merek</label>
                    <input
                        type="text"
                        name="brand"
                        placeholder="Nike, Adidas, Puma, New Balance"
                        value="<?= aman($produkEdit["brand"] ?? "") ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Harga (Rp)</label>
                    <input
                        type="number"
                        name="price"
                        min="1"
                        placeholder="1500000"
                        value="<?= aman($produkEdit["price"] ?? "") ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Stok</label>
                    <input
                        type="number"
                        name="stock"
                        min="0"
                        placeholder="10"
                        value="<?= aman($produkEdit["stock"] ?? "") ?>"
                        required
                    >
                </div>

                <div class="field full">
                    <label>URL Foto Produk</label>
                    <input
                        type="url"
                        name="image"
                        id="image-url"
                        placeholder="https://images.unsplash.com/..."
                        value="<?= aman($produkEdit["image"] ?? "") ?>"
                        required
                    >

                    <small class="muted">
                        Gunakan alamat gambar langsung, bukan link halaman foto Unsplash.
                    </small>

                    <?php if (!empty($produkEdit["image"])): ?>
                        <img
                            src="<?= aman($produkEdit["image"]) ?>"
                            class="preview-image"
                            alt="Foto produk saat ini"
                        >
                    <?php endif; ?>
                </div>

                <div class="field full">
                    <label>Deskripsi Produk</label>
                    <textarea
                        name="description"
                        placeholder="Tulis deskripsi singkat sepatu..."
                    ><?= aman($produkEdit["description"] ?? "") ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-blue">
                    <?= $produkEdit ? "Simpan Perubahan" : "＋ Tambah Produk" ?>
                </button>

                <?php if ($produkEdit): ?>
                    <a href="admin.php#daftar-produk" class="btn btn-gray">
                        Batal Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- DAFTAR PRODUK -->
    <section class="section" id="daftar-produk">
        <div class="section-heading">
            <div>
                <h2>Kelola Produk</h2>
                <p>Edit foto, harga, stok, atau hapus produk yang tidak dijual.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($produk && $produk->num_rows > 0): ?>
                    <?php while ($p = $produk->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img
                                    src="<?= aman($p["image"]) ?>"
                                    class="product-image"
                                    alt="<?= aman($p["name"]) ?>"
                                    loading="lazy"
                                >
                            </td>

                            <td>
                                <div class="product-name">
                                    <?= aman($p["name"]) ?>
                                </div>
                                <div class="muted">
                                    <?= aman($p["brand"]) ?>
                                </div>
                                <div class="muted">
                                    ID: <?= (int) $p["id"] ?>
                                </div>
                            </td>

                            <td>
                                Rp<?= number_format((float) $p["price"], 0, ",", ".") ?>
                            </td>

                            <td><?= (int) $p["stock"] ?></td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="admin.php?edit=<?= (int) $p["id"] ?>#form-produk"
                                        class="btn btn-blue"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="aksi"
                                            value="hapus_produk"
                                        >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $p["id"] ?>"
                                        >

                                        <button type="submit" class="btn btn-red">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty">
                            Belum ada produk. Tambahkan produk terlebih dahulu.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- KELOLA PESANAN -->
    <section class="section" id="pesanan">
        <div class="section-heading">
            <div>
                <h2>Kelola Pesanan</h2>
                <p>Lihat data pelanggan, barang yang dibeli, dan ubah status pesanan.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Barang</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($pesanan && $pesanan->num_rows > 0): ?>
                    <?php while ($o = $pesanan->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong>#<?= (int) $o["id"] ?></strong>
                                <div class="muted">
                                    <?= aman($o["created_at"] ?? "") ?>
                                </div>
                            </td>

                            <td>
                                <strong>
                                    <?= aman($o["nama_pelanggan"] ?? "Pelanggan") ?>
                                </strong>
                                <div class="muted">
                                    <?= aman($o["recipient"] ?? "") ?>
                                </div>
                                <div class="muted">
                                    <?= aman($o["phone"] ?? "") ?>
                                </div>
                                <div class="muted">
                                    <?= aman($o["address"] ?? "") ?>
                                </div>
                            </td>

                            <td>
                                <details class="order-items">
                                    <summary>Lihat barang</summary>

                                    <?php
                                    $idPesananDetail = (int) $o["id"];

                                    $stmtItem = $conn->prepare(
                                        "SELECT product_name, size, price, quantity
                                         FROM order_items
                                         WHERE order_id = ?"
                                    );

                                    $stmtItem->bind_param("i", $idPesananDetail);
                                    $stmtItem->execute();
                                    $hasilItem = $stmtItem->get_result();
                                    ?>

                                    <?php if ($hasilItem->num_rows > 0): ?>
                                        <?php while ($item = $hasilItem->fetch_assoc()): ?>
                                            <div>
                                                <strong><?= aman($item["product_name"]) ?></strong>
                                                <br>
                                                Ukuran: <?= aman($item["size"]) ?>
                                                <br>
                                                Jumlah: <?= (int) $item["quantity"] ?>
                                                <br>
                                                Harga: Rp<?= number_format((float) $item["price"], 0, ",", ".") ?>
                                            </div>
                                            <hr style="margin:8px 0;border:0;border-top:1px solid #e5e7eb">
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p class="muted">Detail barang tidak tersedia.</p>
                                    <?php endif; ?>

                                    <?php $stmtItem->close(); ?>
                                </details>
                            </td>

                            <td>
                                <strong>
                                    Rp<?= number_format((float) $o["total"], 0, ",", ".") ?>
                                </strong>
                            </td>

                            <td><?= aman($o["status"]) ?></td>

                            <td>
                                <form method="POST" class="status-form">
                                    <input
                                        type="hidden"
                                        name="aksi"
                                        value="ubah_status"
                                    >

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= (int) $o["id"] ?>"
                                    >

                                    <select name="status" required>
                                        <?php foreach ($statusPesanan as $s): ?>
                                            <option
                                                value="<?= aman($s) ?>"
                                                <?= ($o["status"] === $s) ? "selected" : "" ?>
                                            >
                                                <?= aman($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="btn btn-blue">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada pesanan dari pelanggan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="footer">
        © <?= date("Y") ?> SOLE STUDIO. Admin Panel.
    </footer>

</main>

</body>
</html>
