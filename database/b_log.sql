-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Jun 2025 pada 13.50
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
-- Database: `b_log`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_keluar`
--

INSERT INTO `barang_keluar` (`id`, `buku_id`, `jumlah`, `keterangan`, `tanggal`, `tujuan`, `user_id`, `created_at`) VALUES
(1, 5, 1, '0', '2025-06-11', 'rusak', 0, '2025-06-11 09:40:56'),
(2, 5, 2, '0', '2025-06-11', 'rusak', 3, '2025-06-11 10:21:31'),
(3, 1, 1, '0', '2025-06-11', 'rusak', 3, '2025-06-11 10:30:55'),
(4, 4, 1, '0', '2025-06-11', 'rusak', 3, '2025-06-11 10:36:44'),
(5, 4, 1, '0', '2025-06-11', 'rusak', 3, '2025-06-11 10:38:24'),
(6, 1, 2, '0', '2025-06-20', 'rusak', 3, '2025-06-20 21:55:58'),
(7, 4, 12, '0', '2025-06-22', '', 0, '2025-06-22 18:21:49'),
(8, 4, 1, '0', '2025-06-22', '', 0, '2025-06-22 18:22:05'),
(9, 6, 10, '0', '2025-06-22', 'jual', 0, '2025-06-22 18:27:09'),
(10, 4, 10, '0', '2025-06-22', '', 0, '2025-06-22 18:42:39'),
(11, 4, 1, 'rusak', '2025-06-22', '', 0, '2025-06-22 18:44:04'),
(12, 3, 1, 'rusak juga', '2025-06-22', '', 0, '2025-06-22 18:45:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `pemasok` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_masuk`
--

INSERT INTO `barang_masuk` (`id`, `buku_id`, `jumlah`, `keterangan`, `tanggal`, `pemasok`, `user_id`, `created_at`) VALUES
(15, 4, 10, 'barang baru', '2025-06-22', '', 0, '2025-06-22 18:38:12'),
(16, 6, 1, 'stok baru', '2025-06-22', '', 0, '2025-06-22 18:38:21'),
(17, 3, 10, 'barang baru', '2025-06-22', '', 0, '2025-06-22 18:38:37'),
(18, 6, 1, 'baru', '2025-06-22', '', 0, '2025-06-22 18:42:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_buku`
--

CREATE TABLE `data_buku` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori_id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `stok_awal` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_buku`
--

INSERT INTO `data_buku` (`id`, `judul`, `kategori_id`, `supplier_id`, `stok_awal`, `created_at`) VALUES
(1, 'laskar betawi', 1, 1, 9, '2025-06-10 14:55:08'),
(3, 'dedeinoen', 4, 1, 23, '2025-06-10 14:47:14'),
(4, 'artur', 5, 2, 43, '2025-06-10 14:54:56'),
(5, 'matematika', 5, 1, 7, '2025-06-11 09:16:17'),
(6, 'berita', 7, 4, 44, '2025-06-20 21:53:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`) VALUES
(1, 'novel', ''),
(4, 'fiksi', ''),
(5, 'manga', ''),
(7, 'majalah', 'majalah harian');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aktivitas` text NOT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aktivitas`, `waktu`) VALUES
(16, 2, 'Menambahkan barang masuk: 10 unit untuk buku ID 4', '2025-06-22 18:36:37'),
(17, 2, 'Menambahkan barang masuk: 10 unit untuk buku ID 3', '2025-06-22 18:36:58'),
(18, 2, 'Barang masuk: 10 unit buku ID 4', '2025-06-22 18:38:12'),
(19, 2, 'Barang masuk: 1 unit buku ID 6', '2025-06-22 18:38:21'),
(20, 2, 'Barang masuk: 10 unit buku ID 3', '2025-06-22 18:38:37'),
(21, 2, 'Barang masuk: 1 unit buku ID 6', '2025-06-22 18:42:19'),
(22, 2, 'Barang keluar: 10 unit buku ID 4', '2025-06-22 18:42:39'),
(23, 2, 'Barang keluar: 1 unit buku ID 4', '2025-06-22 18:44:04'),
(24, 2, 'Barang keluar: 1 unit buku ID 3', '2025-06-22 18:45:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `code`) VALUES
(1, 'ADMIN', 'ADM'),
(2, 'STAFF', 'STF'),
(3, 'USER', 'USR');

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kontak_person` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id`, `nama`, `kontak_person`, `telepon`, `alamat`, `email`) VALUES
(1, 'gramedia', 'william', '08921213134', 'jl raden', 'gramedia@gmail.com'),
(2, 'jpfoundation', 'lian', '081212131300', 'jl yadika', ''),
(4, 'pt superindo', 'tirta', '0891213141', 'jl kusuma jaya', ''),
(5, 'pt cahaya', '', '089676777', 'jl laksana', 'wildan@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('Aktif','Non-aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `user_id`, `fullname`, `email`, `image`, `password`, `role_id`, `status`, `created_at`, `foto`) VALUES
(2, 'ADM-002', 'william', 'fc@gmail.com', 'https://i.pinimg.com/736x/20/f0/2c/20f02cad2014df96258caeed3be893cb.jpg', 'william123', 1, 'Aktif', '2025-06-10 16:06:33', NULL),
(3, 'STF-001', 'julian', 'amandacorp27@gmail.com', 'https://i.pinimg.com/736x/eb/8b/50/eb8b5045993d21d1f98dd8052591c194.jpg', 'julian123', 2, 'Aktif', '2025-06-10 17:12:35', 'foto_3.png');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `data_buku`
--
ALTER TABLE `data_buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `data_buku`
--
ALTER TABLE `data_buku`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `data_buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `barang_masuk_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `data_buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `data_buku`
--
ALTER TABLE `data_buku`
  ADD CONSTRAINT `data_buku_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `data_buku_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`);

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
