/**
 * Hydrates dynamic UPI QR containers (.upi-qr-render) using locally bundled MIT qrcode-generator.
 * Also supports Barcode Studio print previews & quick SVG label scaling.
 */
(function () {
  function renderAllUpiQrs() {
    if (typeof window.qrcode !== 'function') return;
    document.querySelectorAll('.upi-qr-render[data-upi-uri]').forEach(function (el) {
      if (el.dataset.rendered === '1') return;
      var uri = el.dataset.upiUri;
      if (!uri) return;
      try {
        var qr = window.qrcode(0, 'M');
        qr.addData(uri);
        qr.make();
        var sizePx = parseInt(el.dataset.qrSize || '110', 10);
        var svgHtml = qr.createSvgTag({ scalable: true, margin: 1 });
        el.innerHTML = svgHtml;
        var svgEl = el.querySelector('svg');
        if (svgEl) {
          svgEl.setAttribute('width', String(sizePx));
          svgEl.setAttribute('height', String(sizePx));
          svgEl.style.display = 'block';
          svgEl.style.margin = '0 auto';
        }
        el.dataset.rendered = '1';
      } catch (e) {
        console.warn('UPI QR render error:', e);
      }
    });
  }

  function initBarcodeScanner() {
    var scanInput = document.getElementById('barcodeScanInput');
    if (!scanInput) return;

    scanInput.addEventListener('keydown', async function (e) {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      var code = (scanInput.value || '').trim();
      if (!code) return;

      try {
        var res = await fetch('/api/barcodes/lookup?code=' + encodeURIComponent(code));
        var data = await res.json();
        if (!data.success || !data.item) {
          scanInput.classList.add('is-invalid');
          setTimeout(function () { scanInput.classList.remove('is-invalid'); }, 1200);
          return;
        }

        var item = data.item;
        var rows = document.querySelectorAll('#invoiceItemsTable tbody tr');
        for (var i = 0; i < rows.length; i++) {
          var idEl = rows[i].querySelector('.row-item-id');
          if (idEl && String(idEl.value) === String(item.id)) {
            var qEl = rows[i].querySelector('.row-qty');
            if (qEl) {
              qEl.value = (parseFloat(qEl.value || '0') + 1).toString();
              qEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
            scanInput.value = '';
            return;
          }
        }

        var targetRow = null;
        for (var j = 0; j < rows.length; j++) {
          var nameEl = rows[j].querySelector('.row-item-name');
          if (nameEl && !(nameEl.value || '').trim()) {
            targetRow = rows[j];
            break;
          }
        }
        if (!targetRow) {
          var btnAdd = document.getElementById('btnAddItem');
          if (btnAdd) btnAdd.click();
          var updatedRows = document.querySelectorAll('#invoiceItemsTable tbody tr');
          targetRow = updatedRows[updatedRows.length - 1];
        }
        if (targetRow) {
          var targetName = targetRow.querySelector('.row-item-name');
          if (targetName) {
            targetName.value = item.name;
            targetName.dispatchEvent(new Event('input', { bubbles: true }));
            targetName.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }
        scanInput.value = '';
      } catch (err) {
        console.warn('Barcode scan error:', err);
      }
    });
  }

  window.renderAllUpiQrs = renderAllUpiQrs;

  function boot() {
    renderAllUpiQrs();
    initBarcodeScanner();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
