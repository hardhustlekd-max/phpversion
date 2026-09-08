<?php
/**
 * Municipal Command Central Dashboard
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadge = Auth::badgeId();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

// Load dynamic system settings
$settings = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config'")->fetch();

// Fetch scoped statistics
$regQuery = "SELECT status, vehicle_category, sub_city FROM motorcycle_registrations WHERE 1=1";
$params = [];
if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
    $regQuery .= " AND hide_from_other_users = 0";
}
if ($userRole === 'clerk') {
    $regQuery .= " AND (LOWER(registered_by) = LOWER(?) OR registered_by = '')";
    $params[] = $userBadge;
}
$stmt = $pdo->prepare($regQuery);
$stmt->execute($params);
$allRegs = $stmt->fetchAll();

$totalRegs = count($allRegs);
$pendingCount = 0;
$approvedCount = 0;
$printedCount = 0;
$rejectedCount = 0;
$subcityCounts = [];

foreach ($allRegs as $r) {
    if ($r['status'] === 'pending_approval') $pendingCount++;
    elseif ($r['status'] === 'approved') $approvedCount++;
    elseif ($r['status'] === 'printed' || $r['status'] === 'ordered_print') $printedCount++;
    elseif ($r['status'] === 'rejected') $rejectedCount++;

    $sc = $r['sub_city'] ?: 'Central';
    $subcityCounts[$sc] = ($subcityCounts[$sc] ?? 0) + 1;
}

// Payment counts
$totalReceipts = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts")->fetchColumn();
$expiringCount = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

// Verification logs
$recentLogs = $pdo->query("SELECT * FROM verification_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
$totalVerifs = (int)$pdo->query("SELECT COUNT(*) FROM verification_logs")->fetchColumn();
?>

<!-- Dashboard Header / Welcome Banner -->
<div class="command-card p-6 mb-6 bg-gradient-to-r from-[#0B1E48] via-[#1E3A8A] to-[#0D2B5C] text-white">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-amber-400 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">security</span>
        <span><?= htmlspecialchars($settings['department'] ?? 'Traffic Management & Law Enforcement') ?></span>
      </div>
      <h2 class="text-xl md:text-2xl font-black text-white">
        <?= $isAmharic ? 'የትራፊክ ቁጥጥርና ፈቃድ ማዕከል' : 'Traffic Enforcement & Permit Central' ?>
      </h2>
      <p class="text-xs text-blue-200 mt-1 flex items-center gap-2">
        <span><?= htmlspecialchars($currentUser['fullName'] ?? $currentUser['name'] ?? 'System Officer') ?> (<?= htmlspecialchars($userRole ?? 'clerk') ?>)</span>
        <span>•</span>
        <span><?= htmlspecialchars($settings['sub_city_office'] ?? 'Belay Zeleke Sub-City Office') ?></span>
      </p>
    </div>

    <!-- Quick Instant Plate Inspector Input -->
    <div class="w-full md:w-80 bg-white/10 p-2.5 rounded-xl border border-white/20 backdrop-blur-sm">
      <label class="block text-[11px] font-bold text-amber-300 uppercase tracking-wider mb-1">
        <?= $isAmharic ? 'የሰሌዳ ፈጣን ፍተሻ (Instant Plate Lookup)' : 'Instant Plate / QR Lookup' ?>
      </label>
      <div class="flex gap-1.5">
        <input 
          type="text" 
          id="dashboardPlateSearch" 
          placeholder="e.g. 3-AA-98124"
          class="w-full px-3 py-1.5 text-xs text-white bg-white/10 rounded-lg border border-white/20 placeholder-blue-200 focus:outline-none focus:bg-white/20">
        <button 
          onclick="handleDashboardSearch()" 
          class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-900 font-black rounded-lg text-xs flex items-center justify-center cursor-pointer transition-colors shadow">
          <span class="material-symbols-outlined text-sm">search</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Primary Metric KPI Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  
  <!-- Total Registered -->
  <div class="command-card p-4 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
      <span class="material-symbols-outlined text-2xl">two_wheeler</span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
        <?= $isAmharic ? 'ጠቅላላ የተመዘገቡ' : 'Total Registered' ?>
      </div>
      <div class="text-2xl font-black text-slate-900 leading-tight mt-0.5"><?= $totalRegs ?></div>
      <div class="text-[10px] text-blue-600 font-medium mt-0.5">
        <?= $isAmharic ? 'ህጋዊ ባለይዞታዎች' : 'Active Motorcycle Database' ?>
      </div>
    </div>
  </div>

  <!-- Pending Approvals -->
  <div class="command-card p-4 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
      <span class="material-symbols-outlined text-2xl">pending_actions</span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
        <?= $isAmharic ? 'ውሳኔ የሚጠብቁ' : 'Pending Approval' ?>
      </div>
      <div class="text-2xl font-black text-amber-600 leading-tight mt-0.5"><?= $pendingCount ?></div>
      <div class="text-[10px] text-slate-500 font-medium mt-0.5">
        <?= $isAmharic ? 'የሰነድ ማረጋገጫ' : 'Awaiting Review' ?>
      </div>
    </div>
  </div>

  <!-- Approved Permits -->
  <div class="command-card p-4 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
      <span class="material-symbols-outlined text-2xl">verified</span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
        <?= $isAmharic ? 'የጸደቁ ፈቃዶች' : 'Approved Permits' ?>
      </div>
      <div class="text-2xl font-black text-emerald-600 leading-tight mt-0.5"><?= $approvedCount ?></div>
      <div class="text-[10px] text-emerald-700 font-medium mt-0.5">
        <?= $isAmharic ? 'የተረጋገጡ' : 'Valid Road Permits' ?>
      </div>
    </div>
  </div>

  <!-- PVC Printed / Issued -->
  <div class="command-card p-4 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
      <span class="material-symbols-outlined text-2xl">badge</span>
    </div>
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
        <?= $isAmharic ? 'የታተሙ PVC ካርዶች' : 'Printed PVC Cards' ?>
      </div>
      <div class="text-2xl font-black text-purple-700 leading-tight mt-0.5"><?= $printedCount ?></div>
      <div class="text-[10px] text-purple-600 font-medium mt-0.5">
        <?= $isAmharic ? 'ካርድ የተሰጣቸው' : 'Cards Issued' ?>
      </div>
    </div>
  </div>
</div>

<!-- Secondary KPIs: Roadside Patrol & Payment Expiration -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  
  <div class="command-card p-4 flex items-center justify-between">
    <div>
      <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        <?= $isAmharic ? 'የመንገድ ላይ ፍተሻዎች' : 'Roadside Inspections' ?>
      </div>
      <div class="text-xl font-black text-slate-800 mt-1"><?= $totalVerifs ?></div>
      <div class="text-[11px] text-slate-400 mt-0.5"><?= $isAmharic ? 'በኦፊሰሮች የተከናወነ' : 'Conducted by field officers' ?></div>
    </div>
    <span class="material-symbols-outlined text-3xl text-cyan-600">policy</span>
  </div>

  <div class="command-card p-4 flex items-center justify-between">
    <div>
      <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        <?= $isAmharic ? 'የክፍያ ደረሰኞች' : 'Payment Receipts' ?>
      </div>
      <div class="text-xl font-black text-slate-800 mt-1"><?= $totalReceipts ?></div>
      <div class="text-[11px] text-emerald-600 mt-0.5"><?= $isAmharic ? 'የከተማው ገቢ ፈንድ' : 'Municipal Revenue Fund' ?></div>
    </div>
    <span class="material-symbols-outlined text-3xl text-emerald-600">payments</span>
  </div>

  <div class="command-card p-4 flex items-center justify-between">
    <div>
      <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        <?= $isAmharic ? 'በቅርብ የሚያበቁ ክፍያዎች' : 'Expiring Within 30 Days' ?>
      </div>
      <div class="text-xl font-black text-amber-600 mt-1"><?= $expiringCount ?></div>
      <div class="text-[11px] text-amber-700 mt-0.5"><?= $isAmharic ? 'ማስጠንቀቂያ የሚገባቸው' : 'Renewal notification needed' ?></div>
    </div>
    <span class="material-symbols-outlined text-3xl text-amber-500">notification_important</span>
  </div>

</div>

<!-- Quick Action Shortcuts -->
<div class="mb-6">
  <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
    <?= $isAmharic ? 'ፈጣን የአሰራር ቁልፎች' : 'Quick Actions' ?>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    
    <a href="index.php?page=registration_form" class="command-card p-3 flex items-center gap-3 hover:border-blue-900 transition-colors">
      <div class="p-2 rounded-lg bg-emerald-100 text-emerald-800">
        <span class="material-symbols-outlined text-lg">person_add</span>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-800"><?= $isAmharic ? 'አዲስ ምዝገባ' : 'New Registration' ?></div>
        <div class="text-[10px] text-slate-400"><?= $isAmharic ? 'አባልና ተሽከርካሪ መመዝገብ' : 'Add member / motor' ?></div>
      </div>
    </a>

    <a href="index.php?page=scan_qr" class="command-card p-3 flex items-center gap-3 hover:border-blue-900 transition-colors">
      <div class="p-2 rounded-lg bg-cyan-100 text-cyan-800">
        <span class="material-symbols-outlined text-lg">qr_code_scanner</span>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-800"><?= $isAmharic ? 'ባርኮድ ስካን' : 'Scan QR Code' ?></div>
        <div class="text-[10px] text-slate-400"><?= $isAmharic ? 'የመንገድ ላይ ፍተሻ' : 'Roadside verify' ?></div>
      </div>
    </a>

    <a href="index.php?page=today_submissions" class="command-card p-3 flex items-center gap-3 hover:border-blue-900 transition-colors">
      <div class="p-2 rounded-lg bg-amber-100 text-amber-800">
        <span class="material-symbols-outlined text-lg">today</span>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-800"><?= $isAmharic ? 'የዛሬ ማመልከቻዎች' : "Today's Submissions" ?></div>
        <div class="text-[10px] text-slate-400"><?= $isAmharic ? 'የዛሬ አባላት ዝርዝር' : 'Review daily entries' ?></div>
      </div>
    </a>

    <a href="index.php?page=report_unregistered" class="command-card p-3 flex items-center gap-3 hover:border-blue-900 transition-colors">
      <div class="p-2 rounded-lg bg-rose-100 text-rose-800">
        <span class="material-symbols-outlined text-lg">policy</span>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-800"><?= $isAmharic ? 'ያልተመዘገበ ሪፖርት' : 'Report Illegal' ?></div>
        <div class="text-[10px] text-slate-400"><?= $isAmharic ? 'ህገወጥ ተሽከርካሪ ማሳወቅ' : 'Log patrol violation' ?></div>
      </div>
    </a>

  </div>
</div>

<!-- Subcity Breakdown and Recent Inspection Logs -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  
  <!-- Subcity Distribution -->
  <div class="command-card p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900 text-lg">pie_chart</span>
        <span><?= $isAmharic ? 'የክፍለ ከተሞች ስርጭት' : 'Sub-City Distribution' ?></span>
      </h3>
      <span class="text-xs text-slate-400"><?= count($subcityCounts) ?> <?= $isAmharic ? 'ክፍለ ከተሞች' : 'Zones' ?></span>
    </div>
    <div class="space-y-3">
      <?php foreach (BAHIR_DAR_SUBCITIES as $sc): 
        $count = $subcityCounts[$sc['en']] ?? 0;
        $pct = $totalRegs > 0 ? round(($count / $totalRegs) * 100) : 0;
      ?>
        <div>
          <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
            <span><?= $isAmharic ? $sc['am'] : $sc['en'] ?></span>
            <span class="font-bold text-slate-900"><?= $count ?> (<?= $pct ?>%)</span>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
            <div class="bg-blue-900 h-2 rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Recent Roadside Verification History -->
  <div class="command-card p-5 lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
        <span class="material-symbols-outlined text-purple-700 text-lg">history</span>
        <span><?= $isAmharic ? 'የቅርብ ጊዜ የመንገድ ላይ ፍተሻዎች' : 'Recent Roadside Inspections' ?></span>
      </h3>
      <a href="index.php?page=inspection_report" class="text-xs text-blue-900 hover:underline font-bold">
        <?= $isAmharic ? 'ሁሉንም እይ →' : 'View All →' ?>
      </a>
    </div>

    <?php if (empty($recentLogs)): ?>
      <div class="text-center py-8 text-xs text-slate-400">
        <?= $isAmharic ? 'እስካሁን የተመዘገበ የፍተሻ ታሪክ የለም።' : 'No roadside inspection logs recorded yet.' ?>
      </div>
    <?php else: ?>
      <div class="divide-y divide-slate-100">
        <?php foreach ($recentLogs as $log): ?>
          <div class="py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full <?= $log['verification_status'] === 'flagged' ? 'bg-red-500' : ($log['verification_status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') ?>"></span>
              <div>
                <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                  <span><?= htmlspecialchars($log['plate_number'] ?? 'N/A') ?></span>
                  <span class="text-[10px] font-normal text-slate-500">(<?= htmlspecialchars($log['full_name'] ?? 'Driver') ?>)</span>
                </div>
                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($log['location_name'] ?? 'Patrol Checkpoint') ?> • <?= htmlspecialchars($log['scanned_at'] ?? 'Recently') ?></div>
              </div>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= 
              ($log['verification_status'] ?? '') === 'flagged' ? 'bg-rose-100 text-rose-800' :
              (($log['verification_status'] ?? '') === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')
            ?>">
              <?= htmlspecialchars($log['verification_status'] ?? 'Verified') ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Lookup Result Modal -->
<div id="lookupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div class="bg-white rounded-xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
    <div class="p-4 bg-[#0B1E48] text-white flex justify-between items-center">
      <h3 class="text-sm font-bold flex items-center gap-2">
        <span class="material-symbols-outlined text-amber-400">verified_user</span>
        <span><?= $isAmharic ? 'የተሽከርካሪ ህጋዊ ፈቃድ ማረጋገጫ' : 'Motorcycle Verification Result' ?></span>
      </h3>
      <button onclick="document.getElementById('lookupModal').classList.add('hidden')" class="p-1 hover:bg-white/10 rounded cursor-pointer">&times;</button>
    </div>
    <div id="lookupModalBody" class="p-5"></div>
  </div>
</div>

<script>
  async function handleDashboardSearch() {
    const input = document.getElementById('dashboardPlateSearch');
    const term = input.value.trim();
    if (!term) {
      showToast('Please enter a plate number to look up', 'error');
      return;
    }

    try {
      const reg = await RoadsideScanner.lookupPlate(term);
      if (reg) {
        showLookupModal(reg);
      }
    } catch (e) {
      showToast('Failed to perform lookup', 'error');
    }
  }

  function showLookupModal(reg) {
    const modal = document.getElementById('lookupModal');
    const body = document.getElementById('lookupModalBody');
    const photo = reg.user_portrait_photo || reg.userPortraitPhoto || 'image/app/logo.png';
    const plate = reg.plate_number || reg.plateNumber;
    const name = reg.full_name || reg.fullName;
    const phone = reg.phone;
    const cat = reg.vehicle_category === 'electric' ? 'Electric Motorcycle' : 'Gas Motorcycle (< 110cc)';
    const status = reg.status;

    body.innerHTML = `
      <div class="flex gap-4 items-start">
        <img src="${photo}" class="w-24 h-28 object-cover rounded-lg border border-slate-200 shadow-sm" alt="Portrait">
        <div class="flex-1 space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-lg font-black text-blue-900 font-mono">${plate}</span>
            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase ${
              status === 'approved' || status === 'printed' ? 'bg-emerald-100 text-emerald-800' :
              (status === 'pending_approval' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')
            }">${status}</span>
          </div>
          <div class="text-sm font-bold text-slate-800">${name}</div>
          <div class="text-xs text-slate-500">Phone: ${phone}</div>
          <div class="text-xs text-slate-600">${cat}</div>
          <div class="text-xs text-slate-500">Sub-City: ${reg.sub_city || 'Central'}</div>
        </div>
      </div>

      <div class="mt-5 pt-4 border-t border-slate-200 flex gap-2 justify-end">
        <button onclick="PrintTemplates.printA4(${JSON.stringify(reg).replace(/"/g, '&quot;')})" class="px-3 py-1.5 rounded-lg bg-blue-900 text-white text-xs font-bold hover:bg-blue-800 cursor-pointer">
          Print A4
        </button>
        <button onclick="PrintTemplates.printPVC(${JSON.stringify(reg).replace(/"/g, '&quot;')})" class="px-3 py-1.5 rounded-lg bg-purple-800 text-white text-xs font-bold hover:bg-purple-700 cursor-pointer">
          Print PVC
        </button>
        <button onclick="document.getElementById('lookupModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 cursor-pointer">
          Close
        </button>
      </div>
    `;

    modal.classList.remove('hidden');
  }
</script>
