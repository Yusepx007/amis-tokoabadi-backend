-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Waktu pembuatan: 01 Jun 2026 pada 05.13
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_tokoabadi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `nama`) VALUES
(2, 'Plafon'),
(1, 'PVC'),
(3, 'Wallpanel');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `kode_motif` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ukuran` varchar(50) NOT NULL,
  `satuan` varchar(50) NOT NULL DEFAULT 'lembar',
  `harga_beli` decimal(15,2) NOT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 10,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `kode_motif`, `nama`, `category_id`, `ukuran`, `satuan`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `foto`) VALUES
(1, 'KAY-01', 'PVC Motif Kayu-01', 1, '20×40 cm', 'lembar', 65000.00, 85000.00, 48, 10, NULL),
(2, 'MAR-03', 'Wallpanel Marmer-03', 3, '30×60 cm', 'lembar', 100000.00, 125000.00, 5, 10, NULL),
(3, 'BAT-02', 'PVC Motif Batu-02', 1, '20×40 cm', 'lembar', 55000.00, 78000.00, 0, 10, NULL),
(4, 'GYP-60', 'Plafon Gypsum 60×60', 2, '60×60 cm', 'lembar', 30000.00, 45000.00, 120, 20, NULL),
(5, 'KAY-02', 'PVC Motif Kayu-02', 1, '20×40 cm', 'lembar', 68000.00, 90000.00, 35, 10, NULL),
(6, 'PLF-W01', 'Plafon PVC Putih', 2, '20×40 cm', 'meter_lari', 45000.00, 65000.00, 8, 15, NULL),
(7, 'WP-MRB-01', 'Wallpanel Marmer Hitam', 3, '60×120 cm', 'lembar', 120000.00, 165000.00, 22, 10, NULL),
(8, 'KAY-03', 'PVC Motif Kayu-03', 1, '20×40 cm', 'lembar', 70000.00, 95000.00, 15, 10, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipe` enum('penjualan','pembelian') NOT NULL DEFAULT 'penjualan',
  `status` enum('lunas','pending','batal') NOT NULL DEFAULT 'pending',
  `total_harga` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `kode_transaksi`, `user_id`, `tipe`, `status`, `total_harga`, `tanggal`) VALUES
(1, 'TRX-240', 3, 'penjualan', 'lunas', 1190000.00, '2026-05-31 17:49:58'),
(2, 'TRX-239', 2, 'penjualan', 'lunas', 510000.00, '2026-05-31 14:49:58'),
(3, 'TRX-238', 3, 'penjualan', 'lunas', 990000.00, '2026-05-31 11:49:58'),
(4, 'TRX-237', 2, 'penjualan', 'pending', 340000.00, '2026-05-31 09:49:58'),
(5, 'TRX-236', 3, 'penjualan', 'lunas', 2750000.00, '2026-05-31 07:49:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 8, 85000.00, 680000.00),
(2, 1, 4, 15, 45000.00, 510000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('pemilik','admin','sales') NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `role`, `password`, `token`, `created_at`) VALUES
(1, 'Bapak Hendra', 'pemilik@abadiplaon.id', 'pemilik', '$2y$12$puRtVWgGu0V4poD2wg/uGO16fv3eon5D/gaoI7BS7JRaJYOIeDie2', 'mock_token_pemilik', '2026-06-01 02:56:03'),
(2, 'Siti Rahayu', 'admin@abadiplaon.id', 'admin', '$2y$12$puRtVWgGu0V4poD2wg/uGO16fv3eon5D/gaoI7BS7JRaJYOIeDie2', 'mock_token_admin', '2026-06-01 02:56:03'),
(3, 'Andi Wijaya', 'sales@abadiplaon.id', 'sales', '$2y$12$puRtVWgGu0V4poD2wg/uGO16fv3eon5D/gaoI7BS7JRaJYOIeDie2', 'mock_token_sales', '2026-06-01 02:56:03');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_motif` (`kode_motif`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
