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
    ]
  };

  var riskChart = null;
  var feedCursor = 0;
  var feedTimer = null;

  function escapeHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
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

  function startFeedTimer() {
    if (feedTimer) clearInterval(feedTimer);
    feedTimer = setInterval(renderLiveFeed, FEED_INTERVAL_MS);
  }

  function init() {
    renderKpi();
    renderLiveFeed();
    renderRiskChart();
    startFeedTimer();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
