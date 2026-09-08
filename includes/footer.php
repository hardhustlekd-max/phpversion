<?php
/**
 * Application Footer & Script Inclusions
 */
?>
  <!-- Global Modal Container -->
  <div class="modal-backdrop" id="global-modal">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 id="global-modal-title" style="font-size: 1.15rem; font-weight: 700;">ዝርዝር መረጃ</h3>
        <button onclick="window.App.closeModal()" style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer; color: var(--color-on-surface-muted);">&times;</button>
      </div>
      <div class="modal-body" id="global-modal-body">
        <!-- Dynamically injected content -->
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toast-container"></div>

  <!-- Application Footer -->
  <footer style="margin-top: auto; border-top: 1px solid var(--color-surface-border); padding: 1.5rem 1rem; text-align: center; font-size: 0.8rem; color: var(--color-on-surface-muted); background: var(--color-surface-card);" class="no-print">
    <div class="container-custom">
      <div style="font-weight: 600;">የባህር ዳር ከተማ አስተዳደር ትራንስፖርትና ተንቀሳቃሽነት ቢሮ (Bahir Dar City Transport Bureau)</div>
      <div style="margin-top: 0.25rem;">Enforcement Pro - Command Central &bull; PHP + AJAX + MySQL Standalone Version 2.0</div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="assets/js/ethiopianCalendar.js"></script>
  <script src="assets/js/qrcode.min.js"></script>
  <script src="assets/js/scanner.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
