<?php
/**
 * Roadside QR & Barcode Scanner View
 */
$currentUser = Auth::user();
$userRole = Auth::role();
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Header -->
  <div class="command-card p-6 bg-white border border-slate-200">
    <div class="flex items-center gap-2 text-cyan-700 text-xs font-bold uppercase tracking-wider mb-1">
      <span class="material-symbols-outlined text-sm">qr_code_scanner</span>
      <span><?= $isAmharic ? 'የመንገድ ላይ ባርኮድ ስካነር' : 'Roadside Enforcement Scanner' ?></span>
    </div>
    <h2 class="text-xl font-black text-slate-900">
      <?= $isAmharic ? 'የሞተርሳይክል ፈቃድና የQR ኮድ ፍተሻ' : 'Permit QR & Barcode Verification' ?>
    </h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= $isAmharic ? 'ካሜራ በመጠቀም ወይም የሰሌዳ ቁጥር በማስገባት የተሽከርካሪውን ህጋዊነት ወዲያውኑ አረጋግጥ' : 'Verify valid license, national ID, and vehicle specs instantly on the road.' ?>
    </p>
  </div>

  <!-- Scanner Controls & Viewport -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Left: Camera & Manual Search -->
    <div class="command-card p-5 bg-white border border-slate-200 space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800"><?= $isAmharic ? 'የካሜራ ስካነር' : 'Camera Feed' ?></h3>
        <div class="flex gap-2">
          <button id="startCamBtn" onclick="initCamera()" class="px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-xs font-bold flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">videocam</span>
            <span>Start Cam</span>
          </button>
          <button id="stopCamBtn" onclick="stopCameraFeed()" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold hidden flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">videocam_off</span>
            <span>Stop</span>
          </button>
        </div>
      </div>

      <!-- Video Box -->
      <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center border border-slate-800">
        <video id="scannerVideo" class="w-full h-full object-cover hidden"></video>
        <div id="videoPlaceholder" class="text-center p-6 text-slate-400">
          <span class="material-symbols-outlined text-4xl mb-2 text-slate-500">camera_alt</span>
          <div class="text-xs"><?= $isAmharic ? 'ካሜራ ለመክፈት ከላይ ያለውን "Start Cam" ይጫኑ' : 'Click "Start Cam" to activate live scanning' ?></div>
        </div>
        <div id="scanOverlay" class="hidden absolute inset-0 border-2 border-dashed border-emerald-400 m-8 rounded-lg pointer-events-none"></div>
      </div>

      <!-- Manual Plate / QR Input -->
      <div class="pt-3 border-t border-slate-100">
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
          <?= $isAmharic ? 'ወይም የሰሌዳ ቁጥር በቀጥታ አስገባ' : 'Or Search by Plate / QR Code' ?>
        </label>
        <div class="flex gap-2">
          <input 
            type="text" 
            id="scanPlateInput" 
            placeholder="e.g. 3-AA-98124"
            class="w-full px-3 py-2 text-xs font-mono uppercase font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
          <button 
            onclick="handleManualLookup()" 
            class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-xs font-bold flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-sm">search</span>
            <span>Verify</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Right: Verification Result Card -->
    <div id="scanResultContainer" class="command-card p-5 bg-white border border-slate-200">
      <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-emerald-600">verified</span>
        <span><?= $isAmharic ? 'የፍተሻ ውጤት (Verification Result)' : 'Verification Result' ?></span>
      </h3>

      <div id="scanResultEmpty" class="text-center py-16 text-slate-400">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">qr_code_2</span>
        <div class="text-xs"><?= $isAmharic ? 'የተሽከርካሪውን ባርኮድ ስካን ያድርጉ ወይም ሰሌዳውን ያስገቡ' : 'Scan a code or search a plate to inspect details.' ?></div>
      </div>

      <div id="scanResultContent" class="hidden space-y-4 pt-4">
        <!-- Driver Profile & Badge -->
        <div class="flex gap-4 items-start">
          <img id="resPortrait" src="" class="w-24 h-28 object-cover rounded-lg border border-slate-200 shadow-sm" alt="Portrait">
          <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between">
              <span id="resPlate" class="text-lg font-black text-blue-900 font-mono"></span>
              <span id="resStatus" class="px-2 py-0.5 rounded text-xs font-bold uppercase"></span>
            </div>
            <div id="resName" class="text-sm font-bold text-slate-900"></div>
            <div id="resPhone" class="text-xs text-slate-500 font-mono"></div>
            <div id="resCategory" class="text-xs font-medium text-emerald-700"></div>
            <div id="resSubCity" class="text-xs text-slate-500"></div>
          </div>
        </div>

        <!-- Vehicle specs grid -->
        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-3 rounded-lg border border-slate-100">
          <div><span class="text-slate-400">Brand:</span> <span id="resBrand" class="font-bold text-slate-700"></span></div>
          <div><span class="text-slate-400">Engine:</span> <span id="resEngine" class="font-mono text-slate-700"></span></div>
          <div><span class="text-slate-400">Chassis:</span> <span id="resChassis" class="font-mono text-slate-700"></span></div>
          <div><span class="text-slate-400">Blood Group:</span> <span id="resBlood" class="font-bold text-slate-700"></span></div>
        </div>

        <!-- Officer Log Action Box -->
        <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg space-y-2">
          <label class="block text-[11px] font-bold text-blue-900 uppercase">
            <?= $isAmharic ? 'የፍተሻ ማስታወሻና ሁኔታ መዝግብ' : 'Log Roadside Inspection Check' ?>
          </label>
          <div class="grid grid-cols-2 gap-2">
            <select id="resCheckStatus" class="px-2 py-1 text-xs border border-blue-200 rounded bg-white">
              <option value="verified">Verified (ህጋዊ)</option>
              <option value="warning">Warning (ማስጠንቀቂያ)</option>
              <option value="flagged">Flagged (የታገደ/ጥሰት)</option>
            </select>
            <input type="text" id="resCheckpoint" value="Fasilo Checkpoint" placeholder="Checkpoint Name" class="px-2 py-1 text-xs border border-blue-200 rounded bg-white">
          </div>
          <input type="text" id="resOfficerNotes" placeholder="Officer notes (e.g. Helmet worn, documents verified)" class="w-full px-2 py-1 text-xs border border-blue-200 rounded bg-white">
          <button onclick="saveInspectionLog()" class="w-full py-2 bg-blue-900 hover:bg-blue-800 text-white rounded text-xs font-bold flex items-center justify-center gap-1 cursor-pointer shadow-xs">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span><?= $isAmharic ? 'ፍተሻውን በማህደር መዝግብ' : 'Record Officer Inspection' ?></span>
          </button>
        </div>

      </div>

    </div>

  </div>

