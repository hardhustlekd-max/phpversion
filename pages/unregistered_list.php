<?php
/**
 * Unregistered Patrol Vehicles List & Case Resolution
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$reports = $pdo->query("SELECT * FROM unregistered_vehicle_reports ORDER BY created_at DESC")->fetchAll();
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-rose-600 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">policy</span>
        <span><?= $isAmharic ? 'የህገወጥ ተሽከርካሪዎች ማህደር' : 'Patrol Violation Registry' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'ያልተመዘገቡ ተሽከርካሪዎችና የጥሰት ጉዳዮች' : 'Unregistered Vehicles & Impound Cases' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5"><?= count($reports) ?> <?= $isAmharic ? 'የተመዘገቡ ጉዳዮች' : 'Reported patrol cases' ?></p>
    </div>

    <div class="flex items-center gap-2">
      <a href="index.php?page=report_unregistered" class="px-3.5 py-2 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition-colors flex items-center gap-1.5 shadow-sm">
        <span class="material-symbols-outlined text-base">add</span>
        <span><?= $isAmharic ? 'አዲስ ጥሰት መዝግብ' : 'Report Violation' ?></span>
      </a>
    </div>
  </div>

  <!-- Reports Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <?php if (empty($reports)): ?>
      <div class="text-center py-16">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">assignment_turned_in</span>
        <div class="text-sm font-bold text-slate-700"><?= $isAmharic ? 'ምንም ያልተመዘገበ ተሽከርካሪ ሪፖርት የለም።' : 'No unregistered motorcycle violations logged.' ?></div>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
            <tr>
              <th class="py-3 px-4"><?= $isAmharic ? 'ቀን / ማስረጃ' : 'Date / Photo' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'የሰሌዳ / መለያ' : 'Plate / Tag' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'አሽከርካሪ' : 'Driver' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ቦታና ክፍለ ከተማ' : 'Location & Subcity' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'የኦፊሰር ስም / ባጅ' : 'Reporting Officer' ?></th>
              <th class="py-3 px-4"><?= $isAmharic ? 'ሁኔታ' : 'Status' ?></th>
              <th class="py-3 px-4 text-right"><?= $isAmharic ? 'እርምጃ' : 'Action' ?></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($reports as $rep): 
              $photo = $rep['evidence_photo'] ?: 'image/app/logo.png';
              $status = $rep['status'];
            ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img src="<?= htmlspecialchars($photo) ?>" onclick="openImageViewer('<?= htmlspecialchars($photo) ?>', 'Violation Evidence')" class="w-10 h-10 rounded-lg object-cover border border-slate-200 cursor-pointer shadow-xs" alt="Evidence">
                    <div class="font-mono text-slate-500 text-[10px]"><?= htmlspecialchars($rep['reported_at']) ?></div>
                  </div>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-rose-700"><?= htmlspecialchars($rep['plate_number']) ?></td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900"><?= htmlspecialchars($rep['driver_name'] ?: 'Unknown') ?></div>
                  <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($rep['driver_phone']) ?></div>
                </td>
                <td class="py-3 px-4">
                  <div><?= htmlspecialchars($rep['location_name']) ?></div>
                  <div class="text-[10px] text-slate-400"><?= htmlspecialchars($rep['sub_city']) ?></div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold"><?= htmlspecialchars($rep['officer_name']) ?></div>
                  <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($rep['officer_badge_id']) ?></div>
                </td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= 
                    $status === 'resolved' ? 'bg-emerald-100 text-emerald-800' :
                    ($status === 'impounded' ? 'bg-purple-100 text-purple-800' : 'bg-rose-100 text-rose-800')
                  ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <?php if ($status !== 'resolved'): ?>
                      <button onclick="resolveCase('<?= $rep['id'] ?>')" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold cursor-pointer">
                        Resolve
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
  async function resolveCase(id) {
    const notes = prompt('Enter resolution notes (የመፍትሄ ማስታወሻ):');
    if (notes === null) return;

    try {
      const res = await AppAPI.post('ajax/unregistered.php?action=update_status', {
        id,
        status: 'resolved',
        resolution_notes: notes.trim()
      });
      if (res.success) {
        showToast('Case marked as resolved', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Failed to resolve case', 'error');
    }
  }
</script>
