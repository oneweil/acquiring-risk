/**
 * 风控总览页（前端演示：数据写死在本文件，不调接口）
 */
(function () {
  'use strict';

  /** @type {object} */
  var MOCK = {
    todo: {
      alerts_pending: 7,
      chargebacks_open: 3,
      str_pending_confirm: 2,
      edd_due_soon: 1
    },
    decision: {
      pass: 1184,
      decline: 41,
      challenge_3ds: 61,
      amount_usd: 384520,
      decline_rate_delta: '+0.4%',
      three_ds_rate_delta: '-1.2%'
    },
    alert_risk: {
      low: 1,
      mid: 2,
      high: 3,
      critical: 1
    },
    top_rules: [
      { name: '黑名单卡号', hits: 28, decline: 22, challenge_3ds: 0 },
      { name: '发卡国与IP不一致', hits: 24, decline: 2, challenge_3ds: 14 },
      { name: '单笔大额交易', hits: 19, decline: 5, challenge_3ds: 8 },
      { name: 'VPN/Proxy 交易', hits: 15, decline: 11, challenge_3ds: 2 },
      { name: '非营业时段交易', hits: 12, decline: 0, challenge_3ds: 0 }
    ],
    risk_merchants: [
      { merchant_no: 'M10086', merchant_name: 'NovaGadgets HK', hits: 18, declines: 6, alerts: 9 },
      { merchant_no: 'M10092', merchant_name: 'SkyTravel EU', hits: 14, declines: 3, alerts: 7 },
      { merchant_no: 'M10105', merchant_name: 'ShopAsia SG', hits: 11, declines: 8, alerts: 5 },
      { merchant_no: 'M10077', merchant_name: 'GlobalDuty Free', hits: 9, declines: 2, alerts: 4 },
      { merchant_no: 'M10118', merchant_name: 'LatAm Market', hits: 8, declines: 1, alerts: 3 }
    ],
    alerts: [
      { time: '14:22', merchant_name: 'NovaGadgets HK', order_no: 'MO20260910081', rule_name: '黑名单卡号', measure_label: '拒绝交易', risk_level: 'high', status: 'pending' },
      { time: '14:08', merchant_name: 'SkyTravel EU', order_no: 'MO20260910076', rule_name: '发卡国与IP不一致', measure_label: '调单', risk_level: 'mid', status: 'processing' },
      { time: '13:51', merchant_name: 'ShopAsia SG', order_no: 'MO20260910070', rule_name: 'VPN/Proxy 交易', measure_label: '拒绝交易', risk_level: 'high', status: 'pending' },
      { time: '13:20', merchant_name: 'GlobalDuty Free', order_no: 'MO20260910063', rule_name: '单笔大额交易', measure_label: '3DS强验', risk_level: 'mid', status: 'pending' },
      { time: '12:44', merchant_name: 'Orient Fashion', order_no: 'MO20260910055', rule_name: '高风险收货国家', measure_label: '仅预警', risk_level: 'mid', status: 'processing' },
      { time: '11:58', merchant_name: 'PixelMart US', order_no: 'MO20260910048', rule_name: '卡号频率异常', measure_label: '拒绝交易', risk_level: 'high', status: 'pending' },
      { time: '11:12', merchant_name: 'EuroBooks DE', order_no: 'MO20260910041', rule_name: '非营业时段交易', measure_label: '仅预警', risk_level: 'low', status: 'pending' },
      { time: '10:35', merchant_name: 'CloudSoft SaaS', order_no: 'MO20260910033', rule_name: 'Test-then-Buy模式', measure_label: '调单', risk_level: 'mid', status: 'processing' }
    ],
    feedPool: [
      { merchant_name: 'NovaGadgets HK', currency: 'USD', amount: 1280.5, order_no: 'MO20260901001', action: '通过', hit_rule: '低风险放行', decision: 'pass' },
      { merchant_name: 'SkyTravel EU', currency: 'EUR', amount: 459.0, order_no: 'MO20260901002', action: '仅预警', hit_rule: '发卡国与IP不一致', decision: 'pass' },
      { merchant_name: 'PixelMart US', currency: 'USD', amount: 89.99, order_no: 'MO20260901003', action: '拒绝交易', hit_rule: '黑名单卡号', decision: 'decline' },
      { merchant_name: 'Orient Fashion', currency: 'HKD', amount: 2380.0, order_no: 'MO20260901004', action: '3DS强验', hit_rule: '高风险收货国家', decision: 'challenge_3ds' },
      { merchant_name: 'CloudSoft SaaS', currency: 'USD', amount: 199.0, order_no: 'MO20260901005', action: '通过', hit_rule: '低风险放行', decision: 'pass' },
      { merchant_name: 'GlobalDuty Free', currency: 'USD', amount: 5120.0, order_no: 'MO20260901006', action: '仅预警', hit_rule: '单笔大额交易', decision: 'pass' },
      { merchant_name: 'Tokyo Gadget', currency: 'JPY', amount: 45800, order_no: 'MO20260901007', action: '通过', hit_rule: '低风险放行', decision: 'pass' },
      { merchant_name: 'EuroBooks DE', currency: 'EUR', amount: 62.4, order_no: 'MO20260901008', action: '仅预警', hit_rule: '非营业时段交易', decision: 'pass' },
      { merchant_name: 'ShopAsia SG', currency: 'USD', amount: 320.0, order_no: 'MO20260901009', action: '拒绝交易', hit_rule: 'VPN/Proxy 交易', decision: 'decline' },
      { merchant_name: 'LatAm Market', currency: 'USD', amount: 760.25, order_no: 'MO20260901010', action: '通过', hit_rule: '低风险放行', decision: 'pass' },
      { merchant_name: 'Nordic Outdoor', currency: 'EUR', amount: 1120.0, order_no: 'MO20260901011', action: '3DS强验', hit_rule: 'Test-then-Buy模式', decision: 'challenge_3ds' },
      { merchant_name: 'HK Beauty Lab', currency: 'HKD', amount: 980.0, order_no: 'MO20260901012', action: '通过', hit_rule: '低风险放行', decision: 'pass' }
    ]
  };

  var FEED_INTERVAL_MS = 4000;
  var FEED_VISIBLE = 10;
  var decisionChart = null;
  var alertRiskChart = null;
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

  function pct(part, total) {
    if (!total) return '0%';
    return ((part / total) * 100).toFixed(1) + '%';
  }

  function riskBadge(level) {
    if (level === 'high' || level === 'critical') {
      return '<span class="badge bg-red text-red-fg">' + (level === 'critical' ? '极高' : '高') + '</span>';
    }
    if (level === 'mid') {
      return '<span class="badge bg-yellow text-yellow-fg">中</span>';
    }
    return '<span class="badge bg-green text-green-fg">低</span>';
  }

  function statusBadge(status) {
    if (status === 'processing') {
      return '<span class="badge bg-azure text-azure-fg">处理中</span>';
    }
    if (status === 'closed') {
      return '<span class="badge bg-secondary text-secondary-fg">已关闭</span>';
    }
    return '<span class="badge bg-orange text-orange-fg">待处理</span>';
  }

  function feedDecisionBadge(decision) {
    if (decision === 'decline') {
      return '<span class="badge bg-red text-red-fg">拒绝</span>';
    }
    if (decision === 'challenge_3ds') {
      return '<span class="badge bg-yellow text-yellow-fg">3DS</span>';
    }
    return '<span class="badge bg-green text-green-fg">通过</span>';
  }

  function pad2(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function nowTime() {
    var d = new Date();
    return pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
  }

  function pickFeedItems() {
    var pool = MOCK.feedPool;
    var items = [];
    var i;
    for (i = 0; i < FEED_VISIBLE; i++) {
      var item = Object.assign({}, pool[(feedCursor + i) % pool.length]);
      item.time = nowTime();
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
        +     '<b>' + escapeHtml(o.merchant_name) + '</b> · ' + escapeHtml(o.currency) + ' ' + Number(o.amount).toLocaleString()
        +     '<span>' + escapeHtml(o.order_no) + ' · ' + escapeHtml(o.action) + ' · ' + escapeHtml(o.hit_rule) + '</span>'
        +   '</div>'
        +   '<div class="dash-feed-status">' + feedDecisionBadge(o.decision) + '</div>'
        + '</div>';
    }).join('');
  }

  function startFeedTimer() {
    if (feedTimer) clearInterval(feedTimer);
    feedTimer = setInterval(renderLiveFeed, FEED_INTERVAL_MS);
  }

  function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function renderKpi() {
    var d = MOCK.decision;
    var t = MOCK.todo;
    var total = d.pass + d.decline + d.challenge_3ds;

    setText('kpiOrders', total.toLocaleString());
    setText('kpiAmount', d.amount_usd.toLocaleString());
    setText('kpiAlerts', String(t.alerts_pending));
    setText('kpiStr', String(t.str_pending_confirm));
    setText('kpiEdd', String(t.edd_due_soon));
  }

  function renderNavBadges() {
    var t = MOCK.todo;
    setText('navTodoAlerts', String(t.alerts_pending));
    setText('navTodoStr', String(t.str_pending_confirm));
    setText('navTodoEdd', String(t.edd_due_soon));
  }

  function renderDecision() {
    var d = MOCK.decision;
    var total = d.pass + d.decline + d.challenge_3ds;

    setText('decPass', d.pass.toLocaleString());
    setText('decPassPct', pct(d.pass, total));
    setText('decDecline', d.decline.toLocaleString());
    setText('decDeclinePct', pct(d.decline, total));
    setText('decDeclineDelta', '较昨日 ' + d.decline_rate_delta);
    setText('dec3ds', d.challenge_3ds.toLocaleString());
    setText('dec3dsPct', pct(d.challenge_3ds, total));
    setText('dec3dsDelta', '较昨日 ' + d.three_ds_rate_delta);
  }

  function chartTooltipTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
  }

  function buildDonutOptions(series, labels, colors) {
    return {
      chart: {
        type: 'donut',
        fontFamily: 'inherit',
        height: 220,
        sparkline: { enabled: true },
        animations: { enabled: false }
      },
      series: series,
      labels: labels,
      colors: colors,
      legend: { show: false },
      tooltip: {
        theme: chartTooltipTheme(),
        fillSeriesColor: false,
        y: {
          formatter: function (val) {
            return Number(val).toLocaleString() + ' 笔';
          }
        }
      }
    };
  }

  function renderDecisionChart() {
    var el = document.getElementById('decisionDonutChart');
    if (!el || typeof ApexCharts === 'undefined') return;

    var d = MOCK.decision;
    var options = buildDonutOptions(
      [d.pass, d.decline, d.challenge_3ds],
      ['通过', '拒绝', '3DS'],
      [
        'color-mix(in srgb, transparent, var(--tblr-green) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-red) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-yellow) 100%)'
      ]
    );
    if (decisionChart) {
      decisionChart.updateOptions(options);
      return;
    }
    decisionChart = new ApexCharts(el, options);
    decisionChart.render();
  }

  function renderAlertRisk() {
    var r = MOCK.alert_risk;
    var total = r.low + r.mid + r.high + r.critical;

    setText('alertRiskLow', r.low.toLocaleString());
    setText('alertRiskLowPct', pct(r.low, total));
    setText('alertRiskMid', r.mid.toLocaleString());
    setText('alertRiskMidPct', pct(r.mid, total));
    setText('alertRiskHigh', r.high.toLocaleString());
    setText('alertRiskHighPct', pct(r.high, total));
    setText('alertRiskCritical', r.critical.toLocaleString());
    setText('alertRiskCriticalPct', pct(r.critical, total));

    var el = document.getElementById('alertRiskDonutChart');
    if (!el || typeof ApexCharts === 'undefined') return;

    var options = buildDonutOptions(
      [r.low, r.mid, r.high, r.critical],
      ['低风险', '中风险', '高风险', '极高风险'],
      [
        'color-mix(in srgb, transparent, var(--tblr-green) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-yellow) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-red) 100%)',
        'color-mix(in srgb, transparent, var(--tblr-purple) 100%)'
      ]
    );
    if (alertRiskChart) {
      alertRiskChart.updateOptions(options);
      return;
    }
    alertRiskChart = new ApexCharts(el, options);
    alertRiskChart.render();
  }

  function renderTopRules() {
    var el = document.getElementById('dashTopRules');
    if (!el) return;
    el.innerHTML = MOCK.top_rules.map(function (r) {
      return ''
        + '<a href="/admin/rule" class="list-group-item list-group-item-action dash-rank-item">'
        +   '<div class="dash-rank-main">'
        +     '<div class="fw-bold">' + escapeHtml(r.name) + '</div>'
        +     '<div class="text-secondary small">拒绝 ' + r.decline + ' · 3DS ' + r.challenge_3ds + '</div>'
        +   '</div>'
        +   '<div class="dash-rank-metric">' + r.hits + '<span>命中</span></div>'
        + '</a>';
    }).join('');
  }

  function renderRiskMerchants() {
    var el = document.getElementById('dashRiskMerchants');
    if (!el) return;
    el.innerHTML = MOCK.risk_merchants.map(function (m) {
      return ''
        + '<a href="/admin/merchant" class="list-group-item list-group-item-action dash-rank-item">'
        +   '<div class="dash-rank-main">'
        +     '<div class="fw-bold">' + escapeHtml(m.merchant_name) + '</div>'
        +     '<div class="text-secondary small">' + escapeHtml(m.merchant_no)
        +       ' · 拒绝 ' + m.declines + ' · 预警 ' + m.alerts + '</div>'
        +   '</div>'
        +   '<div class="dash-rank-metric">' + m.hits + '<span>命中</span></div>'
        + '</a>';
    }).join('');
  }

  function renderAlerts() {
    var el = document.getElementById('dashAlertBody');
    if (!el) return;
    el.innerHTML = MOCK.alerts.map(function (a) {
      return ''
        + '<tr class="dash-alert-row" data-href="/admin/alert">'
        +   '<td class="text-secondary">' + escapeHtml(a.time) + '</td>'
        +   '<td>' + escapeHtml(a.merchant_name) + '</td>'
        +   '<td><code>' + escapeHtml(a.order_no) + '</code></td>'
        +   '<td>' + escapeHtml(a.rule_name) + '</td>'
        +   '<td>' + escapeHtml(a.measure_label) + '</td>'
        +   '<td>' + riskBadge(a.risk_level) + '</td>'
        +   '<td>' + statusBadge(a.status) + '</td>'
        + '</tr>';
    }).join('');

    el.querySelectorAll('.dash-alert-row').forEach(function (tr) {
      tr.addEventListener('click', function () {
        window.location.href = tr.getAttribute('data-href');
      });
    });
  }

  function init() {
    try {
      renderKpi();
      renderNavBadges();
      renderDecision();
      renderDecisionChart();
      renderAlertRisk();
      renderLiveFeed();
      startFeedTimer();
      renderTopRules();
      renderRiskMerchants();
      renderAlerts();
    } catch (err) {
      if (typeof console !== 'undefined' && console.error) {
        console.error('[dashboard] init failed', err);
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
