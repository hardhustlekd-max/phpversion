<?php
/**
 * System Settings & Printer Configuration
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$settings = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config' LIMIT 1")->fetch();
if (!$settings || !is_array($settings)) {
    $settings = [];
}
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200">
    <div class="flex items-center gap-2 text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">
      <span class="material-symbols-outlined text-sm">settings</span>
      <span><?= $isAmharic ? 'የሲስተም ቅንብሮች' : 'System Configuration' ?></span>
    </div>
    <h2 class="text-xl font-black text-slate-900">
      <?= $isAmharic ? 'የሲስተም፣ የፕሪንተርና የፈቃድ ቅንብሮች' : 'System & Hardware Settings' ?>
    </h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= $isAmharic ? 'የፕሪንተር አይነት፣ የካርድ መለኪያ፣ የቀን መቁጠሪያና የሰራተኞች እይታ ፈቃዶች' : 'Configure PVC card printers, CR80 stock types, Ethiopian calendar, and staff permissions.' ?>
    </p>
  </div>

  <form id="systemSettingsForm" class="space-y-6">
    
    <!-- Officer & Office Details -->
    <div class="command-card p-6 bg-white border border-slate-200 space-y-4">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900">badge</span>
        <span><?= $isAmharic ? 'የኦፊሰርና የቢሮ መረጃ' : 'Officer & Municipal Office Info' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Officer Name</label>
          <input type="text" id="officerName" value="<?= htmlspecialchars($settings['officer_name'] ?? '') ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department</label>
          <input type="text" id="deptName" value="<?= htmlspecialchars($settings['department'] ?? '') ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub-City Office Branch</label>
          <input type="text" id="subCityOffice" value="<?= htmlspecialchars($settings['sub_city_office'] ?? '') ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Calendar System</label>
          <select id="calendarSystem" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
            <option value="ethiopian" <?= ($settings['calendar_system'] ?? '') === 'ethiopian' ? 'selected' : '' ?>>የኢትዮጵያ ዘመን አቆጣጠር (Ethiopian Calendar GMT+3)</option>
            <option value="gregorian" <?= ($settings['calendar_system'] ?? '') === 'gregorian' ? 'selected' : '' ?>>Gregorian Calendar</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Hardware & PVC Card Printing -->
    <div class="command-card p-6 bg-white border border-slate-200 space-y-4">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-purple-700">print</span>
        <span><?= $isAmharic ? 'የፕሪንተርና የPVC ካርድ ቅንብር' : 'Hardware & PVC Card Printing' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Default PVC Card Printer</label>
          <select id="defaultPrinter" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
            <option value="Zebra ZD621 Industrial PVC Card Printer">Zebra ZD621 Industrial PVC Card Printer</option>
            <option value="Evolis Zenius Expert ID Card Printer">Evolis Zenius Expert ID Card Printer</option>
            <option value="HID Fargo DTC1250e Dual-Sided ID Printer">HID Fargo DTC1250e Dual-Sided ID Printer</option>
            <option value="Standard Laser / Inkjet A4 Document Printer">Standard Laser / Inkjet A4 Document Printer</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Card Stock Type</label>
          <select id="cardStockType" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
            <option value="CR80 Standard PVC Card (85.6 x 54 mm)">CR80 Standard PVC Card (85.6 x 54 mm)</option>
            <option value="A4 Heavyweight Security Paper (120gsm)">A4 Heavyweight Security Paper (120gsm)</option>
            <option value="Adhesive Vinyl Motorcycle Tank Decal">Adhesive Vinyl Motorcycle Tank Decal</option>
          </select>
        </div>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <input type="checkbox" id="autoPrintQr" <?= !empty($settings['auto_print_qr']) ? 'checked' : '' ?> class="rounded text-blue-900">
        <label for="autoPrintQr" class="text-xs font-medium text-slate-700 cursor-pointer">
          <?= $isAmharic ? 'አዲስ ምዝገባ ሲጠናቀቅ ወዲያውኑ የQR ባጅ አትም' : 'Automatically launch print dialog upon registration approval' ?>
        </label>
      </div>
    </div>

    <!-- Clerk Visibility Controls -->
    <div class="command-card p-6 bg-white border border-slate-200 space-y-4">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-amber-600">visibility</span>
        <span><?= $isAmharic ? 'የጸሐፊ (Clerk) የእይታ ፈቃዶች' : 'Clerk Role Visibility Governance' ?></span>
      </h3>

      <div class="space-y-3 text-xs">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="showClerkPermit" <?= !empty($settings['show_clerk_permit_status']) ? 'checked' : '' ?> class="rounded text-blue-900">
          <span class="font-medium text-slate-700">Allow Clerks to view overall permit approval status</span>
        </label>

        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="showClerkSubmissions" <?= !empty($settings['show_clerk_submissions_action']) ? 'checked' : '' ?> class="rounded text-blue-900">
          <span class="font-medium text-slate-700">Allow Clerks to see "Today's Submissions" shortcut</span>
        </label>

        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="showClerkApproved" <?= !empty($settings['show_clerk_approved_vehicles_action']) ? 'checked' : '' ?> class="rounded text-blue-900">
          <span class="font-medium text-slate-700">Allow Clerks to view Approved Vehicles Table</span>
        </label>

        <div class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Clerk Payment KPI Permission</label>
            <select id="clerkKpiPerm" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded bg-white">
              <option value="allow" <?= ($settings['clerk_payment_kpi_permission'] ?? '') === 'allow' ? 'selected' : '' ?>>Allow (ተፈቅዷል)</option>
              <option value="view_only" <?= ($settings['clerk_payment_kpi_permission'] ?? '') === 'view_only' ? 'selected' : '' ?>>View Only (እይታ ብቻ)</option>
              <option value="deny" <?= ($settings['clerk_payment_kpi_permission'] ?? '') === 'deny' ? 'selected' : '' ?>>Deny (ተከልክሏል)</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Clerk Payment Records Table Permission</label>
            <select id="clerkTablePerm" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded bg-white">
              <option value="allow" <?= ($settings['clerk_payment_table_permission'] ?? '') === 'allow' ? 'selected' : '' ?>>Allow (ተፈቅዷል)</option>
              <option value="view_only" <?= ($settings['clerk_payment_table_permission'] ?? '') === 'view_only' ? 'selected' : '' ?>>View Only (እይታ ብቻ)</option>
              <option value="deny" <?= ($settings['clerk_payment_table_permission'] ?? '') === 'deny' ? 'selected' : '' ?>>Deny (ተከልክሏል)</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <button type="submit" id="saveSettingsBtn" class="px-6 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow cursor-pointer">
        <span class="material-symbols-outlined text-sm">save</span>
        <span><?= $isAmharic ? 'ቅንብሮችን መዝግብ (Save Settings)' : 'Save Settings' ?></span>
      </button>
    </div>

  </form>

</div>

<script>
  document.getElementById('systemSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveSettingsBtn');
    btn.disabled = true;

    const payload = {
      officer_name: document.getElementById('officerName').value.trim(),
      department: document.getElementById('deptName').value.trim(),
      sub_city_office: document.getElementById('subCityOffice').value.trim(),
      calendar_system: document.getElementById('calendarSystem').value,
      default_printer: document.getElementById('defaultPrinter').value,
      card_stock_type: document.getElementById('cardStockType').value,
      auto_print_qr: document.getElementById('autoPrintQr').checked ? 1 : 0,
      show_clerk_permit_status: document.getElementById('showClerkPermit').checked ? 1 : 0,
      show_clerk_submissions_action: document.getElementById('showClerkSubmissions').checked ? 1 : 0,
      show_clerk_approved_vehicles_action: document.getElementById('showClerkApproved').checked ? 1 : 0,
      clerk_payment_kpi_permission: document.getElementById('clerkKpiPerm').value,
      clerk_payment_table_permission: document.getElementById('clerkTablePerm').value
    };

    try {
      const res = await AppAPI.post('ajax/settings.php?action=save', payload);
      if (res.success) {
        showToast('Settings saved successfully', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (err) {
      showToast(err.message || 'Failed to save settings', 'error');
      btn.disabled = false;
    }
  });
</script>
