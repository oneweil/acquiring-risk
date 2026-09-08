/**
 * 订单监控列表（AJAX 拉取 + 前端渲染）
 */
var OrderPage = (function () {
  'use strict';

  var LIST_URL = '/admin/order/list';
  var LIVE_INTERVAL_MS = 5000;
  var COLSPAN = 8;
  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    liveOn: false,
    liveTimer: null
  };

  var RISK_LT = {
    '极高风险': 'bg-purple-lt',
    '高风险': 'bg-red-lt',
    '中风险': 'bg-yellow-lt',
    '低风险': 'bg-green-lt',
    '未评估': 'bg-secondary-lt'
  };

  var STATUS_LT = {
    '成功': 'bg-green-lt',
    '拦截': 'bg-red-lt',
    '失败': 'bg-secondary-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function dash(val) {
    if (val === null || val === undefined || val === '') return '—';
    return escapeHtml(String(val));
  }

  function formatAmount(amount) {
    if (amount === null || amount === undefined || amount === '') return '—';
    return Number(amount).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function getFilters() {
    var form = document.getElementById('orderFilterForm');
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

  function applyQueryToForm() {
    var qs = new URLSearchParams(window.location.search);
    var form = document.getElementById('orderFilterForm');
    if (!form) return;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name) return;
      if (qs.has(el.name)) el.value = qs.get(el.name);
    });

    state.page = Math.max(1, parseInt(qs.get('page') || '1', 10) || 1);
    state.pageSize = ListPage.normalizePageSize(parseInt(qs.get('pageSize') || '10', 10) || 10);
  }

  function syncUrl() {
    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));
    var qs = params.toString();
    var url = window.location.pathname + (qs ? '?' + qs : '');
    window.history.replaceState(null, '', url);
  }

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('orderTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('orderTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无订单数据</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      var riskLabel = row.risk_level_label || '未评估';
      var statusLabel = row.status_label || row.status || '—';
      var riskCls = RISK_LT[riskLabel] || 'bg-secondary-lt';
      var statusCls = STATUS_LT[statusLabel] || 'bg-secondary-lt';
      var hitRule = row.hit_rule
        ? '<span class="badge bg-secondary-lt">' + escapeHtml(row.hit_rule) + '</span>'
        : '';
      var geo = [row.card_type, row.card_country].filter(Boolean).join(' · ') || '—';
      var ipGeo = row.ip_country ? ('IP ' + row.ip_country) : 'IP —';

      return ''
        + '<tr>'
        +   '<td>'
        +     '<div>' + dash(row.merchant_name) + '</div>'
        +     '<div class="text-secondary small font-monospace">' + dash(row.merchant_id) + '</div>'
        +   '</td>'
        +   '<td>'
        +     '<div class="font-monospace">' + dash(row.order_no) + '</div>'
        +     '<div class="text-secondary small font-monospace">' + dash(row.channel_no) + '</div>'
        +   '</td>'
        +   '<td>'
        +     '<div>' + dash(row.currency) + ' ' + formatAmount(row.amount) + '</div>'
        +     '<div class="text-secondary small">' + dash(row.trade_time) + '</div>'
        +   '</td>'
        +   '<td class="text-secondary">'
        +     '<div>' + escapeHtml(geo) + '</div>'
        +     '<div class="small">' + escapeHtml(ipGeo) + '</div>'
        +   '</td>'
        +   '<td class="text-secondary">' + dash(row.three_ds) + '</td>'
        +   '<td>'
        +     '<div class="d-flex flex-wrap gap-1 align-items-center">'
        +       '<span class="badge ' + riskCls + '">' + escapeHtml(riskLabel) + '</span>'
        +       hitRule
        +     '</div>'
        +   '</td>'
        +   '<td><span class="badge ' + statusCls + '">' + escapeHtml(statusLabel) + '</span></td>'
        +   '<td>'
        +     '<div class="dropdown">'
        +       '<button class="btn btn-ghost-secondary btn-icon btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="订单操作">'
        +         '<i class="ti ti-dots"></i>'
        +       '</button>'
        +       '<div class="dropdown-menu dropdown-menu-end">'
        +         '<button type="button" class="dropdown-item js-list-detail" data-id="'
        +           escapeHtml(String(row.channel_no || row.order_no || '')) + '">查看详情</button>'
        +         '<a class="dropdown-item disabled" href="#" tabindex="-1" aria-disabled="true">关联预警</a>'
        +       '</div>'
        +     '</div>'
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var summary = document.getElementById('orderTableSummary');
    var pager = document.getElementById('orderTablePager');
    var sizeWrap = document.getElementById('orderPageSize');
    if (!summary || !pager) return;

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
    if (page) state.page = Math.max(1, page);
    setLoading(true);
    syncUrl();

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    fetch(LIST_URL + '?' + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (!json || json.code !== 0 || !json.data) {
          var tbody = document.getElementById('orderTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">'
              + escapeHtml((json && json.msg) || '加载失败') + '</td></tr>';
          }
          return;
        }
        var data = json.data;
        state.page = data.current_page || state.page;
        renderRows(data.data || []);
        renderPager(data);
      })
      .catch(function () {
        state.loading = false;
        var tbody = document.getElementById('orderTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
        }
      });
  }

  function setLiveButton() {
    var btn = document.getElementById('orderLiveBtn');
    if (!btn) return;
    if (state.liveOn) {
      btn.className = 'btn btn-danger btn-sm';
      btn.innerHTML = '<i class="ti ti-player-stop me-1"></i>停止实时刷新';
    } else {
      btn.className = 'btn btn-success btn-sm';
      btn.innerHTML = '<i class="ti ti-player-play me-1"></i>开启实时刷新';
    }
  }

  function stopLive() {
    state.liveOn = false;
    if (state.liveTimer) {
      clearInterval(state.liveTimer);
      state.liveTimer = null;
    }
    setLiveButton();
  }

  function toggleLive() {
    if (state.liveOn) {
      stopLive();
      return;
    }
    state.liveOn = true;
    setLiveButton();
    state.liveTimer = setInterval(function () {
      if (!state.loading) loadList();
    }, LIVE_INTERVAL_MS);
  }

  function bindEvents() {
    var form = document.getElementById('orderFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }

    var resetBtn = document.getElementById('orderFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        Array.prototype.forEach.call(form ? form.elements : [], function (el) {
          if (!el.name) return;
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else if (el.type !== 'submit' && el.type !== 'button') el.value = '';
        });
        state.pageSize = 10;
        loadList(1);
      });
    }

    var liveBtn = document.getElementById('orderLiveBtn');
    if (liveBtn) {
      liveBtn.addEventListener('click', toggleLive);
    }

    var footer = document.getElementById('orderTableFooter');
    if (footer) {
      footer.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page]');
        if (link) {
          e.preventDefault();
          var page = parseInt(link.getAttribute('data-page'), 10);
          if (!isNaN(page)) loadList(page);
          return;
        }
        var sizeBtn = e.target.closest('[data-page-size]');
        if (sizeBtn) {
          e.preventDefault();
          var size = ListPage.normalizePageSize(parseInt(sizeBtn.getAttribute('data-page-size'), 10) || 10);
          if (size !== state.pageSize) {
            state.pageSize = size;
            loadList(1);
          }
        }
      });
    }

    window.addEventListener('beforeunload', stopLive);
  }

  function init() {
    applyQueryToForm();
    bindEvents();
    loadList(state.page);
  }

  return { init: init, loadList: loadList };
})();
