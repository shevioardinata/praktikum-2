<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'koneksi.php';

$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'Teknisi Elektromedis');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($password)) {
        $error = 'Semua kolom bertanda bintang (*) wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal harus terdiri dari 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi kata sandi tidak cocok!';
    } elseif (!$terms) {
        $error = 'Anda harus menyetujui SOP Pemeliharaan & Keselamatan Alat Medis!';
    } else {
        // Cek duplikasi username atau email
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Username atau Email sudah terdaftar! Silakan gunakan yang lain.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $nama_lengkap, $username, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = 'Terjadi kesalahan sistem saat mendaftar: ' . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Akun - SIM-ALKES Alat Elektromedis Rumah Sakit</title>
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
    .glass-card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(12px);
    }
    .dark .glass-card {
      background: rgba(15, 23, 42, 0.95);
      border-color: rgba(51, 65, 85, 0.6);
    }
  </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-between selection:bg-cyan-500 selection:text-white">

  <!-- Header Nav -->
  <header class="w-full py-4 px-6 md:px-12 flex justify-between items-center border-b border-slate-200/80 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-20">
    <a href="index.php" class="flex items-center gap-3 group">
      <div class="w-10 h-10 rounded-xl bg-cyan-600 flex items-center justify-center text-white shadow-md shadow-cyan-600/30 group-hover:scale-105 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
      </div>
      <div>
        <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
          SIM-ALKES <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 font-semibold">ELEKTROMEDIS</span>
        </span>
        <p class="text-xs text-slate-500 dark:text-slate-400">Instalasi Pemeliharaan Sarana RS (IPSRS)</p>
      </div>
    </a>

    <div class="flex items-center gap-3">
      <span class="text-sm text-slate-500 dark:text-slate-400 hidden sm:inline">Sudah punya akun?</span>
      <a href="login.php" class="py-1.5 px-3.5 rounded-lg border border-cyan-600 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/30 font-medium text-xs transition-colors">
        Masuk (Login)
      </a>
    </div>
  </header>

  <!-- Konten Utama Register -->
  <main class="flex-1 flex items-center justify-center p-4 sm:p-6 md:p-10 relative overflow-hidden">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-cyan-400/10 dark:bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg">
      <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none p-6 sm:p-8 relative">
        
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-teal-600 text-white shadow-lg shadow-cyan-600/25 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <line x1="19" y1="8" x2="19" y2="14" />
              <line x1="22" y1="11" x2="16" y2="11" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Registrasi Personel</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Daftarkan akun teknisi atau petugas pengelola alat elektromedis
          </p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 text-sm flex items-start gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div><?= htmlspecialchars($error); ?></div>
          </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4">
          
          <div>
            <label for="nama_lengkap" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
              Nama Lengkap & Gelar *
            </label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required
                   value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                   placeholder="Contoh: Muhammad Ilham, S.Tr.TEM"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Username *
              </label>
              <input type="text" id="username" name="username" required
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                     placeholder="tanpa spasi (contoh: ilham_atem)"
                     class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
            </div>

            <div>
              <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Email Rumah Sakit *
              </label>
              <input type="email" id="email" name="email" required
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     placeholder="nama@rumahsakit.id"
                     class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
            </div>
          </div>

          <div>
            <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
              Peran / Jabatan Fungsional *
            </label>
            <select id="role" name="role" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
              <option value="Teknisi Elektromedis" selected>Teknisi Elektromedis (ATEM / Elektromedik)</option>
              <option value="Admin IPSRS">Admin IPSRS (Pengelola Sarana Medis)</option>
              <option value="Kepala Ruangan">Kepala Ruangan / Koordinator Unit Medis</option>
              <option value="Staff Medis">Staff Medis / Operator Alat</option>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Password *
              </label>
              <input type="password" id="password" name="password" required
                     placeholder="Minimal 6 karakter"
                     class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
            </div>

            <div>
              <label for="confirm_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Ulangi Password *
              </label>
              <input type="password" id="confirm_password" name="confirm_password" required
                     placeholder="Konfirmasi sandi"
                     class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all">
            </div>
          </div>

          <div class="pt-2">
            <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
              <span>Saya menyatakan bertindak sesuai standar operasional prosedur (SOP) keselamatan listrik alat kesehatan dan bertanggung jawab atas integritas data kalibrasi.</span>
            </label>
          </div>

          <button type="submit" 
                  class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white font-semibold text-sm shadow-md shadow-cyan-600/30 hover:shadow-cyan-600/50 transition-all flex items-center justify-center gap-2 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Daftarkan Akun Personel
          </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-600 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800 pt-4">
          Sudah memiliki akun terdaftar?
          <a href="login.php" class="font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
            Masuk di sini
          </a>
        </div>

      </div>
    </div>
  </main>

  <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 dark:border-slate-800">
    &copy; <?= date('Y'); ?> SIM-ALKES - Sistem Informasi Manajemen & Kalibrasi Alat Elektromedis
  </footer>

</body>
</html>
