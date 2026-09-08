<?php
/**
 * Super Admin Security & Audit Trail
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$logs = $pdo->query("SELECT * FROM system_audit_logs ORDER BY created_at DESC LIMIT 150")->fetchAll();
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-purple-700 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">security</span>
        <span><?= $isAmharic ? 'የደህንነት ቁጥጥር' : 'Security & Compliance' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'የደህንነት ኦዲትና የተጠቃሚዎች ክትትል ማህደር' : 'System Audit Trail & Security Logs' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5"><?= count($logs) ?> <?= $isAmharic ? 'የተመዘገቡ የኦዲት ኩነቶች' : 'Audit events logged' ?></p>
    </div>

    <div class="flex items-center gap-2">
      <button onclick="clearAuditLogs()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer">
        <span class="material-symbols-outlined text-base text-rose-600">delete_sweep</span>
        <span><?= $isAmharic ? 'ኦዲት አጽዳ' : 'Purge Logs' ?></span>
      </button>
    </div>
  </div>

  <!-- Audit Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <?php if (empty($logs)): ?>
      <div class="text-center py-16">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">verified_user</span>
        <div class="text-sm font-bold text-slate-700"><?= $isAmharic ? 'ምንም የደህንነት ማስጠንቀቂያ ወይም ክስተት የለም።' : 'No audit log entries recorded.' ?></div>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">Timestamp</th>
              <th class="py-3 px-4">User / Badge</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Action</th>
              <th class="py-3 px-4">IP Address</th>
              <th class="py-3 px-4">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($logs as $l): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-4 font-mono text-slate-500"><?= htmlspecialchars($l['created_at']) ?></td>
                <td class="py-3 px-4 font-mono font-bold text-purple-900"><?= htmlspecialchars($l['user_badge_id'] ?: 'System') ?></td>
                <td class="py-3 px-4 uppercase text-[10px] font-bold text-slate-500"><?= htmlspecialchars($l['user_role'] ?: '—') ?></td>
                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($l['action']) ?></td>
                <td class="py-3 px-4 font-mono text-slate-500"><?= htmlspecialchars($l['ip_address'] ?: '127.0.0.1') ?></td>
                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($l['details'] ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
  async function clearAuditLogs() {
    if (!confirm('Are you sure you want to clear the audit logs?')) return;
    try {
      const res = await AppAPI.post('ajax/maintenance.php?action=clear_audit', {});
      if (res.success) {
        showToast('Audit log purged', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Failed to purge logs', 'error');
    }
  }
</script>
