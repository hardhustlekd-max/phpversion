<?php
/**
 * Navigation Bar Tabs Component (Identical to Original React App Navigation)
 */
?>
<nav class="bg-[#0B1E48]/95 border-b border-yellow-500/20 backdrop-blur-md px-4 sm:px-6 py-2 shadow-xs">
  <div class="max-w-7xl mx-auto flex items-center gap-1.5 overflow-x-auto scrollbar-none" id="main-nav-tabs">
    
    <!-- Dashboard -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer bg-yellow-500 text-[#0B1E48] font-black shadow-xs" 
      data-tab="dashboard" 
      onclick="window.App.navigateTo('dashboard')">
      <span class="material-symbols-outlined text-[16px]">space_dashboard</span>
      <span>ዳሽቦርድ (Dashboard)</span>
    </button>

    <!-- Forms / Registration -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="forms" 
      onclick="window.App.navigateTo('forms')">
      <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
      <span>አዲስ ምዝገባ (Register)</span>
    </button>

    <!-- Records / Tables -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="tables" 
      onclick="window.App.navigateTo('tables')">
      <span class="material-symbols-outlined text-[16px]">table_chart</span>
      <span>የሞተር ማህደር (Registry)</span>
    </button>

    <!-- Today Submissions -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="today_submissions" 
      onclick="window.App.navigateTo('today_submissions')">
      <span class="material-symbols-outlined text-[16px]">edit_note</span>
      <span>የዛሬ ምዝገባ (Today)</span>
    </button>

    <!-- QR Scanner -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="scan" 
      onclick="window.App.navigateTo('scan')">
      <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
      <span>ፈጣን ፍተሻ (Scan)</span>
    </button>

    <!-- Inspection Reports -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="inspection_report" 
      onclick="window.App.navigateTo('inspection_report')">
      <span class="material-symbols-outlined text-[16px]">analytics</span>
      <span>የፍተሻ ሪፖርት (Logs)</span>
    </button>

    <!-- Payment Receipts -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="payment_receipts" 
      onclick="window.App.navigateTo('payment_receipts')">
      <span class="material-symbols-outlined text-[16px]">receipt_long</span>
      <span>የክፍያ ደረሰኞች (Receipts)</span>
    </button>

    <!-- Violations -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15" 
      data-tab="unregistered_list" 
      onclick="window.App.navigateTo('unregistered_list')">
      <span class="material-symbols-outlined text-[16px]">no_drinks</span>
      <span>የታገዱ ዝርዝር (Violations)</span>
    </button>

    <!-- Super Admin -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-amber-300 hover:bg-white/15" 
      data-tab="superadmin" 
      onclick="window.App.navigateTo('superadmin')">
      <span class="material-symbols-outlined text-[16px]">admin_panel_settings</span>
      <span>የበላይ አስተዳዳሪ (Super Admin)</span>
    </button>

    <!-- Settings -->
    <button 
      class="nav-tab-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-white/90 hover:bg-white/15 ml-auto" 
      data-tab="settings" 
      onclick="window.App.navigateTo('settings')">
      <span class="material-symbols-outlined text-[16px]">settings</span>
      <span>ቅንብሮች (Settings)</span>
    </button>

  </div>
</nav>
