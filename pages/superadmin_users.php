<?php
/**
 * Super Admin User Management
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-purple-700 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">group</span>
        <span><?= $isAmharic ? 'የተጠቃሚዎች ማኔጅመንት' : 'User Governance' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'የሲስተም ተጠቃሚዎችና መለያዎች ማስተዳደሪያ' : 'System Users & Account Management' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5"><?= count($users) ?> <?= $isAmharic ? 'ተጠቃሚዎች ተመዝግበዋል' : 'Accounts Registered' ?></p>
    </div>

    <div class="flex items-center gap-2">
      <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-3.5 py-2 bg-purple-800 text-white text-xs font-bold rounded-lg hover:bg-purple-900 transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-base">person_add</span>
        <span><?= $isAmharic ? 'አዲስ ተጠቃሚ ፍጠር' : 'Add New User' ?></span>
      </button>
    </div>
  </div>

  <!-- Users Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-700">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Badge ID</th>
            <th class="py-3 px-4">Full Name / Email</th>
            <th class="py-3 px-4">Role</th>
            <th class="py-3 px-4">Sub-City Branch</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 font-mono font-bold text-purple-900"><?= htmlspecialchars($u['badge_id']) ?></td>
              <td class="py-3 px-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($u['full_name']) ?></div>
                <div class="text-[11px] text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
              </td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= 
                  $u['role'] === 'superadmin' ? 'bg-purple-100 text-purple-800' :
                  ($u['role'] === 'admin' ? 'bg-blue-100 text-blue-800' :
                  ($u['role'] === 'officer' ? 'bg-cyan-100 text-cyan-800' : 'bg-rose-100 text-rose-800'))
                ?>">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($u['sub_city'] ?: 'Central') ?></td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                  <?= htmlspecialchars($u['status']) ?>
                </span>
              </td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <?php if ($u['badge_id'] !== 'SUPER-ADMIN-01'): ?>
                    <button onclick="toggleUserStatus('<?= $u['id'] ?>', '<?= $u['status'] === 'active' ? 'suspended' : 'active' ?>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold cursor-pointer">
                      <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                    </button>
                    <button onclick="deleteUserAccount('<?= $u['id'] ?>')" class="p-1 text-rose-600 hover:bg-rose-50 rounded cursor-pointer" title="Delete">
                      <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                  <?php else: ?>
                    <span class="text-[10px] text-slate-400 italic">Protected</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div class="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
    <div class="p-4 bg-purple-800 text-white flex justify-between items-center">
      <h3 class="text-sm font-bold flex items-center gap-2">
        <span class="material-symbols-outlined">person_add</span>
        <span>Add System User</span>
      </h3>
      <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="p-1 hover:bg-white/10 rounded cursor-pointer">&times;</button>
    </div>
    <form id="addUserForm" class="p-5 space-y-3">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge ID *</label>
        <input type="text" id="newBadgeId" required placeholder="e.g. OFFICER-901, CLERK-302" class="w-full px-3 py-2 text-xs font-mono uppercase border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
        <input type="text" id="newFullName" required placeholder="Officer / Staff Full Name" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
        <input type="email" id="newEmail" placeholder="staff@bahirdar.gov.et" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">System Role *</label>
        <select id="newRole" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
          <option value="clerk">Clerk / Secretary</option>
          <option value="officer">Field Enforcement Officer</option>
          <option value="admin">Branch Administrator</option>
          <option value="superadmin">Super Administrator</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub-City Branch</label>
        <input type="text" id="newSubCity" value="Belay Zeleke" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Initial Password *</label>
        <input type="password" id="newPassword" value="admin123" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold">Cancel</button>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-purple-800 text-white text-xs font-bold hover:bg-purple-900">Create Account</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('addUserForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      badge_id: document.getElementById('newBadgeId').value.trim().toUpperCase(),
      full_name: document.getElementById('newFullName').value.trim(),
      email: document.getElementById('newEmail').value.trim(),
      role: document.getElementById('newRole').value,
      sub_city: document.getElementById('newSubCity').value.trim(),
      password: document.getElementById('newPassword').value
    };

    try {
      const res = await AppAPI.post('ajax/users.php?action=create', payload);
      if (res.success) {
        showToast('User account created successfully', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (err) {
      showToast(err.message || 'Failed to create user', 'error');
    }
  });

  async function toggleUserStatus(id, newStatus) {
    try {
      const res = await AppAPI.post('ajax/users.php?action=update', { id, status: newStatus });
      if (res.success) {
        showToast(`User status updated to ${newStatus}`, 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Status update failed', 'error');
    }
  }

  async function deleteUserAccount(id) {
    if (!confirm('Are you sure you want to delete this user account?')) return;
    try {
      const res = await AppAPI.post('ajax/users.php?action=delete', { id });
      if (res.success) {
        showToast('Account deleted', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (e) {
      showToast('Failed to delete user', 'error');
    }
  }
</script>
