/**
 * Print & Export Templates Engine
 * Generates official A4 Permit Sheets, CR80 PVC Cards, and Vehicle QR Stickers
 */

const PrintTemplates = (() => {
  function getQRData(reg) {
    return reg.qr_code_data || reg.qrCodeData || `https://enforcement.gov.et/verify/${reg.plate_number || reg.plateNumber}`;
  }

  function getPlate(reg) {
    return reg.plate_number || reg.plateNumber || '—';
  }

  function getName(reg) {
    return reg.full_name || reg.fullName || '—';
  }

  function getPhone(reg) {
    return reg.phone || '—';
  }

  function getCategory(reg) {
    const cat = reg.vehicle_category || reg.vehicleCategory;
    return cat === 'electric' ? 'ኤሌክትሪክ (Electric)' : 'ነዳጅ ከ110cc በታች (Gas < 110cc)';
  }

  function getEngine(reg) {
    return reg.engine_or_serial_no || reg.engineOrSerialNo || '—';
  }

  function getSubCity(reg) {
    return reg.sub_city || reg.subCity || 'Central';
  }

  function getPhoto(reg) {
    return reg.user_portrait_photo || reg.userPortraitPhoto || 'image/app/logo.png';
  }

  function openPrintWindow(htmlContent) {
    const printWin = window.open('', '_blank', 'width=900,height=800');
    if (!printWin) {
      alert('Please allow popups to print documents.');
      return;
    }
    printWin.document.open();
    printWin.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Print Document - Enforcement Pro</title>
        <link rel="stylesheet" href="assets/css/app.css">
        <script src="assets/js/qrcode.min.js"></script>
        <style>
          @page { size: auto; margin: 10mm; }
          body { font-family: 'Abyssinica SIL', sans-serif; margin: 0; padding: 15px; color: #111; }
        </style>
      </head>
      <body>
        ${htmlContent}
        <script>
          window.onload = function() {
            setTimeout(function() { window.print(); }, 400);
          };
        </script>
      </body>
      </html>
    `);
    printWin.document.close();
  }

  function printA4(reg) {
    const qrSvg = QRCode.generateSVG(getQRData(reg), 110, 1);
    const ethDate = EthiopianCalendarJS.formatDate(reg.registration_date || reg.registrationDate);

    const html = `
      <div class="a4-permit-sheet" style="border: 3px double #0B1E48; padding: 25px; border-radius: 8px; position: relative;">
        <!-- Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0B1E48; padding-bottom: 12px; margin-bottom: 15px;">
          <img src="image/app/logo.png" style="height: 65px; width: 65px; object-fit: contain;" alt="Logo">
          <div style="text-align: center; flex: 1;">
            <h2 style="margin: 0; font-size: 19px; color: #0B1E48;">የአማራ ብሔራዊ ክልላዊ መንግሥት</h2>
            <h3 style="margin: 2px 0; font-size: 16px; color: #1E293B;">የባህርዳር ከተማ አስተዳደር የትራፊክ ማኔጅመንት ኤጀንሲ</h3>
            <p style="margin: 2px 0; font-size: 13px; font-weight: bold; color: #D97706;">የሞተር ብስክሌት ህጋዊ የይለፍ ፈቃድ ሰርተፊኬት (A4 Official Permit)</p>
          </div>
          <img src="image/app/flag.jpg" style="height: 48px; width: 75px; object-fit: cover; border-radius: 4px;" alt="Flag">
        </div>

        <!-- Body Details -->
        <div style="display: flex; gap: 20px; margin-top: 15px;">
          <!-- Driver Portrait & QR -->
          <div style="width: 170px; text-align: center; border-right: 1px dashed #CBD5E1; padding-right: 15px;">
            <img src="${getPhoto(reg)}" style="width: 140px; height: 160px; object-fit: cover; border: 2px solid #0B1E48; border-radius: 6px;" alt="Portrait">
            <div style="margin-top: 12px; display: inline-block; padding: 4px; border: 1px solid #CBD5E1; border-radius: 4px; background: #fff;">
              ${qrSvg}
            </div>
            <p style="font-size: 11px; margin: 4px 0; font-weight: bold;">Security QR Verifier</p>
          </div>

          <!-- Specs Table -->
          <div style="flex: 1;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold; width: 160px;">የሰሌዳ ቁጥር (Plate No):</td>
                <td style="padding: 6px; font-size: 16px; font-weight: 900; color: #0B1E48;">${getPlate(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የባለቤቱ ሙሉ ስም (Full Name):</td>
                <td style="padding: 6px; font-weight: bold;">${getName(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">ስልክ ቁጥር (Phone):</td>
                <td style="padding: 6px;">${getPhone(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የተሽከርካሪ ዓይነት (Category):</td>
                <td style="padding: 6px;">${getCategory(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የሞተር ብራንድ / ሞዴል:</td>
                <td style="padding: 6px;">${reg.motor_brand || reg.motorBrand || '—'} / ${reg.motorModel || '—'}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የሻሲ / ሞተር ቁጥር (Engine No):</td>
                <td style="padding: 6px; font-family: monospace;">${getEngine(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">ክፍለ ከተማ (Sub-City):</td>
                <td style="padding: 6px;">${getSubCity(reg)}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የምዝገባ ቀን (Registration Date):</td>
                <td style="padding: 6px;">${ethDate}</td>
              </tr>
              <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 6px; font-weight: bold;">የደረሰኝ ቁጥር (Receipt No):</td>
                <td style="padding: 6px; font-family: monospace;">${reg.receipt_number || reg.receiptNumber || 'REC-PENDING'}</td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Official Signatures and Seals -->
        <div style="margin-top: 50px; display: flex; justify-content: space-between; text-align: center; font-size: 12px;">
          <div>
            <div style="border-top: 1px solid #333; width: 170px; margin-bottom: 4px;"></div>
            <p style="margin: 0; font-weight: bold;">የመዝጋቢው ፊርማ (Clerk)</p>
          </div>
          <div>
            <div style="border: 2px dashed #059669; width: 110px; height: 110px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #059669; font-weight: bold; font-size: 11px; margin: 0 auto;">
              ህጋዊ ማህተም<br>OFFICIAL SEAL
            </div>
          </div>
          <div>
            <div style="border-top: 1px solid #333; width: 170px; margin-bottom: 4px;"></div>
            <p style="margin: 0; font-weight: bold;">የኤጀንሲው ኃላፊ ፊርማ</p>
          </div>
        </div>

        <div style="margin-top: 30px; font-size: 10px; color: #64748B; text-align: center; border-top: 1px solid #E2E8F0; padding-top: 8px;">
          ይህ ፈቃድ በባህርዳር ከተማ አስተዳደር ህግና ደንብ መሠረት የተሰጠ ሲሆን በማንኛውም የፍተሻ ጣቢያ ሲጠየቅ የመታየት ግዴታ አለበት።
        </div>
      </div>
    `;

    openPrintWindow(html);
  }

  function printPVC(reg) {
    const qrSvg = QRCode.generateSVG(getQRData(reg), 68, 1);
    const html = `
      <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; padding: 20px;">
        <!-- CR80 Front -->
        <div class="pvc-card-sheet" style="background: linear-gradient(135deg, #0B1E48 0%, #1E3A8A 100%); color: #fff; position: relative; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
          <div style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 3px;">
            <img src="image/app/logo.png" style="width: 24px; height: 24px; object-fit: contain; filter: brightness(1.2);" alt="Logo">
            <div>
              <div style="font-size: 8px; font-weight: 900; letter-spacing: 0.5px;">BAHIR DAR MOTOR PERMIT</div>
              <div style="font-size: 7px; color: #FCD34D;">ባህርዳር ከተማ የትራፊክ ማኔጅመንት</div>
            </div>
          </div>

          <div style="display: flex; gap: 8px; margin-top: 6px;">
            <img src="${getPhoto(reg)}" style="width: 58px; height: 72px; object-fit: cover; border-radius: 4px; border: 1px solid #fff;" alt="Photo">
            <div style="font-size: 8px; flex: 1; line-height: 1.3;">
              <div style="font-size: 11px; font-weight: 900; color: #FCD34D; font-family: monospace;">${getPlate(reg)}</div>
              <div style="font-weight: bold; margin-top: 2px;">${getName(reg)}</div>
              <div style="color: #93C5FD; font-size: 7.5px;">${getCategory(reg)}</div>
              <div style="font-size: 7px; margin-top: 4px;">Zone: ${getSubCity(reg)}</div>
            </div>
          </div>
          <div style="position: absolute; bottom: 4px; right: 6px; font-size: 7px; color: #CBD5E1;">CR80 PVC VALID</div>
        </div>

        <!-- CR80 Back -->
        <div class="pvc-card-sheet" style="background: #ffffff; color: #0F172A; position: relative; border-radius: 8px; border: 1px solid #CBD5E1; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 8px; font-weight: bold; color: #0B1E48;">SECURITY INSPECTION PASS</div>
            <div style="font-size: 7px; color: #64748B;">SN: ${reg.id || 'REG-ID'}</div>
          </div>

          <div style="display: flex; gap: 10px; margin-top: 8px; align-items: center;">
            <div style="padding: 2px; border: 1px solid #E2E8F0; border-radius: 4px;">
              ${qrSvg}
            </div>
            <div style="font-size: 7.5px; line-height: 1.4; color: #334155;">
              <div><strong>Engine:</strong> ${getEngine(reg)}</div>
              <div><strong>Phone:</strong> ${getPhone(reg)}</div>
              <div><strong>Status:</strong> APPROVED</div>
              <div style="margin-top: 4px; font-size: 6.5px; color: #64748B;">Scan QR for real-time roadside verification.</div>
            </div>
          </div>
          <div style="position: absolute; bottom: 4px; left: 6px; font-size: 6.5px; color: #94A3B8;">Bahir Dar Municipal Traffic Enforcement</div>
        </div>
      </div>
    `;

    openPrintWindow(html);
  }

  function printQRSticker(reg) {
    const qrSvg = QRCode.generateSVG(getQRData(reg), 130, 1);
    const html = `
      <div style="display: flex; justify-content: center; padding: 20px;">
        <div class="qr-sticker-sheet" style="width: 280px; border: 2px solid #0B1E48; border-radius: 12px; padding: 15px; text-align: center; background: #fff;">
          <div style="font-size: 10px; font-weight: bold; color: #0B1E48; text-transform: uppercase;">Bahir Dar Municipal Permit</div>
          <div style="font-size: 20px; font-weight: 900; color: #0B1E48; margin: 4px 0; font-family: monospace; letter-spacing: 1px;">${getPlate(reg)}</div>
          <div style="font-size: 12px; font-weight: bold; color: #D97706; margin-bottom: 10px;">${getName(reg)}</div>
          <div style="display: inline-block; padding: 6px; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
            ${qrSvg}
          </div>
          <div style="font-size: 9px; font-weight: bold; color: #059669; margin-top: 8px;">VALID REGISTERED MOTORCYCLE</div>
          <div style="font-size: 8px; color: #64748B; margin-top: 2px;">Sub-City: ${getSubCity(reg)}</div>
        </div>
      </div>
    `;

    openPrintWindow(html);
  }

  function exportCSV(regs) {
    if (!regs || !regs.length) {
      alert('No records to export');
      return;
    }
    const headers = ['Plate Number', 'Full Name', 'Phone', 'Category', 'Brand', 'Engine Serial', 'Sub City', 'Status', 'Date'];
    const rows = regs.map(r => [
      r.plate_number || r.plateNumber,
      `"${(r.full_name || r.fullName || '').replace(/"/g, '""')}"`,
      r.phone,
      r.vehicle_category || r.vehicleCategory,
      r.motor_brand || r.motorBrand,
      r.engine_or_serial_no || r.engineOrSerialNo,
      r.sub_city || r.subCity,
      r.status,
      r.registration_date || r.registrationDate
    ]);

    const csvContent = '\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `motorcycle_registrations_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  function exportJSON(regs) {
    if (!regs || !regs.length) {
      alert('No records to export');
      return;
    }
    const blob = new Blob([JSON.stringify(regs, null, 2)], { type: 'application/json;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `motorcycle_registrations_${new Date().toISOString().slice(0,10)}.json`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  return {
    printA4,
    printPVC,
    printQRSticker,
    exportCSV,
    exportJSON
  };
})();

window.PrintTemplates = PrintTemplates;
