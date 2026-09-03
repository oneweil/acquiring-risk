/**
 * 黑名单管理（AJAX 列表 + 新增/编辑 Modal）
 */
var BlacklistPage = (function () {
  'use strict';

  var LIST_URL = '/admin/blacklist/list';
  var SAVE_URL = '/admin/blacklist/save';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    saving: false,
    modal: null
  };

  var COLSPAN = 8;

  var STATUS_LT = {
    '生效中': 'bg-green-lt',
    '已失效': 'bg-secondary-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function getFilters() {
    var form = document.getElementById('blacklistFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    return params;
  }

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('blacklistTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('blacklistTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无黑名单数据</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      var statusCls = STATUS_LT[row.status_label] || 'bg-secondary-lt';
      var expiry = row.expiry_date ? escapeHtml(row.expiry_date) : '长期';
      var payload = encodeURIComponent(JSON.stringify(row));

      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + escapeHtml(row.code) + '</td>'
        +   '<td>' + escapeHtml(row.type_label) + '</td>'
        +   '<td class="font-monospace">' + escapeHtml(row.value) + '</td>'
        +   '<td class="text-secondary text-wrap" style="max-width:240px;">' + escapeHtml(row.reason) + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.effective_date) + '</td>'
        +   '<td class="text-secondary">' + expiry + '</td>'
        +   '<td><span class="badge ' + statusCls + '">' + escapeHtml(row.status_label) + '</span></td>'
        +   '<td>'
        +     '<button type="button" class="btn btn-ghost-primary btn-sm js-blacklist-edit" data-row="' + payload + '">编辑</button>'
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var footer = document.getElementById('blacklistTableFooter');
    var summary = document.getElementById('blacklistTableSummary');
    var pager = document.getElementById('blacklistTablePager');
    var sizeWrap = document.getElementById('blacklistPageSize');
    if (!footer || !summary || !pager) return;

    var total = data.total;
    var page = data.current_page;
    var lastPage = data.last_page;
    var perPage = data.per_page || state.pageSize;
    state.pageSize = ListPage.normalizePageSize(perPage);
    if (sizeWrap) {
      sizeWrap.innerHTML = ListPage.renderPageSizeDropdown(state.pageSize);
    }

    if (total <= 0) {
      summary.textContent = '暂无数据';
      pager.innerHTML = '';
      return;
    }

    summary.textContent = '共 ' + total + ' 条记录';
    pager.innerHTML = ListPage.renderPaginationHtml(page, lastPage);
  }

  function loadList(page) {
    if (state.loading) return;
    state.page = Math.max(1, page || 1);
    setLoading(true);

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    var qs = params.toString();
    var nextUrl = qs ? (window.location.pathname + '?' + qs) : window.location.pathname;
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', nextUrl);
    }

    fetch(LIST_URL + '?' + qs, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (json.code !== 0 || !json.data) {
          var tbody = document.getElementById('blacklistTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">'
              + escapeHtml(json.msg || '加载失败') + '</td></tr>';
          }
          return;
        }
        var data = json.data;
        state.page = data.current_page;
        renderRows(data.data || []);
        renderPager(data);
      })
      .catch(function () {
        state.loading = false;
        var tbody = document.getElementById('blacklistTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
        }
      });
  }

  function syncExpiryModeUi() {
    var mode = document.getElementById('blExpiryMode');
    var wrap = document.getElementById('blExpiryDateWrap');
    var dateInput = document.getElementById('blExpiryDate');
    if (!mode || !wrap) return;
    var isCustom = mode.value === 'custom';
    wrap.style.display = isCustom ? '' : 'none';
    if (dateInput) {
      dateInput.required = isCustom;
      if (!isCustom) dateInput.value = '';
    }
  }

  function resetForm() {
    var form = document.getElementById('blacklistForm');
    if (form) form.reset();
    var idEl = document.getElementById('blId');
    if (idEl) idEl.value = '';
    var status = document.getElementById('blStatus');
    if (status) status.value = '1';
    var mode = document.getElementById('blExpiryMode');
    if (mode) mode.value = 'long';
    syncExpiryModeUi();
  }

  function openAddModal() {
    resetForm();
    var title = document.getElementById('blacklistModalLabel');
    if (title) title.textContent = '新增黑名单';
    if (state.modal) state.modal.show();
  }

  function openEditModal(row) {
    resetForm();
    var title = document.getElementById('blacklistModalLabel');
    if (title) title.textContent = '编辑黑名单';

    var idEl = document.getElementById('blId');
    if (idEl) idEl.value = row.id || '';
    var typeEl = document.getElementById('blType');
    if (typeEl) typeEl.value = row.type || '';
    var valueEl = document.getElementById('blValue');
    if (valueEl) valueEl.value = row.value || '';
    var reasonEl = document.getElementById('blReason');
    if (reasonEl) reasonEl.value = row.reason || '';
    var statusEl = document.getElementById('blStatus');
    if (statusEl) statusEl.value = row.status ? '1' : '0';

    var modeEl = document.getElementById('blExpiryMode');
    var dateEl = document.getElementById('blExpiryDate');
    if (row.expiry_date) {
      if (modeEl) modeEl.value = 'custom';
      if (dateEl) dateEl.value = String(row.expiry_date).slice(0, 10);
    } else {
      if (modeEl) modeEl.value = 'long';
    }
    syncExpiryModeUi();

    if (state.modal) state.modal.show();
  }

  function saveForm(e) {
    e.preventDefault();
    if (state.saving) return;

    var form = document.getElementById('blacklistForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;

    var mode = document.getElementById('blExpiryMode');
    var dateEl = document.getElementById('blExpiryDate');
    if (mode && mode.value === 'custom' && dateEl && !dateEl.value) {
      alert('请填写有效的到期日期');
      dateEl.focus();
      return;
    }

    var fd = new FormData(form);
    state.saving = true;
    var btn = document.getElementById('blacklistSaveBtn');
    if (btn) {
      btn.disabled = true;
      btn.textContent = '保存中…';
    }

    fetch(SAVE_URL, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.saving = false;
        if (btn) {
          btn.disabled = false;
          btn.textContent = '保存';
        }
        if (json.code !== 0) {
          alert(json.msg || '保存失败');
          return;
        }
        if (state.modal) state.modal.hide();
        loadList(state.page);
      })
      .catch(function () {
        state.saving = false;
        if (btn) {
          btn.disabled = false;
          btn.textContent = '保存';
        }
        alert('网络错误，请稍后重试');
      });
  }

  function bindEvents() {
    var form = document.getElementById('blacklistFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }

    var resetBtn = document.getElementById('blacklistFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('[form="blacklistFilterForm"]').forEach(function (el) {
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else if (el.type !== 'submit' && el.type !== 'button') el.value = '';
        });
        if (form) form.reset();
        loadList(1);
      });
    }

    var pager = document.getElementById('blacklistTablePager');
    if (pager) {
      pager.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page]');
        if (!link) return;
        e.preventDefault();
        var page = parseInt(link.getAttribute('data-page'), 10);
        if (!isNaN(page)) loadList(page);
      });
    }

    var sizeWrap = document.getElementById('blacklistPageSize');
    if (sizeWrap) {
      sizeWrap.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page-size]');
        if (!link) return;
        e.preventDefault();
        var size = ListPage.normalizePageSize(link.getAttribute('data-page-size'));
        if (size === state.pageSize) return;
        state.pageSize = size;
        loadList(1);
      });
    }

    var addBtn = document.getElementById('blacklistAddBtn');
    if (addBtn) {
      addBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openAddModal();
      });
    }

    var modeEl = document.getElementById('blExpiryMode');
    if (modeEl) {
      modeEl.addEventListener('change', syncExpiryModeUi);
    }

    var saveFormEl = document.getElementById('blacklistForm');
    if (saveFormEl) {
      saveFormEl.addEventListener('submit', saveForm);
    }

    var tbody = document.getElementById('blacklistTableBody');
    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-blacklist-edit');
        if (!btn) return;
        e.preventDefault();
        try {
          var row = JSON.parse(decodeURIComponent(btn.getAttribute('data-row') || ''));
          openEditModal(row);
        } catch (err) {
          alert('无法打开编辑表单');
        }
      });
    }
  }

  function applyQueryToForm() {
    var params = new URLSearchParams(window.location.search);
    document.querySelectorAll('[form="blacklistFilterForm"]').forEach(function (el) {
      if (!el.name) return;
      if (params.has(el.name)) el.value = params.get(el.name);
    });
    var page = parseInt(params.get('page') || '1', 10);
    state.page = isNaN(page) ? 1 : Math.max(1, page);
    state.pageSize = ListPage.normalizePageSize(params.get('pageSize') || '10');
  }

  function init() {
    var modalEl = document.getElementById('blacklistModal');
    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal);
    if (modalEl && ModalCtor) {
      state.modal = ModalCtor.getOrCreateInstance(modalEl);
    }

    applyQueryToForm();
    bindEvents();
    syncExpiryModeUi();
    loadList(state.page);
  }

  return { init: init, loadList: loadList };
})();