/**
 * RACE FINANCE Offline PWA Queue & Conflict-Safe Sync Engine (IndexedDB)
 * Queues newly created offline bills locally with idempotent client_uuid tokens
 * and syncs safely to POST /api/invoices/sync when connectivity returns.
 */
(function () {
  if (!window.indexedDB) return;

  var DB_NAME = 'race_finance_offline_db';
  var DB_VERSION = 1;
  var STORE_QUEUE = 'offlineInvoices';
  var STORE_LOG = 'syncLog';

  function generateUuid() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return window.crypto.randomUUID();
    }
    return 'rf-' + Date.now() + '-' + Math.random().toString(16).slice(2, 10);
  }

  function openDb() {
    return new Promise(function (resolve, reject) {
      var req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = function (e) {
        var db = e.target.result;
        if (!db.objectStoreNames.contains(STORE_QUEUE)) {
          db.createObjectStore(STORE_QUEUE, { keyPath: 'client_uuid' });
        }
        if (!db.objectStoreNames.contains(STORE_LOG)) {
          db.createObjectStore(STORE_LOG, { keyPath: 'id', autoIncrement: true });
        }
      };
      req.onsuccess = function (e) { resolve(e.target.result); };
      req.onerror = function (e) { reject(e.target.error); };
    });
  }

  async function getAllQueue() {
    var db = await openDb();
    return new Promise(function (resolve, reject) {
      var tx = db.transaction(STORE_QUEUE, 'readonly');
      var req = tx.objectStore(STORE_QUEUE).getAll();
      req.onsuccess = function () { resolve(req.result || []); };
      req.onerror = function () { reject(req.error); };
    });
  }

  async function putQueueItem(item) {
    var db = await openDb();
    return new Promise(function (resolve, reject) {
      var tx = db.transaction(STORE_QUEUE, 'readwrite');
      tx.objectStore(STORE_QUEUE).put(item);
      tx.oncomplete = function () { resolve(); };
      tx.onerror = function () { reject(tx.error); };
    });
  }

  async function deleteQueueItem(uuid) {
    var db = await openDb();
    return new Promise(function (resolve, reject) {
      var tx = db.transaction(STORE_QUEUE, 'readwrite');
      tx.objectStore(STORE_QUEUE).delete(uuid);
      tx.oncomplete = function () { resolve(); };
      tx.onerror = function () { reject(tx.error); };
    });
  }

  function serializeFormToPayload(form) {
    var fd = new FormData(form);
    var payload = {};
    fd.forEach(function (val, key) {
      if (key === '_csrf') return;
      if (key.endsWith('[]')) {
        var base = key.slice(0, -2);
        if (!Array.isArray(payload[base])) payload[base] = [];
        payload[base].push(val);
      } else {
        payload[key] = val;
      }
    });
    return payload;
  }

  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    var inp = document.querySelector('input[name="_csrf"]');
    return inp ? inp.value : '';
  }

  async function refreshBadgeAndModal() {
    var badge = document.getElementById('offlineStatusBadge');
    var modalList = document.getElementById('offlineQueueList');
    var items = await getAllQueue();
    var pending = items.filter(function (x) { return x.status === 'pending'; }).length;
    var conflicts = items.filter(function (x) { return x.status === 'conflict'; }).length;

    if (badge) {
      if (!navigator.onLine) {
        badge.className = 'rf-btn rf-btn-amber rf-btn-sm w-full mb-3 py-1.5 justify-center text-xs';
        badge.innerHTML = '<i class="bi bi-wifi-off"></i> Offline' + (items.length ? ' (' + items.length + ')' : '');
      } else if (conflicts > 0) {
        badge.className = 'rf-btn rf-btn-danger rf-btn-sm w-full mb-3 py-1.5 justify-center text-xs';
        badge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Sync Conflicts (' + conflicts + ')';
      } else if (pending > 0) {
        badge.className = 'rf-btn rf-btn-secondary rf-btn-sm w-full mb-3 py-1.5 justify-center text-xs';
        badge.innerHTML = '<i class="bi bi-cloud-upload"></i> Pending Sync (' + pending + ')';
      } else {
        badge.className = 'rf-btn rf-btn-sage rf-btn-sm w-full mb-3 py-1.5 justify-center text-xs';
        badge.innerHTML = '<i class="bi bi-wifi"></i> Online';
      }
    }

    if (modalList) {
      if (!items.length) {
        modalList.innerHTML = '<div class="text-[#78746C] text-center py-6 text-xs">No queued offline bills on this device.</div>';
      } else {
        modalList.innerHTML = items.map(function (it) {
          var badgeHtml = it.status === 'conflict'
            ? '<span class="rf-badge rf-badge-rose">Conflict</span>'
            : '<span class="rf-badge rf-badge-amber">Pending</span>';
          var errHtml = it.error ? '<div class="text-xs text-[#8F2D2B] mt-1">' + escapeHtml(it.error) + '</div>' : '';
          return '<div class="border border-[#E2DFD7] rounded-xl p-3 mb-2 bg-[#FAF9F6]">'
            + '<div class="flex items-center justify-between gap-2">'
            + '<div class="text-xs"><strong class="text-[#1C1B18]">' + escapeHtml(it.payload.party_name || 'Customer') + '</strong> &bull; Total: ₹' + escapeHtml(String(it.payload.grand_total || '0')) + ' ' + badgeHtml + '</div>'
            + '<div class="flex items-center gap-1.5">'
            + '<button type="button" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2 btn-retry-sync" data-uuid="' + escapeHtml(it.client_uuid) + '">Retry</button>'
            + '<button type="button" class="rf-btn rf-btn-danger rf-btn-sm py-1 px-2 btn-discard-sync" data-uuid="' + escapeHtml(it.client_uuid) + '">Discard</button>'
            + '</div></div>'
            + '<div class="text-[11px] font-mono text-[#78746C] mt-1">UUID: ' + escapeHtml(it.client_uuid) + '</div>'
            + errHtml
            + '</div>';
        }).join('');
      }
    }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  var syncing = false;
  async function syncQueue(onlyUuid) {
    if (syncing || !navigator.onLine) return;
    syncing = true;
    try {
      var items = await getAllQueue();
      var csrf = getCsrfToken();

      for (var i = 0; i < items.length; i++) {
        var entry = items[i];
        if (onlyUuid && entry.client_uuid !== onlyUuid) continue;
        if (!onlyUuid && entry.status === 'conflict') continue; // Require explicit manual retry for conflicts

        try {
          var res = await fetch('/api/invoices/sync', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': csrf
            },
            body: JSON.stringify({
              client_uuid: entry.client_uuid,
              firm_id: entry.firm_id,
              payload: entry.payload,
              _csrf: csrf
            })
          });

          if (res.status === 401 || res.status === 403) {
            entry.error = 'Session expired or unauthorized — please sign in again and retry.';
            await putQueueItem(entry);
            break;
          }

          var data = await res.json();
          if (res.ok && data.success) {
            await deleteQueueItem(entry.client_uuid);
          } else {
            entry.status = 'conflict';
            entry.error = data.error || ('Sync failed (HTTP ' + res.status + ')');
            await putQueueItem(entry);
          }
        } catch (netErr) {
          // Remain pending on transient network loss
          break;
        }
      }
    } finally {
      syncing = false;
      await refreshBadgeAndModal();
    }
  }

  function attachInvoiceFormInterceptor() {
    var form = document.getElementById('invoiceForm');
    if (!form) return;
    if (form.dataset.isEdit === '1') return; // Only new invoices queue offline

    form.addEventListener('submit', async function (e) {
      if (navigator.onLine) return;
      e.preventDefault();
      var payload = serializeFormToPayload(form);
      var uuid = generateUuid();
      await putQueueItem({
        client_uuid: uuid,
        firm_id: parseInt(form.dataset.firmId || '0', 10),
        payload: payload,
        status: 'pending',
        created_at: new Date().toISOString(),
        error: null
      });
      await refreshBadgeAndModal();
      alert('You are currently offline. This bill has been saved safely to your device queue and will sync automatically when you reconnect.');
      window.location.href = '/dashboard';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    attachInvoiceFormInterceptor();
    refreshBadgeAndModal();

    var syncAllBtn = document.getElementById('btnSyncOfflineNow');
    if (syncAllBtn) {
      syncAllBtn.addEventListener('click', function () { syncQueue(null); });
    }

    var modalList = document.getElementById('offlineQueueList');
    if (modalList) {
      modalList.addEventListener('click', async function (e) {
        var retryBtn = e.target.closest('.btn-retry-sync');
        if (retryBtn) {
          await syncQueue(retryBtn.dataset.uuid);
          return;
        }
        var discBtn = e.target.closest('.btn-discard-sync');
        if (discBtn) {
          await deleteQueueItem(discBtn.dataset.uuid);
          await refreshBadgeAndModal();
        }
      });
    }

    if (navigator.onLine) {
      syncQueue(null);
    }
  });

  window.addEventListener('online', function () {
    refreshBadgeAndModal();
    syncQueue(null);
  });
  window.addEventListener('offline', refreshBadgeAndModal);

  window.RACE_OFFLINE = {
    syncNow: function () { return syncQueue(null); },
    refresh: refreshBadgeAndModal
  };
})();
