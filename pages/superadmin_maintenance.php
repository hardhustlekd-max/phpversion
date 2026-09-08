<?php
/**
 * Super Admin Database Maintenance & System Reset
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

try {
    $settings = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config' LIMIT 1")->fetch() ?: [];
    $regCount = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations")->fetchColumn();
    $rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM motorcycle_registrations WHERE status = 'rejected'")->fetchColumn();
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $vlogCount = (int)$pdo->query("SELECT COUNT(*) FROM verification_logs")->fetchColumn();
    $rcptCount = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts")->fetchColumn();
} catch (Exception $e) {
    $settings = [];
    $regCount = 0;
    $rejectedCount = 0;
    $userCount = 0;
    $vlogCount = 0;
    $rcptCount = 0;
}
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200">
    <div class="flex items-center gap-2 text-rose-700 text-xs font-bold uppercase tracking-wider mb-1">
      <span class="material-symbols-outlined text-sm">database</span>
      <span><?= $isAmharic ? 'የዳታቤዝ ጥገናና ቁጥጥር' : 'Database Maintenance' ?></span>
    </div>
    <h2 class="text-xl font-black text-slate-900">
      <?= $isAmharic ? 'የዳታቤዝ ጥገና፣ ባክአፕና የሲስተም ሪሴት' : 'Database Integrity & System Reset' ?>
    </h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= $isAmharic ? 'ሙሉ የዳታቤዝ ባክአፕ ማውረድ፣ ውድቅ የሆኑትን ማጽዳትና ሲስተሙን እንደ አዲስ ማስጀመር' : 'Export JSON database backups, purge rejected applications, and execute master resets.' ?>
    </p>
  </div>

  <!-- Table Metrics -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-slate-400 uppercase">Registrations</div>
      <div class="text-2xl font-black text-slate-900 mt-1"><?= $regCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-slate-400 uppercase">Rejected Records</div>
      <div class="text-2xl font-black text-rose-600 mt-1"><?= $rejectedCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-slate-400 uppercase">Receipts</div>
      <div class="text-2xl font-black text-emerald-600 mt-1"><?= $rcptCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-slate-400 uppercase">Officers & Users</div>
      <div class="text-2xl font-black text-purple-700 mt-1"><?= $userCount ?></div>
    </div>
  </div>

  <!-- Maintenance Operations -->
  <div class="space-y-4">
    
    <!-- Export Backup Card -->
    <div class="command-card p-5 bg-white border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-900">cloud_download</span>
          <span><?= $isAmharic ? 'ሙሉ የዳታቤዝ ባክአፕ አውርድ (Export Backup)' : 'Export Full Database Backup' ?></span>
        </h3>
        <p class="text-xs text-slate-500 mt-1">
          <?= $isAmharic ? 'ሁሉንም የተመዘገቡ አባላት፣ ደረሰኞች፣ የፍተሻ ታሪኮችና ቅንብሮች በJSON ፎርማት ያውርዱ።' : 'Download a complete snapshot of all tables, accounts, receipts, and permissions in JSON format.' ?>
        </p>
      </div>
      <a href="ajax/maintenance.php?action=backup_export" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shrink-0">
        <span class="material-symbols-outlined text-sm">download</span>
        <span>Download JSON</span>
      </a>
    </div>

    <!-- Purge Rejected Card -->
    <div class="command-card p-5 bg-white border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
          <span class="material-symbols-outlined text-amber-600">cleaning_services</span>
          <span><?= $isAmharic ? 'ውድቅ የሆኑ ማመልከቻዎችን አጽዳ (Purge Rejected)' : 'Purge Rejected Applications' ?></span>
        </h3>
        <p class="text-xs text-slate-500 mt-1">
          <?= $isAmharic ? 'ውድቅ የተደረጉ <?= $rejectedCount ?> ማመልከቻዎችን ከዳታቤዝ በቋሚነት ያጠፋል።' : 'Permanently remove rejected applications from the active database table to free up storage.' ?>
        </p>
      </div>
      <button onclick="purgeRejected()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shrink-0 cursor-pointer">
        <span class="material-symbols-outlined text-sm">delete_sweep</span>
        <span>Purge (<?= $rejectedCount ?>)</span>
      </button>
    </div>

    <!-- Master Reset Danger Zone -->
    <div class="command-card p-5 bg-rose-50 border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-sm font-bold text-rose-900 flex items-center gap-2">
          <span class="material-symbols-outlined text-rose-700">warning</span>
          <span><?= $isAmharic ? 'የሲስተም ማስተር ሪሴት (Master Operational Reset - Task 12)' : 'Master Operational Reset' ?></span>
        </h3>
        <p class="text-xs text-rose-700 mt-1">
          <?= $isAmharic ? 'የስራ ማስኬጃ መዝገቦችን (ተሽከርካሪዎች፣ ደረሰኞች፣ ፍተሻዎች) ያጸዳል፤ የተጠቃሚ መለያዎችንና የፈቃድ ማትሪክስ ሳይነካ ያቆያል።' : 'Clears all operational registration records, receipts, and patrol logs while keeping user accounts and the RBAC matrix safe.' ?>
        </p>
        <div class="text-[11px] text-rose-600 font-mono mt-1">
          Last Reset: <?= htmlspecialchars($settings['last_system_reset_at'] ?? 'Never') ?>
        </div>
      </div>
      <button onclick="executeSystemReset()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shrink-0 cursor-pointer shadow">
        <span class="material-symbols-outlined text-sm">restart_alt</span>
        <span>Execute Reset</span>
      </button>
    </div>

  </div>

</div>

<script>
  async function purgeRejected() {
    if (!confirm('Are you sure you want to permanently delete all rejected applications?')) return;
    try {
      const res = await AppAPI.post('ajax/maintenance.php?action=purge_rejected', {});
      if (res.success) {
        showToast(`Purged ${res.purged} rejected records`, 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Purge failed', 'error');
    }
  }

  async function executeSystemReset() {
    const confirmation = prompt('WARNING: This will purge all operational motorcycle records. Type RESET to confirm:');
    if (confirmation !== 'RESET') {
      showToast('Reset cancelled', 'info');
      return;
    }

    try {
      const res = await AppAPI.post('ajax/maintenance.php?action=reset_database', {});
      if (res.success) {
        showToast('System operational database has been reset', 'success');
        setTimeout(() => window.location.reload(), 600);
      }
    } catch (e) {
      showToast('Reset failed', 'error');
    }
  }
</script>
