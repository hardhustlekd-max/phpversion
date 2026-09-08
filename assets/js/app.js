/**
 * Enforcement Pro - Master Application Script
 * Vanilla JavaScript + AJAX Fetch API + MySQL Backend
 */

(function() {
  'use strict';

  // Global State Store
  const AppState = {
    user: null,
    lang: localStorage.getItem('permit_lang') || 'am',
    theme: localStorage.getItem('permit_theme') || 'light',
    activeTab: 'dashboard',
    registrations: [],
    officers: [],
    printOrders: [],
    verifications: [],
    unregisteredReports: [],
    paymentReceipts: [],
    settings: {},
    users: [],
    auditLogs: [],
    rolePermissions: {},
    scanner: null,
  };

  // Translations Dictionary
  const I18N = {
    am: {
      appName: 'የትራንስፖርት ፈቃድና ህግ ማስከበሪያ',
      appSubtitle: 'የባህር ዳር ከተማ አስተዳደር ትራንስፖርት ቢሮ',
      dashboard: 'ዳሽቦርድ',
      forms: 'አዲስ ምዝገባ',
      tables: 'የሞተር ሳይክሎች ማህደር',
      todaySubmissions: 'የዛሬ ምዝገባዎች',
      scan: 'ፈጣን ፍተሻ (ስካነር)',
      inspectionReport: 'የፍተሻ ሪፖርት',
      reportUnregistered: 'ያልተመዘገበ ጥቆማ',
      unregisteredList: 'ያልተመዘገቡ ዝርዝር',
      paymentReceipts: 'የክፍያ ደረሰኞች',
      superadmin: 'ዋና አስተዳዳሪ',
      settings: 'ቅንብሮች',
      totalVehicles: 'ጠቅላላ ተሽከርካሪዎች',
      approvedVehicles: 'የተፈቀደላቸው',
      pendingVehicles: 'በመጠባበቅ ላይ',
      rejectedVehicles: 'ውድቅ የተደረጉ',
      todayRegistrations: 'የዛሬ አዳዲስ ምዝገባዎች',
      batchPrintOrders: 'የህትመት ትዕዛዞች',
      roadsideScans: 'የመንገድ ላይ ፍተሻዎች',
      electricVehicles: 'የኤሌክትሪክ ሞተሮች',
      gasVehicles: 'የነዳጅ ሞተሮች (ከ110cc በታች)',
      searchPlaceholder: 'የታርጋ ቁጥር፣ የሞተር ቁጥር ወይም ስም ፈልግ...',
      searchBtn: 'ፈልግ',
      status: 'ሁኔታ',
      actions: 'እርምጃዎች',
      approved: 'የተፈቀደ',
      pending_approval: 'በመጠባበቅ ላይ',
      rejected: 'ውድቅ የተደረገ',
      ordered_print: 'ህትመት የታዘዘ',
      printed: 'የታተመ',
      verified: 'ትክክለኛ',
      warning: 'ማስጠንቀቂያ',
      flagged: 'የታገደ',
      logout: 'ውጣ',
      changePassword: 'የይለፍ ቃል ቀይር',
      profile: 'መገለጫ',
      loading: 'በመጫን ላይ...',
      save: 'አስቀምጥ',
      cancel: 'ሰርዝ',
      printPermit: 'የፈቃድ ወረቀት አትም',
      viewQR: 'የQR ኮድ ካርድ',
      approveBtn: 'ፍቀድ',
      rejectBtn: 'ውድቅ አድርግ',
      rejectionReason: 'ውድቅ የተደረገበት ምክንያት',
      fullName: 'ሙሉ ስም',
      phone: 'ስልክ ቁጥር',
      subCity: 'ክፍለ ከተማ',
      plateNumber: 'የታርጋ ቁጥር',
      chassisNumber: 'የሻሲ ቁጥር',
      engineNumber: 'የሞተር / ሲሪያል ቁጥር',
      category: 'የተሽከርካሪ ዓይነት',
      brand: 'ብራንድ / ሞዴል',
      bloodGroup: 'የደም ዓይነት',
      registeredBy: 'መዝጋቢ',
      date: 'ቀን',
      documents: 'ሰነዶች',
      portraitPhoto: 'የባለቤት ፎቶ',
      nationalIdFront: 'ብሔራዊ መታወቂያ (ፊት)',
      nationalIdBack: 'ብሔራዊ መታወቂያ (ጀርባ)',
      drivingLicense: 'የመንጃ ፍቃድ',
      drivingPermit: 'የመንቀሳቀሻ ፈቃድ (ሊብሬ)',
      receiptScreenshot: 'የክፍያ ደረሰኝ ቅጂ',
      cameraCapture: 'ካሜራ አንሳ',
      uploadFile: 'ፋይል ምረጥ',
      nextStep: 'ቀጣይ ደረጃ',
      prevStep: 'ወደኋላ',
      submitRegistration: 'ምዝገባውን አጠናቅቅ',
      confirmReset: 'ዳግም ማስጀመር ይፈልጋሉ?',
      successSaved: 'በተሳካ ሁኔታ ተቀምጧል!',
      errorOccurred: 'ስህተት ተከስቷል፣ እባክዎ እንደገና ይሞክሩ።',
    },
    en: {
      appName: 'Enforcement Pro - Command Central',
      appSubtitle: 'Bahir Dar City Transport Bureau Enforcement',
      dashboard: 'Dashboard',
      forms: 'New Registration',
      tables: 'Motorcycle Registry',
      todaySubmissions: "Today's Submissions",
      scan: 'Field Scanner',
      inspectionReport: 'Inspection Logs',
      reportUnregistered: 'Report Violation',
      unregisteredList: 'Violation Reports',
      paymentReceipts: 'Payment Receipts',
      superadmin: 'Super Admin',
      settings: 'Settings',
      totalVehicles: 'Total Motorcycles',
      approvedVehicles: 'Approved Permits',
      pendingVehicles: 'Pending Approval',
      rejectedVehicles: 'Rejected Applications',
      todayRegistrations: "Today's Registrations",
      batchPrintOrders: 'Batch Print Orders',
      roadsideScans: 'Roadside Field Scans',
      electricVehicles: 'Electric Motorcycles',
      gasVehicles: 'Gas Engines (<110cc)',
      searchPlaceholder: 'Search plate number, engine serial, or owner name...',
      searchBtn: 'Search',
      status: 'Status',
      actions: 'Actions',
      approved: 'Approved',
      pending_approval: 'Pending Approval',
      rejected: 'Rejected',
      ordered_print: 'Ordered Print',
      printed: 'Printed',
      verified: 'Verified',
      warning: 'Warning',
      flagged: 'Flagged',
      logout: 'Logout',
      changePassword: 'Change Password',
      profile: 'Profile',
      loading: 'Loading...',
      save: 'Save',
      cancel: 'Cancel',
      printPermit: 'Print Permit (A4)',
      viewQR: 'View QR Code',
      approveBtn: 'Approve',
      rejectBtn: 'Reject',
      rejectionReason: 'Rejection Reason',
      fullName: 'Full Name',
      phone: 'Phone Number',
      subCity: 'Sub-City',
      plateNumber: 'Plate Number',
      chassisNumber: 'Chassis Number',
      engineNumber: 'Engine / Serial No',
      category: 'Vehicle Category',
      brand: 'Brand / Model',
      bloodGroup: 'Blood Group',
      registeredBy: 'Registered By',
      date: 'Date',
      documents: 'Documents',
      portraitPhoto: 'Portrait Photo',
      nationalIdFront: 'National ID (Front)',
      nationalIdBack: 'National ID (Back)',
      drivingLicense: 'Driving License',
      drivingPermit: 'Driving Permit (Libre)',
      receiptScreenshot: 'Receipt Screenshot',
      cameraCapture: 'Take Photo',
      uploadFile: 'Upload File',
      nextStep: 'Next Step',
      prevStep: 'Previous Step',
      submitRegistration: 'Submit Registration',
      confirmReset: 'Are you sure you want to reset?',
      successSaved: 'Saved successfully!',
      errorOccurred: 'An error occurred, please try again.',
    }
  };

  function t(key) {
    return I18N[AppState.lang][key] || I18N['en'][key] || key;
  }

  // Toast Notifications
  function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '⚠';
    toast.innerHTML = `<span style="font-weight: bold;">${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // Live EAT GMT+3 Ethiopian & Gregorian Clock
  function updateLiveClock() {
    const clockEl = document.getElementById('header-live-clock');
    if (!clockEl) return;

    if (window.EthiopianCalendar) {
      const eth = window.EthiopianCalendar.toEthiopianDate(new Date());
      const now = new Date();
      const timeStr = eth.timeAm;
      const dateStr = AppState.lang === 'am' ? eth.formattedAm : eth.formattedEn;
      clockEl.innerHTML = `
        <div style="font-weight: 600; color: #38bdf8;">${timeStr}</div>
        <div style="color: #94a3b8; font-size: 0.7rem;">${dateStr}</div>
      `;
    }
  }

  // Fetch Full Data Synchronization from Server
  async function syncAllData() {
    try {
      const res = await fetch('ajax/sync.php');
      const data = await res.json();
      if (data.success) {
        AppState.registrations = data.registrations || [];
        AppState.officers = data.officers || [];
        AppState.printOrders = data.printOrders || [];
        AppState.verifications = data.verifications || [];
        AppState.unregisteredReports = data.unregisteredReports || [];
        AppState.paymentReceipts = data.paymentReceipts || [];
        AppState.settings = data.settings || {};
        AppState.users = data.users || [];
        AppState.auditLogs = data.auditLogs || [];
        AppState.rolePermissions = data.rolePermissions || {};

        // Render current active tab view
        renderActivePage();
      } else {
        console.error('Data sync failed:', data.error);
      }
    } catch (err) {
      console.error('Sync network error:', err);
    }
  }

  // Hash Routing
  function navigateTo(tabName) {
    AppState.activeTab = tabName;
    window.location.hash = tabName;

    // Update navbar active state
    document.querySelectorAll('.nav-tab-btn').forEach(btn => {
      if (btn.getAttribute('data-tab') === tabName) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    renderActivePage();
  }

  // Render Content based on active tab
  function renderActivePage() {
    const mainContainer = document.getElementById('main-page-content');
    if (!mainContainer) return;

    switch (AppState.activeTab) {
      case 'dashboard':
        renderDashboard(mainContainer);
        break;
      case 'forms':
        renderRegistrationForm(mainContainer);
        break;
      case 'tables':
        renderTables(mainContainer);
        break;
      case 'today_submissions':
        renderTodaySubmissions(mainContainer);
        break;
      case 'scan':
        renderScannerPage(mainContainer);
        break;
      case 'inspection_report':
        renderInspectionReport(mainContainer);
        break;
      case 'report_unregistered':
        renderUnregisteredForm(mainContainer);
        break;
      case 'unregistered_list':
        renderUnregisteredList(mainContainer);
        break;
      case 'payment_receipts':
        renderPaymentReceipts(mainContainer);
        break;
      case 'superadmin':
        renderSuperAdmin(mainContainer);
        break;
      case 'settings':
        renderSettings(mainContainer);
        break;
      default:
        renderDashboard(mainContainer);
    }
  }

  // -------------------------------------------------------------
  // 1. DASHBOARD OVERVIEW
  // -------------------------------------------------------------
  function renderDashboard(container) {
    const regs = AppState.registrations;
    const total = regs.length;
    const approved = regs.filter(r => r.status === 'approved' || r.status === 'printed').length;
    const pending = regs.filter(r => r.status === 'pending_approval').length;
    const rejected = regs.filter(r => r.status === 'rejected').length;

    const todayStr = new Date().toISOString().split('T')[0];
    const todayRegs = regs.filter(r => (r.registrationDate || '').startsWith(todayStr)).length;
    const electricCount = regs.filter(r => r.vehicleCategory === 'electric').length;
    const gasCount = regs.filter(r => r.vehicleCategory === 'gas_under_110cc').length;
    const fieldScans = AppState.verifications.length;

    container.innerHTML = `
      <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('dashboard')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">${t('appSubtitle')}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn btn-primary" onclick="window.App.navigateTo('forms')">
            <span>+</span> ${t('forms')}
          </button>
          <button class="btn btn-outline" onclick="window.App.navigateTo('scan')">
            <span>📷</span> ${t('scan')}
          </button>
        </div>
      </div>

      <!-- KPI Cards Grid -->
      <div class="grid-kpi">
        <div class="card" style="border-left: 4px solid #3b82f6;">
          <div style="font-size: 0.85rem; color: var(--color-on-surface-muted);">${t('totalVehicles')}</div>
          <div style="font-size: 2rem; font-weight: 800; margin-top: 0.25rem;">${total}</div>
          <div style="font-size: 0.75rem; color: #3b82f6; margin-top: 0.25rem;">⚡ ${electricCount} ${t('electricVehicles')} | ⛽ ${gasCount} ${t('gasVehicles')}</div>
        </div>

        <div class="card" style="border-left: 4px solid #16a34a;">
          <div style="font-size: 0.85rem; color: var(--color-on-surface-muted);">${t('approvedVehicles')}</div>
          <div style="font-size: 2rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;">${approved}</div>
          <div style="font-size: 0.75rem; color: var(--color-on-surface-muted); margin-top: 0.25rem;">${Math.round((approved / (total || 1)) * 100)}% of total registry</div>
        </div>

        <div class="card" style="border-left: 4px solid #f59e0b;">
          <div style="font-size: 0.85rem; color: var(--color-on-surface-muted);">${t('pendingVehicles')}</div>
          <div style="font-size: 2rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">${pending}</div>
          <div style="font-size: 0.75rem; color: var(--color-on-surface-muted); margin-top: 0.25rem;">Awaiting verification</div>
        </div>

        <div class="card" style="border-left: 4px solid #8b5cf6;">
          <div style="font-size: 0.85rem; color: var(--color-on-surface-muted);">${t('roadsideScans')}</div>
          <div style="font-size: 2rem; font-weight: 800; color: #8b5cf6; margin-top: 0.25rem;">${fieldScans}</div>
          <div style="font-size: 0.75rem; color: var(--color-on-surface-muted); margin-top: 0.25rem;">Verified on road</div>
        </div>
      </div>

      <!-- Recent Registrations & Quick Actions -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 600;">የቅርብ ጊዜ ምዝገባዎች (Recent Applications)</h3>
            <a href="#tables" onclick="window.App.navigateTo('tables')" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none;">ሁሉንም እይ →</a>
          </div>

          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>${t('plateNumber')}</th>
                  <th>${t('fullName')}</th>
                  <th>${t('category')}</th>
                  <th>${t('status')}</th>
                  <th>${t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                ${regs.slice(0, 5).map(r => `
                  <tr>
                    <td><strong style="font-family: monospace; color: #1e40af;">${escapeHtml(r.plateNumber)}</strong></td>
                    <td>${escapeHtml(r.fullName)}</td>
                    <td><span style="font-size: 0.75rem;">${r.vehicleCategory === 'electric' ? '⚡ Electric' : '⛽ Gas (<110cc)'}</span></td>
                    <td><span class="badge badge-${r.status}">${t(r.status)}</span></td>
                    <td>
                      <button class="btn btn-outline btn-sm" onclick="window.App.openPermitModal('${r.id}')">👁 ዝርዝር</button>
                    </td>
                  </tr>
                `).join('')}
                ${regs.length === 0 ? '<tr><td colspan="5" style="text-align: center; color: #94a3b8;">ምንም የተመዘገበ መረጃ የለም (No records found)</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">የፈጣን ፍተሻ መፈለጊያ (Instant Inspector Search)</h3>
          <div class="form-group">
            <label class="form-label">${t('searchPlaceholder')}</label>
            <div style="display: flex; gap: 0.5rem;">
              <input type="text" id="dash-quick-search" class="form-control" placeholder="ምሳሌ: 3-AA-98124 ወይም EN-EL-889921">
              <button class="btn btn-primary" onclick="window.App.handleDashSearch()">
                ${t('searchBtn')}
              </button>
            </div>
          </div>
          <div id="dash-quick-result" style="margin-top: 1rem;"></div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 2. MULTI-STEP REGISTRATION FORM (WIZARD)
  // -------------------------------------------------------------
  let currentStep = 1;
  const newRegData = {
    vehicleCategory: 'electric',
    subCity: 'Belay Zeleke',
    bloodGroup: 'O+',
    status: 'pending_approval',
    userPortraitPhoto: '',
    nationalIdPhoto: '',
    nationalIdBackPhoto: '',
    drivingLicensePhoto: '',
    drivingPermitPhoto: '',
    receiptScreenshot: ''
  };

  function renderRegistrationForm(container) {
    container.innerHTML = `
      <div style="max-width: 900px; margin: 0 auto;">
        <div style="margin-bottom: 1.5rem;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('forms')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">አዲስ የሞተር ሳይክል ፈቃድና የባለቤት መረጃ መመዝገቢያ ቅጽ</p>
        </div>

        <!-- Step Indicator -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; background: var(--color-surface-card); padding: 1rem 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-surface-border);">
          <div style="text-align: center; flex: 1; border-bottom: 3px solid ${currentStep >= 1 ? '#3b82f6' : '#cbd5e1'}; padding-bottom: 0.5rem; font-weight: 600; color: ${currentStep >= 1 ? '#3b82f6' : 'inherit'};">
            1. የባለቤት መረጃ (Personal Info)
          </div>
          <div style="text-align: center; flex: 1; border-bottom: 3px solid ${currentStep >= 2 ? '#3b82f6' : '#cbd5e1'}; padding-bottom: 0.5rem; font-weight: 600; color: ${currentStep >= 2 ? '#3b82f6' : 'inherit'};">
            2. የተሽከርካሪ ዝርዝር (Vehicle Details)
          </div>
          <div style="text-align: center; flex: 1; border-bottom: 3px solid ${currentStep >= 3 ? '#3b82f6' : '#cbd5e1'}; padding-bottom: 0.5rem; font-weight: 600; color: ${currentStep >= 3 ? '#3b82f6' : 'inherit'};">
            3. ሰነዶችና ፎቶ (Documents & Media)
          </div>
          <div style="text-align: center; flex: 1; border-bottom: 3px solid ${currentStep >= 4 ? '#3b82f6' : '#cbd5e1'}; padding-bottom: 0.5rem; font-weight: 600; color: ${currentStep >= 4 ? '#3b82f6' : 'inherit'};">
            4. ማረጋገጫ (Review & Finish)
          </div>
        </div>

        <div class="card" id="step-card-content">
          ${renderStepContent(currentStep)}
        </div>
      </div>
    `;
  }

  function renderStepContent(step) {
    if (step === 1) {
      return `
        <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 1.25rem;">ደረጃ 1፡ የባለቤት ዝርዝር መረጃ (Owner Information)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">${t('fullName')} *</label>
            <input type="text" id="reg-fullName" class="form-control" value="${escapeHtml(newRegData.fullName || '')}" required placeholder="ምሳሌ: አበበ ከበደ">
          </div>
          <div class="form-group">
            <label class="form-label">${t('phone')} *</label>
            <input type="tel" id="reg-phone" class="form-control" value="${escapeHtml(newRegData.phone || '')}" required placeholder="+251 91 234 5678">
          </div>
          <div class="form-group">
            <label class="form-label">${t('subCity')}</label>
            <select id="reg-subCity" class="form-control">
              <option value="Belay Zeleke" ${newRegData.subCity === 'Belay Zeleke' ? 'selected' : ''}>በላይ ዘለቀ (Belay Zeleke)</option>
              <option value="Fasilo" ${newRegData.subCity === 'Fasilo' ? 'selected' : ''}>ፋሲሎ (Fasilo)</option>
              <option value="Dagmawi Minilik" ${newRegData.subCity === 'Dagmawi Minilik' ? 'selected' : ''}>ዳግማዊ ምኒልክ (Dagmawi Minilik)</option>
              <option value="Tana" ${newRegData.subCity === 'Tana' ? 'selected' : ''}>ጣና (Tana)</option>
              <option value="Gish Abay" ${newRegData.subCity === 'Gish Abay' ? 'selected' : ''}>ግሽ ዓባይ (Gish Abay)</option>
              <option value="Shimbit" ${newRegData.subCity === 'Shimbit' ? 'selected' : ''}>ሽምቢት (Shimbit)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">${t('bloodGroup')}</label>
            <select id="reg-bloodGroup" class="form-control">
              <option value="O+" ${newRegData.bloodGroup === 'O+' ? 'selected' : ''}>O+</option>
              <option value="O-" ${newRegData.bloodGroup === 'O-' ? 'selected' : ''}>O-</option>
              <option value="A+" ${newRegData.bloodGroup === 'A+' ? 'selected' : ''}>A+</option>
              <option value="A-" ${newRegData.bloodGroup === 'A-' ? 'selected' : ''}>A-</option>
              <option value="B+" ${newRegData.bloodGroup === 'B+' ? 'selected' : ''}>B+</option>
              <option value="B-" ${newRegData.bloodGroup === 'B-' ? 'selected' : ''}>B-</option>
              <option value="AB+" ${newRegData.bloodGroup === 'AB+' ? 'selected' : ''}>AB+</option>
            </select>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
          <button class="btn btn-primary" onclick="window.App.handleStepNext(1)">
            ${t('nextStep')} →
          </button>
        </div>
      `;
    }

    if (step === 2) {
      return `
        <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 1.25rem;">ደረጃ 2፡ የሞተር ሳይክል ዝርዝር መረጃ (Vehicle Specifications)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">${t('category')} *</label>
            <select id="reg-vehicleCategory" class="form-control">
              <option value="electric" ${newRegData.vehicleCategory === 'electric' ? 'selected' : ''}>⚡ የኤሌክትሪክ ሞተር (Electric Motorcycle)</option>
              <option value="gas_under_110cc" ${newRegData.vehicleCategory === 'gas_under_110cc' ? 'selected' : ''}>⛽ የነዳጅ ሞተር ከ110cc በታች (Gas <110cc)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">${t('plateNumber')} *</label>
            <input type="text" id="reg-plateNumber" class="form-control" value="${escapeHtml(newRegData.plateNumber || '')}" required placeholder="ምሳሌ: 3-BD-99120" style="font-weight: bold; text-transform: uppercase;">
          </div>
          <div class="form-group">
            <label class="form-label">${t('brand')}</label>
            <input type="text" id="reg-motorBrand" class="form-control" value="${escapeHtml(newRegData.motorBrand || '')}" placeholder="ምሳሌ: Super Soco, Bajaj, TVS">
          </div>
          <div class="form-group">
            <label class="form-label">ሞዴል (Model)</label>
            <input type="text" id="reg-motorModel" class="form-control" value="${escapeHtml(newRegData.motorModel || '')}" placeholder="ምሳሌ: TC Max, Boxer 100">
          </div>
          <div class="form-group">
            <label class="form-label">${t('engineNumber')} *</label>
            <input type="text" id="reg-engineOrSerialNo" class="form-control" value="${escapeHtml(newRegData.engineOrSerialNo || '')}" required placeholder="ምሳሌ: EN-EL-778811">
          </div>
          <div class="form-group">
            <label class="form-label">${t('chassisNumber')}</label>
            <input type="text" id="reg-chassisNumber" class="form-control" value="${escapeHtml(newRegData.chassisNumber || '')}" placeholder="ምሳሌ: CH-987654321">
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
          <button class="btn btn-outline" onclick="window.App.handleStepPrev()">
            ← ${t('prevStep')}
          </button>
          <button class="btn btn-primary" onclick="window.App.handleStepNext(2)">
            ${t('nextStep')} →
          </button>
        </div>
      `;
    }

    if (step === 3) {
      return `
        <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 1.25rem;">ደረጃ 3፡ ሰነዶችና ፎቶዎች (Document Uploads & Camera)</h3>
        <p style="color: var(--color-on-surface-muted); font-size: 0.85rem; margin-bottom: 1rem;">እባክዎ የተሽከርካሪውን ሰነዶች በካሜራ በማንሳት ወይም ከኮምፒውተርዎ በመምረጥ ይጫኑ።</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
          ${renderDocUploadBox('userPortraitPhoto', t('portraitPhoto'), newRegData.userPortraitPhoto)}
          ${renderDocUploadBox('nationalIdPhoto', t('nationalIdFront'), newRegData.nationalIdPhoto)}
          ${renderDocUploadBox('nationalIdBackPhoto', t('nationalIdBack'), newRegData.nationalIdBackPhoto)}
          ${renderDocUploadBox('drivingLicensePhoto', t('drivingLicense'), newRegData.drivingLicensePhoto)}
          ${renderDocUploadBox('drivingPermitPhoto', t('drivingPermit'), newRegData.drivingPermitPhoto)}
          ${renderDocUploadBox('receiptScreenshot', t('receiptScreenshot'), newRegData.receiptScreenshot)}
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
          <button class="btn btn-outline" onclick="window.App.handleStepPrev()">
            ← ${t('prevStep')}
          </button>
          <button class="btn btn-primary" onclick="window.App.handleStepNext(3)">
            ${t('nextStep')} →
          </button>
        </div>
      `;
    }

    if (step === 4) {
      return `
        <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 1.25rem;">ደረጃ 4፡ ማረጋገጫና ምዝገባ (Review & Submission)</h3>
        
        <div style="background: var(--color-surface-container); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.9rem;">
            <div><strong>${t('fullName')}:</strong> ${escapeHtml(newRegData.fullName)}</div>
            <div><strong>${t('phone')}:</strong> ${escapeHtml(newRegData.phone)}</div>
            <div><strong>${t('plateNumber')}:</strong> <span style="font-family: monospace; color: #1e40af; font-weight: bold;">${escapeHtml(newRegData.plateNumber)}</span></div>
            <div><strong>${t('category')}:</strong> ${newRegData.vehicleCategory === 'electric' ? '⚡ Electric' : '⛽ Gas'}</div>
            <div><strong>${t('engineNumber')}:</strong> ${escapeHtml(newRegData.engineOrSerialNo)}</div>
            <div><strong>${t('subCity')}:</strong> ${escapeHtml(newRegData.subCity)}</div>
            <div><strong>${t('bloodGroup')}:</strong> ${escapeHtml(newRegData.bloodGroup)}</div>
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
          <button class="btn btn-outline" onclick="window.App.handleStepPrev()">
            ← ${t('prevStep')}
          </button>
          <button class="btn btn-success" id="btn-submit-reg" onclick="window.App.submitNewRegistration()">
            ✓ ${t('submitRegistration')}
          </button>
        </div>
      `;
    }
  }

  function renderDocUploadBox(fieldKey, labelText, currentVal) {
    return `
      <div class="card" style="padding: 1rem; border: 1px dashed var(--color-surface-border); text-align: center;">
        <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem;">${labelText}</div>
        <div style="height: 120px; background: var(--color-surface-container); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; overflow: hidden;">
          ${currentVal ? `<img src="${currentVal}" style="max-height: 100%; max-width: 100%; object-fit: contain;">` : '<span style="color: #94a3b8; font-size: 0.8rem;">ምስል አልተመረጠም (No image)</span>'}
        </div>
        <div style="display: flex; gap: 0.4rem; justify-content: center;">
          <label class="btn btn-outline btn-sm" style="cursor: pointer;">
            📁 ${t('uploadFile')}
            <input type="file" accept="image/*" style="display: none;" onchange="window.App.handleFileUpload('${fieldKey}', this)">
          </label>
          <button class="btn btn-outline btn-sm" type="button" onclick="window.App.openCameraForField('${fieldKey}')">
            📷 ${t('cameraCapture')}
          </button>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 3. TABLES & REGISTRY DIRECTORY
  // -------------------------------------------------------------
  let tableFilter = { status: 'all', subCity: 'all', search: '', category: 'all' };

  function renderTables(container) {
    const regs = AppState.registrations.filter(r => {
      if (tableFilter.status !== 'all' && r.status !== tableFilter.status) return false;
      if (tableFilter.subCity !== 'all' && r.subCity !== tableFilter.subCity) return false;
      if (tableFilter.category !== 'all' && r.vehicleCategory !== tableFilter.category) return false;
      if (tableFilter.search) {
        const q = tableFilter.search.toLowerCase();
        const matchesPlate = (r.plateNumber || '').toLowerCase().includes(q);
        const matchesName = (r.fullName || '').toLowerCase().includes(q);
        const matchesEngine = (r.engineOrSerialNo || '').toLowerCase().includes(q);
        if (!matchesPlate && !matchesName && !matchesEngine) return false;
      }
      return true;
    });

    container.innerHTML = `
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
          <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">${t('tables')}</h1>
            <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የተመዘገቡ ተሽከርካሪዎች ማህደርና ፍተሻ (${regs.length} records)</p>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-primary" onclick="window.App.navigateTo('forms')">+ ${t('forms')}</button>
            <button class="btn btn-outline" onclick="window.App.handleBatchPrintOrder()">🖨 የህትመት ትዕዛዝ (Batch Print)</button>
          </div>
        </div>

        <!-- Filter Bar -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <div>
              <input type="text" id="table-search-input" class="form-control" placeholder="${t('searchPlaceholder')}" value="${escapeHtml(tableFilter.search)}" oninput="window.App.onTableSearch(this.value)">
            </div>
            <div>
              <select class="form-control" onchange="window.App.onTableStatusFilter(this.value)">
                <option value="all" ${tableFilter.status === 'all' ? 'selected' : ''}>ሁሉም ሁኔታዎች (All Statuses)</option>
                <option value="approved" ${tableFilter.status === 'approved' ? 'selected' : ''}>የተፈቀደ (Approved)</option>
                <option value="pending_approval" ${tableFilter.status === 'pending_approval' ? 'selected' : ''}>በመጠባበቅ ላይ (Pending)</option>
                <option value="rejected" ${tableFilter.status === 'rejected' ? 'selected' : ''}>ውድቅ የተደረገ (Rejected)</option>
                <option value="ordered_print" ${tableFilter.status === 'ordered_print' ? 'selected' : ''}>ህትመት የታዘዘ (Ordered Print)</option>
                <option value="printed" ${tableFilter.status === 'printed' ? 'selected' : ''}>የታተመ (Printed)</option>
              </select>
            </div>
            <div>
              <select class="form-control" onchange="window.App.onTableCategoryFilter(this.value)">
                <option value="all" ${tableFilter.category === 'all' ? 'selected' : ''}>ሁሉም ዓይነቶች (All Categories)</option>
                <option value="electric" ${tableFilter.category === 'electric' ? 'selected' : ''}>⚡ Electric</option>
                <option value="gas_under_110cc" ${tableFilter.category === 'gas_under_110cc' ? 'selected' : ''}>⛽ Gas (<110cc)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Registrations Table -->
        <div class="card" style="padding: 0; overflow: hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th style="width: 30px;"><input type="checkbox" id="select-all-reg" onchange="window.App.toggleSelectAll(this)"></th>
                  <th>${t('plateNumber')}</th>
                  <th>${t('fullName')}</th>
                  <th>${t('phone')}</th>
                  <th>${t('category')}</th>
                  <th>${t('engineNumber')}</th>
                  <th>${t('subCity')}</th>
                  <th>${t('status')}</th>
                  <th>${t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                ${regs.map(r => `
                  <tr>
                    <td><input type="checkbox" class="reg-checkbox" value="${r.id}"></td>
                    <td><strong style="font-family: monospace; color: #1e40af;">${escapeHtml(r.plateNumber)}</strong></td>
                    <td>${escapeHtml(r.fullName)}</td>
                    <td>${escapeHtml(r.phone)}</td>
                    <td>${r.vehicleCategory === 'electric' ? '⚡ Electric' : '⛽ Gas'}</td>
                    <td><span style="font-family: monospace; font-size: 0.8rem;">${escapeHtml(r.engineOrSerialNo)}</span></td>
                    <td>${escapeHtml(r.subCity)}</td>
                    <td><span class="badge badge-${r.status}">${t(r.status)}</span></td>
                    <td>
                      <div style="display: flex; gap: 0.35rem;">
                        <button class="btn btn-outline btn-sm" onclick="window.App.openPermitModal('${r.id}')" title="ዝርዝር">👁</button>
                        <button class="btn btn-outline btn-sm" onclick="window.App.openA4PrintModal('${r.id}')" title="A4 ፈቃድ ወረቀት">🖨</button>
                        <button class="btn btn-outline btn-sm" onclick="window.App.openQRCardModal('${r.id}')" title="የQR ካርድ">📱</button>
                        <button class="btn btn-outline btn-sm" onclick="window.App.openStatusChangeModal('${r.id}')" title="ሁኔታ ቀይር">⚙</button>
                      </div>
                    </td>
                  </tr>
                `).join('')}
                ${regs.length === 0 ? '<tr><td colspan="9" style="text-align: center; padding: 2rem; color: #94a3b8;">ምንም መረጃ አልተገኘም (No records match the filter)</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 4. TODAY'S SUBMISSIONS
  // -------------------------------------------------------------
  function renderTodaySubmissions(container) {
    const todayStr = new Date().toISOString().split('T')[0];
    const todayRegs = AppState.registrations.filter(r => (r.registrationDate || '').startsWith(todayStr));

    container.innerHTML = `
      <div>
        <div style="margin-bottom: 1.5rem;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('todaySubmissions')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">ዛሬ የተመዘገቡ አዳዲስ ሞተር ሳይክሎች (${todayRegs.length} entries today)</p>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>${t('plateNumber')}</th>
                  <th>${t('fullName')}</th>
                  <th>${t('phone')}</th>
                  <th>${t('category')}</th>
                  <th>${t('status')}</th>
                  <th>${t('registeredBy')}</th>
                  <th>${t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                ${todayRegs.map(r => `
                  <tr>
                    <td><strong style="font-family: monospace; color: #1e40af;">${escapeHtml(r.plateNumber)}</strong></td>
                    <td>${escapeHtml(r.fullName)}</td>
                    <td>${escapeHtml(r.phone)}</td>
                    <td>${r.vehicleCategory === 'electric' ? '⚡ Electric' : '⛽ Gas'}</td>
                    <td><span class="badge badge-${r.status}">${t(r.status)}</span></td>
                    <td><span style="font-size: 0.8rem; color: #64748b;">${escapeHtml(r.registeredBy)}</span></td>
                    <td>
                      <button class="btn btn-outline btn-sm" onclick="window.App.openPermitModal('${r.id}')">ዝርዝር</button>
                      <button class="btn btn-outline btn-sm" onclick="window.App.openA4PrintModal('${r.id}')">አትም</button>
                    </td>
                  </tr>
                `).join('')}
                ${todayRegs.length === 0 ? '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">ዛሬ የተመዘገበ አዲስ መረጃ የለም (No registrations submitted today)</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 5. ROADSIDE FIELD SCANNER & INSPECTION
  // -------------------------------------------------------------
  function renderScannerPage(container) {
    container.innerHTML = `
      <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 1.5rem; text-align: center;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('scan')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የመንገድ ላይ ፈጣን የQR ኮድና የታርጋ ቁጥር መፈተሻ (Roadside Inspector)</p>
        </div>

        <div class="card" style="text-align: center; margin-bottom: 1.5rem;">
          <div style="position: relative; max-width: 480px; margin: 0 auto; background: #000; border-radius: var(--radius-md); overflow: hidden; height: 320px; display: flex; align-items: center; justify-content: center;">
            <video id="scanner-video" style="width: 100%; height: 100%; object-fit: cover;"></video>
            <div id="scanner-crosshair" style="position: absolute; width: 220px; height: 220px; border: 2px dashed #38bdf8; border-radius: 12px; pointer-events: none;"></div>
          </div>

          <div style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.75rem;">
            <button class="btn btn-primary" id="btn-start-scanner" onclick="window.App.startCameraScanner()">
              📹 ካሜራ ጀምር (Start Camera)
            </button>
            <button class="btn btn-outline" id="btn-stop-scanner" onclick="window.App.stopCameraScanner()" style="display: none;">
              ⏹ ካሜራ አቁም (Stop Camera)
            </button>
          </div>
        </div>

        <!-- Manual Lookup Fallback -->
        <div class="card">
          <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 0.75rem;">በታርጋ ወይም በሞተር ቁጥር ይፈልጉ (Manual Lookup)</h3>
          <div style="display: flex; gap: 0.5rem;">
            <input type="text" id="scanner-manual-input" class="form-control" placeholder="ምሳሌ: 3-AA-98124 ወይም EN-EL-889921">
            <button class="btn btn-primary" onclick="window.App.handleManualScanSearch()">${t('searchBtn')}</button>
          </div>
          <div id="scanner-result-box" style="margin-top: 1.25rem;"></div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 6. INSPECTION REPORT LOGS
  // -------------------------------------------------------------
  function renderInspectionReport(container) {
    const logs = AppState.verifications;

    container.innerHTML = `
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
          <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">${t('inspectionReport')}</h1>
            <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የመንገድ ላይ ፍተሻዎች ታሪክና ሪፖርት (${logs.length} inspection records)</p>
          </div>
          <button class="btn btn-outline" onclick="window.print()">🖨 ሪፖርቱን አትም (Print)</button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>ቀንና ሰዓት (Timestamp)</th>
                  <th>${t('plateNumber')}</th>
                  <th>${t('fullName')}</th>
                  <th>${t('status')}</th>
                  <th>የፍተሻ ውጤት (Verification)</th>
                  <th>ፍተሻ ያደረገው መኮንን</th>
                  <th>ማስታወሻ</th>
                </tr>
              </thead>
              <tbody>
                ${logs.map(l => `
                  <tr>
                    <td><span style="font-size: 0.8rem; color: #64748b;">${escapeHtml(l.scannedAt)}</span></td>
                    <td><strong style="font-family: monospace; color: #1e40af;">${escapeHtml(l.plateNumber)}</strong></td>
                    <td>${escapeHtml(l.fullName)}</td>
                    <td><span class="badge badge-${l.permitStatus}">${t(l.permitStatus)}</span></td>
                    <td><span class="badge badge-${l.verificationStatus}">${t(l.verificationStatus)}</span></td>
                    <td>${escapeHtml(l.officerBadgeId || 'OFFICER-8842')}</td>
                    <td><span style="font-size: 0.85rem;">${escapeHtml(l.officerNotes || 'Routine check passed')}</span></td>
                  </tr>
                `).join('')}
                ${logs.length === 0 ? '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">ምንም የፍተሻ ታሪክ አልተመዘገበም (No roadside verification logs)</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 7. REPORT UNREGISTERED VEHICLE (VIOLATION FORM)
  // -------------------------------------------------------------
  function renderUnregisteredForm(container) {
    container.innerHTML = `
      <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 1.5rem;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('reportUnregistered')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">ህገ-ወጥ ወይም ያልተመዘገበ የሞተር ሳይክል ጥቆማና የእግድ መመዝገቢያ ቅጽ</p>
        </div>

        <div class="card">
          <form id="form-unregistered-report" onsubmit="window.App.submitUnregisteredReport(event)">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
              <div class="form-group">
                <label class="form-label">${t('plateNumber')} (ካለ)</label>
                <input type="text" id="unreg-plate" class="form-control" placeholder="ምሳሌ: 2-BD-12345 ወይም የታርጋ አልባ">
              </div>
              <div class="form-group">
                <label class="form-label">የአሽከርካሪ ስም</label>
                <input type="text" id="unreg-driver" class="form-control" placeholder="የአሽከርካሪ ስም">
              </div>
              <div class="form-group">
                <label class="form-label">${t('phone')}</label>
                <input type="tel" id="unreg-phone" class="form-control" placeholder="+251 91 123 4567">
              </div>
              <div class="form-group">
                <label class="form-label">${t('subCity')}</label>
                <select id="unreg-subcity" class="form-control">
                  <option value="Belay Zeleke">በላይ ዘለቀ (Belay Zeleke)</option>
                  <option value="Fasilo">ፋሲሎ (Fasilo)</option>
                  <option value="Dagmawi Minilik">ዳግማዊ ምኒልክ (Dagmawi Minilik)</option>
                  <option value="Tana">ጣና (Tana)</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">የተያዘበት ቦታ (Checkpoint / Location) *</label>
                <input type="text" id="unreg-location" class="form-control" required placeholder="ምሳሌ: ቀበሌ 04 የፍተሻ ጣቢያ">
              </div>
              <div class="form-group">
                <label class="form-label">${t('engineNumber')}</label>
                <input type="text" id="unreg-engine" class="form-control" placeholder="የሞተር ቁጥር">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">የጥሰት ዝርዝር ማብራሪያ (Violation Notes) *</label>
              <textarea id="unreg-notes" class="form-control" rows="3" required placeholder="የተሽከርካሪው ሁኔታ፣ ያለ ፈቃድ ማሽከርከር፣ የታርጋ አለመኖር..."></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">የማስረጃ ፎቶ (Evidence Photo)</label>
              <input type="file" id="unreg-photo" class="form-control" accept="image/*">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
              <button type="button" class="btn btn-outline" onclick="window.App.navigateTo('unregistered_list')">${t('cancel')}</button>
              <button type="submit" class="btn btn-danger">🚨 ጥቆማውን መዝግብ (Submit Citation)</button>
            </div>
          </form>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 8. UNREGISTERED VEHICLES LIST
  // -------------------------------------------------------------
  function renderUnregisteredList(container) {
    const list = AppState.unregisteredReports;

    container.innerHTML = `
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
          <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">${t('unregisteredList')}</h1>
            <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">ያልተመዘገቡና የታገዱ ሞተር ሳይክሎች ዝርዝር (${list.length} citations)</p>
          </div>
          <button class="btn btn-danger" onclick="window.App.navigateTo('report_unregistered')">+ አዲስ ጥቆማ መዝግብ</button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>ቀን (Date)</th>
                  <th>${t('plateNumber')}</th>
                  <th>የአሽከርካሪ ስም</th>
                  <th>${t('subCity')}</th>
                  <th>የተያዘበት ቦታ</th>
                  <th>ሁኔታ</th>
                  <th>እርምጃ</th>
                </tr>
              </thead>
              <tbody>
                ${list.map(ur => `
                  <tr>
                    <td><span style="font-size: 0.8rem; color: #64748b;">${escapeHtml(ur.reportedAt)}</span></td>
                    <td><strong style="font-family: monospace; color: #dc2626;">${escapeHtml(ur.plateNumber || 'ታርጋ የሌለው')}</strong></td>
                    <td>${escapeHtml(ur.driverName || 'ያልታወቀ')}</td>
                    <td>${escapeHtml(ur.subCity)}</td>
                    <td>${escapeHtml(ur.locationName)}</td>
                    <td><span class="badge badge-warning">${escapeHtml(ur.status)}</span></td>
                    <td>
                      <button class="btn btn-outline btn-sm" onclick="window.App.resolveCitation('${ur.id}')">ፍታ (Resolve)</button>
                    </td>
                  </tr>
                `).join('')}
                ${list.length === 0 ? '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">ምንም የተመዘገበ ጥቆማ የለም (No unregistered violations)</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 9. PAYMENT RECEIPTS LEDGER & VALIDITY MONITOR
  // -------------------------------------------------------------
  function renderPaymentReceipts(container) {
    const receipts = AppState.paymentReceipts;

    container.innerHTML = `
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
          <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">${t('paymentReceipts')}</h1>
            <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የከተማ አስተዳደር ዓመታዊ የፈቃድና የትራንስፖርት ክፍያ ደረሰኞች</p>
          </div>
          <button class="btn btn-primary" onclick="window.App.openNewPaymentModal()">+ አዲስ ደረሰኝ መዝግብ</button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>የደረሰኝ ቁጥር</th>
                  <th>የባለቤት ስም</th>
                  <th>${t('plateNumber')}</th>
                  <th>የክፍያ መጠን (ETB)</th>
                  <th>የተከፈለበት ቀን</th>
                  <th>የሚያበቃበት ቀን</th>
                  <th>ሁኔታ</th>
                  <th>እርምጃ</th>
                </tr>
              </thead>
              <tbody>
                ${receipts.map(rc => {
                  const isExpired = new Date(rc.expirationDate) < new Date();
                  return `
                    <tr>
                      <td><strong style="font-family: monospace; color: #0284c7;">${escapeHtml(rc.receiptNumber)}</strong></td>
                      <td>${escapeHtml(rc.ownerName)}</td>
                      <td><span style="font-family: monospace;">${escapeHtml(rc.plateNumber || '—')}</span></td>
                      <td><strong>${Number(rc.amount).toFixed(2)} ETB</strong></td>
                      <td>${escapeHtml(rc.paymentDate)}</td>
                      <td>${escapeHtml(rc.expirationDate)}</td>
                      <td>
                        <span class="badge ${isExpired ? 'badge-rejected' : 'badge-approved'}">
                          ${isExpired ? 'ጊዜው ያለፈበት (Expired)' : 'ትክክለኛ (Active)'}
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-outline btn-sm" onclick="window.App.deletePaymentReceipt('${rc.id}')">አጥፋ</button>
                      </td>
                    </tr>
                  `;
                }).join('')}
                ${receipts.length === 0 ? '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">ምንም የተመዘገበ ደረሰኝ የለም</td></tr>' : ''}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 10. SUPER ADMIN INTERFACE (USERS, RBAC, RESET)
  // -------------------------------------------------------------
  function renderSuperAdmin(container) {
    const users = AppState.users;

    container.innerHTML = `
      <div>
        <div style="margin-bottom: 1.5rem;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('superadmin')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የስርዓቱ የበላይ ተቆጣጣሪ ማዕከልና የተጠቃሚዎች ማስተዳደሪያ</p>
        </div>

        <!-- Sub-Tabs inside Super Admin -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-surface-border); padding-bottom: 0.5rem;">
          <button class="btn btn-primary btn-sm" id="sadmin-tab-users" onclick="window.App.showSuperAdminTab('users')">ተጠቃሚዎች (Users)</button>
          <button class="btn btn-outline btn-sm" id="sadmin-tab-rbac" onclick="window.App.showSuperAdminTab('rbac')">የፍቃድ ማትሪክስ (RBAC)</button>
          <button class="btn btn-outline btn-sm" id="sadmin-tab-audit" onclick="window.App.showSuperAdminTab('audit')">የደህንነት ምዝግብ (Audit Logs)</button>
          <button class="btn btn-outline btn-sm" id="sadmin-tab-backup" onclick="window.App.showSuperAdminTab('backup')">ዳታቤዝ ባክአፕና ሪሴት (Backup & Reset)</button>
        </div>

        <div id="superadmin-tab-content">
          ${renderSuperAdminUsersTab()}
        </div>
      </div>
    `;
  }

  function renderSuperAdminUsersTab() {
    const users = AppState.users;
    return `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 600;">የስርዓቱ ተጠቃሚዎች (${users.length})</h3>
        <button class="btn btn-primary btn-sm" onclick="window.App.openNewUserModal()">+ አዲስ ተጠቃሚ መዝግብ</button>
      </div>

      <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>የመለያ ቁጥር (Badge ID)</th>
                <th>ሙሉ ስም</th>
                <th>ኢሜይል</th>
                <th>ሚና (Role)</th>
                <th>ክፍለ ከተማ</th>
                <th>ሁኔታ</th>
                <th>እርምጃ</th>
              </tr>
            </thead>
            <tbody>
              ${users.map(u => `
                <tr>
                  <td><strong style="font-family: monospace; color: #1e40af;">${escapeHtml(u.badgeId)}</strong></td>
                  <td>${escapeHtml(u.fullName)}</td>
                  <td>${escapeHtml(u.email)}</td>
                  <td><span class="badge badge-printed">${escapeHtml(u.role)}</span></td>
                  <td>${escapeHtml(u.subCity || 'Central')}</td>
                  <td><span class="badge ${u.status === 'active' ? 'badge-approved' : 'badge-rejected'}">${escapeHtml(u.status)}</span></td>
                  <td>
                    <button class="btn btn-outline btn-sm" onclick="window.App.editUserModal('${u.id}')">አስተካክል</button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // 11. SETTINGS PAGE
  // -------------------------------------------------------------
  function renderSettings(container) {
    const s = AppState.settings;

    container.innerHTML = `
      <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 1.5rem;">
          <h1 style="font-size: 1.5rem; font-weight: 700;">${t('settings')}</h1>
          <p style="color: var(--color-on-surface-muted); font-size: 0.9rem;">የመስሪያ ቤቱና የመኮንኑ የስራ ቅንብሮች</p>
        </div>

        <div class="card">
          <form onsubmit="window.App.saveSettings(event)">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
              <div class="form-group">
                <label class="form-label">የሃላፊው ስም (Officer Name)</label>
                <input type="text" id="set-officerName" class="form-control" value="${escapeHtml(s.officerName || '')}">
              </div>
              <div class="form-group">
                <label class="form-label">የስራ ክፍል (Department)</label>
                <input type="text" id="set-department" class="form-control" value="${escapeHtml(s.department || '')}">
              </div>
              <div class="form-group">
                <label class="form-label">የክፍለ ከተማ ጽ/ቤት (Office)</label>
                <input type="text" id="set-subCityOffice" class="form-control" value="${escapeHtml(s.subCityOffice || '')}">
              </div>
              <div class="form-group">
                <label class="form-label">ነባሪ ፕሪንተር (Default Printer)</label>
                <input type="text" id="set-printer" class="form-control" value="${escapeHtml(s.defaultPrinter || '')}">
              </div>
              <div class="form-group">
                <label class="form-label">የቀን አቆጣጠር ስርዓት (Calendar System)</label>
                <select id="set-calendarSystem" class="form-control">
                  <option value="ethiopian" ${s.calendarSystem === 'ethiopian' ? 'selected' : ''}>የኢትዮጵያ ዘመን አቆጣጠር (Ethiopian Calendar)</option>
                  <option value="gregorian" ${s.calendarSystem === 'gregorian' ? 'selected' : ''}>Gregorian Calendar</option>
                </select>
              </div>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px solid var(--color-surface-border); padding-top: 1.5rem; display: flex; justify-content: flex-end;">
              <button type="submit" class="btn btn-primary">${t('save')}</button>
            </div>
          </form>
        </div>
      </div>
    `;
  }

  // -------------------------------------------------------------
  // HELPER FUNCTIONS & MODAL HANDLERS
  // -------------------------------------------------------------
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Open Permit Detail Modal
  function openPermitModal(id) {
    const reg = AppState.registrations.find(r => r.id === id);
    if (!reg) return;

    const modal = document.getElementById('global-modal');
    const modalBody = document.getElementById('global-modal-body');
    const modalTitle = document.getElementById('global-modal-title');

    modalTitle.innerText = `${reg.fullName} — ${reg.plateNumber}`;
    modalBody.innerHTML = `
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div><strong>የባለቤት ስም:</strong> ${escapeHtml(reg.fullName)}</div>
        <div><strong>ስልክ ቁጥር:</strong> ${escapeHtml(reg.phone)}</div>
        <div><strong>የታርጋ ቁጥር:</strong> <span style="font-family: monospace; font-weight: bold; color: #1e40af;">${escapeHtml(reg.plateNumber)}</span></div>
        <div><strong>የሞተር ቁጥር:</strong> ${escapeHtml(reg.engineOrSerialNo)}</div>
        <div><strong>የሻሲ ቁጥር:</strong> ${escapeHtml(reg.chassisNumber || '—')}</div>
        <div><strong>ክፍለ ከተማ:</strong> ${escapeHtml(reg.subCity)}</div>
        <div><strong>ሁኔታ:</strong> <span class="badge badge-${reg.status}">${t(reg.status)}</span></div>
        <div><strong>የተመዘገበበት ቀን:</strong> ${escapeHtml(reg.registrationDate)}</div>
      </div>

      <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">የተያያዙ ሰነዶች (Attached Documents)</h4>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem;">
        ${reg.userPortraitPhoto ? `<div><small>የባለቤት ፎቶ</small><img src="${reg.userPortraitPhoto}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;"></div>` : ''}
        ${reg.nationalIdPhoto ? `<div><small>ብሔራዊ መታወቂያ (ፊት)</small><img src="${reg.nationalIdPhoto}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;"></div>` : ''}
        ${reg.drivingLicensePhoto ? `<div><small>የመንጃ ፍቃድ</small><img src="${reg.drivingLicensePhoto}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;"></div>` : ''}
        ${reg.drivingPermitPhoto ? `<div><small>የመንቀሳቀሻ ፈቃድ</small><img src="${reg.drivingPermitPhoto}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;"></div>` : ''}
      </div>

      <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
        <button class="btn btn-outline" onclick="window.App.openA4PrintModal('${reg.id}')">🖨 A4 ፈቃድ አትም</button>
        <button class="btn btn-primary" onclick="window.App.closeModal()">ዝጋ</button>
      </div>
    `;

    modal.classList.add('show');
  }

  // Open A4 Permit Paper Printable Preview
  function openA4PrintModal(id) {
    const reg = AppState.registrations.find(r => r.id === id);
    if (!reg) return;

    const modal = document.getElementById('global-modal');
    const modalBody = document.getElementById('global-modal-body');
    const modalTitle = document.getElementById('global-modal-title');

    modalTitle.innerText = `A4 የፈቃድ ወረቀት — ${reg.plateNumber}`;
    modalBody.innerHTML = `
      <div id="printable-permit-area" style="background: #ffffff; color: #000000; padding: 25px; border: 2px solid #000; font-family: 'Abyssinica SIL', sans-serif;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 15px;">
          <img src="public/logo.png" style="width: 65px; height: 65px; object-fit: contain;">
          <div style="text-align: center;">
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">የባህር ዳር ከተማ አስተዳደር ትራንስፖርት ቢሮ</h2>
            <h3 style="font-size: 1rem; font-weight: 700; margin: 3px 0;">የሞተር ሳይክል ሕጋዊ የመንቀሳቀሻ ፈቃድ ሰርተፊኬት</h3>
            <div style="font-size: 0.8rem;">BAHIR DAR CITY TRANSPORT & MOBILITY BUREAU - PERMIT CERTIFICATE</div>
          </div>
          <div id="print-qr-code" style="width: 65px; height: 65px;"></div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; font-size: 0.95rem; line-height: 1.8;">
          <div>
            <div><strong>የፈቃድ ቁጥር (Permit No):</strong> ${reg.id}</div>
            <div><strong>የባለቤት ሙሉ ስም (Owner):</strong> ${reg.fullName}</div>
            <div><strong>የታርጋ ቁጥር (Plate No):</strong> <span style="font-size: 1.15rem; font-weight: bold; border: 1px solid #000; padding: 1px 6px;">${reg.plateNumber}</span></div>
            <div><strong>የሞተር/ሲሪያል ቁጥር (Engine No):</strong> ${reg.engineOrSerialNo}</div>
            <div><strong>የተሽከርካሪ ምድብ (Category):</strong> ${reg.vehicleCategory === 'electric' ? 'የኤሌክትሪክ ሞተር (Electric)' : 'የነዳጅ ሞተር (Gas <110cc)'}</div>
            <div><strong>ክፍለ ከተማ (Sub-City):</strong> ${reg.subCity}</div>
            <div><strong>የተመዘገበበት ቀን (Date):</strong> ${reg.registrationDate}</div>
            <div><strong>የፈቃድ ሁኔታ:</strong> <span style="font-weight: bold; text-transform: uppercase;">${reg.status}</span></div>
          </div>
          <div style="text-align: center;">
            ${reg.userPortraitPhoto ? `<img src="${reg.userPortraitPhoto}" style="width: 130px; height: 150px; object-fit: cover; border: 1px solid #000;">` : '<div style="width: 130px; height: 150px; border: 1px dashed #000; display: flex; align-items: center; justify-content: center;">ፎቶ</div>'}
            <div style="margin-top: 5px; font-size: 0.8rem;">የባለቤት ፎቶ</div>
          </div>
        </div>

        <div style="margin-top: 30px; border-top: 1px dashed #000; padding-top: 15px; display: flex; justify-content: space-between; font-size: 0.85rem;">
          <div>ያረጋገጠው ሃላፊ ፊርማ፡ ___________________</div>
          <div>ማህተም (Bureau Stamp)</div>
          <div>ቀን፡ ${new Date().toLocaleDateString()}</div>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;" class="no-print">
        <button class="btn btn-outline" onclick="window.App.closeModal()">ዝጋ</button>
        <button class="btn btn-primary" onclick="window.print()">🖨 አትም (Print Paper)</button>
      </div>
    `;

    modal.classList.add('show');

    // Generate QR in printable area
    setTimeout(() => {
      new window.QRCode('print-qr-code', {
        text: JSON.stringify({ id: reg.id, plateNumber: reg.plateNumber, fullName: reg.fullName }),
        width: 65,
        height: 65
      });
    }, 100);
  }

  // Open QR Code Card Modal
  function openQRCardModal(id) {
    const reg = AppState.registrations.find(r => r.id === id);
    if (!reg) return;

    const modal = document.getElementById('global-modal');
    const modalBody = document.getElementById('global-modal-body');
    const modalTitle = document.getElementById('global-modal-title');

    modalTitle.innerText = `የQR ኮድ ካርድ — ${reg.plateNumber}`;
    modalBody.innerHTML = `
      <div style="text-align: center; padding: 1.5rem; background: #ffffff; color: #000; border-radius: 12px; border: 2px solid #1e40af; max-width: 320px; margin: 0 auto;">
        <div style="font-weight: 700; font-size: 1rem; color: #1e40af;">ባህር ዳር ትራንስፖርት ቢሮ</div>
        <div style="font-size: 0.75rem; margin-bottom: 1rem;">MUNICIPAL MOTORCYCLE PERMIT</div>
        <div id="modal-qr-target" style="display: flex; justify-content: center; margin-bottom: 1rem;"></div>
        <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800; letter-spacing: 1px;">${escapeHtml(reg.plateNumber)}</div>
        <div style="font-weight: 600; font-size: 0.95rem; margin-top: 0.25rem;">${escapeHtml(reg.fullName)}</div>
        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">${reg.vehicleCategory === 'electric' ? '⚡ Electric Motorcycle' : '⛽ Gas (<110cc)'}</div>
      </div>
      <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
        <button class="btn btn-primary" onclick="window.App.closeModal()">ዝጋ</button>
      </div>
    `;

    modal.classList.add('show');

    setTimeout(() => {
      new window.QRCode('modal-qr-target', {
        text: JSON.stringify({ id: reg.id, plateNumber: reg.plateNumber, fullName: reg.fullName, engine: reg.engineOrSerialNo }),
        width: 160,
        height: 160
      });
    }, 100);
  }

  // Open Status Change Modal
  function openStatusChangeModal(id) {
    const reg = AppState.registrations.find(r => r.id === id);
    if (!reg) return;

    const modal = document.getElementById('global-modal');
    const modalBody = document.getElementById('global-modal-body');
    const modalTitle = document.getElementById('global-modal-title');

    modalTitle.innerText = `የፈቃድ ሁኔታ ማስተካከያ — ${reg.plateNumber}`;
    modalBody.innerHTML = `
      <div class="form-group">
        <label class="form-label">${t('status')}</label>
        <select id="modal-status-select" class="form-control" onchange="window.App.onStatusChangeSelect(this.value)">
          <option value="approved" ${reg.status === 'approved' ? 'selected' : ''}>የተፈቀደ (Approved)</option>
          <option value="pending_approval" ${reg.status === 'pending_approval' ? 'selected' : ''}>በመጠባበቅ ላይ (Pending)</option>
          <option value="rejected" ${reg.status === 'rejected' ? 'selected' : ''}>ውድቅ የተደረገ (Rejected)</option>
          <option value="ordered_print" ${reg.status === 'ordered_print' ? 'selected' : ''}>ህትመት የታዘዘ (Ordered Print)</option>
          <option value="printed" ${reg.status === 'printed' ? 'selected' : ''}>የታተመ (Printed)</option>
        </select>
      </div>

      <div class="form-group" id="rejection-reason-group" style="${reg.status === 'rejected' ? '' : 'display: none;'}">
        <label class="form-label">${t('rejectionReason')}</label>
        <textarea id="modal-rejection-reason" class="form-control" rows="3" placeholder="ውድቅ የተደረገበትን ምክንያት እዚህ ይግለጹ...">${escapeHtml(reg.rejectionReason || '')}</textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
        <button class="btn btn-outline" onclick="window.App.closeModal()">${t('cancel')}</button>
        <button class="btn btn-primary" onclick="window.App.saveStatusChange('${reg.id}')">${t('save')}</button>
      </div>
    `;

    modal.classList.add('show');
  }

  function closeModal() {
    const modal = document.getElementById('global-modal');
    if (modal) modal.classList.remove('show');
  }

  // Public API Exported to window.App
  window.App = {
    state: AppState,
    navigateTo,
    showToast,
    closeModal,
    openPermitModal,
    openA4PrintModal,
    openQRCardModal,
    openStatusChangeModal,

    // Step Wizard Handlers
    handleStepNext: function(current) {
      if (current === 1) {
        newRegData.fullName = document.getElementById('reg-fullName').value.trim();
        newRegData.phone = document.getElementById('reg-phone').value.trim();
        newRegData.subCity = document.getElementById('reg-subCity').value;
        newRegData.bloodGroup = document.getElementById('reg-bloodGroup').value;

        if (!newRegData.fullName || !newRegData.phone) {
          showToast('እባክዎ ሙሉ ስም እና ስልክ ቁጥር ያስገቡ', 'error');
          return;
        }
      } else if (current === 2) {
        newRegData.vehicleCategory = document.getElementById('reg-vehicleCategory').value;
        newRegData.plateNumber = document.getElementById('reg-plateNumber').value.trim().toUpperCase();
        newRegData.motorBrand = document.getElementById('reg-motorBrand').value.trim();
        newRegData.motorModel = document.getElementById('reg-motorModel').value.trim();
        newRegData.engineOrSerialNo = document.getElementById('reg-engineOrSerialNo').value.trim();
        newRegData.chassisNumber = document.getElementById('reg-chassisNumber').value.trim();

        if (!newRegData.plateNumber || !newRegData.engineOrSerialNo) {
          showToast('እባክዎ የታርጋ ቁጥር እና የሞተር ቁጥር ያስገቡ', 'error');
          return;
        }
      }

      currentStep++;
      renderActivePage();
    },

    handleStepPrev: function() {
      if (currentStep > 1) {
        currentStep--;
        renderActivePage();
      }
    },

    handleFileUpload: async function(fieldKey, input) {
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      const formData = new FormData();
      formData.append('file', file);
      formData.append('folder', 'permits');

      try {
        const res = await fetch('ajax/upload.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
          newRegData[fieldKey] = json.url;
          showToast('ምስሉ በተሳካ ሁኔታ ተጭኗል!', 'success');
          renderActivePage();
        } else {
          showToast(json.error || 'ስህተት ተከስቷል', 'error');
        }
      } catch (err) {
        showToast('Upload error', 'error');
      }
    },

    openCameraForField: function(fieldKey) {
      // Simulate/trigger camera snapshot capture via file input with capture="user"
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/*';
      input.capture = 'environment';
      input.onchange = (e) => window.App.handleFileUpload(fieldKey, input);
      input.click();
    },

    submitNewRegistration: async function() {
      const btn = document.getElementById('btn-submit-reg');
      if (btn) btn.disabled = true;

      newRegData.id = 'REG-BD-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000);
      newRegData.registeredBy = (AppState.user && AppState.user.badgeId) || 'CLERK-001';
      newRegData.registrationDate = new Date().toISOString().split('T')[0];

      try {
        const res = await fetch('ajax/registrations.php?action=save', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(newRegData)
        });
        const json = await res.json();
        if (json.success) {
          showToast(t('successSaved'), 'success');
          await syncAllData();
          currentStep = 1;
          navigateTo('tables');
        } else {
          showToast(json.error || 'ስህተት ተከስቷል', 'error');
        }
      } catch (err) {
        showToast('Submission error', 'error');
      } finally {
        if (btn) btn.disabled = false;
      }
    },

    // Table Filtering
    onTableSearch: function(val) {
      tableFilter.search = val;
      renderTables(document.getElementById('main-page-content'));
    },
    onTableStatusFilter: function(val) {
      tableFilter.status = val;
      renderTables(document.getElementById('main-page-content'));
    },
    onTableCategoryFilter: function(val) {
      tableFilter.category = val;
      renderTables(document.getElementById('main-page-content'));
    },

    // Status Change
    onStatusChangeSelect: function(status) {
      const box = document.getElementById('rejection-reason-group');
      if (box) {
        box.style.display = status === 'rejected' ? 'block' : 'none';
      }
    },
    saveStatusChange: async function(id) {
      const status = document.getElementById('modal-status-select').value;
      const rejectionReason = document.getElementById('modal-rejection-reason')?.value || null;

      try {
        const res = await fetch('ajax/registrations.php?action=update_status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id, status, rejectionReason })
        });
        const json = await res.json();
        if (json.success) {
          showToast('ሁኔታው ተቀይሯል!', 'success');
          closeModal();
          await syncAllData();
        } else {
          showToast(json.error || 'ስህተት ተከስቷል', 'error');
        }
      } catch (err) {
        showToast('Update status error', 'error');
      }
    },

    // Scanner
    startCameraScanner: async function() {
      try {
        if (!AppState.scanner) {
          AppState.scanner = new window.PermitScanner('scanner-video', (qrText) => {
            window.App.handleScanSuccess(qrText);
          });
        }
        await AppState.scanner.start();
        document.getElementById('btn-start-scanner').style.display = 'none';
        document.getElementById('btn-stop-scanner').style.display = 'inline-flex';
      } catch (err) {
        showToast('ካሜራ መክፈት አልተቻለም (Camera access denied or unavailable)', 'error');
      }
    },
    stopCameraScanner: function() {
      if (AppState.scanner) {
        AppState.scanner.stop();
      }
      document.getElementById('btn-start-scanner').style.display = 'inline-flex';
      document.getElementById('btn-stop-scanner').style.display = 'none';
    },
    handleScanSuccess: function(qrText) {
      showToast('QR ኮድ ተገኝቷል!', 'success');
      this.stopCameraScanner();

      // Look up registration by payload
      let regId = qrText;
      try {
        const parsed = JSON.parse(qrText);
        regId = parsed.id || parsed.plateNumber || qrText;
      } catch (e) {}

      this.lookupAndShowInspection(regId);
    },
    handleManualScanSearch: function() {
      const val = document.getElementById('scanner-manual-input').value.trim();
      if (!val) return;
      this.lookupAndShowInspection(val);
    },
    handleDashSearch: function() {
      const val = document.getElementById('dash-quick-search').value.trim();
      if (!val) return;
      const found = AppState.registrations.find(r => 
        (r.plateNumber || '').toLowerCase().includes(val.toLowerCase()) ||
        (r.engineOrSerialNo || '').toLowerCase().includes(val.toLowerCase()) ||
        (r.fullName || '').toLowerCase().includes(val.toLowerCase())
      );
      const resBox = document.getElementById('dash-quick-result');
      if (found) {
        resBox.innerHTML = `
          <div style="background: var(--color-surface-container); padding: 1rem; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
            <div>
              <strong style="font-family: monospace; color: #1e40af;">${found.plateNumber}</strong> — ${found.fullName}
              <div style="font-size: 0.8rem; color: #64748b;">${found.vehicleCategory} | ${found.subCity}</div>
            </div>
            <button class="btn btn-primary btn-sm" onclick="window.App.openPermitModal('${found.id}')">👁 ዝርዝር እይ</button>
          </div>
        `;
      } else {
        resBox.innerHTML = `<div style="color: #dc2626; font-size: 0.85rem;">ተሽከርካሪው አልተገኘም (No motorcycle found)</div>`;
      }
    },
    lookupAndShowInspection: function(query) {
      const reg = AppState.registrations.find(r => 
        (r.id || '').toLowerCase() === query.toLowerCase() ||
        (r.plateNumber || '').toLowerCase().includes(query.toLowerCase()) ||
        (r.engineOrSerialNo || '').toLowerCase() === query.toLowerCase()
      );

      const targetBox = document.getElementById('scanner-result-box');
      if (!targetBox) return;

      if (reg) {
        const isVerified = reg.status === 'approved' || reg.status === 'printed';
        targetBox.innerHTML = `
          <div style="border: 2px solid ${isVerified ? '#22c55e' : '#f59e0b'}; border-radius: var(--radius-md); padding: 1.25rem; background: var(--color-surface-card);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <span class="badge ${isVerified ? 'badge-verified' : 'badge-warning'}" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">
                ${isVerified ? '✓ ትክክለኛ ፈቃድ (VERIFIED)' : '⚠ ያልተረጋገጠ ፈቃድ (WARNING)'}
              </span>
              <span style="font-family: monospace; font-size: 1.2rem; font-weight: bold; color: #1e40af;">${reg.plateNumber}</span>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
              <div>
                <div><strong>ሙሉ ስም:</strong> ${reg.fullName}</div>
                <div><strong>ስልክ:</strong> ${reg.phone}</div>
                <div><strong>ሞተር/ሲሪያል:</strong> ${reg.engineOrSerialNo}</div>
                <div><strong>ክፍለ ከተማ:</strong> ${reg.subCity}</div>
              </div>
              <div style="text-align: right;">
                ${reg.userPortraitPhoto ? `<img src="${reg.userPortraitPhoto}" style="width: 75px; height: 75px; border-radius: 8px; object-fit: cover;">` : ''}
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">የመኮንኑ ፍተሻ ማስታወሻ (Officer Notes)</label>
              <input type="text" id="officer-scan-notes" class="form-control" placeholder="ምሳሌ: የራስ ቁር (Helmet) እና የመንጃ ፍቃድ ተረጋግጧል።">
            </div>

            <button class="btn btn-success" style="width: 100%;" onclick="window.App.saveRoadsideInspection('${reg.id}', '${reg.plateNumber}', '${reg.fullName}', '${reg.phone}', '${reg.vehicleCategory}', '${reg.engineOrSerialNo}', '${reg.status}', '${isVerified ? 'verified' : 'warning'}')">
              💾 የፍተሻ መዝገብ አስቀምጥ (Save Roadside Log)
            </button>
          </div>
        `;
      } else {
        targetBox.innerHTML = `
          <div style="border: 2px solid #dc2626; border-radius: var(--radius-md); padding: 1.25rem; background: #fee2e2; color: #b91c1c;">
            <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem;">🚨 ያልተመዘገበ ተሽከርካሪ (UNREGISTERED)</div>
            <p style="font-size: 0.85rem;">የተፈለገው ታርጋ ወይም ሲሪያል ቁጥር በማዘጋጃ ቤቱ ማህደር ውስጥ አልተገኘም።</p>
            <button class="btn btn-danger" style="margin-top: 0.75rem;" onclick="window.App.navigateTo('report_unregistered')">
              የእግድ ጥቆማ መዝግብ (File Citation)
            </button>
          </div>
        `;
      }
    },

    saveRoadsideInspection: async function(regId, plate, name, phone, cat, engine, permitStatus, verifStatus) {
      const notes = document.getElementById('officer-scan-notes')?.value || 'Roadside check completed';
      const badgeId = (AppState.user && AppState.user.badgeId) || 'OFFICER-8842';

      try {
        const res = await fetch('ajax/verifications.php?action=save', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            plateNumber: plate,
            fullName: name,
            phone: phone,
            vehicleCategory: cat,
            engineOrSerialNo: engine,
            permitStatus: permitStatus,
            verificationStatus: verifStatus,
            officerNotes: notes,
            officerBadgeId: badgeId,
            locationName: 'Bahir Dar City Patrol Checkpoint',
            registrationId: regId
          })
        });
        const json = await res.json();
        if (json.success) {
          showToast('የፍተሻ ሪፖርቱ ተመዝግቧል!', 'success');
          await syncAllData();
          document.getElementById('scanner-result-box').innerHTML = '';
        }
      } catch (err) {
        showToast('Save log error', 'error');
      }
    },

    // Theme & Language Toggles
    toggleLanguage: function() {
      AppState.lang = AppState.lang === 'am' ? 'en' : 'am';
      localStorage.setItem('permit_lang', AppState.lang);
      document.documentElement.lang = AppState.lang;
      document.getElementById('lang-toggle-label').innerText = AppState.lang === 'am' ? 'English' : 'አማርኛ';
      renderActivePage();
    },

    toggleTheme: function() {
      AppState.theme = AppState.theme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('permit_theme', AppState.theme);
      if (AppState.theme === 'dark') {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },

    logout: async function() {
      await fetch('ajax/auth.php?action=logout');
      window.location.href = 'index.php?page=login';
    }
  };

  // Initialization on DOM Load
  document.addEventListener('DOMContentLoaded', async function() {
    // Set theme
    if (AppState.theme === 'dark') {
      document.documentElement.classList.add('dark');
    }

    // Set language
    document.documentElement.lang = AppState.lang;

    // Start Live Clock
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    // Check hash for direct route
    const hash = window.location.hash.replace('#', '') || 'dashboard';
    AppState.activeTab = hash;

    // Synchronize initial data
    await syncAllData();

    // Attach click outside dropdown handler
    document.addEventListener('click', (e) => {
      const userBtn = document.querySelector('.user-btn');
      const dropdown = document.getElementById('user-dropdown-menu');
      if (dropdown && userBtn && !userBtn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
      }
    });
  });

})();
