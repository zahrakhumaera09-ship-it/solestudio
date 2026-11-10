-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 10 Nov 2026 pada 11.45
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sole_studio`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `address` text NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `status` enum('Pending','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `recipient`, `phone`, `address`, `total`, `status`, `created_at`) VALUES
(1, 2, 'laskar', '085703681367', 'korea selatan', 649000.00, 'Pending', '2026-10-17 12:52:18'),
(2, 2, 'momo', '08572345718', 'Jakarta Selatan', 1250000.00, 'Pending', '2026-10-18 11:29:46'),
(3, 2, 'laskar', '085703681367', 'jkil', 1050000.00, 'Pending', '2026-10-21 09:35:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `size` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size`, `price`, `quantity`) VALUES
(1, 1, 2, 'Urban Runner', 40, 649000.00, 1),
(2, 2, 24, 'Classic Leather White', 39, 1250000.00, 1),
(3, 3, 23, 'Sepatu Sneaker Speedcat Croc Wanita', 39, 1050000.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `description`, `price`, `image`, `stock`, `created_at`) VALUES
(1, 'Unisex Classic Clog', 'Crocs', 'Sendal classic minimalis untuk gaya sehari-hari.', 499000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-CROCS-FFSSDCCRA-CCR10001-001-Black.jpg?width=300&height=300&quality=80&fit=cover', 15, '2026-10-17 12:04:32'),
(2, 'handball spezial women\'s sneakers - black', 'Adidas', 'Sneakers dengan desain kulit hewan dan unik.', 649000.00, 'https://www.footlocker.id/media/catalog/product/0/8/0886-ADIKI2183005004-1_1790345167.jpg?width=1200&height=1200&store=idn&image-type=image', 11, '2026-10-17 12:04:32'),
(3, 'samba jane women\'s sneakers - crystal sky/off white/gum 3', 'Adidas', 'Sepatu biru simpel yang mudah dipadukan.', 459000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-ADIDAS-FFSSBADI5-ADILA5345-Blue.jpg?width=1200&height=1200&store=idn&image-type=image', 20, '2026-10-17 12:04:32'),
(4, 'crosspath men\'s sneakers - bungee cord/coal brown', 'Nike', 'Sneakers bergaya streetwear untuk aktivitas harian.', 579000.00, 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=800', 10, '2026-10-17 12:04:32'),
(5, 'xt-6 men\'s sneakers - pearl blue/grisa', 'Salamon', NULL, 1850000.00, 'https://www.footlocker.id/media/catalog/product/0/8/0886-SALL4547280012W008-3_1791202911.jpg?width=1200&height=1200&store=idn&image-type=image', 15, '2026-10-17 16:00:00'),
(6, 'jordan max aura men\'s basketball shoes - black/black-gym red-white', 'Air Jordan', NULL, 1750000.00, 'https://www.footlocker.id/media/catalog/product/0/8/0886-NIKAQ9084006005008-1_1790259382.jpg?width=1200&height=1200&store=idn&image-type=image', 12, '2026-10-17 16:00:00'),
(7, 'killshot 2 men\'s sneakers - vintage gree', 'Nike', NULL, 1650000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-NIKE-FFSSBNIK5-NIKFQ8903300-Green.jpg?width=1200&height=1200&store=idn&image-type=image', 10, '2026-10-17 16:00:00'),
(8, 'cloudtilt women\'s sneakers - biscuit ivory', 'ON', NULL, 2200000.00, 'https://www.footlocker.id/media/catalog/product/0/3/03-ON-FFSSBONR5-ONRW10055221-Brown.jpg?width=1200&height=1200&store=idn&image-type=image', 8, '2026-10-17 16:00:00'),
(9, 'hali 1 unisex basketball shoes - cobalt glaze/zen blue', 'Puma', NULL, 22000000.00, 'https://www.footlocker.id/media/catalog/product/0/3/03-PUMA-F34KBPMAA-PMA314098-01-Cobalt.jpg?width=1200&height=1200&store=idn&image-type=image', 14, '2026-10-17 16:00:00'),
(10, 'speedcat ballet women\'s sneakers - black/ white', 'Puma', NULL, 1550000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-PUMA-FFSSEPMAA-PMA401287-06-Black.jpg?width=1200&height=1200&store=idn&image-type=image', 9, '2026-10-17 16:00:00'),
(11, 'samba og men\'s sneaker shoes - ftwr white', 'Adidas', NULL, 1350000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-ADIDAS-FFSSBADI5-ADIB75806-White.jpg?width=1200&height=1200&store=idn&image-type=image', 11, '2026-10-17 16:00:00'),
(12, 'run star trainer unisex sneakers - black/egret/honey', 'Converse', NULL, 1600000.00, 'https://www.footlocker.id/media/catalog/product/0/1/01-CONVERSE-FFSSBCONA-CONA08263C-Black.jpg?width=300&height=300&quality=80&fit=cover', 12, '2026-10-17 16:00:00'),
(13, 'gel-stratus mc unisex lifestyle shoes - grey', 'asics', NULL, 1750000.00, 'https://www.footlocker.id/media/catalog/product/0/3/03-ASICS-FFSSEASIA-ASI23B049020-Grey.jpg?width=1200&height=1200&store=idn&image-type=image', 7, '2026-10-17 16:00:00'),
(14, '530 Sport Silver', 'New Balance', NULL, 1900000.00, 'https://www.footlocker.id/media/catalog/product/0/8/0886-NEWU74041J00W09H-3.jpg?width=1200&height=1200&store=idn&image-type=image', 10, '2026-10-17 16:00:00'),
(15, 'Nike Air Monarch IV', 'Nike', NULL, 2500000.00, 'https://static.nike.com/a/images/t_web_pw_592_v2/f_auto/u_9ddf04c7-2a9a-4d76-add1-d15af8f0263d,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/c0c44197-0824-4096-9320-fc18c9ba2028/AIR+MONARCH+IV.png', 6, '2026-10-17 16:00:00'),
(16, 'MEXICO 66 SLIP-ON', 'Onitsuka Tiger', NULL, 1450000.00, 'https://images.asics.com/is/image/asics/1183C157_202_SR_RT_GLB?qlt=100&wid=1280&hei=1452&bgc=255,255,255&resMode=bisharp', 13, '2026-10-17 16:00:00'),
(17, 'xt-6 unisex lifestyle shoes - black phantom\r\n', 'salomon', NULL, 1200000.00, 'https://www.footlocker.id/media/catalog/product/0/2/02-SALOMON-F34ADSALA-SALL41086600-Black.jpg?width=1200&height=1200&store=idn&image-type=image', 10, '2026-10-17 16:00:00'),
(18, 'Sepatu Sneaker Speedcat Nova Ballet Wanita', 'Puma', NULL, 1299000.00, 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_500,h_500/global/408340/01/sv01/fnd/IDN/fmt/png/Sepatu-Sneaker-Speedcat-Nova-Ballet-Wanita', 8, '2026-10-17 16:00:00'),
(19, 'GSM', 'Onitsuka Tiger', NULL, 950000.00, 'https://images.asics.com/is/image/asics/1183C436_200_SR_RT_GLB?qlt=100&wid=1280&hei=1452&bgc=255,255,255&resMode=bisharp', 15, '2026-10-17 16:00:00'),
(20, 'Sepatu Sneaker VTG Espresso x ROSÉ Speedcat Unisex', 'Puma', NULL, 1100000.00, 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_500,h_500/global/410661/01/sv01/fnd/IDN/fmt/png/Sepatu-Sneaker-VTG-Espresso-x-ROS%C3%89-Speedcat-Unisex', 10, '2026-10-17 16:00:00'),
(21, 'Old Skool Red', 'Vans', NULL, 1000000.00, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=800&sat=-40', 12, '2026-10-17 16:00:00'),
(22, 'Nike Air Force 1 \'01', 'nike', NULL, 1150000.00, 'https://static.nike.com/a/images/q_auto:eco/t_product_v1/f_auto/dpr_1.0/h_494,c_limit/a7421270-84db-4143-a5bc-5273a54adea6/AIR+FORCE+1+%2701.png', 7, '2026-10-17 16:00:00'),
(23, 'Sepatu Sneaker Speedcat Croc Wanita', 'Puma', NULL, 1050000.00, 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_500,h_500/global/407674/01/sv01/fnd/IDN/fmt/png/Sepatu-Sneaker-Speedcat-Croc-Wanita', 8, '2026-10-17 16:00:00'),
(24, 'Classic Leather White', 'Adidas Star', NULL, 1250000.00, 'https://images.unsplash.com/photo-1593287073863-c992914cb3e3?q=80&w=387&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 10, '2026-10-17 16:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'zahra', 'adminsolestudio@gmail.com', '$2y$10$VwEZAyb//VvNsRCIxsrLJu0e0MvRsN7nsXrixhbhUs8XJQCDwTP2y', 'admin', '2026-10-17 12:19:44'),
(2, 'laskar', 'laskarsky@gmail.com', '$2y$10$ni8Y2GNgRDqaXtPhAuC8ZOvmJFm/nYjXPUkkhkU11j23s3G5BDu1q', 'customer', '2026-10-17 12:26:26');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`size`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
