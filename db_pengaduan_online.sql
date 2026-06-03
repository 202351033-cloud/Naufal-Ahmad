-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Des 2025 pada 06.11
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
-- Database: `db_pengaduan_online`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` enum('laporan','keluhan','aspirasi','') NOT NULL,
  `deskripsi` text NOT NULL,
  `dokumen_pendukung` varchar(255) DEFAULT NULL,
  `dokumen_path` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu','Diproses','Selesai','Ditolak') DEFAULT 'Menunggu',
  `tgl_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_proses` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id`, `user_id`, `judul`, `kategori`, `deskripsi`, `dokumen_pendukung`, `dokumen_path`, `status`, `tgl_kirim`, `tgl_proses`) VALUES
(9, 2, 'tambal jalan', 'laporan', '                    aaaaaaaaaaaa      ', 'dok_693ee9428596e.jpg', NULL, 'Selesai', '2025-12-14 10:43:46', '2025-12-15 04:44:24'),
(10, 2, 'tambal jalan', 'aspirasi', 'tolongggg', 'dok_693f8ba29deab.jpg', NULL, 'Menunggu', '2025-12-14 22:16:34', NULL),
(11, 5, 'lampu jalan', 'keluhan', 'aaaaaaaaaaaaaa', 'dok_693f95ebef964.jpg', NULL, 'Menunggu', '2025-12-14 23:00:27', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `nik` char(16) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `tgl_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `jenis_kelamin`, `nik`, `email`, `password`, `role`, `tgl_daftar`) VALUES
(2, 'arya farras', 'Laki-laki', '1234567891011121', 'aryafarras@gmail.com', '$2y$10$v2Zau2UeCfUY85awOGQzJeDozeSswJYmVqeo5TjYasCo/jwPMq3QO', 'user', '2025-12-05 06:41:12'),
(3, 'Administrator Utama', 'Laki-laki', '1111222233334444', 'admin@example.com', '$2y$10$UJrLAhYt9NWpjW/l.vgxP.TX5rmX1eeaHbbmw8Ldr.NyEDtqYvcd2', 'admin', '2025-12-05 06:52:01'),
(5, 'arkananta', 'Laki-laki', '1234567891012323', 'aryafarrasrkananta1@gmail.com', '$2y$10$79HCi/SkZBMj01416FJVJ.iOvN/iBdpVh61PP/8ioesZKlkhZRfES', 'user', '2025-12-15 04:59:14');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
