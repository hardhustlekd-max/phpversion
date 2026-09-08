<?php
/**
 * Application Header & Topbar
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$currentUser = getCurrentUser();
$ethDate = toEthiopianDate();
?>
<!DOCTYPE html>
<html lang="am" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo defined('APP_NAME') ? APP_NAME : (defined('APP_NAME_AM') ? APP_NAME_AM : 'Enforcement Pro - Bahir Dar'); ?></title>
  <link rel="icon" type="image/png" href="public/logo.png" />
  <link rel="preload" href="public/AbyssinicaSIL-Regular.woff2" as="font" type="font/woff2" crossorigin />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abyssinica+SIL&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <!-- Google Material Symbols Outlined -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <!-- Tailwind CSS via CDN for full UI design fidelity -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#1D61E7',
            'primary-dark': '#0B1E48',
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
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">

  <!-- Deep Navy Municipal Top Header (Identical to Original React UI) -->
  <header class="bg-[#0B1E48] text-white border-b border-yellow-500/30 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4">
      
      <!-- Brand & Municipal Bureau Seal -->
      <a href="index.php#dashboard" class="flex items-center gap-3 text-white no-underline group cursor-pointer" onclick="window.App.navigateTo('dashboard')">
        <div class="relative w-10 h-10 rounded-lg overflow-hidden border border-yellow-400/40 shadow-sm shrink-0 bg-white/10 flex items-center justify-center">
          <img src="public/logo.png" alt="Bureau Seal" class="w-full h-full object-cover" onerror="this.src='public/flag.jpg'"/>
        </div>
        <div class="leading-tight">
          <div class="font-black text-sm sm:text-base text-white tracking-tight flex items-center gap-1.5">
            <span>የትራንስፖርት ፈቃድና ህግ ማስከበሪያ</span>
            <span class="text-[10px] bg-yellow-500/20 text-yellow-400 px-1.5 py-0.5 rounded font-extrabold border border-yellow-500/30">PRO</span>
          </div>
          <div class="text-[11px] text-slate-300 font-medium">
            Bahir Dar City Transport Bureau Enforcement Central
          </div>
        </div>
      </a>

      <!-- Ethiopian Calendar & Time Live Widget -->
      <div class="hidden md:flex items-center gap-2 bg-white/10 border border-yellow-500/30 rounded-lg px-3 py-1.5 text-xs text-white">
        <span class="material-symbols-outlined text-yellow-400 text-base">calendar_month</span>
        <span class="font-extrabold text-yellow-300"><?php echo $ethDate['formattedAm']; ?></span>
        <span class="text-white/40">|</span>
        <span class="material-symbols-outlined text-amber-400 text-sm">schedule</span>
        <span class="font-mono text-amber-200"><?php echo $ethDate['timeAm']; ?></span>
        <span class="text-[10px] bg-yellow-500/20 text-yellow-300 px-1.5 py-0.5 rounded font-bold ml-1">GMT+3</span>
      </div>

      <!-- Right Controls: Language, Notifications, User Dropdown -->
      <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        
        <!-- Language Switcher -->
        <button 
          type="button" 
          class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-bold text-white transition-all cursor-pointer"
          onclick="window.App.toggleLanguage()">
          <span class="material-symbols-outlined text-base">translate</span>
          <span id="lang-toggle-label">EN</span>
        </button>

        <!-- Notification Bell with Counter -->
        <button 
          type="button" 
          class="relative w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all cursor-pointer"
          title="Notifications">
          <span class="material-symbols-outlined text-lg">notifications</span>
          <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border-2 border-[#0B1E48]">
            5
          </span>
        </button>

        <!-- User Role Profile Pill -->
        <div class="relative user-menu">
          <button 
            type="button" 
            class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-all cursor-pointer"
            onclick="document.getElementById('user-dropdown-menu').classList.toggle('hidden')">
            <div class="w-6 h-6 rounded-full bg-yellow-500 text-[#0B1E48] font-black flex items-center justify-center text-xs shrink-0">
              <?php echo strtoupper(substr($currentUser['badgeId'] ?? 'U', 0, 2)); ?>
            </div>
            <div class="text-left hidden sm:block leading-tight">
              <div class="font-extrabold text-white text-xs"><?php echo htmlspecialchars($currentUser['badgeId'] ?? 'CLERK-001'); ?></div>
              <div class="text-[10px] text-yellow-400 capitalize"><?php echo htmlspecialchars($currentUser['role'] ?? 'Clerk'); ?></div>
            </div>
            <span class="material-symbols-outlined text-base text-slate-300">expand_more</span>
          </button>

          <!-- Dropdown Menu -->
          <div class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl py-2 z-50 text-slate-800 dark:text-white" id="user-dropdown-menu">
            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
              <div class="font-bold text-xs"><?php echo htmlspecialchars($currentUser['fullName'] ?? 'System Operator'); ?></div>
              <div class="text-[11px] text-slate-500 capitalize"><?php echo htmlspecialchars($currentUser['role'] ?? 'Clerk'); ?></div>
            </div>
            <button class="w-full text-left px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 cursor-pointer" onclick="window.App.navigateTo('settings')">
              <span class="material-symbols-outlined text-base text-slate-400">settings</span>
              <span>ቅንብሮች (Settings)</span>
            </button>
            <button class="w-full text-left px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 cursor-pointer" onclick="window.App.navigateTo('superadmin')">
              <span class="material-symbols-outlined text-base text-purple-500">admin_panel_settings</span>
              <span>የበላይ አስተዳዳሪ (Super Admin)</span>
            </button>
            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
            <button class="w-full text-left px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer" onclick="window.App.logout()">
              <span class="material-symbols-outlined text-base text-rose-600">logout</span>
              <span>ውጣ (Logout)</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </header>
