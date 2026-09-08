<?php
/**
 * Municipal Payment Receipts & Revenue Tracking
 */
$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');

$receipts = $pdo->query("SELECT * FROM payment_receipts ORDER BY created_at DESC")->fetchAll();
$now = new DateTime();
$totalCount = count($receipts);
$activeCount = 0;
$expiringCount = 0;
$expiredCount = 0;

foreach ($receipts as &$rc) {
    $exp = new DateTime($rc['expiration_date']);
    $diff = (int)$now->diff($exp)->format('%r%a');
    if ($diff < 0) {
        $rc['status'] = 'expired';
        $expiredCount++;
    } elseif ($diff <= 30) {
        $rc['status'] = 'expiring_soon';
        $expiringCount++;
    } else {
        $rc['status'] = 'active';
        $activeCount++;
    }
}
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-sm">receipt_long</span>
        <span><?= $isAmharic ? 'የከተማው ገቢና ክፍያዎች' : 'Municipal Revenue & Permits' ?></span>
      </div>
      <h2 class="text-xl font-black text-slate-900">
        <?= $isAmharic ? 'የፈቃድ ክፍያ ደረሰኞችና የታደሱ ማህደሮች' : 'Permit Payment Receipts' ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5"><?= $totalCount ?> <?= $isAmharic ? 'የተመዘገቡ ደረሰኞች' : 'Payment receipts on record' ?></p>
    </div>

    <div class="flex items-center gap-2">
      <button onclick="document.getElementById('addReceiptModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-base">add</span>
        <span><?= $isAmharic ? 'አዲስ ደረሰኝ መዝግብ' : 'Add Payment Receipt' ?></span>
      </button>
    </div>
  </div>

  <!-- KPI Metrics -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider"><?= $isAmharic ? 'ጠቅላላ ደረሰኞች' : 'Total Receipts' ?></div>
      <div class="text-2xl font-black text-slate-900 mt-1"><?= $totalCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider"><?= $isAmharic ? 'ህጋዊ / ንቁ (Active)' : 'Active Valid' ?></div>
      <div class="text-2xl font-black text-emerald-600 mt-1"><?= $activeCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider"><?= $isAmharic ? 'በቅርብ የሚያበቁ' : 'Expiring (<30d)' ?></div>
      <div class="text-2xl font-black text-amber-600 mt-1"><?= $expiringCount ?></div>
    </div>
    <div class="command-card p-4">
      <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider"><?= $isAmharic ? 'ያለፈባቸው (Expired)' : 'Expired' ?></div>
      <div class="text-2xl font-black text-rose-600 mt-1"><?= $expiredCount ?></div>
    </div>
  </div>

  <!-- Table -->
  <div class="command-card overflow-hidden bg-white border border-slate-200">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-700">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
          <tr>
            <th class="py-3 px-4"><?= $isAmharic ? 'የደረሰኝ ቁጥር' : 'Receipt Number' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'ባለቤት / ስም' : 'Owner / Name' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'የሰሌዳ ቁጥር' : 'Plate Number' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'መጠን (ETB)' : 'Amount' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'የተከፈለበት ቀን' : 'Payment Date' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'የሚያበቃበት ቀን' : 'Expiration Date' ?></th>
            <th class="py-3 px-4"><?= $isAmharic ? 'ሁኔታ' : 'Status' ?></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($receipts as $rc): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 font-mono font-bold text-blue-900"><?= htmlspecialchars($rc['receipt_number']) ?></td>
              <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($rc['owner_name']) ?></td>
              <td class="py-3 px-4 font-mono font-black text-slate-700"><?= htmlspecialchars($rc['plate_number']) ?></td>
              <td class="py-3 px-4 font-mono font-bold text-emerald-700"><?= number_format($rc['amount'], 2) ?> ETB</td>
              <td class="py-3 px-4 font-mono text-slate-500"><?= htmlspecialchars($rc['payment_date']) ?></td>
              <td class="py-3 px-4 font-mono text-slate-500"><?= htmlspecialchars($rc['expiration_date']) ?></td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= 
                  $rc['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' :
                  ($rc['status'] === 'expiring_soon' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')
                ?>">
                  <?= htmlspecialchars($rc['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Add Receipt Modal -->
<div id="addReceiptModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div class="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
    <div class="p-4 bg-emerald-700 text-white flex justify-between items-center">
      <h3 class="text-sm font-bold flex items-center gap-2">
        <span class="material-symbols-outlined">receipt_long</span>
        <span><?= $isAmharic ? 'አዲስ የክፍያ ደረሰኝ መዝግብ' : 'Register Payment Receipt' ?></span>
      </h3>
      <button onclick="document.getElementById('addReceiptModal').classList.add('hidden')" class="p-1 hover:bg-white/10 rounded cursor-pointer">&times;</button>
    </div>
    <form id="addReceiptForm" class="p-5 space-y-3">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Receipt Number *</label>
        <input type="text" id="newRcptNo" required placeholder="REC-2026-..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owner Name *</label>
        <input type="text" id="newRcptName" required placeholder="Full Name" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Plate Number *</label>
        <input type="text" id="newRcptPlate" required placeholder="3-AA-..." class="w-full px-3 py-2 text-xs font-mono uppercase border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
        <input type="tel" id="newRcptPhone" placeholder="09xxxxxxxx" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Amount (ETB)</label>
        <input type="number" id="newRcptAmount" value="850.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none">
      </div>
      <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('addReceiptModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold">Cancel</button>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Save Receipt</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('addReceiptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      receipt_number: document.getElementById('newRcptNo').value.trim(),
      owner_name: document.getElementById('newRcptName').value.trim(),
      plate_number: document.getElementById('newRcptPlate').value.trim().toUpperCase(),
      phone: document.getElementById('newRcptPhone').value.trim(),
      amount: document.getElementById('newRcptAmount').value
    };

    try {
      const res = await AppAPI.post('ajax/payments.php?action=create', payload);
      if (res.success) {
        showToast('Payment receipt recorded', 'success');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (err) {
      showToast(err.message || 'Failed to save receipt', 'error');
    }
  });
</script>
