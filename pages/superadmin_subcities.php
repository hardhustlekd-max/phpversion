<?php
/**
 * Super Admin Sub-Cities & Emergency Freeze Governance
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$settings = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config' LIMIT 1")->fetch();
$frozenSubcities = !empty($settings['frozen_subcities']) ? json_decode($settings['frozen_subcities'], true) : [];
if (!is_array($frozenSubcities)) $frozenSubcities = [];

// Counts per subcity
$counts = $pdo->query("SELECT sub_city, COUNT(*) as cnt FROM motorcycle_registrations GROUP BY sub_city")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200">
    <div class="flex items-center gap-2 text-purple-700 text-xs font-bold uppercase tracking-wider mb-1">
      <span class="material-symbols-outlined text-sm">location_city</span>
      <span><?= $isAmharic ? 'የክፍለ ከተሞች አስተዳደር' : 'Municipal Zone Governance' ?></span>
    </div>
    <h2 class="text-xl font-black text-slate-900">
      <?= $isAmharic ? 'የክፍለ ከተሞች ቁጥጥርና የአደጋ ጊዜ እገዳ (Emergency Freeze)' : 'Sub-City Governance & Emergency Freeze' ?>
    </h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= $isAmharic ? 'በማናቸውም ክፍለ ከተማ አዲስ ምዝገባን ወይም የፈቃድ አሰጣጥን በጊዜያዊነት ማገድ' : 'Halt new registrations and traffic permits in specific zones during security emergencies.' ?>
    </p>
  </div>

  <!-- Subcities Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach (BAHIR_DAR_SUBCITIES as $sc): 
      $isFrozen = in_array($sc['en'], $frozenSubcities, true);
      $cnt = $counts[$sc['en']] ?? 0;
    ?>
      <div class="command-card p-5 bg-white border <?= $isFrozen ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200' ?> flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-400 uppercase"><?= $sc['en'] ?></span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $isFrozen ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' ?>">
              <?= $isFrozen ? 'Frozen / የታገደ' : 'Operational / ክፍት' ?>
            </span>
          </div>
          <h3 class="text-lg font-black text-slate-900"><?= $sc['am'] ?></h3>
          <p class="text-xs text-slate-500 mt-1"><?= $cnt ?> <?= $isAmharic ? 'የተመዘገቡ ሞተርሳይክሎች' : 'Active Registered Motors' ?></p>
        </div>

        <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between">
          <span class="text-xs text-slate-600 font-medium"><?= $isAmharic ? 'የስራ ሁኔታ' : 'Zone Status' ?></span>
          <button 
            onclick="toggleSubcityFreeze('<?= $sc['en'] ?>', <?= $isFrozen ? 'false' : 'true' ?>)"
            class="px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-colors <?= $isFrozen ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' ?>">
            <?= $isFrozen ? 'Unfreeze Zone' : 'Emergency Freeze' ?>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
  let frozenList = <?= json_encode($frozenSubcities) ?>;

  async function toggleSubcityFreeze(subCity, willFreeze) {
    if (willFreeze) {
      if (!confirm(`Are you sure you want to FREEZE ${subCity}? New permits will be blocked.`)) return;
      if (!frozenList.includes(subCity)) frozenList.push(subCity);
    } else {
      frozenList = frozenList.filter(s => s !== subCity);
    }

    try {
      const res = await AppAPI.post('ajax/settings.php?action=save', { frozen_subcities: frozenList });
      if (res.success) {
        showToast(`Sub-city freeze state updated for ${subCity}`, 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Failed to update freeze state', 'error');
    }
  }
</script>
