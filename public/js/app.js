/**
 * Main Client Application Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Instant Table Quick Search Filter
  const tableSearchInputs = document.querySelectorAll('[data-table-search]');
  tableSearchInputs.forEach(input => {
    const targetTableId = input.dataset.tableSearch;
    const table = document.getElementById(targetTableId);
    if (!table) return;

    input.addEventListener('input', () => {
      const query = input.value.toLowerCase().trim();
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(query)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });

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
          th.classList.add('table-primary');
          if (sortDirection === 'asc') {
            iconSpan.innerHTML = '<i class="bi bi-arrow-up text-primary fw-bold"></i>';
          } else {
            iconSpan.innerHTML = '<i class="bi bi-arrow-down text-primary fw-bold"></i>';
          }
        } else {
          th.removeAttribute('data-current-direction');
          th.classList.remove('table-primary');
          iconSpan.innerHTML = '<i class="bi bi-arrow-down-up text-muted"></i>';
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
