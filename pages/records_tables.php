<?php
/**
 * Motorcycle Registrations & Member Records Archive
 * Complete searchable, filterable registry with QR inspection modals and export
 */

$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadge = Auth::badgeId();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

// Search & Filter Parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');
$subCityFilter = trim($_GET['subcity'] ?? 'all');
$categoryFilter = trim($_GET['category'] ?? 'all');

// Build Scoped SQL Query
$sql = "SELECT * FROM motorcycle_registrations WHERE 1=1";
$params = [];

// Superadmin vs Other roles visibility
if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
    $sql .= " AND hide_from_other_users = 0";
}

// Clerk scope (if applicable)
if ($userRole === 'clerk') {
    $sql .= " AND (LOWER(registered_by) = LOWER(?) OR registered_by = '' OR registered_by IS NULL)";
    $params[] = $userBadge;
}

// Status filter
if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

// Subcity filter
if ($subCityFilter !== 'all' && !empty($subCityFilter)) {
    $sql .= " AND sub_city = ?";
    $params[] = $subCityFilter;
}

// Category filter
if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
    $sql .= " AND vehicle_category = ?";
    $params[] = $categoryFilter;
}

// Search text
if (!empty($search)) {
    $sql .= " AND (plate_number LIKE ? OR full_name LIKE ? OR phone_number LIKE ? OR chassis_number LIKE ? OR motor_number LIKE ?)";
    $searchWild = "%$search%";
    $params = array_merge($params, [$searchWild, $searchWild, $searchWild, $searchWild, $searchWild]);
}

$sql .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Counts for quick filter pills
$countAll = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations" . ($userRole !== 'superadmin' ? " WHERE hide_from_other_users = 0" : ""))->fetchColumn();
$countApproved = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations WHERE status = 'approved'" . ($userRole !== 'superadmin' ? " AND hide_from_other_users = 0" : ""))->fetchColumn();
$countPending = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations WHERE status = 'pending_approval'" . ($userRole !== 'superadmin' ? " AND hide_from_other_users = 0" : ""))->fetchColumn();
$countPrinted = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations WHERE status IN ('printed', 'ordered_print')" . ($userRole !== 'superadmin' ? " AND hide_from_other_users = 0" : ""))->fetchColumn();
?>

<!-- Header & Quick Action Bar -->
<div class="command-card p-5 mb-6 bg-white border border-slate-200">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">table_chart</span>
        <span><?= $isAmharic ? 'የአባላትና የተሽከርካሪዎች ማህደር' : 'Registry Database' ?></span>
      </div>
      <h2 class="text-xl md:text-2xl font-black text-slate-900">
        <?= $isAmharic ? 'የተመዘገቡ ሞተረኞች ዝርዝር ማህደር' : 'Motorcycle Permit & Member Records' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        <?= $isAmharic ? 'የባህርዳር ከተማ ህጋዊ የይለፍ ፈቃድ የተሰጣቸው አጠቃላይ መረጃዎች' : 'Official municipal registry of verified motorcycle operators' ?>
      </p>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap items-center gap-2">
      <a href="index.php?page=registration_form" class="px-3.5 py-2 bg-[#0B1E48] hover:bg-[#132A5E] text-white text-xs font-bold rounded-lg flex items-center gap-1.5 shadow-xs transition-colors no-underline">
        <span class="material-symbols-outlined text-sm text-amber-400">add_circle</span>
        <span><?= $isAmharic ? 'አዲስ ምዝገባ' : 'New Registration' ?></span>
      </a>
      <button type="button" onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg border border-slate-200 flex items-center gap-1.5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-sm">print</span>
        <span><?= $isAmharic ? 'አትም (Print)' : 'Print List' ?></span>
      </button>
      <button type="button" onclick="exportTableToCSV('motor_records.csv')" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200 flex items-center gap-1.5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-sm text-emerald-600">download</span>
        <span><?= $isAmharic ? 'ኤክስፖርት (CSV)' : 'Export CSV' ?></span>
      </button>
    </div>
  </div>

  <!-- Status Filter Tabs -->
  <div class="flex flex-wrap gap-2 mt-5 pt-4 border-t border-slate-100 text-xs font-bold">
    <a href="index.php?page=records_tables&status=all" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'all' ? 'bg-[#0B1E48] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> no-underline transition-colors flex items-center gap-1.5">
      <span><?= $isAmharic ? 'ሁሉም (All)' : 'All Records' ?></span>
      <span class="px-1.5 py-0.5 rounded-full text-[10px] <?= $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-800' ?>"><?= $countAll ?></span>
    </a>
    <a href="index.php?page=records_tables&status=approved" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'approved' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' ?> no-underline transition-colors flex items-center gap-1.5">
      <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
      <span><?= $isAmharic ? 'የጸደቁ (Approved)' : 'Approved' ?></span>
      <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-200/60"><?= $countApproved ?></span>
    </a>
    <a href="index.php?page=records_tables&status=pending_approval" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'pending_approval' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-900 hover:bg-amber-100' ?> no-underline transition-colors flex items-center gap-1.5">
      <span class="w-2 h-2 rounded-full bg-amber-400"></span>
      <span><?= $isAmharic ? 'በመጠባበቅ ላይ (Pending)' : 'Pending' ?></span>
      <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-amber-200/60"><?= $countPending ?></span>
    </a>
    <a href="index.php?page=records_tables&status=printed" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'printed' ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-900 hover:bg-blue-100' ?> no-underline transition-colors flex items-center gap-1.5">
      <span class="w-2 h-2 rounded-full bg-blue-400"></span>
      <span><?= $isAmharic ? 'የታተሙ (Printed)' : 'Printed Badges' ?></span>
      <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-blue-200/60"><?= $countPrinted ?></span>
    </a>
  </div>
