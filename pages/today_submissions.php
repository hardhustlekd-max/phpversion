<?php
/**
 * Today's Submissions Workbench
 * Daily review queue, clerk corrections, and fast-track permit approvals
 */

$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadge = Auth::badgeId();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

// Fetch today's submissions (MySQL CURDATE() or created today)
$sql = "SELECT * FROM motorcycle_registrations WHERE DATE(created_at) = CURDATE()";
$params = [];

if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
    $sql .= " AND hide_from_other_users = 0";
}

if ($userRole === 'clerk') {
    $sql .= " AND (LOWER(registered_by) = LOWER(?) OR registered_by = '' OR registered_by IS NULL)";
    $params[] = $userBadge;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$todayRecords = $stmt->fetchAll();

// If no records created today, also show the latest pending records for review
if (empty($todayRecords)) {
    $fallbackSql = "SELECT * FROM motorcycle_registrations WHERE status = 'pending_approval'";
    if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
        $fallbackSql .= " AND hide_from_other_users = 0";
    }
    $fallbackSql .= " ORDER BY created_at DESC LIMIT 20";
    $todayRecords = $pdo->query($fallbackSql)->fetchAll();
    $isFallback = true;
} else {
    $isFallback = false;
}

$todayTotal = count($todayRecords);
$todayApproved = 0;
$todayPending = 0;
$todayPrinted = 0;

foreach ($todayRecords as $r) {
    if ($r['status'] === 'approved') $todayApproved++;
    elseif ($r['status'] === 'pending_approval') $todayPending++;
    elseif ($r['status'] === 'printed' || $r['status'] === 'ordered_print') $todayPrinted++;
}
?>

<!-- Header Banner -->
<div class="command-card p-5 mb-6 bg-white border border-slate-200">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">today</span>
        <span><?= $isAmharic ? 'የዛሬ ማመልከቻዎች የስራ ሰሌዳ' : "Today's Submissions Workbench" ?></span>
      </div>
      <h2 class="text-xl md:text-2xl font-black text-slate-900">
        <?= $isAmharic ? 'የዛሬ የተመዘገቡ ማመልከቻዎች' : "Daily Registration Queue" ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        <?= $isFallback ? ($isAmharic ? 'የዛሬ አዲስ ማመልከቻ እስኪገባ ድረስ በቅርቡ የተመዘገቡትን በመጠባበቅ ላይ ያሉ ማመልከቻዎች እያሳየ ነው' : 'Showing recent pending submissions awaiting verification') : ($isAmharic ? 'ዛሬ የተመዘገቡ አዳዲስ ሞተሮችና የተጠቃሚዎች መረጃ' : 'Applications logged today awaiting review and badge issuance') ?>
      </p>
    </div>

    <!-- Quick Stats Summary -->
    <div class="flex items-center gap-3">
      <div class="px-3 py-2 bg-blue-50 border border-blue-200 rounded-xl text-center">
        <div class="text-[10px] uppercase font-bold text-blue-800"><?= $isAmharic ? 'ጠቅላላ' : 'Total' ?></div>
        <div class="text-lg font-black text-blue-900 font-mono"><?= $todayTotal ?></div>
      </div>
      <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-center">
        <div class="text-[10px] uppercase font-bold text-amber-800"><?= $isAmharic ? 'በመጠባበቅ' : 'Pending' ?></div>
        <div class="text-lg font-black text-amber-900 font-mono"><?= $todayPending ?></div>
      </div>
      <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
        <div class="text-[10px] uppercase font-bold text-emerald-800"><?= $isAmharic ? 'የጸደቁ' : 'Approved' ?></div>
        <div class="text-lg font-black text-emerald-900 font-mono"><?= $todayApproved ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Submissions Queue List -->
