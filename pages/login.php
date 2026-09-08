<?php
/**
 * Standalone Login Page
 * Faithfully matches the original login design with Ethiopian municipal identity
 * Works standalone, in subdirectories, and on shared hosting (InfinityFree, cPanel, XAMPP)
 */

// 1. Safe inclusion of Configuration and Auth
$baseDir = dirname(__DIR__);
if (file_exists($baseDir . '/config/config.php')) {
    require_once $baseDir . '/config/config.php';
} elseif (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} elseif (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

if (file_exists($baseDir . '/includes/auth.php')) {
    require_once $baseDir . '/includes/auth.php';
} elseif (file_exists(__DIR__ . '/../includes/auth.php')) {
    require_once __DIR__ . '/../includes/auth.php';
} elseif (file_exists(__DIR__ . '/includes/auth.php')) {
    require_once __DIR__ . '/includes/auth.php';
}

// 2. Safe Fallback Constants (prevents fatal error if config is missing or unreadable)
if (!defined('APP_NAME_AM')) define('APP_NAME_AM', 'ባህርዳር ሞተረኞች ማህበር');
if (!defined('APP_NAME_EN')) define('APP_NAME_EN', 'Bahir Dar Motorcyclists Association');
if (!defined('APP_TITLE')) define('APP_TITLE', 'Enforcement Pro - Command Central');

// Ensure Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language handler
if (isset($_GET['lang'])) {
    $_SESSION['app_lang'] = ($_GET['lang'] === 'en') ? 'en' : 'am';
}
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

// Check if already logged in -> redirect to dashboard
if (class_exists('Auth') && Auth::isLoggedIn()) {
    $target = file_exists(__DIR__ . '/../index.php') ? '../index.php?page=dashboard' : 'index.php?page=dashboard';
    header("Location: $target");
    exit;
}

// 3. Native Form POST Processing (Works even if AJAX / JavaScript fails or on restrictive hosts)
$postError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badge = trim($_POST['badge_id'] ?? $_POST['badgeId'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    if (empty($badge)) {
        $postError = $isAmharic ? 'እባክዎ የመለያ መታወቂያ / ባጅ ያስገቡ' : 'Badge ID or Email is required';
    } else {
        if (class_exists('Auth')) {
            $res = Auth::login($badge, $pass);
            if (!empty($res['success'])) {
                $target = file_exists(__DIR__ . '/../index.php') ? '../index.php?page=dashboard' : 'index.php?page=dashboard';
                header("Location: $target");
                exit;
            } else {
                $postError = $res['error'] ?? ($isAmharic ? 'የመግቢያ መረጃው ትክክል አይደለም' : 'Invalid Badge ID or Password');
            }
        } else {
            // Direct fallback session login for demo
            $_SESSION['user_id'] = 'user-' . $badge;
            $_SESSION['badge_id'] = $badge;
            $_SESSION['role'] = (strpos(strtoupper($badge), 'SUPER') !== false) ? 'superadmin' : ((strpos(strtoupper($badge), 'ADMIN') !== false) ? 'admin' : ((strpos(strtoupper($badge), 'OFFICER') !== false) ? 'officer' : 'clerk'));
            $_SESSION['full_name'] = $badge;
            $target = file_exists(__DIR__ . '/../index.php') ? '../index.php?page=dashboard' : 'index.php?page=dashboard';
            header("Location: $target");
            exit;
        }
    }
}

// Determine relative path for static assets
$assetPrefix = file_exists(__DIR__ . '/../assets') ? '../' : './';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="h-full bg-slate-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isAmharic ? 'መግቢያ' : 'Login' ?> — <?= htmlspecialchars(APP_NAME_AM) ?></title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abyssinica+SIL&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#1D61E7',
            'primary-dark': '#0B1E48',
          }
        }
      }
    }
  </script>
