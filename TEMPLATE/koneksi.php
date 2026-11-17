<?php
/**
 * Koneksi Database & Auto-Migration SIM-ALKES
 * Sistem Informasi & Pemeliharaan Alat Elektromedis
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_elektromedis');

// 1. Koneksi awal ke server MySQL
$koneksi_server = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$koneksi_server) {
    die("Gagal terhubung ke MySQL Server: " . mysqli_connect_error());
}

// 2. Buat database jika belum ada
$query_create_db = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($koneksi_server, $query_create_db)) {
    die("Gagal membuat database: " . mysqli_error($koneksi_server));
}

// 3. Sambungkan ke database db_elektromedis
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Gagal terhubung ke database " . DB_NAME . ": " . mysqli_connect_error());
}

// 4. Buat Tabel Users jika belum ada
$query_users_table = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin IPSRS', 'Teknisi Elektromedis', 'Kepala Ruangan', 'Staff Medis') DEFAULT 'Teknisi Elektromedis',
    `foto` VARCHAR(255) DEFAULT 'default.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $query_users_table);

// 5. Buat Tabel Alat Elektromedis jika belum ada
$query_alat_table = "CREATE TABLE IF NOT EXISTS `alat_elektromedis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_alat` VARCHAR(50) NOT NULL UNIQUE,
    `nama_alat` VARCHAR(150) NOT NULL,
    `kategori` VARCHAR(100) NOT NULL,
    `merk` VARCHAR(100) NOT NULL,
    `tipe_model` VARCHAR(100) NOT NULL,
    `nomor_seri` VARCHAR(100) NOT NULL,
    `tahun_pengadaan` INT(4) NOT NULL,
    `lokasi_ruangan` VARCHAR(100) NOT NULL,
    `distributor` VARCHAR(150) DEFAULT '-',
    `status_alat` ENUM('Laik Pakai', 'Perlu Kalibrasi', 'Dalam Perbaikan', 'Rusak Berat') DEFAULT 'Laik Pakai',
    `daya_listrik` VARCHAR(50) DEFAULT '220V / 50Hz',
    `tgl_kalibrasi_terakhir` DATE NULL,
    `tgl_kalibrasi_berikutnya` DATE NULL,
    `spesifikasi` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $query_alat_table);

// 6. Buat Tabel Riwayat Pemeliharaan jika belum ada
$query_riwayat_table = "CREATE TABLE IF NOT EXISTS `riwayat_pemeliharaan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `alat_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `jenis_kegiatan` ENUM('Kalibrasi BPFK', 'Preventive Maintenance', 'Corrective Maintenance', 'Uji Keselamatan Listrik') NOT NULL,
    `teknisi` VARCHAR(100) NOT NULL,
    `hasil` ENUM('Laik Pakai / Lolos', 'Perlu Perbaikan', 'Tidak Laik') NOT NULL,
    `catatan` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`alat_id`) REFERENCES `alat_elektromedis`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $query_riwayat_table);

// 7. Seed Akun Default jika tabel users masih kosong
$cek_users = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `users`");
$row_users = mysqli_fetch_assoc($cek_users);
if ($row_users['total'] == 0) {
    $pass_admin = password_hash('admin123', PASSWORD_BCRYPT);
    $pass_teknisi = password_hash('teknisi123', PASSWORD_BCRYPT);

    $sql_seed_user = "INSERT INTO `users` (`nama_lengkap`, `username`, `email`, `password`, `role`) VALUES
    ('Ahmad Fauzi, S.Tr.TEM', 'admin', 'admin.elektromedis@rumahsakit.id', '$pass_admin', 'Admin IPSRS'),
    ('Budi Santoso, A.Md.TEM', 'teknisi', 'teknisi.budi@rumahsakit.id', '$pass_teknisi', 'Teknisi Elektromedis')";
    mysqli_query($conn, $sql_seed_user);
}

// 8. Seed Data Alat Elektromedis jika tabel masih kosong
$cek_alat = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `alat_elektromedis`");
$row_alat = mysqli_fetch_assoc($cek_alat);
if ($row_alat['total'] == 0) {
    $sql_seed_alat = "INSERT INTO `alat_elektromedis` 
    (`kode_alat`, `nama_alat`, `kategori`, `merk`, `tipe_model`, `nomor_seri`, `tahun_pengadaan`, `lokasi_ruangan`, `distributor`, `status_alat`, `daya_listrik`, `tgl_kalibrasi_terakhir`, `tgl_kalibrasi_berikutnya`, `spesifikasi`) 
    VALUES
    ('ELK-ICU-001', 'Defibrillator Biphasic & Pacer', 'Life Support & Terapi', 'Mindray', 'BeneHeart D3', 'SN-MD-98231', 2022, 'ICU (Intensive Care Unit)', 'PT. Global Medika Teknika', 'Laik Pakai', '220V 150W / Battery Li-ion', '2024-03-15', '2025-03-15', 'Energi 1-360 Joule, Layar TFT 7 inch, ECG 3/5 lead monitor, waktu pengisian daya < 5 detik ke 200J.'),
    ('ELK-ICU-002', 'Ventilator Mekanik ICU', 'Life Support & Terapi', 'Hamilton Medical', 'HAMILTON-C1', 'SN-HM-41029', 2021, 'ICU (Intensive Care Unit)', 'PT. Surya Biomedika', 'Laik Pakai', '220V 210W', '2024-02-10', '2025-02-10', 'Mode ventilasi ASV, PCV+, PSV, SIMV, nCPAP. Dilengkapi turbin internal tanpa kompresor sentral.'),
    ('ELK-IGD-003', 'Patient Monitor Multiparameter (5 Para)', 'Diagnostik & Monitoring', 'Philips Healthcare', 'IntelliVue MX450', 'SN-PH-77120', 2023, 'IGD (Instalasi Gawat Darurat)', 'PT. Philips Indonesia Healthcare', 'Laik Pakai', '100-240V 75W', '2024-05-20', '2025-05-20', 'Monitoring ECG 12-lead, SpO2, NIBP, Respiration, 2 Temp, EtCO2 ready. Layar sentuh 12 inch high-definition.'),
    ('ELK-POL-004', 'Electrocardiograph (EKG 12-Channel)', 'Diagnostik & Monitoring', 'GE Healthcare', 'MAC 2000', 'SN-GE-33411', 2022, 'Poliklinik Jantung & Pembuluh', 'PT. GE Medika Nusantara', 'Laik Pakai', '220V 60W', '2024-01-18', '2025-01-18', 'Analisis otomatis algoritma Marquette 12SL, printer thermal A4 built-in, konektivitas LAN & PDF export.'),
    ('ELK-OK-005', 'Electrosurgical Unit (ESU Cauter)', 'Bedah & Elektrosurgikal', 'Valleylab / Medtronic', 'ForceTriad Energy Platform', 'SN-FT-66512', 2020, 'Kamar Operasi (OK Bedah 1)', 'PT. Medtronic Health Solutions', 'Perlu Kalibrasi', '220V 450W', '2023-11-05', '2024-11-05', 'Teknologi LigaSure vessel sealing, Monopolar Cut/Coag 300W, Bipolar 120W, deteksi impedansi jaringan otomatis.'),
    ('ELK-NIC-006', 'Infant Incubator & Radiant Warmer', 'Life Support & Terapi', 'GE Healthcare', 'Giraffe OmniBed Carestation', 'SN-GF-10933', 2021, 'NICU (Neonatal ICU)', 'PT. GE Medika Nusantara', 'Perlu Kalibrasi', '220V 800W', '2023-10-12', '2024-10-12', 'Kombinasi inkubator tertutup dan infant warmer terbuka otomatis, kontrol kelembapan servo, timbangan terintegrasi.'),
    ('ELK-RAW-007', 'Syringe Pump Presisi Mikro', 'Penunjang Terapi', 'B. Braun', 'Perfusor Space', 'SN-BB-88401', 2023, 'Ruang Rawat Inap Melati', 'PT. B. Braun Medical Indonesia', 'Laik Pakai', '100-240V 15W', '2024-06-01', '2025-06-01', 'Aliran 0.01 - 999.9 ml/jam, akurasi +/- 2%, pustaka obat DoseGuard, proteksi free-flow clamp.'),
    ('ELK-RAW-008', 'Infusion Pump Otomatis', 'Penunjang Terapi', 'Terumo', 'TE-171 Terufusion', 'SN-TR-55320', 2022, 'Ruang Rawat Inap Anggrek', 'PT. Terumo Indonesia', 'Laik Pakai', '220V 25W', '2024-04-14', '2025-04-14', 'Metode peristaltik finger cassette, deteksi tetesan ultrasonik, sensor oklusi tekanan 3 tingkat.'),
    ('ELK-RAD-009', 'Mobile X-Ray Digital Unit', 'Radiologi & Imaging', 'Siemens Healthineers', 'Mobilett Elara TS', 'SN-SM-22910', 2021, 'Instalasi Radiologi', 'PT. Siemens Indonesia', 'Dalam Perbaikan', '220V 30kW Generator', '2023-09-22', '2024-09-22', 'Flat panel detector nirkabel 35x43 cm, daya output 30 kW, sistem kemudi motor listrik, tabung sinar-X fokus ganda.'),
    ('ELK-OK-010', 'Suction Pump Bedah Heavy Duty', 'Bedah & Tindakan', 'Thomas Medical', '1632 High Vacuum Aspirator', 'SN-TM-90812', 2022, 'Kamar Operasi (OK Bedah 2)', 'PT. Medquest Jaya Global', 'Laik Pakai', '220V 180W', '2024-07-10', '2025-07-10', 'Kapasitas hisap > 40 L/menit, vakum maks -680 mmHg, tabung ganda 2x2.5 liter tahan autoklaf, filter hidrofobik antibakteri.'),
    ('ELK-HEM-011', 'Mesin Hemodialisis (Cuci Darah)', 'Life Support & Terapi', 'Fresenius Medical Care', '4008S Classic', 'SN-FN-71829', 2019, 'Unit Hemodialisa', 'PT. Fresenius Medical Care ID', 'Laik Pakai', '220V 1500W', '2024-05-02', '2025-05-02', 'Ultrafiltrasi volumetrik terkontrol, sensor kebocoran darah optik, monitor konduktivitas dialisat, disinfection program otomatis.'),
    ('ELK-POL-012', 'USG Doppler 4D Color', 'Diagnostik & Monitoring', 'GE Healthcare', 'Voluson E8 Expert', 'SN-VL-50119', 2022, 'Poli Kandungan & Obgyn', 'PT. GE Medika Nusantara', 'Laik Pakai', '220V 600W', '2024-08-11', '2025-08-11', 'Transduser Matrix 4D, layar OLED 23 inch, Color Doppler, Pulse Wave Doppler, HDlive rendering imaging.')";
    mysqli_query($conn, $sql_seed_alat);
}