</div>

<script>
  let activeRegistration = null;

  function initCamera() {
    document.getElementById('startCamBtn').classList.add('hidden');
    document.getElementById('stopCamBtn').classList.remove('hidden');
    document.getElementById('videoPlaceholder').classList.add('hidden');
    document.getElementById('scannerVideo').classList.remove('hidden');
    document.getElementById('scanOverlay').classList.remove('hidden');

    RoadsideScanner.startCamera('scannerVideo', (scannedData) => {
      showToast('Scanned code successfully', 'success');
      handleScannedCode(scannedData);
    });
  }

  function stopCameraFeed() {
    RoadsideScanner.stopCamera();
    document.getElementById('startCamBtn').classList.remove('hidden');
    document.getElementById('stopCamBtn').classList.add('hidden');
    document.getElementById('videoPlaceholder').classList.remove('hidden');
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scanOverlay').classList.add('hidden');
  }

  async function handleManualLookup() {
    const query = document.getElementById('scanPlateInput').value.trim();
    if (!query) return;
    handleScannedCode(query);
  }

  async function handleScannedCode(code) {
    const reg = await RoadsideScanner.lookupPlate(code);
    if (reg) {
      renderResult(reg);
    }
  }

  function renderResult(reg) {
    activeRegistration = reg;
    document.getElementById('scanResultEmpty').classList.add('hidden');
    document.getElementById('scanResultContent').classList.remove('hidden');

    document.getElementById('resPortrait').src = reg.user_portrait_photo || reg.userPortraitPhoto || 'image/app/logo.png';
    document.getElementById('resPlate').textContent = reg.plate_number || reg.plateNumber;
    document.getElementById('resName').textContent = reg.full_name || reg.fullName;
    document.getElementById('resPhone').textContent = reg.phone;
    document.getElementById('resCategory').textContent = reg.vehicle_category === 'electric' ? 'Electric Motorcycle' : 'Gas Motorcycle (< 110cc)';
    document.getElementById('resSubCity').textContent = 'Sub-City: ' + (reg.sub_city || 'Central');

    document.getElementById('resBrand').textContent = reg.motor_brand || 'Standard';
    document.getElementById('resEngine').textContent = reg.engine_or_serial_no || '—';
    document.getElementById('resChassis').textContent = reg.chassis_number || '—';
    document.getElementById('resBlood').textContent = reg.blood_group || 'O+';

    const statusEl = document.getElementById('resStatus');
    const st = reg.status;
    statusEl.textContent = st;
    statusEl.className = 'px-2 py-0.5 rounded text-xs font-bold uppercase ' + (
      st === 'approved' || st === 'printed' ? 'bg-emerald-100 text-emerald-800' :
      (st === 'pending_approval' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')
    );
  }

  async function saveInspectionLog() {
    if (!activeRegistration) return;

    const payload = {
      registration_id: activeRegistration.id,
      plate_number: activeRegistration.plate_number,
      full_name: activeRegistration.full_name,
      phone: activeRegistration.phone,
      vehicle_category: activeRegistration.vehicle_category,
      engine_or_serial_no: activeRegistration.engine_or_serial_no,
      permit_status: activeRegistration.status,
      verification_status: document.getElementById('resCheckStatus').value,
      location_name: document.getElementById('resCheckpoint').value.trim(),
      officer_notes: document.getElementById('resOfficerNotes').value.trim(),
      user_portrait_photo: activeRegistration.user_portrait_photo
    };

    try {
      const res = await AppAPI.post('ajax/verifications.php?action=create', payload);
      if (res.success) {
        showToast('Inspection check logged successfully', 'success');
        document.getElementById('resOfficerNotes').value = '';
      }
    } catch (e) {
      showToast('Failed to log verification', 'error');
    }
  }
</script>