</head>
<body class="h-full flex flex-col justify-between bg-slate-100 text-slate-800 font-sans">

  <!-- Header Banner -->
  <header class="w-full bg-[#0B1E48] text-white py-3 px-4 shadow-md sticky top-0 z-50">
    <div class="max-w-5xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0 overflow-hidden border border-white/20">
          <img src="<?= $assetPrefix ?>public/logo.png" alt="Logo" class="w-full h-full object-cover" onerror="this.src='<?= $assetPrefix ?>public/flag.jpg'">
        </div>
        <div>
          <div class="text-sm font-black tracking-tight leading-tight">
            <?= $isAmharic ? 'ባህርዳር ሞተረኞች ማህበር' : 'Bahir Dar Motorcyclists Association' ?>
          </div>
          <div class="text-[11px] text-amber-300 font-medium leading-none">
            <?= $isAmharic ? 'የትራንስፖርት ፈቃድና ህግ ማስከበሪያ ሲስተም' : 'Traffic Enforcement & Permit Central' ?>
          </div>
        </div>
      </div>

      <!-- Lang toggle -->
      <a href="?lang=<?= $isAmharic ? 'en' : 'am' ?>" class="px-2.5 py-1 text-xs font-bold rounded bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center gap-1 cursor-pointer no-underline">
        <span class="material-symbols-outlined text-[16px]">translate</span>
        <span><?= $isAmharic ? 'English' : 'አማርኛ' ?></span>
      </a>
    </div>
  </header>

  <!-- Login Card Container -->
  <main class="flex-1 flex items-center justify-center p-4 my-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
      
      <!-- Card Banner -->
      <div class="bg-gradient-to-r from-[#0B1E48] to-[#1E3A8A] text-white p-6 text-center relative">
        <div class="w-16 h-16 bg-white p-1 rounded-full mx-auto shadow-md mb-3 flex items-center justify-center border-2 border-amber-400 overflow-hidden">
          <img src="<?= $assetPrefix ?>public/logo.png" alt="Logo" class="w-full h-full object-cover" onerror="this.src='<?= $assetPrefix ?>public/flag.jpg'">
        </div>
        <h2 class="text-lg font-black tracking-tight text-white">
          <?= $isAmharic ? 'የትራፊክ ማኔጅመንት ፖርታል' : 'Municipal Officer Portal' ?>
        </h2>
        <p class="text-xs text-blue-200 mt-1">
          <?= $isAmharic ? 'ደህንነቱ የተጠበቀ የመግቢያ በር' : 'Secure Law Enforcement Access' ?>
        </p>
      </div>

      <!-- Login Form (Supports both Native POST and AJAX) -->
      <form id="loginForm" method="POST" action="" class="p-6 space-y-4">
        
        <!-- Error Alert Box -->
        <div id="loginAlert" class="<?= empty($postError) ? 'hidden' : '' ?> p-3 rounded-lg text-xs font-medium bg-rose-50 text-rose-800 border border-rose-200">
          <?= htmlspecialchars($postError) ?>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የመለያ መታወቂያ / ባጅ (Badge ID)' : 'Badge ID or Email' ?>
          </label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg">badge</span>
            <input 
              type="text" 
              id="badge_id" 
              name="badge_id" 
              required
              value="<?= htmlspecialchars($_POST['badge_id'] ?? 'SUPER-ADMIN-01') ?>"
              placeholder="SUPER-ADMIN-01, ADMIN-001, OFFICER-442, CLERK-209"
              class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 focus:border-blue-900 outline-none bg-slate-50 focus:bg-white transition-all font-medium">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የይለፍ ቃል (Password)' : 'Password' ?>
          </label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg">lock</span>
            <input 
              type="password" 
              id="password" 
              name="password" 
              value="admin123"
              required
              placeholder="••••••••"
              class="w-full pl-9 pr-10 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 focus:border-blue-900 outline-none bg-slate-50 focus:bg-white transition-all font-medium">
            <button 
              type="button" 
              onclick="togglePasswordVisibility()"
              class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer">
              <span id="passIcon" class="material-symbols-outlined text-lg">visibility</span>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
          <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
            <input type="checkbox" name="remember" checked class="rounded text-blue-900 focus:ring-blue-900">
            <span><?= $isAmharic ? 'አስታውሰኝ (Remember Session)' : 'Remember Session' ?></span>
          </label>
        </div>

        <button 
          type="submit" 
          id="submitBtn"
          class="w-full bg-[#0B1E48] hover:bg-[#132A5E] active:scale-[0.99] text-white py-2.5 px-4 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md cursor-pointer">
          <span class="material-symbols-outlined text-lg text-amber-400">login</span>
          <span><?= $isAmharic ? 'ይግቡ (Sign In)' : 'Sign In' ?></span>
        </button>

        <!-- Quick Demo Credentials Selector -->
        <div class="pt-3 border-t border-slate-200">
          <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center mb-2">
            <?= $isAmharic ? 'ፈጣን የሙከራ አካውንቶች (Quick Demo Logins)' : 'Quick Demo Logins' ?>
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" onclick="fillCreds('SUPER-ADMIN-01', 'admin123')" class="p-2 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 text-left cursor-pointer transition-colors">
              <div class="font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                <span>Super Admin</span>
              </div>
              <div class="text-[10px] text-purple-700 font-mono mt-0.5">SUPER-ADMIN-01</div>
            </button>

            <button type="button" onclick="fillCreds('ADMIN-001', 'admin123')" class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 text-left cursor-pointer transition-colors">
              <div class="font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Manager / Admin</span>
              </div>
              <div class="text-[10px] text-blue-700 font-mono mt-0.5">ADMIN-001</div>
            </button>

            <button type="button" onclick="fillCreds('OFFICER-442', 'officer123')" class="p-2 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-900 border border-cyan-200 text-left cursor-pointer transition-colors">
              <div class="font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-cyan-600"></span>
                <span>Field Officer</span>
              </div>
              <div class="text-[10px] text-cyan-700 font-mono mt-0.5">OFFICER-442</div>
            </button>

            <button type="button" onclick="fillCreds('CLERK-209', 'clerk123')" class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-900 border border-rose-200 text-left cursor-pointer transition-colors">
              <div class="font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                <span>Clerk / Secretary</span>
              </div>
              <div class="text-[10px] text-rose-700 font-mono mt-0.5">CLERK-209</div>
            </button>
          </div>
        </div>

      </form>

      <div class="bg-slate-50 p-3 text-center border-t border-slate-200 text-[11px] text-slate-500">
        <?= htmlspecialchars(APP_NAME_AM) ?> • <?= date('Y') ?>
      </div>
    </div>
  </main>

  <script>
    function togglePasswordVisibility() {
      const pass = document.getElementById('password');
      const icon = document.getElementById('passIcon');
      if (pass.type === 'password') {
        pass.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        pass.type = 'password';
        icon.textContent = 'visibility';
      }
    }

    function fillCreds(badge, pass) {
      document.getElementById('badge_id').value = badge;
      document.getElementById('password').value = pass;
    }

    // Optional AJAX enhancement with seamless native fallback
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      const alertBox = document.getElementById('loginAlert');
      const btn = document.getElementById('submitBtn');
      const badge = document.getElementById('badge_id').value.trim();
      const pass = document.getElementById('password').value;

      // Determine correct endpoint relative to current location
      const isSubdir = window.location.pathname.includes('/pages/');
      const apiEndpoint = isSubdir ? '../ajax/auth.php?action=login' : 'ajax/auth.php?action=login';
      const redirectTarget = isSubdir ? '../index.php?page=dashboard' : 'index.php?page=dashboard';

      try {
        const res = await fetch(apiEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ badge_id: badge, password: pass })
        });

        if (res.ok) {
          const data = await res.json();
          if (data.success) {
            e.preventDefault();
            window.location.href = redirectTarget;
            return;
          } else if (data.error) {
            e.preventDefault();
            alertBox.textContent = data.error;
            alertBox.classList.remove('hidden');
            return;
          }
        }
      } catch (err) {
        // If AJAX fetch fails, let the browser submit normally via standard POST
        console.log('AJAX bypassed, falling back to standard HTTP POST submission');
      }
    });
  </script>
</body>
</html>
