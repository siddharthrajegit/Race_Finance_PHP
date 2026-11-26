<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$method = $valuation['method'] ?? 'fifo';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-calculator text-success me-2"></i>Stock Valuation Report</h3>
    <small class="text-muted">Auditable inventory cost valuation using cost layers recorded in stock movements</small>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <div class="btn-group btn-group-sm" role="group">
      <a href="/warehouses/valuation?method=fifo" class="btn <?= $method === 'fifo' ? 'btn-primary' : 'btn-outline-primary' ?>">FIFO Costing</a>
      <a href="/warehouses/valuation?method=weighted_avg" class="btn <?= $method === 'weighted_avg' ? 'btn-primary' : 'btn-outline-primary' ?>">Weighted Average</a>
    </div>
    <a href="/warehouses" class="btn btn-outline-secondary btn-sm">Back to Warehouses</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm"><div class="card-body py-3">
      <div class="small text-muted">Total On-Hand Units</div>
      <div class="fs-4 fw-bold text-dark"><?= number_format((float)$valuation['total_qty'], 2) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm"><div class="card-body py-3">
      <div class="small text-muted">Inventory Cost Value (<?= $method === 'fifo' ? 'FIFO' : 'Weighted Avg' ?>)</div>
      <div class="fs-4 fw-bold text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$valuation['total_value'], 2) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm"><div class="card-body py-3">
      <div class="small text-muted">Estimated Retail / Sale Potential</div>
      <div class="fs-4 fw-bold text-success"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$valuation['total_sale_potential'], 2) ?></div>
    </div></div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.875rem;">
      <thead class="table-light">
        <tr>
          <th>Item Name</th>
          <th>Category</th>
          <th class="text-end">On Hand</th>
          <th class="text-end">Unit Cost (<?= $method === 'fifo' ? 'FIFO Avg' : 'W-Avg' ?>)</th>
          <th class="text-end">Stock Cost Value</th>
          <th class="text-end">Sale Potential</th>
          <th class="text-end">Est. Margin</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($valuation['items'] as $row): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="small text-muted"><?= htmlspecialchars($row['category'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end"><?= number_format((float)$row['on_hand_qty'], 2) ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['unit_cost'], 2) ?></td>
            <td class="text-end fw-bold text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['stock_value'], 2) ?></td>
            <td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['sale_potential'], 2) ?></td>
            <td class="text-end <?= $row['estimated_margin'] >= 0 ? 'text-success' : 'text-danger' ?>">
              <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['estimated_margin'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
