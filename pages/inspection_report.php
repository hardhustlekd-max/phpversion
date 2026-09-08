<?php
/**
 * Roadside Officer Verification Logs & Inspection History
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$statusFilter = $_GET['status'] ?? 'all';
$query = "SELECT * FROM verification_logs WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND verification_status = ?";
    $params[] = $statusFilter;
}
$query .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-purple-700 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">policy</span>
        <span><?= $isAmharic ? 'የመንገድ ላይ ፍተሻ ሪፖርት' : 'Roadside Inspection Logs' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'የትራፊክ ፍተሻዎችና የኦፊሰሮች ማህደር' : 'Traffic Enforcement & Officer Audit Logs' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5"><?= count($logs) ?> <?= $isAmharic ? 'የተመዘገቡ ፍተሻዎች' : 'Recorded roadside checks' ?></p>
    </div>

    <div class="flex items-center gap-2">
      <a href="index.php?page=scan_qr" class="px-3.5 py-2 bg-blue-900 text-white text-xs font-bold rounded-lg hover:bg-blue-800 transition-colors flex items-center gap-1.5 shadow-sm">
        <span class="material-symbols-outlined text-base">qr_code_scanner</span>
        <span><?= $isAmharic ? 'አዲስ ፍተሻ ጀምር' : 'Launch Scanner' ?></span>
      </a>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="flex gap-2">
    <a href="index.php?page=inspection_report&status=all" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors <?= $statusFilter === 'all' ? 'bg-blue-900 text-white' : 'bg-white border border-slate-200 text-slate-600' ?>">
      <?= $isAmharic ? 'ሁሉም (All)' : 'All Checks' ?>
    </a>
    <a href="index.php?page=inspection_report&status=verified" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors <?= $statusFilter === 'verified' ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 text-slate-600' ?>">
      <?= $isAmharic ? 'የተረጋገጡ (Verified)' : 'Verified' ?>
    </a>
    <a href="index.php?page=inspection_report&status=warning" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors <?= $statusFilter === 'warning' ? 'bg-amber-500 text-white' : 'bg-white border border-slate-200 text-slate-600' ?>">
      <?= $isAmharic ? 'ማስጠንቀቂያ (Warning)' : 'Warnings' ?>
    </a>
    <a href="index.php?page=inspection_report&status=flagged" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors <?= $statusFilter === 'flagged' ? 'bg-rose-600 text-white' : 'bg-white border border-slate-200 text-slate-600' ?>">
      <?= $isAmharic ? 'ህገወጥ / የታገደ (Flagged)' : 'Flagged' ?>
    </a>
  </div>

  <!-- Logs Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <?php if (empty($logs)): ?>
      <div class="text-center py-16">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">find_in_page</span>
        <div class="text-sm font-bold text-slate-700"><?= $isAmharic ? 'ምንም የፍተሻ መረጃ አልተገኘም።' : 'No inspection records found.' ?></div>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
            <tr>
              <th class="py-3 px-4"><?= $isAmharic ? 'የተፈተሸበት ቀንና ሰዓት' : 'Date & Time' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'የሰሌዳ ቁጥር' : 'Plate Number' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ባለቤት / አሽከርካሪ' : 'Driver Name' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ፍተሻ የተደረገበት ቦታ' : 'Checkpoint' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'የኦፊሰር ባጅ' : 'Officer Badge' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ውጤት' : 'Result' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ማስታወሻ' : 'Notes' ?></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($logs as $log): 
              $vStatus = $log['verification_status'];
            ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-4 font-mono text-slate-500"><?= htmlspecialchars($log['scanned_at']) ?></td>
                <td class="py-3 px-4 font-mono font-black text-blue-900"><?= htmlspecialchars($log['plate_number']) ?></td>
                <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($log['full_name']) ?></td>
                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($log['location_name'] ?? 'Patrol Checkpoint') ?></td>
                <td class="py-3 px-4 font-mono text-purple-700 font-bold"><?= htmlspecialchars($log['officer_badge_id']) ?></td>
                <td class="py-3 px-4">
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= 
                    $vStatus === 'verified' ? 'bg-emerald-100 text-emerald-800' :
                    ($vStatus === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')
                  ?>">
                    <?= htmlspecialchars($vStatus) ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-slate-500 italic"><?= htmlspecialchars($log['officer_notes'] ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
