<?php
session_start();
require_once 'koneksi.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_nama = $_SESSION['nama_lengkap'] ?? 'Pengunjung Tamu';
$user_role = $_SESSION['role'] ?? 'Tamu (Guest)';
$user_username = $_SESSION['username'] ?? '';

// Ambil pesan flash
$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
$login_success = isset($_GET['login_success']);

// Ambil data statistik alat elektromedis
$query_stat = "SELECT 
    COUNT(*) AS total_alat,
    SUM(CASE WHEN status_alat = 'Laik Pakai' THEN 1 ELSE 0 END) AS laik_pakai,
    SUM(CASE WHEN status_alat = 'Perlu Kalibrasi' THEN 1 ELSE 0 END) AS perlu_kalibrasi,
    SUM(CASE WHEN status_alat IN ('Dalam Perbaikan', 'Rusak Berat') THEN 1 ELSE 0 END) AS dalam_perbaikan
FROM alat_elektromedis";
$res_stat = mysqli_query($conn, $query_stat);
$stat = mysqli_fetch_assoc($res_stat);

// Ambil seluruh data alat elektromedis
$query_alat = "SELECT * FROM alat_elektromedis ORDER BY id DESC";
$res_alat = mysqli_query($conn, $query_alat);
$alat_list = [];
while ($row = mysqli_fetch_assoc($res_alat)) {
    $alat_list[] = $row;
}

// Ambil data riwayat pemeliharaan
$query_riwayat = "SELECT r.*, a.nama_alat, a.kode_alat 
                  FROM riwayat_pemeliharaan r 
                  JOIN alat_elektromedis a ON r.alat_id = a.id 
                  ORDER BY r.tanggal DESC LIMIT 10";
