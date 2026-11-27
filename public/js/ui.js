/**
 * RACE FINANCE - Vanilla UI Controller (Zero Bootstrap JS)
 * Handles Modals, Dropdowns, Accordions/Collapse, Tabs, Dismissible Alerts, and Toasts.
 */
(function () {
  function getEl(target) {
    if (!target) return null;
    if (typeof target === 'string') {
      return document.querySelector(target.startsWith('#') ? target : '#' + target);
    }
    return target;
  }

  function openModal(target) {
    const modal = getEl(target);
    if (!modal) return null;
    modal.classList.remove('hidden');
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const focusable = modal.querySelector('input:not([type="hidden"]), select, textarea, button:not([data-ui-dismiss])');
    if (focusable) setTimeout(() => focusable.focus(), 40);
    return modal;
  }

  function closeModal(target) {
    const modal = getEl(target);
    if (!modal) return;
    modal.classList.remove('open');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.rf-modal.open')) {
      document.body.style.overflow = '';
    }
  }

  function closeAllModals() {
    document.querySelectorAll('.rf-modal.open').forEach(closeModal);
  }

  function closeAllDropdowns(exceptMenu) {
    document.querySelectorAll('[data-ui-dropdown-menu].open').forEach(menu => {
      if (menu !== exceptMenu) {
        menu.classList.remove('open');
        const wrap = menu.closest('[data-ui-dropdown]');
        const toggle = wrap ? wrap.querySelector('[data-ui-dropdown-toggle]') : null;
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function toggleDropdown(toggle) {
    const wrap = toggle.closest('[data-ui-dropdown]');
    if (!wrap) return;
    const menu = wrap.querySelector('[data-ui-dropdown-menu]');
    if (!menu) return;

    const isOpen = menu.classList.contains('open');
    closeAllDropdowns(menu);

    if (!isOpen) {
      menu.classList.remove('bottom-full', 'mb-1.5');
      menu.classList.add('open');
      toggle.setAttribute('aria-expanded', 'true');

      // Flip upward if overflowing bottom of viewport
      const rect = menu.getBoundingClientRect();
      if (rect.bottom > window.innerHeight - 12 && rect.top > rect.height + 16) {
        menu.classList.add('bottom-full', 'mb-1.5');
      }
    } else {
      menu.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  }

  function toggleCollapse(trigger) {
    const selector = trigger.getAttribute('data-ui-collapse');
    if (!selector) return;
    const panel = document.querySelector(selector);
    if (!panel) return;

    const parentSel = panel.getAttribute('data-ui-parent');
    const isCurrentlyOpen = !panel.classList.contains('hidden');

    if (parentSel) {
      const parent = document.querySelector(parentSel);
      if (parent) {
        parent.querySelectorAll('[data-ui-collapse-panel]').forEach(other => {
          if (other !== panel) {
            other.classList.add('hidden');
            const otherBtn = parent.querySelector(`[data-ui-collapse="#${other.id}"]`);
            if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
          }
        });
      }
    }

    if (isCurrentlyOpen) {
      panel.classList.add('hidden');
      trigger.setAttribute('aria-expanded', 'false');
    } else {
      panel.classList.remove('hidden');
      trigger.setAttribute('aria-expanded', 'true');
    }
  }

  function activateTab(tabBtn) {
    const targetSel = tabBtn.getAttribute('data-ui-tab');
    const tabsRoot = tabBtn.closest('[data-ui-tabs]');
    if (!targetSel || !tabsRoot) return;

    const scopeSelector = tabsRoot.getAttribute('data-ui-tabs-target');
    const panelsContainer = scopeSelector ? document.querySelector(scopeSelector) : tabsRoot.parentElement;

    tabsRoot.querySelectorAll('[data-ui-tab]').forEach(btn => {
      btn.classList.remove('active');
      btn.setAttribute('aria-selected', 'false');
    });
    tabBtn.classList.add('active');
    tabBtn.setAttribute('aria-selected', 'true');

    if (panelsContainer) {
      panelsContainer.querySelectorAll('[data-ui-tab-panel]').forEach(panel => {
        panel.classList.add('hidden');
      });
      const targetPanel = panelsContainer.querySelector(targetSel) || document.querySelector(targetSel);
      if (targetPanel) targetPanel.classList.remove('hidden');
    }
  }

  function showToast(message, type = 'info', duration = 3800) {
    let container = document.getElementById('rfToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'rfToastContainer';
      container.className = 'fixed bottom-5 right-5 z-[1100] flex flex-col gap-2 max-w-sm w-full pointer-events-none px-4 sm:px-0';
      document.body.appendChild(container);
    }

    const styles = {
      success: 'bg-[#1C1B18] text-white border-[#2E5A36]',
      danger: 'bg-[#8F2D2B] text-white border-[#752321]',
      warning: 'bg-[#8A5A00] text-white border-[#6E4700]',
      info: 'bg-[#1C1B18] text-white border-[#32302B]'
    };

    const toastEl = document.createElement('div');
    toastEl.className = `pointer-events-auto flex items-center justify-between gap-3 rounded-xl border px-4 py-3 text-xs font-medium shadow-lg transition-all duration-200 ${styles[type] || styles.info}`;
    toastEl.setAttribute('role', 'status');

    const textSpan = document.createElement('span');
    textSpan.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'text-white/70 hover:text-white bg-transparent border-0 cursor-pointer p-0.5 leading-none';
    closeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
    closeBtn.addEventListener('click', () => toastEl.remove());

    toastEl.appendChild(textSpan);
    toastEl.appendChild(closeBtn);
    container.appendChild(toastEl);

    if (duration > 0) {
      setTimeout(() => {
        toastEl.style.opacity = '0';
        setTimeout(() => toastEl.remove(), 200);
      }, duration);
    }
    return toastEl;
  }

  document.addEventListener('click', function (e) {
    // Modal open trigger
    const modalOpenBtn = e.target.closest('[data-ui-modal]');
    if (modalOpenBtn) {
      e.preventDefault();
      openModal(modalOpenBtn.getAttribute('data-ui-modal'));
      return;
    }

    // Modal dismiss trigger
    const modalCloseBtn = e.target.closest('[data-ui-dismiss="modal"]');
    if (modalCloseBtn) {
      e.preventDefault();
      const modal = modalCloseBtn.closest('.rf-modal');
      if (modal) closeModal(modal);
      return;
    }

    // Alert dismiss trigger
    const alertCloseBtn = e.target.closest('[data-ui-dismiss="alert"]');
    if (alertCloseBtn) {
      e.preventDefault();
      const alertBox = alertCloseBtn.closest('[data-ui-alert]');
      if (alertBox) alertBox.remove();
      return;
    }

    // Clicking modal backdrop closes modal
    if (e.target.classList && e.target.classList.contains('rf-modal') && e.target.classList.contains('open')) {
      closeModal(e.target);
      return;
    }

    // Dropdown toggle
    const dropdownToggle = e.target.closest('[data-ui-dropdown-toggle]');
    if (dropdownToggle) {
      e.preventDefault();
      e.stopPropagation();
      toggleDropdown(dropdownToggle);
      return;
    }

    // Collapse / accordion toggle
    const collapseBtn = e.target.closest('[data-ui-collapse]');
    if (collapseBtn) {
      e.preventDefault();
      toggleCollapse(collapseBtn);
      return;
    }

    // Tab switch
    const tabBtn = e.target.closest('[data-ui-tab]');
    if (tabBtn) {
      e.preventDefault();
      activateTab(tabBtn);
      return;
    }

    // Close open dropdowns when clicking outside or clicking a dropdown action link
    if (!e.target.closest('[data-ui-dropdown-menu]') || e.target.closest('.rf-dropdown-item')) {
      closeAllDropdowns();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAllDropdowns();
      closeAllModals();
    }
  });

  window.RFUI = {
    openModal,
    closeModal,
    closeAllModals,
    closeAllDropdowns,
    toast: showToast
  };
})();
