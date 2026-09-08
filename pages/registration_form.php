<?php
/**
 * Multi-Step Motorcycle Permit Registration Form
 * Steps 1 through 4 with document uploads, camera capture, and QR generation
 */
$lang = $_SESSION['app_lang'] ?? 'am';
$isAmharic = ($lang === 'am');
$subcities = BAHIR_DAR_SUBCITIES;
?>

<div class="max-w-4xl mx-auto">
  
  <!-- Form Header -->
  <div class="command-card p-6 mb-6 bg-white border border-slate-200">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">
          <?= $isAmharic ? 'አዲስ አባልና ተሽከርካሪ መመዝገቢያ' : 'New Member & Vehicle Registration' ?>
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          <?= $isAmharic ? 'የባህርዳር ከተማ ህጋዊ የይለፍ ፈቃድ ማመልከቻ ቅጽ' : 'Official Municipal Permit Application Form' ?>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-500">Step <span id="currentStepNum">1</span> of 4</span>
      </div>
    </div>

    <!-- Stepper Navigation Header -->
    <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
      <div id="stepTab1" class="p-2 rounded-lg bg-blue-900 text-white flex items-center justify-center gap-1.5 transition-colors">
        <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[11px]">1</span>
        <span class="hidden sm:inline"><?= $isAmharic ? 'የአባል መረጃ' : 'Owner Info' ?></span>
      </div>
      <div id="stepTab2" class="p-2 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center gap-1.5 transition-colors">
        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[11px]">2</span>
        <span class="hidden sm:inline"><?= $isAmharic ? 'የሞተር ዝርዝር' : 'Vehicle Specs' ?></span>
      </div>
      <div id="stepTab3" class="p-2 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center gap-1.5 transition-colors">
        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[11px]">3</span>
        <span class="hidden sm:inline"><?= $isAmharic ? 'ሰነዶች' : 'Documents' ?></span>
      </div>
      <div id="stepTab4" class="p-2 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center gap-1.5 transition-colors">
        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[11px]">4</span>
        <span class="hidden sm:inline"><?= $isAmharic ? 'ክፍያና ማጠቃለያ' : 'Payment & Review' ?></span>
      </div>
    </div>
  </div>

  <!-- Registration Form Body -->
  <form id="multiStepForm" class="command-card p-6 bg-white border border-slate-200 space-y-6">

    <!-- STEP 1: Owner / Driver Info -->
    <div id="stepSection1" class="space-y-4">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900">person</span>
        <span><?= $isAmharic ? 'ደረጃ 1: የባለቤቱ / የአሽከርካሪው የግል መረጃ' : 'Step 1: Driver / Owner Information' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'ሙሉ ስም (Full Name) *' : 'Full Name *' ?>
          </label>
          <input type="text" id="fullName" required placeholder="e.g. ተስፋዬ ገብሬ (Tesfaye Gebre)"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'ስልክ ቁጥር (Phone Number) *' : 'Phone Number *' ?>
          </label>
          <input type="tel" id="phone" required placeholder="e.g. 0918123456"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'ክፍለ ከተማ (Sub-City) *' : 'Sub-City Zone *' ?>
          </label>
          <select id="subCity" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
            <?php foreach ($subcities as $sc): ?>
              <option value="<?= $sc['en'] ?>"><?= $isAmharic ? $sc['am'] : $sc['en'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የደም ዓይነት (Blood Group)' : 'Blood Group' ?>
          </label>
          <select id="bloodGroup" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
            <option value="O+">O+</option>
            <option value="A+">A+</option>
            <option value="B+">B+</option>
            <option value="AB+">AB+</option>
            <option value="O-">O-</option>
            <option value="A-">A-</option>
            <option value="B-">B-</option>
            <option value="AB-">AB-</option>
          </select>
        </div>
      </div>
    </div>

    <!-- STEP 2: Vehicle Specs -->
    <div id="stepSection2" class="space-y-4 hidden">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900">two_wheeler</span>
        <span><?= $isAmharic ? 'ደረጃ 2: የተሽከርካሪው ዝርዝር መረጃ' : 'Step 2: Vehicle Specifications' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የተሽከርካሪ ምድብ (Category) *' : 'Vehicle Category *' ?>
          </label>
          <select id="vehicleCategory" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
            <option value="electric"><?= $isAmharic ? 'ኤሌክትሪክ ሞተር (Electric Motorcycle)' : 'Electric Motorcycle' ?></option>
            <option value="gas_under_110cc"><?= $isAmharic ? 'ነዳጅ ከ110cc በታች (Gas < 110cc)' : 'Gas Motorcycle (< 110cc)' ?></option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የሰሌዳ ቁጥር (Plate Number) *' : 'Plate Number *' ?>
          </label>
          <input type="text" id="plateNumber" required placeholder="e.g. 3-AA-98124"
            class="w-full px-3 py-2 text-sm font-mono uppercase font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የሞተር ብራንድ (Brand)' : 'Motor Brand' ?>
          </label>
          <input type="text" id="motorBrand" placeholder="e.g. Super Soco, Yamaha, Haojue"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'ሞዴል (Model)' : 'Motor Model' ?>
          </label>
          <input type="text" id="motorModel" placeholder="e.g. TC-Max, Boxer 100"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የሞተር / ሴሪያል ቁጥር (Engine/Serial No) *' : 'Engine / Serial Number *' ?>
          </label>
          <input type="text" id="engineNo" required placeholder="e.g. ENG-ELEC-44910"
            class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የሻሲ ቁጥር (Chassis Number)' : 'Chassis Number' ?>
          </label>
          <input type="text" id="chassisNumber" placeholder="e.g. CHAS-ET-883921"
            class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>
      </div>
    </div>

    <!-- STEP 3: Document Uploads -->
    <div id="stepSection3" class="space-y-4 hidden">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900">attach_file</span>
        <span><?= $isAmharic ? 'ደረጃ 3: ሰነዶችና ፎቶዎች መስቀያ' : 'Step 3: Upload Identity & Vehicle Documents' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Driver Portrait -->
        <div class="border border-dashed border-slate-300 p-4 rounded-xl text-center bg-slate-50">
          <div class="text-xs font-bold text-slate-700 mb-2"><?= $isAmharic ? 'የባለቤቱ ፎቶ (Driver Portrait) *' : 'Driver Portrait Photo *' ?></div>
          <div id="previewPortraitContainer" class="w-24 h-28 mx-auto bg-white border border-slate-200 rounded-lg mb-2 overflow-hidden flex items-center justify-center">
            <img id="previewPortrait" src="image/app/logo.png" class="w-full h-full object-cover" alt="Portrait">
          </div>
          <input type="file" id="filePortrait" accept="image/*" class="hidden" onchange="handleFileUpload(this, 'previewPortrait', 'userPortraitUrl', 'portraits')">
          <button type="button" onclick="document.getElementById('filePortrait').click()" class="px-3 py-1 bg-blue-900 text-white text-xs font-bold rounded cursor-pointer">
            <?= $isAmharic ? 'ፎቶ ምረጥ / ጫን' : 'Choose Photo' ?>
          </button>
          <input type="hidden" id="userPortraitUrl" value="image/app/logo.png">
        </div>

        <!-- National ID -->
        <div class="border border-dashed border-slate-300 p-4 rounded-xl text-center bg-slate-50">
          <div class="text-xs font-bold text-slate-700 mb-2"><?= $isAmharic ? 'ብሔራዊ መታወቂያ (National ID)' : 'National ID Card' ?></div>
          <div class="w-24 h-28 mx-auto bg-white border border-slate-200 rounded-lg mb-2 overflow-hidden flex items-center justify-center">
            <img id="previewNationalId" src="image/app/flag.jpg" class="w-full h-full object-cover" alt="National ID">
          </div>
          <input type="file" id="fileNationalId" accept="image/*" class="hidden" onchange="handleFileUpload(this, 'previewNationalId', 'nationalIdUrl', 'national_ids')">
          <button type="button" onclick="document.getElementById('fileNationalId').click()" class="px-3 py-1 bg-blue-900 text-white text-xs font-bold rounded cursor-pointer">
            <?= $isAmharic ? 'መታወቂያ ምረጥ' : 'Choose ID' ?>
          </button>
          <input type="hidden" id="nationalIdUrl" value="image/app/flag.jpg">
        </div>

        <!-- Driving License -->
        <div class="border border-dashed border-slate-300 p-4 rounded-xl text-center bg-slate-50">
          <div class="text-xs font-bold text-slate-700 mb-2"><?= $isAmharic ? 'የመንጃ ፍቃድ (Driving License)' : 'Driving License' ?></div>
          <div class="w-24 h-28 mx-auto bg-white border border-slate-200 rounded-lg mb-2 overflow-hidden flex items-center justify-center">
            <img id="previewLicense" src="image/app/flag.jpg" class="w-full h-full object-cover" alt="License">
          </div>
          <input type="file" id="fileLicense" accept="image/*" class="hidden" onchange="handleFileUpload(this, 'previewLicense', 'licenseUrl', 'licenses')">
          <button type="button" onclick="document.getElementById('fileLicense').click()" class="px-3 py-1 bg-blue-900 text-white text-xs font-bold rounded cursor-pointer">
            <?= $isAmharic ? 'ፍቃድ ምረጥ' : 'Choose License' ?>
          </button>
          <input type="hidden" id="licenseUrl" value="image/app/flag.jpg">
        </div>

        <!-- Police Permit -->
        <div class="border border-dashed border-slate-300 p-4 rounded-xl text-center bg-slate-50">
          <div class="text-xs font-bold text-slate-700 mb-2"><?= $isAmharic ? 'የመንቀሳቀሻ ፈቃድ (Police Permit)' : 'Police Permit' ?></div>
          <div class="w-24 h-28 mx-auto bg-white border border-slate-200 rounded-lg mb-2 overflow-hidden flex items-center justify-center">
            <img id="previewPermit" src="image/app/flag.jpg" class="w-full h-full object-cover" alt="Permit">
          </div>
          <input type="file" id="filePermit" accept="image/*" class="hidden" onchange="handleFileUpload(this, 'previewPermit', 'permitUrl', 'permits')">
          <button type="button" onclick="document.getElementById('filePermit').click()" class="px-3 py-1 bg-blue-900 text-white text-xs font-bold rounded cursor-pointer">
            <?= $isAmharic ? 'ሰነድ ምረጥ' : 'Choose Permit' ?>
          </button>
          <input type="hidden" id="permitUrl" value="image/app/flag.jpg">
        </div>

      </div>
    </div>

    <!-- STEP 4: Review, Payment & QR -->
    <div id="stepSection4" class="space-y-4 hidden">
      <h3 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-900">receipt</span>
        <span><?= $isAmharic ? 'ደረጃ 4: የክፍያ መረጃና ማጠቃለያ' : 'Step 4: Payment & Application Review' ?></span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የደረሰኝ ቁጥር (Receipt Number) *' : 'Payment Receipt No *' ?>
          </label>
          <input type="text" id="receiptNumber" placeholder="e.g. REC-9921-2026"
            class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            <?= $isAmharic ? 'የክፍያ መጠን (Amount in ETB)' : 'Payment Amount (ETB)' ?>
          </label>
          <input type="number" id="paymentAmount" value="850.00"
            class="w-full px-3 py-2 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
        </div>
      </div>

      <!-- Live Generated QR & Summary Box -->
      <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
        <div id="summaryQrCode" class="p-2 bg-white rounded-lg border border-slate-200 shrink-0"></div>
        <div class="flex-1 space-y-1 text-xs text-slate-700">
          <div><strong>Driver:</strong> <span id="summaryName">—</span></div>
          <div><strong>Plate:</strong> <span id="summaryPlate" class="font-mono font-bold text-blue-900">—</span></div>
          <div><strong>Category:</strong> <span id="summaryCategory">—</span></div>
          <div><strong>Zone:</strong> <span id="summarySubCity">—</span></div>
          <div class="text-[11px] text-slate-400 mt-2">
            <?= $isAmharic ? 'ማመልከቻው ሲቀርብ የQR ኮድና የፍተሻ ሰርተፊኬት ወዲያውኑ ይዘጋጃል።' : 'QR code and inspection certificate are generated instantly upon registration.' ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Stepper Footer Controls -->
    <div class="flex justify-between items-center pt-4 border-t border-slate-100">
      <button 
        type="button" 
        id="prevStepBtn" 
        onclick="navigateStep(-1)" 
        class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 hidden cursor-pointer">
        <?= $isAmharic ? '← ወደ ኋላ' : '← Previous' ?>
      </button>

      <div class="ml-auto flex gap-2">
        <button 
          type="button" 
          id="nextStepBtn" 
          onclick="navigateStep(1)" 
          class="px-5 py-2 rounded-lg bg-blue-900 text-white text-xs font-bold hover:bg-blue-800 cursor-pointer">
          <?= $isAmharic ? 'ቀጣይ ደረጃ →' : 'Next Step →' ?>
        </button>

        <button 
          type="submit" 
          id="submitFormBtn" 
          class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 hidden cursor-pointer flex items-center gap-2">
          <span class="material-symbols-outlined text-sm">check_circle</span>
          <span><?= $isAmharic ? 'ምዝገባውን አጽድቅና መዝግብ' : 'Submit & Complete' ?></span>
        </button>
      </div>
    </div>

  </form>
</div>

<script>
  let currentStep = 1;

  function updateStepperUI() {
    for (let i = 1; i <= 4; i++) {
      const section = document.getElementById(`stepSection${i}`);
      const tab = document.getElementById(`stepTab${i}`);
      if (i === currentStep) {
        section.classList.remove('hidden');
        tab.classList.remove('bg-slate-100', 'text-slate-600');
        tab.classList.add('bg-blue-900', 'text-white');
      } else {
        section.classList.add('hidden');
        tab.classList.remove('bg-blue-900', 'text-white');
        tab.classList.add('bg-slate-100', 'text-slate-600');
      }
    }

    document.getElementById('currentStepNum').textContent = currentStep;
    document.getElementById('prevStepBtn').classList.toggle('hidden', currentStep === 1);
    document.getElementById('nextStepBtn').classList.toggle('hidden', currentStep === 4);
    document.getElementById('submitFormBtn').classList.toggle('hidden', currentStep !== 4);

    if (currentStep === 4) {
      updateSummary();
    }
  }

  function navigateStep(delta) {
    if (delta > 0 && currentStep === 1) {
      if (!document.getElementById('fullName').value.trim() || !document.getElementById('phone').value.trim()) {
        showToast('Please enter full name and phone number', 'error');
        return;
      }
    }
    if (delta > 0 && currentStep === 2) {
      if (!document.getElementById('plateNumber').value.trim() || !document.getElementById('engineNo').value.trim()) {
        showToast('Please enter plate number and engine serial', 'error');
        return;
      }
    }

    currentStep += delta;
    if (currentStep < 1) currentStep = 1;
    if (currentStep > 4) currentStep = 4;
    updateStepperUI();
  }

  function updateSummary() {
    const plate = document.getElementById('plateNumber').value.toUpperCase();
    const name = document.getElementById('fullName').value;
    const cat = document.getElementById('vehicleCategory').value;
    const sub = document.getElementById('subCity').value;

    document.getElementById('summaryPlate').textContent = plate || '—';
    document.getElementById('summaryName').textContent = name || '—';
    document.getElementById('summaryCategory').textContent = cat === 'electric' ? 'Electric Motorcycle' : 'Gas Motorcycle (< 110cc)';
    document.getElementById('summarySubCity').textContent = sub;

    const qrContainer = document.getElementById('summaryQrCode');
    qrContainer.innerHTML = QRCode.generateSVG(`https://enforcement.gov.et/verify/${plate || 'PREVIEW'}`, 80, 1);
  }

  async function handleFileUpload(input, imgPreviewId, hiddenInputId, category) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const fd = new FormData();
    fd.append('file', file);
    fd.append('category', category);

    try {
      showToast('Uploading document...', 'info');
      const res = await AppAPI.upload(fd);
      if (res.success && res.url) {
        document.getElementById(imgPreviewId).src = res.url;
        document.getElementById(hiddenInputId).value = res.url;
        showToast('Document uploaded successfully', 'success');
      }
    } catch (e) {
      showToast('Failed to upload image', 'error');
    }
  }

  document.getElementById('multiStepForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitFormBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">progress_activity</span> Saving...';

    const payload = {
      full_name: document.getElementById('fullName').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      sub_city: document.getElementById('subCity').value,
      blood_group: document.getElementById('bloodGroup').value,
      vehicle_category: document.getElementById('vehicleCategory').value,
      plate_number: document.getElementById('plateNumber').value.trim().toUpperCase(),
      motor_brand: document.getElementById('motorBrand').value.trim(),
      motorModel: document.getElementById('motorModel').value.trim(),
      engine_or_serial_no: document.getElementById('engineNo').value.trim(),
      chassis_number: document.getElementById('chassisNumber').value.trim(),
      user_portrait_photo: document.getElementById('userPortraitUrl').value,
      national_id_photo: document.getElementById('nationalIdUrl').value,
      driving_license_photo: document.getElementById('licenseUrl').value,
      driving_permit_photo: document.getElementById('permitUrl').value,
      receipt_number: document.getElementById('receiptNumber').value.trim(),
      payment_amount: document.getElementById('paymentAmount').value
    };

    try {
      const res = await AppAPI.post('ajax/registrations.php?action=create', payload);
      if (res.success) {
        showToast('Motorcycle registration submitted successfully!', 'success');
        setTimeout(() => {
          window.location.href = 'index.php?page=records_tables';
        }, 800);
      }
    } catch (err) {
      showToast(err.message || 'Failed to submit registration', 'error');
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-sm">check_circle</span> Submit & Complete';
    }
  });
</script>