$res_riwayat = mysqli_query($conn, $query_riwayat);
$riwayat_list = [];
while ($row = mysqli_fetch_assoc($res_riwayat)) {
    $riwayat_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM-ALKES - Sistem Informasi & Pemeliharaan Alat Elektromedis</title>
  <link rel="icon" type="image/svg+xml" href="dist/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dist/assets/main-CsgFyUFh.css">
  <script>
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <style>
    body { font-family: 'Figtree', sans-serif; }
    .ecg-line {
      stroke-dasharray: 1000;
      stroke-dashoffset: 1000;
      animation: ecgDash 4s linear infinite;
    }
    @keyframes ecgDash {
      to { stroke-dashoffset: 0; }
    }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; }
    }
  </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col selection:bg-cyan-500 selection:text-white">

  <!-- NAVBAR UTAMA -->
  <header class="no-print sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      
      <!-- Brand Logo -->
      <a href="index.php" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white shadow-md shadow-cyan-600/30 group-hover:scale-105 transition-transform">
          <!-- Medical Pulse SVG -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">SIM-ALKES</span>
            <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 font-bold">
              ELEKTROMEDIS
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">Instalasi Pemeliharaan Sarana RS (IPSRS)</p>
        </div>
      </a>

      <!-- Menu Navigasi Tengah -->
      <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
        <a href="#katalog" onclick="switchTab('katalog')" class="px-3 py-2 rounded-lg text-cyan-600 dark:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
          Katalog Alat
        </a>
        <a href="#tabel" onclick="switchTab('tabel')" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
          Tabel Inventaris
        </a>
        <a href="#sop" onclick="switchTab('sop')" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
          SOP & Kalibrasi IEC
        </a>
        <a href="#riwayat" onclick="switchTab('riwayat')" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
          Riwayat Servis
        </a>
      </nav>

      <!-- Aksi Kanan: Theme Toggle & User Auth -->
      <div class="flex items-center gap-3">
        
        <!-- Tombol Toggle Dark Mode -->
        <button type="button" onclick="toggleTheme()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Ubah Mode Gelap/Terang">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 dark:hidden" viewBox="0 0 20 20" fill="currentColor">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden dark:block text-amber-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
          </svg>
        </button>

        <?php if ($is_logged_in): ?>
          <!-- Status User Logged In -->
          <div class="flex items-center gap-2.5">
            <div class="hidden sm:block text-right">
              <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5 justify-end">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?= htmlspecialchars($user_nama); ?>
              </div>
              <span class="text-[10px] font-semibold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">
                <?= htmlspecialchars($user_role); ?>
              </span>
            </div>

            <!-- Tombol Tambah Alat -->
            <button type="button" onclick="bukaModalTambah()" class="py-2 px-3 sm:px-3.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs shadow-md shadow-cyan-600/30 flex items-center gap-1.5 transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
              </svg>
              <span class="hidden sm:inline">Tambah Alat</span>
            </button>

            <!-- Tombol Logout -->
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors" title="Keluar (Logout)">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </a>
          </div>

        <?php else: ?>
          <!-- Tombol Login & Register untuk Guest -->
          <div class="flex items-center gap-2">
            <a href="login.php" class="py-2 px-3.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:border-cyan-500 dark:hover:border-cyan-500 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
              Masuk (Login)
            </a>
            <a href="register.php" class="py-2 px-3.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md shadow-cyan-600/20 transition-all">
              Daftar Akun
            </a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </header>

  <!-- ALERT NOTIFIKASI FLASH -->
  <div class="no-print max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
    <?php if ($login_success): ?>
      <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div>
            <strong>Selamat datang kembali, <?= htmlspecialchars($user_nama); ?>!</strong>
            <p class="text-xs text-emerald-700 dark:text-emerald-300">Anda berhasil login dengan hak akses sebagai <b><?= htmlspecialchars($user_role); ?></b>.</p>
          </div>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
      </div>
    <?php endif; ?>

    <?php if (!empty($pesan_sukses)): ?>
      <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 text-cyan-800 dark:text-cyan-200 text-sm flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
          <span class="text-xl">✅</span>
          <span><?= htmlspecialchars($pesan_sukses); ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-cyan-600 hover:text-cyan-900">&times;</button>
      </div>
    <?php endif; ?>

    <?php if (!empty($pesan_error)): ?>
      <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
          <span class="text-xl">⚠️</span>
          <span><?= htmlspecialchars($pesan_error); ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
      </div>
    <?php endif; ?>
  </div>

  <!-- HERO SECTION ELEKTROMEDIS -->
  <section class="no-print relative overflow-hidden py-10 sm:py-14 bg-gradient-to-b from-cyan-900/10 via-transparent to-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <div class="lg:col-span-7 space-y-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 dark:bg-cyan-950/80 border border-cyan-200 dark:border-cyan-800 text-cyan-800 dark:text-cyan-300 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-cyan-500 animate-ping"></span>
            Standar Akreditasi KARS & Pengujian BPFK
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
            Sistem Manajemen & Kalibrasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 to-blue-600">Alat Elektromedis</span>
          </h1>

          <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed">
            Platform komprehensif untuk inventarisasi terpusat, pengawasan jadwal kalibrasi tahunan, pengujian keselamatan listrik <strong>IEC 60601-1</strong>, dan penelusuran riwayat servis peralatan medis rumah sakit.
          </p>

          <div class="flex flex-wrap gap-3 pt-2">
            <a href="#katalog" onclick="switchTab('katalog')" class="py-2.5 px-5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-sm shadow-lg shadow-cyan-600/30 transition-all flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
              Jelajahi Katalog Alat
            </a>
            <a href="#tabel" onclick="switchTab('tabel')" class="py-2.5 px-5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-semibold text-sm hover:border-cyan-500 transition-colors flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Tabel Inventaris
            </a>
            <?php if (!$is_logged_in): ?>
              <a href="login.php" class="py-2.5 px-5 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 font-semibold text-sm hover:bg-blue-100 transition-all">
                🔐 Login Teknisi
              </a>
            <?php else: ?>
              <button onclick="bukaModalTambah()" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Input Alat Baru
              </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Banner Visual / ECG Wave Graphic -->
        <div class="lg:col-span-5">
          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 p-6 shadow-xl backdrop-blur relative">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Live Hospital Telemetry</span>
              </div>
              <span class="text-xs font-mono text-cyan-600 dark:text-cyan-400 font-bold">220V / 50Hz NORMAL</span>
            </div>

            <!-- SVG Waveform -->
            <div class="h-28 flex items-center justify-center bg-slate-950 rounded-xl p-3 relative overflow-hidden">
              <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:12px_12px] opacity-40"></div>
              <svg class="w-full h-full text-cyan-400" viewBox="0 0 500 100" fill="none" stroke="currentColor" stroke-width="2.5">
                <path class="ecg-line" d="M0,50 L80,50 L100,50 L115,20 L130,85 L145,10 L160,70 L175,50 L240,50 L260,35 L280,50 L340,50 L355,20 L370,85 L385,10 L400,70 L415,50 L500,50" />
              </svg>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-4 text-center">
              <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                <span class="text-[10px] text-slate-500 uppercase font-semibold">Tegangan Jala</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 font-mono">223.4 VAC</p>
              </div>
              <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                <span class="text-[10px] text-slate-500 uppercase font-semibold">Ground Loop</span>
                <p class="text-sm font-bold text-emerald-600 font-mono">0.08 &Omega; (OK)</p>
              </div>
              <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                <span class="text-[10px] text-slate-500 uppercase font-semibold">Leakage Curr</span>
                <p class="text-sm font-bold text-cyan-600 font-mono">14.2 &mu;A</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- KARTU STATISTIK (KPI CARDS) -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Total Alat -->
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Alat Terdata</span>
          <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-slate-900 dark:text-white"><?= (int)$stat['total_alat']; ?></span>
          <span class="text-xs text-slate-500">Unit Terdaftar</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Inventaris elektromedis aktif RS</p>
      </div>

      <!-- Laik Pakai -->
      <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/20 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Laik Pakai</span>
          <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400"><?= (int)$stat['laik_pakai']; ?></span>
          <span class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">
            <?= $stat['total_alat'] > 0 ? round(($stat['laik_pakai'] / $stat['total_alat']) * 100) : 0; ?>%
          </span>
        </div>
        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-2">Lolos uji & siap operasional</p>
      </div>

      <!-- Perlu Kalibrasi -->
      <div class="rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Perlu Kalibrasi</span>
          <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-400"><?= (int)$stat['perlu_kalibrasi']; ?></span>
          <span class="text-xs text-amber-700 dark:text-amber-300">Unit Wajib BPFK</span>
        </div>
        <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-2">Masa berlaku kalibrasi habis</p>
      </div>

      <!-- Dalam Perbaikan -->
      <div class="rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Dalam Perbaikan</span>
          <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-300 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-rose-600 dark:text-rose-400"><?= (int)$stat['dalam_perbaikan']; ?></span>
          <span class="text-xs text-rose-700 dark:text-rose-300">Unit Non-Aktif</span>
        </div>
        <p class="text-[11px] text-rose-700 dark:text-rose-400 mt-2">Corrective Maintenance / Sparepart</p>
      </div>

    </div>
  </section>

  <!-- TAB NAVIGASI KONTEN -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2 w-full no-print">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-3">
      
      <!-- Nav Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto">
        <button type="button" onclick="switchTab('katalog')" id="tabBtn-katalog"
                class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all bg-cyan-600 text-white shadow-sm shadow-cyan-600/30">
          🖼️ Katalog Visual Alat
        </button>
        <button type="button" onclick="switchTab('tabel')" id="tabBtn-tabel"
                class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
          📋 Tabel Data Inventaris
        </button>
        <button type="button" onclick="switchTab('sop')" id="tabBtn-sop"
                class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
          ⚡ Standar Keselamatan IEC 60601-1
        </button>
        <button type="button" onclick="switchTab('riwayat')" id="tabBtn-riwayat"
                class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
          🕒 Riwayat Pemeliharaan
        </button>
      </div>

      <!-- Quick Search Bar Realtime -->
      <div class="relative w-full sm:w-64">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
        <input type="text" id="liveSearchInput" onkeyup="filterAlatLive()" 
               placeholder="Cari nama, merk, kode..."
               class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

    </div>
  </section>

  <!-- AREA KONTEN UTAMA -->
  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
    
    <!-- TAB 1: KATALOG VISUAL ALAT ELEKTROMEDIS -->
    <div id="tabContent-katalog" class="space-y-6">
      
      <!-- Filter Kategori & Lokasi Buttons -->
      <div class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
          <span class="text-slate-400 font-medium me-1">Kategori:</span>
          <button type="button" onclick="filterKategori('all', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-cyan-600 text-white font-medium">Semua (<?= count($alat_list); ?>)</button>
          <button type="button" onclick="filterKategori('Life Support & Terapi', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium">Life Support & Terapi</button>
          <button type="button" onclick="filterKategori('Diagnostik & Monitoring', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium">Diagnostik & Monitoring</button>
          <button type="button" onclick="filterKategori('Bedah & Elektrosurgikal', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium">Bedah & ESU</button>
          <button type="button" onclick="filterKategori('Radiologi & Imaging', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium">Radiologi</button>
          <button type="button" onclick="filterKategori('Penunjang Terapi', this)" class="kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium">Penunjang Terapi</button>
        </div>

        <div class="flex items-center gap-2 text-xs">
          <label for="filterStatusSelect" class="text-slate-400">Status:</label>
          <select id="filterStatusSelect" onchange="filterStatus()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs">
            <option value="all">Semua Status</option>
            <option value="Laik Pakai">Laik Pakai</option>
            <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
            <option value="Dalam Perbaikan">Dalam Perbaikan</option>
          </select>
        </div>
      </div>

      <!-- Grid Cards Alat Elektromedis -->
      <div id="gridAlat" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($alat_list as $alat): 
          $status_class = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300';
          if ($alat['status_alat'] === 'Perlu Kalibrasi') {
              $status_class = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300';
          } elseif (in_array($alat['status_alat'], ['Dalam Perbaikan', 'Rusak Berat'])) {
              $status_class = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300';
          }
        ?>
          <div class="alat-card rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden group"
               data-nama="<?= strtolower(htmlspecialchars($alat['nama_alat'])); ?>"
               data-merk="<?= strtolower(htmlspecialchars($alat['merk'])); ?>"
               data-kode="<?= strtolower(htmlspecialchars($alat['kode_alat'])); ?>"
               data-ruangan="<?= strtolower(htmlspecialchars($alat['lokasi_ruangan'])); ?>"
               data-kategori="<?= htmlspecialchars($alat['kategori']); ?>"
               data-status="<?= htmlspecialchars($alat['status_alat']); ?>">
            
            <div class="p-5">
              <!-- Top Badge Header -->
              <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                  <?= htmlspecialchars($alat['kategori']); ?>
                </span>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border <?= $status_class; ?>">
                  <?= htmlspecialchars($alat['status_alat']); ?>
                </span>
              </div>

              <!-- Judul & Merk Alat -->
              <div class="mb-3">
                <span class="text-[11px] font-mono text-cyan-600 dark:text-cyan-400 font-semibold tracking-wide block">
                  <?= htmlspecialchars($alat['kode_alat']); ?>
                </span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-cyan-600 transition-colors mt-0.5">
                  <?= htmlspecialchars($alat['nama_alat']); ?>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  <?= htmlspecialchars($alat['merk']); ?> &bull; <span class="font-medium"><?= htmlspecialchars($alat['tipe_model']); ?></span>
                </p>
              </div>

              <!-- Parameter Teknis -->
              <div class="space-y-1.5 py-3 border-y border-slate-100 dark:border-slate-800/80 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Lokasi Unit:</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($alat['lokasi_ruangan']); ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Nomor Seri:</span>
                  <span class="font-mono text-slate-700 dark:text-slate-300"><?= htmlspecialchars($alat['nomor_seri']); ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Daya Listrik:</span>
                  <span class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($alat['daya_listrik']); ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Jatuh Tempo Kalibrasi:</span>
                  <span class="font-semibold <?= ($alat['status_alat'] === 'Perlu Kalibrasi') ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300'; ?>">
                    <?= $alat['tgl_kalibrasi_berikutnya'] ? date('d M Y', strtotime($alat['tgl_kalibrasi_berikutnya'])) : '-'; ?>
                  </span>
                </div>
              </div>

              <!-- Snippet Deskripsi -->
              <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-3 italic">
                "<?= htmlspecialchars($alat['spesifikasi'] ?: 'Tidak ada catatan spesifikasi tambahan.'); ?>"
              </p>
            </div>

            <!-- Card Actions -->
            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
              <button type="button" onclick='bukaModalDetail(<?= json_encode($alat); ?>)' 
                      class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Spesifikasi & Uji
              </button>

              <?php if ($is_logged_in): ?>
                <div class="flex items-center gap-1.5">
                  <button type="button" onclick='bukaModalKalibrasi(<?= $alat['id']; ?>, "<?= addslashes($alat['nama_alat']); ?>")' 
                          class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40" title="Catat Kalibrasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                  </button>
                  <button type="button" onclick='bukaModalEdit(<?= json_encode($alat); ?>)' 
                          class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40" title="Edit Alat">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <a href="action_alat.php?action=hapus&id=<?= $alat['id']; ?>" 
                     onclick="return confirm('Apakah Anda yakin ingin menghapus data alat <?= addslashes($alat['nama_alat']); ?>?')"
                     class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40" title="Hapus Alat">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </a>
                </div>
              <?php else: ?>
                <a href="login.php" class="text-slate-400 hover:text-cyan-600 text-[11px]">
                  Login untuk kelola &rarr;
                </a>
              <?php endif; ?>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- TAB 2: TABEL INVENTARIS ALAT ELEKTROMEDIS -->
    <div id="tabContent-tabel" class="hidden space-y-4">
      
      <!-- Action Toolbar -->
      <div class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 no-print">
        <div class="flex items-center gap-2">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Inventaris Lengkap</h2>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 font-semibold">
            <?= count($alat_list); ?> Data
          </span>
        </div>

        <div class="flex items-center gap-2">
          <button type="button" onclick="window.print()" class="py-2 px-3.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold flex items-center gap-1.5 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Laporan
          </button>

          <?php if ($is_logged_in): ?>
            <button type="button" onclick="bukaModalTambah()" class="py-2 px-3.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-all shadow-md shadow-cyan-600/20">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Tambah Alat Baru
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tabel Responsif -->
      <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300" id="mainTableAlat">
          <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-[11px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-700">
            <tr>
              <th class="py-3.5 px-4">No</th>
              <th class="py-3.5 px-4">Kode & Nama Alat</th>
              <th class="py-3.5 px-4">Kategori</th>
              <th class="py-3.5 px-4">Merk & Tipe</th>
              <th class="py-3.5 px-4">Nomor Seri</th>
              <th class="py-3.5 px-4">Lokasi Ruangan</th>
              <th class="py-3.5 px-4">Status Kelaikan</th>
              <th class="py-3.5 px-4">Jatuh Tempo Kalibrasi</th>
              <th class="py-3.5 px-4 text-center no-print">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php if (empty($alat_list)): ?>
              <tr>
                <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data alat elektromedis.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($alat_list as $index => $alat): 
                $status_badge = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300';
                if ($alat['status_alat'] === 'Perlu Kalibrasi') {
                    $status_badge = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300';
                } elseif (in_array($alat['status_alat'], ['Dalam Perbaikan', 'Rusak Berat'])) {
                    $status_badge = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300';
                }
              ?>
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                  <td class="py-3 px-4 font-mono text-slate-400"><?= $index + 1; ?></td>
                  <td class="py-3 px-4">
                    <span class="font-mono text-[10px] text-cyan-600 dark:text-cyan-400 font-bold block"><?= htmlspecialchars($alat['kode_alat']); ?></span>
                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($alat['nama_alat']); ?></span>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] font-semibold text-slate-600 dark:text-slate-300">
                      <?= htmlspecialchars($alat['kategori']); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <span class="font-semibold"><?= htmlspecialchars($alat['merk']); ?></span>
                    <span class="text-slate-400 block text-[11px]"><?= htmlspecialchars($alat['tipe_model']); ?></span>
                  </td>
                  <td class="py-3 px-4 font-mono"><?= htmlspecialchars($alat['nomor_seri']); ?></td>
                  <td class="py-3 px-4 font-medium"><?= htmlspecialchars($alat['lokasi_ruangan']); ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $status_badge; ?>">
                      <?= htmlspecialchars($alat['status_alat']); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 font-medium">
                    <?= $alat['tgl_kalibrasi_berikutnya'] ? date('d/m/Y', strtotime($alat['tgl_kalibrasi_berikutnya'])) : '-'; ?>
                  </td>
                  <td class="py-3 px-4 text-center no-print">
                    <div class="flex items-center justify-center gap-1.5">
                      <button type="button" onclick='bukaModalDetail(<?= json_encode($alat); ?>)' 
                              class="p-1 rounded-lg text-cyan-600 hover:bg-cyan-50 dark:hover:bg-cyan-950/40" title="Detail">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>

                      <?php if ($is_logged_in): ?>
                        <button type="button" onclick='bukaModalKalibrasi(<?= $alat['id']; ?>, "<?= addslashes($alat['nama_alat']); ?>")' 
                                class="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40" title="Kalibrasi">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                          </svg>
                        </button>
                        <button type="button" onclick='bukaModalEdit(<?= json_encode($alat); ?>)' 
                                class="p-1 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40" title="Edit">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <a href="action_alat.php?action=hapus&id=<?= $alat['id']; ?>" 
                           onclick="return confirm('Hapus alat <?= addslashes($alat['nama_alat']); ?>?')"
                           class="p-1 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40" title="Hapus">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>

    <!-- TAB 3: STANDAR KESELAMATAN IEC 60601-1 & REGULASI BPFK -->
    <div id="tabContent-sop" class="hidden space-y-6">
      
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Standar Keselamatan Listrik Alat Elektromedis</h2>
            <p class="text-xs text-slate-500">Acuan Internasional IEC 60601-1 & Permenkes RI No. 54 Tahun 2015</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-6">
          <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <span class="text-xs font-bold text-cyan-600 uppercase tracking-wider block mb-1">Parameter 1</span>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Resistansi Pembumian (Ground)</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              Memastikan jalur proteksi pembumian sasis ke stop kontak memiliki resistansi sangat rendah:
            </p>
            <div class="mt-3 p-2.5 rounded-lg bg-white dark:bg-slate-900 font-mono text-xs font-semibold text-emerald-600 border border-slate-200 dark:border-slate-800">
              Batas Maksimal: &le; 0.2 &Omega; (200 m&Omega;)
            </div>
          </div>

          <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <span class="text-xs font-bold text-cyan-600 uppercase tracking-wider block mb-1">Parameter 2</span>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Arus Bocor Sasis (Enclosure Leakage)</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              Mengukur arus yang mengalir dari sasis peralatan medis melalui tubuh manusia ke ground:
            </p>
            <div class="mt-3 p-2.5 rounded-lg bg-white dark:bg-slate-900 font-mono text-xs font-semibold text-cyan-600 border border-slate-200 dark:border-slate-800">
              Kondisi Normal (NC): &le; 100 &mu;A<br>
              Kondisi Gangguan Tunggal (SFC): &le; 500 &mu;A
            </div>
          </div>

          <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <span class="text-xs font-bold text-cyan-600 uppercase tracking-wider block mb-1">Parameter 3</span>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Arus Bocor Pasien (Applied Part)</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              Arus yang mungkin bocor ke pasien melalui sensor elektrostatik (Applied Part Type CF / BF / B):
            </p>
            <div class="mt-3 p-2.5 rounded-lg bg-white dark:bg-slate-900 font-mono text-xs font-semibold text-amber-600 border border-slate-200 dark:border-slate-800">
              Tipe CF (Jantung): &le; 10 &mu;A (NC)<br>
              Tipe BF/B: &le; 100 &mu;A (NC)
            </div>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Siklus Pemeliharaan Berkala (Preventive Maintenance)</h3>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <span class="font-bold text-cyan-600 block mb-1">Tahap 1: Fisik & Aksesoris</span>
              Cek kabel daya, integritas selongsong kabel, grounding plug, dan keutuhan casing.
            </div>
            <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <span class="font-bold text-cyan-600 block mb-1">Tahap 2: Uji Kinerja & Fungsi</span>
              Uji akurasi sensor, alarm visual/suara, responsivitas tombol, dan kalibrasi output.
            </div>
            <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <span class="font-bold text-cyan-600 block mb-1">Tahap 3: Uji Keselamatan Listrik</span>
              Menggunakan Electrical Safety Analyzer (ESA) sesuai metode IEC 62353.
            </div>
            <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <span class="font-bold text-cyan-600 block mb-1">Tahap 4: Pelabelan Kelaikan</span>
              Pemasangan stiker sertifikat BPFK hijau (Laik Pakai) atau merah (Tidak Laik).
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- TAB 4: RIWAYAT PEMELIHARAAN -->
    <div id="tabContent-riwayat" class="hidden space-y-4">
      <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Log Riwayat Kalibrasi & Servis Terkini</h2>
        <p class="text-xs text-slate-500 mb-4">Catatan teknis hasil preventive & corrective maintenance alat elektromedis</p>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Kode & Nama Alat</th>
                <th class="py-3 px-4">Jenis Kegiatan</th>
                <th class="py-3 px-4">Teknisi / Lembaga</th>
                <th class="py-3 px-4">Hasil Pengujian</th>
                <th class="py-3 px-4">Catatan Servis</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <?php if (empty($riwayat_list)): ?>
                <tr>
                  <td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan riwayat pemeliharaan. Silakan gunakan tombol kalibrasi pada alat untuk mencatat.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($riwayat_list as $rw): ?>
                  <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-4 font-mono"><?= date('d/m/Y', strtotime($rw['tanggal'])); ?></td>
                    <td class="py-3 px-4 font-bold"><?= htmlspecialchars($rw['kode_alat'] . ' - ' . $rw['nama_alat']); ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($rw['jenis_kegiatan']); ?></td>
                    <td class="py-3 px-4 font-medium"><?= htmlspecialchars($rw['teknisi']); ?></td>
                    <td class="py-3 px-4">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= ($rw['hasil'] === 'Laik Pakai / Lolos') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'; ?>">
                        <?= htmlspecialchars($rw['hasil']); ?>
                      </span>
                    </td>
                    <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($rw['catatan'] ?: '-'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>

  <!-- MODAL 1: TAMBAH ALAT ELEKTROMEDIS -->
  <div id="modalTambah" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full p-6 relative my-8 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="w-8 h-8 rounded-lg bg-cyan-600 text-white flex items-center justify-center text-sm font-bold">+</span>
          Tambah Alat Elektromedis Baru
        </h3>
        <button type="button" onclick="tutupModal('modalTambah')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
      </div>

      <form method="POST" action="action_alat.php" class="space-y-4 text-xs">
        <input type="hidden" name="action" value="tambah">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1">Kode Inventaris Alat *</label>
            <input type="text" name="kode_alat" required placeholder="Contoh: ELK-ICU-015"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-cyan-500">
          </div>

          <div>
            <label class="block font-semibold mb-1">Nama Alat Elektromedis *</label>
            <input type="text" name="nama_alat" required placeholder="Contoh: Defibrillator Biphasic"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-cyan-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-semibold mb-1">Kategori *</label>
            <select name="kategori" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Life Support & Terapi">Life Support & Terapi</option>
              <option value="Diagnostik & Monitoring">Diagnostik & Monitoring</option>
              <option value="Bedah & Elektrosurgikal">Bedah & Elektrosurgikal</option>
              <option value="Radiologi & Imaging">Radiologi & Imaging</option>
              <option value="Penunjang Terapi">Penunjang Terapi</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold mb-1">Merk / Brand *</label>
            <input type="text" name="merk" required placeholder="Mindray, Philips, dll."
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Tipe / Model *</label>
            <input type="text" name="tipe_model" required placeholder="BeneHeart D3, dll."
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-semibold mb-1">Nomor Seri (SN) *</label>
            <input type="text" name="nomor_seri" required placeholder="SN-XXXX-YYYY"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono">
          </div>

          <div>
            <label class="block font-semibold mb-1">Tahun Pengadaan *</label>
            <input type="number" name="tahun_pengadaan" required value="<?= date('Y'); ?>" min="1990" max="2035"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Lokasi Ruangan *</label>
            <input type="text" name="lokasi_ruangan" required placeholder="ICU, IGD, OK 1, dll."
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1">Status Kelaikan Alat *</label>
            <select name="status_alat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Laik Pakai" selected>Laik Pakai</option>
              <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
              <option value="Dalam Perbaikan">Dalam Perbaikan</option>
              <option value="Rusak Berat">Rusak Berat</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold mb-1">Karakteristik Daya Listrik</label>
            <input type="text" name="daya_listrik" value="220V / 50Hz"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1">Tanggal Kalibrasi Terakhir</label>
            <input type="date" name="tgl_kalibrasi_terakhir"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Tanggal Jatuh Tempo Kalibrasi</label>
            <input type="date" name="tgl_kalibrasi_berikutnya"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Distributor / Vendor Agen</label>
          <input type="text" name="distributor" placeholder="PT. Distributor Resmi Alat"
                 class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
        </div>

        <div>
          <label class="block font-semibold mb-1">Spesifikasi Teknis & Keterangan</label>
          <textarea name="spesifikasi" rows="3" placeholder="Tuliskan spesifikasi teknis, fitur, batas toleransi akurasi, atau catatan khusus..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
          <button type="button" onclick="tutupModal('modalTambah')" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold shadow-md shadow-cyan-600/30">Simpan Data Alat</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 2: EDIT ALAT ELEKTROMEDIS -->
  <div id="modalEdit" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full p-6 relative my-8 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Data Alat Elektromedis</h3>
        <button type="button" onclick="tutupModal('modalEdit')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
      </div>

      <form method="POST" action="action_alat.php" class="space-y-4 text-xs">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit_id" name="id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1">Nama Alat *</label>
            <input type="text" id="edit_nama_alat" name="nama_alat" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Kategori *</label>
            <select id="edit_kategori" name="kategori" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Life Support & Terapi">Life Support & Terapi</option>
              <option value="Diagnostik & Monitoring">Diagnostik & Monitoring</option>
              <option value="Bedah & Elektrosurgikal">Bedah & Elektrosurgikal</option>
              <option value="Radiologi & Imaging">Radiologi & Imaging</option>
              <option value="Penunjang Terapi">Penunjang Terapi</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-semibold mb-1">Merk *</label>
            <input type="text" id="edit_merk" name="merk" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Tipe / Model *</label>
            <input type="text" id="edit_tipe_model" name="tipe_model" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Nomor Seri (SN) *</label>
            <input type="text" id="edit_nomor_seri" name="nomor_seri" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-semibold mb-1">Tahun Pengadaan</label>
            <input type="number" id="edit_tahun_pengadaan" name="tahun_pengadaan" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Lokasi Ruangan *</label>
            <input type="text" id="edit_lokasi_ruangan" name="lokasi_ruangan" required
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Status Kelaikan *</label>
            <select id="edit_status_alat" name="status_alat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Laik Pakai">Laik Pakai</option>
              <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
              <option value="Dalam Perbaikan">Dalam Perbaikan</option>
              <option value="Rusak Berat">Rusak Berat</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1">Tgl Kalibrasi Terakhir</label>
            <input type="date" id="edit_tgl_kalibrasi_terakhir" name="tgl_kalibrasi_terakhir"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Tgl Kalibrasi Berikutnya</label>
            <input type="date" id="edit_tgl_kalibrasi_berikutnya" name="tgl_kalibrasi_berikutnya"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Spesifikasi Teknis</label>
          <textarea id="edit_spesifikasi" name="spesifikasi" rows="3"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
          <button type="button" onclick="tutupModal('modalEdit')" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 3: INPUT KALIBRASI & UJI TEKNIS -->
  <div id="modalKalibrasi" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full p-6 relative">
      <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">⚡</span>
          Catat Hasil Kalibrasi & Uji Teknis
        </h3>
        <button type="button" onclick="tutupModal('modalKalibrasi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
      </div>

      <form method="POST" action="action_alat.php" class="space-y-4 text-xs">
        <input type="hidden" name="action" value="kalibrasi">
        <input type="hidden" id="kalibrasi_alat_id" name="alat_id">

        <div>
          <label class="block font-semibold mb-1">Nama Alat:</label>
          <input type="text" id="kalibrasi_nama_alat" readonly class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/80 font-bold">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1">Tanggal Pelaksanaan *</label>
            <input type="date" name="tanggal" required value="<?= date('Y-m-d'); ?>"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Jenis Kegiatan *</label>
            <select name="jenis_kegiatan" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Kalibrasi BPFK">Kalibrasi BPFK</option>
              <option value="Uji Keselamatan Listrik">Uji Keselamatan Listrik</option>
              <option value="Preventive Maintenance">Preventive Maintenance</option>
              <option value="Corrective Maintenance">Corrective Maintenance</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1">Nama Teknisi / Lembaga *</label>
            <input type="text" name="teknisi" required value="<?= htmlspecialchars($user_nama); ?>"
                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
          </div>

          <div>
            <label class="block font-semibold mb-1">Hasil Evaluasi *</label>
            <select name="hasil" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
              <option value="Laik Pakai / Lolos">Laik Pakai / Lolos</option>
              <option value="Perlu Perbaikan">Perlu Perbaikan</option>
              <option value="Tidak Laik">Tidak Laik</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Catatan Hasil Uji & Nilai Toleransi</label>
          <textarea name="catatan" rows="3" placeholder="Contoh: Arus bocor sasis 22 uA (Lolos). Resistansi ground 0.05 Ohm. Output defibrilator 200J akurasi 99.2%."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
          <button type="button" onclick="tutupModal('modalKalibrasi')" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-md shadow-emerald-600/30">Simpan Hasil Kalibrasi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 4: DETAIL ALAT & RESUME KESELAMATAN -->
  <div id="modalDetail" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-xl w-full p-6 relative">
      <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-4">
        <div>
          <span id="detail_kode" class="font-mono text-xs text-cyan-600 dark:text-cyan-400 font-bold block"></span>
          <h3 id="detail_nama" class="text-lg font-bold text-slate-900 dark:text-white"></h3>
        </div>
        <button type="button" onclick="tutupModal('modalDetail')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
      </div>

      <div class="space-y-3 text-xs">
        <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
          <div>
            <span class="text-slate-500">Merk / Tipe:</span>
            <p id="detail_merk_tipe" class="font-bold text-slate-800 dark:text-slate-200"></p>
          </div>
          <div>
            <span class="text-slate-500">Nomor Seri:</span>
            <p id="detail_sn" class="font-mono font-bold text-slate-800 dark:text-slate-200"></p>
          </div>
          <div>
            <span class="text-slate-500">Kategori:</span>
            <p id="detail_kategori" class="font-semibold"></p>
          </div>
          <div>
            <span class="text-slate-500">Lokasi Ruangan:</span>
            <p id="detail_ruangan" class="font-bold text-cyan-600"></p>
          </div>
          <div>
            <span class="text-slate-500">Status Kelaikan:</span>
            <p id="detail_status" class="font-bold"></p>
          </div>
          <div>
            <span class="text-slate-500">Tahun Pengadaan:</span>
            <p id="detail_tahun" class="font-medium"></p>
          </div>
        </div>

        <div class="p-3 rounded-xl bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200 dark:border-cyan-900/50">
          <span class="font-bold text-cyan-800 dark:text-cyan-300 block mb-1">Resume Pengujian Keselamatan Listrik:</span>
          <ul class="space-y-1 text-slate-600 dark:text-slate-300">
            <li>&bull; Tegangan Sumber: <span class="font-mono font-semibold" id="detail_daya"></span></li>
            <li>&bull; Standar Baku: IEC 60601-1 Medical Electrical Equipment</li>
            <li>&bull; Terakhir Dikalibrasi: <span id="detail_kalibrasi_lalu" class="font-semibold"></span></li>
            <li>&bull; Jatuh Tempo Kalibrasi: <span id="detail_kalibrasi_next" class="font-semibold text-emerald-600"></span></li>
          </ul>
        </div>

        <div>
          <span class="text-slate-500 font-semibold block mb-1">Spesifikasi & Catatan:</span>
          <p id="detail_spesifikasi" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 leading-relaxed"></p>
        </div>
      </div>

      <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
        <button type="button" onclick="tutupModal('modalDetail')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs">Tutup</button>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="no-print mt-12 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 py-8 text-xs text-slate-500 dark:text-slate-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-lg bg-cyan-600 text-white flex items-center justify-center font-bold text-xs">
          +
        </div>
        <span class="font-bold text-slate-700 dark:text-slate-300">SIM-ALKES &copy; <?= date('Y'); ?></span>
        <span>&bull; Instalasi Pemeliharaan Sarana Rumah Sakit (IPSRS)</span>
      </div>
      <div>
        Sistem Manajemen Inventaris & Kalibrasi Peralatan Elektromedik Rumah Sakit
      </div>
    </div>
  </footer>

  <!-- SCRIPT INTERAKTIF -->
  <script>
    // Tab Switching
    function switchTab(tabId) {
      const tabs = ['katalog', 'tabel', 'sop', 'riwayat'];
      tabs.forEach(t => {
        const content = document.getElementById('tabContent-' + t);
        const btn = document.getElementById('tabBtn-' + t);
        if (content && btn) {
          if (t === tabId) {
            content.classList.remove('hidden');
            btn.className = 'tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all bg-cyan-600 text-white shadow-sm shadow-cyan-600/30';
          } else {
            content.classList.add('hidden');
            btn.className = 'tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
          }
        }
      });
    }

    // Toggle Dark Mode
    function toggleTheme() {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.theme = isDark ? 'dark' : 'light';
    }

    // Filter Kategori
    let currentKategori = 'all';
    function filterKategori(kat, btnElem) {
      currentKategori = kat;
      document.querySelectorAll('.kat-btn').forEach(b => {
        b.className = 'kat-btn px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-medium';
      });
      btnElem.className = 'kat-btn px-3 py-1.5 rounded-lg bg-cyan-600 text-white font-medium';
      filterAlatCards();
    }

    // Filter Status
    function filterStatus() {
      filterAlatCards();
    }

    // Live Search
    function filterAlatLive() {
      filterAlatCards();
      filterTableLive();
    }

    function filterAlatCards() {
      const q = document.getElementById('liveSearchInput').value.toLowerCase().trim();
      const status = document.getElementById('filterStatusSelect').value;
      const cards = document.querySelectorAll('.alat-card');

      cards.forEach(card => {
        const nama = card.dataset.nama || '';
        const merk = card.dataset.merk || '';
        const kode = card.dataset.kode || '';
        const ruangan = card.dataset.ruangan || '';
        const kat = card.dataset.kategori || '';
        const st = card.dataset.status || '';

        const matchSearch = q === '' || nama.includes(q) || merk.includes(q) || kode.includes(q) || ruangan.includes(q);
        const matchKategori = (currentKategori === 'all') || (kat === currentKategori);
        const matchStatus = (status === 'all') || (st === status);

        if (matchSearch && matchKategori && matchStatus) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }

    function filterTableLive() {
      const q = document.getElementById('liveSearchInput').value.toLowerCase().trim();
      const table = document.getElementById('mainTableAlat');
      if (!table) return;
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (q === '' || text.includes(q)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Modal Handlers
    function bukaModalTambah() {
      document.getElementById('modalTambah').classList.remove('hidden');
    }

    function bukaModalEdit(alat) {
      document.getElementById('edit_id').value = alat.id;
      document.getElementById('edit_nama_alat').value = alat.nama_alat;
      document.getElementById('edit_kategori').value = alat.kategori;
      document.getElementById('edit_merk').value = alat.merk;
      document.getElementById('edit_tipe_model').value = alat.tipe_model;
      document.getElementById('edit_nomor_seri').value = alat.nomor_seri;
      document.getElementById('edit_tahun_pengadaan').value = alat.tahun_pengadaan;
      document.getElementById('edit_lokasi_ruangan').value = alat.lokasi_ruangan;
      document.getElementById('edit_status_alat').value = alat.status_alat;
      document.getElementById('edit_tgl_kalibrasi_terakhir').value = alat.tgl_kalibrasi_terakhir || '';
      document.getElementById('edit_tgl_kalibrasi_berikutnya').value = alat.tgl_kalibrasi_berikutnya || '';
      document.getElementById('edit_spesifikasi').value = alat.spesifikasi || '';

      document.getElementById('modalEdit').classList.remove('hidden');
    }

    function bukaModalKalibrasi(alatId, namaAlat) {
      document.getElementById('kalibrasi_alat_id').value = alatId;
      document.getElementById('kalibrasi_nama_alat').value = namaAlat;
      document.getElementById('modalKalibrasi').classList.remove('hidden');
    }

    function bukaModalDetail(alat) {
      document.getElementById('detail_kode').innerText = alat.kode_alat;
      document.getElementById('detail_nama').innerText = alat.nama_alat;
      document.getElementById('detail_merk_tipe').innerText = alat.merk + ' (' + alat.tipe_model + ')';
      document.getElementById('detail_sn').innerText = alat.nomor_seri;
      document.getElementById('detail_kategori').innerText = alat.kategori;
      document.getElementById('detail_ruangan').innerText = alat.lokasi_ruangan;
      document.getElementById('detail_status').innerText = alat.status_alat;
      document.getElementById('detail_tahun').innerText = 'Tahun ' + alat.tahun_pengadaan;
      document.getElementById('detail_daya').innerText = alat.daya_listrik;
      document.getElementById('detail_kalibrasi_lalu').innerText = alat.tgl_kalibrasi_terakhir || 'Belum ada data';
      document.getElementById('detail_kalibrasi_next').innerText = alat.tgl_kalibrasi_berikutnya || 'Perlu Kalibrasi BPFK';
      document.getElementById('detail_spesifikasi').innerText = alat.spesifikasi || 'Spesifikasi standar operasional pabrikan.';

      document.getElementById('modalDetail').classList.remove('hidden');
    }

    function tutupModal(id) {
      document.getElementById(id).classList.add('hidden');
    }

    // Tutup modal jika klik di luar box
    window.addEventListener('click', function(e) {
      ['modalTambah', 'modalEdit', 'modalKalibrasi', 'modalDetail'].forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) {
          modal.classList.add('hidden');
        }
      });
    });
  </script>
</body>
</html>
