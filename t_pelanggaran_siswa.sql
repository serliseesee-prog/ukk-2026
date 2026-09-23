-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Sep 2026 pada 07.28
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
-- Database: `db_ukk_2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran_siswa`
--

CREATE TABLE `t_pelanggaran_siswa` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `pelanggaran_id` int(11) NOT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL,
  `poin` int(11) NOT NULL,
  `tindakan` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `t_pelanggaran_siswa`
--

INSERT INTO `t_pelanggaran_siswa` (`id`, `tahun_ajaran_id`, `siswa_id`, `nama_siswa`, `kelas_id`, `nama_kelas`, `pelanggaran_id`, `nama_pelanggaran`, `pelanggaran_kategori_id`, `guru_id`, `nama_guru`, `tanggal`, `keterangan`, `poin`, `tindakan`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Alya Putri', 1, 'X RPL 1', 1, 'Terlambat masuk sekolah', 1, 1, 'Ahmad Fauzi', '2026-01-02', 'Datang terlambat', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(2, 1, 2, 'Bagas Ramadhan', 2, 'X RPL 2', 2, 'Tidak memakai atribut lengkap', 2, 2, 'Budi Santoso', '2026-01-03', 'Atribut tidak lengkap', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(3, 1, 3, 'Citra Lestari', 3, 'X RPL 3', 3, 'Tidak masuk tanpa keterangan', 3, 3, 'Citra Dewi', '2026-01-04', 'Tidak hadir', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(4, 1, 4, 'Dimas Saputra', 4, 'X RPL 4', 4, 'Tidak rapi', 4, 4, 'Dedi Kurnia', '2026-01-05', 'Seragam kurang rapi', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(5, 1, 5, 'Eka Wulandari', 5, 'X RPL 5', 5, 'Membuang sampah sembarangan', 5, 5, 'Euis Rahayu', '2026-01-06', 'Membuang sampah', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(6, 1, 6, 'Fajar Maulana', 6, 'X RPL 6', 6, 'Berbicara tidak sopan', 6, 6, 'Fajar Hidayat', '2026-01-07', 'Berbicara kurang sopan', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(7, 1, 7, 'Gina Amelia', 7, 'X RPL 7', 7, 'Melanggar tata tertib', 7, 7, 'Gina Lestari', '2026-01-08', 'Melanggar aturan', 10, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(8, 1, 8, 'Hendra Kurnia', 8, 'X RPL 8', 8, 'Merusak fasilitas', 8, 8, 'Herman Wijaya', '2026-01-09', 'Merusak fasilitas', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(9, 1, 9, 'Intan Permata', 9, 'X RPL 9', 9, 'Merusak lingkungan', 9, 9, 'Indah Sari', '2026-01-10', 'Merusak tanaman', 15, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(10, 1, 10, 'Jaka Pratama', 10, 'X RPL 10', 10, 'Menyontek', 10, 10, 'Joko Susanto', '2026-01-11', 'Menyontek saat ujian', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(11, 1, 11, 'Kirana Sari', 11, 'X RPL 11', 11, 'Terlambat upacara', 1, 11, 'Kartika Dewi', '2026-01-12', 'Terlambat upacara', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(12, 1, 12, 'Lukman Hakim', 12, 'X RPL 12', 12, 'Sepatu tidak sesuai', 2, 12, 'Lukman Hakim', '2026-01-13', 'Sepatu tidak sesuai', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(13, 1, 13, 'Maya Anggraini', 13, 'X RPL 13', 13, 'Bolos sekolah', 3, 13, 'Maya Sari', '2026-01-14', 'Meninggalkan sekolah', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(14, 1, 14, 'Naufal Akbar', 14, 'X RPL 14', 14, 'Rambut tidak rapi', 4, 14, 'Nana Sujana', '2026-01-15', 'Rambut tidak sesuai', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(15, 1, 15, 'Olivia Rahma', 15, 'X RPL 15', 15, 'Tidak piket', 5, 15, 'Oki Setiawan', '2026-01-16', 'Tidak melaksanakan piket', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(16, 1, 16, 'Putra Wijaya', 16, 'X RPL 16', 16, 'Mengganggu teman', 6, 16, 'Putri Amelia', '2026-01-17', 'Mengganggu teman', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(17, 1, 17, 'Qonita Aulia', 17, 'X RPL 17', 17, 'Keluar kelas tanpa izin', 7, 17, 'Rudi Hartono', '2026-01-18', 'Keluar kelas', 5, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(18, 1, 18, 'Rizky Firmansyah', 18, 'XI RPL 1', 18, 'Menggunakan fasilitas sembarangan', 8, 18, 'Sari Wulandari', '2026-01-19', 'Menggunakan fasilitas', 10, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(19, 1, 19, 'Salsa Nabila', 19, 'XI RPL 2', 19, 'Mencoret dinding', 9, 19, 'Tono Pratama', '2026-01-20', 'Mencoret dinding', 15, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(20, 1, 20, 'Taufik Hidayat', 20, 'XI RPL 3', 20, 'Menyalin tugas teman', 10, 20, 'Umi Kalsum', '2026-01-21', 'Menyalin tugas', 10, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(21, 1, 21, 'Ulfa Ramadhani', 21, 'XI RPL 4', 21, 'Terlambat masuk kelas', 1, 21, 'Vina Anggraini', '2026-01-22', 'Terlambat masuk kelas', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(22, 1, 22, 'Vino Alfarizi', 22, 'XI RPL 5', 22, 'Seragam tidak lengkap', 2, 22, 'Wawan Setiawan', '2026-01-23', 'Seragam tidak lengkap', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(23, 1, 23, 'Wulan Sari', 23, 'XI RPL 6', 23, 'Tidak mengikuti pelajaran', 3, 23, 'Yuli Astuti', '2026-01-24', 'Tidak mengikuti pelajaran', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(24, 1, 24, 'Yoga Prasetyo', 24, 'XI RPL 7', 24, 'Penampilan tidak rapi', 4, 24, 'Zainal Abidin', '2026-01-25', 'Penampilan kurang rapi', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(25, 1, 25, 'Zahra Amelia', 25, 'XI RPL 8', 25, 'Membuang sampah di kelas', 5, 25, 'Asep Heryawan', '2026-01-26', 'Membuang sampah', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(26, 1, 26, 'Andi Setiawan', 26, 'XI RPL 9', 26, 'Berbicara kasar', 6, 26, 'Bella Kartika', '2026-01-27', 'Berbicara kasar', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(27, 1, 27, 'Bella Safitri', 27, 'XI RPL 10', 27, 'Membawa barang terlarang', 7, 27, 'Cecep Suherman', '2026-01-28', 'Membawa barang terlarang', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(28, 1, 28, 'Cahya Nugraha', 28, 'XI RPL 11', 28, 'Merusak meja', 8, 28, 'Dian Permata', '2026-01-29', 'Merusak meja', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(29, 1, 29, 'Della Puspita', 29, 'XI RPL 12', 29, 'Merusak tanaman', 9, 29, 'Eman Sulaeman', '2026-01-30', 'Merusak tanaman', 10, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(30, 1, 30, 'Erwin Kurniawan', 30, 'XI RPL 13', 30, 'Bekerja sama saat ujian', 10, 30, 'Fitri Handayani', '2026-01-31', 'Bekerja sama saat ujian', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(31, 1, 31, 'Fina Maharani', 31, 'XI RPL 14', 31, 'Terlambat apel', 1, 31, 'Gilang Ramadhan', '2026-02-01', 'Terlambat apel', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(32, 1, 32, 'Galih Ramadhan', 32, 'XI RPL 15', 32, 'Tidak memakai dasi', 2, 32, 'Hana Safitri', '2026-02-02', 'Tidak memakai dasi', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(33, 1, 33, 'Hani Nuraini', 33, 'XI RPL 16', 33, 'Tidak hadir kegiatan', 3, 33, 'Imam Fauzi', '2026-02-03', 'Tidak hadir kegiatan', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(34, 1, 34, 'Ilham Fauzi', 34, 'XI RPL 17', 34, 'Atribut tidak rapi', 4, 34, 'Jihan Lestari', '2026-02-04', 'Atribut tidak rapi', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(35, 1, 35, 'Jihan Amalia', 35, 'XII RPL 1', 35, 'Tidak menjaga kebersihan', 5, 35, 'Kamaludin', '2026-02-05', 'Tidak menjaga kebersihan', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(36, 1, 36, 'Kevin Saputra', 36, 'XII RPL 2', 36, 'Tidak menghormati guru', 6, 36, 'Lina Marlina', '2026-02-06', 'Tidak menghormati guru', 15, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(37, 1, 37, 'Larasati Dewi', 37, 'XII RPL 3', 37, 'Melanggar aturan kelas', 7, 37, 'Maman Suherman', '2026-02-07', 'Melanggar aturan kelas', 10, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(38, 1, 38, 'Miftahul Arif', 38, 'XII RPL 4', 38, 'Merusak kursi', 8, 38, 'Nia Kurniati', '2026-02-08', 'Merusak kursi', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(39, 1, 39, 'Nadia Fitri', 39, 'XII RPL 5', 39, 'Merusak taman', 9, 39, 'Oman Rahman', '2026-02-09', 'Merusak taman', 10, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(40, 1, 40, 'Oscar Firmansyah', 40, 'XII RPL 6', 40, 'Plagiarisme tugas', 10, 40, 'Pipit Anggraini', '2026-02-10', 'Menyalin karya', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(41, 1, 41, 'Putri Maharani', 41, 'XII RPL 7', 41, 'Terlambat sekolah', 1, 41, 'Qori Aulia', '2026-02-11', 'Datang terlambat', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(42, 1, 42, 'Raka Aditya', 42, 'XII RPL 8', 42, 'Tidak memakai ikat pinggang', 2, 42, 'Rina Marlina', '2026-02-12', 'Atribut tidak lengkap', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(43, 1, 43, 'Siti Aisyah', 43, 'XII RPL 9', 43, 'Meninggalkan sekolah', 3, 43, 'Sandi Nugraha', '2026-02-13', 'Keluar sekolah', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(44, 1, 44, 'Teguh Pranata', 44, 'XII RPL 10', 44, 'Tidak menjaga kerapihan', 4, 44, 'Tuti Alawiyah', '2026-02-14', 'Tidak rapi', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(45, 1, 45, 'Vania Putri', 45, 'XII RPL 11', 45, 'Tidak melaksanakan piket', 5, 45, 'Ujang Supriatna', '2026-02-15', 'Tidak piket', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(46, 1, 46, 'Wahyu Setiawan', 46, 'XII RPL 12', 46, 'Bersikap tidak sopan', 6, 46, 'Vera Oktavia', '2026-02-16', 'Bersikap kurang sopan', 10, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(47, 1, 47, 'Yuni Kartika', 47, 'XII RPL 13', 47, 'Melanggar peraturan', 7, 47, 'Wahyu Nugroho', '2026-02-17', 'Melanggar peraturan', 10, 'Peringatan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(48, 1, 48, 'Zaki Ramadhan', 48, 'XII RPL 14', 48, 'Merusak fasilitas kelas', 8, 48, 'Yayan Sopian', '2026-02-18', 'Merusak fasilitas', 20, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(49, 1, 49, 'Aurel Puspita', 49, 'XII RPL 15', 49, 'Mengotori lingkungan', 9, 49, 'Zulaikha', '2026-02-19', 'Mengotori lingkungan', 5, 'Teguran', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30'),
(50, 1, 50, 'Bima Pratama', 50, 'XII RPL 16', 50, 'Menyontek tugas', 10, 50, 'Agus Setiawan', '2026-02-20', 'Menyontek tugas', 15, 'Pembinaan', 'Selesai', '2026-09-23 02:34:30', '2026-09-23 02:34:30');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `tahun_ajaran_id_2` (`tahun_ajaran_id`),
  ADD KEY `siswa_id_2` (`siswa_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_4` FOREIGN KEY (`pelanggaran_id`) REFERENCES `t_pelanggaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_5` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_6` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
