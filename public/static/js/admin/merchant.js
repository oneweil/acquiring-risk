/**
 * 商户列表（KPI + AJAX 筛选分页 + 详情 Modal + 批量重评占位）
 */
var MerchantPage = (function () {
  'use strict';

  var LIST_URL = '/admin/merchant/list';
  var STATS_URL = '/admin/merchant/stats';
  var DETAIL_URL = '/admin/merchant/detail';
  var REASSESS_URL = '/admin/merchant/reassess';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    detailModal: null
  };

  var COLSPAN = 14;

  var RISK_LT = {
    '低风险': 'bg-green-lt',
    '中风险': 'bg-yellow-lt',
    '高风险': 'bg-red-lt'
  };

  var TRADING_LT = {
    '正常': 'bg-green-lt',
    '观察': 'bg-yellow-lt',
    '受限': 'bg-orange-lt',
    '暂停': 'bg-red-lt',
    '未开通': 'bg-secondary-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function dash(val) {
    if (val === null || val === undefined || val === '') return '—';
    return escapeHtml(String(val));
  }

  function notifySuccess(message) {
    if (window.AdminUi && typeof AdminUi.success === 'function') {
      AdminUi.success(message);
      return;
    }
    window.alert(message);
  }

  function notifyError(message) {
    if (window.AdminUi && typeof AdminUi.danger === 'function') {
      AdminUi.danger(message);
      return;
    }
    window.alert(message);
  }

  function getFilters() {
    var form = document.getElementById('merchantFilterForm');
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
    var form = document.getElementById('merchantFilterForm');
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
    var tbody = document.getElementById('merchantTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function formatRate(val) {
    if (val === null || val === undefined || val === '') return '—';
    return escapeHtml(String(val)) + '%';
  }

  function formatAmount(val) {
    if (val === null || val === undefined || val === '') return '—';
    var n = Number(val);
    if (isNaN(n)) return dash(val);
    return escapeHtml(n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
  }

  function badge(label, clsMap) {
    if (!label) return '—';
    var cls = clsMap[label] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('merchantTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无商户数据</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + dash(row.merchant_id) + '</td>'
        +   '<td>' + dash(row.name) + '</td>'
        +   '<td>' + dash(row.industry) + '</td>'
        +   '<td class="text-secondary">' + dash(row.onboard_date) + '</td>'
        +   '<td>' + (row.risk_score != null ? escapeHtml(String(row.risk_score)) : '—') + '</td>'
        +   '<td>' + badge(row.risk_level_label, RISK_LT) + '</td>'
        +   '<td>' + formatRate(row.chargeback_rate) + '</td>'
        +   '<td>' + formatRate(row.fraud_rate) + '</td>'
        +   '<td class="text-secondary">' + dash(row.assessed_at) + '</td>'
        +   '<td>' + (row.today_count != null ? escapeHtml(String(row.today_count)) : '—') + '</td>'
        +   '<td>' + formatAmount(row.today_amount) + '</td>'
        +   '<td>' + badge(row.review_status_label, { '通过': 'bg-green-lt', '不通过': 'bg-red-lt' }) + '</td>'
        +   '<td>' + badge(row.trading_status_label, TRADING_LT) + '</td>'
        +   '<td>'
        +     '<button type="button" class="btn btn-ghost-primary btn-sm js-merchant-detail" data-id="'
        +       escapeHtml(String(row.merchant_id || '')) + '">详情</button>'
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var summary = document.getElementById('merchantTableSummary');
    var pager = document.getElementById('merchantTablePager');
    var sizeWrap = document.getElementById('merchantPageSize');
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

  function renderStats(stats) {
    var map = {
      mKpiTotal: stats.total,
      mKpiActive: stats.active,
      mKpiRestricted: stats.restricted,
      mKpiLow: stats.low,
      mKpiMid: stats.mid,
      mKpiHigh: stats.high
    };
    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = map[id] != null ? String(map[id]) : '—';
    });
  }

  function loadStats() {
    return fetch(STATS_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '统计加载失败');
          return;
        }
        renderStats(json.data || {});
      })
      .catch(function () {
        notifyError('统计加载失败');
      });
  }

  function loadList() {
    if (state.loading) return;
    setLoading(true);
    syncUrl();

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    fetch(LIST_URL + '?' + params.toString(), {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '列表加载失败');
          var tbody = document.getElementById('merchantTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">加载失败</td></tr>';
          }
          return;
        }
        var data = json.data || {};
        state.page = data.current_page || state.page;
        renderRows(data.data || []);
        renderPager(data);
      })
      .catch(function () {
        state.loading = false;
        notifyError('列表加载失败');
      });
  }

  function detailItem(label, valueHtml) {
    return ''
      + '<div class="col-6 col-md-4">'
      +   '<div class="text-secondary small">' + escapeHtml(label) + '</div>'
      +   '<div class="mt-1">' + valueHtml + '</div>'
      + '</div>';
  }

  function renderAssessTable(details) {
    if (!details || !details.length) {
      return '<div class="text-secondary text-center py-3">暂无评估明细</div>';
    }
    return ''
      + '<div class="table-responsive">'
      + '<table class="table table-sm table-vcenter">'
      + '<thead><tr><th>维度</th><th>权重</th><th>原始分</th><th>加权分</th><th>命中评估规则</th></tr></thead>'
      + '<tbody>'
      + details.map(function (d) {
        return '<tr>'
          + '<td>' + dash(d.name) + '</td>'
          + '<td>' + (d.weight != null ? escapeHtml(String(d.weight)) + '%' : '—') + '</td>'
          + '<td>' + (d.raw != null ? escapeHtml(String(d.raw)) : '—') + '</td>'
          + '<td><strong>' + (d.weighted != null ? escapeHtml(String(d.weighted)) : '—') + '</strong></td>'
          + '<td class="text-secondary small text-wrap" style="max-width:280px;">' + dash(d.rule_content) + '</td>'
          + '</tr>';
      }).join('')
      + '</tbody></table></div>';
  }

  function renderDetail(m) {
    var rejected = m.review_status === 'rejected';
    var title = document.getElementById('merchantDetailModalLabel');
    var sub = document.getElementById('merchantDetailSub');
    var body = document.getElementById('merchantDetailBody');
    if (title) title.textContent = rejected ? '商户入网拒绝详情' : '商户详情';
    if (sub) sub.textContent = (m.merchant_id || '') + (m.name ? ' · ' + m.name : '');

    var scoreText = m.risk_score != null ? escapeHtml(String(m.risk_score)) + ' / 100' : '—';
    var complianceText = m.compliance_hits != null ? escapeHtml(String(m.compliance_hits)) + ' 条' : '—';

    body.innerHTML = ''
      + '<div class="mb-3">'
      +   '<h4 class="mb-2">商户信息</h4>'
      +   '<div class="row g-3">'
      +     detailItem('商户号', '<span class="font-monospace">' + dash(m.merchant_id) + '</span>')
      +     detailItem('商户名称', dash(m.name))
      +     detailItem('行业', dash(m.industry))
      +     detailItem('注册国家', dash(m.country))
      +     detailItem('注册时间', dash(m.register_date))
      +     detailItem('注册时长', m.register_days != null ? escapeHtml(String(m.register_days)) + ' 天' : '—')
      +     detailItem('入网日期', dash(m.onboard_date))
      +     detailItem('业务网站', dash(m.website))
      +     detailItem('网站合规', dash(m.website_status_label))
      +     detailItem('合规命中', complianceText)
      +     detailItem('拒付率', formatRate(m.chargeback_rate))
      +     detailItem('欺诈率', formatRate(m.fraud_rate))
      +     detailItem('退款率', formatRate(m.refund_rate))
      +     detailItem('放量比', m.volume_anomaly_ratio != null ? escapeHtml(String(m.volume_anomaly_ratio)) + '%' : '—')
      +     detailItem('今日笔数', m.today_count != null ? escapeHtml(String(m.today_count)) : '—')
      +     detailItem('今日金额', formatAmount(m.today_amount))
      +     detailItem('收单状态', badge(m.trading_status_label, TRADING_LT))
      +   '</div>'
      + '</div>'
      + '<div class="mb-3">'
      +   '<h4 class="mb-2">审核说明</h4>'
      +   '<div class="row g-3">'
      +     detailItem('审核状态', badge(m.review_status_label, { '通过': 'bg-green-lt', '不通过': 'bg-red-lt' }))
      +     detailItem('审核时间', dash(m.review_time))
      +     detailItem('审核说明', dash(m.review_remark))
      +   '</div>'
      + '</div>'
      + '<div class="mb-3">'
      +   '<h4 class="mb-2">评估信息</h4>'
      +   '<div class="row g-3">'
      +     detailItem('综合评分', scoreText)
      +     detailItem('风险等级', badge(m.risk_level_label, RISK_LT))
      +     detailItem('等级规则', dash(m.level_rule_text))
      +     detailItem('最近评估', dash(m.assessed_at))
      +   '</div>'
      + '</div>'
      + '<div>'
      +   '<h4 class="mb-2">维度评分明细（当前）</h4>'
      +   renderAssessTable(m.assess_details)
      + '</div>';
  }

  function openDetail(merchantId) {
    if (!merchantId) return;
    var body = document.getElementById('merchantDetailBody');
    if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载中…</div>';
    if (state.detailModal) state.detailModal.show();

    fetch(DETAIL_URL + '?merchant_id=' + encodeURIComponent(merchantId), {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '详情加载失败');
          if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载失败</div>';
          return;
        }
        renderDetail(json.data || {});
      })
      .catch(function () {
        notifyError('详情加载失败');
        if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载失败</div>';
      });
  }

  function reassessAll() {
    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
        return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
      };

    confirmFn({
      type: 'warning',
      title: '重新评估全部商户？',
      message: '将对列表商户发起批量重新评估。评估引擎就绪后才会写回评分。',
      confirmText: '确认重评',
      cancelText: '取消'
    }).then(function (ok) {
      if (!ok) return;

      return fetch(REASSESS_URL, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: '{}'
      }).then(function (res) { return res.json(); });
    }).then(function (json) {
      if (!json) return;
      notifyError(json.msg || '评估引擎未就绪');
    }).catch(function () {
      notifyError('请求失败');
    });
  }

  function bindEvents() {
    var form = document.getElementById('merchantFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        state.page = 1;
        loadList();
      });
    }

    var resetBtn = document.getElementById('merchantFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        document.querySelectorAll('[form="merchantFilterForm"]').forEach(function (el) {
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else if (el.type !== 'submit' && el.type !== 'button') el.value = '';
        });
        if (form) form.reset();
        state.page = 1;
        loadList();
      });
    }

    var pager = document.getElementById('merchantTablePager');
    if (pager) {
      pager.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page]');
        if (!link) return;
        e.preventDefault();
        var page = parseInt(link.getAttribute('data-page'), 10);
        if (isNaN(page) || page === state.page || state.loading) return;
        state.page = page;
        loadList();
      });
    }

    var sizeWrap = document.getElementById('merchantPageSize');
    if (sizeWrap) {
      sizeWrap.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page-size]');
        if (!link) return;
        e.preventDefault();
        var size = ListPage.normalizePageSize(link.getAttribute('data-page-size'));
        if (size === state.pageSize) return;
        state.pageSize = size;
        state.page = 1;
        loadList();
      });
    }

    var reassessBtn = document.getElementById('merchantReassessBtn');
    if (reassessBtn) {
      reassessBtn.addEventListener('click', function () {
        reassessAll();
      });
    }

    var tbody = document.getElementById('merchantTableBody');
    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-merchant-detail');
        if (!btn) return;
        openDetail(btn.getAttribute('data-id'));
      });
    }
  }

  function init() {
    var modalEl = document.getElementById('merchantDetailModal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
      state.detailModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    }
    applyQueryToForm();
    bindEvents();
    loadStats();
    loadList();
  }

  return { init: init };
})();
