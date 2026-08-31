/**
 * 订单监控列表（AJAX 拉取 + 前端渲染）
 */
var OrderPage = (function () {
  'use strict';

  var LIST_URL = '/admin/order/list';
  var LIVE_INTERVAL_MS = 5000;
  var state = {
    page: 1,
    loading: false,
    liveOn: false,
    liveTimer: null
  };

  var AVATAR_CLASSES = [
    'bg-primary-lt text-primary',
    'bg-azure-lt text-azure',
    'bg-indigo-lt text-indigo',
    'bg-purple-lt text-purple',
    'bg-teal-lt text-teal'
  ];

  var RISK_LT = {
    '极高风险': 'bg-purple-lt',
    '高风险': 'bg-red-lt',
    '中风险': 'bg-yellow-lt',
    '低风险': 'bg-green-lt'
  };

  var STATUS_LT = {
    '成功': 'bg-green-lt',
    '拦截': 'bg-red-lt',
    '审核中': 'bg-yellow-lt',
    '失败': 'bg-secondary-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function merchantInitials(name) {
    name = String(name || '').trim();
    if (!name) return '—';
    var parts = name.split(/\s+/);
    if (parts.length >= 2) {
      return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
  }

  function formatAmount(amount) {
    return Number(amount).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function getFilters() {
    var form = document.getElementById('orderFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    // HTML5：带 form="orderFilterForm" 的控件也会出现在 form.elements 中
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
    var tbody = document.getElementById('orderTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="9" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderRows(items, page, pageSize) {
    var tbody = document.getElementById('orderTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center text-secondary py-5">暂无订单数据</td></tr>';
      return;
    }

    var offset = (page - 1) * pageSize;
    tbody.innerHTML = items.map(function (row, idx) {
      var i = offset + idx + 1;
      var avatarCls = AVATAR_CLASSES[(i - 1) % AVATAR_CLASSES.length];
      var riskCls = RISK_LT[row.risk_level] || 'bg-secondary-lt';
      var statusCls = STATUS_LT[row.status] || 'bg-secondary-lt';
      var hitRule = row.hit_rule && row.hit_rule !== '无'
        ? '<span class="badge bg-secondary-lt">' + escapeHtml(row.hit_rule) + '</span>'
        : '';

      return ''
        + '<tr>'
        +   '<td>'
        +     '<input class="form-check-input m-0 align-middle table-selectable-check" type="checkbox" aria-label="选择订单 ' + escapeHtml(row.order_no) + '" value="' + escapeHtml(row.order_no) + '">'
        +   '</td>'
        +   '<td>'
        +     '<div class="d-flex align-items-center">'
        +       '<span class="avatar avatar-xs rounded me-2 ' + avatarCls + '">' + escapeHtml(merchantInitials(row.merchant_name)) + '</span>'
        +       '<div>'
        +         '<div>' + escapeHtml(row.merchant_name) + '</div>'
        +         '<div class="text-secondary small font-monospace">' + escapeHtml(row.merchant_id) + '</div>'
        +       '</div>'
        +     '</div>'
        +   '</td>'
        +   '<td>'
        +     '<div class="font-monospace">' + escapeHtml(row.order_no) + '</div>'
        +     '<div class="text-secondary small font-monospace">' + escapeHtml(row.channel_no) + '</div>'
        +   '</td>'
        +   '<td>'
        +     '<div>' + escapeHtml(row.currency) + ' ' + formatAmount(row.amount) + '</div>'
        +     '<div class="text-secondary small">' + escapeHtml(row.trade_time) + '</div>'
        +   '</td>'
        +   '<td class="text-secondary">'
        +     '<div>' + escapeHtml(row.card_type) + ' · ' + escapeHtml(row.card_country) + '</div>'
        +     '<div class="small">IP ' + escapeHtml(row.ip_country) + '</div>'
        +   '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.three_ds) + '</td>'
        +   '<td>'
        +     '<div class="d-flex flex-wrap gap-1 align-items-center">'
        +       '<span class="badge ' + riskCls + '">' + escapeHtml(row.risk_level) + '</span>'
        +       hitRule
        +     '</div>'
        +   '</td>'
        +   '<td><span class="badge ' + statusCls + '">' + escapeHtml(row.status) + '</span></td>'
        +   '<td>'
        +     '<div class="dropdown">'
        +       '<button class="btn btn-ghost-secondary btn-icon btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="订单操作">'
        +         '<i class="ti ti-dots"></i>'
        +       '</button>'
        +       '<div class="dropdown-menu dropdown-menu-end">'
        +         '<button type="button" class="dropdown-item js-list-detail" data-id="' + escapeHtml(row.order_no) + '">查看详情</button>'
        +         '<a class="dropdown-item" href="#">关联预警</a>'
        +       '</div>'
        +     '</div>'
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var footer = document.getElementById('orderTableFooter');
    var summary = document.getElementById('orderTableSummary');
    var pager = document.getElementById('orderTablePager');
    if (!footer || !summary || !pager) return;

    var total = data.total;
    var page = data.page;
    var lastPage = data.last_page;

    if (total <= 0) {
      summary.textContent = '暂无数据';
      pager.innerHTML = '';
      return;
    }

    summary.innerHTML = '共 <strong>' + total + '</strong> 条记录，第 ' + page + ' / ' + lastPage + ' 页';

    if (lastPage <= 1) {
      pager.innerHTML = '';
      return;
    }

    var html = '<ul class="pagination pagination-sm mb-0">';
    html += pageItem(page - 1, '上一页', page <= 1);
    var windowSize = 2;
    var start = Math.max(1, page - windowSize);
    var end = Math.min(lastPage, page + windowSize);
    if (start > 1) {
      html += pageItem(1, '1', false, page === 1);
      if (start > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    for (var p = start; p <= end; p++) {
      html += pageItem(p, String(p), false, p === page);
    }
    if (end < lastPage) {
      if (end < lastPage - 1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      html += pageItem(lastPage, String(lastPage), false, page === lastPage);
    }
    html += pageItem(page + 1, '下一页', page >= lastPage);
    html += '</ul>';
    pager.innerHTML = html;
  }

  function pageItem(page, label, disabled, active) {
    if (disabled) {
      return '<li class="page-item disabled"><span class="page-link">' + escapeHtml(label) + '</span></li>';
    }
    if (active) {
      return '<li class="page-item active" aria-current="page"><span class="page-link">' + escapeHtml(label) + '</span></li>';
    }
    return '<li class="page-item"><a class="page-link" href="#" data-page="' + page + '">' + escapeHtml(label) + '</a></li>';
  }

  function loadList(page) {
    if (state.loading) return;
    state.page = Math.max(1, page || 1);
    setLoading(true);

    var params = getFilters();
    params.set('page', String(state.page));

    // 同步 URL，便于刷新/分享筛选状态
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
          var tbody = document.getElementById('orderTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-5">'
              + escapeHtml(json.msg || '加载失败') + '</td></tr>';
          }
          return;
        }
        var data = json.data;
        state.page = data.page;
        renderRows(data.items || [], data.page, data.page_size);
        renderPager(data);

        var selectAll = document.getElementById('orderSelectAll');
        if (selectAll) selectAll.checked = false;
      })
      .catch(function () {
        state.loading = false;
        var tbody = document.getElementById('orderTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
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
      if (!state.loading) loadList(state.page);
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
        document.querySelectorAll('[form="orderFilterForm"]').forEach(function (el) {
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else if (el.type !== 'submit' && el.type !== 'button') el.value = '';
        });
        if (form) form.reset();
        loadList(1);
      });
    }

    var liveBtn = document.getElementById('orderLiveBtn');
    if (liveBtn) {
      liveBtn.addEventListener('click', toggleLive);
    }

    var pager = document.getElementById('orderTablePager');
    if (pager) {
      pager.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page]');
        if (!link) return;
        e.preventDefault();
        var page = parseInt(link.getAttribute('data-page'), 10);
        if (!isNaN(page)) loadList(page);
      });
    }

    var selectAll = document.getElementById('orderSelectAll');
    if (selectAll) {
      selectAll.addEventListener('change', function (e) {
        document.querySelectorAll('.table-selectable-check').forEach(function (cb) {
          cb.checked = e.target.checked;
        });
      });
    }

    window.addEventListener('beforeunload', stopLive);
  }

  function applyQueryToForm() {
    var params = new URLSearchParams(window.location.search);
    document.querySelectorAll('[form="orderFilterForm"]').forEach(function (el) {
      if (!el.name) return;
      if (params.has(el.name)) el.value = params.get(el.name);
    });
    var page = parseInt(params.get('page') || '1', 10);
    state.page = isNaN(page) ? 1 : Math.max(1, page);
  }

  function init() {
    applyQueryToForm();
    bindEvents();
    loadList(state.page);
  }

  return { init: init, loadList: loadList };
})();