</div>

<!-- Search & Filter Controls -->
<div class="command-card p-4 mb-6 bg-white border border-slate-200">
  <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <input type="hidden" name="page" value="records_tables">
    <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">

    <!-- Search input -->
    <div class="relative">
      <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
      <input 
        type="text" 
        name="search" 
        value="<?= htmlspecialchars($search) ?>" 
        placeholder="<?= $isAmharic ? 'በሰሌዳ፣ በስም፣ በስልክ፣ በሻሲ...' : 'Plate, Owner Name, Chassis...' ?>"
        class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 focus:border-blue-900 outline-none">
    </div>

    <!-- Subcity dropdown -->
    <div>
      <select name="subcity" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 bg-white">
        <option value="all"><?= $isAmharic ? 'ሁሉም ክፍለ ከተሞች (All Sub-Cities)' : 'All Sub-Cities' ?></option>
        <?php foreach (BAHIR_DAR_SUBCITIES as $sc): ?>
          <option value="<?= htmlspecialchars($sc['en']) ?>" <?= $subCityFilter === $sc['en'] ? 'selected' : '' ?>>
            <?= $isAmharic ? htmlspecialchars($sc['am']) : htmlspecialchars($sc['en']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Category dropdown -->
    <div>
      <select name="category" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 bg-white">
        <option value="all"><?= $isAmharic ? 'ሁሉም ምድቦች (All Categories)' : 'All Categories' ?></option>
        <option value="Commercial" <?= $categoryFilter === 'Commercial' ? 'selected' : '' ?>><?= $isAmharic ? 'የንግድ / የትራንስፖርት' : 'Commercial Transport' ?></option>
        <option value="Private" <?= $categoryFilter === 'Private' ? 'selected' : '' ?>><?= $isAmharic ? 'የግል (Private)' : 'Private' ?></option>
        <option value="Association" <?= $categoryFilter === 'Association' ? 'selected' : '' ?>><?= $isAmharic ? 'የማህበር (Association)' : 'Association' ?></option>
      </select>
    </div>

    <!-- Submit & Reset Buttons -->
    <div class="flex items-center gap-2">
      <button type="submit" class="flex-1 bg-[#0B1E48] hover:bg-[#132A5E] text-white py-2 px-3 text-xs font-bold rounded-lg flex items-center justify-center gap-1 cursor-pointer">
        <span class="material-symbols-outlined text-sm">filter_alt</span>
        <span><?= $isAmharic ? 'አጣራ (Filter)' : 'Apply' ?></span>
      </button>
      <a href="index.php?page=records_tables" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg border border-slate-200 no-underline" title="Reset Filters">
        <span class="material-symbols-outlined text-sm">restart_alt</span>
      </a>
    </div>
  </form>
</div>

<!-- Main Records Table -->
<div class="command-card bg-white border border-slate-200 overflow-hidden shadow-xs">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse" id="recordsDataTable">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-black tracking-wider text-[11px]">
          <th class="py-3 px-4 w-10 text-center">#</th>
          <th class="py-3 px-4"><?= $isAmharic ? 'የሰሌዳ ቁጥር' : 'Plate Number' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'የባለቤቱ ስም / ስልክ' : 'Owner / Phone' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'የሞተር መረጃ' : 'Motorcycle Specs' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'ክፍለ ከተማ' : 'Sub-City' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'ምድብ' : 'Category' ?></th>
          <th class="py-3 px-4 text-center"><?= $isAmharic ? 'ሁኔታ' : 'Status' ?></th>
          <th class="py-3 px-4 text-center"><?= $isAmharic ? 'እርምጃዎች' : 'Actions' ?></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($records)): ?>
          <tr>
            <td colspan="8" class="py-12 text-center text-slate-400">
              <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
              <div class="font-bold text-sm text-slate-600"><?= $isAmharic ? 'ምንም መረጃ አልተገኘም' : 'No records found' ?></div>
              <div class="text-xs text-slate-400 mt-1"><?= $isAmharic ? 'የተለየ የፍለጋ ቃል በመጠቀም እንደገና ይሞክሩ' : 'Try adjusting your search or filters' ?></div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($records as $idx => $r): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 text-center font-bold text-slate-400"><?= $idx + 1 ?></td>
              
              <!-- Plate Number -->
              <td class="py-3 px-4 font-black text-slate-900">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-slate-900 border border-amber-300 rounded font-mono text-xs font-black shadow-2xs">
                  <span class="text-blue-900 text-[10px]">ET</span>
                  <span><?= htmlspecialchars($r['plate_number'] ?? 'N/A') ?></span>
                </div>
              </td>

              <!-- Owner Name & Phone -->
              <td class="py-3 px-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($r['full_name'] ?? 'Unknown') ?></div>
                <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($r['phone_number'] ?? 'N/A') ?></div>
              </td>

              <!-- Motorcycle Details -->
              <td class="py-3 px-4">
                <div class="font-bold text-slate-800"><?= htmlspecialchars($r['make'] ?? '') ?> <?= htmlspecialchars($r['model'] ?? '') ?></div>
                <div class="text-[10px] text-slate-500 font-mono">Chassis: <?= htmlspecialchars($r['chassis_number'] ?? 'N/A') ?></div>
              </td>

              <!-- Sub-City -->
              <td class="py-3 px-4">
                <span class="font-semibold text-slate-700"><?= htmlspecialchars($r['sub_city'] ?? 'Central') ?></span>
                <div class="text-[10px] text-slate-400">Kebele <?= htmlspecialchars($r['kebele'] ?? '01') ?></div>
              </td>

              <!-- Category -->
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                  <?= htmlspecialchars($r['vehicle_category'] ?? 'Commercial') ?>
                </span>
              </td>

              <!-- Status Badge -->
              <td class="py-3 px-4 text-center">
                <?php
                $st = $r['status'] ?? 'pending_approval';
                if ($st === 'approved'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    <span><?= $isAmharic ? 'የጸደቀ' : 'Approved' ?></span>
                  </span>
                <?php elseif ($st === 'printed' || $st === 'ordered_print'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    <span><?= $isAmharic ? 'የታተመ' : 'Printed' ?></span>
                  </span>
                <?php elseif ($st === 'rejected'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                    <span><?= $isAmharic ? 'ውድቅ የተደረገ' : 'Rejected' ?></span>
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    <span><?= $isAmharic ? 'በመጠባበቅ' : 'Pending' ?></span>
                  </span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button 
                    type="button" 
                    onclick='openRecordDetailsModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                    class="p-1.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 transition-colors cursor-pointer" 
                    title="View Details & QR">
                    <span class="material-symbols-outlined text-base">visibility</span>
                  </button>

                  <button 
                    type="button"
                    onclick='printPermitBadge(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                    class="p-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors cursor-pointer"
                    title="Print Permit Badge">
                    <span class="material-symbols-outlined text-base">badge</span>
                  </button>

                  <?php if ($userRole === 'superadmin' || $userRole === 'admin'): ?>
                    <button 
                      type="button" 
                      onclick="deleteRecord('<?= htmlspecialchars($r['id']) ?>')"
                      class="p-1.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors cursor-pointer" 
                      title="Delete Record">
                      <span class="material-symbols-outlined text-base">delete</span>
                    </button>
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

<!-- Modal: Record Inspection & QR Code Details -->
<div id="recordModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
    <div class="bg-[#0B1E48] text-white p-5 flex items-center justify-between rounded-t-2xl">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-amber-400 text-2xl">badge</span>
        <div>
          <h3 class="font-black text-base text-white"><?= $isAmharic ? 'የይለፍ ፈቃድ መረጃና የደህንነት ኮድ' : 'Permit Details & Security QR' ?></h3>
          <p class="text-[11px] text-blue-200" id="modalPlateSubtitle">Plate Number</p>
        </div>
      </div>
      <button onclick="closeRecordModal()" class="p-1 text-white/80 hover:text-white rounded-lg cursor-pointer">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <div class="p-6 space-y-6">
      <!-- QR Code & Quick Status Block -->
      <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
        <div id="modalQrContainer" class="w-36 h-36 bg-white p-2 border border-slate-300 rounded-xl shadow-xs flex items-center justify-center shrink-0"></div>
        <div class="space-y-2 text-center sm:text-left">
          <div class="text-xs font-bold text-slate-500 uppercase tracking-wider"><?= $isAmharic ? 'ኦፊሴላዊ የፈቃድ መለያ' : 'Official Permit Identifier' ?></div>
          <div class="text-lg font-black text-blue-900 font-mono" id="modalPermitId">ET-BD-9821</div>
          <div id="modalStatusBadge"></div>
          <div class="text-xs text-slate-500" id="modalRegDate">Registered Date</div>
        </div>
      </div>

      <!-- Detail Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'የባለቤቱ ሙሉ ስም' : 'Owner Full Name' ?></span>
          <span class="font-bold text-slate-800" id="modalFullName">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'ስልክ ቁጥር' : 'Phone Number' ?></span>
          <span class="font-bold text-slate-800 font-mono" id="modalPhone">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'ክፍለ ከተማ / ቀበሌ' : 'Sub-City / Kebele' ?></span>
          <span class="font-bold text-slate-800" id="modalSubCity">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'የሞተር ሞዴል' : 'Make & Model' ?></span>
          <span class="font-bold text-slate-800" id="modalMakeModel">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'የሻሲ ቁጥር' : 'Chassis Number' ?></span>
          <span class="font-bold text-slate-800 font-mono" id="modalChassis">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block mb-0.5"><?= $isAmharic ? 'የሞተር ቁጥር' : 'Engine Number' ?></span>
          <span class="font-bold text-slate-800 font-mono" id="modalEngine">-</span>
        </div>
      </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="bg-slate-50 p-4 border-t border-slate-200 flex items-center justify-between rounded-b-2xl">
      <button type="button" onclick="closeRecordModal()" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs cursor-pointer">
        <?= $isAmharic ? 'ዝጋ (Close)' : 'Close' ?>
      </button>
      <div class="flex gap-2">
        <button type="button" id="modalPrintBtn" class="px-4 py-2 rounded-lg bg-[#0B1E48] hover:bg-[#132A5E] text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-sm">
          <span class="material-symbols-outlined text-sm text-amber-400">print</span>
          <span><?= $isAmharic ? 'ባጅ አትም' : 'Print Badge' ?></span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentSelectedRecord = null;

function openRecordDetailsModal(data) {
  currentSelectedRecord = data;
  document.getElementById('modalPlateSubtitle').textContent = 'Plate: ' + (data.plate_number || 'N/A');
  document.getElementById('modalPermitId').textContent = data.permit_number || data.plate_number || 'ET-BD-PERMIT';
  document.getElementById('modalFullName').textContent = data.full_name || 'N/A';
  document.getElementById('modalPhone').textContent = data.phone_number || 'N/A';
  document.getElementById('modalSubCity').textContent = (data.sub_city || 'Central') + ' (Kebele ' + (data.kebele || '01') + ')';
  document.getElementById('modalMakeModel').textContent = (data.make || '') + ' ' + (data.model || '');
  document.getElementById('modalChassis').textContent = data.chassis_number || 'N/A';
  document.getElementById('modalEngine').textContent = data.motor_number || 'N/A';
  document.getElementById('modalRegDate').textContent = 'Created: ' + (data.created_at || 'Recently');

  // Status Pill
  const statusContainer = document.getElementById('modalStatusBadge');
  const st = data.status || 'pending_approval';
  if (st === 'approved') {
    statusContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Approved Official Permit</span>';
  } else if (st === 'printed') {
    statusContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Badge Issued & Printed</span>';
  } else {
    statusContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">Pending Verification</span>';
  }

  // Generate QR Code
  const qrBox = document.getElementById('modalQrContainer');
  qrBox.innerHTML = '';
  if (window.QRCode) {
    new QRCode(qrBox, {
      text: JSON.stringify({
        plate: data.plate_number,
        owner: data.full_name,
        chassis: data.chassis_number,
        status: data.status,
        issued: data.created_at
      }),
      width: 120,
      height: 120
    });
  } else {
    qrBox.innerHTML = '<div class="text-xs text-slate-400 font-mono">QR: ' + (data.plate_number || '') + '</div>';
  }

  document.getElementById('modalPrintBtn').onclick = function() {
    printPermitBadge(data);
  };

  document.getElementById('recordModal').classList.remove('hidden');
}

function closeRecordModal() {
  document.getElementById('recordModal').classList.add('hidden');
}

function printPermitBadge(data) {
  const printWin = window.open('', '_blank', 'width=600,height=750');
  if (!printWin) return alert('Popups blocked. Please allow popups.');
  
  printWin.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Official Permit Badge - ${data.plate_number || 'BD'}</title>
      <style>
        body { font-family: sans-serif; background: #f8fafc; padding: 20px; text-align: center; }
        .badge-card { max-width: 380px; margin: 0 auto; background: white; border: 2px solid #0B1E48; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .badge-header { background: #0B1E48; color: white; padding: 14px; }
        .badge-body { padding: 20px; }
        .plate-box { background: #FEF3C7; border: 2px solid #F59E0B; padding: 6px 14px; font-size: 18px; font-weight: bold; border-radius: 8px; display: inline-block; margin: 10px 0; font-family: monospace; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        .footer { background: #f1f5f9; padding: 8px; font-size: 10px; color: #64748b; }
      </style>
    </head>
    <body>
      <div class="badge-card">
        <div class="badge-header">
          <h3 style="margin:0; font-size: 15px;">ባህርዳር ሞተረኞች ማህበር</h3>
          <p style="margin:2px 0 0 0; font-size: 11px; color: #93c5fd;">MUNICIPAL MOTORCYCLE PERMIT</p>
        </div>
        <div class="badge-body">
          <div class="plate-box">${data.plate_number || 'ET-BD-9821'}</div>
          <div class="row"><strong>Owner:</strong> <span>${data.full_name || 'N/A'}</span></div>
          <div class="row"><strong>Phone:</strong> <span>${data.phone_number || 'N/A'}</span></div>
          <div class="row"><strong>Sub-City:</strong> <span>${data.sub_city || 'Central'}</span></div>
          <div class="row"><strong>Vehicle:</strong> <span>${data.make || ''} ${data.model || ''}</span></div>
          <div class="row"><strong>Chassis:</strong> <span>${data.chassis_number || 'N/A'}</span></div>
          <div class="row"><strong>Status:</strong> <span style="color: #059669; font-weight: bold;">VERIFIED & ACTIVE</span></div>
        </div>
        <div class="footer">Bahir Dar City Transport Bureau • Enforcement Pro</div>
      </div>
      <script>
        window.onload = function() { window.print(); }
      <\/script>
    </body>
    </html>
  `);
  printWin.document.close();
}

async function deleteRecord(id) {
  if (!confirm('Are you sure you want to permanently remove this registration record?')) return;
  try {
    const res = await fetch('ajax/registrations.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.error || 'Failed to delete record');
    }
  } catch (err) {
    alert('Error connecting to server.');
  }
}

function exportTableToCSV(filename) {
  const table = document.getElementById('recordsDataTable');
  let csv = [];
  for (let r = 0; r < table.rows.length; r++) {
    let row = [], cols = table.rows[r].querySelectorAll('td, th');
    for (let c = 0; c < cols.length - 1; c++) {
      row.push('"' + cols[c].innerText.replace(/"/g, '""').trim() + '"');
    }
    csv.push(row.join(','));
  }
  const csvBlob = new Blob([csv.join('\n')], { type: 'text/csv' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(csvBlob);
  link.download = filename;
  link.click();
}
</script>
