<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0 text-dark">
          <i class="bi bi-box-seam me-2 text-primary"></i>
          <?= !empty($item) ? 'Edit Item / Product' : 'Add New Item / Product' ?>
        </h4>
        <a href="/items" class="btn btn-outline-secondary btn-sm">Cancel</a>
      </div>

      <div class="card-body p-4">
        <form action="<?= !empty($item) ? '/items/edit/' . $item['id'] : '/items/create' ?>" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <!-- Item Name & Code -->
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label for="name" class="form-label">Item / Product Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Cotton T-Shirt / Steel Rod 10mm" value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>
            <div class="col-md-4">
              <label for="item_code" class="form-label">Item Code / Barcode</label>
              <input type="text" class="form-control font-monospace" id="item_code" name="item_code" placeholder="e.g. SKU-101" value="<?= htmlspecialchars($item['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>

          <!-- HSN & Unit -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="hsn_code" class="form-label">HSN / SAC Code</label>
              <input type="text" class="form-control font-monospace" id="hsn_code" name="hsn_code" placeholder="e.g. 61091000" value="<?= htmlspecialchars($item['hsn_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
              <label for="unit" class="form-label">Unit of Measurement</label>
              <select class="form-select" id="unit" name="unit">
                <option value="PCS" <?= (empty($item) || ($item['unit'] ?? '') === 'PCS') ? 'selected' : '' ?>>PCS (Pieces)</option>
                <option value="KG" <?= (!empty($item) && ($item['unit'] ?? '') === 'KG') ? 'selected' : '' ?>>KG (Kilograms)</option>
                <option value="BOX" <?= (!empty($item) && ($item['unit'] ?? '') === 'BOX') ? 'selected' : '' ?>>BOX (Boxes)</option>
                <option value="MTR" <?= (!empty($item) && ($item['unit'] ?? '') === 'MTR') ? 'selected' : '' ?>>MTR (Meters)</option>
                <option value="LTR" <?= (!empty($item) && ($item['unit'] ?? '') === 'LTR') ? 'selected' : '' ?>>LTR (Liters)</option>
                <option value="NOS" <?= (!empty($item) && ($item['unit'] ?? '') === 'NOS') ? 'selected' : '' ?>>NOS (Numbers)</option>
                <option value="BAG" <?= (!empty($item) && ($item['unit'] ?? '') === 'BAG') ? 'selected' : '' ?>>BAG (Bags)</option>
                <option value="PKT" <?= (!empty($item) && ($item['unit'] ?? '') === 'PKT') ? 'selected' : '' ?>>PKT (Packets)</option>
              </select>
            </div>
          </div>

          <!-- Pricing & GST Tax Rate -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="sale_price" class="form-label">Sale Price (₹)</label>
              <div class="input-group">
                <span class="input-group-text bg-light">₹</span>
                <input type="number" class="form-control text-end" id="sale_price" name="sale_price" step="any" min="0" placeholder="0.00" value="<?= htmlspecialchars((string)($item['sale_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label for="purchase_price" class="form-label">Purchase Price (₹)</label>
              <div class="input-group">
                <span class="input-group-text bg-light">₹</span>
                <input type="number" class="form-control text-end" id="purchase_price" name="purchase_price" step="any" min="0" placeholder="0.00" value="<?= htmlspecialchars((string)($item['purchase_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label for="tax_rate" class="form-label">GST Tax Rate (%)</label>
              <select class="form-select" id="tax_rate" name="tax_rate">
                <option value="0" <?= (empty($item) || (float)($item['tax_rate'] ?? 0) === 0.0) ? 'selected' : '' ?>>0% (Exempt / Nil)</option>
                <option value="5" <?= (!empty($item) && (float)$item['tax_rate'] === 5.0) ? 'selected' : '' ?>>5% (GST 5%)</option>
                <option value="12" <?= (!empty($item) && (float)$item['tax_rate'] === 12.0) ? 'selected' : '' ?>>12% (GST 12%)</option>
                <option value="18" <?= (!empty($item) && (float)$item['tax_rate'] === 18.0) ? 'selected' : '' ?>>18% (GST 18%)</option>
                <option value="28" <?= (!empty($item) && (float)$item['tax_rate'] === 28.0) ? 'selected' : '' ?>>28% (GST 28%)</option>
              </select>
            </div>
          </div>

          <!-- Stock Levels -->
          <div class="row g-3 mb-3">
            <?php if (empty($item)): ?>
              <div class="col-md-6">
                <label for="opening_stock" class="form-label">Opening Stock Quantity</label>
                <input type="number" class="form-control" id="opening_stock" name="opening_stock" step="any" placeholder="0" value="0">
              </div>
            <?php endif; ?>
            <div class="<?= !empty($item) ? 'col-md-12' : 'col-md-6' ?>">
              <label for="low_stock_threshold" class="form-label">Low Stock Alert Threshold</label>
              <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" step="any" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($item['low_stock_threshold'] ?? '5'), ENT_QUOTES, 'UTF-8') ?>">
              <div class="form-text">Get an alert when stock drops below this number.</div>
            </div>
          </div>

          <!-- Description -->
          <div class="mb-4">
            <label for="description" class="form-label">Item Description / Specifications</label>
            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional notes or product details..."><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="/items" class="btn btn-light px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
              <i class="bi bi-check2-circle me-1"></i> <?= !empty($item) ? 'Update Item' : 'Save Item' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
