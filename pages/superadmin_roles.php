<?php
/**
 * Super Admin RBAC Role Permissions Matrix
 * 16 Tasks x 5 System Roles
 */
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$matrix = RBAC::getMatrix();
$roles = ['superadmin', 'admin', 'officer', 'clerk', 'public'];
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-purple-700 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">shield_person</span>
        <span><?= $isAmharic ? 'የፈቃድ ማትሪክስ' : 'Access Control Governance' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'የስራ ሚናዎችና ፈቃዶች ማትሪክስ (RBAC 16 Tasks)' : 'Role-Based Access Control Matrix' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        <?= $isAmharic ? 'በ16ቱ የስራ ተግባራትና በ5ቱ የተጠቃሚ ሚናዎች መካከል ያለውን የፈቃድ ደረጃ መወሰኛ' : 'Configure granular permissions across 16 core operational tasks and 5 system roles.' ?>
      </p>
    </div>

    <div class="flex items-center gap-2">
      <button onclick="saveRbacMatrix()" id="saveMatrixBtn" class="px-4 py-2 bg-purple-800 text-white text-xs font-bold rounded-lg hover:bg-purple-900 transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-sm">save</span>
        <span><?= $isAmharic ? 'ማትሪክሱን መዝግብ' : 'Save Matrix Changes' ?></span>
      </button>
    </div>
  </div>

  <!-- RBAC Matrix Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-700">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
          <tr>
            <th class="py-3 px-4 w-12">#</th>
            <th class="py-3 px-4 min-w-[240px]">Task / Operation</th>
            <th class="py-3 px-4 text-center">Super Admin</th>
            <th class="py-3 px-4 text-center">Admin</th>
            <th class="py-3 px-4 text-center">Officer</th>
            <th class="py-3 px-4 text-center">Clerk</th>
            <th class="py-3 px-4 text-center">Public</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach (SYSTEM_TASKS as $task): 
            $tid = $task['id'];
            $taskData = $matrix[$tid] ?? null;
          ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 font-mono font-bold text-slate-400"><?= $tid ?></td>
              <td class="py-3 px-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($task['title_en']) ?></div>
                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($task['title_am']) ?></div>
              </td>
              <?php foreach ($roles as $r): 
                $currPerm = $taskData[$r] ?? ($r === 'superadmin' ? 'allow' : 'deny');
                $isSuper = ($r === 'superadmin');
              ?>
                <td class="py-3 px-4 text-center">
                  <?php if ($isSuper): ?>
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-900 font-bold rounded text-[10px]">ALWAYS ALLOW</span>
                  <?php else: ?>
                    <select 
                      data-task="<?= $tid ?>" 
                      data-role="<?= $r ?>" 
                      class="rbac-select px-2 py-1 text-[11px] font-bold rounded border border-slate-200 outline-none <?= 
                        $currPerm === 'allow' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' :
                        ($currPerm === 'view_only' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-rose-50 text-rose-800 border-rose-300')
                      ?>">
                      <option value="allow" <?= $currPerm === 'allow' ? 'selected' : '' ?>>Allow (ተፈቅዷል)</option>
                      <option value="view_only" <?= $currPerm === 'view_only' ? 'selected' : '' ?>>View Only (እይታ ብቻ)</option>
                      <option value="deny" <?= $currPerm === 'deny' ? 'selected' : '' ?>>Deny (ተከልክሏል)</option>
                    </select>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  async function saveRbacMatrix() {
    const selects = document.querySelectorAll('.rbac-select');
    const matrix = {};

    // Base structure
    for (let i = 1; i <= 16; i++) {
      matrix[i] = { superadmin: 'allow', admin: 'deny', officer: 'deny', clerk: 'deny', public: 'deny' };
    }

    selects.forEach(sel => {
      const task = parseInt(sel.getAttribute('data-task'), 10);
      const role = sel.getAttribute('data-role');
      const val = sel.value;
      if (matrix[task]) {
        matrix[task][role] = val;
      }
    });

    const btn = document.getElementById('saveMatrixBtn');
    btn.disabled = true;

    try {
      const res = await AppAPI.post('ajax/rbac.php?action=save', { matrix });
      if (res.success) {
        showToast('RBAC permissions matrix updated successfully', 'success');
        setTimeout(() => window.location.reload(), 600);
      }
    } catch (e) {
      showToast(e.message || 'Failed to save RBAC matrix', 'error');
      btn.disabled = false;
    }
  }
</script>