<div class="command-card bg-white border border-slate-200 overflow-hidden shadow-xs">
  <div class="p-4 border-b border-slate-100 flex items-center justify-between">
    <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
      <span class="material-symbols-outlined text-blue-900 text-lg">pending_actions</span>
      <span><?= $isAmharic ? 'የማመልከቻዎች ዝርዝር (Submissions List)' : 'Pending Submissions Queue' ?></span>
    </h3>
    <a href="index.php?page=registration_form" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
      <span class="material-symbols-outlined text-sm">add</span>
      <span><?= $isAmharic ? 'አዲስ አስገባ' : 'Add New' ?></span>
    </a>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-black text-[11px]">
          <th class="py-3 px-4">#</th>
          <th class="py-3 px-4"><?= $isAmharic ? 'የሰሌዳ ቁጥር' : 'Plate Number' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'የባለቤቱ ስም' : 'Owner Name' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'ሞተርና ሻሲ' : 'Motor & Chassis' ?></th>
          <th class="py-3 px-4"><?= $isAmharic ? 'ክፍለ ከተማ' : 'Sub-City' ?></th>
          <th class="py-3 px-4 text-center"><?= $isAmharic ? 'ሁኔታ' : 'Status' ?></th>
          <th class="py-3 px-4 text-center"><?= $isAmharic ? 'የማረጋገጫ እርምጃ' : 'Review Action' ?></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($todayRecords)): ?>
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400">
              <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">task_alt</span>
              <div class="font-bold text-sm text-slate-600"><?= $isAmharic ? 'ዛሬ የተመዘገበ ማመልከቻ የለም' : 'No submissions recorded today' ?></div>
              <div class="text-xs text-slate-400 mt-1"><?= $isAmharic ? 'አዲስ አባል ለመመዝገብ ከላይ ያለውን ቁልፍ ይጫኑ' : 'Click "Add New" to register a motorcycle member' ?></div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($todayRecords as $i => $rec): ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="py-3 px-4 font-bold text-slate-400"><?= $i + 1 ?></td>
              
              <td class="py-3 px-4">
                <span class="px-2 py-1 bg-amber-50 text-slate-900 border border-amber-300 rounded font-mono font-black text-xs">
                  <?= htmlspecialchars($rec['plate_number'] ?? 'N/A') ?>
                </span>
              </td>

              <td class="py-3 px-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($rec['full_name'] ?? 'Unknown') ?></div>
                <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($rec['phone_number'] ?? 'N/A') ?></div>
              </td>

              <td class="py-3 px-4">
                <div class="font-semibold text-slate-800"><?= htmlspecialchars($rec['make'] ?? '') ?> <?= htmlspecialchars($rec['model'] ?? '') ?></div>
                <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($rec['chassis_number'] ?? 'N/A') ?></div>
              </td>

              <td class="py-3 px-4">
                <span class="font-semibold text-slate-700"><?= htmlspecialchars($rec['sub_city'] ?? 'Central') ?></span>
              </td>

              <td class="py-3 px-4 text-center">
                <?php if ($rec['status'] === 'approved'): ?>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                <?php elseif ($rec['status'] === 'printed'): ?>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Printed</span>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900">Pending Review</span>
                <?php endif; ?>
              </td>

              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <?php if ($rec['status'] !== 'approved'): ?>
                    <button 
                      type="button" 
                      onclick="updateStatus('<?= htmlspecialchars($rec['id']) ?>', 'approved')"
                      class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] flex items-center gap-1 shadow-xs cursor-pointer">
                      <span class="material-symbols-outlined text-xs">check</span>
                      <span><?= $isAmharic ? 'አጽድቅ' : 'Approve' ?></span>
                    </button>
                  <?php endif; ?>

                  <button 
                    type="button" 
                    onclick="updateStatus('<?= htmlspecialchars($rec['id']) ?>', 'rejected')"
                    class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-xs">close</span>
                    <span><?= $isAmharic ? 'ውድቅ' : 'Reject' ?></span>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function updateStatus(id, newStatus) {
  if (!confirm(`Are you sure you want to change status to "${newStatus}"?`)) return;
  try {
    const res = await fetch('ajax/registrations.php?action=update_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, status: newStatus })
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.error || 'Failed to update status');
    }
  } catch (err) {
    alert('Error connecting to server.');
  }
}
</script>
