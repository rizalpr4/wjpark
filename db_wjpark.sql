-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Jun 2026 pada 14.57
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_wjpark`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kapasitas`
--

CREATE TABLE `kapasitas` (
  `id` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT 80,
  `terisi` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kapasitas`
--

INSERT INTO `kapasitas` (`id`, `total`, `terisi`, `updated_at`) VALUES
(1, 90, 4, '2026-06-04 18:33:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tarif`
--

CREATE TABLE `tarif` (
  `id` int(11) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `tarif_awal` int(11) NOT NULL DEFAULT 3000,
  `jam_awal` int(11) NOT NULL DEFAULT 3,
  `tarif_perjam` int(11) NOT NULL DEFAULT 1000,
  `tarif_maks` int(11) NOT NULL DEFAULT 15000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tarif`
--

INSERT INTO `tarif` (`id`, `jenis`, `tarif_awal`, `jam_awal`, `tarif_perjam`, `tarif_maks`) VALUES
(1, 'Motor', 3000, 3, 1000, 15000),
(2, 'Motor Gede (Moge)', 5000, 3, 5000, 60000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `no_plat` varchar(20) NOT NULL,
  `id_tarif` int(11) NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `durasi_menit` int(11) DEFAULT NULL,
  `biaya` int(11) DEFAULT NULL,
  `id_operator` int(11) NOT NULL,
  `status` enum('parkir','selesai') DEFAULT 'parkir',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `no_plat`, `id_tarif`, `waktu_masuk`, `waktu_keluar`, `durasi_menit`, `biaya`, `id_operator`, `status`, `created_at`) VALUES
(3, 'B 1332 HJI', 1, '2026-05-30 22:35:46', '2026-05-31 10:28:06', 712, 12000, 1, 'selesai', '2026-05-30 22:35:46'),
(4, 'B 1178 RR', 2, '2026-05-30 22:37:46', '2026-06-04 18:23:45', 6946, 60000, 1, 'selesai', '2026-05-30 22:37:46'),
(5, 'F 1614 JIK', 2, '2026-05-30 22:39:03', NULL, NULL, NULL, 1, 'parkir', '2026-05-30 22:39:03'),
(6, 'B 4578 DD', 1, '2026-05-31 09:04:58', NULL, NULL, NULL, 1, 'parkir', '2026-05-31 09:04:58'),
(7, 'F 7788 CC', 1, '2026-05-31 09:12:49', '2026-05-31 10:02:15', 49, 3000, 1, 'selesai', '2026-05-31 09:12:49'),
(8, 'B 8861', 1, '2026-05-31 10:02:33', NULL, NULL, NULL, 1, 'parkir', '2026-05-31 10:02:33'),
(9, 'F 1133 FJH', 1, '2026-05-31 10:26:42', NULL, NULL, NULL, 1, 'parkir', '2026-05-31 10:26:42'),
(10, 'B 1234 ABC', 1, '2026-06-04 18:22:53', '2026-06-04 18:24:41', 2, 3000, 1, 'selesai', '2026-06-04 18:22:53'),
(11, 'B 1234 ABC', 1, '2026-06-04 18:32:30', '2026-06-04 18:33:37', 1, 3000, 1, 'selesai', '2026-06-04 18:32:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','operator') DEFAULT 'operator',
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `aktif`, `created_at`) VALUES
(1, 'admin', '$2y$10$ICuhxzWR7kvKf/3rSGHWduTcIz0uZxT7uvjIdbwnLrqQhqhWZx6hC', 'Teguh', 'admin', 1, '2026-05-30 22:13:08'),
(3, 'operator2', '$2y$10$uAS8GRUyjytQSGwuXmM3xOaR4woeb9Pn1HdGsJpQNDLiwa2yvWwTq', 'Dimar Alam Setyo Tuhhu', 'operator', 1, '2026-05-31 09:22:32'),
(6, 'budi', '$2y$10$qtoFqYKqvlEqbcJvnNBFcONoO023sf6HdT562HDgR9dV87PEKq56O', 'Budi', 'operator', 1, '2026-06-04 18:36:35');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kapasitas`
--
ALTER TABLE `kapasitas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tarif`
--
ALTER TABLE `tarif`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tarif` (`id_tarif`),
  ADD KEY `id_operator` (`id_operator`),
  ADD KEY `no_plat` (`no_plat`),
  ADD KEY `status` (`status`),
  ADD KEY `waktu_masuk` (`waktu_masuk`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kapasitas`
--
ALTER TABLE `kapasitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tarif`
--
ALTER TABLE `tarif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_tarif`) REFERENCES `tarif` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_operator`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
