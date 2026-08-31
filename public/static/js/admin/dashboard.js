/**
 * 风控总览页（前端演示：数据写死在本文件，不调接口）
 */
(function () {
  'use strict';

  var FEED_INTERVAL_MS = 4000;
  var FEED_VISIBLE = 10;

  /** @type {object} */
  var MOCK = {
    kpi: {
      orders: 1286,
      amount: 384520,
      alerts: 7,
      blockRate: '3.2%',
      pendingMerchants: 4,
      activeMerchants: 86,
      highRisk: 19,
      threeDsRate: '68.4%',
      threeDsHint: '较昨日 +2.1%'
    },
    riskDist: {
      low: 82.0,
      mid: 12.0,
      high: 6.0
    },
    feedPool: [
      { merchantName: 'NovaGadgets HK', currency: 'USD', amount: 1280.5, orderNo: 'MO20260901001', action: '通过', hitRule: '低风险放行', status: '成功' },
      { merchantName: 'SkyTravel EU', currency: 'EUR', amount: 459.0, orderNo: 'MO20260901002', action: '人工审核', hitRule: '发卡国与IP不一致', status: '审核中' },
      { merchantName: 'PixelMart US', currency: 'USD', amount: 89.99, orderNo: 'MO20260901003', action: '拒绝交易', hitRule: '黑名单卡号', status: '拦截' },
      { merchantName: 'Orient Fashion', currency: 'HKD', amount: 2380.0, orderNo: 'MO20260901004', action: '3DS强验', hitRule: '高风险收货国家', status: '成功' },
      { merchantName: 'CloudSoft SaaS', currency: 'USD', amount: 199.0, orderNo: 'MO20260901005', action: '通过', hitRule: '低风险放行', status: '成功' },
      { merchantName: 'GlobalDuty Free', currency: 'USD', amount: 5120.0, orderNo: 'MO20260901006', action: '人工审核', hitRule: '单笔大额交易', status: '审核中' },
      { merchantName: 'Tokyo Gadget', currency: 'JPY', amount: 45800, orderNo: 'MO20260901007', action: '通过', hitRule: '低风险放行', status: '成功' },
      { merchantName: 'EuroBooks DE', currency: 'EUR', amount: 62.4, orderNo: 'MO20260901008', action: '仅预警', hitRule: '非营业时段交易', status: '成功' },
      { merchantName: 'ShopAsia SG', currency: 'USD', amount: 320.0, orderNo: 'MO20260901009', action: '拒绝交易', hitRule: 'VPN/Proxy 交易', status: '拦截' },
      { merchantName: 'LatAm Market', currency: 'USD', amount: 760.25, orderNo: 'MO20260901010', action: '通过', hitRule: '低风险放行', status: '成功' },
      { merchantName: 'Nordic Outdoor', currency: 'EUR', amount: 1120.0, orderNo: 'MO20260901011', action: '人工审核', hitRule: 'Test-then-Buy模式', status: '审核中' },
      { merchantName: 'HK Beauty Lab', currency: 'HKD', amount: 980.0, orderNo: 'MO20260901012', action: '通过', hitRule: '低风险放行', status: '成功' }
    ],
    alerts: [
      {
        id: 'AL2026090001',
        time: '2026-09-01 10:42:18',
        merchantId: 'M10086',
        orderNo: 'MO20260658222',
        amount: 'USD 1,280.00',
        riskLevel: '高风险',
        ruleName: '发卡国与IP不一致；高风险收货国家',
        action: '人工审核',
        status: '待处理',
        strReport: '—',
        orderStatus: '审核中',
        hitRules: [
          { id: 'R016', name: '发卡国与IP不一致' },
          { id: 'R020', name: '高风险收货国家' }
        ]
      },
      {
        id: 'AL2026090002',
        time: '2026-09-01 10:28:05',
        merchantId: 'M10012',
        orderNo: 'MO20260901006',
        amount: 'USD 5,120.00',
        riskLevel: '中风险',
        ruleName: '单笔大额交易',
        action: '人工审核',
        status: '待处理',
        strReport: 'LTR待确认',
        orderStatus: '审核中',
        hitRules: [{ id: 'R013', name: '单笔大额交易' }]
      },
      {
        id: 'AL2026090003',
        time: '2026-09-01 09:55:41',
        merchantId: 'M10045',
        orderNo: 'MO20260901003',
        amount: 'USD 89.99',
        riskLevel: '极高风险',
        ruleName: '黑名单卡号',
        action: '拒绝交易',
        status: '已关闭',
        strReport: '—',
        orderStatus: '拦截',
        hitRules: [{ id: 'R031', name: '黑名单卡号' }]
      },
      {
        id: 'AL2026090004',
        time: '2026-09-01 09:31:12',
        merchantId: 'M10078',
        orderNo: 'MO20260901011',
        amount: 'EUR 1,120.00',
        riskLevel: '高风险',
        ruleName: 'Test-then-Buy模式',
        action: '人工审核',
        status: '处理中',
        strReport: '—',
        orderStatus: '审核中',
        hitRules: [{ id: 'R009', name: 'Test-then-Buy模式' }]
      },
      {
        id: 'AL2026090005',
        time: '2026-09-01 08:47:33',
        merchantId: 'M10033',
        orderNo: 'MO20260901009',
        amount: 'USD 320.00',
        riskLevel: '高风险',
        ruleName: 'VPN/Proxy 交易',
        action: '拒绝交易',
        status: '已关闭',
        strReport: 'STR草稿',
        orderStatus: '拦截',
        hitRules: [{ id: 'R018', name: 'VPN/Proxy 交易' }]
      }
    ]
  };

  var riskChart = null;
  var feedCursor = 0;
  var feedTimer = null;
  var alertModal = null;

  function escapeHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function riskBadge(level) {
    var map = {
      '极高风险': 'bg-purple text-purple-fg',
      '高风险': 'bg-red text-red-fg',
      '中风险': 'bg-yellow text-yellow-fg',
      '低风险': 'bg-green text-green-fg'
    };
    return '<span class="badge ' + (map[level] || 'bg-secondary text-secondary-fg') + '">' + escapeHtml(level) + '</span>';
  }

  function statusBadge(status) {
    var map = {
      '待处理': 'bg-red text-red-fg',
      '处理中': 'bg-yellow text-yellow-fg',
      '已关闭': 'bg-secondary text-secondary-fg'
    };
    return '<span class="badge ' + (map[status] || 'bg-secondary text-secondary-fg') + '">' + escapeHtml(status) + '</span>';
  }

  function measureBadge(name) {
    var cls = 'bg-azure text-azure-fg';
    if (name === '拒绝交易') cls = 'bg-red text-red-fg';
    else if (name === '人工审核' || name === '调单') cls = 'bg-purple text-purple-fg';
    else if (name === '仅预警') cls = 'bg-green text-green-fg';
    else if (name === '3DS强验') cls = 'bg-blue text-blue-fg';
    return '<span class="badge ' + cls + '">' + escapeHtml(name) + '</span>';
  }

  function feedStatusBadge(status) {
    if (status === '拦截') return '<span class="badge bg-red text-red-fg">拦截</span>';
    if (status === '审核中') return '<span class="badge bg-purple text-purple-fg">审核</span>';
    return '<span class="badge bg-green text-green-fg">成功</span>';
  }

  function pad2(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function nowTime() {
    var d = new Date();
    return pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
  }

  function renderKpi() {
    var k = MOCK.kpi;
    document.getElementById('kpiOrders').textContent = k.orders.toLocaleString();
    document.getElementById('kpiAmount').textContent = k.amount.toLocaleString();
    document.getElementById('kpiAlerts').textContent = String(k.alerts);
    document.getElementById('kpiBlockRate').textContent = k.blockRate;
    document.getElementById('kpiPendingMerchants').textContent = String(k.pendingMerchants);
    document.getElementById('kpiActiveMerchants').textContent = String(k.activeMerchants);
    document.getElementById('kpiHighRisk').textContent = String(k.highRisk);
    document.getElementById('kpi3dsRate').textContent = k.threeDsRate;
    document.getElementById('kpi3dsHint').textContent = k.threeDsHint;
  }

  function pickFeedItems() {
    var pool = MOCK.feedPool;
    var items = [];
    var i;
    for (i = 0; i < FEED_VISIBLE; i++) {
      var item = Object.assign({}, pool[(feedCursor + i) % pool.length]);
      item.time = nowTime();
      // 轻微扰动金额，模拟实时波动
      item.amount = +(item.amount * (0.92 + Math.random() * 0.16)).toFixed(2);
      items.push(item);
    }
    feedCursor = (feedCursor + 1) % pool.length;
    return items;
  }

  function renderLiveFeed() {
    var el = document.getElementById('liveFeed');
    if (!el) return;
    var items = pickFeedItems();
    el.innerHTML = items.map(function (o) {
      return ''
        + '<div class="dash-feed-item">'
        +   '<div class="dash-feed-time">' + escapeHtml(o.time) + '</div>'
        +   '<div class="dash-feed-main">'
        +     '<b>' + escapeHtml(o.merchantName) + '</b> · ' + escapeHtml(o.currency) + ' ' + Number(o.amount).toLocaleString()
        +     '<span>' + escapeHtml(o.orderNo) + ' · ' + escapeHtml(o.action) + ' · ' + escapeHtml(o.hitRule) + '</span>'
        +   '</div>'
        +   '<div class="dash-feed-status">' + feedStatusBadge(o.status) + '</div>'
        + '</div>';
    }).join('');
  }

  function chartTooltipTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
  }

  /** Tabler 官方 Pie/Donut 配置，见 https://docs.tabler.io/ui/components/charts */
  function buildRiskChartOptions(dist) {
    return {
      chart: {
        type: 'donut',
        fontFamily: 'inherit',
        height: 240,
        sparkline: {
          enabled: true
        },
        animations: {
          enabled: false
        }
      },
      series: [dist.low, dist.mid, dist.high],
      labels: ['低风险', '中风险', '高风险'],
      grid: {
        strokeDashArray: 4
      },
      colors: [
        'color-mix(in srgb, transparent, var(--tblr-green) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-yellow) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-red) 100%)'
      ],
      legend: {
        show: true,
        position: 'bottom',
        offsetY: 12,
        markers: {
          width: 10,
          height: 10,
          radius: 100
        },
        itemMargin: {
          horizontal: 8,
          vertical: 8
        }
      },
      tooltip: {
        theme: chartTooltipTheme(),
        fillSeriesColor: false,
        y: {
          formatter: function (val) {
            return Number(val).toFixed(1) + '%';
          }
        }
      }
    };
  }

  function renderRiskChart() {
    var el = document.getElementById('riskDonutChart');
    if (!el || typeof ApexCharts === 'undefined') return;

    var options = buildRiskChartOptions(MOCK.riskDist);

    if (riskChart) {
      riskChart.updateOptions(options);
      return;
    }

    riskChart = new ApexCharts(el, options);
    riskChart.render();
  }

  function renderAlerts() {
    var body = document.getElementById('dashAlertBody');
    if (!body) return;
    var list = MOCK.alerts;
    if (!list.length) {
      body.innerHTML = '<tr><td colspan="10" class="text-center text-secondary py-5">暂无预警</td></tr>';
      return;
    }
    body.innerHTML = list.map(function (a) {
      return ''
        + '<tr>'
        +   '<td>' + escapeHtml(a.time) + '</td>'
        +   '<td><span class="font-monospace">' + escapeHtml(a.merchantId) + '</span></td>'
        +   '<td><span class="font-monospace">' + escapeHtml(a.orderNo) + '</span></td>'
        +   '<td>' + escapeHtml(a.amount) + '</td>'
        +   '<td>' + riskBadge(a.riskLevel) + '</td>'
        +   '<td class="text-wrap" style="max-width:14rem;">' + escapeHtml(a.ruleName) + '</td>'
        +   '<td>' + measureBadge(a.action) + '</td>'
        +   '<td>' + statusBadge(a.status) + '</td>'
        +   '<td>' + escapeHtml(a.strReport) + '</td>'
        +   '<td class="text-center">'
        +     '<button type="button" class="btn btn-sm btn-primary js-alert-handle" data-id="' + escapeHtml(a.id) + '">处理</button>'
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function findAlert(id) {
    for (var i = 0; i < MOCK.alerts.length; i++) {
      if (MOCK.alerts[i].id === id) return MOCK.alerts[i];
    }
    return null;
  }

  function openAlertModal(alertId) {
    var a = findAlert(alertId);
    if (!a || !alertModal) return;

    document.getElementById('alertHandleModalLabel').textContent =
      a.action === '人工审核' ? '人工审核' : '预警处理';
    document.getElementById('alertHandleModalSub').textContent =
      a.orderNo + ' · ' + a.ruleName;

    var hits = a.hitRules || [];
    document.getElementById('alertHitRulesBody').innerHTML = hits.length
      ? hits.map(function (h) {
          return '<tr><td><span class="badge bg-secondary-lt">' + escapeHtml(h.id) + '</span></td><td>' + escapeHtml(h.name) + '</td></tr>';
        }).join('')
      : '<tr><td colspan="2" class="text-secondary">暂无命中规则</td></tr>';

    document.getElementById('alertExecNotice').innerHTML =
      '<div><b>步骤 1 · 已自动执行：</b>处置策略「' + escapeHtml(a.action) + '」已生效，交易已挂起，订单状态 <b>' + escapeHtml(a.orderStatus) + '</b>。</div>'
      + '<div class="mt-1"><b>步骤 2 · 人工复核：</b>请核查交易信息后，选择审核通过放行、审核拒绝拦截或标记误报。</div>';

    document.getElementById('alertCurrentMeasure').innerHTML = measureBadge(a.action);
    document.getElementById('alertResult').selectedIndex = 0;
    document.getElementById('alertRemark').value = '';

    alertModal.show();
  }

  function bindEvents() {
    var body = document.getElementById('dashAlertBody');
    if (body) {
      body.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-alert-handle');
        if (!btn) return;
        openAlertModal(btn.getAttribute('data-id'));
      });
    }

    var submitBtn = document.getElementById('alertSubmitBtn');
    if (submitBtn) {
      submitBtn.addEventListener('click', function () {
        // 演示页：不落库、不改状态，仅关闭弹窗
        if (alertModal) alertModal.hide();
      });
    }
  }

  function startFeedTimer() {
    if (feedTimer) clearInterval(feedTimer);
    feedTimer = setInterval(renderLiveFeed, FEED_INTERVAL_MS);
  }

  function init() {
    var modalEl = document.getElementById('alertHandleModal');
    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal);
    if (modalEl && ModalCtor) {
      alertModal = ModalCtor.getOrCreateInstance(modalEl);
    }

    renderKpi();
    renderLiveFeed();
    renderRiskChart();
    renderAlerts();
    bindEvents();
    startFeedTimer();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
