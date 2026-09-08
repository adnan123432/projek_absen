# Sistem Absensi QR Siswa

## Catatan Perbaikan Terbaru
- **Perbaikan bug "kelas ini tidak ada jadwal"**: sebelumnya tabel `jadwal` hanya
  diisi untuk 1 kelas (X IPA 1). Sekarang `database.sql` sudah berisi jadwal untuk
  **5 kelas** (X IPA 1, X IPA 2, X IPS 1, XI IPA 1, XI IPS 1), hari Senin–Jumat.
  Kalau kamu menambah kelas baru, **wajib** tambahkan juga jadwalnya di tabel
  `jadwal`, kalau tidak scan akan selalu gagal untuk kelas tersebut.
- `api/scan.php` sekarang mencocokkan nama kelas pakai `TRIM` + `LOWER` supaya
  tidak gagal gara-gara beda spasi/huruf besar-kecil, dan pesan error sekarang
  lebih jelas (membedakan "kelas belum ada jadwal sama sekali" vs "kelas ada,
  tapi hari ini kosong").
- `rekap.php` dirombak: ada ringkasan jumlah kelas & jumlah siswa, kartu
  ringkasan per kelas, dan tampilan tabel yang lebih rapi.

## Cara Setup

1. **Buat database**
   - Import `database.sql` ke MySQL (lewat phpMyAdmin atau `mysql -u root -p < database.sql`)

2. **Atur koneksi database**
   - Edit `db.php`, sesuaikan `$host`, `$user`, `$pass`, `$dbname` dengan setting MySQL kamu

3. **Tambah data siswa**
   - Insert data siswa ke tabel `siswa` (nama, kelas, nisn, id_unik)
   - `id_unik` boleh diisi manual (misal `SW-2026-0001`) atau otomatis pakai NISN

4. **Generate QR code**
   - Jalankan `generate_qr.php` lewat browser: `http://localhost/absensi_qr/generate_qr.php`
   - QR untuk tiap siswa akan tersimpan di folder `/qrcodes`
   - Cetak/print QR tersebut, bagikan ke masing-masing siswa (misal ditempel di kartu pelajar)

5. **Atur jadwal kelas**
   - Isi tabel `jadwal` dengan jam masuk & jam batas absen tiap kelas per hari

6. **Buka halaman scan**
   - Akses `scan.html` lewat HP/laptop yang ada kamera: `http://localhost/absensi_qr/scan.html`
   - Izinkan akses kamera saat diminta browser
   - Arahkan ke QR siswa → otomatis muncul nama, kelas, NISN, dan status hadir/terlambat

7. **Setup cron job untuk "tidak hadir" otomatis**
   - Jadwalkan `cron_absen.php` jalan otomatis tiap hari setelah jam batas absen lewat
   - Siswa yang tidak scan sama sekali otomatis tercatat "tidak hadir"

## Struktur Folder
```
absensi_qr/
├── database.sql        <- skema database
├── db.php               <- koneksi database
├── generate_qr.php       <- generate QR per siswa
├── scan.html             <- halaman scan pakai kamera HP
├── cron_absen.php        <- auto-tandai tidak hadir
├── qrcodes/              <- hasil QR tersimpan di sini
└── api/
    └── scan.php          <- proses hasil scan & simpan absensi
```

## Catatan Keamanan
- Sebaiknya taruh folder ini di **jaringan lokal sekolah / hosting dengan HTTPS**, jangan expose bebas ke publik tanpa autentikasi
- Kalau mau lebih aman, bisa tambahkan login untuk halaman `scan.html` supaya hanya guru/piket yang bisa akses
- `scan.html` butuh HTTPS kalau diakses bukan dari localhost, karena browser mewajibkan koneksi aman untuk akses kamera
