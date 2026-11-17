<?php
session_start();
require_once 'koneksi.php';

// Pastikan user sudah login untuk melakukan aksi modifikasi data
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?pesan=wajib_login");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_alat = trim($_POST['kode_alat'] ?? '');
    $nama_alat = trim($_POST['nama_alat'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $merk = trim($_POST['merk'] ?? '');
    $tipe_model = trim($_POST['tipe_model'] ?? '');
    $nomor_seri = trim($_POST['nomor_seri'] ?? '');
    $tahun_pengadaan = (int)($_POST['tahun_pengadaan'] ?? date('Y'));
    $lokasi_ruangan = trim($_POST['lokasi_ruangan'] ?? '');
    $distributor = trim($_POST['distributor'] ?? '-');
    $status_alat = trim($_POST['status_alat'] ?? 'Laik Pakai');
    $daya_listrik = trim($_POST['daya_listrik'] ?? '220V / 50Hz');
    $tgl_kalibrasi_terakhir = !empty($_POST['tgl_kalibrasi_terakhir']) ? $_POST['tgl_kalibrasi_terakhir'] : null;
    $tgl_kalibrasi_berikutnya = !empty($_POST['tgl_kalibrasi_berikutnya']) ? $_POST['tgl_kalibrasi_berikutnya'] : null;
    $spesifikasi = trim($_POST['spesifikasi'] ?? '');

    // Cek duplikasi kode alat
    $cek = mysqli_prepare($conn, "SELECT id FROM alat_elektromedis WHERE kode_alat = ?");
    mysqli_stmt_bind_param($cek, "s", $kode_alat);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        mysqli_stmt_close($cek);
        header("Location: index.php?error=" . urlencode("Kode alat '$kode_alat' sudah digunakan!"));
        exit;
    }
    mysqli_stmt_close($cek);

    $sql = "INSERT INTO alat_elektromedis 
            (kode_alat, nama_alat, kategori, merk, tipe_model, nomor_seri, tahun_pengadaan, lokasi_ruangan, distributor, status_alat, daya_listrik, tgl_kalibrasi_terakhir, tgl_kalibrasi_berikutnya, spesifikasi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssisssssss", 
        $kode_alat, $nama_alat, $kategori, $merk, $tipe_model, $nomor_seri, $tahun_pengadaan, $lokasi_ruangan, $distributor, $status_alat, $daya_listrik, $tgl_kalibrasi_terakhir, $tgl_kalibrasi_berikutnya, $spesifikasi
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?sukses=" . urlencode("Alat elektromedis '$nama_alat' berhasil ditambahkan ke inventaris!"));
    } else {
        header("Location: index.php?error=" . urlencode("Gagal menyimpan data: " . mysqli_error($conn)));
    }
    mysqli_stmt_close($stmt);
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama_alat = trim($_POST['nama_alat'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $merk = trim($_POST['merk'] ?? '');
    $tipe_model = trim($_POST['tipe_model'] ?? '');
    $nomor_seri = trim($_POST['nomor_seri'] ?? '');
    $tahun_pengadaan = (int)($_POST['tahun_pengadaan'] ?? date('Y'));
    $lokasi_ruangan = trim($_POST['lokasi_ruangan'] ?? '');
    $distributor = trim($_POST['distributor'] ?? '-');
    $status_alat = trim($_POST['status_alat'] ?? 'Laik Pakai');
    $daya_listrik = trim($_POST['daya_listrik'] ?? '220V / 50Hz');
    $tgl_kalibrasi_terakhir = !empty($_POST['tgl_kalibrasi_terakhir']) ? $_POST['tgl_kalibrasi_terakhir'] : null;
    $tgl_kalibrasi_berikutnya = !empty($_POST['tgl_kalibrasi_berikutnya']) ? $_POST['tgl_kalibrasi_berikutnya'] : null;
    $spesifikasi = trim($_POST['spesifikasi'] ?? '');

    $sql = "UPDATE alat_elektromedis SET 
            nama_alat = ?, kategori = ?, merk = ?, tipe_model = ?, nomor_seri = ?, tahun_pengadaan = ?, lokasi_ruangan = ?, distributor = ?, status_alat = ?, daya_listrik = ?, tgl_kalibrasi_terakhir = ?, tgl_kalibrasi_berikutnya = ?, spesifikasi = ?
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssisssssssi", 
        $nama_alat, $kategori, $merk, $tipe_model, $nomor_seri, $tahun_pengadaan, $lokasi_ruangan, $distributor, $status_alat, $daya_listrik, $tgl_kalibrasi_terakhir, $tgl_kalibrasi_berikutnya, $spesifikasi, $id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?sukses=" . urlencode("Data alat '$nama_alat' berhasil diperbarui!"));
    } else {
        header("Location: index.php?error=" . urlencode("Gagal mengupdate data: " . mysqli_error($conn)));
    }
    mysqli_stmt_close($stmt);
    exit;
}

if ($action === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM alat_elektromedis WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?sukses=" . urlencode("Data alat elektromedis berhasil dihapus!"));
        } else {
            header("Location: index.php?error=" . urlencode("Gagal menghapus data: " . mysqli_error($conn)));
        }
        mysqli_stmt_close($stmt);
    }
    exit;
}

if ($action === 'kalibrasi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $alat_id = (int)($_POST['alat_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $jenis_kegiatan = $_POST['jenis_kegiatan'] ?? 'Kalibrasi BPFK';
    $teknisi = trim($_POST['teknisi'] ?? $_SESSION['nama_lengkap']);
    $hasil = $_POST['hasil'] ?? 'Laik Pakai / Lolos';
    $catatan = trim($_POST['catatan'] ?? '');

    // Simpan ke riwayat pemeliharaan
    $sql_riwayat = "INSERT INTO riwayat_pemeliharaan (alat_id, tanggal, jenis_kegiatan, teknisi, hasil, catatan) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_riwayat);
    mysqli_stmt_bind_param($stmt, "isssss", $alat_id, $tanggal, $jenis_kegiatan, $teknisi, $hasil, $catatan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Update status dan tanggal kalibrasi alat
    $tgl_berikutnya = date('Y-m-d', strtotime('+1 year', strtotime($tanggal)));
    $status_baru = ($hasil === 'Laik Pakai / Lolos') ? 'Laik Pakai' : (($hasil === 'Perlu Perbaikan') ? 'Dalam Perbaikan' : 'Rusak Berat');
    
    $sql_upd = "UPDATE alat_elektromedis SET status_alat = ?, tgl_kalibrasi_terakhir = ?, tgl_kalibrasi_berikutnya = ? WHERE id = ?";
    $stmt_upd = mysqli_prepare($conn, $sql_upd);
    mysqli_stmt_bind_param($stmt_upd, "sssi", $status_baru, $tanggal, $tgl_berikutnya, $alat_id);
    mysqli_stmt_execute($stmt_upd);
    mysqli_stmt_close($stmt_upd);

    header("Location: index.php?sukses=" . urlencode("Catatan kalibrasi & uji teknis berhasil disimpan!"));
    exit;
}

header("Location: index.php");
exit;
