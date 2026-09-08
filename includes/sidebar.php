<?php
/**
 * Application Desktop Sidebar Navigation
 * 100% Identical to the Original React App Desktop Sidebar
 */
$currentPage = $_GET['page'] ?? 'dashboard';
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');
$userRole = Auth::role();
$userBadgeId = Auth::badgeId();
$currentUser = Auth::user();
$isSuperAdmin = ($userRole === 'superadmin' || $userRole === 'super_admin');
$isAdmin = ($userRole === 'admin');
$isClerk = ($userRole === 'clerk');
$isOfficer = ($userRole === 'officer');
?>
<!-- DESKTOP SIDEBAR NAVIGATION (hidden md:flex) -->
<aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 md:z-50 md:bg-[#0A1838] md:text-white md:border-r md:border-[#16274E] md:p-3.5 md:justify-between md:shadow-2xl select-none">
  <div class="flex flex-col h-full min-h-0">
    
    <!-- Desktop Brand & Logo Header -->
    <a href="index.php?page=dashboard" class="flex items-center gap-3 px-1 py-1 mb-5 cursor-pointer hover:opacity-90 transition-opacity no-underline text-white">
      <div class="w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center shrink-0 overflow-hidden border border-white/20">
        <img src="public/logo.png" alt="Logo" class="w-full h-full object-cover rounded-full" onerror="this.src='public/flag.jpg'" />
      </div>
      <div class="min-w-0">
        <h1 class="font-black text-xs text-white tracking-tight leading-tight truncate">
          <?= $isAmharic ? 'ባህርዳር ሞተረኞች ማህበር' : 'BAHIR DAR MOTORCYCLISTS ASSOCIATION' ?>
        </h1>
      </div>
    </a>

    <!-- Desktop Main Menu Items Navigation List -->
    <nav class="flex-1 overflow-y-auto space-y-1 pr-1 scrollbar-thin scrollbar-thumb-white/20">
      
      <!-- Dashboard Link -->
      <a href="index.php?page=dashboard" 
         class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'dashboard' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
        <span class="material-symbols-outlined text-[20px]">space_dashboard</span>
        <span><?= $isAmharic ? 'ዋና ገፅ' : 'Dashboard' ?></span>
      </a>

      <?php if ($isClerk): ?>
        <!-- CLERK SPECIFIC SIDE MENU -->
        <!-- 1. New Registration -->
        <a href="index.php?page=registration_form" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'registration_form' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
          <span><?= $isAmharic ? 'አዲስ ምዝገባ' : 'New Registration' ?></span>
        </a>

        <!-- 2. Submission Correction -->
        <a href="index.php?page=today_submissions" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'today_submissions' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">edit_note</span>
          <span><?= $isAmharic ? 'ማመልከቻ ማስተካከያ' : 'Submission Correction' ?></span>
        </a>

        <!-- 3. Scan QR Code -->
        <a href="index.php?page=scan_qr" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'scan_qr' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
          <span><?= $isAmharic ? 'ኮውአር ኮድ ፈትሽ' : 'Scan QR Code' ?></span>
        </a>

        <!-- 4. Payment Receipts Entry -->
        <a href="index.php?page=payment_receipts" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'payment_receipts' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px] text-emerald-400">receipt_long</span>
          <span><?= $isAmharic ? 'የክፍያ ደረሰኞች' : 'Payment Receipts' ?></span>
        </a>

        <!-- 5. View Submissions -->
        <a href="index.php?page=records_tables" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'records_tables' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">folder_open</span>
          <span><?= $isAmharic ? 'የቀረቡ ማመልከቻዎች' : 'View Submissions' ?></span>
        </a>

      <?php else: ?>
        <!-- NON-CLERK ROLES (ADMIN, OFFICER, SUPERADMIN) -->
        <?php if (!$isOfficer): ?>
          <!-- Registration Link -->
          <a href="index.php?page=registration_form" 
             class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'registration_form' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
            <span><?= $isAmharic ? 'አዲስ ምዝገባ' : 'New Registration' ?></span>
          </a>

          <!-- Payment Receipts Link -->
          <a href="index.php?page=payment_receipts" 
             class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'payment_receipts' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[20px] text-emerald-400">receipt_long</span>
            <span><?= $isAmharic ? 'የክፍያ ደረሰኞች' : 'Payment Receipts' ?></span>
          </a>

          <!-- Records Table Link -->
          <a href="index.php?page=records_tables" 
             class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'records_tables' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[20px]">table_chart</span>
            <span><?= $isAmharic ? 'የአባላት መረጃዎች ማህደር' : 'Records & Tables' ?></span>
          </a>
        <?php endif; ?>

        <!-- Inspection Report Link -->
        <a href="index.php?page=inspection_report" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'inspection_report' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">analytics</span>
          <span><?= $isAmharic ? 'የፍተሻ ሪፖርት' : 'Inspection Report' ?></span>
        </a>

        <!-- Scan QR Scanner Link -->
        <a href="index.php?page=scan_qr" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'scan_qr' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
          <span><?= $isAmharic ? 'ኮውአር ኮድ ፈትሽ' : 'Scan QR Code' ?></span>
        </a>

        <!-- Report Unregistered Vehicle Link -->
        <a href="index.php?page=report_unregistered" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'report_unregistered' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px] text-amber-400">report_problem</span>
          <span><?= $isAmharic ? 'ባልተመዘገበ ተሽከርካሪ ሪፖርት' : 'Report Unregistered Vehicle' ?></span>
        </a>

        <!-- Unlawful Motors Link (Manager & Super Admin) -->
        <?php if ($isAdmin || $isSuperAdmin): ?>
          <a href="index.php?page=unregistered_list" 
             class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'unregistered_list' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[20px] text-red-400">no_drinks</span>
            <span><?= $isAmharic ? 'የህገወጥ ሞተሮች ማህደር' : 'Unregistered Motors Registry' ?></span>
          </a>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Super Admin Dedicated Governance Links (SUPER ADMIN ONLY) -->
      <?php if ($isSuperAdmin): ?>
        <div class="pt-2 mt-2 border-t border-white/15 space-y-1">
          <p class="text-[10px] font-black text-[#60A5FA] uppercase tracking-wider px-3 mb-1 flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
            <span><?= $isAmharic ? 'ዋና አስተዳዳሪ' : 'Super Admin' ?></span>
          </p>

          <!-- 1. System Users & Roles -->
          <a href="index.php?page=superadmin_users" 
             class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'superadmin_users' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
            <span><?= $isAmharic ? 'ሚና እና ፈቃድ' : 'Roles & Permissions' ?></span>
          </a>

          <!-- 2. Sub-City Governance -->
          <a href="index.php?page=superadmin_subcities" 
             class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'superadmin_subcities' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[18px]">location_city</span>
            <span><?= $isAmharic ? 'የክፍለ ከተማ ቁጥጥር' : 'Sub-City Governance' ?></span>
          </a>

          <!-- 3. Security Audit -->
          <a href="index.php?page=superadmin_security" 
             class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'superadmin_security' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[18px]">shield</span>
            <span><?= $isAmharic ? 'የሴኪዩሪቲ ኦዲት' : 'Security & Audit' ?></span>
          </a>

          <!-- 4. System Maintenance & DB -->
          <a href="index.php?page=superadmin_maintenance" 
             class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'superadmin_maintenance' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
            <span class="material-symbols-outlined text-[18px]">database</span>
            <span><?= $isAmharic ? 'የሲስተም ጥገና' : 'System Maintenance' ?></span>
          </a>
        </div>
      <?php endif; ?>

      <!-- Universal Settings Page Link (For All Roles) -->
      <div class="pt-2 mt-2 border-t border-white/15">
        <a href="index.php?page=settings" 
           class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-md text-xs font-bold transition-all no-underline <?= $currentPage === 'settings' ? 'bg-[#1D61E7] text-white font-black shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
          <span class="material-symbols-outlined text-[20px]">settings</span>
          <span><?= $isAmharic ? 'ቅንብሮች' : 'Settings' ?></span>
        </a>
      </div>
    </nav>

    <!-- Sidebar Bottom Profile Card & Logout (Matching Exact Original Structure) -->
    <div class="pt-3 border-t border-[#1E3466] space-y-2 mt-auto">
      <div class="bg-[#112248] border border-[#1E3466] rounded-md p-2.5 flex items-center gap-2.5 text-white shadow-xs">
        <div class="w-9 h-9 rounded-full bg-slate-200 border-2 border-amber-400 shrink-0 overflow-hidden shadow-xs flex items-center justify-center">
          <img src="public/logo.png" alt="User Avatar" class="w-full h-full object-cover" onerror="this.src='public/flag.jpg'" />
        </div>
        <div class="min-w-0 flex-1">
          <span class="text-xs font-black text-white block truncate leading-tight">
            <?= htmlspecialchars($userBadgeId ?: ($isAmharic ? 'አቶ መፈሪያ' : 'Mr. Meferiya')) ?>
          </span>
          <span class="text-[10px] text-slate-300 font-medium block truncate">
            <?= $isSuperAdmin ? 'Super Admin' : ($isAdmin ? 'Manager' : ($isClerk ? 'Secretary' : 'Officer')) ?>
          </span>
          <div class="flex items-center gap-1 mt-0.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-[10px] text-emerald-400 font-bold">Online</span>
          </div>
        </div>
      </div>

      <!-- Bottom Non-Red Logout Button -->
      <a href="ajax/auth_actions.php?action=logout" 
         class="w-full bg-[#132A5E] hover:bg-[#1A387C] text-white border border-[#2A4E9B] font-extrabold text-xs py-2.5 rounded-md flex items-center justify-center gap-2 shadow-sm transition-all no-underline active:scale-98">
        <span class="material-symbols-outlined text-[18px] text-amber-400">logout</span>
        <span><?= $isAmharic ? 'ወጣ (Logout)' : 'Logout' ?></span>
      </a>
    </div>
  </div>
</aside>
