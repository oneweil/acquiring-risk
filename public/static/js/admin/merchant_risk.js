/**
 * 商户评估规则配置页（维度双权重 + 规则行 template/config）
 */
var MerchantRiskPage = (function () {
  'use strict';

  var CONFIG_URL = '/admin/merchant_risk/config';
  var SAVE_URL = '/admin/merchant_risk/save';
  var REASSESS_URL = '/admin/merchant_risk/reassess';

  var CATEGORY_ORDER = [
    'industry',
    'chargeback',
    'fraud',
    'tenure',
    'geo',
    'website',
    'compliance',
    'refund',
    'volume_anomaly'
  ];

  var CATEGORY_TO_DIM = {
    industry: 'industry',
    chargeback: 'chargeback',
    fraud: 'fraud',
    refund: 'refund',
    website: 'website',
    compliance: 'compliance',
    tenure: 'tenure',
    volume_anomaly: 'volumeAnomaly',
    geo: 'geo'
  };

  var CATEGORY_ICON = {
    industry: 'ti-building-factory',
    chargeback: 'ti-receipt-refund',
    fraud: 'ti-spy',
    tenure: 'ti-clock',
    geo: 'ti-world',
    website: 'ti-world-www',
    compliance: 'ti-scale',
    refund: 'ti-arrow-back-up',
    volume_anomaly: 'ti-chart-line'
  };

  var state = {
    loading: false,
    saving: false,
    dimensions: [],
    rules: [],
    categoryLabels: {}
  };

  function escapeHtml(str) {
    if (window.ListPage && typeof ListPage.escapeHtml === 'function') {
      return ListPage.escapeHtml(str);
    }
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
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

  function parseIntSafe(value, fallback) {
    var n = parseInt(value, 10);
    return isNaN(n) ? fallback : n;
  }

  function parseNumSafe(value, fallback) {
    var n = parseFloat(value);
    return isNaN(n) ? fallback : n;
  }

  function dimByKey(dimKey) {
    for (var i = 0; i < state.dimensions.length; i++) {
      if (state.dimensions[i].dim_key === dimKey) return state.dimensions[i];
    }
    return null;
  }

  /** 占位符只读联动：从其它规则的 config 取值展示，不可单独编辑；保存时写回本行 config */
  var THRESHOLD_LINKS = {
    MA_VOL_1: { low_threshold: { from: 'MA_VOL_0', key: 'low_threshold' } },
    MA_VOL_2: { threshold: { from: 'MA_VOL_1', key: 'threshold' } },
    MA_CFG_TEN: { threshold: { from: 'MA_TEN_2', key: 'threshold' } },
    MA_CB_3: { threshold: { from: 'MA_CB_2', key: 'threshold' } },
    MA_FR_2: { threshold: { from: 'MA_FR_1', key: 'threshold' } },
    MA_RF_2: { threshold: { from: 'MA_RF_1', key: 'threshold' } }
  };

  function ruleById(ruleId) {
    for (var i = 0; i < state.rules.length; i++) {
      if (state.rules[i].rule_id === ruleId) return state.rules[i];
    }
    return null;
  }

  function configDisplayValue(key, config) {
    if (!config || config[key] === undefined || config[key] === null) return '';
    if (key === 'countries' && Array.isArray(config[key])) {
      return config[key].join(',');
    }
    return String(config[key]);
  }

  function linkedValue(ruleId, key, config) {
    var links = THRESHOLD_LINKS[ruleId];
    if (links && links[key]) {
      var src = ruleById(links[key].from);
      if (src && src.config) {
        return configDisplayValue(links[key].key, src.config);
      }
      return '';
    }
    return configDisplayValue(key, config);
  }

  function isLinkedReadonly(ruleId, key) {
    return !!(THRESHOLD_LINKS[ruleId] && THRESHOLD_LINKS[ruleId][key]);
  }

  function renderTemplate(template, config, ruleId) {
    var html = '';
    var re = /\{([a-zA-Z0-9_]+)\}/g;
    var last = 0;
    var m;
    var text = String(template || '');
    while ((m = re.exec(text)) !== null) {
      html += escapeHtml(text.slice(last, m.index));
      var key = m[1];
      var val = linkedValue(ruleId, key, config);
      if (isLinkedReadonly(ruleId, key)) {
        html += '<strong class="mx-1 js-ma-linked" data-rule-id="' + escapeHtml(ruleId)
          + '" data-key="' + escapeHtml(key) + '">' + escapeHtml(val) + '</strong>';
      } else {
        var width = key === 'countries' ? '10rem' : (val.length > 6 ? '5.5rem' : '4.5rem');
        html += '<input type="text" class="form-control form-control-sm d-inline-block mx-1 js-ma-config" '
          + 'data-rule-id="' + escapeHtml(ruleId) + '" data-key="' + escapeHtml(key) + '" '
          + 'value="' + escapeHtml(val) + '" style="width:' + width + ';vertical-align:baseline;">';
      }
      last = m.index + m[0].length;
    }
    html += escapeHtml(text.slice(last));
    return html || escapeHtml(text);
  }

  function syncLinkedThresholdDisplays() {
    Object.keys(THRESHOLD_LINKS).forEach(function (ruleId) {
      var links = THRESHOLD_LINKS[ruleId];
      Object.keys(links).forEach(function (key) {
        var val = linkedValue(ruleId, key, null);
        document.querySelectorAll(
          '.js-ma-linked[data-rule-id="' + ruleId + '"][data-key="' + key + '"]'
        ).forEach(function (el) {
          el.textContent = val;
        });
      });
    });
  }

  function applyLinkedThresholdsToState() {
    Object.keys(THRESHOLD_LINKS).forEach(function (ruleId) {
      var rule = ruleById(ruleId);
      if (!rule) return;
      if (!rule.config) rule.config = {};
      var links = THRESHOLD_LINKS[ruleId];
      Object.keys(links).forEach(function (key) {
        var src = ruleById(links[key].from);
        if (!src || !src.config) return;
        var v = src.config[links[key].key];
        if (v !== undefined && v !== null) {
          rule.config[key] = v;
        }
      });
    });
  }

  function updateWeightBars() {
    var weightSum = 0;
    var onboardSum = 0;
    state.dimensions.forEach(function (d) {
      weightSum += parseIntSafe(d.weight, 0);
      onboardSum += parseIntSafe(d.onboarding_weight, 0);
    });

    var wEl = document.getElementById('maWeightSum');
    var oEl = document.getElementById('maOnboardSum');
    if (wEl) wEl.textContent = String(weightSum);
    if (oEl) oEl.textContent = String(onboardSum);

    var wBar = document.getElementById('maWeightSumBar');
    var oBar = document.getElementById('maOnboardSumBar');
    if (wBar) {
      wBar.className = 'alert mb-0 py-2 px-3 ' + (weightSum === 100 ? 'alert-success' : 'alert-danger');
    }
    if (oBar) {
      oBar.className = 'alert mb-0 py-2 px-3 ' + (onboardSum === 100 ? 'alert-success' : 'alert-danger');
    }
  }

  function syncDimFromInputs() {
    document.querySelectorAll('.js-ma-dim-weight').forEach(function (el) {
      var dim = dimByKey(el.getAttribute('data-dim-key'));
      if (dim) dim.weight = parseIntSafe(el.value, 0);
    });
    document.querySelectorAll('.js-ma-dim-onboard').forEach(function (el) {
      var dim = dimByKey(el.getAttribute('data-dim-key'));
      if (dim) dim.onboarding_weight = parseIntSafe(el.value, 0);
    });
    updateWeightBars();
  }

  function renderOnboardingPanel() {
    var wrap = document.getElementById('maOnboardingWeights');
    if (!wrap) return;

    var rows = state.dimensions.filter(function (d) { return !d.onboarding_excluded; });
    wrap.innerHTML = rows.map(function (d) {
      return ''
        + '<div class="col-12 col-md-6 col-lg-4">'
        +   '<div class="input-group input-group-sm">'
        +     '<span class="input-group-text" style="min-width:7rem;">' + escapeHtml(d.name) + '</span>'
        +     '<input type="number" class="form-control js-ma-dim-onboard" min="0" max="100" step="1" '
        +       'data-dim-key="' + escapeHtml(d.dim_key) + '" value="' + escapeHtml(String(d.onboarding_weight)) + '">'
        +     '<span class="input-group-text">%</span>'
        +   '</div>'
        + '</div>';
    }).join('');
  }

  function renderGroups() {
    var wrap = document.getElementById('maRuleGroups');
    if (!wrap) return;

    var byCat = {};
    state.rules.forEach(function (rule) {
      if (!byCat[rule.category]) byCat[rule.category] = [];
      byCat[rule.category].push(rule);
    });

    var html = CATEGORY_ORDER.map(function (category) {
      var rules = byCat[category] || [];
      if (!rules.length) return '';

      var dimKey = CATEGORY_TO_DIM[category] || null;
      var dim = dimKey ? dimByKey(dimKey) : null;
      var label = (state.categoryLabels && state.categoryLabels[category])
        || (rules[0] && rules[0].category_label)
        || category;
      var icon = CATEGORY_ICON[category] || 'ti-adjustments';
      var periodic = !!(rules[0] && rules[0].periodic_only);

      var headExtra = '';
      if (dim) {
        headExtra = ''
          + '<div class="d-flex flex-wrap align-items-center gap-2 ms-auto">'
          +   '<div class="input-group input-group-sm" style="width:8.5rem;">'
          +     '<span class="input-group-text">复评权重</span>'
          +     '<input type="number" class="form-control js-ma-dim-weight" min="0" max="100" step="1" '
          +       'data-dim-key="' + escapeHtml(dim.dim_key) + '" value="' + escapeHtml(String(dim.weight)) + '">'
          +     '<span class="input-group-text">%</span>'
          +   '</div>'
          + '</div>';
      }

      var rows = rules.map(function (rule) {
        var score = rule.score !== undefined && rule.score !== null ? rule.score : '';
        return ''
          + '<div class="list-group-item js-ma-rule-row" data-rule-id="' + escapeHtml(rule.rule_id) + '">'
          +   '<div class="row g-2 align-items-center">'
          +     '<div class="col-12 col-lg">'
          +       '<div class="fw-medium">'
          +         renderTemplate(rule.content_template, rule.config || {}, rule.rule_id)
          +       '</div>'
          +       (rule.description
            ? '<div class="text-secondary small mt-1">' + escapeHtml(rule.description) + '</div>'
            : '')
          +     '</div>'
          +     '<div class="col-4 col-md-2 col-lg-2">'
          +       '<div class="input-group input-group-sm">'
          +         '<span class="input-group-text">分</span>'
          +         '<input type="number" class="form-control js-ma-score" min="0" max="100" step="1" '
          +           'data-rule-id="' + escapeHtml(rule.rule_id) + '" '
          +           'value="' + escapeHtml(String(score)) + '">'
          +       '</div>'
          +     '</div>'
          +     '<div class="col-4 col-md-2 col-lg-1 text-center">'
          +       '<label class="form-check form-switch m-0 d-inline-flex justify-content-center">'
          +         '<input class="form-check-input js-ma-rule-enabled" type="checkbox" '
          +           'data-rule-id="' + escapeHtml(rule.rule_id) + '"'
          +           (rule.enabled ? ' checked' : '') + '>'
          +       '</label>'
          +     '</div>'
          +   '</div>'
          + '</div>';
      }).join('');

      return ''
        + '<div class="col-12">'
        +   '<div class="card">'
        +     '<div class="card-header d-flex flex-wrap align-items-center gap-2">'
        +       '<h3 class="card-title mb-0">'
        +         '<i class="ti ' + icon + ' me-2 text-primary"></i>'
        +         escapeHtml(label)
        +         (periodic
          ? '<span class="text-secondary small fw-normal ms-2">— 仅已入网商户复评</span>'
          : '')
        +       '</h3>'
        +       headExtra
        +     '</div>'
        +     '<div class="card-header py-2 d-none d-lg-block">'
        +       '<div class="row g-2 text-secondary small">'
        +         '<div class="col-lg">规则内容</div>'
        +         '<div class="col-lg-2">维度分</div>'
        +         '<div class="col-lg-1 text-center">启用</div>'
        +       '</div>'
        +     '</div>'
        +     '<div class="list-group list-group-flush">' + rows + '</div>'
        +   '</div>'
        + '</div>';
    }).join('');

    wrap.innerHTML = html || '<div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">暂无规则数据</div></div></div>';
  }

  function applyConfig(data) {
    if (!data) return;
    state.dimensions = (data.dimensions || []).map(function (d) {
      return {
        dim_key: d.dim_key,
        name: d.name,
        weight: d.weight,
        onboarding_weight: d.onboarding_weight,
        sort: d.sort,
        onboarding_excluded: !!d.onboarding_excluded
      };
    });
    state.rules = (data.rules || []).map(function (r) {
      return {
        rule_id: r.rule_id,
        category: r.category,
        category_label: r.category_label,
        description: r.description,
        content_template: r.content_template,
        score: parseIntSafe(r.score, 0),
        config: Object.assign({}, r.config || {}),
        enabled: !!r.enabled,
        sort: r.sort,
        periodic_only: !!r.periodic_only
      };
    });
    state.categoryLabels = data.category_labels || {};

    renderOnboardingPanel();
    renderGroups();
    updateWeightBars();
    bindDynamicEvents();
  }

  function collectPayload() {
    syncDimFromInputs();

    document.querySelectorAll('.js-ma-config').forEach(function (el) {
      var ruleId = el.getAttribute('data-rule-id');
      var key = el.getAttribute('data-key');
      var rule = ruleById(ruleId);
      if (!rule) return;
      if (!rule.config) rule.config = {};

      if (key === 'countries') {
        rule.config.countries = String(el.value || '')
          .split(/[,，\s]+/)
          .map(function (s) { return s.trim().toUpperCase(); })
          .filter(Boolean);
      } else if (key === 'threshold' || key === 'low_threshold') {
        rule.config[key] = parseNumSafe(el.value, 0);
      } else if (key === 'match_key') {
        rule.config.match_key = String(el.value || '').trim();
      } else {
        rule.config[key] = String(el.value || '').trim();
      }
    });

    document.querySelectorAll('.js-ma-score').forEach(function (el) {
      var rule = ruleById(el.getAttribute('data-rule-id'));
      if (rule) rule.score = parseIntSafe(el.value, 0);
    });

    applyLinkedThresholdsToState();

    document.querySelectorAll('.js-ma-rule-enabled').forEach(function (el) {
      var ruleId = el.getAttribute('data-rule-id');
      var rule = ruleById(ruleId);
      if (rule) rule.enabled = !!el.checked;
    });

    return {
      dimensions: state.dimensions.map(function (d) {
        return {
          dim_key: d.dim_key,
          weight: parseIntSafe(d.weight, 0),
          onboarding_weight: parseIntSafe(d.onboarding_weight, 0)
        };
      }),
      rules: state.rules.map(function (r) {
        var config = Object.assign({}, r.config || {});
        delete config.score;
        return {
          rule_id: r.rule_id,
          score: parseIntSafe(r.score, 0),
          config: config,
          enabled: !!r.enabled
        };
      })
    };
  }

  function weightsValid() {
    syncDimFromInputs();
    var weightSum = 0;
    var onboardSum = 0;
    for (var i = 0; i < state.dimensions.length; i++) {
      weightSum += parseIntSafe(state.dimensions[i].weight, 0);
      onboardSum += parseIntSafe(state.dimensions[i].onboarding_weight, 0);
    }
    if (weightSum !== 100) {
      notifyError('复评维度权重合计须为 100%（当前 ' + weightSum + '%）');
      return false;
    }
    if (onboardSum !== 100) {
      notifyError('入网维度权重合计须为 100%（当前 ' + onboardSum + '%）');
      return false;
    }
    return true;
  }

  function setBusy(busy) {
    state.saving = busy;
    ['maSaveBtn', 'maSaveBtn2', 'maReassessBtn', 'maReassessBtn2'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.disabled = busy;
      if (id.indexOf('Save') >= 0) {
        el.textContent = busy ? '保存中…' : '保存评估规则';
      }
    });
  }

  function saveConfig() {
    if (state.saving) return;
    if (!weightsValid()) return;

    var payload = collectPayload();
    setBusy(true);
    fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        setBusy(false);
        if (json.code !== 0) {
          notifyError(json.msg || '保存失败');
          return;
        }
        if (json.data) applyConfig(json.data);
        notifySuccess(json.msg || '保存成功');
      })
      .catch(function () {
        setBusy(false);
        notifyError('网络错误，请稍后重试');
      });
  }

  function reassessAll() {
    if (state.saving) return;

    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
          return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
        };

    confirmFn({
      type: 'warning',
      title: '重新评估全部商户？',
      message: '将按当前已保存的评估规则重新计算全部商户评分。若尚未保存修改，请先保存。',
      confirmText: '确认重评',
      cancelText: '取消'
    }).then(function (ok) {
      if (!ok) return;

      setBusy(true);
      fetch(REASSESS_URL, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          setBusy(false);
          if (json.code !== 0) {
            notifyError(json.msg || '评估引擎未就绪');
            return;
          }
          notifySuccess(json.msg || '已触发重新评估');
        })
        .catch(function () {
          setBusy(false);
          notifyError('网络错误，请稍后重试');
        });
    });
  }

  function bindDynamicEvents() {
    document.querySelectorAll('.js-ma-dim-weight, .js-ma-dim-onboard').forEach(function (el) {
      el.addEventListener('input', syncDimFromInputs);
    });

    document.querySelectorAll(
      '.js-ma-config[data-key="threshold"], .js-ma-config[data-key="low_threshold"]'
    ).forEach(function (el) {
      el.addEventListener('input', function () {
        var ruleId = el.getAttribute('data-rule-id');
        var key = el.getAttribute('data-key');
        var rule = ruleById(ruleId);
        if (rule && key) {
          if (!rule.config) rule.config = {};
          rule.config[key] = parseNumSafe(el.value, 0);
        }
        applyLinkedThresholdsToState();
        syncLinkedThresholdDisplays();
      });
    });
  }

  function bindStaticEvents() {
    ['maSaveBtn', 'maSaveBtn2'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          saveConfig();
        });
      }
    });
    ['maReassessBtn', 'maReassessBtn2'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          reassessAll();
        });
      }
    });
  }

  function loadConfig() {
    var boot = window.__MERCHANT_ASSESS_CONFIG__;
    if (boot && (boot.dimensions || boot.rules)) {
      applyConfig(boot);
      return;
    }

    state.loading = true;
    fetch(CONFIG_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (json.code !== 0) {
          notifyError(json.msg || '加载失败');
          return;
        }
        applyConfig(json.data || {});
      })
      .catch(function () {
        state.loading = false;
        notifyError('网络错误，请稍后重试');
      });
  }

  function init() {
    bindStaticEvents();
    loadConfig();
  }

  return { init: init };
})();
