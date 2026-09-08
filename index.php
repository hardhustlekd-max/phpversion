<?php
/**
 * Enforcement Pro - Command Central
 * 100% Identical Master Application Shell
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ethiopian_calendar.php';

// Check if user is logged in
if (!Auth::isLoggedIn()) {
    header('Location: pages/login.php');
    exit;
}

$currentPage = $_GET['page'] ?? 'dashboard';
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadgeId = Auth::badgeId();
$ethDate = EthiopianCalendar::getToday();

// Map page titles and icons
$pageInfo = [
    'dashboard' => ['titleAm' => 'ዋና ገፅ', 'titleEn' => 'Dashboard', 'icon' => 'space_dashboard'],
    'registration_form' => ['titleAm' => 'አዲስ ምዝገባ', 'titleEn' => 'New Registration', 'icon' => 'how_to_reg'],
    'forms' => ['titleAm' => 'አዲስ ምዝገባ', 'titleEn' => 'New Registration', 'icon' => 'how_to_reg'],
    'today_submissions' => ['titleAm' => 'የዛሬ ማመልከቻዎች', 'titleEn' => "Today's Submissions", 'icon' => 'today'],
    'records_tables' => ['titleAm' => 'የአባላት መረጃዎች ማህደር', 'titleEn' => 'Records & Tables', 'icon' => 'table_chart'],
    'tables' => ['titleAm' => 'የአባላት መረጃዎች ማህደር', 'titleEn' => 'Records & Tables', 'icon' => 'table_chart'],
    'scan_qr' => ['titleAm' => 'ኮውአር ኮድ ፈትሽ', 'titleEn' => 'Scan QR Code', 'icon' => 'qr_code_scanner'],
    'scan' => ['titleAm' => 'ኮውአር ኮድ ፈትሽ', 'titleEn' => 'Scan QR Code', 'icon' => 'qr_code_scanner'],
    'inspection_report' => ['titleAm' => 'የፍተሻ ሪፖርት', 'titleEn' => 'Inspection Report', 'icon' => 'analytics'],
    'report_unregistered' => ['titleAm' => 'ባልተመዘገበ ተሽከርካሪ ሪፖርት', 'titleEn' => 'Report Unregistered Vehicle', 'icon' => 'report_problem'],
    'unregistered_list' => ['titleAm' => 'የህገወጥ ሞተሮች ማህደር', 'titleEn' => 'Unregistered Motors Registry', 'icon' => 'no_drinks'],
    'payment_receipts' => ['titleAm' => 'የክፍያ ደረሰኞች', 'titleEn' => 'Payment Receipts', 'icon' => 'receipt_long'],
    'superadmin_users' => ['titleAm' => 'ሚና እና ፈቃድ', 'titleEn' => 'Roles & Permissions', 'icon' => 'manage_accounts'],
    'superadmin_subcities' => ['titleAm' => 'የክፍለ ከተማ ቁጥጥር', 'titleEn' => 'Sub-City Governance', 'icon' => 'location_city'],
    'superadmin_security' => ['titleAm' => 'የሴኪዩሪቲ ኦዲት', 'titleEn' => 'Security & Audit Logs', 'icon' => 'shield'],
    'superadmin_maintenance' => ['titleAm' => 'የሲስተም ጥገና', 'titleEn' => 'System Maintenance', 'icon' => 'database'],
    'settings' => ['titleAm' => 'ቅንብሮች', 'titleEn' => 'Settings', 'icon' => 'settings'],
];

$activeInfo = $pageInfo[$currentPage] ?? $pageInfo['dashboard'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>የትራንስፖርት ፈቃድና ህግ ማስከበሪያ - Bahir Dar City Transport Bureau Enforcement</title>
  <link rel="icon" type="image/png" href="public/logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abyssinica+SIL&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <!-- Google Material Symbols Outlined -->
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
            surface: '#F8FAFC',
            'surface-container-lowest': '#FFFFFF',
            'surface-container-low': '#F8FAFC',
            'surface-container': '#F1F5F9',
            'surface-container-high': '#E2E8F0',
            'outline-variant': '#E2E8F0',
            'on-surface': '#0F172A',
            secondary: '#64748B',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body class="h-screen h-[100dvh] max-h-screen max-h-[100dvh] overflow-hidden bg-slate-50 text-slate-900 flex flex-col font-sans">

  <!-- Include Desktop Sidebar -->
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <!-- MAIN CONTAINER & TOP HEADER (md:pl-64) -->
  <div class="md:pl-64 flex-1 flex flex-col min-w-0 h-full max-h-full overflow-hidden">
    
    <!-- MOBILE NAVIGATION HEADER (md:hidden) -->
    <header class="sticky top-0 z-50 bg-[#0B1E48] text-white shadow-md px-3 sm:px-6 py-2.5 md:hidden shrink-0 relative overflow-hidden">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-4">
        
        <!-- Left Logo & Brand Title -->
        <a href="index.php?page=dashboard" class="flex items-center gap-2.5 min-w-0 shrink cursor-pointer no-underline text-white">
          <div class="w-9 h-9 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0 overflow-hidden">
            <img src="public/logo.png" alt="Logo" class="w-full h-full object-cover rounded-full" onerror="this.src='public/flag.jpg'"/>
          </div>
          <div class="min-w-0">
            <h1 class="font-extrabold text-xs sm:text-sm text-white tracking-tight leading-tight truncate">
              <?= $isAmharic ? 'ባህርዳር ሞተረኞች ማህበር' : 'BAHIR DAR MOTORCYCLISTS ASSOCIATION' ?>
            </h1>
          </div>
        </a>

        <!-- Right Mobile Menu Toggle Button -->
        <div class="flex items-center gap-2">
          <button type="button" 
                  onclick="document.getElementById('mobile-drawer').classList.toggle('hidden')"
                  class="p-2 rounded-md bg-white/10 hover:bg-white/20 text-white cursor-pointer">
            <span class="material-symbols-outlined text-[24px]">menu</span>
          </button>
        </div>
      </div>
    </header>

    <!-- MOBILE DRAWER DROPDOWN -->
    <div id="mobile-drawer" class="hidden md:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex flex-col">
      <div class="bg-[#0B1E48] text-white p-4 w-4/5 max-w-sm h-full flex flex-col justify-between shadow-2xl overflow-y-auto">
        <div class="space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/20">
            <span class="font-black text-sm"><?= $isAmharic ? 'ማውጫ' : 'Menu' ?></span>
            <button onclick="document.getElementById('mobile-drawer').classList.add('hidden')" class="p-1 rounded text-white/80 hover:text-white cursor-pointer">
              <span class="material-symbols-outlined text-xl">close</span>
            </button>
          </div>
          <div class="space-y-1">
            <a href="index.php?page=dashboard" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">space_dashboard</span>
              <span><?= $isAmharic ? 'ዋና ገፅ' : 'Dashboard' ?></span>
            </a>
            <a href="index.php?page=registration_form" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">how_to_reg</span>
              <span><?= $isAmharic ? 'አዲስ ምዝገባ' : 'New Registration' ?></span>
            </a>
            <a href="index.php?page=records_tables" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">table_chart</span>
              <span><?= $isAmharic ? 'የአባላት መረጃዎች ማህደር' : 'Records & Tables' ?></span>
            </a>
            <a href="index.php?page=scan_qr" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">qr_code_scanner</span>
              <span><?= $isAmharic ? 'ኮውአር ኮድ ፈትሽ' : 'Scan QR Code' ?></span>
            </a>
            <a href="index.php?page=inspection_report" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">analytics</span>
              <span><?= $isAmharic ? 'የፍተሻ ሪፖርት' : 'Inspection Report' ?></span>
            </a>
            <a href="index.php?page=payment_receipts" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-emerald-400">receipt_long</span>
              <span><?= $isAmharic ? 'የክፍያ ደረሰኞች' : 'Payment Receipts' ?></span>
            </a>
            <a href="index.php?page=settings" class="flex items-center gap-2.5 p-2.5 rounded-lg text-xs font-bold text-white no-underline hover:bg-white/10">
              <span class="material-symbols-outlined text-yellow-400">settings</span>
              <span><?= $isAmharic ? 'ቅንብሮች' : 'Settings' ?></span>
            </a>
          </div>
        </div>
        <div class="pt-4 border-t border-white/20">
          <a href="ajax/auth_actions.php?action=logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#132A5E] hover:bg-[#1A387C] text-white rounded-md text-xs font-bold no-underline">
            <span class="material-symbols-outlined text-amber-400">logout</span>
            <span><?= $isAmharic ? 'ውጣ (Sign Out)' : 'Sign Out' ?></span>
          </a>
        </div>
      </div>
    </div>

    <!-- DESKTOP TOP BAR (hidden md:flex) -->
    <header class="hidden md:flex items-center justify-between px-4 sm:px-6 py-3 bg-white text-slate-900 sticky top-0 z-40 border-b border-slate-200/80 shadow-2xs">
      
      <!-- Left Side: Active Page Title & Icon -->
      <div class="flex items-center gap-3">
        <h2 class="font-black text-base text-[#0B1E48] tracking-tight flex items-center gap-2.5">
          <span class="material-symbols-outlined text-[#1D61E7] text-[24px]">
            <?= $activeInfo['icon'] ?>
          </span>
          <span>
            <?= $isAmharic ? $activeInfo['titleAm'] : $activeInfo['titleEn'] ?>
          </span>
        </h2>
      </div>

      <!-- Right Side Tools: Notifications, Date Dropdown, User Profile -->
      <div class="flex items-center gap-2 sm:gap-2.5">
        
        <!-- Notification Bell with Count Badge 5 -->
        <div class="relative">
          <button type="button" 
                  class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200/90 flex items-center justify-center text-slate-700 transition-colors cursor-pointer"
                  title="Notifications">
            <span class="material-symbols-outlined text-[20px]">notifications</span>
            <span class="bg-rose-500 text-white text-[10px] font-black w-4.5 h-4.5 rounded-full flex items-center justify-center absolute -top-1 -right-1 border-2 border-white">
              5
            </span>
          </button>
        </div>

        <!-- Working Ethiopian Calendar Date & Time Pill -->
        <div class="relative">
          <div class="bg-slate-100 hover:bg-slate-200 border border-slate-200/90 text-slate-800 text-xs font-bold px-3 py-1.5 rounded-md flex items-center gap-2 cursor-pointer transition-all shadow-2xs select-none">
            <span class="material-symbols-outlined text-amber-600 text-[18px]">calendar_month</span>
            <span class="font-extrabold"><?= $isAmharic ? $ethDate['formattedAm'] : $ethDate['formattedEn'] ?></span>
            <span class="hidden lg:inline text-[10px] bg-amber-500/15 text-amber-800 font-mono px-1.5 py-0.5 rounded font-bold">
              <?= $isAmharic ? $ethDate['timeAm'] : $ethDate['timeEn'] ?>
            </span>
          </div>
        </div>

        <!-- User Role Profile Dropdown -->
        <div class="relative">
          <button type="button" 
                  onclick="document.getElementById('topbar-user-menu').classList.toggle('hidden')"
                  class="bg-slate-100 hover:bg-slate-200 border border-slate-200/90 text-slate-800 text-xs font-bold px-2.5 py-1 rounded-md flex items-center gap-2 cursor-pointer shadow-2xs">
            <div class="w-6 h-6 rounded-full bg-amber-400 text-[#0B1E48] font-black flex items-center justify-center text-[10px] shrink-0">
              <?= strtoupper(substr($userBadgeId ?: 'U', 0, 2)) ?>
            </div>
            <div class="text-left hidden lg:block leading-tight">
              <div class="font-extrabold text-slate-800 text-xs"><?= htmlspecialchars($userBadgeId ?: 'OPERATOR') ?></div>
              <div class="text-[10px] text-slate-500 uppercase"><?= htmlspecialchars($userRole) ?></div>
            </div>
            <span class="material-symbols-outlined text-slate-400 text-[16px]">expand_more</span>
          </button>

          <!-- Dropdown List -->
          <div id="topbar-user-menu" class="hidden absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-2xl py-2 z-50 text-slate-800">
            <div class="px-4 py-2 border-b border-slate-100">
              <div class="font-bold text-xs"><?= htmlspecialchars($currentUser['fullName'] ?? 'System Operator') ?></div>
              <div class="text-[11px] text-slate-500 capitalize"><?= htmlspecialchars($userRole) ?></div>
            </div>
            <a href="index.php?page=settings" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-50 flex items-center gap-2 no-underline text-slate-700">
              <span class="material-symbols-outlined text-slate-400 text-base">settings</span>
              <span><?= $isAmharic ? 'ቅንብሮች' : 'Settings' ?></span>
            </a>
            <?php if ($isSuperAdmin): ?>
            <a href="index.php?page=superadmin_users" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-50 flex items-center gap-2 no-underline text-purple-700">
              <span class="material-symbols-outlined text-purple-600 text-base">admin_panel_settings</span>
              <span><?= $isAmharic ? 'የበላይ አስተዳዳሪ' : 'Super Admin' ?></span>
            </a>
            <?php endif; ?>
            <div class="border-t border-slate-100 my-1"></div>
            <a href="ajax/auth_actions.php?action=logout" class="block px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 no-underline">
              <span class="material-symbols-outlined text-rose-600 text-base">logout</span>
              <span><?= $isAmharic ? 'ውጣ (Logout)' : 'Logout' ?></span>
            </a>
          </div>
        </div>

      </div>
    </header>

    <!-- BREADCRUMB NAVIGATION -->
    <nav aria-label="Breadcrumb" class="bg-slate-100/70 border-b border-slate-200 px-4 sm:px-6 py-2 text-xs text-slate-600 flex items-center gap-1.5 shrink-0">
      <a href="index.php?page=dashboard" class="flex items-center gap-1 text-slate-600 hover:text-blue-900 no-underline font-medium">
        <span class="material-symbols-outlined text-[16px]">space_dashboard</span>
        <span><?= $isAmharic ? 'ዋና ገፅ' : 'Dashboard' ?></span>
      </a>
      <?php if ($currentPage !== 'dashboard'): ?>
        <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
        <span class="font-bold text-blue-900 flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]"><?= $activeInfo['icon'] ?></span>
          <span><?= $isAmharic ? $activeInfo['titleAm'] : $activeInfo['titleEn'] ?></span>
        </span>
      <?php endif; ?>
    </nav>

    <!-- MAIN PAGE CONTENT VIEWPORT -->
    <main class="flex-1 overflow-y-auto w-full max-w-7xl md:max-w-[1600px] p-2.5 sm:p-3 md:p-6 pb-16 md:pb-8 space-y-3 md:space-y-6 mx-auto min-h-0" id="main-content">
      <?php
      // Dynamic page inclusion mapping
      $pageFiles = [
          'dashboard' => __DIR__ . '/pages/dashboard.php',
          'registration_form' => __DIR__ . '/pages/registration_form.php',
          'forms' => __DIR__ . '/pages/registration_form.php',
          'today_submissions' => __DIR__ . '/pages/today_submissions.php',
          'records_tables' => __DIR__ . '/pages/records_tables.php',
          'tables' => __DIR__ . '/pages/records_tables.php',
          'scan_qr' => __DIR__ . '/pages/scan_qr.php',
          'scan' => __DIR__ . '/pages/scan_qr.php',
          'inspection_report' => __DIR__ . '/pages/inspection_report.php',
          'report_unregistered' => __DIR__ . '/pages/report_unregistered.php',
          'unregistered_list' => __DIR__ . '/pages/unregistered_list.php',
          'payment_receipts' => __DIR__ . '/pages/payment_receipts.php',
          'superadmin_users' => __DIR__ . '/pages/superadmin_users.php',
          'superadmin_subcities' => __DIR__ . '/pages/superadmin_subcities.php',
          'superadmin_security' => __DIR__ . '/pages/superadmin_security.php',
          'superadmin_maintenance' => __DIR__ . '/pages/superadmin_maintenance.php',
          'settings' => __DIR__ . '/pages/settings.php',
      ];

      $fileToLoad = $pageFiles[$currentPage] ?? $pageFiles['dashboard'];
      if (file_exists($fileToLoad)) {
          require_once $fileToLoad;
      } else {
          echo "<div class='bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-500'>";
          echo "<div class='text-3xl mb-2'>🚧</div>";
          echo "<div class='font-bold text-slate-700'>ገጹ በመዘጋጀት ላይ ነው (Page under construction)</div>";
          echo "</div>";
      }
      ?>
    </main>
  </div>

  <!-- Scripts -->
  <script src="assets/js/ethiopianCalendar.js"></script>
  <script src="assets/js/qrcode.min.js"></script>
  <script src="assets/js/scanner.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
