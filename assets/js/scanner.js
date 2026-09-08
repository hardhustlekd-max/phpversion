/**
 * Roadside Enforcement QR Code & Barcode Camera Scanner
 */

(function(root) {
  class PermitScanner {
    constructor(videoElementId, onScanCallback) {
      this.video = document.getElementById(videoElementId);
      this.onScan = onScanCallback;
      this.stream = null;
      this.isScanning = false;
      this.detector = null;

      if ('BarcodeDetector' in window) {
        try {
          this.detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
        } catch (e) {
          console.warn('Native BarcodeDetector not available:', e);
        }
      }
    }

    async start() {
      if (!this.video) return;
      try {
        this.stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
          audio: false
        });
        this.video.srcObject = this.stream;
        await this.video.play();
        this.isScanning = true;
        this.scanLoop();
      } catch (err) {
        console.error('Camera access error:', err);
        throw err;
      }
    }

    stop() {
      this.isScanning = false;
      if (this.stream) {
        this.stream.getTracks().forEach(track => track.stop());
        this.stream = null;
      }
      if (this.video) {
        this.video.srcObject = null;
      }
    }

    async scanLoop() {
      if (!this.isScanning || !this.video) return;

      if (this.detector && this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
        try {
          const barcodes = await this.detector.detect(this.video);
          if (barcodes.length > 0) {
            const rawValue = barcodes[0].rawValue;
            if (rawValue && this.onScan) {
              this.onScan(rawValue);
              this.stop();
              return;
            }
          }
        } catch (err) {
          // Frame skip
        }
      }

      if (this.isScanning) {
        requestAnimationFrame(() => this.scanLoop());
      }
    }

    // Process static uploaded image file
    async processImageFile(file) {
      if (!file) return null;
      if (this.detector) {
        try {
          const bitmap = await createImageBitmap(file);
          const barcodes = await this.detector.detect(bitmap);
          if (barcodes.length > 0) {
            return barcodes[0].rawValue;
          }
        } catch (e) {
          console.error('Image barcode detection error:', e);
        }
      }
      return null;
    }
  }

  root.PermitScanner = PermitScanner;
})(window);
