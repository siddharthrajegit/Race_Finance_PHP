/**
 * Main Client Application Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Instant Table Quick Search Filter & Custom Date Range Filter
  function initTableDateAndSearchFilters() {
    const tableSearchInputs = document.querySelectorAll('[data-table-search]');
    tableSearchInputs.forEach(input => {
      const targetTableId = input.dataset.tableSearch;
      const table = document.getElementById(targetTableId);
      if (!table) return;

      const fromInput = document.querySelector(`[data-date-filter-from="${targetTableId}"]`);
      const toInput = document.querySelector(`[data-date-filter-to="${targetTableId}"]`);
      const clearBtn = document.querySelector(`[data-date-filter-clear="${targetTableId}"]`);
      const countBadge = document.getElementById(`visibleRecordsCount_${targetTableId}`);

      function applyFilter() {
        const query = input.value.toLowerCase().trim();
        const fromVal = fromInput ? fromInput.value : '';
        const toVal = toInput ? toInput.value : '';

        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
          // Skip empty-state placeholder rows that span full width
          if (row.querySelector('td[colspan]')) return;

          const invDate = row.dataset.invoiceDate || '';
          const text = row.textContent.toLowerCase();

          let matchDate = true;
          if (fromVal && invDate && invDate < fromVal) matchDate = false;
          if (toVal && invDate && invDate > toVal) matchDate = false;

          let matchQuery = true;
          if (query && !text.includes(query)) matchQuery = false;

          if (matchDate && matchQuery) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        if (countBadge) {
          countBadge.textContent = visibleCount;
        }

        if (window.updateBulkBar) {
          window.updateBulkBar(targetTableId);
        }
      }

      input.addEventListener('input', applyFilter);
      if (fromInput) fromInput.addEventListener('change', applyFilter);
      if (toInput) toInput.addEventListener('change', applyFilter);
      if (clearBtn) {
        clearBtn.addEventListener('click', () => {
          if (fromInput) fromInput.value = '';
          if (toInput) toInput.value = '';
          applyFilter();
        });
      }
    });
  }

  // 1c. Bulk Checkbox Selection & Bulk Print Queue
  function initBulkPrintSelection() {
    const tables = document.querySelectorAll('table[id]');
    tables.forEach(table => {
      const tableId = table.id;
      const selectAllMaster = table.querySelector('.bulk-select-all');
      const bulkBar = document.getElementById(`bulkActionBar_${tableId}`);
      if (!bulkBar) return;

      const counterSpan = bulkBar.querySelector('.bulk-selected-counter');
      const printBtn = bulkBar.querySelector('.bulk-print-btn');
      const selectAllFilteredBtn = bulkBar.querySelector('.bulk-select-all-filtered-btn');
      const clearBtn = bulkBar.querySelector('.bulk-clear-selection-btn');

      window.updateBulkBar = function(tId) {
        if (tId && tId !== tableId) return;
        const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
        visibleRows.forEach(tr => {
          const cb = tr.querySelector('.invoice-row-checkbox');
          if (cb) tr.classList.toggle('row-selected', cb.checked);
        });

        const checkedBoxes = table.querySelectorAll('tbody tr:not([style*="display: none"]) .invoice-row-checkbox:checked');
        const count = checkedBoxes.length;

        if (counterSpan) counterSpan.textContent = count;
        if (count > 0) {
          bulkBar.classList.remove('d-none');
          bulkBar.classList.add('d-flex');
        } else {
          bulkBar.classList.add('d-none');
          bulkBar.classList.remove('d-flex');
        }

        if (selectAllMaster) {
          const visibleBoxes = table.querySelectorAll('tbody tr:not([style*="display: none"]) .invoice-row-checkbox');
          selectAllMaster.checked = visibleBoxes.length > 0 && checkedBoxes.length === visibleBoxes.length;
          selectAllMaster.indeterminate = count > 0 && count < visibleBoxes.length;
        }
      };

      if (selectAllMaster) {
        selectAllMaster.addEventListener('change', () => {
          const isChecked = selectAllMaster.checked;
          const visibleBoxes = table.querySelectorAll('tbody tr:not([style*="display: none"]) .invoice-row-checkbox');
          visibleBoxes.forEach(cb => { cb.checked = isChecked; });
          window.updateBulkBar(tableId);
        });
      }

      table.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('invoice-row-checkbox')) {
          window.updateBulkBar(tableId);
        }
      });

      if (selectAllFilteredBtn) {
        selectAllFilteredBtn.addEventListener('click', () => {
          const visibleBoxes = table.querySelectorAll('tbody tr:not([style*="display: none"]) .invoice-row-checkbox');
          visibleBoxes.forEach(cb => { cb.checked = true; });
          window.updateBulkBar(tableId);
        });
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', () => {
          const allBoxes = table.querySelectorAll('.invoice-row-checkbox');
          allBoxes.forEach(cb => { cb.checked = false; });
          window.updateBulkBar(tableId);
        });
      }

      if (printBtn) {
        printBtn.addEventListener('click', () => {
          const checkedBoxes = Array.from(table.querySelectorAll('.invoice-row-checkbox:checked'));
          if (checkedBoxes.length === 0) {
            alert('Please select at least one invoice to print.');
            return;
          }
          const ids = checkedBoxes.map(cb => cb.value).join(',');
          const docType = printBtn.dataset.docType || 'sale';
          const bulkPrintUrl = `/invoices/bulk-print?ids=${encodeURIComponent(ids)}&type=${encodeURIComponent(docType)}`;
          window.open(bulkPrintUrl, '_blank');
        });
      }
    });
  }

  initTableDateAndSearchFilters();
  initBulkPrintSelection();

  // 1b. Table Column Sorting & Sort Dropdown Handler
  function initTableSorting() {
    const sortDropdowns = document.querySelectorAll('[data-table-sort]');
    const sortableHeaders = document.querySelectorAll('.sortable-th');

    function camelCase(str) {
      return str.replace(/-([a-z])/g, g => g[1].toUpperCase());
    }

    function sortTable(tableId, sortKey, sortDirection) {
      const table = document.getElementById(tableId);
      if (!table) return;
      const tbody = table.querySelector('tbody');
      if (!tbody) return;

      const camelKey = camelCase(sortKey);
      const rows = Array.from(tbody.querySelectorAll('tr[data-' + sortKey + ']'));
      if (rows.length === 0) return;

      rows.sort((a, b) => {
        const valA = a.dataset[camelKey] || '';
        const valB = b.dataset[camelKey] || '';

        let comparison = 0;
        if (sortKey === 'grand-total') {
          const numA = parseFloat(valA) || 0;
          const numB = parseFloat(valB) || 0;
          comparison = numA - numB;
        } else if (sortKey === 'invoice-date') {
          comparison = valA.localeCompare(valB);
        } else if (sortKey === 'invoice-number') {
          // Natural alphanumeric sort for invoice numbers (e.g. 2 comes before 10)
          comparison = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
        } else {
          // Party name or other text
          comparison = valA.localeCompare(valB, undefined, { sensitivity: 'base' });
        }

        return sortDirection === 'desc' ? -comparison : comparison;
      });

      // Append back to tbody in sorted order
      rows.forEach(row => tbody.appendChild(row));

      // Update Header Icons & Active State
      const headers = table.querySelectorAll('.sortable-th');
      headers.forEach(th => {
        const key = th.dataset.sortKey;
        const iconSpan = th.querySelector('.sort-icon');
        if (!iconSpan) return;

        if (key === sortKey) {
          th.dataset.currentDirection = sortDirection;
          th.classList.add('table-primary', 'active-sort');
          if (sortDirection === 'asc') {
            iconSpan.innerHTML = '<i class="bi bi-arrow-up"></i>';
          } else {
            iconSpan.innerHTML = '<i class="bi bi-arrow-down"></i>';
          }
        } else {
          th.removeAttribute('data-current-direction');
          th.classList.remove('table-primary', 'active-sort');
          iconSpan.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
        }
      });

      // Synchronize select dropdown if present
      const dropdown = document.querySelector(`[data-table-sort="${tableId}"]`);
      if (dropdown) {
        const optionVal = `${sortKey}-${sortDirection}`;
        const hasOption = Array.from(dropdown.options).some(opt => opt.value === optionVal);
        if (hasOption) {
          dropdown.value = optionVal;
        }
      }
    }

    // Header click event
    sortableHeaders.forEach(th => {
      th.addEventListener('click', () => {
        const table = th.closest('table');
        if (!table) return;
        const tableId = table.id;
        const sortKey = th.dataset.sortKey;
        if (!sortKey) return;

        const currentDir = th.dataset.currentDirection;
        let nextDir = 'asc';
        if (currentDir === 'asc') {
          nextDir = 'desc';
        } else if (currentDir === 'desc') {
          nextDir = 'asc';
        } else {
          // Default direction: descending for amounts and dates, ascending for names and numbers
          nextDir = (sortKey === 'grand-total' || sortKey === 'invoice-date') ? 'desc' : 'asc';
        }

        sortTable(tableId, sortKey, nextDir);
      });
    });

    // Dropdown change event
    sortDropdowns.forEach(dropdown => {
      const targetTableId = dropdown.dataset.tableSort;
      dropdown.addEventListener('change', () => {
        const val = dropdown.value;
        if (!val) {
          sortTable(targetTableId, 'invoice-date', 'desc');
          return;
        }
        const lastDash = val.lastIndexOf('-');
        if (lastDash === -1) return;
        const sortKey = val.substring(0, lastDash);
        const sortDir = val.substring(lastDash + 1);
        sortTable(targetTableId, sortKey, sortDir);
      });
    });
  }

  initTableSorting();

  // 2. Generic Delete Confirmations
  const deleteForms = document.querySelectorAll('.form-delete-confirm');
  deleteForms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const message = form.dataset.confirmMessage || 'Are you sure you want to delete this record? This action cannot be undone.';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // 3. Print helper
  const printButtons = document.querySelectorAll('.btn-trigger-print');
  printButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      window.print();
    });
  });

  // 4. Mobile Sidebar Toggle & Backdrop
  const sidebarToggleBtns = document.querySelectorAll('.btn-sidebar-toggle');
  const appSidebar = document.getElementById('appSidebar');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');

  function openSidebar() {
    if (appSidebar) appSidebar.classList.add('sidebar-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
  }

  function closeSidebar() {
    if (appSidebar) appSidebar.classList.remove('sidebar-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
  }

  sidebarToggleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (appSidebar && appSidebar.classList.contains('sidebar-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  });

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', closeSidebar);
  }

  // 5. Interactive Settings & Form Radio Cards
  const radioCards = document.querySelectorAll('.setting-radio-card');
  radioCards.forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    if (!radio) return;

    // Click anywhere on card to select
    card.addEventListener('click', (e) => {
      if (e.target !== radio && e.target.tagName !== 'LABEL') {
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    radio.addEventListener('change', () => {
      const groupName = radio.name;
      if (!groupName) return;

      const allInGroup = document.querySelectorAll(`input[type="radio"][name="${groupName}"]`);
      allInGroup.forEach(r => {
        const parentCard = r.closest('.setting-radio-card');
        if (parentCard) {
          if (r.checked) {
            parentCard.classList.add('active');
          } else {
            parentCard.classList.remove('active');
          }
        }
      });
    });
  });

  // 6. Interactive Settings Toggle Box (Click anywhere on the box to toggle checked/unchecked)
  const toggleBoxes = document.querySelectorAll('.setting-toggle-box');
  toggleBoxes.forEach(box => {
    const checkbox = box.querySelector('input[type="checkbox"]');
    if (!checkbox) return;

    box.addEventListener('click', (e) => {
      // If click was directly on the checkbox/label, let browser handle the flip
      if (e.target !== checkbox && e.target.tagName !== 'LABEL') {
        checkbox.checked = !checkbox.checked;
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    checkbox.addEventListener('change', () => {
      if (checkbox.checked) {
        box.classList.add('active');
      } else {
        box.classList.remove('active');
      }
    });
  });

  // 7. Global Date Picker Initialization (Flatpickr)
  if (typeof flatpickr !== 'undefined') {
    document.querySelectorAll('.date-picker').forEach(input => {
      const formatSetting = (input.dataset.dateFormat || 'DD/MM/YYYY').toUpperCase();
      let fpFormat = 'd/m/Y';
      if (formatSetting === 'YYYY-MM-DD') {
        fpFormat = 'Y-m-d';
      } else if (formatSetting === 'DD-MM-YYYY') {
        fpFormat = 'd-m-Y';
      } else if (formatSetting === 'DD/MM/YYYY') {
        fpFormat = 'd/m/Y';
      }

      // Light restriction: limit characters to digits and date separators, max length 10
      input.setAttribute('maxlength', '10');
      input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9\/\-\.]/g, '').slice(0, 10);
      });

      flatpickr(input, {
        dateFormat: fpFormat,
        allowInput: true,
        monthSelectorType: 'static',
        disableMobile: true,
        onClose: function(selectedDates, dateStr, instance) {
          instance.element.dispatchEvent(new Event('change', { bubbles: true }));
          instance.element.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    });
  }
});
