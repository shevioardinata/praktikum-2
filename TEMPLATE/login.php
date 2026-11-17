<?php
session_start();

// Jika sudah login, langsung arahkan ke index.php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'koneksi.php';

$error = '';
$pesan_sukses = '';

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $pesan_sukses = 'Pendaftaran akun berhasil! Silakan masuk menggunakan username dan password Anda.';
}
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $pesan_sukses = 'Anda telah berhasil keluar (logout) dari sistem.';
}
if (isset($_GET['pesan']) && $_GET['pesan'] == 'wajib_login') {
    $error = 'Silakan login terlebih dahulu untuk mengakses fitur pengelolaan alat elektromedis.';
}

// Proses submit form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login_input) || empty($password)) {
        $error = 'Harap isi username/email dan password!';
    } else {
        // Cari user berdasarkan username ATAU email
        $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verifikasi password hash
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: index.php?login_success=1");
                exit;
            } else {
                $error = 'Password yang Anda masukkan salah!';
            }
        } else {
            $error = 'Username atau email tidak terdaftar!';
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
  <title>Login - SIM-ALKES Alat Elektromedis Rumah Sakit</title>
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
    .medical-gradient {
      background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #0f172a 100%);
    }
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

  <!-- Header Nav Minimal -->
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
      <a href="index.php" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Katalog Alat
      </a>
      <span class="text-slate-300 dark:text-slate-700">|</span>
      <a href="register.php" class="text-sm font-medium text-cyan-600 dark:text-cyan-400 hover:underline">
        Daftar Akun
      </a>
    </div>
  </header>

  <!-- Konten Utama Login -->
  <main class="flex-1 flex items-center justify-center p-4 sm:p-6 md:p-10 relative overflow-hidden">
    <!-- Ornamen Background Halus -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-cyan-400/10 dark:bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500/10 dark:bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md">
      
      <!-- Card Login -->
      <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none p-6 sm:p-8 relative">
        
        <!-- Header Card -->
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white shadow-lg shadow-cyan-600/25 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Masuk ke Sistem</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Portal Manajemen & Kalibrasi Alat Elektromedis
          </p>
        </div>

        <!-- Alert Notifikasi -->
        <?php if (!empty($error)): ?>
          <div class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 text-sm flex items-start gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div><?= htmlspecialchars($error); ?></div>
          </div>
        <?php endif; ?>

        <?php if (!empty($pesan_sukses)): ?>
          <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-sm flex items-start gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div><?= htmlspecialchars($pesan_sukses); ?></div>
          </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="login.php" class="space-y-4">
          <div>
            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
              Username atau Email
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </span>
              <input type="text" id="username" name="username" required autofocus
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                     placeholder="Contoh: admin atau teknisi"
                     class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent text-sm transition-all">
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Password
              </label>
            </div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
              </span>
              <input type="password" id="password" name="password" required
                     placeholder="Masukkan kata sandi Anda"
                     class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent text-sm transition-all">
              <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
              <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
              Ingat saya
            </label>
            <span class="text-slate-400">Standar Keamanan Rumah Sakit</span>
          </div>

          <button type="submit" 
                  class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-semibold text-sm shadow-md shadow-cyan-600/30 hover:shadow-cyan-600/50 transition-all flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Masuk ke Sistem
          </button>
        </form>

        <!-- Divider Akun Uji Coba (Demo Quick Fill) -->
        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800">
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2.5 text-center">
            Pilihan Cepat Akun Demo (Uji Coba):
          </p>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="isiAkunDemo('admin', 'admin123')"
                    class="py-2 px-3 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 hover:border-cyan-500 dark:hover:border-cyan-500 bg-slate-50 dark:bg-slate-800/60 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 text-slate-700 dark:text-slate-200 transition-colors flex items-center justify-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              Admin IPSRS
            </button>
            <button type="button" onclick="isiAkunDemo('teknisi', 'teknisi123')"
                    class="py-2 px-3 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 hover:border-cyan-500 dark:hover:border-cyan-500 bg-slate-50 dark:bg-slate-800/60 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 text-slate-700 dark:text-slate-200 transition-colors flex items-center justify-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
              Teknisi Elektromedik
            </button>
          </div>
        </div>

        <!-- Footer Link Registrasi -->
        <div class="mt-6 text-center text-xs text-slate-600 dark:text-slate-400">
          Belum memiliki akun terdaftar?
          <a href="register.php" class="font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
            Daftar Akun Baru
          </a>
        </div>

      </div>

      <!-- Info Tambahan Regulasi -->
      <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400 flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span>Standar Keselamatan Medis IEC 60601-1 & Permenkes No. 54/2015</span>
      </div>

    </div>
  </main>

  <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 dark:border-slate-800">
    &copy; <?= date('Y'); ?> SIM-ALKES - Sistem Informasi Manajemen & Kalibrasi Alat Elektromedis
  </footer>

  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
      } else {
        passwordInput.type = 'password';
      }
    }

    function isiAkunDemo(username, password) {
      document.getElementById('username').value = username;
      document.getElementById('password').value = password;
    }
  </script>
</body>
</html>
