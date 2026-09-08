<?php
/**
 * Report Unregistered / Illegal Motorcycle Form
 */
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');
$subcities = BAHIR_DAR_SUBCITIES;
?>

<div class="max-w-3xl mx-auto space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200">
    <div class="flex items-center gap-2 text-rose-600 text-xs font-bold uppercase tracking-wider mb-1">
      <span class="material-symbols-outlined text-sm">notification_important</span>
      <span><?= $isAmharic ? 'የመንገድ ላይ ጥሰት ሪፖርት' : 'Patrol Violation Form' ?></span>
    </div>
    <h2 class="text-xl font-black text-slate-900">
      <?= $isAmharic ? 'ያልተመዘገበ ወይም ህገወጥ ሞተር ሪፖርት ማድረጊያ' : 'Report Unregistered Motorcycle' ?>
    </h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= $isAmharic ? 'በመንገድ ላይ የተያዘ ያለፈቃድ የሚንቀሳቀስ ተሽከርካሪ ማስመዝገቢያ' : 'Log unpermitted or illegal motorcycle seized/intercepted during roadside patrol.' ?>
    </p>
  </div>

  <!-- Form -->
  <form id="reportUnregForm" class="command-card p-6 bg-white border border-slate-200 space-y-4">
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የሰሌዳ / መለያ ቁጥር (Plate / Tag No) *' : 'Plate / Tag Number *' ?>
        </label>
        <input type="text" id="unregPlate" required placeholder="e.g. UNTAGGED-0492 or 3-AA-..."
          class="w-full px-3 py-2 text-sm font-mono uppercase font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የአሽከርካሪው ስም (Driver Name)' : 'Driver Name' ?>
        </label>
        <input type="text" id="unregDriver" placeholder="e.g. ከበደ ወርቁ (Kebede Worku)"
          class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'ስልክ ቁጥር (Driver Phone)' : 'Driver Phone' ?>
        </label>
        <input type="tel" id="unregPhone" placeholder="09xxxxxxxx"
          class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የሞተር ብራንድ (Brand / Model)' : 'Brand / Model' ?>
        </label>
        <input type="text" id="unregBrand" placeholder="e.g. Haojue 110, Bajaj, Electric"
          class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የሞተር / ሴሪያል ቁጥር (Engine No)' : 'Engine Number' ?>
        </label>
        <input type="text" id="unregEngine" placeholder="ENG-..."
          class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የሻሲ ቁጥር (Chassis No)' : 'Chassis Number' ?>
        </label>
        <input type="text" id="unregChassis" placeholder="CHAS-..."
          class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'ክፍለ ከተማ (Sub-City Zone) *' : 'Sub-City Zone *' ?>
        </label>
        <select id="unregSubCity" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
          <?php foreach ($subcities as $sc): ?>
            <option value="<?= $sc['en'] ?>"><?= $isAmharic ? $sc['am'] : $sc['en'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          <?= $isAmharic ? 'የፍተሻ / የተያዘበት ቦታ (Patrol Location) *' : 'Patrol Checkpoint Location *' ?>
        </label>
        <input type="text" id="unregLocation" required placeholder="e.g. ቀበሌ 13 አደባባይ (Kebele 13 Roundabout)"
          class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none">
      </div>
    </div>

    <!-- Evidence Upload -->
    <div class="border border-dashed border-slate-300 p-4 rounded-xl text-center bg-slate-50">
      <div class="text-xs font-bold text-slate-700 mb-2"><?= $isAmharic ? 'የተሽከርካሪው ማስረጃ ፎቶ (Evidence Photo)' : 'Vehicle Evidence Photo' ?></div>
      <div class="w-32 h-24 mx-auto bg-white border border-slate-200 rounded-lg mb-2 overflow-hidden flex items-center justify-center">
        <img id="previewEvidence" src="image/app/logo.png" class="w-full h-full object-cover" alt="Evidence">
      </div>
      <input type="file" id="fileEvidence" accept="image/*" class="hidden" onchange="handleEvidenceUpload(this)">
      <button type="button" onclick="document.getElementById('fileEvidence').click()" class="px-3 py-1 bg-slate-800 text-white text-xs font-bold rounded cursor-pointer">
        <?= $isAmharic ? 'ፎቶ ምረጥ / አንሳ' : 'Select Photo' ?>
      </button>
      <input type="hidden" id="evidencePhotoUrl" value="">
    </div>

    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
        <?= $isAmharic ? 'የጥሰቱ ዝርዝርና የኦፊሰር ማስታወሻ (Notes / Violation Description) *' : 'Violation Description *' ?>
      </label>
      <textarea id="unregNotes" rows="3" required placeholder="e.g. ተሽከርካሪው ያለሰሌዳና ያለፈቃድ ሲንቀሳቀስ ተይዟል..."
        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-600 outline-none"></textarea>
    </div>

    <div class="flex justify-end pt-3 border-t border-slate-100">
      <button type="submit" id="submitReportBtn" class="px-6 py-2.5 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition-colors flex items-center gap-1.5 shadow cursor-pointer">
        <span class="material-symbols-outlined text-sm">send</span>
        <span><?= $isAmharic ? 'ሪፖርቱን መዝግብ' : 'Submit Violation Report' ?></span>
      </button>
    </div>

  </form>

</div>

<script>
  async function handleEvidenceUpload(input) {
    if (!input.files || !input.files[0]) return;
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('category', 'evidence');
    try {
      showToast('Uploading evidence photo...', 'info');
      const res = await AppAPI.upload(fd);
      if (res.success && res.url) {
        document.getElementById('previewEvidence').src = res.url;
        document.getElementById('evidencePhotoUrl').value = res.url;
        showToast('Evidence attached', 'success');
      }
    } catch (e) {
      showToast('Upload failed', 'error');
    }
  }

  document.getElementById('reportUnregForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitReportBtn');
    btn.disabled = true;

    const payload = {
      plate_number: document.getElementById('unregPlate').value.trim().toUpperCase(),
      driver_name: document.getElementById('unregDriver').value.trim(),
      driver_phone: document.getElementById('unregPhone').value.trim(),
      motor_brand: document.getElementById('unregBrand').value.trim(),
      engine_or_serial_no: document.getElementById('unregEngine').value.trim(),
      chassis_number: document.getElementById('unregChassis').value.trim(),
      sub_city: document.getElementById('unregSubCity').value,
      location_name: document.getElementById('unregLocation').value.trim(),
      evidence_photo: document.getElementById('evidencePhotoUrl').value,
      notes: document.getElementById('unregNotes').value.trim()
    };

    try {
      const res = await AppAPI.post('ajax/unregistered.php?action=create', payload);
      if (res.success) {
        showToast('Violation logged successfully', 'success');
        setTimeout(() => window.location.href = 'index.php?page=unregistered_list', 600);
      }
    } catch (err) {
      showToast(err.message || 'Failed to submit report', 'error');
      btn.disabled = false;
    }
  });
</script>
