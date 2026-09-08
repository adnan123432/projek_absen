-- =========================================
-- DATABASE: absensi_qr
-- =========================================
CREATE DATABASE IF NOT EXISTS absensi_qr;
USE absensi_qr;

-- Tabel data siswa
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_unik VARCHAR(50) UNIQUE NOT NULL,   -- ini yang akan di-encode ke dalam QR
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    nisn VARCHAR(20) NOT NULL,
    qr_path VARCHAR(255) DEFAULT NULL,     -- lokasi file gambar QR
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel jadwal per kelas (jam masuk & batas absen)
CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas VARCHAR(20) NOT NULL,
    hari ENUM('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
    jam_masuk TIME NOT NULL,     -- jam mulai pelajaran, lewat ini = terlambat
    jam_batas_absen TIME NOT NULL, -- lewat jam ini dianggap tidak absen sama sekali
    UNIQUE KEY unique_jadwal (kelas, hari)
);

-- Tabel absensi harian
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_scan TIME DEFAULT NULL,
    status ENUM('hadir','terlambat','tidak hadir') NOT NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    UNIQUE KEY unique_absen_harian (siswa_id, tanggal)
);

-- =========================================
-- CONTOH DATA — 5 KELAS
-- =========================================

-- Kelas X IPA 1
INSERT INTO siswa (id_unik, nama, kelas, nisn) VALUES
('SW-2026-0001', 'Ahmad Fauzi', 'X IPA 1', '0051234567'),
('SW-2026-0002', 'Siti Nurhaliza', 'X IPA 1', '0051234568'),
('SW-2026-0003', 'Budi Santoso', 'X IPA 1', '0051234569'),
('SW-2026-0004', 'Dewi Lestari', 'X IPA 1', '0051234570');

-- Kelas X IPA 2
INSERT INTO siswa (id_unik, nama, kelas, nisn) VALUES
('SW-2026-0005', 'Rizky Ramadhan', 'X IPA 2', '0051234571'),
('SW-2026-0006', 'Putri Ayu', 'X IPA 2', '0051234572'),
('SW-2026-0007', 'Fajar Nugraha', 'X IPA 2', '0051234573'),
('SW-2026-0008', 'Ananda Salsabila', 'X IPA 2', '0051234574');

-- Kelas X IPS 1
INSERT INTO siswa (id_unik, nama, kelas, nisn) VALUES
('SW-2026-0009', 'Muhammad Rafi', 'X IPS 1', '0051234575'),
('SW-2026-0010', 'Nabila Zahra', 'X IPS 1', '0051234576'),
('SW-2026-0011', 'Yusuf Firmansyah', 'X IPS 1', '0051234577');

-- Kelas XI IPA 1
INSERT INTO siswa (id_unik, nama, kelas, nisn) VALUES
('SW-2026-0012', 'Dian Permata', 'XI IPA 1', '0051234578'),
('SW-2026-0013', 'Bayu Aji', 'XI IPA 1', '0051234579'),
('SW-2026-0014', 'Intan Permatasari', 'XI IPA 1', '0051234580');

-- Kelas XI IPS 1
INSERT INTO siswa (id_unik, nama, kelas, nisn) VALUES
('SW-2026-0015', 'Reza Aditya', 'XI IPS 1', '0051234581'),
('SW-2026-0016', 'Salsabila Putri', 'XI IPS 1', '0051234582'),
('SW-2026-0017', 'Gilang Ramadhan', 'XI IPS 1', '0051234583');

-- =========================================
-- JADWAL — WAJIB ADA UNTUK SETIAP KELAS x SETIAP HARI EFEKTIF
-- (Kalau kelas/hari tidak ada di sini, scan.php akan menolak
--  dengan pesan "Tidak ada jadwal pelajaran untuk kelas ... hari ini")
-- =========================================
INSERT INTO jadwal (kelas, hari, jam_masuk, jam_batas_absen) VALUES
-- X IPA 1
('X IPA 1', 'senin', '07:00:00', '09:00:00'),
('X IPA 1', 'selasa', '07:00:00', '09:00:00'),
('X IPA 1', 'rabu', '07:00:00', '09:00:00'),
('X IPA 1', 'kamis', '07:00:00', '09:00:00'),
('X IPA 1', 'jumat', '07:00:00', '09:00:00'),

-- X IPA 2
('X IPA 2', 'senin', '07:00:00', '09:00:00'),
('X IPA 2', 'selasa', '07:00:00', '09:00:00'),
('X IPA 2', 'rabu', '07:00:00', '09:00:00'),
('X IPA 2', 'kamis', '07:00:00', '09:00:00'),
('X IPA 2', 'jumat', '07:00:00', '09:00:00'),

-- X IPS 1
('X IPS 1', 'senin', '07:00:00', '09:00:00'),
('X IPS 1', 'selasa', '07:00:00', '09:00:00'),
('X IPS 1', 'rabu', '07:00:00', '09:00:00'),
('X IPS 1', 'kamis', '07:00:00', '09:00:00'),
('X IPS 1', 'jumat', '07:00:00', '09:00:00'),

-- XI IPA 1
('XI IPA 1', 'senin', '07:00:00', '09:00:00'),
('XI IPA 1', 'selasa', '07:00:00', '09:00:00'),
('XI IPA 1', 'rabu', '07:00:00', '09:00:00'),
('XI IPA 1', 'kamis', '07:00:00', '09:00:00'),
('XI IPA 1', 'jumat', '07:00:00', '09:00:00'),

-- XI IPS 1
('XI IPS 1', 'senin', '07:00:00', '09:00:00'),
('XI IPS 1', 'selasa', '07:00:00', '09:00:00'),
('XI IPS 1', 'rabu', '07:00:00', '09:00:00'),
('XI IPS 1', 'kamis', '07:00:00', '09:00:00'),
('XI IPS 1', 'jumat', '07:00:00', '09:00:00');
